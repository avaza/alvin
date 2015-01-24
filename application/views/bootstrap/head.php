<?php // Basic Boilerplate Header for ARC Views ?>
<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->

<!-- ================================== HEAD =================================== -->

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    
    <title>
        <?php
            $headerTitle = isset($headerTitle) ? $headerTitle : 'ARC System';
            echo $headerTitle;
        ?>
    </title>
    
    <meta name="description" content="Avaza Language Services - Real-Time Communication System">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Josh Murray"/>

    <link rel="shortcut icon" href="favicon.png">

    <link rel="stylesheet" href="/assets/stylesheets/vendors/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/stylesheets/vendors/bootstrap-theme.min.css">
    <link rel="stylesheet" href="/assets/stylesheets/all.css">
    <?php
        if( isset($styleSheets) && !empty($styleSheets)):
            foreach( $styleSheets as $sheet ):
                echo "<link rel=\"stylesheet\" href=\"/assets/stylesheets/{$sheet}\">\n";
            endforeach;
        endif;
    ?>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js"></script>
</head>