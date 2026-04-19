<!-- Security Monitor Dashboard -->
<?php helper('html'); ?>
<?php
$severityMeta = [
	'critical' => ['badge' => 'bg-danger', 'icon' => 'bi-exclamation-octagon-fill'],
	'high' => ['badge' => 'bg-warning text-dark', 'icon' => 'bi-exclamation-triangle-fill'],
	'medium' => ['badge' => 'bg-orange text-dark', 'icon' => 'bi-shield-fill-exclamation'],
	'low' => ['badge' => 'bg-info text-dark', 'icon' => 'bi-info-circle-fill'],
];
?>

<style>
.security-page {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}
.security-hero {
	background: linear-gradient(135deg, #f8fbff 0%, #ffffff 58%, #eef4ff 100%);
	border: 1px solid var(--border);
	border-radius: calc(var(--radius) + 4px);
	padding: 1.5rem;
	box-shadow: var(--shadow-sm);
}
.security-hero-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.7fr) minmax(300px, 0.95fr);
	gap: 1rem;
}
.security-hero-title {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
}
.security-hero-icon,
.hero-panel-icon,
.metric-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 16px;
}
.security-hero-icon {
	width: 58px;
	height: 58px;
	font-size: 1.6rem;
	background: rgba(220, 38, 38, 0.12);
	color: #dc2626;
	flex-shrink: 0;
}
.security-page-title {
	font-size: 1.8rem;
	font-weight: 700;
	line-height: 1.1;
	margin-bottom: 0.35rem;
}
.security-hero-points {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 0.75rem;
	margin-top: 1.2rem;
}
.hero-point {
	background: rgba(255, 255, 255, 0.9);
	border: 1px solid rgba(148, 163, 184, 0.18);
	border-radius: 14px;
	padding: 0.9rem 1rem;
}
.hero-point small {
	display: block;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: var(--bs-secondary-color, #64748b);
	margin-bottom: 0.3rem;
}
.hero-point strong {
	font-size: 1rem;
}
.hero-side {
	background: #ffffff;
	border: 1px solid rgba(37, 99, 235, 0.12);
	border-radius: 18px;
	padding: 1.25rem;
	box-shadow: var(--shadow-sm);
}
.hero-side-top {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 0.75rem;
	margin-bottom: 1rem;
}
.hero-panel-icon {
	width: 48px;
	height: 48px;
	background: rgba(37, 99, 235, 0.12);
	color: var(--primary);
	font-size: 1.2rem;
}
.live-status {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.45rem 0.75rem;
	border-radius: 999px;
	background: rgba(34, 197, 94, 0.12);
	color: #15803d;
	font-size: 0.82rem;
	font-weight: 600;
}
.live-status::before {
	content: "";
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: currentColor;
}
.hero-side-metrics {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 0.75rem;
	margin-bottom: 1rem;
}
.hero-side-metric {
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.14);
	border-radius: 14px;
	padding: 0.85rem 0.95rem;
}
.hero-side-metric strong {
	display: block;
	font-size: 1.1rem;
	line-height: 1.2;
	margin-top: 0.2rem;
}
.monitor-card {
	border-radius: calc(var(--radius) + 2px);
	border: 1px solid var(--border);
	box-shadow: var(--shadow-sm);
	animation: fadeIn 0.35s ease;
}
.monitor-card:hover {
	box-shadow: var(--shadow-md);
}
.metric-card {
	min-height: 152px;
}
.metric-icon {
	width: 52px;
	height: 52px;
	font-size: 1.35rem;
}
.metric-chip {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	padding: 0.3rem 0.6rem;
	border-radius: 999px;
	font-size: 0.75rem;
	font-weight: 600;
	background: var(--bg-secondary);
	color: var(--bs-secondary-color, #64748b);
}
.monitor-section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 1rem;
	flex-wrap: wrap;
}
.filter-panel {
	padding: 1rem 1.1rem;
	background: #f8fafc;
	border-radius: 16px;
	border: 1px solid rgba(148, 163, 184, 0.16);
}
.alert-list {
	display: grid;
	gap: 0.75rem;
}
.alert-item {
	display: flex;
	align-items: flex-start;
	gap: 0.85rem;
	padding: 0.9rem 1rem;
	border: 1px solid rgba(148, 163, 184, 0.14);
	border-radius: 14px;
	background: #fff;
}
.alert-icon {
	width: 42px;
	height: 42px;
	border-radius: 12px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	flex-shrink: 0;
}
.mini-list {
	display: grid;
	gap: 0.7rem;
}
.mini-list-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	gap: 1rem;
	padding: 0.8rem 0.9rem;
	border-radius: 12px;
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.12);
}
.mini-list-ip {
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
.chart-box {
	position: relative;
	height: 300px;
}
.chart-box.chart-sm {
	height: 260px;
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
.dataTables_wrapper .dataTables_processing {
	background: #ffffff;
	border: 1px solid var(--border);
	border-radius: 12px;
	box-shadow: var(--shadow-sm);
	padding: 0.75rem 1rem;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
	padding: 0 !important;
	margin: 0 0.1rem !important;
	border: 0 !important;
	background: transparent !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button .page-link {
	border-radius: 8px;
}
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate {
	padding: 1rem !important;
}
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
	display: none;
}
.event-ip {
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
.event-uri {
	display: inline-block;
	max-width: 100%;
	padding: 0.45rem 0.65rem;
	border-radius: 10px;
	background: #f8fafc;
	color: var(--bs-secondary-color, #64748b);
	line-height: 1.35;
}
.detail-grid {
	display: grid;
	grid-template-columns: repeat(2, minmax(0, 1fr));
	gap: 0.9rem;
}
.detail-box {
	padding: 0.9rem 1rem;
	border-radius: 14px;
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.15);
}
.detail-box pre {
	white-space: pre-wrap;
	word-break: break-word;
	margin-bottom: 0;
	font-size: 0.85rem;
}
.bg-orange {
	background-color: #fb923c !important;
}
@media (max-width: 991.98px) {
	.security-hero-grid,
	.security-hero-points,
	.hero-side-metrics,
	.detail-grid {
		grid-template-columns: 1fr;
	}
}
@media (max-width: 767.98px) {
	.security-hero {
		padding: 1.15rem;
	}
	.security-page-title {
		font-size: 1.45rem;
	}
	.chart-box {
		height: 250px;
	}
	.log-table {
		min-width: 1100px;
	}
}
</style>

<div class="container-fluid">
	<div class="security-page">
		<div class="security-hero">
			<div class="security-hero-grid">
				<div>
					<div class="security-hero-title">
						<div class="security-hero-icon">
							<i class="bi bi-shield-lock"></i>
						</div>
						<div>
							<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
								<span class="badge text-bg-light border text-primary">Security Center</span>
								<span class="live-status">Protection Active</span>
								<?php if ($filter_active): ?>
								<span class="badge bg-info">Filtered View</span>
								<?php endif; ?>
							</div>
							<h1 class="security-page-title">Security Monitor</h1>
							<p class="text-muted mb-0">Deteksi, logging, alert, dan kontrol proteksi dalam satu dashboard monitoring yang lebih informatif untuk analisis cepat.</p>
						</div>
					</div>
					<div class="security-hero-points">
						<div class="hero-point">
							<small>Detection</small>
							<strong>SQLi, XSS, CSRF, brute force, suspicious request</strong>
						</div>
						<div class="hero-point">
							<small>Logging</small>
							<strong>IP, user, endpoint, payload ringkas, status event</strong>
						</div>
						<div class="hero-point">
							<small>Action</small>
							<strong>Alert triage, detail event, blocked IP management</strong>
						</div>
					</div>
				</div>
				<div class="hero-side">
					<div class="hero-side-top">
						<div>
							<small class="text-muted d-block mb-1">Quick Command Center</small>
							<h5 class="mb-1">Realtime Security Posture</h5>
							<p class="text-muted mb-0 small">Ringkasan status event dan akses cepat ke daftar IP yang sudah diblok.</p>
						</div>
						<div class="hero-panel-icon">
							<i class="bi bi-shield-check"></i>
						</div>
					</div>
					<div class="hero-side-metrics">
						<div class="hero-side-metric">
							<small class="text-muted">Blocked Events</small>
							<strong><?= number_format($blocked_events) ?></strong>
						</div>
						<div class="hero-side-metric">
							<small class="text-muted">Allowed Events</small>
							<strong><?= number_format($allowed_events) ?></strong>
						</div>
					</div>
					<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-primary w-100">
						<i class="bi bi-ban me-2"></i>Manage Blocked IPs
					</a>
				</div>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-lg-3 col-md-6">
				<div class="card monitor-card metric-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Total Attack Events</p>
								<h2 class="mb-0 fw-bold text-danger"><?= number_format($total_attacks) ?></h2>
							</div>
							<div class="metric-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-exclamation-triangle"></i></div>
						</div>
						<div class="mt-auto"><span class="metric-chip text-danger"><i class="bi bi-activity"></i>All recorded threats</span></div>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="card monitor-card metric-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Today</p>
								<h2 class="mb-0 fw-bold text-warning"><?= number_format($today_attacks) ?></h2>
							</div>
							<div class="metric-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-week"></i></div>
						</div>
						<div class="mt-auto"><span class="metric-chip text-warning"><i class="bi bi-clock-history"></i>Events in current day</span></div>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="card monitor-card metric-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Blocked IPs</p>
								<h2 class="mb-0 fw-bold text-primary"><?= number_format($blocked_count) ?></h2>
							</div>
							<div class="metric-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-shield-slash"></i></div>
						</div>
						<div class="mt-auto"><span class="metric-chip text-primary"><i class="bi bi-router"></i>Current protection list</span></div>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-6">
				<div class="card monitor-card metric-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Priority Indicators</p>
								<h2 class="mb-0 fw-bold text-success"><?= number_format($brute_force_count + $csrf_count) ?></h2>
							</div>
							<div class="metric-icon bg-success bg-opacity-10 text-success"><i class="bi bi-shield-check"></i></div>
						</div>
						<div class="mt-auto"><span class="metric-chip text-success"><i class="bi bi-person-lock"></i>Brute force + CSRF tracked</span></div>
					</div>
				</div>
			</div>
		</div>

		<div class="card monitor-card border-0">
			<div class="card-body">
				<form method="GET" action="<?= base_url('securitymonitor') ?>" class="filter-panel">
					<div class="monitor-section-header mb-3">
						<div>
							<h5 class="mb-1"><i class="bi bi-funnel text-primary me-2"></i>Filter Monitoring Event</h5>
							<small class="text-muted">Gunakan filter untuk memperjelas timeline, chart, dan tabel event.</small>
						</div>
						<?php if ($filter_active): ?>
						<a href="<?= base_url('securitymonitor') ?>" class="btn btn-outline-secondary btn-sm">
							<i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
						</a>
						<?php endif; ?>
					</div>
					<div class="row g-3 align-items-end">
						<div class="col-lg-3 col-md-6">
							<label class="form-label small text-muted mb-1">IP Address</label>
							<input type="text" name="ip" class="form-control" value="<?= esc($filters['ip']) ?>" placeholder="Search IP">
						</div>
						<div class="col-lg-2 col-md-6">
							<label class="form-label small text-muted mb-1">Attack Type</label>
							<select name="attack_type" class="form-select">
								<option value="">All Types</option>
								<?php foreach ($attack_types as $attackType): ?>
								<option value="<?= esc($attackType) ?>" <?= $filters['attack_type'] === $attackType ? 'selected' : '' ?>><?= esc($attackType) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="col-lg-2 col-md-6">
							<label class="form-label small text-muted mb-1">Status</label>
							<select name="status" class="form-select">
								<option value="">All Status</option>
								<option value="blocked" <?= $filters['status'] === 'blocked' ? 'selected' : '' ?>>Blocked</option>
								<option value="allowed" <?= $filters['status'] === 'allowed' ? 'selected' : '' ?>>Allowed</option>
							</select>
						</div>
						<div class="col-lg-2 col-md-6">
							<label class="form-label small text-muted mb-1">Date From</label>
							<input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
						</div>
						<div class="col-lg-2 col-md-6">
							<label class="form-label small text-muted mb-1">Date To</label>
							<input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
						</div>
						<div class="col-lg-1 col-md-6">
							<button type="submit" class="btn btn-primary w-100">
								<i class="bi bi-search"></i>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-lg-7">
				<div class="card monitor-card border-0 h-100">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-bell text-danger me-2"></i>Suspicious Activity Alert</h5>
								<small class="text-muted">Highlight aktivitas dengan volume tinggi, severity tinggi, atau sudah masuk status blocked.</small>
							</div>
							<span class="badge text-bg-light border"><?= count($alerts) ?> alert</span>
						</div>
					</div>
					<div class="card-body">
						<div class="alert-list">
							<?php if (!$alerts): ?>
							<div class="text-center text-muted py-4">
								<i class="bi bi-check2-circle fs-2 d-block mb-2"></i>
								No alert matched current filter.
							</div>
							<?php else: ?>
							<?php foreach ($alerts as $alert): ?>
							<?php $meta = $severityMeta[$alert['severity']] ?? $severityMeta['low']; ?>
							<div class="alert-item">
								<div class="alert-icon <?= $meta['badge'] ?> bg-opacity-10">
									<i class="bi <?= $meta['icon'] ?>"></i>
								</div>
								<div class="flex-grow-1">
									<div class="d-flex justify-content-between gap-2 flex-wrap mb-1">
										<strong><?= esc($alert['attack_type']) ?></strong>
										<span class="badge <?= $meta['badge'] ?>"><?= strtoupper($alert['severity']) ?></span>
									</div>
									<p class="mb-1 small text-muted">IP <?= esc($alert['ip_address']) ?> memicu <?= (int) $alert['total'] ?> event. Last seen <?= date('d M Y H:i', strtotime($alert['last_seen'])) ?>.</p>
									<small class="text-muted"><?= !empty($alert['blocked_until']) ? 'Proteksi otomatis aktif untuk IP ini.' : 'Perlu review lanjutan jika pola berlanjut.' ?></small>
								</div>
							</div>
							<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-5">
				<div class="card monitor-card border-0 h-100">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-diagram-3 text-primary me-2"></i>Top IP Attacker</h5>
								<small class="text-muted">IP dengan volume event tertinggi pada scope filter saat ini.</small>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="mini-list">
							<?php if (!$top_attackers): ?>
							<div class="text-center text-muted py-4">
								<i class="bi bi-inbox fs-2 d-block mb-2"></i>
								No attacker data.
							</div>
							<?php else: ?>
							<?php foreach ($top_attackers as $attacker): ?>
							<div class="mini-list-item">
								<div>
									<div class="mini-list-ip"><i class="bi bi-router"></i><?= esc($attacker['ip_address']) ?></div>
									<small class="text-muted d-block mt-2">Last seen <?= date('d M Y H:i', strtotime($attacker['last_seen'])) ?></small>
								</div>
								<div class="text-end">
									<div class="fw-bold"><?= number_format($attacker['total']) ?></div>
									<small class="text-muted">events</small>
								</div>
							</div>
							<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-lg-8">
				<div class="card monitor-card border-0">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-graph-up text-danger me-2"></i>Attack Timeline</h5>
								<small class="text-muted">Trend event keamanan 7 hari terakhir mengikuti filter aktif.</small>
							</div>
							<span class="badge text-bg-light border">Last 7 Days</span>
						</div>
					</div>
					<div class="card-body">
						<div class="chart-box">
							<canvas id="attackChart"></canvas>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="card monitor-card border-0">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-pie-chart text-warning me-2"></i>Attack Types</h5>
								<small class="text-muted">Distribusi jenis serangan terbanyak.</small>
							</div>
						</div>
					</div>
					<div class="card-body d-flex align-items-center justify-content-center">
						<div class="chart-box chart-sm" style="max-width:260px; max-height:260px;">
							<canvas id="typeChart"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-lg-5">
				<div class="card monitor-card border-0 h-100">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-bar-chart text-primary me-2"></i>Top Attacker Volume</h5>
								<small class="text-muted">Perbandingan volume event antar IP attacker utama.</small>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="chart-box chart-sm">
							<canvas id="attackerChart"></canvas>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-7">
				<div class="card monitor-card border-0 h-100">
					<div class="card-header bg-white border-bottom">
						<div class="monitor-section-header">
							<div>
								<h5 class="mb-1"><i class="bi bi-clock-history text-info me-2"></i>Last 24h Activity</h5>
								<small class="text-muted">Distribusi event per jam untuk membaca spike aktivitas mencurigakan.</small>
							</div>
						</div>
					</div>
					<div class="card-body">
						<div class="chart-box chart-sm">
							<canvas id="hourlyChart"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="card monitor-card border-0">
			<div class="card-header bg-white border-bottom">
				<div class="monitor-section-header">
					<div>
						<h5 class="mb-1"><i class="bi bi-list-ul text-primary me-2"></i>Event Log Detail</h5>
						<small class="text-muted">Tabel event detail dengan user, endpoint, payload ringkas, status, dan akses detail per event.</small>
					</div>
					<div class="d-flex flex-wrap gap-2">
						<span class="badge bg-danger">Blocked: <?= number_format($blocked_events) ?></span>
						<span class="badge bg-success">Allowed: <?= number_format($allowed_events) ?></span>
					</div>
				</div>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table id="security-log-table" class="table table-hover log-table mb-0 w-100">
						<thead>
							<tr>
								<th style="width:160px;">Timestamp</th>
								<th style="width:145px;">IP</th>
								<th style="width:150px;">Attack</th>
								<th style="width:95px;">Status</th>
								<th style="width:220px;">Endpoint</th>
								<th style="width:220px;">Payload</th>
								<th style="width:170px;">User</th>
								<th style="width:90px;" class="text-center">Detail</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div class="card-footer bg-white border-top py-0">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 px-3 py-2">
					<div class="text-muted">
						<small>Initial batch <strong id="security-loaded-count"><?= number_format($loaded_rows) ?></strong> rows</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="eventDetailModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header bg-white">
				<h5 class="modal-title"><i class="bi bi-shield-exclamation me-2"></i>Security Event Detail</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div id="event-detail-loading" class="text-center py-5 text-muted">
					<div class="spinner-border spinner-border-sm me-2"></div>Loading event detail...
				</div>
				<div id="event-detail-content" class="d-none">
					<div class="detail-grid">
						<div class="detail-box"><small class="text-muted d-block mb-1">Attack Type</small><strong id="detail-attack-type">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Status</small><strong id="detail-status">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">IP Address</small><strong id="detail-ip">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Timestamp</small><strong id="detail-time">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">User</small><strong id="detail-user">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Method / Endpoint</small><strong id="detail-endpoint">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Request Source</small><strong id="detail-source">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Request Count</small><strong id="detail-count">-</strong></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Payload Summary</small><pre id="detail-payload">-</pre></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">User Agent</small><pre id="detail-ua">-</pre></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Reason</small><pre id="detail-reason">-</pre></div>
						<div class="detail-box"><small class="text-muted d-block mb-1">Threshold Info</small><pre id="detail-threshold">-</pre></div>
					</div>
				</div>
				<div id="event-detail-error" class="d-none text-center py-5 text-danger">
					<i class="bi bi-exclamation-circle fs-2 d-block mb-2"></i>
					Failed to load event detail.
				</div>
			</div>
		</div>
	</div>
</div>

<script>
let attackChart;
let typeChart;
let attackerChart;
let hourlyChart;
let eventDetailModal;
let securityLogTable;

function escapeHtml(value) {
	return $('<div>').text(value || '').html();
}

function buildChartUrl() {
	const params = new URLSearchParams(window.location.search);
	params.delete('page');
	const query = params.toString();
	return '<?= base_url('securitymonitor/chartData') ?>' + (query ? ('?' + query) : '');
}

function buildLogTableUrl() {
	const params = new URLSearchParams(window.location.search);
	const query = params.toString();
	return '<?= base_url('securitymonitor/logsData') ?>' + (query ? ('?' + query) : '');
}

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
	const response = await fetch(buildChartUrl(), {
		headers: {
			'X-Requested-With': 'XMLHttpRequest'
		}
	});
	return response.json();
}

function renderCharts(data) {
	const commonGrid = {
		color: 'rgba(148, 163, 184, 0.18)'
	};

	attackChart = new Chart(document.getElementById('attackChart').getContext('2d'), {
		type: 'line',
		data: {
			labels: data.timeline.labels,
			datasets: [{
				label: 'Attack Events',
				data: data.timeline.data,
				borderColor: 'rgb(239, 68, 68)',
				backgroundColor: 'rgba(239, 68, 68, 0.12)',
				fill: true,
				tension: 0.35,
				borderWidth: 2
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false }
			},
			scales: {
				y: {
					beginAtZero: true,
					grid: commonGrid,
					ticks: { precision: 0 }
				},
				x: {
					grid: { display: false }
				}
			}
		}
	});

	typeChart = new Chart(document.getElementById('typeChart').getContext('2d'), {
		type: 'doughnut',
		data: {
			labels: data.types.labels,
			datasets: [{
				data: data.types.data,
				backgroundColor: [
					'rgb(239, 68, 68)',
					'rgb(234, 179, 8)',
					'rgb(59, 130, 246)',
					'rgb(251, 146, 60)',
					'rgb(16, 185, 129)',
					'rgb(107, 114, 128)'
				],
				borderWidth: 0
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: {
					position: 'bottom',
					labels: {
						padding: 14,
						font: { size: 11 }
					}
				}
			}
		}
	});

	attackerChart = new Chart(document.getElementById('attackerChart').getContext('2d'), {
		type: 'bar',
		data: {
			labels: data.attackers.labels,
			datasets: [{
				label: 'Events',
				data: data.attackers.data,
				backgroundColor: 'rgba(37, 99, 235, 0.75)',
				borderRadius: 8,
				maxBarThickness: 38
			}]
		},
		options: {
			indexAxis: 'y',
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false }
			},
			scales: {
				x: {
					beginAtZero: true,
					grid: commonGrid,
					ticks: { precision: 0 }
				},
				y: {
					grid: { display: false }
				}
			}
		}
	});

	hourlyChart = new Chart(document.getElementById('hourlyChart').getContext('2d'), {
		type: 'bar',
		data: {
			labels: data.hourly.labels,
			datasets: [{
				label: 'Events / hour',
				data: data.hourly.data,
				backgroundColor: 'rgba(14, 165, 233, 0.72)',
				borderRadius: 6,
				maxBarThickness: 18
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false }
			},
			scales: {
				y: {
					beginAtZero: true,
					grid: commonGrid,
					ticks: { precision: 0 }
				},
				x: {
					grid: { display: false }
				}
			}
		}
	});
}

function setDetailLoadingState(state) {
	document.getElementById('event-detail-loading').classList.toggle('d-none', state !== 'loading');
	document.getElementById('event-detail-content').classList.toggle('d-none', state !== 'content');
	document.getElementById('event-detail-error').classList.toggle('d-none', state !== 'error');
}

function setText(id, value) {
	document.getElementById(id).textContent = value && value !== '' ? value : '-';
}

async function openEventDetail(id) {
	setDetailLoadingState('loading');
	eventDetailModal.show();

	try {
		const response = await fetch('<?= base_url('securitymonitor/eventDetail') ?>/' + id, {
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			}
		});
		const result = await response.json();
		if (!result.success) {
			throw new Error(result.message || 'Failed');
		}

		const data = result.data;
		setText('detail-attack-type', data.attack_type);
		setText('detail-status', data.event_status.toUpperCase());
		setText('detail-ip', data.ip_address);
		setText('detail-time', data.created_at);
		setText('detail-user', data.user_label);
		setText('detail-endpoint', data.request_method + ' ' + data.endpoint);
		setText('detail-source', data.request_source || 'web_request');
		setText('detail-count', String(data.count_in_window || 1));
		setText('detail-payload', data.payload_summary || '-');
		setText('detail-ua', data.user_agent_label || '-');
		setText('detail-reason', data.request_reason || '-');
		setText('detail-threshold', (data.threshold_limit ? ('Threshold: ' + data.threshold_limit) : 'Threshold: -') + ' | Count window: ' + (data.count_in_window || 1));
		setDetailLoadingState('content');
	} catch (error) {
		setDetailLoadingState('error');
	}
}

function bindEventDetailButtons() {
	$('#security-log-table').on('click', '.event-detail-btn', function() {
		openEventDetail($(this).data('id'));
	});
}

function buildSeverityBadge(type, severity) {
	const severityMap = {
		critical: { badge: 'bg-danger', icon: 'bi-exclamation-octagon-fill' },
		high: { badge: 'bg-warning text-dark', icon: 'bi-exclamation-triangle-fill' },
		medium: { badge: 'bg-orange text-dark', icon: 'bi-shield-fill-exclamation' },
		low: { badge: 'bg-info text-dark', icon: 'bi-info-circle-fill' }
	};
	const meta = severityMap[severity] || severityMap.low;
	return '<span class="badge ' + meta.badge + '"><i class="bi ' + meta.icon + ' me-1"></i>' + escapeHtml(type) + '</span>';
}

function initSecurityLogTable() {
	const $table = $('#security-log-table');
	if (!$table.length || !$.fn.DataTable) {
		return;
	}

	securityLogTable = $table.DataTable({
		processing: true,
		serverSide: true,
		searching: false,
		lengthChange: false,
		pageLength: 15,
		deferRender: true,
		scrollX: true,
		order: [[0, 'desc']],
		ajax: {
			url: buildLogTableUrl(),
			type: 'POST',
			dataSrc: function(json) {
				$('#security-loaded-count').text(json.loaded_count || 0);
				return json.data || [];
			}
		},
		columns: [
			{ data: 'created_at' },
			{
				data: 'ip_address',
				render: function(data) {
					return '<span class="event-ip"><i class="bi bi-router"></i>' + escapeHtml(data) + '</span>';
				}
			},
			{
				data: null,
				orderable: false,
				render: function(data, type, row) {
					return buildSeverityBadge(row.attack_type, row.severity);
				}
			},
			{
				data: 'event_status',
				render: function(data) {
					const badgeClass = data === 'blocked' ? 'bg-danger' : 'bg-success';
					return '<span class="badge ' + badgeClass + '">' + escapeHtml(String(data).toUpperCase()) + '</span>';
				}
			},
			{
				data: null,
				orderable: false,
				render: function(data, type, row) {
					return '<div class="small fw-semibold">' + escapeHtml(row.request_method) + '</div><small class="event-uri text-break">' + escapeHtml(row.endpoint) + '</small>';
				}
			},
			{
				data: 'payload_summary',
				orderable: false,
				render: function(data) {
					return '<small class="event-uri text-break">' + escapeHtml(data || '-') + '</small>';
				}
			},
			{
				data: null,
				orderable: false,
				render: function(data, type, row) {
					return '<div class="small fw-semibold">' + escapeHtml(row.user_label) + '</div><small class="text-muted">' + escapeHtml(row.request_source) + '</small>';
				}
			},
			{
				data: 'id',
				orderable: false,
				searchable: false,
				className: 'text-center',
				render: function(data) {
					return '<button type="button" class="btn btn-sm btn-outline-primary event-detail-btn" data-id="' + data + '"><i class="bi bi-eye"></i></button>';
				}
			}
		],
		language: {
			emptyTable: 'No security event found for current filter.',
			zeroRecords: 'No security event found for current filter.'
		}
	});
}

function bootSecurityCharts() {
	loadSecurityChartJs()
		.then(loadChartData)
		.then(renderCharts)
		.catch(function(error) {
			console.error('Error loading security charts:', error);
		});
}

function bootSecurityLogTable() {
	if ('requestIdleCallback' in window) {
		requestIdleCallback(initSecurityLogTable, { timeout: 800 });
	} else {
		setTimeout(initSecurityLogTable, 120);
	}
}

document.addEventListener('DOMContentLoaded', function() {
	const modalElement = document.getElementById('eventDetailModal');
	if (modalElement && modalElement.parentElement !== document.body) {
		document.body.appendChild(modalElement);
	}

	eventDetailModal = new bootstrap.Modal(modalElement, {
		backdrop: false
	});
	bindEventDetailButtons();
	bootSecurityLogTable();

	if ('requestIdleCallback' in window) {
		requestIdleCallback(bootSecurityCharts, { timeout: 1200 });
	} else {
		setTimeout(bootSecurityCharts, 200);
	}
});
</script>
