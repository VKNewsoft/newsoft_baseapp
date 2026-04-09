<div class="role-page-shell role-page-shell-list">
	<div class="role-page-hero">
		<div>
			<div class="role-page-kicker">Builtin / Role</div>
			<h3 class="role-page-heading">Manajemen Role</h3>
			<p class="role-page-copy mb-0">Tambah, edit, dan review role langsung dari satu halaman tanpa meninggalkan tabel utama.</p>
		</div>
		<div class="role-page-actions">
			<button type="button" class="btn btn-success btn-sm btn-add-role">
				<i class="fa fa-plus pe-1"></i> Tambah Role
			</button>
		</div>
	</div>

	<div class="card role-list-card">
		<div class="card-body p-0">
			<div class="role-list-toolbar">
				<div>
					<h5 class="mb-1">Daftar Role</h5>
					<p class="mb-0 text-muted">Role aktif, module default, dan tindak lanjut assignment permission bisa dipantau dari sini.</p>
				</div>
			</div>

			<div class="table-responsive role-table-wrap">
				<?php
				if (!empty($message)) {
					show_message($message);
				}
				
				$column =[
						 'ignore_action' => 'Aksi'
						, 'nama_role' => 'Nama Role'
						, 'judul_role' => 'Judul Role'
						, 'id_module' => 'Default Module'
						, 'keterangan' => 'Keterangan'
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
				<span id="dataTables-scrolls" style="display:none">510</span>
				<span id="role-form-url" style="display:none"><?=current_url() . '/ajaxForm'?></span>
				<span id="role-save-url" style="display:none"><?=current_url() . '/ajaxSave'?></span>
			</div>
		</div>
	</div>
</div>
