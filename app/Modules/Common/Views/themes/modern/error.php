<div class="card">
	<div class="card-header">
		<h5 class="card-title">
		<?php
		if (empty($title)) {
			echo 'Error';
		} else {
			echo $title;
		}		
		?></h5>
	</div>
	
	<div class="card-body">
		<?php
		helper('admin/html');
		if (!empty($msg)) {
			show_message($msg);
		}
		?>
	</div>
</div>