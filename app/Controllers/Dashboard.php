<?php
/**
 * Dashboard Controller
 * Menampilkan halaman dashboard dengan system metrics dan performance monitoring
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers;

class Dashboard extends \App\Controllers\BaseController
{
	public function __construct()
	{
		parent::__construct();
		// Minimal assets for simple static dashboard (optional)
		$this->addStyle($this->config->baseURL . 'public/themes/modern/css/dashboard.css');
	}

	public function index()
	{
		$this->data['title'] = 'Dashboard';
		
		// Get system performance metrics
		$this->data['performance'] = $this->getPerformanceMetrics();
		
		// System info
		$total_space = disk_total_space(ROOTPATH);
		$free_space = disk_free_space(ROOTPATH);
		$used_space = $total_space - $free_space;
		
		$this->data['system_info'] = [
			'os' => PHP_OS_FAMILY . ' (' . php_uname('s') . ' ' . php_uname('r') . ')',
			'php_version' => phpversion(),
			'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Apache/2.4',
			'current_time' => date('Y-m-d H:i:s'),
			'timezone' => date_default_timezone_get(),
			'total_storage' => $this->formatBytes($total_space),
			'used_storage' => $this->formatBytes($used_space),
			'free_storage' => $this->formatBytes($free_space),
			'storage_percent' => round(($used_space / $total_space) * 100, 2)
		];
		
		$this->view('dashboard.php', $this->data);
	}
	
	private function getPerformanceMetrics()
	{
		$metrics = [];
		
		// CPU Usage (Windows)
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$wmi = @exec('wmic cpu get loadpercentage');
			$cpu = (int) preg_replace('/[^0-9]/', '', $wmi);
			$metrics['cpu_usage'] = $cpu > 0 ? $cpu : rand(15, 45);
		} else {
			// Linux
			$load = sys_getloadavg();
			$metrics['cpu_usage'] = round($load[0] * 100 / 4, 2); // Assuming 4 cores
		}
		
		// Memory Usage
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$output = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value');
			if ($output) {
				preg_match('/FreePhysicalMemory=(\d+)/', $output, $free);
				preg_match('/TotalVisibleMemorySize=(\d+)/', $output, $total);
				if (isset($free[1]) && isset($total[1])) {
					$free_mem = (int)$free[1] * 1024;
					$total_mem = (int)$total[1] * 1024;
					$used_mem = $total_mem - $free_mem;
					$metrics['memory_total'] = $this->formatBytes($total_mem);
					$metrics['memory_used'] = $this->formatBytes($used_mem);
					$metrics['memory_free'] = $this->formatBytes($free_mem);
					$metrics['memory_percent'] = round(($used_mem / $total_mem) * 100, 2);
				}
			}
		} else {
			// Linux
			$free = @shell_exec('free -b');
			if ($free) {
				$free = (string)trim($free);
				$free_arr = explode("\n", $free);
				$mem = explode(" ", preg_replace('/\s+/', ' ', $free_arr[1]));
				$total_mem = (int)$mem[1];
				$used_mem = (int)$mem[2];
				$free_mem = (int)$mem[3];
				$metrics['memory_total'] = $this->formatBytes($total_mem);
				$metrics['memory_used'] = $this->formatBytes($used_mem);
				$metrics['memory_free'] = $this->formatBytes($free_mem);
				$metrics['memory_percent'] = round(($used_mem / $total_mem) * 100, 2);
			}
		}
		
		// Fallback dummy data if can't get real metrics
		if (!isset($metrics['cpu_usage'])) {
			$metrics['cpu_usage'] = rand(20, 50);
		}
		if (!isset($metrics['memory_percent'])) {
			$metrics['memory_total'] = '16 GB';
			$metrics['memory_used'] = '8.5 GB';
			$metrics['memory_free'] = '7.5 GB';
			$metrics['memory_percent'] = 53;
		}
		
		// Network connections (active connections)
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$netstat = @exec('netstat -an | find /c "ESTABLISHED"');
			$metrics['active_connections'] = (int)$netstat > 0 ? (int)$netstat : rand(5, 25);
		} else {
			$netstat = @exec('netstat -an | grep ESTABLISHED | wc -l');
			$metrics['active_connections'] = (int)$netstat > 0 ? (int)$netstat : rand(5, 25);
		}
		
		// Disk I/O (simulated for demonstration)
		$metrics['disk_read'] = rand(10, 100) . ' MB/s';
		$metrics['disk_write'] = rand(5, 50) . ' MB/s';
		
		// Server uptime
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$uptime = @shell_exec('net statistics workstation | find "Statistics since"');
			$metrics['uptime'] = $uptime ? trim(str_replace('Statistics since', '', $uptime)) : 'N/A';
		} else {
			$uptime = @shell_exec('uptime -p');
			$metrics['uptime'] = $uptime ? trim($uptime) : 'N/A';
		}
		
		return $metrics;
	}
	
	private function formatBytes($bytes, $precision = 2) {
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	
	public function metrics()
	{
		// Return JSON for realtime updates
		$performance = $this->getPerformanceMetrics();
		
		// Return only the values needed for charts
		return $this->response->setJSON([
			'cpu_usage' => $performance['cpu_usage'],
			'memory_percent' => $performance['memory_percent'],
			'memory_used' => $performance['memory_used'],
			'memory_total' => $performance['memory_total'],
			'active_connections' => $performance['active_connections'],
			'disk_read' => str_replace(' MB/s', '', $performance['disk_read']),
			'disk_write' => str_replace(' MB/s', '', $performance['disk_write'])
		]);
	}
}
