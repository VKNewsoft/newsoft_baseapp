<div class="card">
	<div class="card-header">
		<h5 class="card-title">Module Role</h5>
	</div>
	<div class="card-body">
	<div class="table-responsive">
		<hr/>
		<?php

		if (!empty($message)) {
			show_message($message);
		}
		
		$column =[
					'ignore_no_urut' => 'No.'
					, 'nama_module' => 'Nama Module'
					, 'judul_module' => 'Judul Module'
					, 'ignore_role' => 'Role'
					, 'ignore_action' => 'Aksi'
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
        <tfoot>
            <tr>
				<?=$th?>
            </tr>
        </tfoot>
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
	</div>
	</div>
</div>