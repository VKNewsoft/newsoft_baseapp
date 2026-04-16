<!-- Security Monitor Dashboard -->
<?php helper('html'); ?>

<style>
.security-shell {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}
.security-overview {
	background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #eef4ff 100%);
	border: 1px solid var(--border);
	border-radius: calc(var(--radius) + 4px);
	padding: 1.5rem;
	box-shadow: var(--shadow-sm);
}
.security-overview-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
	gap: 1rem;
	align-items: stretch;
}
.security-title {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
}
.security-title-icon,
.panel-icon,
.stat-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 14px;
}
.security-title-icon {
	width: 56px;
	height: 56px;
	font-size: 1.5rem;
	background: rgba(220, 38, 38, 0.1);
	color: #dc2626;
	flex-shrink: 0;
}
.security-headline {
	font-size: 1.75rem;
	font-weight: 700;
	line-height: 1.1;
	margin-bottom: 0.35rem;
}
.security-points {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 0.75rem;
	margin-top: 1.25rem;
}
.security-point {
	padding: 0.9rem 1rem;
	border-radius: 14px;
	border: 1px solid rgba(148, 163, 184, 0.18);
	background: rgba(255, 255, 255, 0.85);
}
.security-point-label {
	display: block;
	font-size: 0.78rem;
	color: var(--bs-secondary-color, #6b7280);
	margin-bottom: 0.35rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
}
.security-point-value {
	font-size: 1rem;
	font-weight: 700;
	color: var(--bs-body-color, #111827);
}
.status-panel {
	border-radius: 18px;
	border: 1px solid rgba(37, 99, 235, 0.12);
	background: #ffffff;
	box-shadow: var(--shadow-sm);
	padding: 1.25rem;
	height: 100%;
}
.status-panel-top {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 1rem;
	margin-bottom: 1rem;
}
.panel-icon {
	width: 48px;
	height: 48px;
	font-size: 1.2rem;
	background: rgba(37, 99, 235, 0.12);
	color: var(--primary);
}
.status-chip {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.45rem 0.8rem;
	border-radius: 999px;
	background: rgba(34, 197, 94, 0.12);
	color: #15803d;
	font-size: 0.85rem;
	font-weight: 600;
}
.status-chip::before {
	content: "";
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: currentColor;
}
.status-metrics {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 0.75rem;
}
.status-metric {
	padding: 0.85rem 0.95rem;
	border-radius: 14px;
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.16);
}
.status-metric strong {
	display: block;
	font-size: 1.05rem;
	line-height: 1.2;
	margin-top: 0.15rem;
}
.security-card {
	border-radius: calc(var(--radius) + 2px);
	border: 1px solid var(--border);
	transition: var(--transition);
	animation: fadeIn 0.4s ease;
	box-shadow: var(--shadow-sm);
}
.security-card:hover {
	box-shadow: var(--shadow-md);
}
.stat-card {
	min-height: 148px;
}
.stat-icon {
	width: 52px;
	height: 52px;
	font-size: 1.35rem;
}
.stat-trend {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	padding: 0.25rem 0.55rem;
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 600;
	background: var(--bg-secondary);
	color: var(--bs-secondary-color, #64748b);
}
.chart-card {
	min-height: 360px;
}
.card-section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 1rem;
	flex-wrap: wrap;
}
.chart-container {
	position: relative;
	height: 300px;
}
.log-table {
	font-size: 0.92rem;
}
.log-table thead th {
	font-size: 0.76rem;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: var(--bs-secondary-color, #64748b);
	background: #f8fafc;
	border-bottom-width: 1px;
}
.log-table tbody td {
	padding-top: 0.95rem;
	padding-bottom: 0.95rem;
	vertical-align: middle;
}
.attack-log-ip {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.35rem 0.65rem;
	border-radius: 10px;
	background: #fff1f2;
	color: #b91c1c;
	font-family: "Courier New", monospace;
	font-weight: 600;
}
.attack-uri {
	max-width: 100%;
	display: inline-block;
	padding: 0.45rem 0.65rem;
	border-radius: 10px;
	background: #f8fafc;
	color: var(--bs-secondary-color, #64748b);
	line-height: 1.35;
}
.legend-list {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
}
.badge-attack,
.legend-badge {
	padding: 0.38rem 0.72rem;
	font-weight: 600;
	font-size: 0.78rem;
	border-radius: 999px;
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
@media (max-width: 991.98px) {
	.security-overview-grid {
		grid-template-columns: 1fr;
	}
	.security-points {
		grid-template-columns: 1fr;
	}
}
@media (max-width: 767.98px) {
	.security-overview {
		padding: 1.15rem;
	}
	.security-headline {
		font-size: 1.45rem;
	}
	.status-metrics {
		grid-template-columns: 1fr;
	}
	.chart-container {
		height: 260px;
	}
	.log-table {
		min-width: 720px;
	}
}
</style>

<div class="container-fluid">
	<div class="security-shell">
		<div class="security-overview">
			<div class="security-overview-grid">
				<div>
					<div class="security-title">
						<div class="security-title-icon">
							<i class="bi bi-shield-lock"></i>
						</div>
						<div>
							<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
								<span class="badge text-bg-light border text-primary">Security Center</span>
								<span class="status-chip">Protection Active</span>
							</div>
							<h1 class="security-headline">Security Monitor</h1>
							<p class="text-muted mb-0">Pantau ancaman, pola serangan, dan aktivitas pemblokiran dalam satu dashboard yang lebih rapi dan mudah dibaca.</p>
						</div>
					</div>
					<div class="security-points">
						<div class="security-point">
							<span class="security-point-label">Focus</span>
							<div class="security-point-value">Threat visibility</div>
						</div>
						<div class="security-point">
							<span class="security-point-label">Coverage</span>
							<div class="security-point-value">Timeline & attack type</div>
						</div>
						<div class="security-point">
							<span class="security-point-label">Action</span>
							<div class="security-point-value">Blocked IP management</div>
						</div>
					</div>
				</div>
				<div class="status-panel">
					<div class="status-panel-top">
						<div>
							<small class="text-muted d-block mb-1">Quick Action</small>
							<h5 class="mb-1">Blocked IP Control</h5>
							<p class="text-muted mb-0 small">Lanjut ke daftar IP yang diblok untuk review dan unblock manual.</p>
						</div>
						<div class="panel-icon">
							<i class="bi bi-grid-1x2"></i>
						</div>
					</div>
					<div class="status-metrics mb-3">
						<div class="status-metric">
							<small class="text-muted">Total Threats</small>
							<strong><?= number_format($total_attacks) ?></strong>
						</div>
						<div class="status-metric">
							<small class="text-muted">Blocked IPs</small>
							<strong><?= number_format($blocked_count) ?></strong>
						</div>
					</div>
					<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-primary w-100">
						<i class="bi bi-ban me-2"></i>Manage Blocked IPs
					</a>
				</div>
			</div>
		</div>

		<div class="row g-3">
		<!-- Total Attacks -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card stat-card border-0">
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
					<div class="mt-auto d-flex justify-content-between align-items-center gap-2">
						<small class="text-muted">All time detected threats</small>
						<span class="stat-trend text-danger"><i class="bi bi-activity"></i>Monitor</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Today Attacks -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card stat-card border-0">
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
					<div class="mt-auto d-flex justify-content-between align-items-center gap-2">
						<small class="text-muted">Attacks detected today</small>
						<span class="stat-trend text-warning"><i class="bi bi-calendar-event"></i>Today</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Blocked IPs -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card stat-card border-0">
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
					<div class="mt-auto d-flex justify-content-between align-items-center gap-2">
						<small class="text-muted">Currently blocked addresses</small>
						<span class="stat-trend text-primary"><i class="bi bi-shield"></i>Active list</span>
					</div>
				</div>
			</div>
		</div>

		<!-- Security Status -->
		<div class="col-lg-3 col-md-6">
			<div class="card security-card stat-card border-0">
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
					<div class="mt-auto d-flex justify-content-between align-items-center gap-2">
						<small class="text-muted">Security system active</small>
						<span class="stat-trend text-success"><i class="bi bi-check2-circle"></i>Online</span>
					</div>
				</div>
			</div>
		</div>
		</div>

		<div class="row g-3">
		<!-- Attack Timeline -->
		<div class="col-lg-8">
			<div class="card security-card chart-card border-0">
				<div class="card-header bg-white border-bottom">
					<div class="card-section-header">
						<div>
							<h5 class="mb-1"><i class="bi bi-graph-up text-danger me-2"></i>Attack Timeline</h5>
							<small class="text-muted">Ringkasan tren serangan dalam 7 hari terakhir.</small>
						</div>
						<span class="badge text-bg-light border">Last 7 Days</span>
					</div>
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
			<div class="card security-card chart-card border-0">
				<div class="card-header bg-white border-bottom">
					<div class="card-section-header">
						<div>
							<h5 class="mb-1"><i class="bi bi-pie-chart text-warning me-2"></i>Attack Types</h5>
							<small class="text-muted">Distribusi kategori ancaman yang tercatat.</small>
						</div>
					</div>
				</div>
				<div class="card-body d-flex align-items-center justify-content-center">
					<div class="chart-container" style="max-width: 250px; max-height: 250px;">
						<canvas id="typeChart"></canvas>
					</div>
				</div>
			</div>
		</div>
		</div>

		<div class="row g-3">
		<div class="col-12">
			<div class="card security-card border-0">
				<div class="card-header bg-white border-bottom">
					<div class="card-section-header">
						<div>
							<h5 class="mb-1"><i class="bi bi-list-ul text-primary me-2"></i>Recent Attack Logs</h5>
							<small class="text-muted">Daftar aktivitas terbaru untuk inspeksi cepat dan triage awal.</small>
						</div>
						<div class="legend-list">
							<span class="badge legend-badge bg-danger"><i class="bi bi-exclamation-octagon-fill me-1"></i>Critical</span>
							<span class="badge legend-badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>High</span>
							<span class="badge legend-badge bg-orange text-dark"><i class="bi bi-shield-fill-exclamation me-1"></i>Medium</span>
							<span class="badge legend-badge bg-info"><i class="bi bi-info-circle-fill me-1"></i>Low</span>
							<span class="badge legend-badge bg-secondary"><i class="bi bi-robot me-1"></i>Bot/Other</span>
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
									<td>
										<span class="attack-log-ip">
											<i class="bi bi-router"></i>
											<?= esc($log['ip_address']) ?>
										</span>
									</td>
									<td>
										<span class="badge badge-attack <?= $badgeClass ?>">
											<i class="<?= $iconClass ?> me-1"></i>
											<?= esc($log['attack_type']) ?>
										</span>
									</td>
									<td>
										<small class="attack-uri text-break"><?= esc(strlen($log['request_uri']) > 80 ? substr($log['request_uri'], 0, 80) . '...' : $log['request_uri']) ?></small>
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
</div>

<script>
// Chart dimuat setelah konten utama tampil agar statistik dan tabel log
// di atas fold tidak ikut tertahan oleh asset visual non-kritis.
let attackChart, typeChart;

function loadSecurityChartJs() {
	return new Promise(function(resolve, reject) {
		if (window.Chart) {
			resolve();
			return;
		}

		const existing = document.querySelector('script[data-security-chart="1"]');
		if (existing) {
			existing.addEventListener('load', resolve, { once: true });
			existing.addEventListener('error', reject, { once: true });
			return;
		}

		const script = document.createElement('script');
		script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
		script.defer = true;
		script.dataset.securityChart = '1';
		script.onload = resolve;
		script.onerror = reject;
		document.body.appendChild(script);
	});
}

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

function bootSecurityCharts() {
	loadSecurityChartJs()
		.then(function() {
			loadChartData();
		})
		.catch(function() {
			console.error('Error loading chart library');
		});
}

if ('requestIdleCallback' in window) {
	requestIdleCallback(bootSecurityCharts, { timeout: 1200 });
} else if (window.NSModulePerformance) {
	window.NSModulePerformance.defer(bootSecurityCharts);
} else {
	setTimeout(bootSecurityCharts, 200);
}
</script>
