<?php
if ($message['status'] == 'error') {
	show_message($message);
	exit;
}
helper('html');
?>
<form method="post" action="<?=base_url()?>/builtin/permission/ajaxEdit">
<div class="row mb-3">
	<div class="col-sm-12">
		<label class="form-label">Pilih Permission</label>
		<?php
		foreach ($module_permission[$request->getGet('id')] as $val) 
		{
			$checkbox['attr']['name'] = 'permission[]';
			$checkbox['attr']['value'] = $val['id_module_permission'];
			$checkbox['attr']['data-nama-permission'] = $val['nama_permission'];
			$checkbox['attr']['id'] = 'permission-' . $val['id_module_permission'];
			$checkbox['label'] = $val['nama_permission'];
			
			$checked = false;
			if (in_array($val['id_module_permission'], $role_permission)) {
				$checked = true;
			}
			echo checkbox($checkbox, $checked);
		}
		?>
		<a href="javascript:void(0)" class="check-all-permission small">Check All</a> <small>|</small> <a href="javascript:void(0)" class="uncheck-all-permission small">Uncheck All</a>
	</div>
</div>
</form>