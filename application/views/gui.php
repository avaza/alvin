<?php

$template = isset( $guiTemplate ) ? $guiTemplate : 'none';

$templates = [
    'main' => '/gui',
    'none' => ''
];

$content = isset( $pageContent ) ? $pageContent : 'missing';

$this->load->view( 'bootstrap' . $templates[$template] . '/head' );
$this->load->view( 'content/' . $content );
$this->load->view( 'bootstrap' . $templates[$template] . '/foot' );