<?php

helper('html');
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=$module_url?>" class="btn btn-light btn-xs" id="add-menu"><i class="fa fa-arrow-circle-left pe-1"></i> Daftar Role Permission</a>
		<hr/>
		<div class="row mb-3">
			<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Nama Role</label>
			<div class="col-sm-8">
				<?=$role['nama_role']?>
				<input type="hidden" id="id-role" name="id_role" value="<?= esc($request->getGet('id') ?? '') ?>"/>
			</div>
		</div>
		<div class="row mb-4">
			<label class="col-sm-3 col-md-2 col-lg-3 col-xl-2 col-form-label">Assign All Permission</label>
			<div class="col-sm-8">
				<?php
				$checked = $has_all_permission ? 'checked' : '';
				?>
				<div class="form-check-input-sm form-switch">
					<input name="aktif" type="checkbox" class="form-check-input assign-all-permission" data-id-module-permission="26" value="1" <?=$checked?>></div>
				<div class="text-muted">Assign/Unassign semua permission ke role <?=$role['nama_role']?></div>
			</div>
		</div>
		<hr/>
		<?php			
		$column =[
					'ignore_urut' => 'No'
					, 'judul_module' => 'Judul Module'
					, 'nama_module' => 'Nama Module'
					, 'nama_permission' => 'Nama Permission'
					, 'judul_permission' => 'Judul Permission'
					, 'keterangan' => 'Keterangan'
					, 'id_role' => 'Assign'
				];
		
		$settings['order'] = [1,'asc'];
		$index = 0;
		$th = '';
		foreach ($column as $key => $val) {
			$th .= '<th>' . $val . '</th>'; 
			if (strpos($key, 'ignore_search') !== false) {
				$settings['columnDefs'][] = ["targets" => $index, "orderable" => false];
			}
			$index++;
		}
		
		?>
		
		<table id="table-result" class="table display table-striped table-bordered table-hover" style="width:100%">
		<thead>
			<tr>
				<?=$th?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?=$th?>
			</tr>
		</tfoot>
		</table>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=base_url() . '/builtin/role-permission/getDataDTPermission?id=' . esc($request->getGet('id') ?? '')?></span>
	</div>
</div>