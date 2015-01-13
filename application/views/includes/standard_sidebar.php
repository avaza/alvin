<aside id="sidebar">
	<?php $lvl = $credentials->level;?>
	<div id="search-bar">
		<form id="search-form" name="search-form" action="http://www.google.com/" method="get">
			<input type="text" id="query" name="q" value="" autocomplete="off" placeholder="Search">
		</form>
	</div>
	<section id="login-details">
		<img class="img-left framed" src="/assets/img/misc/avatar_small.png" alt="Hello">
		<h3>Logged in as</h3>
		<h2><a class="user-button" href="javascript:void(0);"><?php echo $credentials->username;?><span class="arrow-link-down"></span></a></h2>
		<ul class="dropdown-username-menu">
		<?php if($credentials->DR_REP != 1){?>
			<li><a href="/dashboard/status">Change Status</a></li>
		<?php } ?>
			<li><a href="/login/do_logout">Logout</a></li>
		</ul>
		<div class="clearfix"></div>
		</section>
	<nav id="nav">
    	<ul class="menu collapsible shadow-bottom">
    		<li><a href="#"><img src="/assets/img/icons/packs/fugue/16x16/database--arrow.png">Call Tracking</a>
    			<ul class="sub">
					<?php if($lvl>='1'){echo '<li><a href="javascript:window.open(\'/tracker/interpreter\');window.open(\'\', \'_self\', \'\');window.close();void(0);">Interpreter</a></li>';}?>
				    <?php if($lvl>='2'){echo '<li><a href="javascript:window.open(\'/tracker/router\');window.open(\'\', \'_self\', \'\');window.close();void(0);">Router</a></li>';}?>
				    <?php if($lvl>='3'){echo '<li><a href="javascript:window.open(\'/tracker/manager\');window.open(\'\', \'_self\', \'\');window.close();void(0);">Manager</a></li>';}?>
    			</ul>
			<?php if($lvl>='3'){echo '<li><a href="/crud/view_crud/clients"><img src="/assets/img/icons/packs/fugue/16x16/address-book-blue.png">Clients</a>';}?>
			<?php if($lvl>='3'){echo '<li><a href="/crud/view_crud/interpreters"><img src="/assets/img/icons/packs/fugue/16x16/user-silhouette.png">Interpreters</a>';}?>
    		<?php if($lvl>='3'){echo '<li><a href="/crud/view_crud/users"><img src="/assets/img/icons/packs/fugue/16x16/application-monitor.png">Users</a>';}?>
    		</li>
			<?php if($lvl>='4'){echo '<li><a href="#"><img src="/assets/img/icons/packs/fugue/16x16/newspaper.png">Invoicing</a>';}?>
    			<ul class="sub">
				    <?php if($lvl>='4'){echo '<li><a href="/invoicing/inv_bill">Client Invoicing</a></li>';}?>
				    <?php if($lvl>='4'){echo '<li><a href="/invoicing/inv_pay">Interpreter Payments</a></li>';}?>
				    
    			</ul>
			<?php if($lvl>='6'){echo '<li><a href="#"><img src="/assets/img/icons/packs/fugue/16x16/application-terminal.png">Administrator Tasks</a>';}?>
				    			<ul class="sub">
				    <?php if($lvl>='6'){echo '<li><a href="/tracker/call_data"><img src="/assets/img/icons/packs/fugue/16x16/table.png">Call Data</a></li>';}?>
				    <?php if($lvl>='6'){echo '<li><a href="/tracker/reports"><img src="/assets/img/icons/packs/fugue/16x16/chart.png">Reports</a></li>';}?>
				    <?php if($lvl>='4'){echo '<li><a href="/reports/timeclock">Timeclock Reports</a></li>';}?>
				    
    			</ul>
    		</li>
    	</ul>
	</nav>
</aside>

