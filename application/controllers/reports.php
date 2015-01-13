<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Reports extends Alvin_Controller {
	
	function __construct(){
        parent::__construct();
        	$this->load->database();
        	$this->load->library('session');
        	$this->load->library('PHPExcel');
        	$this->load->model('reports_model');
    }

    //NAVIGATION
    function timeclock(){
        $this->load->helper('download');
        $data = stripcslashes($this->input->post('csv_data'));
        if($data){
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"my-data.csv\"");
            force_download("my-data.csv", $data); 
        } else{
            $data['main_content'] = 'reporting/timecard_report';
            $this->load->view('includes/templates/standard', $data);
        }        
    }
    //END NAVIGATION 
    function status_change($type, $user){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->status_change($type, $user)));
    }
    //APIS
    function send_punch(){
        header('Content-Type: application/x-json; charset=utf-8');
        $timestamp = $this->input->post('timestamp');
        $id = $this->input->post('id');
        if($timestamp != 0 && $id == 0){//ADD NEW PUNCH FROM TIMESTAMP
            $type = 3;
            $punch_array = array('this_punch' => $timestamp);
        } else if($id != 0 && $timestamp != 0){//UPDATE EXISTING PUNCH
            $type = 2;
            $punch_array = array('id' => $id, 'this_punch' => $timestamp);
        } else{//ADD NEW PUNCH FROM RIGHT NOW
            $type = 1;
            $punch_array = array('this_punch' => date('Y-m-d H:i:s'));
        }
        $punch_array['type'] = $type;
        $punch_array['status_type'] = $this->input->post('type');
        $punch_array['intid'] = $this->input->post('intid');
        $punch_array['status_name'] = $this->input->post('status_name');
        $punch_array['time_punch'] = $this->reports_model->is_time_punch($punch_array);
        $completed = $this->reports_model->complete_punch($punch_array);
        if(isset($completed['punched'])){
            $completed['msg'] = $punch_array['status_name'] . ' at ' . $punch_array['this_punch'];
        }
        echo(json_encode($completed));        
    }
    
    function get_timepunches(){
    	header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->get_timepunches()));
    }

    function get_edit_punch($id){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->get_edit_punch($id)));
    }

    function whos_clocked_in(){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->whos_clocked_in()));
    }

    function get_staff(){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->get_staff()));
    }

    function get_staff_member($intid){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->get_staff_member($intid)));
    }

    function add_punch($intid, $timestamp, $type){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->reports_model->add_punch()));
    }

    function edit_punch($id, $timestamp){
        $this->load->model('login_model');
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->login_model->edit_punch()));
    }
    //END APIS    
}