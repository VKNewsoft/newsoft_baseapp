<?php

helper('html');?>

<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		?>
		<form method="post" class="modal-form" id="add-form" action="<?=current_url(true)?>" >
			<div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Module</label>
					<div class="col-sm-8 form-inline">
						<?=$module['judul_module']?>
					</div>
				</div>
				<div class="row mb-3">
					<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Role</label>
					<div class="col-sm-8">
						
						<?php 
						foreach ($role_detail as $val) {
							$role_detail_options[$val['nama_role_detail']] = $val['nama_role_detail'] . ' | ' . $val['judul_role_detail'];
						}
						
						$module_role_check = [];
						foreach ($module_role as $val) {
							$module_role_check[$val['id_role']] = $val;
						}
						
						echo '<div id="check-all-wrapper">';
					foreach ($role as $role_val) 
					{
						$checkbox = ['attr' => ['class' => 'toggle-role', 'id' => $role_val['id_role'], 'name' => 'role_' . $role_val['id_role']], 'label' => $role_val['judul_role']];
						
						$checked = [];
						if (isset($request->getPost('role_' . $role_val['id_role']))) {
							$checked = ['role_' . $role_val['id_role']];
						} else {
							if (key_exists($role_val['id_role'], $module_role_check)) {
								$checked[] = 'role_' . $role_val['id_role'];
							}
						}
						// echo '<pre>';print_r($checked);
						echo checkbox($checkbox, $checked);
						$display = !$checked ? ' style="display:none"' : '';

						$action = ['read_data' => 'Read', 'create_data' => 'Create', 'update_data' => 'Update', 'delete_data' => 'Delete'];
						
						echo '<div class="ms-4 role-child" ' . $display . '>';
									foreach ($action as $key => $val_action) 
									{
										$selected = '';
										if (isset($request->getPost('role_' . $role_val['id_role']))) {
											$selected = $request->getPost('akses_' . $key . '_' . $role_val['id_role']);
										} else {
											if (key_exists($role_val['id_role'], $module_role_check)) {
												$selected = $module_role_check[$role_val['id_role']][$key];
											}
										}											if ($key == 'create_data') {
												$options = options(['name' => 'akses_' . $key . '_' . $role_val['id_role']], ['yes' => 'yes | YA', 'no' => 'no | TIDAK'], $selected, false);
											} else {
												$options = options(['name' => 'akses_' . $key . '_' . $role_val['id_role']], $role_detail_options, $selected, false);
											}
											echo '
											<div class="mb-3 row form-inline">
											<div class="col-sm-2">'.$val_action.'</div>
											<div class="col-sm-8 form-inline">' . $options . '</div>
											</div>';
										}
							echo '
								</div>';
						}
							
						
						
						
						echo '</div>';
						?>
						
					</div>
				</div>
				
				<?php 
				$id = '';
				if (!empty($msg['id_module'])) {
					$id = $msg['id_module']; // Setelah submit add
				} elseif (!empty($request->getGet('id'))) {
					$id = $request->getGet('id');
				} ?>
				<input type="hidden" name="id" value="<?=$id?>"/>
				<button type="submit" name="submit" value="submit" class="btn btn-primary mt-2">Save</button>
				<?php
					$form_token = $auth->generateFormToken('form_edit');
				?>
				<input type="hidden" name="form_token" value="<?=$form_token?>"/>
			</div>
		</form>
	</div>
</div>