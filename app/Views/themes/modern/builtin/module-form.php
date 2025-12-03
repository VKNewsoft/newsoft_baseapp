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
				<div class="bg-lightgrey p-3 mt-4 mb-4 ps-4">
					<h5>Permission</h5>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Module Permission</label>
					<div class="col-sm-5">
						<?=options(['name' => 'generate_permission']
									, ['' => 'Tidak', 'crud_all' => 'CRUD All', 'crud_own' => 'CRUD Own', 'crud_all_crud_own' => 'CRUD + CRUD Own']
									, set_value('generate_permission', @$generate_permission) 
								)?>
						<small>Tambah permission pada module. Permission CRUD: create, read_all, update_all, delete_all (<u>jika permission sudah ada, maka tidak akan dibuat</u>). CRUD Own: read_own, update_own, dan delete_own</small>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Module Role</label>
					<div class="col-sm-5">
						<?php
						$options = [];
						$options[''] = 'Tidak';
						foreach ($roles as $val) {
							$options[$val['id_role']] = $val['judul_role'];
						}
						echo options(['name' => 'id_role'], $options, set_value('id_role', ''));
						?>
						<small>Assign role pada module. Role akan memiliki module permission sesuai permission diatas</small>
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
			<div class="bg-lightgrey p-3 mt-4 mb-4 ps-4">
				<h5>Permission</h5>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Module Permission</label>
				<div class="col-sm-5">
					<?php
					$display = $module_permission ? '' : ' style="display:none"';
					echo '
					<div class="module-permission-container"' . $display . '>
						Permission pada module ini:
						<ul class="list-circle module-permission">';
					foreach ($module_permission as $val) {
						echo '<li><small>' . $val['nama_permission'] . ' (' . $val['judul_permission'] . ')</small>
								<a href="javascript:void(0)" title="Hapus permission ' . $val['nama_permission'] . '" class="delete-module-permission text-danger" data-url="' . base_url() . '/builtin/permission/ajaxDelete" data-id-permission="'. $val['id_module_permission'].'">
									<i class="ms-2 fas fa-times"></i>
								</a>
						</li>';
					}
					
					$display = count($module_permission) > 1 ? '' :  ' style="display:none"';
					echo '</ul>
						<a href="javascript:void(0)" class="ms-2 small text-danger delete-all-module-permission"' . $display . ' data-id-module="' . $id . '"><i class="fas fa-times"></i>&nbsp;&nbsp;Delete All Permission</a>
					</div>';
					
					echo '<a href="javascript:void(0)" class="text-success add-module-permission" data-id-module="' . $id . '"><small><i class="fas fa-plus"></i>&nbsp;&nbsp;Tambah Permission</small></a>';
					?>
				</div>
			</div>
			<div class="row mb-3">
				<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Role Module Permission</label>
				<div class="col-sm-5">
					<?php
					// ROLE
					$module_role_check = [];
					if ( $roles ) {
						foreach ($roles as $val) {
							$module_role_check[$val['id_role']] = $val;
						}
					}
					
					echo '<div>';
					foreach ($roles as $val_role) 
					{						
						echo '
						<div><strong id="judul-role-' . $val_role['id_role'] . '">' . $val_role['judul_role'] . '</strong> <small>|</small> <a href="javascript:void(0)" class="text-success add-role-module-permission" data-id-module="' . $id . '" data-id-role="' . $val_role['id_role'] . '"><small>Edit Permission</small></a></div>
						<div class="ms-2 role-module-permission-container">
							<ul class="list-circle" id="role-permission-'. $val_role['id_role'] . '">';
							$count_permission = 0;
							if (key_exists($val_role['id_role'], $role_permission_module)) {
								foreach ($role_permission_module[$val_role['id_role']] as $key => $val_permission) 
								{
									echo '<li data-id-permission="'. $val_permission['id_module_permission'] . '"><small>' . $val_permission['nama_permission'] . '</small>
										<a href="javascript:void(0)" title="Hapus permission ' . $val_permission['nama_permission'] . ' dari role ' . $val_role['judul_role'] .' pada module ' . $module['judul_module'] . '" class="delete-role-module-permission text-danger" data-url="' . base_url() . '/builtin/role-permission/ajaxDeletePermission" data-id-role="' . $val_role['id_role'] . '" data-id-permission="'. $val_permission['id_module_permission'].'">
											<i class="ms-2 fas fa-times"></i>
										</a>
									</li>';
									$count_permission++;
								}
								
							}
							echo '
							</ul>';
							
							$display = $count_permission > 1 ? '' :  ' style="display:none"';
							echo '<a'. $display .' href="javascript:void(0)" class="ms-2 small text-danger delete-all-role-module-permission" data-id-role="' . $val_role['id_role'] . '" data-id-module="' . $id . '"><i class="fas fa-times"></i>&nbsp;&nbsp;Delete All Permission</a>';
						echo '
						</div>';
					}

					echo '</div>';
					?>
					</div>
			</div>
		<?php
		}
		?>
	</div>
</div>