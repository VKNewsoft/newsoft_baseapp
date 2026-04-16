<?php
/**
 * View daftar sinkronisasi schema.
 *
 * Tampilan dibuat ringkas agar perbedaan schema, statement aman, dan item
 * review manual bisa dibaca cepat dalam satu halaman.
 */

$syncSummary = $sync_summary ?? [];
$summary = $syncSummary['summary'] ?? [];
$diffItems = $syncSummary['diff']['items'] ?? [];
$isSynced = !empty($syncSummary['is_synced']);
$isRegistered = !empty($syncSummary['is_registered']);
$registration = $syncSummary['registration'] ?? [];
$generatedAt = $syncSummary['generated_at'] ?? '-';
$formToken = $auth->generateFormToken('form_db_synchronisation');
?>

<div class="page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Administrator / DB Synchronisation</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Bandingkan schema database aktif dengan dump installer, review perubahannya, lalu jalankan sinkronisasi aman tanpa menghapus data existing.</p>
		</div>
		<div class="page-actions">
			<a href="<?=current_url()?>?refresh=1" class="btn btn-outline-secondary btn-sm">
				<i class="fa fa-rotate-right pe-1"></i> Refresh Diff
			</a>
		</div>
	</div>

	<?php if (!empty($msg)) : ?>
		<div class="mb-3">
			<?php show_message($msg['message'] ?? '', $msg['status'] ?? 'info'); ?>
		</div>
	<?php endif; ?>

	<div class="db-sync-overview-grid">
		<div class="card page-card db-sync-status-card <?=($isSynced && $isRegistered) ? 'is-synced' : 'is-outdated'?>">
			<div class="card-body">
				<div class="db-sync-status-head">
					<span class="db-sync-status-dot"></span>
					<div>
						<h5 class="mb-1"><?=($isSynced && $isRegistered) ? 'Module & Schema Sinkron' : 'Module Belum Lengkap / Schema Belum Sinkron'?></h5>
						<p class="mb-0 text-muted">Fallback static registration tetap menjaga module bisa dipakai walau seed menu/module belum masuk ke database.</p>
					</div>
				</div>
				<div class="db-sync-status-meta">
					<div>
						<small>Module DB</small>
						<strong><?=!empty($registration['module_exists']) ? 'Ada' : 'Fallback'?></strong>
					</div>
					<div>
						<small>Menu DB</small>
						<strong><?=!empty($registration['menu_exists']) ? 'Ada' : 'Fallback'?></strong>
					</div>
					<div>
						<small>Perubahan Aman</small>
						<strong><?=esc((string) ($summary['safe_changes'] ?? 0))?></strong>
					</div>
					<div>
						<small>Review Manual</small>
						<strong><?=esc((string) ($summary['manual_review'] ?? 0))?></strong>
					</div>
					<div>
						<small>Generate</small>
						<strong><?=esc($generatedAt)?></strong>
					</div>
				</div>
			</div>
		</div>

		<div class="card page-card">
			<div class="card-body">
				<div class="db-sync-metric-grid">
					<div class="db-sync-metric">
						<span>Tabel Baru</span>
						<strong><?=esc((string) ($summary['missing_tables'] ?? 0))?></strong>
					</div>
					<div class="db-sync-metric">
						<span>Kolom Baru</span>
						<strong><?=esc((string) ($summary['missing_columns'] ?? 0))?></strong>
					</div>
					<div class="db-sync-metric">
						<span>Index Baru</span>
						<strong><?=esc((string) ($summary['missing_indexes'] ?? 0))?></strong>
					</div>
					<div class="db-sync-metric">
						<span>Beda Type</span>
						<strong><?=esc((string) ($summary['different_columns'] ?? 0))?></strong>
					</div>
					<div class="db-sync-metric">
						<span>Beda Index</span>
						<strong><?=esc((string) ($summary['different_indexes'] ?? 0))?></strong>
					</div>
					<div class="db-sync-metric">
						<span>Objek Tambahan</span>
						<strong><?=esc((string) (($summary['extra_tables'] ?? 0) + ($summary['extra_columns'] ?? 0) + ($summary['extra_indexes'] ?? 0)))?></strong>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Preview Diff Schema</h5>
					<p class="mb-0 text-muted">Kolom before vs after memperlihatkan kondisi database aktif dibanding target dump installer. Hanya perubahan aman yang mendapat SQL eksekusi otomatis.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap">
				<?php if (!$diffItems) : ?>
					<div class="db-sync-empty-state">
						<i class="fas fa-circle-check"></i>
						<h6 class="mb-1">Tidak ada perbedaan schema</h6>
						<p class="mb-0 text-muted">Database aktif sudah sinkron dengan file installer terbaru.</p>
					</div>
				<?php else : ?>
					<table class="table table-striped table-bordered align-middle mb-0">
						<thead>
							<tr>
								<th>Status</th>
								<th>Objek</th>
								<th>Before</th>
								<th>After</th>
								<th>SQL</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($diffItems as $item) : ?>
								<tr>
									<td>
										<span class="db-sync-badge <?=$item['is_safe'] ? 'is-safe' : 'is-manual'?>">
											<?=$item['is_safe'] ? 'Aman' : 'Review'?>
										</span>
										<div class="small text-muted mt-1"><?=esc($item['scope'])?></div>
									</td>
									<td>
										<div class="fw-semibold"><?=esc($item['label'])?></div>
										<div class="small text-muted"><?=esc($item['table'])?><?=!empty($item['name']) ? ' / ' . esc($item['name']) : ''?></div>
									</td>
									<td>
										<pre class="db-sync-code mb-0"><?=esc($item['current'])?></pre>
									</td>
									<td>
										<pre class="db-sync-code mb-0"><?=esc($item['target'])?></pre>
									</td>
									<td>
										<?php if (!empty($item['sql'])) : ?>
											<details class="db-sync-sql-preview">
												<summary>Lihat SQL</summary>
												<pre class="db-sync-code mt-2 mb-0"><?=esc($item['sql'])?></pre>
											</details>
										<?php else : ?>
											<span class="text-muted small">Tidak dieksekusi otomatis</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body">
			<div class="db-sync-action-panel">
				<div>
					<h5 class="mb-1">Eksekusi Sinkronisasi Aman</h5>
					<p class="mb-0 text-muted">Proses hanya membuat tabel, menambah kolom, dan menambah index yang belum ada. Perubahan type/index yang berpotensi merusak tetap ditahan untuk review manual.</p>
				</div>
				<?php if ($can_apply_sync && ($summary['safe_changes'] ?? 0) > 0) : ?>
					<form action="<?=base_url('db-synchronisation/apply')?>" method="post" class="db-sync-action-form">
						<input type="hidden" name="form_token" value="<?=$formToken?>">
						<button type="submit" name="submit" value="1" class="btn btn-success">
							<i class="fa fa-bolt pe-1"></i> Jalankan Sinkronisasi Aman
						</button>
					</form>
				<?php elseif (!$can_apply_sync) : ?>
					<div class="text-muted small">Role Anda hanya dapat melihat preview diff.</div>
				<?php else : ?>
					<div class="text-muted small">Tidak ada perubahan aman yang perlu dieksekusi.</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
