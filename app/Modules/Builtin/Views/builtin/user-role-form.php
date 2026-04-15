<?php
helper('html');

$checked = [];
foreach ($user_role as $row) {
	$checked[] = $row['id_role'];
}

$checkbox[] = ['attr' => ['id' => 'check-all', 'name' => 'check_all'], 'label' =>'Check All / Uncheck All'];
$checked_all = count($checked) == count($roles) ? true : false;
echo checkbox($checkbox, $checked_all);

echo '<hr class="mt-1 mb-2"/>';
echo '<form method="post" id="check-all-wrapper" action="">';
$checkbox = [];
foreach ($roles as $val) {
	
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
echo '</form>';