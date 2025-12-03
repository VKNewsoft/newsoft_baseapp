<div class="card">
	<div class="card-header">
		<h5 class="card-title">Role</h5>
	</div>
	<div class="card-body">
	<?php
		
	helper ('html');
		echo btn_link([
			'attr' => ['class' => 'btn btn-success btn-xs'],
			'url' => current_url() . '/add',
			'icon' => 'fa fa-plus',
			'label' => 'Tambah Role'
		]);
		
		echo btn_link([
			'attr' => ['class' => 'btn btn-light btn-xs'],
			'url' => current_url(),
			'icon' => 'fa fa-arrow-circle-left',
			'label' => 'Daftar Role'
		]);
	?>
	<hr/>
	<div class="table-responsive">
		<?php

		if (!empty($message)) {
			show_message($message);
		}
		
		$column =[
					 'ignore_action' => 'Aksi'
					// , 'ignore_no_urut' => 'No.'
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
		<table id="table-result" class="table display nowrap table-striped table-bordered" style="width:100%">
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
	</div>
	</div>
</div>