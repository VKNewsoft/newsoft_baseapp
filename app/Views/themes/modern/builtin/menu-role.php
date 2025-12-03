<div class="card">
	<div class="card-header">
		<h5 class="card-title">Menu Role</h5>
	</div>
	<div class="card-body">
	<div class="table-responsive">
		<hr/>
		<?php

		if (!empty($message)) {
			show_message($message);
		}
		
		$column =[
					// 'ignore_no_urut' => 'No.'
					 'ignore_action' => 'Aksi'
					, 'nama_menu' => 'Menu'
					, 'url' => 'Url'
					, 'ignore_role' => 'Role'
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
		<span id="dataTables-scrolls" style="display:none">540</span>
	</div>
	</div>
</div>