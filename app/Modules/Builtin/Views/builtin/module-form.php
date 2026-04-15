<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		helper ('html');
		echo btn_link([
			'attr' => ['class' => 'btn btn-success btn-xs'],
			'url' => $config->baseURL . 'builtin/module/add',
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Module'
		]);
		
		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs'],
			'url' => $config->baseURL . 'builtin/module',
			'icon' => 'fa fa-arrow-circle-left',
			'label' => 'Daftar Module'
		]);
		?>
		<hr/>
		<?php
		if (!empty($message)) {
			
			show_alert($message);
			$postRole = $request->getPost('role');
			$postJudulModule = $request->getPost('judul_module');
			if ($request->uri->getSegment(3) == 'add' && !empty($postRole)) 
			{
				$html = 'Selanjutnya, set permission module ' . esc($postJudulModule) . ' untuk role: <ul class="list-circle">'; 
				foreach ($postRole as $id_role) {
					$html .= '<li><a target="_blank" title="Set Permission Untuk Role ' . esc($role[$id_role]['judul_role']) . '" class="text-light" href="' . base_url() . '/builtin/role-permission/edit?id=' . esc($id_role) . '">' . esc($role[$id_role]['judul_role']) . '</a></li>'; 
				}
				$html .= '</ul>';
				show_message(['status' => 'success', 'content'=> $html]);
			}				
		}
		
		if (empty($nama_module)) {
			$fields = ['nama_module', 'judul_module', 'deskripsi', 'id_module_status'];
			foreach ($fields as $val) {
				$$val = '';
			}
		}

		$permission_label_map = [
			'create' => 'Membuat data baru',
			'read_all' => 'Melihat semua data',
			'read_own' => 'Melihat data milik sendiri',
			'update_all' => 'Mengubah semua data',
			'update_own' => 'Mengubah data milik sendiri',
			'delete_all' => 'Menghapus semua data',
			'delete_own' => 'Menghapus data milik sendiri'
		];
		
		// Id Module
		$id = '';
		$postId = $request->getPost('id');
		$getId = $request->getGet('id');
		if (!empty($postId)) {
			$id = $postId;
		} else if (!empty($getId)) {
			$id = $getId;
		} elseif (!empty($id)) { // ADD Auto Increment
			$id = $id;
		} 
		?>
		<form method="post" action="">
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Module</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="nama_module" value="<?=set_value('nama_module', @$nama_module)?>" placeholder="Nama Module" required/>
					<small>Sesuai nama yang ada di URL.</small>
					<input type="hidden" name="nama_module_old" value="<?=set_value('nama_module', @$nama_module)?>">
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Judul Module</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="judul_module" value="<?=set_value('judul_module', @$judul_module)?>" placeholder="Nama Module" required/>
					<span id="judul-module" style="display:none"><?=@$judul_module?></span>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Deskripsi</label>
				<div class="col-sm-5">
					<input class="form-control" type="text" name="deskripsi" value="<?=set_value('deskripsi', @$deskripsi)?>" placeholder="Deskripsi"/>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Login</label>
				<div class="col-sm-5">
					<?php
					echo options(['name' => 'login'], ['Y' => 'Ya', 'N' => 'Tidak', 'R' => 'Restrict'], ['login', @$login])?>
					<small>Apakah untuk mengakses module perlu login? Restrict berarti untuk mengakses module, posisi tidak boleh login, jika posisi sedang login, module tidak bisa diakses (halaman akan diarahkan ke default module), contoh module login dan register.</small>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Status</label>
				<div class="col-sm-5">
					<?php 
					foreach ($module_status as $item) {
						$options[$item['id_module_status']] = $item['nama_status'];
					}
					echo options(['name' => 'id_module_status'], $options, ['id_module_status', @$id_module_status])?>
				</div>
			</div>
			
			<?php
			
			if (empty($id)) {
				?>
				<div class="module-permission-panel mt-4">
					<div class="module-permission-panel-header">
						<div>
							<h5 class="mb-1">Permission</h5>
							<p class="text-muted mb-0">Tentukan paket permission awal agar module langsung siap dipakai.</p>
						</div>
					</div>
					<div class="module-permission-grid">
						<div class="permission-guide-card">
							<h6>Template Permission</h6>
							<p class="text-muted">Pilih paket akses sesuai skenario penggunaan module.</p>
							<?=options(['name' => 'generate_permission', 'class' => 'form-select']
										, ['' => 'Tidak', 'crud_all' => 'CRUD All', 'crud_own' => 'CRUD Own', 'crud_all_crud_own' => 'CRUD + CRUD Own']
										, set_value('generate_permission', @$generate_permission) 
									)?>
							<div class="permission-guide-list mt-3">
								<div class="permission-guide-item">
									<strong>CRUD All</strong>
									<small>Create, read_all, update_all, delete_all</small>
								</div>
								<div class="permission-guide-item">
									<strong>CRUD Own</strong>
									<small>Read_own, update_own, delete_own</small>
								</div>
								<div class="permission-guide-item">
									<strong>Catatan</strong>
									<small>Permission yang sudah ada tidak akan dibuat ulang.</small>
								</div>
							</div>
						</div>
						<div class="permission-guide-card">
							<h6>Role Awal</h6>
							<p class="text-muted">Assign role default agar permission hasil generate langsung terhubung.</p>
							<?php
							$options = [];
							$options[''] = 'Tidak';
							foreach ($roles as $val) {
								$options[$val['id_role']] = $val['judul_role'];
							}
							echo options(['name' => 'id_role', 'class' => 'form-select'], $options, set_value('id_role', ''));
							?>
							<div class="permission-guide-list mt-3">
								<div class="permission-guide-item">
									<strong>Tujuan</strong>
									<small>Role terpilih akan menerima permission module sesuai template di kiri.</small>
								</div>
								<div class="permission-guide-item">
									<strong>Saran</strong>
									<small>Pilih role utama terlebih dahulu, lalu detailnya bisa disesuaikan setelah module dibuat.</small>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php
			}
			
			?>
			<input type="hidden" name="id" value="<?=$id?>"/>
			<button type="submit" name="submit" value="submit" class="btn btn-primary mt-2">Save</button>
		</form>
		<?php
	
		if ($id) {
			?>
			<div class="module-permission-panel mt-4">
				<div class="module-permission-panel-header">
					<div>
						<h5 class="mb-1">Permission</h5>
						<p class="text-muted mb-0">Kelola permission module dan distribusinya ke setiap role dengan tampilan yang lebih ringkas.</p>
					</div>
				</div>

				<div class="permission-summary-card mb-4">
					<div class="permission-summary-head">
						<div>
							<h6 class="mb-1">Module Permission</h6>
							<p class="text-muted mb-0">Permission dasar yang tersedia untuk module ini.</p>
						</div>
						<a href="javascript:void(0)" class="btn btn-outline-success btn-sm add-module-permission" data-id-module="<?=$id?>">
							<i class="fas fa-plus me-1"></i>Tambah Permission
						</a>
					</div>
					<?php
					$display = $module_permission ? '' : ' style="display:none"';
					echo '<div class="module-permission-container"' . $display . '>';
					echo '<div class="permission-chip-list module-permission">';
					foreach ($module_permission as $val) {
						echo '<div class="permission-chip-item">
								<div class="permission-chip-content">
									<strong>' . $val['nama_permission'] . '</strong>
									<small>' . $val['judul_permission'] . '</small>
								</div>
								<a href="javascript:void(0)" title="Hapus permission ' . $val['nama_permission'] . '" class="delete-module-permission text-danger" data-url="' . base_url() . '/builtin/permission/ajaxDelete" data-id-permission="'. $val['id_module_permission'].'">
									<i class="fas fa-times"></i>
								</a>
							</div>';
					}
					echo '</div>';
					$display = count($module_permission) > 1 ? '' :  ' style="display:none"';
					echo '<a href="javascript:void(0)" class="small text-danger delete-all-module-permission"' . $display . ' data-id-module="' . $id . '"><i class="fas fa-times me-1"></i>Delete All Permission</a>';
					echo '</div>';
					if (!$module_permission) {
						echo '<div class="permission-empty-state module-permission-empty">
								<i class="fas fa-shield-alt"></i>
								<div>
									<strong>Belum ada permission module</strong>
									<small>Tambahkan permission dasar agar role bisa diberi akses yang sesuai.</small>
								</div>
							</div>';
					}
					?>
				</div>

				<div class="permission-summary-card">
					<div class="permission-summary-head">
						<div>
							<h6 class="mb-1">Role Module Permission</h6>
							<p class="text-muted mb-0">Setiap card di bawah menunjukkan permission yang dimiliki tiap role pada module ini.</p>
						</div>
					</div>
					<div class="role-permission-toolbar">
						<div class="role-permission-search">
							<i class="fas fa-search"></i>
							<input type="text" class="form-control" id="role-permission-search" placeholder="Cari role...">
						</div>
						<div class="role-permission-toolbar-actions">
							<button type="button" class="btn btn-light btn-sm" id="expand-all-role-permission">Expand All</button>
							<button type="button" class="btn btn-light btn-sm" id="collapse-all-role-permission">Collapse All</button>
						</div>
					</div>
					<div class="role-permission-summary">
						<div class="role-permission-summary-item">
							<strong id="visible-role-permission-count"><?=count($roles)?></strong>
							<small>Role Ditampilkan</small>
						</div>
						<div class="role-permission-summary-item">
							<strong><?=count(array_filter($role_permission_module))?></strong>
							<small>Role Dengan Permission</small>
						</div>
						<div class="role-permission-summary-item">
							<strong><?=count($roles) - count(array_filter($role_permission_module))?></strong>
							<small>Perlu Review</small>
						</div>
					</div>
					<div class="role-permission-grid">
						<?php
						foreach ($roles as $val_role) 
						{
							$count_permission = 0;
							if (key_exists($val_role['id_role'], $role_permission_module)) {
								$count_permission = count($role_permission_module[$val_role['id_role']]);
							}
							$is_expanded = $count_permission > 0 ? 'true' : 'false';
							$card_class = $count_permission > 0 ? ' is-expanded' : '';
							echo '<div class="role-permission-card role-permission-accordion'. $card_class .'" data-role-name="' . strtolower(esc($val_role['judul_role'])) . '" data-role-id="' . $val_role['id_role'] . '" data-permission-count="' . $count_permission . '">
									<div class="role-permission-card-head role-permission-toggle" role="button" tabindex="0" aria-expanded="' . $is_expanded . '">
										<div>
											<strong id="judul-role-' . $val_role['id_role'] . '">' . $val_role['judul_role'] . '</strong>
											<div class="role-permission-caption">Permission khusus untuk role ini pada module ' . $module['judul_module'] . '</div>
										</div>
										<div class="role-permission-card-meta">
											<span class="role-permission-badge">'. $count_permission .' permission</span>
											<a href="javascript:void(0)" class="btn btn-outline-success btn-sm add-role-module-permission" data-id-module="' . $id . '" data-id-role="' . $val_role['id_role'] . '">Edit Permission</a>
											<span class="role-permission-chevron"><i class="fas fa-chevron-down"></i></span>
										</div>
									</div>
									<div class="role-module-permission-container">';

							echo '<div class="permission-chip-list role-permission-chip-list" id="role-permission-'. $val_role['id_role'] . '">';
							if (key_exists($val_role['id_role'], $role_permission_module)) {
								foreach ($role_permission_module[$val_role['id_role']] as $key => $val_permission) 
								{
									echo '<div class="permission-chip-item" data-id-permission="'. $val_permission['id_module_permission'] . '">
											<div class="permission-chip-content">
												<strong>' . $val_permission['nama_permission'] . '</strong>
												<small>' . ($permission_label_map[$val_permission['nama_permission']] ?? 'Permission tambahan untuk aksi khusus') . '</small>
											</div>
											<a href="javascript:void(0)" title="Hapus permission ' . $val_permission['nama_permission'] . ' dari role ' . $val_role['judul_role'] .' pada module ' . $module['judul_module'] . '" class="delete-role-module-permission text-danger" data-url="' . base_url() . '/builtin/role-permission/ajaxDeletePermission" data-id-role="' . $val_role['id_role'] . '" data-id-permission="'. $val_permission['id_module_permission'].'">
												<i class="fas fa-times"></i>
											</a>
										</div>';
								}
							}
							echo '</div>';

							echo '<div class="permission-empty-state role-permission-empty"'. ($count_permission ? ' style="display:none"' : '') .'>
									<i class="fas fa-user-shield"></i>
									<div>
										<strong>Belum ada permission</strong>
										<small>Gunakan tombol edit untuk memilih akses yang relevan bagi role ini.</small>
									</div>
								</div>';

							$display = $count_permission > 1 ? '' :  ' style="display:none"';
							echo '<a'. $display .' href="javascript:void(0)" class="small text-danger delete-all-role-module-permission" data-id-role="' . $val_role['id_role'] . '" data-id-module="' . $id . '"><i class="fas fa-times me-1"></i>Delete All Permission</a>';
							echo '</div></div>';
						}
						?>
					</div>
				</div>
			</div>
		<?php
		}
		?>
	</div>
</div>
