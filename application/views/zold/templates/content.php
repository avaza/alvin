<?php

/**
 * HTML Content Loader
 */

if(isset($content)){
   $this->load->view( 'headers/' . $content, $data );
   $this->load->view( $content, $data );
   $this->load->view( 'footers/' . $content, $data );
}
else
{
    die('No View Specified');
}