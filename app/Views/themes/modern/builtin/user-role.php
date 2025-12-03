<div class="card">
	<div class="card-header">
		<h5 class="card-title">User Role</h5>
	</div>
	<div class="card-body">
	<div class="table-responsive">
		<?php

		if (!empty($message)) {
			show_message($message);
		}
		
		$column =[
					// 'ignore_no_urut' => 'No.'
					 'ignore_action' => 'Aksi'
					, 'username' => 'Menu'
					, 'nama' => 'Nama'
					, 'email' => 'Email'
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
			$settings['order'] = [2,'asc'];
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