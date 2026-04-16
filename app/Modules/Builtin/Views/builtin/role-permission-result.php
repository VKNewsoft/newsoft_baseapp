<?php helper('html'); ?>

<div class="page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Builtin / Role Permission</div>
			<h3 class="page-heading">Daftar Role</h3>
			<p class="page-copy mb-0">Monitor sebaran permission per role dengan tampilan yang lebih bersih sebelum masuk ke halaman detail assignment.</p>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Ringkasan Permission Role</h5>
					<p class="mb-0 text-muted">Jumlah modul dan jumlah permission tampil langsung di tabel utama agar audit akses lebih cepat.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap result-table-region">
				<?php
				$column =[
						 'ignore_action' => 'Action'
						, 'nama_role' => 'Nama Role'
						, 'judul_role' => 'Judul Role'
						, 'ignore_jml_module' => 'Jml. Module'
						, 'ignore_jml_permission' => 'Jml. Permission'
					];
				
				$settings['order'] = [1,'asc'];
				$index = 0;
				$th = '';
				foreach ($column as $key => $val) {
					$th .= '<th>' . $val . '</th>'; 
					if (strpos($key, 'ignore') !== false) {
						$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
					}
					$index++;
				}
				?>
				
				<div id="table-result-skeleton" class="result-table-skeleton" aria-hidden="true">
					<?php
					// Skeleton generik ini menahan reflow area tabel saat list role
					// permission menunggu response awal dari server.
					for ($i = 0; $i < 5; $i++) {
						echo '<span class="result-table-skeleton__row"></span>';
					}
					?>
				</div>
				<table id="table-result" class="table display nowrap table-striped table-bordered table-hover align-middle mb-0 result-table-ready" style="width:100%">
				<thead>
					<tr>
						<?=$th?>
					</tr>
				</thead>
				</table>
				<?php
					foreach ($column as $key => $val) {
						$column_dt[] = ['data' => $key];
					}
				?>
				<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
				<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
				<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDT'?></span>
				<span id="dataTables-scrolls" style="display:none">400</span>
			</div>
		</div>
	</div>
</div>
