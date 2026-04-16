<!DOCTYPE HTML>
<html lang="en">
<head>
<title><?=$site_title?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes" />
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex, nofollow">
<?php
helper('setting_layout');
$currentFontKey = setting_layout_font_key($app_layout['font_family'] ?? 'open-sans');
$currentFont = setting_layout_font_entry($app_layout['font_family'] ?? 'open-sans');
$currentFontFamily = $currentFont['family'];
$currentFontCssPath = $currentFont['css_path'];
$fontPreloadFiles = setting_layout_font_preload_files($app_layout['font_family'] ?? 'open-sans');
$criticalFontCss = setting_layout_font_critical_css($app_layout['font_family'] ?? 'open-sans');
$fontAssetVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/' . $currentFontCssPath);
$bootstrapCssVersion = @filemtime(ROOTPATH . 'public/vendors/bootstrap/css/bootstrap.min.css');
$bootstrapCustomCssVersion = @filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/bootstrap-custom.css');
$fontawesomeCssVersion = @filemtime(ROOTPATH . 'public/vendors/fontawesome/css/all.css');
$registerCssVersion = @filemtime(APPPATH . 'Modules/Common/Assets/css/register.css');
$paceCssVersion = @filemtime(ROOTPATH . 'public/vendors/pace/pace-theme-default.css');
$swalCssVersion = @filemtime(ROOTPATH . 'public/vendors/sweetalert2/sweetalert2.min.css');
?>
<style>:root{--app-font-family: <?=$currentFontFamily?>;}html,body{font-family:var(--app-font-family);}<?=$criticalFontCss?></style>
<script>
window.__APP_FONT_FAMILY__ = <?=json_encode($currentFontFamily)?>;
document.documentElement.style.setProperty('--app-font-family', window.__APP_FONT_FAMILY__);
</script>
<link rel="manifest" href="manifest.json"/>
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?r='.@filemtime(ROOTPATH . 'public/images/'.$setting_aplikasi['favicon'])?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?v='.$bootstrapCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/bootstrap-custom.css?v=' . $bootstrapCustomCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css?v='.$fontawesomeCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/css/register.css?v='.$registerCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?v='.$paceCssVersion?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?v='.$swalCssVersion?>"/>

<?php
if (@$styles) {
	foreach($styles as $file) {
		echo '<link rel="stylesheet" type="text/css" href="'.$file.'?v='.time().'"/>';
	}
}

?>

<link rel="stylesheet" id="style-switch" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?v='.@filemtime(APPPATH . 'Modules/Common/Assets/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css')?>"/>
<?php foreach ($fontPreloadFiles as $fontFile): ?>
<link rel="preload" as="font" type="font/woff2" crossorigin fetchpriority="high" href="<?=$config->baseURL . 'module-assets/Common/builtin/fonts/'.$fontFile['file'].'?v='.@filemtime(APPPATH . 'Modules/Common/Assets/builtin/fonts/'.$fontFile['file'])?>"/>
<?php endforeach; ?>
<link rel="preload" as="style" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>"/>
<link rel="stylesheet" id="font-switch" data-font-key="<?=esc($currentFontKey, 'attr')?>" type="text/css" href="<?=$config->baseURL . 'module-assets/Common/builtin/'.$currentFontCssPath.'?v='.$fontAssetVersion?>"/>

<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js?v='.@filemtime(ROOTPATH . 'public/vendors/jquery/jquery.min.js')?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js?v='.@filemtime(ROOTPATH . 'public/vendors/bootstrap/js/bootstrap.min.js')?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js?v='.@filemtime(ROOTPATH . 'public/vendors/bootbox/bootbox.min.js')?>"></script>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?v='.@filemtime(ROOTPATH . 'public/vendors/pace/pace.min.js')?>"></script>
<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
</script>
<?php

if (@$scripts) {
	foreach($scripts as $file) {
		echo '<script defer type="text/javascript" src="'.$file.'?v='.time().'"></script>';
	}
}

?>
</head>
<body style="font-family: <?=esc($currentFontFamily, 'attr')?>;">
	<div class="background"></div>
	<div class="backdrop"></div>
	<div class="card-container" <?=@$style?>>
		<?php
		$this->renderSection('content')
		?>
		<div class="copyright">
			<?php $footer = $setting_aplikasi['footer_login'] ? str_replace( '{{YEAR}}', date('Y'), html_entity_decode($setting_aplikasi['footer_login']) ) : '';
			echo $footer;
			?>
		</div>
	</div><!-- login container -->
</body>
<script defer type="text/javascript" src="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js?v='.$swalCssVersion?>"></script>
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
      // console.log(choiceResult.outcome);

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
    window.addEventListener('load', function() {
		if ('requestIdleCallback' in window) {
			requestIdleCallback(init, { timeout: 1500 });
		} else {
			setTimeout(init, 300);
		}
	}, false);

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
</html>
		


