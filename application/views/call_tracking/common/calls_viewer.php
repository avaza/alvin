<?php
$this->load->view('includes/basic_header');
$data = array('page' => $page, 'credentials' => $credentials);
$this->load->view('call_tracking/live/database_calls', $data);
$this->load->view('includes/basic_footer');
?>