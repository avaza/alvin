<?php
$data = array('credentials' => $credentials);
$this->load->view('includes/basic_header', $data);
$this->load->view($main_content, $data);
$this->load->view('includes/basic_footer', $data);