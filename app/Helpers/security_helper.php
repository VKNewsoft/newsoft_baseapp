<?php
/**
 * security_helper.php
 * Security Attack Detection Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

if (!function_exists('detect_attack')) {
	function detect_attack($request)
	{
		$sources = [
			'body' => (string) ($request->getBody() ?? ''),
			'get' => $request->getGet() ? http_build_query($request->getGet()) : '',
			'post' => $request->getPost() ? http_build_query($request->getPost()) : '',
			'uri' => (string) ($request->getUri()->getPath() ?? ''),
			'user_agent' => (string) (($request->getUserAgent() ? $request->getUserAgent()->getAgentString() : '') ?? ''),
		];

		$patterns = [
			'sql_injection' => [
				'/\bunion\s+all\s+select\b/i',
				'/\b(select|insert|update|delete|drop|alter)\b[\s\S]{0,60}\b(from|into|table)\b/i',
				'/\b(or|and)\b\s+[\'"]?\d+[\'"]?\s*=\s*[\'"]?\d+[\'"]?/i',
				'/\bsleep\s*\(\s*\d+\s*\)/i',
				'/\bbenchmark\s*\(/i',
			],
			'xss' => [
				'/<script[^>]*>[\s\S]*?<\/script>/i',
				'/<img[^>]+onerror\s*=/i',
				'/<svg[^>]+onload\s*=/i',
				'/javascript\s*:/i',
				'/<iframe[^>]*>/i',
			],
			'sqlmap' => [
				'/sqlmap/i',
				'/sqlmap\/\d+/i',
			],
			'suspicious_request' => [
				'/\.\.\//i',
				'/\.\.\\\\/i',
				'/\b(?:cmd|powershell|wget|curl)\b[\s\S]{0,25}(?:;|&&|\|)/i',
				'/%00/i',
				'/base64_(?:encode|decode)\s*\(/i',
				'/\b(?:eval|assert|system|shell_exec)\s*\(/i',
			],
		];

		foreach ($patterns as $type => $regexes) {
			foreach ($sources as $sourceName => $sourceValue) {
				if ($sourceValue === '') {
					continue;
				}

				foreach ($regexes as $regex) {
					if (preg_match($regex, $sourceValue, $matches)) {
						return [
							'type' => $type,
							'source' => $sourceName,
							'pattern' => $regex,
							'payload' => substr(preg_replace('/\s+/', ' ', (string) ($matches[0] ?? $sourceValue)), 0, 180),
						];
					}
				}
			}
		}

		return false;
	}
}

if (!function_exists('get_client_ip')) {
	function get_client_ip()
	{
		$request = service('request');
		return $request->getIPAddress();
	}
}
