    <div id="main" role="main">
		<div id="title-bar">
			<ul id="breadcrumbs">
				<li><a href="<?php echo base_url();?>dashboard" title="Home"><span id="bc-home"></span></a></li>
				<li class="no-hover">Dashboard</li>
			</ul>
		</div>
	<div class="shadow-bottom shadow-titlebar"></div>
<div id="main-content">
  <div class="container_12">
	<div class="grid_12">
		<h1>Main</h1>
	</div>
	<div class="grid_6">
		<div class="block-border">
			<div class="block-content">
				<ul class="shortcut-list">
					<?php if($credentials->level >= 2){?>
					<li>
						<a href="javascript:window.open('/tracker/router');window.open('', '_self', '');window.close();void(0);">
							<img src="/assets/img/icons/packs/crystal/48x48/apps/wifi.png">
							Router
						</a>
					</li>
					<?php } ?>
					<li>
						<a href="javascript:window.open('/tracker/interpreter');window.open('', '_self', '');window.close();void(0);">
							<img src="/assets/img/icons/packs/crystal/48x48/apps/languages.png">
							Interpreter
						</a>
					</li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>
	</div>

