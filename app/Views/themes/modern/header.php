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
	include('app/Views/themes/modern/header-error.php');
	exit;
}

$user = $session->get('user');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title><?= $setting_aplikasi['judul_web'] ?> | <?= $current_module['judul_module'] ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta name="robots" content="noindex, nofollow" />
	<meta name="googlebot" content="noindex, nofollow" />
	<link rel="shortcut icon" href="<?= $config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?r='.time() ?>" />

	<!-- Styles: vendors / theme / dynamic -->
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/fontawesome/css/all.css' ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootstrap-icons/bootstrap-icons.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/overlayscrollbars/OverlayScrollbars.min.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/site.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/datatables/dist/css/dataTables.bootstrap5.min.css?r='.time() ?>" />

	<link id="style-switch-bootswatch" rel="stylesheet" href="<?= $config->baseURL . 'public/vendors/bootswatch/'. ( empty($_COOKIE['nsd_adm_theme']) || @$_COOKIE['nsd_adm_theme'] == 'light' ? esc($app_layout['bootswatch_theme']) : 'default' ) .'/bootstrap.min.css?r='.time() ?>" />
	<link id="style-switch" rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?r='.time() ?>" />
	<link id="style-switch-sidebar" rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['sidebar_color'].'-sidebar.css?r='.time() ?>" />
	<link id="font-switch" rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/fonts/'.$app_layout['font_family'].'.css?r='.time() ?>" />
	<link id="font-size-switch" rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/fonts/font-size-'.$app_layout['font_size'].'.css?r='.time() ?>" />
	<link id="logo-background-color-switch" rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['logo_background_color'].'-logo-background.css?r='.time() ?>" />
	<link rel="stylesheet" href="<?= $config->baseURL . 'public/themes/modern/builtin/css/bootstrap-custom.css?r=' . time() ?>" />

	<?php if (@$styles): ?>
		<!-- Dynamic styles -->
		<?php foreach ($styles as $file): ?>
			<link rel="stylesheet" data-type="dynamic-resource-head" href="<?= $file . '?r=' . time() ?>" />
		<?php endforeach; ?>
	<?php endif; ?>

	<!-- Small page styles kept in head -->
	<style>
		.sidebar-menu a.active,
		.sidebar-menu .active {
			background-color: #2563eb !important;
			color: #ffffff !important;
		}
		.sidebar-group-header.active-group {
			background-color: #f8fafc;
			color: #2563eb;
		}
		.sidebar-group { margin-bottom: .5rem; }
		.sidebar-group-header { cursor: default; border-radius: .25rem; }
	</style>

	<!-- JS globals and vendor libs -->
	<script>
		var base_url = "<?= $config->baseURL ?>";
		var module_url = "<?= $module_url ?>";
		var current_url = "<?= current_url() ?>";
		var theme_url = "<?= $config->baseURL . '/public/themes/modern/builtin/' ?>";
		let current_bootswatch_theme = "<?= $app_layout['bootswatch_theme'] ?>";
	</script>

	<script src="<?= $config->baseURL . 'public/vendors/jquery/jquery.min.js' ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/bootstrap/js/bootstrap.bundle.min.js' ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/bootbox/bootbox.min.js' ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js' ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/overlayscrollbars/jquery.overlayScrollbars.min.js' ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/js.cookie/js.cookie.min.js' ?>"></script>

	<script src="<?= $config->baseURL . 'public/themes/modern/builtin/js/functions.js?r='.time() ?>"></script>
	<script src="<?= $config->baseURL . 'public/themes/modern/builtin/js/site.js?r='.time() ?>"></script>
	<script src="<?= $config->baseURL . 'public/themes/modern/builtin/js/popper.min.js' ?>"></script>

	<!-- DataTables -->
	<script src="<?= $config->baseURL . 'public/vendors/datatables/dist/js/jquery.dataTables.min.js?r='.time() ?>"></script>
	<script src="<?= $config->baseURL . 'public/vendors/datatables/dist/js/dataTables.bootstrap5.min.js?r='.time() ?>"></script>

	<?php if (@$scripts): ?>
		<!-- Dynamic scripts -->
		<?php foreach ($scripts as $file): ?>
			<?php if (is_array($file) && !empty($file['print'])): ?>
				<script data-type="dynamic-resource-head"><?= $file['script'] ?></script>
			<?php elseif (!is_array($file)): ?>
				<script data-type="dynamic-resource-head" src="<?= $file . '?r=' . time() ?>"></script>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</head>
<body class="<?= @$_COOKIE['nsd_adm_mobile'] ? 'mobile-menu-show' : '' ?>">
	<header class="nav-header shadow">
		<div class="nav-header-logo pull-left">
			<a class="header-logo" href="<?= $config->baseURL ?>">
				<img src="<?= $config->baseURL . '/public/images/' . $setting_aplikasi['logo_app'] ?>"/>
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

		<div class="content">
			<!-- <?= !empty($breadcrumb) ? breadcrumb($breadcrumb) : '' ?> -->
			<div class="content-wrapper">

<!-- Sidebar JS separated for clarity -->
<script>
(function(){
	const search = document.getElementById('sidebarSearch');
	const clear = document.getElementById('sidebarSearchClear');
	const groups = Array.from(document.querySelectorAll('.sidebar-group'));
	if (!search || !groups.length) return;

	const openedAccordions = new Set();

	function resetSearchStates(){
		groups.forEach(function(group){
			group.style.display = '';
			group.querySelectorAll('a, li').forEach(function(it){ it.style.display = ''; });
			group.querySelectorAll('.search-open').forEach(function(el){
				if (!el.querySelector('.active, [aria-current]')) {
					el.classList.remove('search-open','tree-open');
				} else {
					el.classList.remove('search-open');
				}
			});
			group.querySelectorAll('.submenu').forEach(function(s){
				if (s.querySelector('.active, [aria-current]')) s.style.display = 'block'; else s.style.display = '';
			});
		});
		openedAccordions.forEach(function(id){
			const collapseEl = document.getElementById(id);
			if (!collapseEl) return;
			if (!collapseEl.querySelector('.active, [aria-current]')){
				collapseEl.classList.remove('show');
				const btn = document.querySelector('[data-bs-target="#' + id + '"]');
				if (btn) { btn.classList.add('collapsed'); btn.setAttribute('aria-expanded','false'); }
			} else {
				const btn = document.querySelector('[data-bs-target="#' + id + '"]');
				if (btn) { btn.classList.remove('collapsed'); btn.setAttribute('aria-expanded','true'); }
			}
		});
		openedAccordions.clear();
	}

	search.addEventListener('input', function(){
		const q = this.value.trim().toLowerCase();
		if (q === ''){ resetSearchStates(); return; }

		groups.forEach(function(group){
			const menu = group.querySelector('.sidebar-menu');
			if (!menu) { group.style.display = 'none'; return; }
			let anyVisible = false;
			const items = Array.from(menu.querySelectorAll('a, li'));
			items.forEach(function(item){
				const text = (item.textContent || item.innerText || '').toLowerCase();
				if (text.indexOf(q) !== -1) {
					item.style.display = '';
					anyVisible = true;
					const collapseAncestor = item.closest('.accordion-collapse');
					if (collapseAncestor && !collapseAncestor.classList.contains('show')){
						collapseAncestor.classList.add('show');
						openedAccordions.add(collapseAncestor.id);
						const btn = document.querySelector('[data-bs-target="#' + collapseAncestor.id + '"]');
						if (btn) { btn.classList.remove('collapsed'); btn.setAttribute('aria-expanded','true'); }
					}
					let parentSub = item.closest('.submenu');
					while(parentSub){ parentSub.style.display = 'block'; const parentLi = parentSub.closest('li'); if (parentLi) parentLi.classList.add('tree-open','search-open'); parentSub = parentLi ? parentLi.closest('.submenu') : null; }
				} else {
					item.style.display = 'none';
				}
			});
			group.style.display = anyVisible ? '' : 'none';
		});
	});
	clear.addEventListener('click', function(){ search.value=''; search.dispatchEvent(new Event('input')); });
})();

// Highlight active link and parent group
document.addEventListener('DOMContentLoaded', function(){
	const links = document.querySelectorAll('.sidebar-menu a[href]');
	if (!links.length) return;

	function normalize(u){
		try { return u.replace(/\/+$/,''); } catch(e){ return u; }
	}
	const cur = normalize(window.location.href);
	const mod = typeof module_url !== 'undefined' ? normalize(module_url) : null;

	links.forEach(function(a){
		const href = normalize(a.href || '');
		if (!href) return;
		if (href === cur || (mod && href === mod) || (cur.indexOf(href) === 0 && href.length > 0)) {
			a.classList.add('active');
			const group = a.closest('.sidebar-group');
			if (group) {
				const header = group.querySelector('.sidebar-group-header');
				if (header) header.classList.add('active-group');
				group.style.display = '';
			}
		}
	});
});
</script>
