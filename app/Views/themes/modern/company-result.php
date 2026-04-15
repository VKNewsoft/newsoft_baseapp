<div class="result-page-shell">
	<div class="result-page-hero">
		<div>
			<div class="result-page-kicker">Company</div>
			<h3 class="result-page-heading"><?=$current_module['judul_module']?></h3>
			<p class="result-page-copy mb-0">Kelola company, bank, unit, dan status aktif dalam result page yang lebih rapat dan tetap nyaman untuk proses popup AJAX.</p>
		</div>
		<div class="result-page-actions">
			<a href="<?=current_url()?>/add" class="btn btn-success btn-sm btn-add"><i class="fa fa-plus pe-1"></i> Tambah Data</a>
		</div>
	</div>

	<div class="card result-list-card">
		<div class="card-body p-0">
			<div class="result-list-toolbar">
				<div>
					<h5 class="mb-1">Daftar Company</h5>
					<p class="mb-0 text-muted">Data unit kerja, rekening, dan status company tetap bisa diproses lewat modal tanpa membuat tabel melewati batas halaman.</p>
				</div>
			</div>

			<div class="table-responsive result-table-wrap result-table-region">
				<?php 
				if (!empty($msg)) {
					show_alert($msg);
				}
					
				$column =[
						'ignore_search_urut' => 'No'
						, 'nama_company' => 'Nama Company'
						, 'kode_lokasi' => 'Unit'
						, 'deskripsi' => 'Deskripsi'
						, 'nama_bank' => 'Bank'
						, 'no_rekening' => 'No. Rekening'
						, 'tenant_aktif' => 'Status'
						, 'ignore_search_action' => 'Action'
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
				<table id="table-data" class="table display nowrap table-striped table-bordered table-hover align-middle mb-0" style="width:100%">
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
				<span id="dataTables-scrolls" style="display:none">350</span>
			</div>
		</div>
	</div>
</div>
