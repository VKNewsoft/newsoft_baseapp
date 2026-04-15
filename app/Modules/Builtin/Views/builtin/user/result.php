<div class="page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Builtin / User</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Kelola user, tenant akses, dan status verifikasi dari satu result page yang lebih padat dan nyaman dipantau.</p>
		</div>
		<div class="page-actions">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		</div>
	</div>

	<div class="card page-card">
		<div class="card-body p-0">
			<div class="page-toolbar">
				<div>
					<h5 class="mb-1">Daftar User</h5>
					<p class="mb-0 text-muted">Role, tenant utama, dan akses company tambahan bisa direview langsung tanpa ruang kosong berlebih.</p>
				</div>
			</div>

			<div class="table-responsive card-table-wrap result-table-region">
				<?php
				if (!empty($message)) {
					show_message($message);
				}
				
				$column =[
						 'ignore_btn_action' => 'Aksi'
						, 'nama_company' => 'Tenant'
						, 'nama' => 'Nama'
						, 'username' => 'Username'
						, 'ignore_access_company' => 'Data Akses'
						, 'judul_role' => 'Role'
						, 'verified' => 'Verified'
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
					$settings['order'] = [3,'asc'];
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
				<span id="dataTables-scrolls" style="display:none">350</span>
			</div>
		</div>
	</div>
</div>
