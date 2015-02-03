<?php // head ARC View : ACE admin GUI header ?>
<!DOCTYPE html>
<html lang="en">

    <!-- ======================================== HEAD ======================================= -->

	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta charset="utf-8" />
        <title>
            <?php
                $headerTitle = isset($headerTitle) ? $headerTitle : 'ARC System';
                echo $headerTitle;
            ?>
        </title>

		<meta name="description" content="" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

		<!-- bootstrap & fontawesome -->
		<link rel="stylesheet" href="/assets/stylesheets/gui/bootstrap.css" />
		<link rel="stylesheet" href="/assets/stylesheets/gui/font-awesome.css" />

		<!-- page specific plugin styles -->

		<!-- text fonts -->
		<link rel="stylesheet" href="/assets/stylesheets/gui/ace/ace-fonts.css" />

		<!-- ace styles -->
		<link rel="stylesheet" href="/assets/stylesheets/gui/ace/ace.css" class="ace-main-stylesheet" id="main-ace-style" />

		<!--[if lte IE 9]>
			<link rel="stylesheet" href="/assets/stylesheets/gui/ace/ace-part2.css" class="ace-main-stylesheet" />
		<![endif]-->

		<!--[if lte IE 9]>
            <link rel="stylesheet" href="/assets/stylesheets/gui/ace/ace-ie.css" />
		<![endif]-->

        <?php
            if( isset($styleSheets) && !empty($styleSheets)):
                foreach( $styleSheets as $sheet ):
                    echo "<link rel=\"stylesheet\" href=\"/assets/stylesheets/{$sheet}\">\n";
                endforeach;
            endif;
        ?>

		<script src="/assets/javascripts/gui/ace/ace-extra.js"></script>


		<!--[if lte IE 8]>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>

    <!-- ======================================== BODY ======================================= -->

    <body class="no-skin">
<?php $this->load->view('bootstrap/gui/_nav'); ?>