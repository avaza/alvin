<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach($js_files as $file): ?>
	<script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
</head>
<body class="special-page" id="crud">
    <div id="crud_grid">
		<?php echo $output;?>
    </div>
</body>
</html>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/crud.js"></script>
