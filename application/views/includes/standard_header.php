<body id="top" class="hider">
  <div id="container">
    <div id="header-surround"><header id="header">
    	<img src="<?php echo base_url();?>assets/img/logo.png" alt="Grape" class="logo">
		<div class="divider-header divider-vertical"></div>
		<a href="javascript:void(0);" onclick="$('#info-dialog').dialog({ modal: true });"><span class="btn-info"></span></a>
			<div id="info-dialog" title="Information" style="display: none;">
				<p>Welcome to Avaza's database system.</p>
			</div>
		<ul class="toolbox-header">
		<?php if($credentials->DR_REP != 1){?>
			<li>
				<a href="<?php echo base_url() ;?>dashboard/status" rel="tooltip" class="toolbox-action" title="Timeclock"><span class="i-24-inbox-document"></span></a>
			</li>
			<li>
				<a rel="tooltip" class="toolbox-action" title="Coming Soon ..." href="#"><span class="i-24-folder-horizontal-open"></span></a>
			</li>
		<?php } ?>
			<li>
				<a href="<?php echo base_url() ;?>tracker/interpreter" rel="tooltip" class="toolbox-action" title="Take Calls"><span class="i-24-phone"></span></a>
			</li>
		</ul>
		<div id="user-info">
			<p>
				<span class="messages">Hello <a href="status"><?php echo $credentials->fname;?></a></span>
				<?php if($credentials->DR_REP != 1){?>
					<a href="javascript:void(0)" class="toolbox-action button">Settings</a>
				<?php } ?>
				<a href="<?php echo base_url() ;?>login/do_logout" class="button red">Logout</a>
			</p>
		</div>
    </header></div>
<div class="fix-shadow-bottom-height"></div>