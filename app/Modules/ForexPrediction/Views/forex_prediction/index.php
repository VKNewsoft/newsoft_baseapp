<?php
/**
 * Halaman prediction forex menampilkan signal, market context, prediksi
 * multi-metode, auto-monitor, dan histori analisis GBP/JPY yang terpisah.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
$latestSnapshot = $latest_snapshot ?? [];
$reportMetrics = $report_metrics ?? [];
$reportRows = $report_rows ?? [];
$predictionResult = $prediction_result ?? [];
$predictionAggregate = $predictionResult['aggregate'] ?? [];
$predictionMethods = $predictionResult['methods'] ?? [];
$predictionBasePrice = $predictionResult['base_price'] ?? [];
$predictionScheduler = $predictionResult['scheduler'] ?? [];
$signalPayload = $signal_payload ?? [];
$marketContext = $signalPayload['market_context'] ?? [];
$combinedContext = $marketContext['combined'] ?? [];
$signalNotes = $signalPayload['notes'] ?? [];
?>
<div class="page-shell forex-page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Forex Prediction</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Baca trading signal aktif, market context multi-timeframe, dan prediksi next-day GBP/JPY tanpa mencampurnya dengan monitor realtime.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$config->baseURL?>forex-monitor?date_from=<?=esc($filters['date_from'])?>&date_to=<?=esc($filters['date_to'])?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-line pe-1"></i> Kembali ke Monitor</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-suite-filter-card mb-4">
		<div class="card-body">
			<form method="get" class="row g-3 align-items-end">
				<div class="col-lg-4 col-md-6">
					<label class="form-label">Tanggal Dari</label>
					<input type="date" name="date_from" class="form-control" value="<?=esc($filters['date_from'])?>">
				</div>
				<div class="col-lg-4 col-md-6">
					<label class="form-label">Tanggal Sampai</label>
					<input type="date" name="date_to" class="form-control" value="<?=esc($filters['date_to'])?>">
				</div>
				<div class="col-lg-4 col-md-12">
					<div class="form-actions">
						<button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filter Prediction</button>
						<a href="<?=$module_url?>" class="btn btn-outline-secondary">Reset</a>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="forex-summary-grid mb-4">
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Current Signal</div>
			<div class="forex-summary-card__value">
				<span class="forex-signal-badge forex-signal-badge--<?=esc($signalPayload['signal_color'] ?? 'yellow')?>"><?=esc($signalPayload['signal_label'] ?? 'WAIT')?></span>
			</div>
			<div class="forex-summary-card__meta">Confidence <?=esc(ucfirst($signalPayload['confidence'] ?? 'low'))?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Live Price</div>
			<div class="forex-summary-card__value"><?=number_format((float) ($live_price['current_price'] ?? 0), 4, ',', '.')?></div>
			<div class="forex-summary-card__meta"><?=esc($live_price['provider'] ?? 'Database Fallback')?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Buy / Sell Zone</div>
			<div class="forex-summary-card__value"><?=esc($signalPayload['buy_zone']['label'] ?? '-')?></div>
			<div class="forex-summary-card__meta"><?=esc($signalPayload['sell_zone']['label'] ?? '-')?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Breakout Up / Down</div>
			<div class="forex-summary-card__value">
				<?=((float) ($signalPayload['breakout_level'] ?? 0) > 0 || (float) ($signalPayload['breakdown_level'] ?? 0) > 0)
					? number_format((float) ($signalPayload['breakout_level'] ?? 0), 4, ',', '.') . ' / ' . number_format((float) ($signalPayload['breakdown_level'] ?? 0), 4, ',', '.')
					: '-'?>
			</div>
			<div class="forex-summary-card__meta"><?=esc($signalPayload['auto_monitor']['label'] ?? 'Auto-monitor nonaktif')?></div>
		</div>
	</div>

	<div class="card page-card project-suite-filter-card forex-alert-form-card mb-4">
		<div class="card-body">
			<div class="page-toolbar px-0 pt-0">
				<div>
					<h5 class="mb-1">Trading Signal</h5>
					<p class="mb-0 text-muted"><?=esc($signalPayload['reason'] ?? 'Menunggu konfirmasi support, resistance, atau breakout dinamis.')?></p>
				</div>
			</div>

			<div class="forex-signal-grid mt-3">
				<div class="forex-signal-hero">
					<div class="forex-signal-hero__eyebrow">Current Signal</div>
					<div class="forex-signal-hero__badge-wrap">
						<span class="forex-signal-badge forex-signal-badge--<?=esc($signalPayload['signal_color'] ?? 'yellow')?>"><?=esc($signalPayload['signal_label'] ?? 'WAIT')?></span>
					</div>
					<div class="forex-signal-hero__confidence">Confidence <?=esc(ucfirst($signalPayload['confidence'] ?? 'low'))?></div>
					<div class="forex-signal-hero__copy"><?=esc($signalPayload['recommendation'] ?? 'Menunggu harga mendekati support atau resistance dominan.')?></div>
				</div>

				<div class="forex-method-card">
					<div class="forex-method-card__label">RSI</div>
					<div class="forex-method-card__value"><?=number_format((float) ($signalPayload['indicators']['rsi'] ?? 0), 2, ',', '.')?></div>
					<div class="forex-method-card__meta">Momentum chart aktif</div>
				</div>
				<div class="forex-method-card">
					<div class="forex-method-card__label">Bollinger</div>
					<div class="forex-method-card__value"><?=number_format((float) ($signalPayload['indicators']['bollinger']['lower'] ?? 0), 4, ',', '.')?></div>
					<div class="forex-method-card__meta">Upper <?=number_format((float) ($signalPayload['indicators']['bollinger']['upper'] ?? 0), 4, ',', '.')?></div>
				</div>
				<div class="forex-method-card">
					<div class="forex-method-card__label">Fibonacci</div>
					<div class="forex-method-card__value"><?=number_format((float) ($signalPayload['indicators']['fibonacci']['0.382'] ?? 0), 4, ',', '.')?></div>
					<div class="forex-method-card__meta">0.618 <?=number_format((float) ($signalPayload['indicators']['fibonacci']['0.618'] ?? 0), 4, ',', '.')?></div>
				</div>
			</div>

			<div class="forex-level-grid mt-3">
				<div class="forex-level-box">
					<div class="forex-level-box__title">Important Note</div>
					<div class="forex-signal-note-list">
						<div class="forex-signal-note-list__item"><?=esc($signalNotes[0] ?? 'Avoid buying near active resistance zone.')?></div>
						<div class="forex-signal-note-list__item"><?=esc($signalNotes[1] ?? 'Wait for pullback to support zone OR breakout above active level.')?></div>
					</div>
				</div>
				<div class="forex-level-box">
					<div class="forex-level-box__title">Auto-monitor</div>
					<div class="forex-level-box__row"><span>Status</span><strong><?=esc($signalPayload['auto_monitor']['label'] ?? 'Auto-monitor nonaktif')?></strong></div>
					<div class="forex-level-box__row"><span>Buy Zone</span><strong><?=esc($signalPayload['buy_zone']['label'] ?? '-')?></strong></div>
					<div class="forex-level-box__row"><span>Sell Zone</span><strong><?=esc($signalPayload['sell_zone']['label'] ?? '-')?></strong></div>
					<form method="post" action="<?=$module_url?>/save-auto-monitor" class="mt-3">
						<?=csrf_field()?>
						<div class="form-check mb-3">
							<input class="form-check-input" type="checkbox" name="auto_monitor" value="1" id="forex-auto-monitor" <?=!empty($signalPayload['auto_monitor']['enabled']) ? 'checked' : ''?>>
							<label class="form-check-label" for="forex-auto-monitor">Aktifkan auto-monitor signal</label>
						</div>
						<button type="submit" class="btn btn-outline-primary btn-sm">Simpan Auto-monitor</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card mb-4">
		<div class="card-body">
			<div class="page-toolbar px-0 pt-0">
				<div>
					<h5 class="mb-1">Market Context</h5>
					<p class="mb-0 text-muted">Support, resistance, dan breakout dihitung ulang dari OHLC Daily, Weekly, dan Monthly dengan prioritas timeframe tertinggi.</p>
				</div>
			</div>
			<div class="forex-market-context-list mt-3">
				<?php foreach (['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $key => $label): ?>
					<?php $item = $marketContext[$key] ?? []; ?>
					<div class="forex-market-context-card">
						<div class="forex-market-context-card__head">
							<div>
								<div class="forex-method-card__label"><?=$label?></div>
								<div class="forex-method-card__meta"><?=esc($item['date_label'] ?? '-')?></div>
							</div>
							<span class="forex-signal-badge forex-signal-badge--<?=esc($item['status_color'] ?? 'yellow')?>"><?=esc($item['status_label'] ?? 'Inside Range')?></span>
						</div>
						<div class="forex-market-context-card__grid">
							<div class="forex-market-context-card__row"><span>High / Low</span><strong><?=!empty($item) ? number_format((float) ($item['high_price'] ?? 0), 4, ',', '.') . ' / ' . number_format((float) ($item['low_price'] ?? 0), 4, ',', '.') : '-'?></strong></div>
							<div class="forex-market-context-card__row"><span>Resistance</span><strong><?=esc($item['resistance_zone']['label'] ?? '-')?></strong></div>
							<div class="forex-market-context-card__row"><span>Support</span><strong><?=esc($item['support_zone']['label'] ?? '-')?></strong></div>
							<div class="forex-market-context-card__row"><span>Breakout Up / Down</span><strong><?=!empty($item) ? number_format((float) ($item['breakout_up_level'] ?? 0), 4, ',', '.') . ' / ' . number_format((float) ($item['breakout_down_level'] ?? 0), 4, ',', '.') : '-'?></strong></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="forex-level-grid mt-3">
				<div class="forex-level-box">
					<div class="forex-level-box__title">Combined Signal</div>
					<div class="forex-level-box__row"><span>Confluence</span><strong><?=esc($signalPayload['confluence']['label'] ?? 'Belum ada confluence kuat')?></strong></div>
					<div class="forex-level-box__row"><span>Priority</span><strong><?=esc(ucfirst($combinedContext['priority_timeframe'] ?? 'monthly'))?></strong></div>
					<div class="forex-level-box__row"><span>Summary</span><strong class="forex-market-context-summary__text"><?=esc($combinedContext['summary'] ?? 'Menunggu context dominan dari OHLC historis.')?></strong></div>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card mb-4">
		<div class="card-body">
			<div class="page-toolbar px-0 pt-0">
				<div>
					<h5 class="mb-1">Final Summary Section</h5>
					<p class="mb-0 text-muted">Prediksi next-day dihitung dari candle harian terakhir yang sudah tersimpan pada histori GBP/JPY.</p>
				</div>
			</div>

			<?php if (!$predictionResult): ?>
				<div class="project-suite-empty">Prediksi next-day belum tersedia karena histori harga belum cukup.</div>
			<?php else: ?>
				<div class="forex-prediction-grid">
					<div class="forex-method-card forex-method-card--accent">
						<div class="forex-method-card__label">Base Candle</div>
						<div class="forex-method-card__value"><?=esc($predictionResult['base_date'] ?? '-')?></div>
						<div class="forex-method-card__meta">Target <?=esc($predictionResult['target_date'] ?? '-')?></div>
					</div>
					<div class="forex-method-card forex-method-card--accent">
						<div class="forex-method-card__label">Majority Direction</div>
						<div class="forex-method-card__value">
							<span class="forex-trend-badge forex-trend-badge--<?=esc($predictionAggregate['direction'] ?? 'sideways')?>"><?=esc(ucfirst($predictionAggregate['direction'] ?? 'sideways'))?></span>
						</div>
						<div class="forex-method-card__meta">Vote B <?=$predictionAggregate['votes']['bullish'] ?? 0?> | S <?=$predictionAggregate['votes']['bearish'] ?? 0?></div>
					</div>
					<div class="forex-method-card forex-method-card--accent">
						<div class="forex-method-card__label">Average Predicted High</div>
						<div class="forex-method-card__value"><?=number_format((float) ($predictionAggregate['predicted_high'] ?? 0), 4, ',', '.')?></div>
						<div class="forex-method-card__meta">Dari 4 metode</div>
					</div>
					<div class="forex-method-card forex-method-card--accent">
						<div class="forex-method-card__label">Average Predicted Low</div>
						<div class="forex-method-card__value"><?=number_format((float) ($predictionAggregate['predicted_low'] ?? 0), 4, ',', '.')?></div>
						<div class="forex-method-card__meta">Sesi New York <?=esc($predictionScheduler['timezone'] ?? 'America/New_York')?></div>
					</div>
				</div>

				<div class="forex-narrative-card mt-3">
					<div class="forex-narrative-card__label">Simple Summary</div>
					<div class="forex-narrative-card__text"><?=esc($predictionAggregate['summary'] ?? '-')?></div>
				</div>

				<div class="forex-method-meta mt-3">
					<span class="badge bg-light text-dark border">Open <?=number_format((float) ($predictionBasePrice['open_price'] ?? 0), 4, ',', '.')?></span>
					<span class="badge bg-light text-dark border">High <?=number_format((float) ($predictionBasePrice['high_price'] ?? 0), 4, ',', '.')?></span>
					<span class="badge bg-light text-dark border">Low <?=number_format((float) ($predictionBasePrice['low_price'] ?? 0), 4, ',', '.')?></span>
					<span class="badge bg-light text-dark border">Close <?=number_format((float) ($predictionBasePrice['close_price'] ?? 0), 4, ',', '.')?></span>
					<span class="badge bg-light text-dark border">Recalc Mode <?=esc($predictionScheduler['mode'] ?? 'manual')?></span>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ($predictionResult): ?>
		<?php foreach (['fibonacci' => 'Fibonacci Section', 'pivot_point' => 'Pivot Section', 'elliott_wave' => 'Elliott Wave Section', 'kang_gun' => 'Kang Gun Section'] as $methodKey => $methodTitle): ?>
			<?php $method = $predictionMethods[$methodKey] ?? []; ?>
			<div class="card page-card mb-4">
				<div class="card-body">
					<div class="page-toolbar px-0 pt-0">
						<div>
							<h5 class="mb-1"><?=$methodTitle?></h5>
							<p class="mb-0 text-muted"><?=esc($method['detail'] ?? '')?></p>
						</div>
					</div>
					<div class="forex-prediction-grid">
						<div class="forex-method-card">
							<div class="forex-method-card__label">Directional Bias</div>
							<div class="forex-method-card__value">
								<?php if ($methodKey === 'elliott_wave'): ?>
									<?=esc(ucfirst($method['wave_bias'] ?? 'corrective'))?>
								<?php else: ?>
									<span class="forex-trend-badge forex-trend-badge--<?=esc($method['direction'] ?? 'sideways')?>"><?=esc(ucfirst($method['direction'] ?? 'sideways'))?></span>
								<?php endif; ?>
							</div>
							<div class="forex-method-card__meta"><?=esc($method['indication'] ?? ($method['wave_bias'] ?? 'Metode aktif'))?></div>
						</div>
						<div class="forex-method-card">
							<div class="forex-method-card__label">Predicted High</div>
							<div class="forex-method-card__value"><?=number_format((float) ($method['predicted_high'] ?? 0), 4, ',', '.')?></div>
							<div class="forex-method-card__meta">Target atas metode</div>
						</div>
						<div class="forex-method-card">
							<div class="forex-method-card__label">Predicted Low</div>
							<div class="forex-method-card__value"><?=number_format((float) ($method['predicted_low'] ?? 0), 4, ',', '.')?></div>
							<div class="forex-method-card__meta">Target bawah metode</div>
						</div>
					</div>

					<?php if ($methodKey === 'fibonacci'): ?>
						<div class="forex-level-grid mt-3">
							<div class="forex-level-box">
								<div class="forex-level-box__title">Projected Support</div>
								<?php foreach (($method['support_levels'] ?? []) as $ratio => $value): ?>
									<div class="forex-level-box__row"><span><?=$ratio?></span><strong><?=number_format((float) $value, 4, ',', '.')?></strong></div>
								<?php endforeach; ?>
							</div>
							<div class="forex-level-box">
								<div class="forex-level-box__title">Projected Resistance</div>
								<?php foreach (($method['resistance_levels'] ?? []) as $ratio => $value): ?>
									<div class="forex-level-box__row"><span><?=$ratio?></span><strong><?=number_format((float) $value, 4, ',', '.')?></strong></div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php elseif ($methodKey === 'pivot_point'): ?>
						<div class="forex-level-grid mt-3">
							<div class="forex-level-box">
								<div class="forex-level-box__title">Key Levels</div>
								<div class="forex-level-box__row"><span>P</span><strong><?=number_format((float) ($method['pivot'] ?? 0), 4, ',', '.')?></strong></div>
								<div class="forex-level-box__row"><span>R1</span><strong><?=number_format((float) ($method['r1'] ?? 0), 4, ',', '.')?></strong></div>
								<div class="forex-level-box__row"><span>R2</span><strong><?=number_format((float) ($method['r2'] ?? 0), 4, ',', '.')?></strong></div>
								<div class="forex-level-box__row"><span>S1</span><strong><?=number_format((float) ($method['s1'] ?? 0), 4, ',', '.')?></strong></div>
								<div class="forex-level-box__row"><span>S2</span><strong><?=number_format((float) ($method['s2'] ?? 0), 4, ',', '.')?></strong></div>
							</div>
						</div>
					<?php elseif ($methodKey === 'kang_gun'): ?>
						<div class="forex-level-grid mt-3">
							<div class="forex-level-box">
								<div class="forex-level-box__title">Volatility Blend</div>
								<div class="forex-level-box__row"><span>Expansion Range</span><strong><?=number_format((float) ($method['expansion_range'] ?? 0), 4, ',', '.')?></strong></div>
								<div class="forex-level-box__row"><span>Volatility Score</span><strong><?=number_format((float) ($method['volatility_score'] ?? 0), 2, ',', '.')?></strong></div>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>

	<div class="forex-summary-grid mb-4">
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Total Report</div>
			<div class="forex-summary-card__value"><?=$reportMetrics['total_reports'] ?? 0?></div>
			<div class="forex-summary-card__meta">Pair <?=$pair?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Average Range</div>
			<div class="forex-summary-card__value"><?=number_format((float) ($reportMetrics['average_range'] ?? 0), 4, ',', '.')?></div>
			<div class="forex-summary-card__meta">Max <?=number_format((float) ($reportMetrics['max_range'] ?? 0), 4, ',', '.')?></div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Komposisi Trend</div>
			<div class="forex-summary-card__value"><?=$reportMetrics['bullish_total'] ?? 0?> / <?=$reportMetrics['bearish_total'] ?? 0?> / <?=$reportMetrics['sideways_total'] ?? 0?></div>
			<div class="forex-summary-card__meta">Bullish / Bearish / Sideways</div>
		</div>
		<div class="forex-summary-card">
			<div class="forex-summary-card__label">Laporan Terbaru</div>
			<div class="forex-summary-card__value"><?=!empty($latestSnapshot['date']) ? esc($latestSnapshot['date']) : '-'?></div>
			<div class="forex-summary-card__meta">
				<?php if (!empty($latestSnapshot['trend'])): ?>
					<span class="forex-trend-badge forex-trend-badge--<?=$latestSnapshot['trend']?>"><?=esc(ucfirst($latestSnapshot['trend']))?></span>
				<?php else: ?>
					Belum ada trend
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="card page-card forex-page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Histori Analisis Harian</h5>
					<p class="mb-0 text-muted">Laporan per tanggal menampilkan range high-low, trend hasil close vs open, dan narasi sederhana untuk kebutuhan evaluasi prediksi rutin.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap project-suite-table">
				<table class="table table-striped table-bordered table-hover align-middle mb-0" data-project-datatable="1" data-page-length="10" data-order='[[0,"desc"]]'>
					<thead>
						<tr>
							<th>Tanggal</th>
							<th>Pair</th>
							<th>High</th>
							<th>Low</th>
							<th>Range</th>
							<th>Trend</th>
							<th>Summary</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$reportRows): ?>
							<tr>
								<td colspan="7" class="text-center text-muted py-4">Belum ada report forex pada periode ini.</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($reportRows as $row): ?>
							<tr>
								<td><?=esc($row['date'])?></td>
								<td><?=esc($row['pair'])?></td>
								<td><?=number_format((float) $row['high_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) $row['low_price'], 4, ',', '.')?></td>
								<td><?=number_format((float) $row['high_low_range'], 4, ',', '.')?></td>
								<td>
									<?php if (!empty($row['trend'])): ?>
										<span class="forex-trend-badge forex-trend-badge--<?=$row['trend']?>"><?=esc(ucfirst($row['trend']))?></span>
									<?php else: ?>
										-
									<?php endif; ?>
								</td>
								<td class="forex-summary-column"><?=esc($row['summary'])?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
