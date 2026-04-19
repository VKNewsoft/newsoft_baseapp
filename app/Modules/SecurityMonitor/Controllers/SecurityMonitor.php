<?php
/**
 * SecurityMonitor.php
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

namespace App\Modules\SecurityMonitor\Controllers;

use App\Modules\SecurityMonitor\Models\SecurityLogModel;
use App\Modules\SecurityMonitor\Models\BlockedIpModel;

class Securitymonitor extends \App\Modules\Common\Controllers\BaseController
{
	protected $logModel;
	protected $blockModel;

	public function __construct()
	{
		parent::__construct();

		$this->logModel = new SecurityLogModel();
		$this->blockModel = new BlockedIpModel();
		$this->data['title'] = 'Security Aplikasi';
		helper(['cookie', 'form']);
	}

	public function index()
	{
		$filters = $this->getLogFilters();
		$summary = $this->logModel->getSummary($filters);
		$statusCounts = $this->logModel->getStatusCounts($filters);

		$this->data = array_merge($this->data, $summary, [
			'blocked_count' => $this->blockModel->countAllResults(),
			'blocked_events' => $statusCounts['blocked'] ?? 0,
			'allowed_events' => $statusCounts['allowed'] ?? 0,
			'alerts' => $this->logModel->getAlerts(6, $filters),
			'top_attackers' => $this->logModel->getTopAttackers(6, $filters),
			'attack_breakdown' => $this->logModel->getAttackTypeBreakdown($filters),
			'attack_types' => $this->logModel->getDistinctAttackTypes(),
			'filters' => $filters,
			'filter_active' => $this->hasActiveFilter($filters),
			'loaded_rows' => min(15, (int) ($summary['total_attacks'] ?? 0)),
		]);

		$this->view('security_monitor/index.php', $this->data);
	}

	public function logsData()
	{
		$filters = $this->getLogFilters();
		$result = $this->logModel->getDataTableResult($filters, $this->request->getPost());
		$data = [];
		foreach ($result['rows'] as $log) {
			$data[] = [
				'id' => (int) $log['id'],
				'created_at' => date('d M Y, H:i:s', strtotime($log['created_at'])),
				'ip_address' => $log['ip_address'],
				'attack_type' => $log['attack_type'],
				'severity' => $log['severity'],
				'event_status' => $log['event_status'],
				'request_method' => $log['request_method'],
				'endpoint' => $log['endpoint'],
				'payload_summary' => $log['payload_summary'],
				'user_label' => $log['user_label'],
				'request_source' => $log['request_source'] !== '' ? $log['request_source'] : 'web_request',
			];
		}

		return $this->response->setJSON([
			'draw' => (int) ($this->request->getPost('draw') ?? 1),
			'recordsTotal' => $result['total'],
			'recordsFiltered' => $result['filtered'],
			'data' => $data,
			'loaded_count' => $result['loaded'],
		]);
	}

	public function chartData()
	{
		$filters = $this->getLogFilters();
		$attacksLast7Days = $this->logModel->getAttacksByDay(6, $filters);
		$attackTypes = $this->logModel->getLogsWithCount(6, $filters);
		$hourly = $this->logModel->getHourlyHeatmap($filters);
		$topAttackers = $this->logModel->getTopAttackers(5, $filters);

		return $this->response->setJSON([
			'timeline' => [
				'labels' => array_keys($attacksLast7Days),
				'data' => array_values($attacksLast7Days),
			],
			'types' => [
				'labels' => array_column($attackTypes, 'attack_type'),
				'data' => array_map('intval', array_column($attackTypes, 'total')),
			],
			'hourly' => [
				'labels' => array_map(static fn ($hour) => str_pad((string) $hour, 2, '0', STR_PAD_LEFT) . ':00', array_keys($hourly)),
				'data' => array_values($hourly),
			],
			'attackers' => [
				'labels' => array_column($topAttackers, 'ip_address'),
				'data' => array_map('intval', array_column($topAttackers, 'total')),
			],
		]);
	}

	public function eventDetail($id = null)
	{
		$id = (int) $id;
		if ($id <= 0) {
			return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Event tidak ditemukan.']);
		}

		$event = $this->logModel->findEvent($id);
		if (!$event) {
			return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Event tidak ditemukan.']);
		}

		return $this->response->setJSON([
			'success' => true,
			'data' => $event,
		]);
	}

	public function blocked()
	{
		$filters = [
			'search' => trim((string) $this->request->getGet('search')),
			'date_from' => trim((string) $this->request->getGet('date_from')),
			'date_to' => trim((string) $this->request->getGet('date_to')),
		];
		$summary = $this->blockModel->getSummary($filters);

		$this->data += [
			'search' => $filters['search'],
			'filters' => $filters,
			'blocked_summary' => $summary,
			'loaded_rows' => min(15, (int) ($summary['total_blocked'] ?? 0)),
		];

		$this->view('security_monitor/blocked.php', $this->data);
	}

	public function blockedData()
	{
		$filters = [
			'search' => trim((string) $this->request->getGet('search')),
			'date_from' => trim((string) $this->request->getGet('date_from')),
			'date_to' => trim((string) $this->request->getGet('date_to')),
		];
		$result = $this->blockModel->getDataTableResult($filters, $this->request->getPost());
		$start = max(0, (int) ($this->request->getPost('start') ?? 0));
		$data = [];
		foreach ($result['rows'] as $index => $row) {
			$data[] = [
				'rownum' => $start + $index + 1,
				'ip_address' => $row['ip_address'],
				'blocked_date' => date('d M Y', strtotime($row['blocked_at'])),
				'blocked_time' => date('H:i:s', strtotime($row['blocked_at'])),
			];
		}

		return $this->response->setJSON([
			'draw' => (int) ($this->request->getPost('draw') ?? 1),
			'recordsTotal' => $result['total'],
			'recordsFiltered' => $result['filtered'],
			'data' => $data,
			'loaded_count' => $result['loaded'],
		]);
	}

	public function unblock()
	{
		$ip = $this->request->getPost('ip');
		if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
			$this->blockModel->unblockIp($ip);
			cache()->delete('blocked_ip_' . md5($ip));
			return $this->response->setJSON(['success' => true, 'message' => "IP $ip berhasil dibuka."]);
		}
		return $this->response->setJSON(['success' => false, 'message' => 'IP tidak valid.']);
	}

	protected function getLogFilters(): array
	{
		return [
			'ip' => trim((string) $this->request->getGet('ip')),
			'attack_type' => trim((string) $this->request->getGet('attack_type')),
			'status' => trim((string) $this->request->getGet('status')),
			'date_from' => trim((string) $this->request->getGet('date_from')),
			'date_to' => trim((string) $this->request->getGet('date_to')),
		];
	}

	protected function hasActiveFilter(array $filters): bool
	{
		foreach ($filters as $value) {
			if ($value !== '') {
				return true;
			}
		}

		return false;
	}
}
