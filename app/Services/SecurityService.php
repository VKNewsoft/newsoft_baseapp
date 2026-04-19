<?php

namespace App\Services;

class SecurityService
{
	protected $db;
	protected $cache;
	protected $request;
	protected $session;
	protected $ip;
	protected $blockDuration = 3600;
	protected $rateWindow = 60;
	protected $rateLimit = 90;
	protected $eventDedupeWindow = 8;
	protected $thresholds = [
		'sql_injection' => ['limit' => 3, 'window' => 900],
		'xss' => ['limit' => 3, 'window' => 900],
		'csrf_attempt' => ['limit' => 5, 'window' => 1800],
		'brute_force' => ['limit' => 5, 'window' => 900],
		'suspicious_request' => ['limit' => 6, 'window' => 900],
		'rate_limit' => ['limit' => 1, 'window' => 300],
		'sqlmap' => ['limit' => 1, 'window' => 300],
	];

	public function __construct()
	{
		$this->db = db_connect();
		$this->cache = cache();
		$this->request = service('request');
		$this->session = session();
		$this->ip = (string) ($this->request->getIPAddress() ?? '');
	}

	public function getThresholds(): array
	{
		return $this->thresholds;
	}

	public function isBlocked(): bool
	{
		$cacheKey = $this->getBlockedCacheKey($this->ip);
		$cached = $this->cache->get($cacheKey);
		if ($cached === true) {
			return true;
		}

		$isBlocked = $this->db->table('blocked_ips')
			->where('ip_address', $this->ip)
			->countAllResults() > 0;

		if ($isBlocked) {
			$this->cache->save($cacheKey, true, $this->blockDuration);
		}

		return $isBlocked;
	}

	public function logAttack($attack, array $meta = []): array
	{
		$type = is_array($attack) ? (string) ($attack['type'] ?? 'suspicious_request') : (string) $attack;
		$meta = is_array($attack) ? array_merge($attack, $meta) : $meta;
		$status = 'allowed';
		$threshold = $this->resolveThreshold($type);
		$recentCount = $this->countRecentEvents($type, $threshold['window']) + 1;

		if ($this->shouldBlock($type, $recentCount)) {
			$this->blockIp($this->ip);
			$status = 'blocked';
		}

		$this->insertEvent($type, $status, array_merge($meta, [
			'count_in_window' => $recentCount,
			'window_seconds' => $threshold['window'],
			'threshold_limit' => $threshold['limit'],
		]));

		return [
			'type' => $type,
			'status' => $status,
			'count_in_window' => $recentCount,
			'threshold' => $threshold,
		];
	}

	public function logCsrfAttempt(array $detail = []): array
	{
		$detail['category'] = 'csrf';
		return $this->logAttack('csrf_attempt', $detail);
	}

	public function logBruteForceAttempt(string $username = '', string $reason = ''): array
	{
		return $this->logAttack('brute_force', [
			'category' => 'authentication',
			'username' => $username,
			'reason' => $reason,
			'endpoint' => $this->getRequestPath(),
			'payload' => $this->buildPayloadSummary([
				'username' => $username,
			]),
		]);
	}

	public function incrementRequest(): bool
	{
		$path = $this->getRequestPath();
		$cacheKey = 'security_rate_' . md5($this->ip . '|' . $path);
		$rateState = $this->cache->get($cacheKey);
		$now = time();

		if (!is_array($rateState) || ($rateState['expires_at'] ?? 0) < $now) {
			$rateState = [
				'count' => 0,
				'expires_at' => $now + $this->rateWindow,
			];
		}

		$rateState['count']++;
		$ttl = max(1, $rateState['expires_at'] - $now);
		$this->cache->save($cacheKey, $rateState, $ttl);

		if ($rateState['count'] > $this->rateLimit) {
			$this->blockIp($this->ip);
			$this->insertEvent('rate_limit', 'blocked', [
				'category' => 'traffic',
				'endpoint' => $path,
				'payload' => $this->buildPayloadSummary(),
				'count_in_window' => $rateState['count'],
				'window_seconds' => $this->rateWindow,
				'threshold_limit' => $this->rateLimit,
			]);
			return false;
		}

		return true;
	}

	public function blockIp($ip): void
	{
		if (!$ip || !$this->isBlockableIp($ip)) {
			return;
		}

		$this->db->table('blocked_ips')->replace([
			'ip_address' => $ip,
			'blocked_at' => date('Y-m-d H:i:s'),
		]);

		$this->cache->save($this->getBlockedCacheKey($ip), true, $this->blockDuration);
	}

	protected function insertEvent(string $attackType, string $status, array $meta = []): void
	{
		if ($this->shouldSkipDuplicate($attackType, $status, $meta)) {
			return;
		}

		$requestContext = [
			'endpoint' => $meta['endpoint'] ?? $this->getRequestPath(),
			'method' => strtoupper((string) ($this->request->getMethod() ?? 'GET')),
			'status' => $status,
			'payload' => $meta['payload'] ?? $this->buildPayloadSummary(),
			'referer' => substr((string) ($this->request->getServer('HTTP_REFERER') ?? ''), 0, 255),
			'pattern' => $meta['pattern'] ?? null,
			'source' => $meta['source'] ?? null,
			'reason' => $meta['reason'] ?? null,
			'count_in_window' => $meta['count_in_window'] ?? 1,
			'window_seconds' => $meta['window_seconds'] ?? null,
			'threshold_limit' => $meta['threshold_limit'] ?? null,
		];

		$userContext = [
			'user_id' => (int) (($this->session->get('user')['id_user'] ?? 0)),
			'username' => (string) ($meta['username'] ?? ($this->session->get('user')['username'] ?? '')),
			'name' => (string) ($this->session->get('user')['nama'] ?? ''),
			'user_agent' => $this->getUserAgentString(),
		];

		$this->db->table('security_logs')->insert([
			'ip_address' => $this->ip,
			'request_uri' => json_encode($requestContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'user_agent' => json_encode($userContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
			'attack_type' => $attackType,
			'created_at' => date('Y-m-d H:i:s'),
			'blocked_until' => $status === 'blocked' ? date('Y-m-d H:i:s', time() + $this->blockDuration) : null,
			'request_count' => (int) ($meta['count_in_window'] ?? 1),
		]);

		log_message(
			$status === 'blocked' ? 'critical' : 'warning',
			'Security event {type} from {ip} on {endpoint} ({status})',
			[
				'type' => $attackType,
				'ip' => $this->ip,
				'endpoint' => $requestContext['endpoint'],
				'status' => $status,
			]
		);
	}

	protected function shouldBlock(string $type, int $count): bool
	{
		$threshold = $this->resolveThreshold($type);
		return $count >= $threshold['limit'];
	}

	protected function resolveThreshold(string $type): array
	{
		return $this->thresholds[$type] ?? ['limit' => 5, 'window' => 900];
	}

	protected function countRecentEvents(string $type, int $window): int
	{
		$windowStart = date('Y-m-d H:i:s', time() - $window);

		return (int) $this->db->table('security_logs')
			->where('ip_address', $this->ip)
			->where('attack_type', $type)
			->where('created_at >=', $windowStart)
			->countAllResults();
	}

	protected function shouldSkipDuplicate(string $type, string $status, array $meta): bool
	{
		$signature = md5(json_encode([
			'ip' => $this->ip,
			'type' => $type,
			'status' => $status,
			'endpoint' => $meta['endpoint'] ?? $this->getRequestPath(),
			'payload' => $meta['payload'] ?? $this->buildPayloadSummary(),
			'pattern' => $meta['pattern'] ?? null,
		]));

		$cacheKey = 'security_event_dup_' . $signature;
		if ($this->cache->get($cacheKey)) {
			return true;
		}

		$this->cache->save($cacheKey, true, $this->eventDedupeWindow);
		return false;
	}

	protected function getRequestPath(): string
	{
		$uri = $this->request->getUri();
		return $uri ? (string) $uri->getPath() : current_url();
	}

	protected function getUserAgentString(): string
	{
		$userAgent = $this->request->getUserAgent();
		return $userAgent ? substr((string) $userAgent->getAgentString(), 0, 255) : '';
	}

	protected function buildPayloadSummary(array $override = []): string
	{
		$payload = array_merge($this->sanitizePayloadData($this->request->getGet() ?? []), $this->sanitizePayloadData($this->request->getPost() ?? []), $override);
		if (!$payload) {
			$rawBody = trim((string) ($this->request->getBody() ?? ''));
			return $rawBody !== '' ? substr(preg_replace('/\s+/', ' ', $rawBody), 0, 240) : '';
		}

		$parts = [];
		foreach ($payload as $key => $value) {
			$parts[] = $key . '=' . $value;
			if (strlen(implode('&', $parts)) > 220) {
				break;
			}
		}

		return substr(implode('&', $parts), 0, 240);
	}

	protected function sanitizePayloadData(array $payload, string $prefix = ''): array
	{
		$result = [];
		$sensitiveKeys = ['password', 'pass', 'pwd', 'csrf', 'token', 'remember'];

		foreach ($payload as $key => $value) {
			$fullKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;
			$keyLower = strtolower((string) $key);

			if (in_array($keyLower, $sensitiveKeys, true)) {
				$result[$fullKey] = '[masked]';
				continue;
			}

			if (is_array($value)) {
				$result += $this->sanitizePayloadData($value, $fullKey);
				continue;
			}

			$clean = substr(preg_replace('/\s+/', ' ', (string) $value), 0, 80);
			if ($clean !== '') {
				$result[$fullKey] = $clean;
			}
		}

		return $result;
	}

	protected function isBlockableIp(string $ip): bool
	{
		if ($ip === '' || in_array($ip, ['127.0.0.1', '::1'], true)) {
			return false;
		}

		if ($this->db->tableExists('whitelist_ips')) {
			$isWhitelisted = $this->db->table('whitelist_ips')
				->where('ip_address', $ip)
				->countAllResults() > 0;
			if ($isWhitelisted) {
				return false;
			}
		}

		return true;
	}

	protected function getBlockedCacheKey(string $ip): string
	{
		return 'blocked_ip_' . md5($ip);
	}
}
