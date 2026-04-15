<!DOCTYPE HTML>
<html lang="en">
<head>
<title>Error !!!</title>
<meta name="descrition" content="An Error Occured"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
	font-family: 'Open Sans','Segoe Ui';
	font-size: 1em;
}
.error-card {
	margin-bottom: 20px;
}
.error-border-danger {
	border: 1px solid #ffcbcb;;
}
.error-danger {
    background: #fbc5c5;
    color: #ef4949;
}

.error-border-warning {
	border: 1px solid #ffe0a7;
}
.error-warning {
	background: #ffe0a7;
    color: #ffa500;
}
.error-card-title, .error-card-content {
	padding: 20px;
}
.error-card-content {
	background: #FFFFFF;
	color: #000000;
}
</style>
</head>
<body>
<?php
if (empty($err_type)) {
	$err_type = 'danger';
}

if (empty($title)) {
	$title = 'ERROR';
}
?>
<div class="error-card error-border-<?=$err_type?>">
	<div class="error-card-title error-<?=$err_type?>">
		<?=$title?>
	</div>
	<div class="error-card-content">
		<?=$content?>
	</div>
</div>
</body>
</html>