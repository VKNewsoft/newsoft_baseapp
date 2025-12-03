<div class="card">
	<div class="card-header">
			<h5 class="card-title"><?=$current_module['judul_module']?></h5>
	</div>
	<div class="card-body">
	<div class="table-responsive">
		<a href="<?=current_url()?>/add" class="btn btn-success btn-xs"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		<hr/>
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		$column =[
					// 'ignore_avatar' => 'Avatar'
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
		<table id="table-result" class="table display nowrap table-striped table-bordered" style="width:100%">
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
		<span id="dataTables-scrolls" style="display:none">460</span>
	</div>
	</div>
</div>