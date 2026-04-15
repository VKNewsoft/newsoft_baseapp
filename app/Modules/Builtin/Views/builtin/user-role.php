<div class="page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Builtin / User Role</div>
			<h3 class="page-heading">User Role</h3>
			<p class="page-copy mb-0">Lihat distribusi role per user dengan layout yang lebih padat, sehingga badge role tidak lagi mendorong konten keluar halaman.</p>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Daftar User dan Role</h5>
					<p class="mb-0 text-muted">Edit assignment tetap dilakukan lewat popup, sementara result view dibuat lebih konsisten dengan modul role.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap result-table-region">
				<?php
				if (!empty($message)) {
					show_message($message);
				}
				
				$column =[
						 'ignore_action' => 'Aksi'
						, 'username' => 'Menu'
						, 'nama' => 'Nama'
						, 'email' => 'Email'
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
				<span id="dataTables-scrolls" style="display:none">400</span>
			</div>
		</div>
	</div>
</div>
