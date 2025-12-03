<?php
/**
 * dashboard.php
 * Dashboard View
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

helper('html');
helper('format');
?>
<style>
.dashboard-card {
	border-radius: var(--radius);
	border: 1px solid var(--border);
	transition: var(--transition);
	animation: fadeIn 0.4s ease;
	height: 100%;
}
.dashboard-card:hover {
	box-shadow: var(--shadow-md);
	transform: translateY(-2px);
}
.stat-card {
	min-height: 140px;
}
.chart-card {
	min-height: 350px;
}
.stat-icon {
	width: 56px;
	height: 56px;
	border-radius: var(--radius);
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 28px;
}
.metric-value {
	font-size: 2.5rem;
	font-weight: 700;
	line-height: 1;
}
.pulse-indicator {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: #10b981;
	display: inline-block;
	animation: pulse 2s infinite;
}
@keyframes pulse {
	0%, 100% { opacity: 1; }
	50% { opacity: 0.5; }
}
.chart-container {
	position: relative;
	height: 280px;
}
</style>

<div class="container-fluid">
	<!-- Performance Metrics Cards -->
	<div class="row g-3 mb-3">
		<!-- CPU Usage -->
		<div class="col-lg-3 col-md-6">
			<div class="card dashboard-card stat-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">CPU Usage</p>
							<span class="pulse-indicator"></span>
							<small class="text-muted ms-1">Live</small>
						</div>
						<div class="stat-icon bg-danger bg-opacity-10 text-danger">
							<i class="bi bi-cpu"></i>
						</div>
					</div>
					<h2 class="mb-2 fw-bold" id="cpu-value"><?= $performance['cpu_usage'] ?>%</h2>
					<div class="progress mt-auto" style="height: 8px;">
						<div class="progress-bar bg-danger" id="cpu-bar" style="width: <?= $performance['cpu_usage'] ?>%"></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Memory Usage -->
		<div class="col-lg-3 col-md-6">
			<div class="card dashboard-card stat-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Memory Usage</p>
							<span class="pulse-indicator"></span>
							<small class="text-muted ms-1">Live</small>
						</div>
						<div class="stat-icon bg-warning bg-opacity-10 text-warning">
							<i class="bi bi-memory"></i>
						</div>
					</div>
					<h2 class="mb-1 fw-bold" id="memory-value"><?= $performance['memory_percent'] ?>%</h2>
					<small class="text-muted" id="memory-detail"><?= $performance['memory_used'] ?> / <?= $performance['memory_total'] ?></small>
					<div class="progress mt-auto" style="height: 8px;">
						<div class="progress-bar bg-warning" id="memory-bar" style="width: <?= $performance['memory_percent'] ?>%"></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Active Connections -->
		<div class="col-lg-3 col-md-6">
			<div class="card dashboard-card stat-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Active Connections</p>
							<span class="pulse-indicator"></span>
							<small class="text-muted ms-1">Live</small>
						</div>
						<div class="stat-icon bg-info bg-opacity-10 text-info">
							<i class="bi bi-ethernet"></i>
						</div>
					</div>
					<h2 class="mb-2 fw-bold" id="connections-value"><?= $performance['active_connections'] ?></h2>
					<small class="text-muted mt-auto">Network connections</small>
				</div>
			</div>
		</div>

		<!-- Disk I/O -->
		<div class="col-lg-3 col-md-6">
			<div class="card dashboard-card stat-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Disk I/O</p>
							<span class="pulse-indicator"></span>
							<small class="text-muted ms-1">Live</small>
						</div>
						<div class="stat-icon bg-primary bg-opacity-10 text-primary">
							<i class="bi bi-hdd"></i>
						</div>
					</div>
					<div class="mt-auto">
						<div class="d-flex justify-content-between mb-2">
							<div>
								<small class="text-muted">Read</small>
								<p class="mb-0 fw-semibold" id="disk-read"><?= $performance['disk_read'] ?></p>
							</div>
							<div class="text-end">
								<small class="text-muted">Write</small>
								<p class="mb-0 fw-semibold" id="disk-write"><?= $performance['disk_write'] ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="row g-3 mb-3">
		<!-- CPU & Memory Chart -->
		<div class="col-lg-8">
			<div class="card dashboard-card chart-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-bar-chart-line text-primary me-2"></i>System Performance</h5>
				</div>
				<div class="card-body">
					<div class="chart-container">
						<canvas id="performanceChart"></canvas>
					</div>
				</div>
			</div>
		</div>

		<!-- Storage Overview -->
		<div class="col-lg-4">
			<div class="card dashboard-card chart-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-pie-chart text-success me-2"></i>Storage Overview</h5>
				</div>
				<div class="card-body d-flex align-items-center justify-content-center">
					<div class="chart-container" style="max-width: 250px; max-height: 250px;">
						<canvas id="storageChart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Network & Disk Activity Chart -->
	<div class="row g-3 mb-3">
		<div class="col-lg-12">
			<div class="card dashboard-card chart-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-activity text-info me-2"></i>Network & Disk Activity</h5>
				</div>
				<div class="card-body">
					<div class="chart-container">
						<canvas id="activityChart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- System Information -->
	<div class="row g-3">
		<div class="col-12">
			<div class="card dashboard-card border-0 shadow-sm" style="min-height: 200px;">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-info-circle text-primary me-2"></i>System Information</h5>
				</div>
				<div class="card-body">
					<div class="row g-3 mb-3">
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">Operating System</small>
							<p class="mb-0 fw-semibold"><?= htmlspecialchars($system_info['os']) ?></p>
						</div>
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">Server Software</small>
							<p class="mb-0 fw-semibold"><?= htmlspecialchars($system_info['server_software']) ?></p>
						</div>
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">PHP Version</small>
							<p class="mb-0 fw-semibold"><?= $system_info['php_version'] ?></p>
						</div>
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">Timezone</small>
							<p class="mb-0 fw-semibold"><?= $system_info['timezone'] ?></p>
						</div>
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">Current Time</small>
							<p class="mb-0 fw-semibold" id="current-time"><?= $system_info['current_time'] ?></p>
						</div>
						<div class="col-md-2">
							<small class="text-muted d-block mb-1">Server Uptime</small>
							<p class="mb-0 fw-semibold"><?= $performance['uptime'] ?? 'N/A' ?></p>
						</div>
					</div>
					<div class="row g-3">
						<div class="col-md-3">
							<small class="text-muted d-block mb-1">Total Storage</small>
							<p class="mb-0 fw-semibold"><?= $system_info['total_storage'] ?></p>
						</div>
						<div class="col-md-3">
							<small class="text-muted d-block mb-1">Used Storage</small>
							<p class="mb-0 fw-semibold text-warning"><?= $system_info['used_storage'] ?></p>
						</div>
						<div class="col-md-3">
							<small class="text-muted d-block mb-1">Free Storage</small>
							<p class="mb-0 fw-semibold text-success"><?= $system_info['free_storage'] ?></p>
						</div>
						<div class="col-md-3">
							<small class="text-muted d-block mb-1">Usage</small>
							<div class="progress" style="height: 20px;">
								<div class="progress-bar <?= $system_info['storage_percent'] > 80 ? 'bg-danger' : ($system_info['storage_percent'] > 60 ? 'bg-warning' : 'bg-success') ?>" 
									style="width: <?= $system_info['storage_percent'] ?>%">
									<?= $system_info['storage_percent'] ?>%
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Real-time data storage
let performanceData = {
	labels: [],
	cpu: [],
	memory: []
};

let activityData = {
	labels: [],
	network: [],
	diskRead: [],
	diskWrite: []
};

// Initialize charts
const performanceChart = new Chart(document.getElementById('performanceChart'), {
	type: 'line',
	data: {
		labels: [],
		datasets: [{
			label: 'CPU Usage (%)',
			data: [],
			borderColor: 'rgb(239, 68, 68)',
			backgroundColor: 'rgba(239, 68, 68, 0.1)',
			tension: 0.4,
			fill: true
		}, {
			label: 'Memory Usage (%)',
			data: [],
			borderColor: 'rgb(234, 179, 8)',
			backgroundColor: 'rgba(234, 179, 8, 0.1)',
			tension: 0.4,
			fill: true
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			legend: {
				position: 'top',
			}
		},
		scales: {
			y: {
				beginAtZero: true,
				max: 100
			}
		}
	}
});

const storageChart = new Chart(document.getElementById('storageChart'), {
	type: 'doughnut',
	data: {
		labels: ['Used', 'Available'],
		datasets: [{
			data: [<?= $system_info['storage_percent'] ?>, <?= 100 - $system_info['storage_percent'] ?>],
			backgroundColor: [
				'<?= $system_info['storage_percent'] > 80 ? 'rgb(239, 68, 68)' : ($system_info['storage_percent'] > 60 ? 'rgb(234, 179, 8)' : 'rgb(34, 197, 94)') ?>',
				'rgb(226, 232, 240)'
			],
			borderWidth: 0
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: true,
		plugins: {
			legend: {
				position: 'bottom'
			}
		}
	}
});

const activityChart = new Chart(document.getElementById('activityChart'), {
	type: 'bar',
	data: {
		labels: [],
		datasets: [{
			label: 'Network Connections',
			data: [],
			backgroundColor: 'rgba(59, 130, 246, 0.5)',
			borderColor: 'rgb(59, 130, 246)',
			borderWidth: 1,
			yAxisID: 'y'
		}, {
			label: 'Disk Read (MB/s)',
			data: [],
			backgroundColor: 'rgba(16, 185, 129, 0.5)',
			borderColor: 'rgb(16, 185, 129)',
			borderWidth: 1,
			yAxisID: 'y1'
		}, {
			label: 'Disk Write (MB/s)',
			data: [],
			backgroundColor: 'rgba(139, 92, 246, 0.5)',
			borderColor: 'rgb(139, 92, 246)',
			borderWidth: 1,
			yAxisID: 'y1'
		}]
	},
	options: {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			legend: {
				position: 'top',
			}
		},
		scales: {
			y: {
				type: 'linear',
				display: true,
				position: 'left',
				title: {
					display: true,
					text: 'Connections'
				}
			},
			y1: {
				type: 'linear',
				display: true,
				position: 'right',
				title: {
					display: true,
					text: 'MB/s'
				},
				grid: {
					drawOnChartArea: false,
				}
			}
		}
	}
});

// Fetch real-time metrics
async function fetchMetrics() {
	try {
		const response = await fetch('<?= base_url('dashboard/metrics') ?>');
		const data = await response.json();
		
		// Update stat cards
		document.getElementById('cpu-value').textContent = data.cpu_usage + '%';
		document.getElementById('cpu-bar').style.width = data.cpu_usage + '%';
		document.getElementById('cpu-bar').className = 'progress-bar ' + 
			(data.cpu_usage > 80 ? 'bg-danger' : (data.cpu_usage > 60 ? 'bg-warning' : 'bg-success'));
		
		document.getElementById('memory-value').textContent = data.memory_percent + '%';
		document.getElementById('memory-detail').textContent = data.memory_used + ' / ' + data.memory_total;
		document.getElementById('memory-bar').style.width = data.memory_percent + '%';
		document.getElementById('memory-bar').className = 'progress-bar ' + 
			(data.memory_percent > 80 ? 'bg-danger' : (data.memory_percent > 60 ? 'bg-warning' : 'bg-success'));
		
		document.getElementById('connections-value').textContent = data.active_connections;
		document.getElementById('disk-read').textContent = data.disk_read + ' MB/s';
		document.getElementById('disk-write').textContent = data.disk_write + ' MB/s';
		
		// Update charts
		const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
		
		// Performance chart (keep last 20 data points)
		performanceData.labels.push(now);
		performanceData.cpu.push(data.cpu_usage);
		performanceData.memory.push(data.memory_percent);
		
		if (performanceData.labels.length > 20) {
			performanceData.labels.shift();
			performanceData.cpu.shift();
			performanceData.memory.shift();
		}
		
		performanceChart.data.labels = performanceData.labels;
		performanceChart.data.datasets[0].data = performanceData.cpu;
		performanceChart.data.datasets[1].data = performanceData.memory;
		performanceChart.update('none');
		
		// Activity chart (keep last 15 data points)
		activityData.labels.push(now);
		activityData.network.push(data.active_connections);
		activityData.diskRead.push(parseFloat(data.disk_read));
		activityData.diskWrite.push(parseFloat(data.disk_write));
		
		if (activityData.labels.length > 15) {
			activityData.labels.shift();
			activityData.network.shift();
			activityData.diskRead.shift();
			activityData.diskWrite.shift();
		}
		
		activityChart.data.labels = activityData.labels;
		activityChart.data.datasets[0].data = activityData.network;
		activityChart.data.datasets[1].data = activityData.diskRead;
		activityChart.data.datasets[2].data = activityData.diskWrite;
		activityChart.update('none');
		
	} catch (error) {
		console.error('Error fetching metrics:', error);
	}
}

// Update current time
function updateTime() {
	const now = new Date();
	document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID');
}

// Initial data
fetchMetrics();

// Update every 3 seconds
setInterval(fetchMetrics, 3000);
setInterval(updateTime, 1000);
</script>