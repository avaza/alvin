<?php
$data = array('credentials' => $credentials);
$this->load->view('includes/basic_header');
$this->load->view('includes/standard_header', $data);
$this->load->view('includes/standard_sidebar', $data);
$this->load->view('call_tracking/common/track_header', $data);
$this->load->view('call_tracking/common/tracking_form');
$this->load->view('call_tracking/common/interpreter_script', $data);
$this->load->view('call_tracking/common/data_view', $data);
if($credentials->DR_REP == 1){
	$this->load->view('call_tracking/common/status', $data);
}
$this->load->view('call_tracking/common/track_footer');
$this->load->view('includes/basic_footer');
?>