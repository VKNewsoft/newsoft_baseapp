<!DOCTYPE HTML>
<html lang="en">
<head>
<title>KASIR</title>
<meta name="descrition" content="Kasir"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes" />
<link rel="manifest" href="manifest.json"/>
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?r='.time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css'?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap-custom.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/bootstrap-custom-pos-kasir.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/layout-mobile.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/layout-mobile-panel.css?r='.time()?>"/>

<!-- Data Tables -->
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css?r='.time()?>"/>
<!-- // Data Tables -->

<link rel="stylesheet" id="style-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="style-switch-sidebar" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css?r='.time()?>"/>
<link rel="stylesheet" id="font-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/'.$app_layout['font_family'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="font-size-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?r='.time()?>"/>
<link rel="stylesheet" id="logo-background-color-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css?r='.time()?>"/>

<?php
if (@$styles) {
	foreach($styles as $file) {
		if (is_array($file)) {
			
			$attr = '';
			if (key_exists('attr', $file)) {
				foreach ($file['attr'] as $attr_name => $attr_value) {
					$attr .= $attr_name . '="' . $attr_value . '"';
				}					
			}
				
			echo '<link rel="stylesheet" data-type="dynamic-resource-head" ' . $attr . ' type="text/css" href="'.$file['file'].'?r='.time().'"/>' . "\n";
		} else {
			echo '<link rel="stylesheet" data-type="dynamic-resource-head" type="text/css" href="'.$file.'?r='.time().'"/>' . "\n";
		}
	}
}

?>

<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
	var module_url = "<?=$module_url?>";
	var current_url = "<?=current_url()?>";
	var theme_url = "<?=$config->baseURL . '/public/themes/modern/builtin/'?>";
</script>

<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/themes/modern/builtin/js/functions.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/themes/modern/js/main-mobile.js?r='.time()?>"></script>

<!-- Data Tables -->
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js?r='.time()?>"></script>
<!-- // Data Tables -->

<!-- Dynamic scripts -->
<?php
if (@$scripts) {
	foreach($scripts as $file) {
		if (is_array($file)) {
			
			$attr = '';
			if (key_exists('attr', $file)) {
				foreach ($file['attr'] as $attr_name => $attr_value) {
					$attr .= $attr_name . '="' . $attr_value . '"';
				}					
			}
				
			if (@$file['print']) {
				echo '<script type="text/javascript" data-type="dynamic-resource-head" ' . $attr . '>' . $file['script'] . '</script>' . "\n";
			} else {
				echo '<script type="text/javascript" data-type="dynamic-resource-head" ' . $attr . ' src="'.$file['script'].'?r='.time().'"></script>' . "\n";
			}
		} else {
			echo '<script type="text/javascript" data-type="dynamic-resource-head" src="'.$file.'?r='.time().'"></script>' . "\n";
		}
	}
}
?>
<!-- Config PWA -->
<link rel="manifest" href="<?=$config->baseURL . 'manifest.json'?>"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#f45">
<link rel="shortcut icon" href="<?=$config->baseURL?>/public/images/<?=$setting_aplikasi['favicon']?>" type="image/png">
<link rel="apple-touch-icon" href="<?=$config->baseURL?>/public/images/<?=$setting_aplikasi['favicon']?>" type="image/png">
<head>
<body>
	
	<div class="page-container" id="page-container">
		<div id="page-content">
		<?php
		$this->renderSection('content');
		?>
		</div>
	</div> <!-- Page Container -->
	<?php
	// echo '<pre>'; print_r($user); die;
	$session = \Config\Services::session();
	$nama_module = $session->get('web')['nama_module'];
	$active_kasir = strpos($nama_module, 'kasir') !== false ? 'active' : '';
	$active_penjualan = ($nama_module == 'penjualan-mobile') ? 'active' : '';
	$active_penjualan_qris = strpos($nama_module, 'penjualan-mobile-qris') !== false ? 'active' : '';
	$active_closing = strpos($nama_module, 'closing-mobile') !== false ? 'active' : '';;
	$active_barang = strpos($nama_module, 'barang-mobile') !== false ? 'active' : '';
	?>
	<nav class="navbar navbar-dark navbar-footer navbar-expand fixed-bottom">
		<ul class="navbar-nav">
			<li class="nav-item bg-primary">
			<a id="btn-menu-mobile" class="nav-link nav-menu-mobile px-4" data-bs-toggle="offcanvas" href="#offcanvasExample" role="button" aria-controls="offcanvasExample"><i class="fa fa-bars"></i></a>
		  </li>
		</ul>
		<ul class="navbar-nav nav-justified w-100">
		  <li class="nav-item bg-primary">
			<a href="<?=base_url()?>/pos-kasir" id="menu-kasir" class="nav-link <?=$active_kasir?> link-spa"><i class="fas fa-calculator"></i><span class="hide-mobile ms-2">Kasir</span></a>
		  </li>
		  <li class="nav-item bg-primary">
			<a href="<?=base_url()?>/penjualan-mobile" id="menu-invoice" class="nav-link <?=$active_penjualan?> link-spa"><i class="fas fa-receipt"></i><span class="hide-mobile ms-2">Transaksi</span></a>
		  </li>
		  <li class="nav-item bg-primary">
			<a href="<?=base_url()?>/closing-mobile" id="menu-closing" class="nav-link <?=$active_closing?> link-spa"><i class="fas fa-receipt"></i><span class="hide-mobile ms-2">Closing</span></a>
		  </li>
		  <li class="nav-item bg-primary">
			<a href="<?=base_url()?>/penjualan-mobile-qris" id="menu-invoice" class="nav-link <?=$active_penjualan_qris?> link-spa"><i class="fas fa-receipt"></i><span class="hide-mobile ms-2">Pending QR</span></a>
		  </li>
		  <!-- <li class="nav-item bg-primary">
			<a href="<?=base_url()?>/closing-mobile" class="nav-link <?=$active_barang?> link-spa"><i class="fas fa-box-open"></i><span class="hide-mobile ms-2">Barang</span></a>
		  </li> -->
		</ul>
	 </nav>
		
	<div class="sidebar-mobile offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" style="width:280px" aria-labelledby="offcanvasExampleLabel">
		<div class="offcanvas-header">
			<h5 class="offcanvas-title" id="offcanvasExampleLabel"> <img src="<?=base_url() . '/public/images/' . $setting_aplikasi['logo_login']?>"/></h5>
			<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body sidebar-body">
			<div class="img-profile">
				<?php
				$file = $user['avatar'];
				
				if ($user['avatar']) {
					$path = ROOTPATH . '/public/images/user/' . $file;
					if (!file_exists($path)) {
						$file = 'default.png';
					}
					
				} else {
					$file = 'default.png';
				}
				?>
				<div class="avatar-profile">
					<img style="max-height: 100px; max-width: 100px;" class="rounded-circle" src="<?=base_url() . '/public/images/user/' . $file?>"/>
				</div>
				<p class="mb-0 mt-3"><?=$user['nama']?></p>
				<p class="mb-0"><?=$user['nama_company']?></p>
				<p class="mb-0"><?=$user['email']?></p>
			</div>
			<nav class="mt-3">
				<ul class="nav nav-pills flex-column">
					<?php
					if (key_exists(46, $session->get('user')['all_permission'] ?? [])) {
						?>
						<li class="nav-item">
							<a class="nav-link link-dark py-3 px-3 link-dashboard" href="<?=base_url() . '/dashboard'?>">
								<i class="fas fa-tachometer-alt me-2"></i>Dashboard
							</a>
						</li>
					<?php
					}
					?>
					<li class="nav-item">
						<a class="nav-link link-dark py-3 px-3 link-spa" href="<?=base_url() . '/builtin/user/edit?mobile=true'?>">
							<i class="fas fa-user me-2"></i>Profile
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link link-dark py-3 px-3 link-spa" href="<?=base_url() . '/builtin/user/edit-password?mobile=true'?>">
							<i class="fas fa-lock me-2"></i>Ubah Password
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link link-dark py-3 px-3 link-spa" href="<?=base_url() . '/login/logout?mobile=true'?>">
							<i class="fas fa-sign-out-alt me-2"></i>Logout
						</a>
					</li>
				</ul>
			</nav>
		</div>
	</div>
<script type='text/javascript'>
window.addEventListener('beforeinstallprompt', function(event){
    // console.log('before add to home screen');
    event.preventDefault();
    promptInstall = event;
    return false;
});
// file feed.js
// dalam block function openCreatePostModal()

function openCreatePostModal() {
  createPostArea.style.display = 'block';

  // tambahkan kode ini untuk menampilkan banner add to home screen
  if(promptInstall){
    promptInstall.prompt()
    promptInstall.userChoice.then(function(choiceResult){
    //   console.log(choiceResult.outcome);

      if(choiceResult.outcome==='dismissed'){
        // console.log('user cancelled installation');
      }else{
        // console.log('user add to home screen');
      }
    });
    promptInstall = null;
  }
  // end of code

}
</script>
<script type='text/javascript'>
window.addEventListener('beforeinstallprompt', function(event){
    // console.log('before add to home screen');
    event.preventDefault();
    promptInstall = event;
    return false;
});
// file feed.js
// dalam block function openCreatePostModal()

function openCreatePostModal() {
  createPostArea.style.display = 'block';

  // tambahkan kode ini untuk menampilkan banner add to home screen
  if(promptInstall){
    promptInstall.prompt()
    promptInstall.userChoice.then(function(choiceResult){
    //   console.log(choiceResult.outcome);

      if(choiceResult.outcome==='dismissed'){
        // console.log('user cancelled installation');
      }else{
        // console.log('user add to home screen');
      }
    });
    promptInstall = null;
  }
  // end of code

}
</script>
<script>
    var BASE_URL = '<?= base_url() ?>';
    document.addEventListener('DOMContentLoaded', init, false);

    function init() {
        if ('serviceWorker' in navigator && navigator.onLine) {
            navigator.serviceWorker.register( BASE_URL + '/service-worker.js')
            .then((reg) => {
                // console.log(BASE_URL);
                // console.log('Registrasi service worker Berhasil', reg);
            }, (err) => {
                // console.error('Registrasi service worker Gagal', err);
            });
        }
    }
</script>
</body>
</html>