<body class="special-page">
<!-- Begin of #container -->
<div id="container">
  	<!-- Begin of LoginBox-section -->
    <section id="login-box">
    	<div class="block-border">
    		<div class="block-header">
    			<h1>You Must Login To Proceed</h1>
    		</div>
    		<?php echo form_open('login/do_login', 'method="post" id="login-form" class="block-content form"');?>
				<p class="inline-small-label">
					<label for="username">Username</label>
					<?php echo form_input('username', set_value(''), 'id="username"');?>
				</p>
				<p class="inline-small-label">
					<label for="password">Password</label>
					<?php echo form_password('password', set_value(''), 'id="password"');?>
				</p>
				<?php if(isset($message)) echo '<p class="error">' . $message . '</p>';?>
				<div class="clear"></div>
				<!-- Begin of #block-actions -->
    			<div class="block-actions">
					<ul class="actions-left">
						<li class="divider-vertical"></li>
						<li><a class="button red" id="reset-login" href="http://www.google.com">Cancel</a></li>
					</ul>
					<ul class="actions-right">
					<?php echo form_submit('submit', 'Login', 'class="button"');?>
					</ul>
				</div> 
				<!--! end of #block-actions -->
            <?php echo form_close();?>
    	</div>
    </section>
    <!--! end of LoginBox-section -->
</div> 
<!--! end of #container -->