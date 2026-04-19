<!-- Blocked IPs Management -->
<?php helper('html'); ?>

<style>
.blocked-page {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}
.blocked-hero {
	background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #eef4ff 100%);
	border: 1px solid var(--border);
	border-radius: calc(var(--radius) + 4px);
	padding: 1.5rem;
	box-shadow: var(--shadow-sm);
}
.blocked-hero-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.7fr) minmax(280px, 0.9fr);
	gap: 1rem;
}
.blocked-hero-title {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
}
.blocked-hero-icon,
.blocked-side-icon,
.blocked-stat-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 16px;
}
.blocked-hero-icon {
	width: 58px;
	height: 58px;
	font-size: 1.55rem;
	background: rgba(220, 38, 38, 0.12);
	color: #dc2626;
	flex-shrink: 0;
}
.blocked-title {
	font-size: 1.75rem;
	font-weight: 700;
	line-height: 1.1;
	margin-bottom: 0.35rem;
}
.blocked-points {
	display: grid;
	grid-template-columns: repeat(3, minmax(0, 1fr));
	gap: 0.75rem;
	margin-top: 1.2rem;
}
.blocked-point {
	background: rgba(255, 255, 255, 0.9);
	border: 1px solid rgba(148, 163, 184, 0.18);
	border-radius: 14px;
	padding: 0.9rem 1rem;
}
.blocked-point small {
	display: block;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: var(--bs-secondary-color, #64748b);
	margin-bottom: 0.3rem;
}
.blocked-side {
	background: #ffffff;
	border: 1px solid rgba(37, 99, 235, 0.12);
	border-radius: 18px;
	padding: 1.25rem;
	box-shadow: var(--shadow-sm);
}
.blocked-side-top {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 0.75rem;
	margin-bottom: 1rem;
}
.blocked-side-icon {
	width: 48px;
	height: 48px;
	background: rgba(37, 99, 235, 0.12);
	color: var(--primary);
	font-size: 1.2rem;
}
.blocked-status-chip {
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
.blocked-status-chip::before {
	content: "";
	width: 8px;
	height: 8px;
	border-radius: 50%;
	background: currentColor;
}
.blocked-card {
	border-radius: calc(var(--radius) + 2px);
	border: 1px solid var(--border);
	box-shadow: var(--shadow-sm);
}
.blocked-card:hover {
	box-shadow: var(--shadow-md);
}
.blocked-filter-box {
	padding: 1rem 1.1rem;
	border-radius: 16px;
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.16);
}
.blocked-stat-card {
	min-height: 150px;
}
.blocked-stat-icon {
	width: 52px;
	height: 52px;
	font-size: 1.3rem;
}
.blocked-stat-chip {
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
.blocked-section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 1rem;
	flex-wrap: wrap;
}
.blocked-ip-badge {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.45rem 0.75rem;
	border-radius: 10px;
	background: #fff1f2;
	color: #991b1b;
	font-family: "Courier New", monospace;
	font-weight: 600;
}
.blocked-table thead th {
	font-size: 0.76rem;
	text-transform: uppercase;
	letter-spacing: 0.05em;
	color: var(--bs-secondary-color, #64748b);
	background: #f8fafc;
	border-bottom-width: 1px;
}
.blocked-table tbody td {
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
@media (max-width: 991.98px) {
	.blocked-hero-grid,
	.blocked-points {
		grid-template-columns: 1fr;
	}
}
@media (max-width: 767.98px) {
	.blocked-hero {
		padding: 1.15rem;
	}
	.blocked-title {
		font-size: 1.42rem;
	}
	.blocked-table {
		min-width: 860px;
	}
}
</style>

<div class="container-fluid">
	<div class="blocked-page">
		<div class="blocked-hero">
			<div class="blocked-hero-grid">
				<div>
					<div class="blocked-hero-title">
						<div class="blocked-hero-icon">
							<i class="bi bi-shield-slash"></i>
						</div>
						<div>
							<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
								<span class="badge text-bg-light border text-primary">Protection List</span>
								<span class="blocked-status-chip">Blocking Active</span>
							</div>
							<h1 class="blocked-title">Blocked IP Addresses</h1>
							<p class="text-muted mb-0">Kelola daftar IP yang diblok dengan filter tanggal, pencarian IP, dan aksi unblock yang cepat tanpa mengubah flow proteksi existing.</p>
						</div>
					</div>
					<div class="blocked-points">
						<div class="blocked-point">
							<small>Scope</small>
							<strong>Blocked IP review & maintenance</strong>
						</div>
						<div class="blocked-point">
							<small>Filter</small>
							<strong>IP search dan tanggal pemblokiran</strong>
						</div>
						<div class="blocked-point">
							<small>Action</small>
							<strong>Manual unblock per entry</strong>
						</div>
					</div>
				</div>
				<div class="blocked-side">
					<div class="blocked-side-top">
						<div>
							<small class="text-muted d-block mb-1">Navigation</small>
							<h5 class="mb-1">Back to Security Dashboard</h5>
							<p class="text-muted mb-0 small">Kembali ke monitor utama untuk melihat alert, trend, dan detail event.</p>
						</div>
						<div class="blocked-side-icon">
							<i class="bi bi-arrow-left-right"></i>
						</div>
					</div>
					<a href="<?= base_url('securitymonitor') ?>" class="btn btn-outline-secondary w-100">
						<i class="bi bi-arrow-left me-2"></i>Back to Dashboard
					</a>
				</div>
			</div>
		</div>

		<div class="card blocked-card border-0">
			<div class="card-body">
				<form method="GET" action="<?= base_url('securitymonitor/blocked') ?>" class="blocked-filter-box">
					<div class="blocked-section-header mb-3">
						<div>
							<h5 class="mb-1"><i class="bi bi-funnel text-primary me-2"></i>Filter Blocked IP</h5>
							<small class="text-muted">Cari IP spesifik atau sempitkan berdasarkan tanggal blokir.</small>
						</div>
						<?php if ($filters['search'] || $filters['date_from'] || $filters['date_to']): ?>
						<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-outline-secondary btn-sm">
							<i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
						</a>
						<?php endif; ?>
					</div>
					<div class="row g-3 align-items-end">
						<div class="col-lg-5 col-md-6">
							<label class="form-label small text-muted mb-1">IP Address</label>
							<input type="text" name="search" class="form-control" value="<?= esc($filters['search']) ?>" placeholder="Enter IP address">
						</div>
						<div class="col-lg-3 col-md-6">
							<label class="form-label small text-muted mb-1">Date From</label>
							<input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
						</div>
						<div class="col-lg-3 col-md-6">
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
			<div class="col-md-4">
				<div class="card blocked-card blocked-stat-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Total Blocked</p>
								<h3 class="fw-bold mb-1"><?= number_format($blocked_summary['total_blocked']) ?></h3>
							</div>
							<div class="blocked-stat-icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-ban"></i></div>
						</div>
						<div class="mt-auto"><span class="blocked-stat-chip"><i class="bi bi-database"></i>Current filtered total</span></div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card blocked-card blocked-stat-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">Blocked Today</p>
								<h3 class="fw-bold mb-1"><?= number_format($blocked_summary['blocked_today']) ?></h3>
							</div>
							<div class="blocked-stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-calendar-check"></i></div>
						</div>
						<div class="mt-auto"><span class="blocked-stat-chip"><i class="bi bi-clock-history"></i>Today scope</span></div>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="card blocked-card blocked-stat-card border-0">
					<div class="card-body d-flex flex-column">
						<div class="d-flex justify-content-between align-items-start mb-3">
							<div>
								<p class="text-muted mb-1 small">This Page</p>
								<h3 class="fw-bold mb-1"><?= number_format($loaded_rows) ?></h3>
							</div>
							<div class="blocked-stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-layout-text-window"></i></div>
						</div>
						<div class="mt-auto"><span class="blocked-stat-chip"><i class="bi bi-list-ol"></i>Paginated result</span></div>
					</div>
				</div>
			</div>
		</div>

		<div class="card blocked-card border-0">
			<div class="card-header bg-white border-bottom">
				<div class="blocked-section-header">
					<div>
						<h5 class="mb-1"><i class="bi bi-list-ul text-primary me-2"></i>Blocked IP List</h5>
						<small class="text-muted">Daftar IP yang diblok lengkap dengan waktu blokir dan aksi unblock.</small>
					</div>
					<?php if ($filters['search']): ?>
					<span class="badge bg-info">Keyword: "<?= esc($filters['search']) ?>"</span>
					<?php endif; ?>
				</div>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table id="blocked-ip-table" class="table table-hover blocked-table mb-0 align-middle w-100">
						<thead>
							<tr>
								<th style="width:60px;">#</th>
								<th>IP Address</th>
								<th style="width:180px;">Blocked Date</th>
								<th style="width:160px;">Blocked Time</th>
								<th style="width:130px;" class="text-center">Action</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div class="card-footer bg-white border-top py-0">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 px-3 py-2">
					<div class="text-muted">
						<small>Initial batch <strong id="blocked-loaded-count"><?= number_format($loaded_rows) ?></strong> rows</small>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
let blockedIpTable;

function buildBlockedTableUrl() {
	const params = new URLSearchParams(window.location.search);
	const query = params.toString();
	return '<?= base_url('securitymonitor/blockedData') ?>' + (query ? ('?' + query) : '');
}

function escapeBlockedHtml(value) {
	return $('<div>').text(value || '').html();
}

function initBlockedIpTable() {
	const $table = $('#blocked-ip-table');
	if (!$table.length || !$.fn.DataTable) {
		return;
	}

	blockedIpTable = $table.DataTable({
		processing: true,
		serverSide: true,
		searching: false,
		lengthChange: false,
		pageLength: 15,
		deferRender: true,
		scrollX: true,
		order: [[2, 'desc']],
		ajax: {
			url: buildBlockedTableUrl(),
			type: 'POST',
			dataSrc: function(json) {
				$('#blocked-loaded-count').text(json.loaded_count || 0);
				return json.data || [];
			}
		},
		columns: [
			{ data: 'rownum', className: 'text-muted' },
			{
				data: 'ip_address',
				render: function(data) {
					return '<span class="blocked-ip-badge"><i class="bi bi-geo-alt-fill"></i>' + escapeBlockedHtml(data) + '</span>';
				}
			},
			{
				data: 'blocked_date',
				render: function(data) {
					return '<i class="bi bi-calendar3 text-muted me-1"></i>' + escapeBlockedHtml(data);
				}
			},
			{
				data: 'blocked_time',
				render: function(data) {
					return '<i class="bi bi-clock text-muted me-1"></i>' + escapeBlockedHtml(data);
				}
			},
			{
				data: 'ip_address',
				orderable: false,
				searchable: false,
				className: 'text-center',
				render: function(data) {
					return '<button class="btn btn-sm btn-success unblock-btn" data-ip="' + escapeBlockedHtml(data) + '"><i class="bi bi-unlock-fill me-1"></i>Unblock</button>';
				}
			}
		],
		language: {
			emptyTable: 'No blocked IPs found.',
			zeroRecords: 'No blocked IPs matched current filter.'
		}
	});
}

$(document).on('click', '.unblock-btn', async function() {
		const ip = this.dataset.ip;
		const result = await Swal.fire({
			title: 'Unblock IP Address?',
			html: 'Are you sure you want to unblock:<br><code class="fs-5 text-danger">' + ip + '</code>',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#22c55e',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="bi bi-unlock me-2"></i>Yes, Unblock!',
			cancelButtonText: 'Cancel'
		});

		if (!result.isConfirmed) {
			return;
		}

		try {
			const response = await fetch('<?= base_url('securitymonitor/unblock') ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-Requested-With': 'XMLHttpRequest'
				},
				body: new URLSearchParams({ ip: ip })
			});

			const data = await response.json();
			if (data.success) {
				await Swal.fire({
					title: 'Unblocked!',
					text: data.message,
					icon: 'success',
					confirmButtonColor: '#22c55e'
				});
				if (blockedIpTable) {
					blockedIpTable.ajax.reload(null, false);
				} else {
					window.location.reload();
				}
				return;
			}

			Swal.fire({
				title: 'Failed!',
				text: data.message,
				icon: 'error',
				confirmButtonColor: '#dc2626'
			});
		} catch (error) {
			Swal.fire({
				title: 'Error!',
				text: 'Failed to connect to server.',
				icon: 'error',
				confirmButtonColor: '#dc2626'
			});
		}
});

$(function() {
	if ('requestIdleCallback' in window) {
		requestIdleCallback(initBlockedIpTable, { timeout: 800 });
	} else {
		setTimeout(initBlockedIpTable, 120);
	}
});
</script>
