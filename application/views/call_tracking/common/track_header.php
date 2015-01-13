<link rel="stylesheet" href="/css/all.css">
<link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/tracker.css">
<?php if($page_type == 'Manager'){ ?>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/call_tracking/manager.js"></script>
<?php } else if($page_type == 'Router'){?>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/call_tracking/router.js"></script>
<?php } else{?>
<script type="text/javascript" src="<?php echo base_url();?>assets/javascripts/application/call_tracking/interpreter.js"></script>
<?php }?>
<script type="text/javascript">
var intid = <?php echo $credentials->intid;?>;
jQNC(document).ready(function() {
    jQNC.getJSON('/gui/login/login?&__auth_user=<?php echo $credentials->ext;?>&__auth_pass=<?php echo $credentials->pin;?>');
});
</script>
<div id="main" role="main">
    <div id="title-bar">
        <ul id="breadcrumbs">
            <li><a href="<?php echo base_url();?>dashboard" title="Home"><span id="bc-home"></span></a></li>
            <li class="no-hover"><a>Call Tracking</a></li>
            <li class="no-hover"><?php echo $page_type;?></li>
        </ul>
    </div>
    <div class="shadow-bottom shadow-titlebar"></div>
    <a class="fancybox" id="hidden_link" href="#transfer" title="Lorem ipsum dolor sit amet"></a>
    <div id="transfer" style="width:400px;display: none;">
    </div>
    <div id="main-content">
        <a href="javascript:void(0);" id="hider"><img id="hicon" style="margin-left: 5px;" src="<?php echo base_url();?>assets/img/hider.png"></a>
