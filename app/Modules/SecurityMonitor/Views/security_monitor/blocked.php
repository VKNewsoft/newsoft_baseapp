<!-- Blocked IPs Management -->
<?php helper('html'); ?>

<style>
.blocked-shell {
	display: flex;
	flex-direction: column;
	gap: 1rem;
}
.blocked-overview {
	background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #eef4ff 100%);
	border: 1px solid var(--border);
	border-radius: calc(var(--radius) + 4px);
	padding: 1.5rem;
	box-shadow: var(--shadow-sm);
}
.blocked-overview-grid {
	display: grid;
	grid-template-columns: minmax(0, 1.7fr) minmax(260px, 0.95fr);
	gap: 1rem;
	align-items: stretch;
}
.blocked-title {
	display: flex;
	align-items: flex-start;
	gap: 1rem;
}
.blocked-title-icon,
.blocked-panel-icon,
.stat-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 14px;
}
.blocked-title-icon {
	width: 56px;
	height: 56px;
	font-size: 1.5rem;
	background: rgba(220, 38, 38, 0.1);
	color: #dc2626;
	flex-shrink: 0;
}
.blocked-headline {
	font-size: 1.7rem;
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
	padding: 0.9rem 1rem;
	border-radius: 14px;
	border: 1px solid rgba(148, 163, 184, 0.18);
	background: rgba(255, 255, 255, 0.88);
}
.blocked-point-label {
	display: block;
	font-size: 0.78rem;
	color: var(--bs-secondary-color, #6b7280);
	margin-bottom: 0.35rem;
	text-transform: uppercase;
	letter-spacing: 0.04em;
}
.blocked-point-value {
	font-size: 1rem;
	font-weight: 700;
}
.blocked-side-panel {
	padding: 1.25rem;
	background: #ffffff;
	border: 1px solid rgba(37, 99, 235, 0.12);
	border-radius: 18px;
	box-shadow: var(--shadow-sm);
	height: 100%;
}
.blocked-panel-top {
	display: flex;
	justify-content: space-between;
	align-items: flex-start;
	gap: 1rem;
	margin-bottom: 1rem;
}
.blocked-panel-icon {
	width: 48px;
	height: 48px;
	font-size: 1.2rem;
	background: rgba(37, 99, 235, 0.12);
	color: var(--primary);
}
.blocked-status-chip {
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
	transition: var(--transition);
	animation: fadeIn 0.4s ease;
	box-shadow: var(--shadow-sm);
}
.blocked-card:hover {
	box-shadow: var(--shadow-md);
}
.panel-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: 1rem;
	flex-wrap: wrap;
}
.filter-wrap {
	padding: 1rem 1.1rem;
	border-radius: 16px;
	background: #f8fafc;
	border: 1px solid rgba(148, 163, 184, 0.14);
}
.search-input-group {
	position: relative;
}
.search-input-group .form-control {
	padding-left: 2.8rem;
	height: 48px;
	border-radius: 12px;
}
.search-input-group .search-icon {
	position: absolute;
	left: 0.95rem;
	top: 50%;
	transform: translateY(-50%);
	color: var(--bs-secondary-color, #64748b);
	z-index: 4;
}
.stat-icon {
	width: 54px;
	height: 54px;
	border-radius: 14px;
	display: flex;
	align-items: center;
	justify-content: center;
}
.stat-card {
	min-height: 156px;
}
.stat-note {
	display: inline-flex;
	align-items: center;
	gap: 0.35rem;
	padding: 0.3rem 0.6rem;
	border-radius: 999px;
	background: var(--bg-secondary);
	color: var(--bs-secondary-color, #64748b);
	font-size: 0.75rem;
	font-weight: 600;
}
.ip-badge {
	display: inline-flex;
	align-items: center;
	gap: 0.45rem;
	padding: 0.45rem 0.75rem;
	font-family: 'Courier New', monospace;
	font-weight: 600;
	background: #fff1f2;
	color: #991b1b;
	border-radius: 10px;
}
.action-btn {
	transition: all 0.2s ease;
	border-radius: 10px;
}
.action-btn:hover {
	transform: translateY(-1px);
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
	.blocked-overview-grid,
	.blocked-points {
		grid-template-columns: 1fr;
	}
}
@media (max-width: 767.98px) {
	.blocked-overview {
		padding: 1.15rem;
	}
	.blocked-headline {
		font-size: 1.4rem;
	}
	.blocked-table {
		min-width: 760px;
	}
}
</style>

<div class="container-fluid">
	<div class="blocked-shell">
		<div class="blocked-overview">
			<div class="blocked-overview-grid">
				<div>
					<div class="blocked-title">
						<div class="blocked-title-icon">
							<i class="bi bi-shield-slash"></i>
						</div>
						<div>
							<div class="d-flex flex-wrap align-items-center gap-2 mb-2">
								<span class="badge text-bg-light border text-primary">Access Control</span>
								<span class="blocked-status-chip">Protection Active</span>
							</div>
							<h1 class="blocked-headline">Blocked IP Addresses</h1>
							<p class="text-muted mb-0">Kelola IP yang sedang diblok dengan tampilan tabel yang lebih bersih, cepat dipindai, dan tetap nyaman dipakai di layar kecil.</p>
						</div>
					</div>
					<div class="blocked-points">
						<div class="blocked-point">
							<span class="blocked-point-label">Review</span>
							<div class="blocked-point-value">Blocked list overview</div>
						</div>
						<div class="blocked-point">
							<span class="blocked-point-label">Filter</span>
							<div class="blocked-point-value">Search by IP address</div>
						</div>
						<div class="blocked-point">
							<span class="blocked-point-label">Action</span>
							<div class="blocked-point-value">Manual unblock control</div>
						</div>
					</div>
				</div>
				<div class="blocked-side-panel">
					<div class="blocked-panel-top">
						<div>
							<small class="text-muted d-block mb-1">Navigation</small>
							<h5 class="mb-1">Back to Dashboard</h5>
							<p class="text-muted mb-0 small">Kembali ke ringkasan monitor untuk melihat timeline serangan dan statistik utama.</p>
						</div>
						<div class="blocked-panel-icon">
							<i class="bi bi-arrow-left-right"></i>
						</div>
					</div>
					<a href="<?= base_url('securitymonitor') ?>" class="btn btn-outline-secondary w-100">
						<i class="bi bi-arrow-left me-2"></i>Back to Dashboard
					</a>
				</div>
			</div>
		</div>

		<div class="row g-3">
		<div class="col-12">
			<div class="card blocked-card border-0">
				<div class="card-body">
					<form method="GET" action="<?= base_url('securitymonitor/blocked') ?>" class="filter-wrap">
						<div class="panel-header mb-3">
							<div>
								<h5 class="mb-1"><i class="bi bi-search text-primary me-2"></i>Search Blocked IP</h5>
								<small class="text-muted">Filter daftar berdasarkan alamat IP tanpa mengubah flow pencarian yang ada.</small>
							</div>
							<?php if ($search): ?>
							<span class="badge bg-info">Keyword: "<?= esc($search) ?>"</span>
							<?php endif; ?>
						</div>
						<div class="row g-3 align-items-end">
						<div class="col-md-8">
							<label class="form-label small text-muted mb-1">
								<i class="bi bi-search me-1"></i>Search IP Address
							</label>
							<div class="search-input-group">
								<i class="bi bi-search search-icon"></i>
								<input 
									type="text" 
									name="search" 
									class="form-control" 
									placeholder="Enter IP address (e.g., 192.168.1.1)" 
									value="<?= esc($search) ?>"
									pattern="^(?:[0-9]{1,3}\.){0,3}[0-9]{0,3}$"
								>
							</div>
						</div>
						<div class="col-md-4">
							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary flex-grow-1" style="height:48px;">
									<i class="bi bi-search me-2"></i>Search
								</button>
								<?php if ($search): ?>
								<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-outline-secondary" style="height:48px; width:48px; display:inline-flex; align-items:center; justify-content:center;">
									<i class="bi bi-x-circle"></i>
								</a>
								<?php endif; ?>
							</div>
						</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		</div>

		<div class="row g-3">
		<div class="col-md-4">
			<div class="card blocked-card stat-card border-0">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Total Blocked IPs</p>
							<h3 class="fw-bold mb-1"><?= $pager->getTotal() ?></h3>
						</div>
						<div class="stat-icon bg-danger bg-opacity-10 text-danger">
						<i class="bi bi-ban fs-3"></i>
					</div>
					</div>
					<div class="mt-auto">
						<span class="stat-note"><i class="bi bi-database"></i>Stored entries</span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card blocked-card stat-card border-0">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Showing on This Page</p>
							<h3 class="fw-bold mb-1"><?= count($blocked_ips) ?></h3>
						</div>
						<div class="stat-icon bg-warning bg-opacity-10 text-warning">
						<i class="bi bi-clock-history fs-3"></i>
					</div>
					</div>
					<div class="mt-auto">
						<span class="stat-note"><i class="bi bi-layout-text-window"></i>Current pagination</span>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card blocked-card stat-card border-0">
				<div class="card-body d-flex flex-column">
					<div class="d-flex justify-content-between align-items-start mb-3">
						<div>
							<p class="text-muted mb-1 small">Protection Status</p>
							<h3 class="fw-bold mb-1">Active</h3>
						</div>
						<div class="stat-icon bg-success bg-opacity-10 text-success">
						<i class="bi bi-shield-check fs-3"></i>
					</div>
					</div>
					<div class="mt-auto">
						<span class="stat-note"><i class="bi bi-check2-circle"></i>Blocking enabled</span>
					</div>
				</div>
			</div>
		</div>
		</div>

		<div class="row g-3">
		<div class="col-12">
			<div class="card blocked-card border-0">
				<div class="card-header bg-white border-bottom">
					<div class="panel-header">
						<div>
							<h5 class="mb-1">
								<i class="bi bi-list-ul text-primary me-2"></i>Blocked IP List
							</h5>
							<small class="text-muted">Tabel daftar blokir dengan fokus ke keterbacaan dan aksi unblock yang cepat.</small>
						</div>
						<?php if ($search): ?>
						<span class="badge bg-info">Search results for: "<?= esc($search) ?>"</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-hover mb-0 align-middle blocked-table">
							<thead class="table-light">
								<tr>
									<th style="width: 60px;">#</th>
									<th>IP Address</th>
									<th style="width: 200px;">Blocked Date</th>
									<th style="width: 200px;">Blocked Time</th>
									<th style="width: 120px;" class="text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if (empty($blocked_ips)): ?>
								<tr>
									<td colspan="5" class="text-center py-5">
										<div class="text-muted">
											<i class="bi bi-inbox fs-1 d-block mb-3"></i>
											<h5>No Blocked IPs Found</h5>
											<p class="mb-0">
												<?php if ($search): ?>
													No results match your search criteria.
												<?php else: ?>
													There are currently no blocked IP addresses.
												<?php endif; ?>
											</p>
										</div>
									</td>
								</tr>
								<?php else: ?>
								<?php 
								$start = ($pager->getCurrentPage() - 1) * $pager->getPerPage();
								foreach ($blocked_ips as $index => $ip): 
								?>
								<tr>
									<td class="text-muted"><?= $start + $index + 1 ?></td>
									<td>
										<span class="ip-badge">
											<i class="bi bi-geo-alt-fill me-1"></i>
							<?= esc($ip['ip_address']) ?>
										</span>
									</td>
									<td>
										<i class="bi bi-calendar3 text-muted me-1"></i>
										<?= date('d M Y', strtotime($ip['blocked_at'])) ?>
									</td>
									<td>
										<i class="bi bi-clock text-muted me-1"></i>
										<?= date('H:i:s', strtotime($ip['blocked_at'])) ?>
									</td>
									<td class="text-center">
										<button 
											class="btn btn-sm btn-success action-btn unblock-btn" 
											data-ip="<?= esc($ip['ip_address']) ?>"
											title="Unblock this IP"
										>
											<i class="bi bi-unlock-fill me-1"></i>Unblock
										</button>
									</td>
								</tr>
								<?php endforeach; ?>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php if ($pager->getPageCount() > 1): ?>
				<div class="card-footer bg-white border-top py-3">
					<div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
						<div class="text-muted">
							<i class="bi bi-ban me-1"></i>
							<small>Showing <strong><?= count($blocked_ips) ?></strong> of <strong><?= $pager->getTotal() ?></strong> blocked IPs</small>
							<span class="mx-2">•</span>
							<small>Page <strong><?= $pager->getCurrentPage() ?></strong> of <strong><?= $pager->getPageCount() ?></strong></small>
						</div>
						<nav aria-label="Blocked IPs pagination">
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.querySelectorAll('.unblock-btn').forEach(btn => {
	btn.addEventListener('click', async function() {
		const ip = this.dataset.ip;
		const result = await Swal.fire({
			title: 'Unblock IP Address?',
			html: `Are you sure you want to unblock:<br><code class="fs-5 text-danger">${ip}</code>`,
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#22c55e',
			cancelButtonColor: '#6c757d',
			confirmButtonText: '<i class="bi bi-unlock me-2"></i>Yes, Unblock!',
			cancelButtonText: 'Cancel'
		});

		if (result.isConfirmed) {
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
					window.location.reload();
				} else {
					Swal.fire({
						title: 'Failed!',
						text: data.message,
						icon: 'error',
						confirmButtonColor: '#dc2626'
					});
				}
			} catch (error) {
				Swal.fire({
					title: 'Error!',
					text: 'Failed to connect to server.',
					icon: 'error',
					confirmButtonColor: '#dc2626'
				});
			}
		}
	});
});
</script>
