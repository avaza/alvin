<script> 
function change_iframe(pixels) {
	jQNC('#crud_frame').css('height', pixels+5+'px');
}
</script>
    <div id="main" role="main">
		<div id="title-bar">
			<ul id="breadcrumbs">
				<li><a href="<?php echo base_url() ;?>dashboard" title="Home"><span id="bc-home"></span></a></li>
					<?php $singular = rtrim($crud_type, "s");?>
					<li class="no-hover"><a><?php echo ucfirst($crud_type); ?></a></li>
					<li class="no-hover">Add/Edit <?php echo ucfirst($singular); ?></li>	
			</ul>
		</div>
		<div class="shadow-bottom shadow-titlebar"></div>
		<div id="main-content">
		<div class="container_12">
		
			<div class="grid_12">
				<h1>Add/Edit</h1>
				<p>Select an option below to edit details in the system.</p>
			</div>
			<div class="grid_12">
				<div class="block-border" id="frame_border">
					<div class="block-content" id="frame_marker">
						<IFRAME id="crud_frame" SRC='<?php echo base_url();?>crud/<?php echo $crud_type;?>_detail' SCROLLING='yes' WIDTH='100%' HEIGHT="100%" FRAMEBORDER='no'></IFRAME>
					</div>
				</div>
			</div>
		</div>
	</div>