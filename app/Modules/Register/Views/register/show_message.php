<?php 
$this->extend('App\Modules\Register\Views\register\layout');
$this->section('content');

$type = $message['status'] == 'error' ? 'danger' : 'success';
$title = $message['status'] == 'error' ? 'Error...' : 'Sukses...';
$loginLogoUrl = public_image_url($setting_aplikasi['logo_login'] ?? '', 'logo_only.png');
?>
<style>
	.card-header img {
		width: 100%;
    	max-width: 100%;
	}
</style>
<div class="card-header pb-3">
	<div class="logo">
		<img src="<?=$loginLogoUrl?>" alt="Logo Login">
	</div>
</div>
<div class="card-body">
	<div class="alert alert-last alert-<?=$type?>">
		<h4><?=$title?></h4>
		<?=$message['message']?>
	</div>
</div>
<?= $this->endSection() ?>
