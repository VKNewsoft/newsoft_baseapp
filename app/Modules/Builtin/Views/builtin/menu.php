<?php
helper('html');?>
<div class="card">
	<div class="card-header">
		<h5 class="card-title">Data Menu</h5>
	</div>
	<div class="card-body d-flex flex-column flex-lg-row gap-4">
		<div class="kategori-container flex-fill">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h6 class="mb-0"><i class="fas fa-tags me-2"></i>Kategori Menu</h6>
				<a href="?module=gedung&action=add" class="btn btn-primary btn-sm" id="add-kategori"><i class="fa fa-plus pe-1"></i> Tambah Kategori</a>
			</div>
			<div id="list-kategori">
				<ul class="list-group menu-kategori-container" id="list-kategori-container">
					<?php
					
					foreach ($menu_kategori as $index => $val) {
						$bg_color = 'style="background-color: #c0f5bc;"';
						$status_icon = '<i class="fas fa-check-circle text-success"></i>';
						if($val['aktif'] == 'N'){
							$bg_color  = 'style="background-color: #f5bcbc;"';
							$status_icon = '<i class="fas fa-times-circle text-danger"></i>';
						}
						$active = $index == 0 ? 'list-group-item-primary' : ''; 
						echo '<li '.$bg_color.' data-id-kategori="' . $val['id_menu_kategori'] . '" class="kategori-item list-group-item list-group-item-action ' . $active . '">
								<div class="d-flex justify-content-between align-items-center">
									<div class="d-flex align-items-center">
										'.$status_icon.'
										<span class="text ms-2">' . $val['nama_kategori'] . '</span>
									</div>
									<ul class="toolbox d-flex">
										<li class="me-2">
											<a class="btn-action text-success btn-edit" href="javascript:void(0)" title="Edit"><i class="fas fa-edit"></i></a>
										</li>
										<li>
											<a class="btn-action text-danger btn-remove" href="javascript:void(0)" title="Hapus"><i class="fas fa-trash"></i></a>
										</li>
									</ul>
								</div>
							</li>';
						
					}
					?>
					<li data-id-kategori="" class="kategori-item list-group-item list-group-item-action" id="kategori-item-template" style="display:none">
						<div class="d-flex justify-content-between align-items-center">
							<div class="d-flex align-items-center">
								<i class="fas fa-check-circle text-success"></i>
								<span class="text ms-2"></span>
							</div>
							<ul class="toolbox d-flex">
								<li class="me-2">
									<a class="btn-action text-success btn-edit" href="javascript:void(0)" title="Edit"><i class="fas fa-edit"></i></a>
								</li>
								<li>
									<a class="btn-action text-danger btn-remove" href="javascript:void(0)" title="Hapus"><i class="fas fa-trash"></i></a>
								</li>
							</ul>
						</div>
					</li>
					<li data-id-kategori="" class="kategori-item list-group-item list-group-item-action list-group-item-secondary uncategorized">
						<div class="d-flex justify-content-between align-items-center">
							<div class="d-flex align-items-center">
								<i class="fas fa-folder-open text-muted"></i>
								<span class="text ms-2">Uncategorized</span>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
		<div class="menu-container flex-fill">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h6 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Menu</h6>
				<a href="?module=gedung&action=add" class="btn btn-success btn-sm" id="add-menu"><i class="fa fa-plus pe-1"></i> Tambah Menu</a>
			</div>
			<div class="dd" id="list-menu">
				<?= $list_menu ?: '<div class="alert alert-danger">Data tidak ditemukan</div>'?>
			</div>
		</div>
	</div>
</div>

<style>
/* Clean and Informative Menu Layout */
.card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    background: #fff;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
    padding: 20px 30px;
}

.card-header h5 {
    margin: 0;
    font-weight: 600;
    font-size: 1.5rem;
}

.card-body {
    padding: 30px;
}

.kategori-container, .menu-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.kategori-container:hover, .menu-container:hover {
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.kategori-container h6, .menu-container h6 {
    color: #495057;
    font-weight: 600;
}

.btn-primary, .btn-success {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 25px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.btn-success {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.btn-primary:hover, .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.list-group-item {
    border: none;
    border-radius: 10px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    padding: 15px;
}

.list-group-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.list-group-item-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    color: white;
    border: none;
}

.list-group-item-primary .text, .list-group-item-primary i {
    color: white !important;
}

.toolbox {
    margin: 0;
    padding: 0;
    list-style: none;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.kategori-item:hover .toolbox {
    opacity: 1;
}

.btn-action {
    transition: all 0.3s ease;
    padding: 5px;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-action:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: scale(1.1);
}

.dd-handle {
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.dd-handle:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.menu-baru {
    background: #ff6b6b;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: bold;
    margin-left: auto;
}

.alert-danger {
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
    color: #721c24;
}

@media (max-width: 991px) {
    .card-body {
        flex-direction: column;
    }
}
</style>