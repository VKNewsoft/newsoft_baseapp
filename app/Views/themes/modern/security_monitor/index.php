<!-- Security Monitor Dashboard -->
<?php helper('html'); ?>

<style>
.security-card {
	border-radius: var(--radius);
	border: 1px solid var(--border);
	transition: var(--transition);
	animation: fadeIn 0.4s ease;
	min-height: 120px;
}
.security-card:hover {
	box-shadow: var(--shadow-md);
	transform: translateY(-2px);
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
.chart-card {
	min-height: 350px;
}
.chart-container {
	position: relative;
	height: 280px;
}
.log-table {
	font-size: 0.9rem;
}
.badge-attack {
	padding: 0.35em 0.65em;
	font-weight: 500;
	font-size: 0.8rem;
}
.bg-orange {
	background-color: #fb923c !important;
}
.badge-attack i {
	font-size: 0.75rem;
}
.badge.bg-danger {
	background-color: #dc2626 !important;
	color: white !important;
}
.badge.bg-warning {
	background-color: #fbbf24 !important;
	color: #1f2937 !important;
}
.badge.bg-info {
	background-color: #3b82f6 !important;
	color: white !important;
}
.badge.bg-secondary {
	background-color: #6b7280 !important;
	color: white !important;
}
.pagination {
	margin-bottom: 0;
	gap: 4px;
}
.pagination .page-link {
	border-radius: 8px;
	border: 1px solid var(--border);
	color: var(--primary);
	padding: 0.5rem 0.9rem;
	font-weight: 500;
	transition: all 0.2s ease;
	min-width: 40px;
	text-align: center;
}
.pagination .page-link:hover {
	background-color: var(--bg-secondary);
	border-color: var(--primary);
	transform: translateY(-1px);
	box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
}
.pagination .page-item.active .page-link {
	background-color: var(--primary);
	border-color: var(--primary);
	color: white;
	font-weight: 600;
	box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
}
.pagination .page-item.disabled .page-link {
	color: var(--text-secondary);
	background-color: var(--bg-tertiary);
	border-color: var(--border);
	opacity: 0.5;
	cursor: not-allowed;
}
.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
	font-weight: 600;
}
.pagination .page-link i {
	font-size: 0.85rem;
}
</style>

<div class="container-fluid">
	<!-- Header -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h4 class="mb-1"><i class="bi bi-shield-lock text-danger me-2"></i>Security Monitor</h4>
			<p class="text-muted mb-0">Real-time security threats monitoring</p>
		</div>
		<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-primary">
			<i class="bi bi-ban me-2"></i>Manage Blocked IPs
		</a>
	</div>

	<!-- Stats Cards -->
	<div class="row g-3 mb-3">
		<!-- Total Attacks -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Total Attacks</p>
							<h2 class="mb-0 fw-bold text-danger" id="total-attacks"><?= number_format($total_attacks) ?></h2>
						</div>
						<div class="stat-icon bg-danger bg-opacity-10 text-danger">
							<i class="bi bi-exclamation-triangle"></i>
						</div>
					</div>
					<small class="text-muted mt-auto">All time detected threats</small>
				</div>
			</div>
		</div>

		<!-- Today Attacks -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Today's Attacks</p>
							<h2 class="mb-0 fw-bold text-warning" id="today-attacks"><?= number_format($today_attacks) ?></h2>
						</div>
						<div class="stat-icon bg-warning bg-opacity-10 text-warning">
							<i class="bi bi-calendar-check"></i>
						</div>
					</div>
					<small class="text-muted mt-auto">Attacks detected today</small>
				</div>
			</div>
		</div>

		<!-- Blocked IPs -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Blocked IPs</p>
							<h2 class="mb-0 fw-bold text-primary" id="blocked-count"><?= number_format($blocked_count) ?></h2>
						</div>
						<div class="stat-icon bg-primary bg-opacity-10 text-primary">
							<i class="bi bi-shield-slash"></i>
						</div>
					</div>
					<small class="text-muted mt-auto">Currently blocked addresses</small>
				</div>
			</div>
		</div>

		<!-- Security Status -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card border-0 shadow-sm">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Status</p>
							<h5 class="mb-0 fw-bold text-success">
								<i class="bi bi-check-circle-fill me-1"></i>Protected
							</h5>
						</div>
						<div class="stat-icon bg-success bg-opacity-10 text-success">
							<i class="bi bi-shield-check"></i>
						</div>
					</div>
					<small class="text-muted mt-auto">Security system active</small>
				</div>
			</div>
		</div>
	</div>

	<!-- Charts Row -->
	<div class="row g-3 mb-3">
		<!-- Attack Timeline -->
		<div class="col-lg-8">
			<div class="card security-card chart-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-graph-up text-danger me-2"></i>Attack Timeline (7 Days)</h5>
				</div>
				<div class="card-body">
					<div class="chart-container">
						<canvas id="attackChart"></canvas>
					</div>
				</div>
			</div>
		</div>

		<!-- Attack Types -->
		<div class="col-lg-4">
			<div class="card security-card chart-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<h5 class="mb-0"><i class="bi bi-pie-chart text-warning me-2"></i>Attack Types</h5>
				</div>
				<div class="card-body d-flex align-items-center justify-content-center">
					<div class="chart-container" style="max-width: 250px; max-height: 250px;">
						<canvas id="typeChart"></canvas>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Recent Logs Table -->
	<div class="row g-3">
		<div class="col-12">
			<div class="card security-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
						<div>
							<h5 class="mb-0"><i class="bi bi-list-ul text-primary me-2"></i>Recent Attack Logs</h5>
						</div>
						<div class="d-flex gap-2 flex-wrap">
							<span class="badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i>Critical</span>
							<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>High</span>
							<span class="badge bg-orange text-dark"><i class="bi bi-shield-fill-exclamation me-1"></i>Medium</span>
							<span class="badge bg-info"><i class="bi bi-info-circle-fill me-1"></i>Low</span>
							<span class="badge bg-secondary"><i class="bi bi-robot me-1"></i>Bot/Other</span>
						</div>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-hover log-table mb-0">
							<thead class="table-light">
								<tr>
									<th style="width: 140px;">IP Address</th>
									<th style="width: 150px;">Attack Type</th>
									<th>Request URI</th>
									<th style="width: 180px;">Timestamp</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($recent_logs)): ?>
								<tr>
									<td colspan="4" class="text-center py-4 text-muted">
										<i class="bi bi-inbox fs-3 d-block mb-2"></i>
										No attack logs found
									</td>
								</tr>
								<?php else: ?>
								<?php foreach ($recent_logs as $log): 
									// Determine severity based on attack type
									$attackType = strtolower($log['attack_type']);
									$badgeClass = 'bg-secondary';
									$iconClass = 'bi-shield-exclamation';
									
									// Critical severity (SQL Injection, Command Injection, RCE)
									if (strpos($attackType, 'sql') !== false || 
										strpos($attackType, 'injection') !== false || 
										strpos($attackType, 'rce') !== false ||
										strpos($attackType, 'command') !== false) {
										$badgeClass = 'bg-danger';
										$iconClass = 'bi-exclamation-octagon-fill';
									}
									// High severity (XSS, Path Traversal, File Upload)
									elseif (strpos($attackType, 'xss') !== false || 
											strpos($attackType, 'script') !== false ||
											strpos($attackType, 'traversal') !== false ||
											strpos($attackType, 'upload') !== false ||
											strpos($attackType, 'path') !== false) {
										$badgeClass = 'bg-warning text-dark';
										$iconClass = 'bi-exclamation-triangle-fill';
									}
									// Medium severity (CSRF, Auth issues)
									elseif (strpos($attackType, 'csrf') !== false || 
											strpos($attackType, 'auth') !== false ||
											strpos($attackType, 'session') !== false) {
										$badgeClass = 'bg-orange text-dark';
										$iconClass = 'bi-shield-fill-exclamation';
									}
									// Low severity (Suspicious patterns, Probe)
									elseif (strpos($attackType, 'suspicious') !== false || 
											strpos($attackType, 'probe') !== false ||
											strpos($attackType, 'scan') !== false) {
										$badgeClass = 'bg-info text-dark';
										$iconClass = 'bi-info-circle-fill';
									}
									// Bot/Spam
									elseif (strpos($attackType, 'bot') !== false || 
											strpos($attackType, 'spam') !== false) {
										$badgeClass = 'bg-secondary';
										$iconClass = 'bi-robot';
									}
								?>
								<tr>
									<td><code class="text-danger"><?= esc($log['ip_address']) ?></code></td>
									<td>
										<span class="badge badge-attack <?= $badgeClass ?>">
											<i class="<?= $iconClass ?> me-1"></i>
											<?= esc($log['attack_type']) ?>
										</span>
									</td>
									<td>
										<small class="text-muted text-break"><?= esc(strlen($log['request_uri']) > 80 ? substr($log['request_uri'], 0, 80) . '...' : $log['request_uri']) ?></small>
									</td>
									<td><small><?= date('d M Y, H:i:s', strtotime($log['created_at'])) ?></small></td>
								</tr>
								<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php if (isset($pager) && $pager->getPageCount() > 1): ?>
				<div class="card-footer bg-white border-top py-3">
					<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
						<div class="text-muted">
							<i class="bi bi-file-text me-1"></i>
							<small>Page <strong><?= $pager->getCurrentPage() ?></strong> of <strong><?= $pager->getPageCount() ?></strong></small>
							<span class="mx-2">•</span>
							<small>Total <strong><?= $pager->getTotal() ?></strong> entries</small>
						</div>
						<nav aria-label="Page navigation">
							<?= $pager->links('default', 'default_full') ?>
						</nav>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Lazy load chart data via AJAX
let attackChart, typeChart;

async function loadChartData() {
	try {
		const response = await fetch('<?= base_url('securitymonitor/chartData') ?>');
		const data = await response.json();
		
		// Attack Timeline Chart
		const ctx1 = document.getElementById('attackChart').getContext('2d');
		attackChart = new Chart(ctx1, {
			type: 'line',
			data: {
				labels: data.timeline.labels,
				datasets: [{
					label: 'Number of Attacks',
					data: data.timeline.data,
					borderColor: 'rgb(239, 68, 68)',
					backgroundColor: 'rgba(239, 68, 68, 0.1)',
					tension: 0.4,
					fill: true,
					borderWidth: 2
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: false
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							precision: 0
						}
					}
				}
			}
		});

		// Attack Types Chart
		const ctx2 = document.getElementById('typeChart').getContext('2d');
		typeChart = new Chart(ctx2, {
			type: 'doughnut',
			data: {
				labels: data.types.labels,
				datasets: [{
					data: data.types.data,
					backgroundColor: [
						'rgb(239, 68, 68)',
						'rgb(234, 179, 8)',
						'rgb(59, 130, 246)',
						'rgb(124, 45, 18)',
						'rgb(22, 163, 74)'
					],
					borderWidth: 0
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: true,
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 15,
							font: {
								size: 11
							}
						}
					}
				}
			}
		});
	} catch (error) {
		console.error('Error loading chart data:', error);
	}
}

// Load charts after page load
document.addEventListener('DOMContentLoaded', function() {
	loadChartData();
});
</script>