<?php helper('html'); ?>

<div class="result-page-shell">
	<div class="result-page-hero">
		<div>
			<div class="result-page-kicker">Builtin / Module</div>
			<h3 class="result-page-heading"><?=$current_module['judul_module']?></h3>
			<p class="result-page-copy mb-0">Pantau modul aktif, status login, dan ketersediaan file controller langsung dari satu halaman yang lebih rapi.</p>
		</div>
		<div class="result-page-actions">
			<?=btn_link([
				'attr' => ['class' => 'btn btn-success btn-sm'],
				'url' => current_url() . '/add',
				'icon' => 'fa fa-plus',
				'label' => 'Tambah Module'
			])?>
		</div>
	</div>

	<div class="card result-list-card">
		<div class="card-body p-0">
			<div class="result-list-toolbar">
				<div>
					<h5 class="mb-1">Daftar Module</h5>
					<p class="mb-0 text-muted">Status aktif, kebutuhan login, dan validasi file HMVC tetap terlihat jelas tanpa menambah jarak kosong di halaman.</p>
				</div>
			</div>

			<div class="table-responsive result-table-wrap result-table-region">
				<?php
				if (!empty($msg)) {
					show_alert($msg);
				}

				if (!empty($message)) {
					show_message($message);
				}
				
				$column =[
						 'ignore_action' => 'Aksi'
						, 'nama_module' => 'Nama Module'
						, 'judul_module' => 'Judul Module'
						, 'deskripsi' => 'Deskripsi'
						, 'ignore_file_exists' => 'File'
						, 'login' => 'Login'
						, 'ignore_aktif' => 'Aktif'
					];
				$th = '';
				foreach ($column as $val) {
					$th .= '<th>' . $val . '</th>'; 
				}
				?>
				<table id="table-result" class="table display nowrap table-striped table-bordered align-middle mb-0" style="width:100%">
		        <thead>
		            <tr>
						<?=$th?>
		            </tr>
		        </thead>
				</table>
				<?php
					$settings['order'] = [2,'asc'];
					$index = 0;
					foreach ($column as $key => $val) {
						$column_dt[] = ['data' => $key];
						if (strpos($key, 'ignore') !== false) {
							$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
						}
						$index++;
					}
				?>
				<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
				<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
				<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDT'?></span>
				<span id="dataTables-scrolls" style="display:none">510</span>
			</div>
		</div>
	</div>
</div>
