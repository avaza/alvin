<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
  	<link rel=dns-prefetch href="//fonts.googleapis.com">
  	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>Avaza Database Control</title>
    <meta name="description" content="Database System Avaza Language Services Corporation">
    <meta name="author" content="Josh Murray">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="/js/cc_out.js"></script>
    <!--<script src="/js/jquery.microtabs.js"></script>-->
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/forms.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/buttons.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/style.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/960.fluid.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/main.css">    
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/lists.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/icons.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/libraries/jquery.fancybox.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/notifications.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/typography.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/tables.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/charts.css">
    <link rel="stylesheet" href="<?php echo base_url();?>assets/stylesheets/jquery-ui-1.8.22.custom.css">
    <script src="<?php echo base_url();?>assets/javascripts/libraries/modernizr-2.0.6.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.22/jquery-ui.min.js"></script>
    <script type="text/javascript">
    jQNC = jQuery.noConflict();
    jQNC(document).ready(function(){
        jQNC("#accordion")
            .accordion({
                header: "> div > h3"
            })
            .sortable({
                axis: "y",
                handle: "h3",
                stop: function( event, ui ){
                    ui.item.children( "h3" ).triggerHandler( "focusout" );
                }
            });
    });
    </script>
</head>