<?php
helper ('html');
$checkbox[] = ['attr' => ['id' => 'check-all', 'name' => 'check_all'], 'label' =>'Check All / Uncheck All'];
$checked_all = count($checked) == count($roles) ? true : false;
echo checkbox($checkbox, $checked_all);

echo '<hr class="mt-1 mb-2"/>';
?>
<form method="post" id="check-all-wrapper" action="">
<?php
	$checkbox = [];
	foreach ($roles as $val) 
	{
		$attr = ['id' => $val['id_role'], 'name' => 'id_role[]', 'value' => $val['id_role'], 'class' => 'check-role'];
		if (in_array($val['id_role'],$checked)) {
			$attr['checked'] = 'checked';
		}
		$checkbox[] = [
						'attr' => $attr
						, 'label' => $val['judul_role']
					];
	}

	echo checkbox($checkbox);
	
?>
<p class="mt-0 mb-0" style="line-height:20px">Parent akan ikut ter assign. Misal <strong>Website &raquo; Role &raquo; User Role</strong>, jika menu <strong>User Role</strong> di assign ke role admin, maka Menu <strong>Role</strong> dan <strong>Website</strong> jika belum ter assign akan ikut ter assign</p>
</form>