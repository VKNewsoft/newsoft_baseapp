<?php

helper('html');
// echo '<pre>'; print_r($module);die;
?>
<div class="result-page-shell">
	<div class="result-page-hero">
		<div>
			<div class="result-page-kicker">Builtin / Permission</div>
			<h3 class="result-page-heading"><?=$title?></h3>
			<p class="result-page-copy mb-0">Kelola permission per modul dengan tampilan result yang lebih ringkas sehingga inspeksi dan maintenance lebih cepat.</p>
		</div>
		<div class="result-page-actions">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-sm add" id="add-permission"><i class="fa fa-plus pe-1"></i> Tambah Permission</a>
		</div>
	</div>

	<div class="card result-list-card">
		<div class="card-body p-0">
			<div class="result-list-toolbar">
				<div>
					<h5 class="mb-1">Daftar Permission</h5>
					<p class="mb-0 text-muted">Nama permission, judul tampilan, dan keterangannya tetap terstruktur dalam area tabel yang terkunci di viewport.</p>
				</div>
			</div>

			<div class="table-responsive result-table-wrap result-table-region">
				<?php 
				if (!empty($msg)) {
					show_alert($msg);
				}
					
				$column =[
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
				
				<table id="table-result" class="table display nowrap table-striped table-bordered table-hover align-middle mb-0" style="width:100%">
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
				<span id="dataTables-scrolls" style="display:none">400</span>
			</div>
		</div>
	</div>
</div>
