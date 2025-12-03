<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$current_module['judul_module']?> - Provinsi</h5>
	</div>
	<div class="card-body">
	<?php
	if (!empty($msg)) {
		show_alert($msg);
	}
		
	helper('html');
	
	// Tombol Tambah
	echo btn_link([
		'attr' => ['class' => 'btn btn-success btn-xs'],
		'url' => current_url() . '/add',
		'icon' => 'fa fa-plus',
		'label' => 'Tambah Provinsi'
	]);
	
	// Tombol Kembali
	echo btn_link([
		'attr' => ['class' => 'btn btn-light btn-xs'],
		'url' => current_url(),
		'icon' => 'fa fa-arrow-circle-left',
		'label' => 'Daftar Provinsi'
	]);
	?>
	<hr/>
	<div class="table-responsive">
		<?php
		if (!empty($message)) {
			show_message($message);
		}
		
		// Definisi kolom untuk DataTables
		$column = [
			'ignore_btn_action' => 'Aksi',
			'id_wilayah_propinsi' => 'ID',
			'nama_propinsi' => 'Nama Provinsi'
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
		// Settings untuk DataTables
		$settings['order'] = [2, 'asc'];
		$index = 0;
		foreach ($column as $key => $val) {
			$column_dt[] = ['data' => $key];
			// Kolom dengan prefix 'ignore' tidak bisa di-sort
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
