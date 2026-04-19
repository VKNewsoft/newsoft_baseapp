<?php
/**
 * SecurityLogModel - Model untuk log serangan keamanan
 * 
 * @package App\Models
 * @year 2020-2026
 */

namespace App\Modules\SecurityMonitor\Models;

use CodeIgniter\Model;

class SecurityLogModel extends Model
{
	protected $table = 'security_logs';
	protected $primaryKey = 'id';
	protected $allowedFields = ['ip_address', 'attack_type', 'request_uri', 'user_agent', 'created_at', 'blocked_until', 'request_count'];
	protected $useTimestamps = false;

	public function getSummary(array $filters = []): array
	{
		$builder = $this->applyFilters($this->builder(), $filters, false);
		$row = $builder
			->select('COUNT(*) as total_attacks, SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_attacks, SUM(CASE WHEN blocked_until IS NOT NULL THEN 1 ELSE 0 END) as blocked_events, SUM(CASE WHEN attack_type = "brute_force" THEN 1 ELSE 0 END) as brute_force_count, SUM(CASE WHEN attack_type = "csrf_attempt" THEN 1 ELSE 0 END) as csrf_count', false)
			->get()
			->getRowArray();

		return [
			'total_attacks' => (int) ($row['total_attacks'] ?? 0),
			'today_attacks' => (int) ($row['today_attacks'] ?? 0),
			'blocked_events' => (int) ($row['blocked_events'] ?? 0),
			'brute_force_count' => (int) ($row['brute_force_count'] ?? 0),
			'csrf_count' => (int) ($row['csrf_count'] ?? 0),
		];
	}

	public function getStatusCounts(array $filters = []): array
	{
		$builder = $this->applyFilters($this->builder(), $filters, false);
		$rows = $builder
			->select('CASE WHEN blocked_until IS NULL THEN "allowed" ELSE "blocked" END as event_status, COUNT(*) as total', false)
			->groupBy('CASE WHEN blocked_until IS NULL THEN "allowed" ELSE "blocked" END', false)
			->get()
			->getResultArray();

		$result = ['allowed' => 0, 'blocked' => 0];
		foreach ($rows as $row) {
			$result[$row['event_status']] = (int) $row['total'];
		}

		return $result;
	}

	public function getLogsWithCount($limit = 10, array $filters = [])
	{
		return $this->applyFilters($this->builder(), $filters, false)
			->select('attack_type, COUNT(*) as total')
			->groupBy('attack_type')
			->orderBy('total', 'DESC')
			->limit($limit)
			->get()
			->getResultArray();
	}

	public function getTopAttackers($limit = 5, array $filters = []): array
	{
		return $this->applyFilters($this->builder(), $filters, false)
			->select('ip_address, COUNT(*) as total, MAX(created_at) as last_seen')
			->groupBy('ip_address')
			->orderBy('total', 'DESC')
			->limit($limit)
			->get()
			->getResultArray();
	}

	public function getAlerts($limit = 6, array $filters = []): array
	{
		$rows = $this->applyFilters($this->builder(), $filters, false)
			->select('ip_address, attack_type, COUNT(*) as total, MAX(created_at) as last_seen, MAX(blocked_until) as blocked_until')
			->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
			->groupBy('ip_address, attack_type')
			->having('total >=', 3)
			->orderBy('total', 'DESC')
			->limit($limit)
			->get()
			->getResultArray();

		foreach ($rows as &$row) {
			$row['severity'] = $this->getSeverity($row['attack_type'], $row['total'], !empty($row['blocked_until']));
		}

		return $rows;
	}

	public function getAttacksByDay($days = 7, array $filters = [])
	{
		$builder = $this->applyFilters($this->builder(), $filters, false);
		$result = $builder
			->select('DATE(created_at) as date, COUNT(*) as total')
			->where('created_at >=', date('Y-m-d', strtotime("-{$days} days")))
			->groupBy('DATE(created_at)')
			->orderBy('date')
			->get()
			->getResultArray();

		$data = [];
		for ($i = $days; $i >= 0; $i--) {
			$date = date('Y-m-d', strtotime("-$i days"));
			$data[$date] = 0;
		}

		foreach ($result as $row) {
			$data[$row['date']] = (int) $row['total'];
		}

		return $data;
	}

	public function getHourlyHeatmap(array $filters = []): array
	{
		$builder = $this->applyFilters($this->builder(), $filters, false);
		$rows = $builder
			->select('HOUR(created_at) as hour_slot, COUNT(*) as total')
			->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
			->groupBy('hour_slot')
			->orderBy('hour_slot', 'ASC')
			->get()
			->getResultArray();

		$data = [];
		for ($i = 0; $i < 24; $i++) {
			$data[$i] = 0;
		}

		foreach ($rows as $row) {
			$data[(int) $row['hour_slot']] = (int) $row['total'];
		}

		return $data;
	}

	public function getDistinctAttackTypes(): array
	{
		return array_values(array_filter(array_map(
			static fn ($row) => $row['attack_type'] ?? '',
			$this->select('attack_type')->distinct()->orderBy('attack_type', 'ASC')->findAll()
		)));
	}

	public function getTotalAttacks(array $filters = []): int
	{
		return (int) $this->applyFilters($this->builder(), $filters, false)->countAllResults();
	}

	public function getAttacksToday(array $filters = []): int
	{
		return (int) $this->applyFilters($this->builder(), $filters, false)
			->where('DATE(created_at)', date('Y-m-d'))
			->countAllResults();
	}

	public function getFilteredLogs(array $filters = [], int $perPage = 15, string $group = 'default', ?int $page = null): array
	{
		$builder = $this->applyFilters($this, $filters, true)->orderBy('created_at', 'DESC');
		$rows = $builder->paginate($perPage, $group, $page);
		return $this->hydrateLogs($rows);
	}

	public function getDataTableResult(array $filters = [], array $dt = []): array
	{
		$columns = [
			0 => 'created_at',
			1 => 'ip_address',
			2 => 'attack_type',
			3 => 'blocked_until',
			4 => 'request_uri',
			5 => 'request_uri',
			6 => 'user_agent',
			7 => 'id',
		];

		$start = max(0, (int) ($dt['start'] ?? 0));
		$length = (int) ($dt['length'] ?? 15);
		$length = $length > 0 ? min($length, 50) : 15;
		$orderIndex = (int) ($dt['order'][0]['column'] ?? 0);
		$orderDir = strtolower((string) ($dt['order'][0]['dir'] ?? 'desc')) === 'asc' ? 'ASC' : 'DESC';
		$orderColumn = $columns[$orderIndex] ?? 'created_at';

		$total = $this->countAllResults();
		$filteredBuilder = $this->applyFilters($this->builder(), $filters, true);
		$filtered = (clone $filteredBuilder)->countAllResults();
		$rows = $filteredBuilder
			->orderBy($orderColumn, $orderDir)
			->limit($length, $start)
			->get()
			->getResultArray();

		return [
			'total' => $total,
			'filtered' => $filtered,
			'rows' => $this->hydrateLogs($rows),
			'loaded' => count($rows),
		];
	}

	public function findEvent(int $id): ?array
	{
		$row = $this->where('id', $id)->first();
		if (!$row) {
			return null;
		}

		return $this->hydrateLogs([$row])[0] ?? null;
	}

	public function getAttackTypeBreakdown(array $filters = []): array
	{
		$rows = $this->getLogsWithCount(20, $filters);
		$result = [];
		foreach ($rows as $row) {
			$result[] = [
				'label' => $row['attack_type'],
				'total' => (int) $row['total'],
				'severity' => $this->getSeverity($row['attack_type'], (int) $row['total']),
			];
		}
		return $result;
	}

	protected function applyFilters($builder, array $filters = [], bool $likeIp = true)
	{
		if (!empty($filters['ip'])) {
			if ($likeIp) {
				$builder->like('ip_address', trim((string) $filters['ip']));
			} else {
				$builder->where('ip_address', trim((string) $filters['ip']));
			}
		}

		if (!empty($filters['attack_type'])) {
			$builder->where('attack_type', trim((string) $filters['attack_type']));
		}

		if (!empty($filters['status'])) {
			if ($filters['status'] === 'blocked') {
				$builder->where('blocked_until IS NOT NULL', null, false);
			} elseif ($filters['status'] === 'allowed') {
				$builder->where('blocked_until IS NULL', null, false);
			}
		}

		if (!empty($filters['date_from'])) {
			$builder->where('created_at >=', trim((string) $filters['date_from']) . ' 00:00:00');
		}

		if (!empty($filters['date_to'])) {
			$builder->where('created_at <=', trim((string) $filters['date_to']) . ' 23:59:59');
		}

		return $builder;
	}

	protected function hydrateLogs(array $rows): array
	{
		foreach ($rows as &$row) {
			$requestContext = $this->decodeJsonField($row['request_uri'] ?? '');
			$userContext = $this->decodeJsonField($row['user_agent'] ?? '');

			$row['request_context'] = $requestContext;
			$row['user_context'] = $userContext;
			$row['event_status'] = empty($row['blocked_until']) ? 'allowed' : 'blocked';
			$row['endpoint'] = $requestContext['endpoint'] ?? ($row['request_uri'] ?? '-');
			$row['request_method'] = $requestContext['method'] ?? 'GET';
			$row['payload_summary'] = $requestContext['payload'] ?? '';
			$row['request_reason'] = $requestContext['reason'] ?? '';
			$row['request_source'] = $requestContext['source'] ?? '';
			$row['threshold_limit'] = (int) ($requestContext['threshold_limit'] ?? 0);
			$row['count_in_window'] = (int) ($requestContext['count_in_window'] ?? ($row['request_count'] ?? 1));
			$row['user_label'] = $this->buildUserLabel($userContext);
			$row['user_agent_label'] = $userContext['user_agent'] ?? ($row['user_agent'] ?? '');
			$row['severity'] = $this->getSeverity($row['attack_type'] ?? '', $row['count_in_window'], $row['event_status'] === 'blocked');
		}

		return $rows;
	}

	protected function decodeJsonField($value): array
	{
		if (!is_string($value) || trim($value) === '') {
			return [];
		}

		$decoded = json_decode($value, true);
		return is_array($decoded) ? $decoded : [];
	}

	protected function buildUserLabel(array $userContext): string
	{
		$name = trim((string) ($userContext['name'] ?? ''));
		$username = trim((string) ($userContext['username'] ?? ''));
		$userId = (int) ($userContext['user_id'] ?? 0);

		if ($name !== '') {
			return $name . ($username !== '' ? ' (@' . $username . ')' : '');
		}

		if ($username !== '') {
			return '@' . $username;
		}

		return $userId > 0 ? 'User #' . $userId : 'Guest';
	}

	protected function getSeverity(string $attackType, int $count = 1, bool $blocked = false): string
	{
		$attackType = strtolower($attackType);
		if ($blocked || strpos($attackType, 'sql') !== false || strpos($attackType, 'rate_limit') !== false) {
			return 'critical';
		}

		if (strpos($attackType, 'xss') !== false || strpos($attackType, 'brute') !== false || $count >= 5) {
			return 'high';
		}

		if (strpos($attackType, 'csrf') !== false || strpos($attackType, 'suspicious') !== false) {
			return 'medium';
		}

		return 'low';
	}
}
