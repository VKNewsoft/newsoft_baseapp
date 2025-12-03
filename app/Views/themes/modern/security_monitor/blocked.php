<!-- Blocked IPs Management -->
<?php helper('html'); ?>

<style>
.blocked-card {
	border-radius: var(--radius);
	border: 1px solid var(--border);
	transition: var(--transition);
	animation: fadeIn 0.4s ease;
}
.blocked-card:hover {
	box-shadow: var(--shadow-md);
}
.ip-badge {
	padding: 0.5em 0.8em;
	font-family: 'Courier New', monospace;
	font-weight: 600;
	background: #fee2e2;
	color: #991b1b;
	border-radius: 6px;
}
.action-btn {
	transition: all 0.2s ease;
}
.action-btn:hover {
	transform: scale(1.05);
}
.search-card {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: white;
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
	<!-- Header with Back Button -->
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h4 class="mb-1">
				<i class="bi bi-shield-slash text-danger me-2"></i>Blocked IP Addresses
			</h4>
			<p class="text-muted mb-0">Manage and monitor blocked IP addresses</p>
		</div>
		<a href="<?= base_url('securitymonitor') ?>" class="btn btn-outline-secondary">
			<i class="bi bi-arrow-left me-2"></i>Back to Dashboard
		</a>
	</div>

	<!-- Search Card -->
	<div class="row g-3 mb-3">
		<div class="col-12">
			<div class="card blocked-card border-0 shadow-sm">
				<div class="card-body">
					<form method="GET" action="<?= base_url('securitymonitor/blocked') ?>" class="row g-3 align-items-end">
						<div class="col-md-8">
							<label class="form-label small text-muted mb-1">
								<i class="bi bi-search me-1"></i>Search IP Address
							</label>
							<input 
								type="text" 
								name="search" 
								class="form-control form-control-lg" 
								placeholder="Enter IP address (e.g., 192.168.1.1)" 
								value="<?= esc($search) ?>"
								pattern="^(?:[0-9]{1,3}\.){0,3}[0-9]{0,3}$"
							>
						</div>
						<div class="col-md-4">
							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary btn-lg flex-grow-1">
									<i class="bi bi-search me-2"></i>Search
								</button>
								<?php if ($search): ?>
								<a href="<?= base_url('securitymonitor/blocked') ?>" class="btn btn-outline-secondary btn-lg">
									<i class="bi bi-x-circle"></i>
								</a>
								<?php endif; ?>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Stats -->
	<div class="row g-3 mb-3">
		<div class="col-md-4">
			<div class="card blocked-card border-0 shadow-sm">
				<div class="card-body text-center py-4">
					<div class="stat-icon bg-danger bg-opacity-10 text-danger mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
						<i class="bi bi-ban fs-3"></i>
					</div>
					<h3 class="fw-bold mb-1"><?= $pager->getTotal() ?></h3>
					<p class="text-muted mb-0 small">Total Blocked IPs</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card blocked-card border-0 shadow-sm">
				<div class="card-body text-center py-4">
					<div class="stat-icon bg-warning bg-opacity-10 text-warning mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
						<i class="bi bi-clock-history fs-3"></i>
					</div>
					<h3 class="fw-bold mb-1"><?= count($blocked_ips) ?></h3>
					<p class="text-muted mb-0 small">Showing on This Page</p>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card blocked-card border-0 shadow-sm">
				<div class="card-body text-center py-4">
					<div class="stat-icon bg-success bg-opacity-10 text-success mx-auto mb-3" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 12px;">
						<i class="bi bi-shield-check fs-3"></i>
					</div>
					<h3 class="fw-bold mb-1">Active</h3>
					<p class="text-muted mb-0 small">Protection Status</p>
				</div>
			</div>
		</div>
	</div>

	<!-- Blocked IPs Table -->
	<div class="row g-3">
		<div class="col-12">
			<div class="card blocked-card border-0 shadow-sm">
				<div class="card-header bg-white border-bottom">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="mb-0">
							<i class="bi bi-list-ul text-primary me-2"></i>Blocked IP List
						</h5>
						<?php if ($search): ?>
						<span class="badge bg-info">Search results for: "<?= esc($search) ?>"</span>
						<?php endif; ?>
					</div>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
						<table class="table table-hover mb-0 align-middle">
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