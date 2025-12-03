<div class="card">
	<div class="card-header">
		<h5 class="card-title">Detail Module</h5>
	</div>
	
	<div class="card-body">
		<?php 
		if (!$role) {
			show_message('Data tidak ditemukan', '', false);
		} else {
			?>
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
						
						$action = ['read_data' => 'Read', 'create_data' => 'Create', 'update_data' => 'Update', 'delete_data' => 'Delete'];
						$yes_no = ['yes' => 'yes | YA', 'no' => 'no | TIDAK'];
					
						echo '<ul>';
						foreach ($role as $role_val) 
						{
							echo '<li><strong>' . $role_val['judul_role'] . '</strong></li>';
							
							if (key_exists($role_val['id_role'], $module_role_check)) {

								echo '<ul class="circle ms-4">';
								foreach ($action as $key => $val_action) 
								{
									$act = $module_role_check[$role_val['id_role']][$key]; // all, own, no
									if (key_exists($act, $yes_no)) {
										$ket = $yes_no[$act];
									} else {
										$ket =$role_detail_options[$act];
									}
									echo '<li><p class="mt-1 mb-1"><span  style="display:inline-block;width:100px">'. $val_action . '</span>: ' . $ket .'</p></li>';
								}
								echo '
									</ul>';
							} else {
								echo '<ul class="circle ms-3"><li><p class="mt-1 mb-1">Role belum di set</p></li></ul>';
							}
						}
						echo '</ul>';
						?>
						
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
</div>