<div class="page-shell">
	<?php
	/**
	 * Nilai filter awal dipakai ulang oleh web dan mobile supaya perubahan
	 * state filter tetap konsisten walau mekanisme render keduanya berbeda.
	 */
	$filters = $email_expiration_filters ?? [];
	$renewStatus = $filters['renew_status'] ?? 'all';
	$sortExpiration = $filters['sort_expiration'] ?? 'nearest';
	?>
	<div class="page-hero">
		<div>
			<div class="page-kicker">Subscription</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Kelola subscription masa aktif akun email, pantau status aktif atau expired, lalu perpanjang periodenya langsung dari result page.</p>
		</div>
		<div class="page-actions">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-sm btn-add"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Daftar Akun Subscription</h5>
					<p class="mb-0 text-muted">Daftar akun subscription yang dicatat untuk kontrol masa aktif atau masa berakhirnya.</p>
				</div>
				<div class="email-expiration-filter-bar">
					<div class="email-expiration-filter-item">
						<label for="filter-renew-status" class="form-label mb-1">Status</label>
						<select id="filter-renew-status" class="form-select form-select-sm">
							<option value="all" <?=$renewStatus === 'all' ? 'selected' : ''?>>Semua</option>
							<option value="ready" <?=$renewStatus === 'ready' ? 'selected' : ''?>>Perlu renew</option>
							<option value="not_ready" <?=$renewStatus === 'not_ready' ? 'selected' : ''?>>Belum perlu</option>
						</select>
					</div>
					<div class="email-expiration-filter-item">
						<label for="filter-sort-expiration" class="form-label mb-1">Urutan</label>
						<select id="filter-sort-expiration" class="form-select form-select-sm">
							<option value="nearest" <?=$sortExpiration === 'nearest' ? 'selected' : ''?>>Terdekat expired</option>
							<option value="longest" <?=$sortExpiration === 'longest' ? 'selected' : ''?>>Terlama expired</option>
						</select>
					</div>
				</div>
			</div>

			<div class="table-responsive card-table-wrap result-table-region">
				<?php
				if (!empty($msg)) {
					show_alert($msg);
				}

				$column = [
					'ignore_search_urut' => 'No',
					'subscription' => 'Subscription',
					'email_akun' => 'Email / Akun',
					'expiration_hari' => 'Expiration',
					'tgl_start' => 'Tanggal Mulai',
					'tgl_end' => 'Tanggal Berakhir',
					'ignore_search_action' => 'Action'
				];

				$settings['order'] = [0, 'asc'];
				$index = 0;
				$th = '';
				foreach ($column as $key => $val) {
					/**
					 * Atur properti kolom:
					 * - Subscription, Expiration, Tanggal Mulai/Berakhir dibuat selebar isi (width: 1%, nowrap)
					 * - Kolom Email / Akun dibiarkan default jadi bisa lebih lebar menyesuaikan sisa ruang
					 */
					$thAttr = '';
					$colDef = ['targets' => $index];

					if (in_array($key, ['ignore_search_urut', 'ignore_search_action', 'subscription', 'expiration_hari', 'tgl_start', 'tgl_end'])) {
						$thAttr = ' style="width: 1%;"';
						$colDef['className'] = 'text-nowrap';
						$colDef['width'] = '1%';
					}

					$th .= '<th' . $thAttr . '>' . $val . '</th>';

					if (strpos($key, 'ignore_search') !== false) {
						$colDef['orderable'] = false;
					}

					if (count($colDef) > 1) {
						$settings['columnDefs'][] = $colDef;
					}
					$index++;
				}
				?>
				<div id="table-data-skeleton" class="result-table-skeleton" aria-hidden="true">
					<?php
					/**
					 * Skeleton dipakai agar transisi load DataTable tetap stabil
					 * saat data masa aktif email belum selesai dirender server-side.
					 */
					for ($i = 0; $i < 5; $i++) {
						echo '<span class="result-table-skeleton__row"></span>';
					}
					?>
				</div>
				<table id="table-data" class="table display nowrap table-striped table-bordered table-hover align-middle mb-0 result-table-ready" style="width:100%">
					<thead>
						<tr><?=$th?></tr>
					</thead>
				</table>
				<?php
				$columnDt = [];
				foreach ($column as $key => $val) {
					$columnDt[] = ['data' => $key];
				}
				?>
				<span id="dataTables-column" style="display:none"><?=json_encode($columnDt)?></span>
				<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
				<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDT'?></span>
				<span id="mobile-list-url" style="display:none"><?=current_url() . '/getMobileList'?></span>
				<span id="dataTables-scrolls" style="display:none">350</span>
			</div>

			<div class="email-expiration-mobile" id="email-expiration-mobile">
				<div class="email-expiration-mobile__header">
					<div>
						<h5 class="mb-1">Daftar Akun Subscription</h5>
						<p class="mb-0 text-muted">Daftar akun subscription yang dicatat untuk kontrol masa aktif atau masa berakhirnya.</p>
					</div>
					<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#mobile-filter-sheet" aria-controls="mobile-filter-sheet">
						<i class="fas fa-sliders-h me-1"></i>Filter
					</button>
				</div>
				<div class="email-expiration-mobile__list" id="email-expiration-mobile-list"></div>
				<div class="email-expiration-mobile__empty d-none" id="email-expiration-mobile-empty">
					Data email tidak ditemukan untuk filter saat ini.
				</div>
				<div class="email-expiration-mobile__footer">
					<button type="button" class="btn btn-outline-primary w-100" id="email-expiration-load-more">Load More</button>
				</div>
			</div>

			<div class="offcanvas offcanvas-bottom email-expiration-filter-sheet" tabindex="-1" id="mobile-filter-sheet" aria-labelledby="mobile-filter-sheet-label">
				<div class="offcanvas-header">
					<h5 class="offcanvas-title" id="mobile-filter-sheet-label">Filter Subscription</h5>
					<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
				</div>
				<div class="offcanvas-body">
					<div class="email-expiration-mobile__filters">
						<div class="email-expiration-filter-item">
							<label for="mobile-filter-renew-status" class="form-label mb-1">Status</label>
							<select id="mobile-filter-renew-status" class="form-select">
								<option value="all" <?=$renewStatus === 'all' ? 'selected' : ''?>>Semua</option>
								<option value="ready" <?=$renewStatus === 'ready' ? 'selected' : ''?>>Perlu renew</option>
								<option value="not_ready" <?=$renewStatus === 'not_ready' ? 'selected' : ''?>>Belum perlu</option>
							</select>
						</div>
						<div class="email-expiration-filter-item">
							<label for="mobile-filter-sort-expiration" class="form-label mb-1">Urutan</label>
							<select id="mobile-filter-sort-expiration" class="form-select">
								<option value="nearest" <?=$sortExpiration === 'nearest' ? 'selected' : ''?>>Terdekat</option>
								<option value="longest" <?=$sortExpiration === 'longest' ? 'selected' : ''?>>Terlama</option>
							</select>
						</div>
						<button type="button" class="btn btn-primary w-100" id="mobile-apply-filter" data-bs-dismiss="offcanvas">Terapkan Filter</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
