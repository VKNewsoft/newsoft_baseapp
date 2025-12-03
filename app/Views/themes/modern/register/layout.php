<!DOCTYPE HTML>
<html lang="en">
<title><?=$site_title?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="mobile-web-app-capable" content="yes" />
<meta name="robots" content="noindex, nofollow">
<meta name="googlebot" content="noindex, nofollow">
<link rel="manifest" href="manifest.json"/>
<link rel="shortcut icon" href="<?=$config->baseURL . 'public/images/'.$setting_aplikasi['favicon'].'?r='.time()?>" />
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/bootstrap/css/bootstrap.min.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/bootstrap-custom.css?r=' . time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/fontawesome/css/all.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/css/register.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/pace/pace-theme-default.css?r='.time()?>"/>
<link rel="stylesheet" type="text/css" href="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.css?r='.time()?>"/>

<?php
if (@$styles) {
	foreach($styles as $file) {
		echo '<link rel="stylesheet" type="text/css" href="'.$file.'?r='.time().'"/>';
	}
}

?>

<link rel="stylesheet" id="style-switch" type="text/css" href="<?=$config->baseURL . 'public/themes/modern/builtin/css/color-schemes/'.$app_layout['color_scheme'].'.css?r='.time()?>"/>

<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/jquery/jquery.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootstrap/js/bootstrap.min.js?r='.time()?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/bootbox/bootbox.min.js'?>"></script>
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/pace/pace.min.js?r='.time()?>"></script>
<script type="text/javascript">
	var base_url = "<?=$config->baseURL?>";
</script>
<?php

if (@$scripts) {
	foreach($scripts as $file) {
		echo '<script type="text/javascript" src="'.$file.'?r='.time().'"/></script>';
	}
}

?>
</html>
<body>
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
<script type="text/javascript" src="<?=$config->baseURL . 'public/vendors/sweetalert2/sweetalert2.min.js'?>"></script>
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
</html>
		