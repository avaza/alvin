<?php
echo $form_load;
$data = array('credentials' => $credentials);
$this->load->view('includes/basic_header', $data);
$this->load->view('includes/standard_header', $data);
$this->load->view('includes/standard_sidebar', $data);
$this->load->view($main_content, $data);
$this->load->view('includes/basic_footer', $data);
?>