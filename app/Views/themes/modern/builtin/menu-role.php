<div class="result-page-shell">
	<div class="result-page-hero">
		<div>
			<div class="result-page-kicker">Builtin / Menu Role</div>
			<h3 class="result-page-heading">Menu Role</h3>
			<p class="result-page-copy mb-0">Atur relasi akses menu terhadap role secara lebih ringkas dengan daftar badge yang tetap mudah dibaca.</p>
		</div>
	</div>

	<div class="card result-list-card">
		<div class="card-body p-0">
			<div class="result-list-toolbar">
				<div>
					<h5 class="mb-1">Daftar Akses Menu</h5>
					<p class="mb-0 text-muted">Setiap menu menampilkan role yang sudah terhubung dan bisa diperbarui lewat popup edit seperti alur modul sebelumnya.</p>
				</div>
			</div>

			<div class="table-responsive result-table-wrap result-table-region">
				<?php
				if (!empty($message)) {
					show_message($message);
				}
				
				$column =[
						 'ignore_action' => 'Aksi'
						, 'nama_menu' => 'Menu'
						, 'url' => 'Url'
						, 'ignore_role' => 'Role'
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
					$settings['order'] = [1,'asc'];
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
				<span id="dataTables-scrolls" style="display:none">400</span>
			</div>
		</div>
	</div>
</div>
