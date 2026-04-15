<div class="card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php
			helper ('html');
		?>
		<div class="gallery-container">
			<?php
			if (!empty($message)) {
				show_message($message);
			} else {
				//  . options(['name' => 'filter_file', 'class' => 'border-secondary filter-file'], $filter_file, set_value('filter_file', 'all')) 
				//  . options(['name' => 'filter_tgl', 'class' => 'border-secondary filter-tgl'], array_merge(['' => 'Semua Tanggal'], $filter_tgl), set_value('filter_tgl', 'all'))
				$display = !$result['data'] ? ' style="display: none"' : '';
				echo '
				<nav class="nav-util">
					<div class="mb-3 form-inline toolbox-bar">
						<div class="toolbox-left"' . $display . '>
							<input id="search-file" class="form-control search-file" type="search" placeholder="Search..."/>
							<button class="btn btn-outline-secondary btn-select-files"><i class="far fa-check-square me-2"></i>Select Files</button>
							<button class="btn btn-outline-secondary btn-cancel-select-files" style="display:none"><i class="fas fa-arrow-circle-left me-2"></i>Cancel</button>
							<button class="btn btn-outline-danger btn-delete-checked disabled" style="display:none" disabled><i class="fas fa-trash-alt me-2"></i><span class="text">Delete Checked</span><span class="num-files"></span></button>
							<button class="btn btn-danger btn-delete-all"><i class="fas fa-trash-alt me-2"></i>Hapus Semua Data</button>
							
							
						</div>
						<div class="toolbox-right">
							<button class="btn btn-success btn-upload-files"><i class="far fa-check-square me-2"></i>Upload Files</button>
						</div>
					</div>
				</nav>
				<hr/>';
				
				?>
				<div id="dropzone-container" style="display:none">
				<form action="<?=$config->baseURL?>filepicker/ajaxUploadFile" class="dropzone-area" id="form-dropzone">
						<div class="dz-message dz-default needsclick">
							<div><i class="fas fa-cloud-upload-alt"></i></div>
							<div>Drag &amp; Drop File Disini <br> (Hanya Type Images Yang Akan Terupload) <br> <font color="red">Jika berhasil terupload bar progress akan berwarna hijau</font></div>
						</div>
						<div class="preview-container dz-preview uploaded-files" style="display:none">
							<div id="file-previews">
								<div id="dropzone-preview-template">
									<div class="dropzone-info">
										<div class="uploaded-thumb"><img data-dz-thumbnail/></div>
										<div class="details">
											<div class="file-info">
												<span data-dz-name></span> (<span data-dz-size></span>)<span class="progress-text"></span>
											</div>
											<div class="dz-progress progress"><div class="dz-upload progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width:0" data-dz-uploadprogress></div></div>
											<button class="btn btn-close" data-dz-remove><i class="fas fa-close"></i></button>
										</div>
										<div class="dz-error-message"><span data-dz-errormessage></span></div>
									</div>
								</div>
							</div>
						</div>
						<div id="nsd-dz-error">
						</div>
						
					</form>
					<hr/>
				</div>
				<?php
				echo '
				<div class="thumb-container">';
					
					$initial_item = false;
					if (!$result['data']) {
						echo '<div class="alert alert-danger error-data-notfound">Data tidak ditemukan</div>';
						$result['data'][] = ['file_type' => 'image'
												, 'id_file_picker' => 1
												, 'nama_file' => ''
												, 'thumbnail' => ['url' => '']
											];
						$initial_item = true;
					}
					
					
				echo '<ul id="list-file-container" class="list-file-container">';
			
				foreach ($result['data'] as $val) {
					
					$img_class = $val['file_type'] != 'image' ? ' file-thumbnail' : '';
					$data_initial_item = $initial_item ? ' data-initial-item="true"' : '';
					$display = $initial_item ? ' style="display:none"' : '';
					?>
					<li class="thumbnail-item" id="file-<?=$val['id_file_picker']?>"<?=$data_initial_item?><?=$display?>>
						
						<div class="toolbox">
							<ul class="right-menu">
								<li><a class="bg-danger text-white btn-delete-file" href="javascript:void(0)"><i class="fas fa-times"></i></a></li>
							</ul>
						</div>
						<?php
						
							$show_filename = '';
							if ($val['file_type'] == 'image') {
								$show_filename = ' style="display: none"';
							}
								/* echo '<span class="extension-badge shadow" style="background:' . $val['thumbnail']['extension_color'] . '; display:none">' . 
										$val['thumbnail']['extension'] . 
									'</span>'; */
		
							echo '
								<span class="filename-container"' . $show_filename . '>
									<span class="filename">' . $val['nama_file'] . '</span>
									<span class="filename-backdrop"></span>
								</span>';
							
							echo '<span class="extension-badge shadow" style="display:none"></span>';
							?>
						<div class="img-container">
							<img class="nsd-img-thumbnail<?=$img_class?>" src="<?=$val['thumbnail']['url']?>" />
							<span class="meta-file" style="display:none"></span>
						</div>
						<div class="file-checked" style="display:none">
							<i class="fas fa-check"></i>
						</div>
					</li>	
				<?php 
				} 
				echo '</ul>
				</div>
				<div class="loading-status bg-success shadow-sm" style="display:none">Showing: <span class="loading-item">' . $loaded_item . '</span> / <span class="total-item">' . $total_item . '</span></div>';
			} ?>
		</div>
	</div>
</div>
<script type="text/javascript">
var item_per_page = <?=$item_per_page?>; 
var loading_item = <?=$loaded_item?>; 
var total_item = <?=$total_item?>
</script>