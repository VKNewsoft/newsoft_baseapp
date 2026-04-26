<?php
/**
 * Halaman monitor forex menggabungkan live price, chart, high-low D/W/M,
 * fetch manual, alert threshold, histori trigger, dan histori harga harian.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
$dashboardPayload = $dashboard_payload ?? [];
$livePrice = $dashboardPayload['live_price'] ?? [];
$highLowSummary = $dashboardPayload['high_low_summary'] ?? [];
$chartPayload = $dashboardPayload['chart'] ?? [];
$latestSnapshot = $latest_snapshot ?? [];
$monitorMetrics = $monitor_metrics ?? [];
$priceRows = $price_rows ?? [];
$activeAlerts = $active_alerts ?? [];
$alertHistory = $alert_history ?? [];
$formData = $form_data ?? [];
$isEditMode = !empty($formData['id_forex_alert']) || !empty($formData['id']);
$formId = (int) ($formData['id_forex_alert'] ?? $formData['id'] ?? 0);
$bootstrapJson = json_encode($dashboardPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<div
	id="forex-dashboard-app"
	class="page-shell forex-page-shell"
	data-snapshot-url="<?=$module_url?>/snapshot"
	data-default-timeframe="<?=esc($default_timeframe ?? '1D')?>"
	data-pair="<?=esc($pair ?? 'GBPJPY')?>"
>
	<div class="page-hero">
		<div>
			<div class="page-kicker">Forex Monitor</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Pantau GBP/JPY realtime, kelola fetch OHLC harian, baca high-low D/W/M, dan pasang alert threshold dari satu module monitor yang terpusat.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$config->baseURL?>forex-prediction?date_from=<?=esc($filters['date_from'])?>&date_to=<?=esc($filters['date_to'])?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-chart-simple pe-1"></i> Buka Prediction</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-suite-filter-card mb-4">
		<div class="card-body">
			<form method="post" action="<?=$module_url?>/fetch" class="row g-3 align-items-end">
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Tanggal Fetch</label>
					<input type="date" name="date" class="form-control" value="<?=esc(date('Y-m-d'))?>" max="<?=esc(date('Y-m-d'))?>">
				</div>
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Periode Dari</label>
					<input type="date" name="date_from" class="form-control" value="<?=esc($filters['date_from'])?>">
				</div>
				<div class="col-lg-3 col-md-6">
					<label class="form-label">Periode Sampai</label>
					<input type="date" name="date_to" class="form-control" value="<?=esc($filters['date_to'])?>">
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="form-check forex-inline-check">
						<input class="form-check-input" type="checkbox" name="force_refresh" value="1" id="force-refresh-monitor">
						<label class="form-check-label" for="force-refresh-monitor">Abaikan cache API</label>
					</div>
					<div class="form-actions mt-2">
						<?=csrf_field()?>
						<input type="hidden" name="timeframe" value="<?=esc($default_timeframe ?? '1D')?>">
						<button type="submit" name="submit" value="1" class="btn btn-success"><i class="fas fa-cloud-arrow-down me-1"></i> Fetch Manual</button>
						<a href="<?=$module_url?>?date_from=<?=esc($filters['date_from'])?>&date_to=<?=esc($filters['date_to'])?>" class="btn btn-outline-secondary">Refresh</a>
					</div>
				</div>
			</form>
			<div class="forex-helper-copy mt-3">
				<span class="badge bg-light text-dark border">Pair tetap: <?=$pair?></span>
				<span class="badge bg-light text-dark border">Sumber utama: Alpha Vantage</span>
				<span class="badge bg-light text-dark border">Auto fetch: `php spark forex:fetch`</span>
			</div>
		</div>
	</div>

	<?php
	/**
	 * Card headline dibuat terpisah agar live price, delta, provider, dan waktu
	 * update tetap mudah dipindai sebelum user berpindah ke chart detail.
	 */
	?>
	<div class="forex-summary-grid mb-4">
		<div class="forex-summary-card forex-dashboard-live-card">
			<div class="forex-summary-card__label">Live Price</div>
			<div class="forex-summary-card__value" id="dashboard-current-price"><?=!empty($livePrice) ? number_format((float) ($livePrice['current_price'] ?? 0), 4, ',', '.') : '-'?></div>
			<div class="forex-summary-card__meta">Pair <?=esc($pair ?? 'GBPJPY')?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Price Change</div>
			<div class="forex-summary-card__value" id="dashboard-change-text">
				<?php if (!empty($livePrice)): ?>
					<?=((float) ($livePrice['change_amount'] ?? 0) >= 0 ? '↑ ' : '↓ ') . number_format(abs((float) ($livePrice['change_amount'] ?? 0)), 4, ',', '.')?>
				<?php else: ?>
					-
				<?php endif; ?>
			</div>
			<div class="forex-summary-card__meta" id="dashboard-change-percent"><?=!empty($livePrice) ? number_format((float) ($livePrice['change_percent'] ?? 0), 4, ',', '.') . '%' : '-'?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Source Provider</div>
			<div class="forex-summary-card__value" id="dashboard-source"><?=esc($livePrice['provider'] ?? '-')?></div>
			<div class="forex-summary-card__meta" id="dashboard-source-type"><?=esc($livePrice['source_type'] ?? '-')?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Last Quote Time</div>
			<div class="forex-summary-card__value" id="dashboard-quote-time"><?=!empty($livePrice['quote_time']) ? esc($livePrice['quote_time']) : '-'?></div>
			<div class="forex-summary-card__meta">Auto refresh 30 detik</div>
		</div>
	</div>

	<div class="forex-dashboard-layout">
		<div class="forex-dashboard-layout__main">
			<div class="card page-card mb-4">
				<div class="card-body">
					<div class="forex-dashboard-toolbar">
						<div>
							<h5 class="mb-1">Interactive Chart</h5>
							<p class="mb-0 text-muted">Candlestick realtime memakai auto-fit awal, polling 30 detik, zoom bebas, HLCC/4, SMA 12, empat Bollinger set, dan panel RSI ringan.</p>
						</div>
						<div class="forex-dashboard-toolbar__actions">
							<div class="btn-group btn-group-sm" role="group" aria-label="Timeframe">
								<button type="button" class="btn btn-outline-secondary active" data-forex-timeframe="1D">1D</button>
								<button type="button" class="btn btn-outline-secondary" data-forex-timeframe="1W">1W</button>
								<button type="button" class="btn btn-outline-secondary" data-forex-timeframe="1M">1M</button>
							</div>
							<div class="btn-group btn-group-sm" role="group" aria-label="Chart Zoom Controls">
								<?php /** Tombol dibuat eksplisit agar kontrol zoom mudah dipahami pada desktop maupun mobile. */ ?>
								<button type="button" class="btn btn-outline-secondary" data-chart-action="zoom-in">Zoom In</button>
								<button type="button" class="btn btn-outline-secondary" data-chart-action="zoom-out">Zoom Out</button>
								<button type="button" class="btn btn-outline-secondary" data-chart-action="reset-zoom">Reset</button>
							</div>
						</div>
					</div>

					<div class="forex-helper-copy mt-3">
						<span class="badge bg-light text-dark border" id="dashboard-chart-provider">Provider chart: <?=esc($chartPayload['provider'] ?? '-')?></span>
						<span class="badge bg-light text-dark border" id="dashboard-chart-indicator">HLCC/4 | SMA 12 | 4x Bollinger</span>
						<span class="badge bg-light text-dark border" id="dashboard-chart-points">Points: <?=count($chartPayload['candles'] ?? [])?></span>
						<span class="badge bg-light text-dark border" id="dashboard-chart-range">Range auto-fit aktif</span>
					</div>

					<div class="forex-indicator-toolbar mt-3">
						<div class="forex-indicator-toolbar__title">Indicator Legend</div>
						<div id="dashboard-indicator-legend" class="forex-indicator-legend"></div>
					</div>

					<div class="forex-chart-shell mt-3">
						<div id="forex-live-candlestick" class="forex-chart-shell__price"></div>
						<div id="forex-live-rsi" class="forex-chart-shell__rsi"></div>
					</div>
				</div>
			</div>

			<div class="card page-card project-suite-filter-card forex-alert-form-card mb-4">
				<div class="card-body">
					<div class="page-toolbar px-0 pt-0">
						<div>
							<h5 class="mb-1">Manual Price Alert</h5>
							<p class="mb-0 text-muted">Pasang target above atau below untuk GBP/JPY. Alert aktif akan dicek pada setiap polling monitor dan otomatis masuk ke histori saat terpenuhi.</p>
						</div>
					</div>
					<form method="post" action="<?=$module_url?>/save-alert" class="row g-3 align-items-end mt-1">
						<div class="col-lg-3 col-md-6">
							<label class="form-label">Target Price</label>
							<input type="number" name="target_price" class="form-control" step="0.0001" min="0.0001" value="<?=esc($formData['target_price'] ?? '')?>" required>
						</div>
						<div class="col-lg-3 col-md-6">
							<label class="form-label">Condition</label>
							<select name="condition_type" class="form-select">
								<option value="above" <?=($formData['condition_type'] ?? 'above') === 'above' ? 'selected' : ''?>>Above</option>
								<option value="below" <?=($formData['condition_type'] ?? '') === 'below' ? 'selected' : ''?>>Below</option>
							</select>
						</div>
						<div class="col-lg-2 col-md-6">
							<div class="form-check">
								<input class="form-check-input" type="checkbox" name="with_sound" value="1" id="forex-alert-sound" <?=!empty($formData['with_sound']) ? 'checked' : ''?>>
								<label class="form-check-label" for="forex-alert-sound">Sound alert</label>
							</div>
						</div>
						<div class="col-lg-2 col-md-6">
							<label class="form-label">Status</label>
							<select name="is_active" class="form-select">
								<option value="1" <?=((string) ($formData['is_active'] ?? '1')) !== '0' ? 'selected' : ''?>>Active</option>
								<option value="0" <?=((string) ($formData['is_active'] ?? '1')) === '0' ? 'selected' : ''?>>Inactive</option>
							</select>
						</div>
						<div class="col-lg-2 col-md-12">
							<div class="form-actions">
								<?=csrf_field()?>
								<input type="hidden" name="id" value="<?=$formId?>">
								<button type="submit" class="btn btn-success"><i class="fas fa-floppy-disk me-1"></i> <?=$isEditMode ? 'Update Alert' : 'Simpan Alert'?></button>
								<a href="<?=$module_url?>" class="btn btn-outline-secondary"><?=$isEditMode ? 'Batal Edit' : 'Reset'?></a>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="forex-dashboard-layout__side">
			<div class="card page-card mb-4">
				<div class="card-body">
					<div class="page-toolbar px-0 pt-0">
						<div>
							<h5 class="mb-1">High / Low Context</h5>
							<p class="mb-0 text-muted">Ringkasan Daily, Weekly, dan Monthly dihitung dari histori tersimpan lalu disesuaikan dengan sesi live yang sedang berjalan.</p>
						</div>
					</div>
					<div class="forex-dashboard-highlow mt-3">
						<?php foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label): ?>
							<?php $item = $highLowSummary[$key] ?? []; ?>
							<div class="forex-market-context-card">
								<div class="forex-market-context-card__head">
									<div>
										<div class="forex-method-card__label"><?=$label?></div>
										<div class="forex-method-card__meta"><?=esc($item['date_label'] ?? '-')?></div>
									</div>
									<span class="forex-trend-badge forex-trend-badge--bullish">D/W/M</span>
								</div>
								<div class="forex-market-context-card__grid">
									<div class="forex-market-context-card__row"><span>High</span><strong><?=number_format((float) ($item['high_price'] ?? 0), 4, ',', '.')?></strong></div>
									<div class="forex-market-context-card__row"><span>Low</span><strong><?=number_format((float) ($item['low_price'] ?? 0), 4, ',', '.')?></strong></div>
									<div class="forex-market-context-card__row"><span>Range</span><strong><?=number_format(max(0, (float) ($item['high_price'] ?? 0) - (float) ($item['low_price'] ?? 0)), 4, ',', '.')?></strong></div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="card page-card mb-4">
				<div class="card-body">
					<div class="page-toolbar px-0 pt-0">
						<div>
							<h5 class="mb-1">Active Alerts</h5>
							<p class="mb-0 text-muted">Threshold aktif dicek setiap polling monitor. Toast dan bunyi opsional dipicu saat harga menembus target.</p>
						</div>
					</div>
					<div id="dashboard-active-alerts" class="forex-alert-mini-list mt-3">
						<?php if (!$activeAlerts): ?>
							<div class="project-suite-empty">Belum ada alert aktif untuk user ini.</div>
						<?php endif; ?>
						<?php foreach ($activeAlerts as $alert): ?>
							<div class="forex-alert-mini-item">
								<div class="forex-alert-mini-item__title"><?=strtoupper(esc($alert['condition_type'] ?? 'above'))?> <?=number_format((float) ($alert['target_price'] ?? 0), 4, ',', '.')?></div>
								<div class="forex-alert-mini-item__meta">Sound <?=!empty($alert['with_sound']) ? 'On' : 'Off'?> | Dibuat <?=!empty($alert['created_at']) ? esc($alert['created_at']) : '-'?></div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>

			<div class="card page-card mb-4">
				<div class="card-body">
					<div class="page-toolbar px-0 pt-0">
						<div>
							<h5 class="mb-1">Snapshot Monitor</h5>
							<p class="mb-0 text-muted">Ringkasan cepat terbaru untuk range rata-rata, tren dominan, dan candle harian yang tersimpan.</p>
						</div>
					</div>
					<div class="forex-level-grid mt-3">
						<div class="forex-level-box">
							<div class="forex-level-box__title">Data Terbaru</div>
							<div class="forex-level-box__row"><span>Tanggal</span><strong><?=!empty($latestSnapshot['date']) ? esc($latestSnapshot['date']) : '-'?></strong></div>
							<div class="forex-level-box__row"><span>High / Low</span><strong><?=!empty($latestSnapshot) ? number_format((float) $latestSnapshot['high_price'], 4, ',', '.') . ' / ' . number_format((float) $latestSnapshot['low_price'], 4, ',', '.') : '-'?></strong></div>
							<div class="forex-level-box__row"><span>Trend</span><strong><?=!empty($latestSnapshot['trend']) ? esc(ucfirst($latestSnapshot['trend'])) : '-'?></strong></div>
						</div>
						<div class="forex-level-box">
							<div class="forex-level-box__title">Metrik Histori</div>
							<div class="forex-level-box__row"><span>Total Hari</span><strong><?=$monitorMetrics['total_days'] ?? 0?></strong></div>
							<div class="forex-level-box__row"><span>Average Range</span><strong><?=number_format((float) ($monitorMetrics['average_range'] ?? 0), 4, ',', '.')?></strong></div>
							<div class="forex-level-box__row"><span>Bull/Bear/Side</span><strong><?=($monitorMetrics['bullish_total'] ?? 0) . '/' . ($monitorMetrics['bearish_total'] ?? 0) . '/' . ($monitorMetrics['sideways_total'] ?? 0)?></strong></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card forex-page-card mb-4">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Active Alert Management</h5>
					<p class="mb-0 text-muted">Kelola target yang masih aktif, pause sementara, atau hapus alert yang sudah tidak dibutuhkan.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
					<thead>
						<tr>
							<th>ID</th>
							<th>Condition</th>
							<th>Target</th>
							<th>Last Checked</th>
							<th>Triggered At</th>
							<th>Sound</th>
							<th>Status</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$activeAlerts): ?>
							<tr>
								<td colspan="8" class="text-center text-muted py-4">Belum ada alert forex aktif atau tersimpan.</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($activeAlerts as $alert): ?>
							<tr>
								<td><?=number_format((int) ($alert['id_forex_alert'] ?? 0), 0, ',', '.')?></td>
								<td><?=strtoupper(esc($alert['condition_type'] ?? 'above'))?></td>
								<td><?=number_format((float) ($alert['target_price'] ?? 0), 4, ',', '.')?></td>
								<td><?=!empty($alert['last_checked_price']) ? number_format((float) $alert['last_checked_price'], 4, ',', '.') : '-'?></td>
								<td><?=!empty($alert['triggered_at']) ? esc($alert['triggered_at']) : '-'?></td>
								<td><?=!empty($alert['with_sound']) ? 'On' : 'Off'?></td>
								<td>
									<span class="forex-trend-badge forex-trend-badge--<?=!empty($alert['is_active']) ? 'bullish' : 'sideways'?>"><?=!empty($alert['is_active']) ? 'Active' : 'Inactive'?></span>
								</td>
								<td>
									<div class="forex-alert-table-actions">
										<a href="<?=$module_url?>?edit=<?=$alert['id_forex_alert']?>" class="btn btn-outline-primary btn-sm">Edit</a>
										<form method="post" action="<?=$module_url?>/toggle-alert">
											<?=csrf_field()?>
											<input type="hidden" name="id_forex_alert" value="<?=$alert['id_forex_alert']?>">
											<button type="submit" class="btn btn-outline-warning btn-sm"><?=!empty($alert['is_active']) ? 'Pause' : 'Aktifkan'?></button>
										</form>
										<form method="post" action="<?=$module_url?>/delete-alert" onsubmit="return confirm('Hapus alert ini?')">
											<?=csrf_field()?>
											<input type="hidden" name="id_forex_alert" value="<?=$alert['id_forex_alert']?>">
											<button type="submit" class="btn btn-outline-danger btn-sm">Hapus</button>
										</form>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="card page-card forex-page-card mb-4">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Alert History</h5>
					<p class="mb-0 text-muted">Riwayat ini menyimpan semua trigger threshold yang sebelumnya terpenuhi oleh harga live GBP/JPY.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
					<thead>
						<tr>
							<th>Triggered At</th>
							<th>Condition</th>
							<th>Target</th>
							<th>Triggered Price</th>
							<th>Sound</th>
							<th>Message</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$alertHistory): ?>
							<tr>
								<td colspan="6" class="text-center text-muted py-4">Belum ada histori trigger alert.</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($alertHistory as $history): ?>
							<tr>
								<td><?=!empty($history['created_at']) ? esc($history['created_at']) : '-'?></td>
								<td><?=strtoupper(esc(str_replace('_', ' ', $history['condition_type'] ?? 'above')))?></td>
								<td><?=number_format((float) ($history['target_price'] ?? 0), 4, ',', '.')?></td>
								<td><?=number_format((float) ($history['triggered_price'] ?? 0), 4, ',', '.')?></td>
								<td><?=!empty($history['with_sound']) ? 'On' : 'Off'?></td>
								<td class="forex-summary-column"><?=esc($history['message'] ?? '-')?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="card page-card forex-page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Histori Harga Harian</h5>
					<p class="mb-0 text-muted">Daftar OHLC harian GBP/JPY lengkap dengan range dan hasil analisis dasar per tanggal.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
					<thead>
						<tr>
							<th>Tanggal</th>
							<th>Pair</th>
							<th>Open</th>
							<th>High</th>
							<th>Low</th>
							<th>Close</th>
							<th>Range</th>
							<th>Trend</th>
							<th>Source API</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$priceRows): ?>
							<tr>
								<td colspan="10" class="text-center text-muted py-4">Belum ada data forex yang tersimpan.</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($priceRows as $row): ?>
							<tr>
								<td><?=esc($row['date'])?></td>
								<td><?=esc($row['pair'])?></td>
								<td><?=number_format((float) $row['open_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) $row['high_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) $row['low_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) $row['close_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) ($row['high_low_range'] ?? ((float) $row['high_price'] - (float) $row['low_price'])), 4, ',', '.')?></td>
								<td>
									<?php if (!empty($row['trend'])): ?>
										<span class="forex-trend-badge forex-trend-badge--<?=$row['trend']?>"><?=esc(ucfirst($row['trend']))?></span>
									<?php else: ?>
										-
									<?php endif; ?>
								</td>
								<td><?=esc($row['source_api'])?></td>
								<td>
									<a href="<?=$config->baseURL?>forex-prediction?date_from=<?=esc($row['date'])?>&date_to=<?=esc($row['date'])?>" class="btn btn-outline-primary btn-sm">Analisis</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<script type="application/json" id="forex-dashboard-bootstrap"><?=$bootstrapJson ?: '{}'?></script>
</div>
