<!DOCTYPE HTML>
<html lang="en">
<head>
<title>KASIR</title>
<meta name="descrition" content="Kasir"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes" />
<?php
helper('setting_layout');
$currentFontKey = setting_layout_font_key($app_layout['font_family'] ?? 'open-sans');
$currentFont = setting_layout_font_entry($app_layout['font_family'] ?? 'open-sans');
$currentFontFamily = $currentFont['family'];
$currentFontCssPath = $currentFont['css_path'];
$fontPreloadFiles = setting_layout_font_preload_files($app_layout['font_family'] ?? 'open-sans');
$criticalFontCss = setting_layout_font_critical_css($app_layout['font_family'] ?? 'open-sans');
$fontAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/' . $currentFontCssPath);
$fontSizeAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/fonts/font-size-' . ($app_layout['font_size'] ?? '14') . '.css');
$faviconVersion = @filemtime(ROOTPATH . 'public/images/'.$setting_aplikasi['favicon']);
$fontawesomeVersion = @filemtime(ROOTPATH . 'public/vendors/fontawesome/css/all.css');
$bootstrapVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/css/bootstrap.min.css');
$bootstrapCustomPosVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/bootstrap-custom-pos-kasir.css');
$sweetalertVersion = @filemtime(ROOTPATH . 'public/vendors/sweetalert2/sweetalert2.min.css');
$overlayCssVersion = @filemtime(ROOTPATH . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css');
$paceCssVersion = @filemtime(ROOTPATH . 'public/vendors/pace/pace-theme-default.css');
$layoutMobileVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/layout-mobile.css');
$layoutMobilePanelVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/layout-mobile-panel.css');
$datatablesCssVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css');
$colorSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css');
$sidebarSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css');
$logoSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css');
$jqueryVersion = @filemtime(ROOTPATH . 'public/vendors/jquery/jquery.min.js');
$bootstrapJsVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js');
$bootboxVersion = @filemtime(ROOTPATH . 'public/vendors/bootbox/bootbox.min.js');
$sweetalertJsVersion = @filemtime(ROOTPATH . 'public/vendors/sweetalert2/sweetalert2.min.js');
$functionsJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/functions.js');
$overlayJsVersion = @filemtime(ROOTPATH . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js');
$paceJsVersion = @filemtime(ROOTPATH . 'public/vendors/pace/pace.min.js');
$mainMobileJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/js/main-mobile.js');
$datatablesJsVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js');
$datatablesBootstrapJsVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js');

/**
 * Menjaga cache asset dinamis tetap stabil agar perpindahan halaman mobile
 * tidak memicu unduhan ulang asset yang sama pada setiap request.
 */
if (!function_exists('append_dynamic_asset_version')) {
	function append_dynamic_asset_version($file)
	{
		if (strpos($file, '://') === false && strpos($file, '//') !== 0 && strpos($file, 'data:') !== 0) {
			$cleanFile = strtok($file, '#');
			if (strpos($cleanFile, '?v=') !== false || strpos($cleanFile, '?r=') !== false || strpos($cleanFile, '&v=') !== false || strpos($cleanFile, '&r=') !== false) {
				return $file;
			}

			$baseUrl = rtrim(base_url(), '/');
			if (strpos($cleanFile, $baseUrl) === 0) {
				$relativePath = ltrim(substr($cleanFile, strlen($baseUrl)), '/');
				$absolutePath = ROOTPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
				if (is_file($absolutePath)) {
					$separator = strpos($file, '?') !== false ? '&' : '?';
					return $file . $separator . 'v=' . @filemtime($absolutePath);
				}
			}
		}

		return $file;
	}
}
?>
<style>:root{--app-font-family: <?=$currentFontFamily?>;}html,body{font-family:var(--app-font-family);}<?=$criticalFontCss?></style>
<script>
window.__APP_FONT_FAMILY__ = <?=json_encode($currentFontFamily)?>;
document.documentElement.style.setProperty('--app-font-family', window.__APP_FONT_FAMILY__);
</script>
<link rel="manifest" href="manifest.json"/>
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?v='.$faviconVersion?>" />
<?php foreach ($fontPreloadFiles as $fontFile): ?>
<link rel="preload" as="font" type="font/woff2" crossorigin fetchpriority="high" href="<?=$config->baseURL . 'module-assets/Common/builtin/fonts/'.$fontFile['file'].'?v='.@filemtime(APPPATH . 'Modules/Common/Assets/builtin/fonts/'.$fontFile['file'])?>"/>
<?php endforeach; ?>
<link rel="preload" as="style" fetchpriority="high" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>"/>
<link rel="stylesheet" id="font-switch" data-font-key="<?=esc($currentFontKey, 'attr')?>" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>"/>
<link rel="stylesheet" id="font-size-switch" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?v='.$fontSizeAssetVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css?v='.$fontawesomeVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?v='.$bootstrapVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap-custom.min.css?v='.@filemtime(ROOTPATH . 'public/vendors/bootstrap/css/bootstrap-custom.min.css')?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?v='.$sweetalertVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/bootstrap-custom-pos-kasir.css?v='.$bootstrapCustomPosVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css?v='.$overlayCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?v='.$paceCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/layout-mobile.css?v='.$layoutMobileVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/layout-mobile-panel.css?v='.$layoutMobilePanelVersion?>"/>

<!-- Data Tables -->
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css?v='.$datatablesCssVersion?>"/>
<!-- // Data Tables -->

<link rel="stylesheet" id="style-switch" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?v='.$colorSchemeVersion?>"/>
<link rel="stylesheet" id="style-switch-sidebar" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css?v='.$sidebarSchemeVersion?>"/>
<link rel="stylesheet" id="logo-background-color-switch" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css?v='.$logoSchemeVersion?>"/>

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
				
			echo '<link rel="stylesheet" data-type="dynamic-resource-head" ' . $attr . ' type="text/css" href="'.append_dynamic_asset_version($file['file']).'"/>' . "\n";
		} else {
			echo '<link rel="stylesheet" data-type="dynamic-resource-head" type="text/css" href="'.append_dynamic_asset_version($file).'"/>' . "\n";
		}
	}
}

?>

<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
	var module_url = "<?=$module_url?>";
	var current_url = "<?=current_url()?>";
	var theme_url = "<?=$config->baseURL . 'module-assets/Common/builtin/'?>";
</script>

<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js?v='.$jqueryVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.$bootstrapJsVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js?v='.$bootboxVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js?v='.$sweetalertJsVersion?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'module-assets/Common/builtin/js/functions.js?v='.$functionsJsVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js?v='.$overlayJsVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?v='.$paceJsVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'module-assets/Common/js/main-mobile.js?v='.$mainMobileJsVersion?>"></script>

<!-- Data Tables -->
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js?v='.$datatablesJsVersion?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js?v='.$datatablesBootstrapJsVersion?>"></script>
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
				echo '<script defer type="text/javascript" data-type="dynamic-resource-head" ' . $attr . ' src="'.append_dynamic_asset_version($file['script']).'"></script>' . "\n";
			}
		} else {
			echo '<script defer type="text/javascript" data-type="dynamic-resource-head" src="'.append_dynamic_asset_version($file).'"></script>' . "\n";
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
<body style="font-family: <?=esc($currentFontFamily, 'attr')?>;">
	
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
<script>
	var BASE_URL = '<?= base_url() ?>';
	var promptInstall = null;
	window.addEventListener('beforeinstallprompt', function(event){
		event.preventDefault();
		promptInstall = event;
		return false;
	});

	function openCreatePostModal() {
		if (typeof createPostArea !== 'undefined' && createPostArea) {
			createPostArea.style.display = 'block';
		}

		if (promptInstall) {
			promptInstall.prompt();
			promptInstall.userChoice.then(function() {
				promptInstall = null;
			});
		}
	}

	function initMobileNonCritical() {
		if ('serviceWorker' in navigator && navigator.onLine) {
			navigator.serviceWorker.register(BASE_URL + '/service-worker.js');
		}
	}

	window.addEventListener('load', function() {
		if ('requestIdleCallback' in window) {
			requestIdleCallback(initMobileNonCritical, { timeout: 1500 });
		} else {
			setTimeout(initMobileNonCritical, 300);
		}
	}, false);
</script>
</body>
</html>
