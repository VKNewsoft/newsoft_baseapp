<?php

helper('html');
// echo '<pre>'; print_r($module);die;
?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<a href="<?=current_url()?>/add" class="btn btn-success btn-xs add" id="add-permission"><i class="fa fa-plus pe-1"></i> Tambah Permission</a>
		<hr/>
		<?php 
		if (!empty($msg)) {
			show_alert($msg);
		}
			
		$column =[
					// 'ignore_search_urut' => 'No'
					 'ignore_search_action' => 'Action'
					, 'judul_module' => 'Nama Module'
					, 'nama_permission' => 'Nama Permission'
					, 'judul_permission' => 'Judul Permission'
					, 'keterangan' => 'Keterangan'
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
		</table>
		<?php
			foreach ($column as $key => $val) {
				$column_dt[] = ['data' => $key];
			}
		?>
		<span id="dataTables-column" style="display:none"><?=json_encode($column_dt)?></span>
		<span id="dataTables-setting" style="display:none"><?=json_encode($settings)?></span>
		<span id="dataTables-url" style="display:none"><?=current_url() . '/getDataDT'?></span>
		<span id="dataTables-scrolls" style="display:none">510</span>
	</div>
</div>