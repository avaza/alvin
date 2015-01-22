<?php
if( ! isset($pageContent) )
    $contentToLoad = 'content/missing';
else
    $contentToLoad = 'content/' . $pageContent;

$this->load->view('bootstrap/head');
$this->load->view('bootstrap/body');
$this->load->view( $contentToLoad );
$this->load->view('bootstrap/foot');