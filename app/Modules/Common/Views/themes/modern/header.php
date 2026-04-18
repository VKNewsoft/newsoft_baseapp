<?php
/**
 * header.php
 * Main Layout Header
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

$session = \Config\Services::session();
if (empty($session->get('user'))) {
	$content = 'Layout halaman ini memerlukan login';
	include(APPPATH . 'Modules/Common/Views/themes/modern/header-error.php');
	exit;
}

$user = $session->get('user');
helper('setting_layout');
$faviconUrl = public_image_url($setting_aplikasi['favicon'] ?? '', 'logo_only.png');
$faviconVersion = public_image_version($setting_aplikasi['favicon'] ?? '', 'logo_only.png');
$logoAppUrl = public_image_url($setting_aplikasi['logo_app'] ?? '', 'logo_only.png');
$currentFontKey = setting_layout_font_key($app_layout['font_family'] ?? 'open-sans');
$currentFont = setting_layout_font_entry($app_layout['font_family'] ?? 'open-sans');
$currentFontFamily = $currentFont['family'];
$currentFontCssPath = $currentFont['css_path'];
$fontPreloadFiles = setting_layout_font_preload_files($app_layout['font_family'] ?? 'open-sans');
$criticalFontCss = setting_layout_font_critical_css($app_layout['font_family'] ?? 'open-sans');
$fontAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/' . $currentFontCssPath);
$fontSizeAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/fonts/font-size-' . ($app_layout['font_size'] ?? '14') . '.css');
$fontawesomeVersion = @filemtime(ROOTPATH . 'public/vendors/fontawesome/css/all.css');
$bootstrapVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/css/bootstrap.min.css');
$bootstrapIconsVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap-icons/bootstrap-icons.css');
$sweetalertVersion = @filemtime(ROOTPATH . 'public/vendors/sweetalert2/sweetalert2.min.css');
$overlayCssVersion = @filemtime(ROOTPATH . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css');
$siteCssVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/site.css');
$datatablesCssVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css');
$bootswatchVersion = @filemtime(ROOTPATH . 'public/vendors/bootswatch/'. ( empty($_COOKIE['nsd_adm_theme']) || @$_COOKIE['nsd_adm_theme'] == 'light' ? esc($app_layout['bootswatch_theme']) : 'default' ) .'/bootstrap.min.css');
$colorSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css');
$sidebarSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css');
$logoSchemeVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css');
$bootstrapCustomVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/bootstrap-custom.css');
$jqueryVersion = @filemtime(ROOTPATH . 'public/vendors/jquery/jquery.min.js');
$bootstrapJsVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js');
$bootboxVersion = @filemtime(ROOTPATH . 'public/vendors/bootbox/bootbox.min.js');
$sweetalertJsVersion = @filemtime(ROOTPATH . 'public/vendors/sweetalert2/sweetalert2.min.js');
$overlayJsVersion = @filemtime(ROOTPATH . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js');
$cookieJsVersion = @filemtime(ROOTPATH . 'public/vendors/js.cookie/js.cookie.min.js');
$functionsJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/functions.js');
$siteJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/site.js');
$sidebarJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/sidebar.js');
$popperJsVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/js/popper.min.js');
$datatablesJsVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js');
$datatablesBootstrapJsVersion = @filemtime(ROOTPATH . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js');

/**
 * Menjaga cache asset dinamis tetap stabil agar browser tidak memuat ulang file
 * pada setiap perpindahan halaman jika URL asset belum memiliki versi sendiri.
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
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?= $setting_aplikasi['judul_web'] ?> | <?= $current_module['judul_module'] ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="googlebot" content="noindex, nofollow" />
	<style>:root{--app-font-family: <?= $currentFontFamily ?>;}html,body{font-family:var(--app-font-family);}<?= $criticalFontCss ?></style>
	<script>
		window.__APP_FONT_FAMILY__ = <?=json_encode($currentFontFamily)?>;
		document.documentElement.style.setProperty('--app-font-family', window.__APP_FONT_FAMILY__);
	</script>
	<link rel="shortcut icon" href="<?= $faviconUrl . '?v=' . $faviconVersion ?>" />

	<?php foreach ($fontPreloadFiles as $fontFile): ?>
	<link rel="preload" as="font" type="font/woff2" crossorigin fetchpriority="high" href="<?= $config->baseURL . 'module-assets/Common/builtin/fonts/'.$fontFile['file'].'?v='.@filemtime(APPPATH . 'Modules/Common/Assets/builtin/fonts/'.$fontFile['file']) ?>" />
	<?php endforeach; ?>
	<link rel="preload" as="style" fetchpriority="high" href="<?= $config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion ?>" />
	<link id="font-switch" rel="stylesheet" data-font-key="<?=esc($currentFontKey, 'attr')?>" href="<?= $config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion ?>" />
	<link id="font-size-switch" rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?v='.$fontSizeAssetVersion ?>" />

	<!-- Styles: vendors / theme / dynamic -->
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/fontawesome/css/all.css?v='.$fontawesomeVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?v='.$bootstrapVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootstrap-icons/bootstrap-icons.css?v='.$bootstrapIconsVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?v='.$sweetalertVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css?v='.$overlayCssVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/site.css?v='.$siteCssVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css?v='.$datatablesCssVersion ?>" />

	<link id="style-switch-bootswatch" rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootswatch/'. ( empty($_COOKIE['nsd_adm_theme']) || @$_COOKIE['nsd_adm_theme'] == 'light' ? esc($app_layout['bootswatch_theme']) : 'default' ) .'/bootstrap.min.css?v='.$bootswatchVersion ?>" />
	<link id="style-switch" rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?v='.$colorSchemeVersion ?>" />
	<link id="style-switch-sidebar" rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css?v='.$sidebarSchemeVersion ?>" />
	<link id="logo-background-color-switch" rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css?v='.$logoSchemeVersion ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'module-assets/Common/builtin/css/bootstrap-custom.css?v=' . $bootstrapCustomVersion ?>" />

	<?php if (@$styles): ?>
		<!-- Dynamic styles -->
		<?php foreach ($styles as $file): ?>
			<link rel="stylesheet" data-type="dynamic-resource-head" href="<?= append_dynamic_asset_version($file) ?>" />
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Small page styles kept in head -->
	<style>
		.sidebar-menu a.active,
		.sidebar-menu .active,
		.sidebar nav li.highlight > a,
		.sidebar nav > ul > li.highlight > a {
			background-color: var(--sidebar-item-active-bg, var(--primary)) !important;
			color: var(--sidebar-item-active-color, #ffffff) !important;
		}
		.sidebar-menu a.active:hover,
		.sidebar-menu .active:hover,
		.sidebar nav li.highlight > a:hover,
		.sidebar nav > ul > li.highlight > a:hover {
			background-color: var(--sidebar-item-active-hover-bg, var(--sidebar-item-active-bg, var(--primary))) !important;
			color: var(--sidebar-item-active-hover-color, var(--sidebar-item-active-color, #ffffff)) !important;
		}
		.sidebar nav li.tree-open > a {
			background-color: var(--sidebar-tree-open-bg, rgba(var(--primary-rgb), 0.12)) !important;
			color: var(--sidebar-tree-open-color, var(--primary)) !important;
		}
		.sidebar nav li.tree-open.highlight > a,
		.sidebar nav li.tree-open.highlight > a:hover {
			background-color: var(--sidebar-item-active-hover-bg, var(--sidebar-item-active-bg, var(--primary))) !important;
			color: var(--sidebar-item-active-hover-color, var(--sidebar-item-active-color, #ffffff)) !important;
		}
		.sidebar-group-header.active-group {
			background-color: var(--sidebar-group-active-bg, rgba(var(--primary-rgb), 0.08));
			color: var(--sidebar-group-active-color, var(--primary));
		}
		.sidebar-group { margin-bottom: .5rem; }
		.sidebar-group-header { cursor: default; border-radius: .25rem; }
	</style>

	<!-- JS globals and vendor libs -->
	<script>
		var base_url = "<?= $config->baseURL ?>";
		var module_url = "<?= $module_url ?>";
		var current_url = "<?= current_url() ?>";
		var theme_url = "<?= $config->baseURL . 'module-assets/Common/builtin/' ?>";
		let current_bootswatch_theme = "<?= $app_layout['bootswatch_theme'] ?>";
	</script>

	<script src="<?= $config->baseURL . 'public/vendors/jquery/jquery.min.js?v='.$jqueryVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js?v='.$bootstrapJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/bootbox/bootbox.min.js?v='.$bootboxVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js?v='.$sweetalertJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js?v='.$overlayJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/js.cookie/js.cookie.min.js?v='.$cookieJsVersion ?>"></script>

	<script src="<?= $config->baseURL . 'module-assets/Common/builtin/js/functions.js?v='.$functionsJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'module-assets/Common/builtin/js/site.js?v='.$siteJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'module-assets/Common/builtin/js/sidebar.js?v='.$sidebarJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'module-assets/Common/builtin/js/popper.min.js?v='.$popperJsVersion ?>"></script>

	<!-- DataTables -->
	<script src="<?= $config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js?v='.$datatablesJsVersion ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js?v='.$datatablesBootstrapJsVersion ?>"></script>

	<?php if (@$scripts): ?>
		<!-- Dynamic scripts -->
		<?php foreach ($scripts as $file): ?>
			<?php if (is_array($file) && !empty($file['print'])): ?>
				<script data-type="dynamic-resource-head"><?= $file['script'] ?></script>
			<?php elseif (!is_array($file)): ?>
				<script data-type="dynamic-resource-head" src="<?= append_dynamic_asset_version($file) ?>"></script>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</head>
<body class="<?= @$_COOKIE['nsd_adm_mobile'] ? 'mobile-menu-show' : '' ?>" style="font-family: <?=esc($currentFontFamily, 'attr')?>;">
	<header class="nav-header shadow">
		<div class="nav-header-logo pull-left">
			<a class="header-logo" href="<?= $config->baseURL ?>">
				<img src="<?= $logoAppUrl ?>"/>
			</a>
		</div>

		<div class="pull-left nav-header-left">
			<ul class="nav-header">
				<li>
					<a href="#" id="mobile-menu-btn"><i class="fa fa-bars"></i></a>
				</li>
			</ul>
		</div>

		<div class="pull-right mobile-menu-btn-right">
			<a href="#" id="mobile-menu-btn-right"><i class="fa fa-ellipsis-h"></i></a>
		</div>

		<div class="pull-right nav-header nav-header-right">
			<ul class="d-flex align-items-center">
				<?php
				$total_notifikasi = 0;
				$show_notifikasi = $total_notifikasi > 0;
				if ($total_notifikasi > 99) $total_notifikasi = '99+';

				if ($show_notifikasi):
				?>
					<li>
						<a href="#" class="icon-link" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-bell"></i>
							<span class="badge rounded-pill badge-notification <?= ($total_notifikasi == 0 ? 'bg-success' : 'bg-danger') ?> position-absolute translate-middle" style="font-size:10px; top:15px; font-weight:normal"><?= $total_notifikasi ?></span>
						</a>
						<div class="dropdown-menu p-3">
							<!-- NOTIFIKASI HERE -->
						</div>
					</li>
				<?php endif; ?>

				<li>
					<a class="icon-link" href="<?= $config->baseURL ?>builtin/setting-layout"><i class="bi bi-gear"></i></a>
				</li>

				<li class="ps-2 nav-account">
					<?php
					$img_url = !empty($user['avatar']) && file_exists(ROOTPATH . '/public/images/user/' . $user['avatar'])
						? $config->baseURL . '/public/images/user/' . $user['avatar']
						: $config->baseURL . '/public/images/user/default.png';
					$account_link = $config->baseURL . 'user';
					?>
					<a class="profile-btn" href="<?= $account_link ?>" data-bs-toggle="dropdown"><img src="<?= $img_url ?>" alt="user_img"></a>

					<?php if ($isloggedin): ?>
						<ul class="dropdown-menu">
							<li class="dropdown-profile px-4 pt-4 pb-2">
								<div class="avatar">
									<a href="<?= $config->baseURL . 'builtin/user/edit?id=' . $user['id_user'] ?>">
										<img style="max-height:100px; max-width:100px;" src="<?= $img_url ?>" alt="user_img">
									</a>
								</div>
								<div class="card-content mt-3">
									<p><small>Nama Karyawan: <br /><?= $user['nama'] ?></small></p>
								</div>
							</li>
							<li><a class="dropdown-item py-2" href="<?= $config->baseURL ?>hrm/profile">Ubah Profil</a></li>
							<li><a class="dropdown-item py-2" href="<?= $config->baseURL ?>builtin/user/edit-password">Change Password</a></li>
							<li><a class="dropdown-item py-2" href="<?= $config->baseURL ?>login/logout">Logout</a></li>
						</ul>
					<?php else: ?>
						<div class="float-login">
							<form method="post" action="<?= $config->baseURL ?>login">
								<input type="email" name="email" placeholder="Email" required>
								<input type="password" name="password" placeholder="Password" required>
								<div class="checkbox">
									<label style="font-weight:normal"><input name="remember" value="1" type="checkbox"> Remember me</label>
								</div>
								<button type="submit" style="width:100%" class="btn btn-success" name="submit">Submit</button>
								<?php $form_token = $auth->generateFormToken('login_form_token_header'); ?>
								<input type="hidden" name="form_token" value="<?= $form_token ?>" />
								<input type="hidden" name="login_form_header" value="login_form_header" />
							</form>
							<a href="<?= $config->baseURL . 'recovery' ?>">Lupa password?</a>
						</div>
					<?php endif; ?>
				</li>
			</ul>
		</div>
	</header>

	<div class="site-content">
		<div class="sidebar-guide">
			<div class="arrow" style="font-size:18px"><i class="fa-solid fa-angles-right"></i></div>
		</div>

		<div class="sidebar shadow">
			<nav class="sidebar-nav p-2">
				<!-- Search / Quick filter -->
				<div class="mb-2 px-2">
					<div class="input-group input-group-sm">
						<span class="input-group-text"><i class="bi bi-search"></i></span>
						<input id="sidebarSearch" type="text" class="form-control" placeholder="Cari menu..." aria-label="Cari menu">
						<button class="btn btn-outline-secondary" type="button" id="sidebarSearchClear" title="Clear"><i class="bi bi-x-lg"></i></button>
					</div>
				</div>

				<div class="sidebar-groups">
					<?php foreach ($menu as $index => $val):
						$kategori = $val['kategori'];
						$list_menu = menu_list($val['menu']);
						$menu_html = build_menu($current_module, $list_menu);

						$groupActive = (stripos($menu_html, 'class="active') !== false
							|| stripos($menu_html, ' aria-current') !== false
							|| stripos($menu_html, "class='active") !== false);

						$iconHtml = !empty($kategori['icon']) ? '<i class="'. $kategori['icon'] .' me-2"></i>' : '<i class="bi bi-list me-2"></i>';
					?>
						<div class="sidebar-group">
							<div class="sidebar-group-header p-2 d-flex align-items-center <?= $groupActive ? 'active-group' : '' ?>" style="border-bottom:3px solid currentColor; padding-bottom:.35rem;">
								<?= $iconHtml ?>
								<span class="fw-semibold"><?= $kategori['nama_kategori'] ?></span>
								<?php if (!empty($kategori['deskripsi'])): ?>
									<small class="text-muted ms-2 d-none d-md-inline"><?= $kategori['deskripsi'] ?></small>
								<?php endif; ?>
							</div>
							<div class="sidebar-group-body mt-1">
								<div class="list-group list-group-flush sidebar-menu">
									<?= $menu_html ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</nav>
		</div>

		<div class="mobile-bottom-nav d-lg-none" id="mobileBottomNav" aria-label="Mobile Navigation">
			<div class="mobile-bottom-nav-track" id="mobileBottomNavTrack"></div>
		</div>

		<div class="mobile-radial-menu d-lg-none" id="mobileRadialMenu" aria-hidden="true">
			<div class="mobile-radial-backdrop" data-radial-close="1"></div>
			<div class="mobile-radial-sheet">
				<div class="mobile-radial-header">
					<button type="button" class="mobile-radial-back" id="mobileRadialBack" aria-label="Kembali">
						<i class="bi bi-arrow-left"></i>
					</button>
					<div class="mobile-radial-title" id="mobileRadialTitle">Menu</div>
					<button type="button" class="mobile-radial-close" data-radial-close="1" aria-label="Tutup">
						<i class="bi bi-x-lg"></i>
					</button>
				</div>
				<div class="mobile-radial-viewport" id="mobileRadialViewport">
					<div class="mobile-radial-wheel" id="mobileRadialWheel"></div>
					<div class="mobile-radial-center">
						<button type="button" class="mobile-radial-center-btn" id="mobileRadialCenterBtn">
							<i class="bi bi-grid"></i>
						</button>
					</div>
				</div>
				<div class="mobile-radial-hint">Geser untuk melihat item lain</div>
			</div>
		</div>

		<div class="content">
			<!-- <?= !empty($breadcrumb) ? breadcrumb($breadcrumb) : '' ?> -->
			<div class="content-wrapper">

