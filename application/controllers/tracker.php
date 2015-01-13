<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Tracker extends Alvin_Controller {
    
    function __construct(){
        parent::__construct();
        $this->load->model('call_data','', TRUE);
    }

    //NAVIGATION
    function router(){
        $data['credentials'] = $this->credentials();
        $data['languages'] = $this->call_data->get_language();
        $data['page_type'] = 'Router';
        $data['page'] = 'router';
        $this->load->view('call_tracking/tracker', $data);
    }

    function index($source = '', $file = ''){
        redirect('../' . $source . '/' . $file);
    } 

    function manager(){
        $data['credentials'] = $this->credentials();
        $data['languages'] = $this->call_data->get_language();
        $data['page_type'] = 'Manager';
        $data['page'] = 'manager';
        $this->load->view('call_tracking/tracker', $data);
    }

    function interpreter(){
        $data['credentials'] = $this->credentials();
        $data['languages'] = $this->call_data->get_my_languages();
        $data['page_type'] = 'Interpreter';
        $data['page'] = 'interpreter';
        $this->load->view('call_tracking/tracker', $data);
    }

    function call_data(){
        $data['main_content'] = 'reporting/call_data';
        $this->load->view('includes/templates/standard', $data);
    }

    function reports(){
        $data['main_content'] = 'reporting/reports';
        $this->load->view('includes/templates/standard', $data);
    }

    //CUDATEL VIEWS
    function cudatel_login($ext, $pin){
        header('Content-Type: application/json; charset=utf-8');
        redirect('http://192.168.1.252/cudatel/login/login?&__auth_user=' . $ext . '&__auth_pass=' . $pin);
    }

    function active_calls(){
        $this->load->view('call_tracking/cudatel_views/active_calls');
    }
    
    function agent_board(){
        $this->load->view('call_tracking/cudatel_views/agent_board');
    }

    function agent_manager(){
        $this->load->view('call_tracking/cudatel_views/agent_manager');
    }

    //DATABASE VIEWSF
    function calls_viewer($page){
        if($page == 'manager' || $page == 'router' || $page == 'interpreter'){
            $data['page'] = $page;
            $this->load->view('call_tracking/common/calls_viewer', $data);
        } else{
            redirect('dashboard');
        }        
    }

    function update_calls_viewer($page){
        header('Content-Type: text/json; charset=utf-8');
        if($page == 'manager' || $page == 'router' || $page == 'interpreter'){
            echo(json_encode($this->call_data->update_calls_viewer_data($page)));
        } else{
            return FALSE;
        }
    }

    //FORM FUNCTIONS
    function get_interpreters($language, $uuid){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_interpreters_by_language($language, $uuid)));
    }

    function get_int_by_intid($intid){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_interpreter_by_intid($intid)));
    } 

    function get_interpreter_data($id){   
        header('Content-Type: application/json; charset=utf-8');
        echo(json_encode($this->call_data->get_interpreters_data($id)));
    } 

    function get_language_code($lang){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_lang_code_from_database($lang)));
    }

    function fix_languages_please(){
        $doit = $this->call_data->fix_languages();
        echo $doit;
    }

    function check_access_code($access_code){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->check_if_access_code_exists($access_code)));
    }
    
    function get_access_code($access_code, $uuid){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_valid_access_code($access_code, $uuid)));
    }

    function end_call($uuid){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->end_call($uuid)));
    }

    function save_rep_name($rep_name, $uuid){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->save_rep_name($rep_name, $uuid)));
    }

    function save_specialf($uuid){   
        header('Content-Type: application/x-json; charset=utf-8');
        $specialf = $this->input->post('specialf');
        echo(json_encode($this->call_data->save_specialf($specialf, $uuid)));
    }

    function check_uuid($uuid){  
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->check_if_uuid_exists($uuid)));
    }

    function link_uuid($uuid, $r){  
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->link_uuid($uuid, $r)));
    }

    function ignore_uuid($uuid){  
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->ignore_uuid($uuid)));
    }

    function insert_uuid_to_db($uuid){   
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->put_uuid_in_db($uuid)));
    } 

    function post_json_call(){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->endtime_update->update_record_db()));
    }

    function add_callout($id, $number){  
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->add_callout_number_to_db($number, $id)));        
    }

    function add_callout_uuid($uuid, $number){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->add_callout_number_by_uuid($number, $uuid)));        
    }

    function ajax_post_call(){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->post_call_to_database()));
    }

    function post_call(){
        if(($this->input->post('ajax')) == '1'){
            $this->ajax_post_call();
        }else{
            $this->load->library('form_validation');
            $this->form_validation->set_rules('access_code', 'Access Code', 'trim|required|numeric');
            $this->form_validation->set_rules('rep_name', 'Rep Name', 'trim|required');  
            $this->form_validation->set_rules('language', 'Language', 'trim|required');
            $this->form_validation->set_rules('intid', 'Interpreter', 'trim|required|numeric');    
            $this->form_validation->set_rules('drop', 'Drop', 'trim|required');   
            $this->form_validation->set_rules('callout', 'Callout', 'trim|required'); 
            header('Content-Type: application/x-json; charset=utf-8');
            if($this->form_validation->run() == FALSE){
                echo json_encode(array('message' => validation_errors('<strong>', '</strong>')));
            } else{
                echo(json_encode($this->call_data->post_call_to_database()));
            }
        }
    }

    //SPECIAL REQUEST DROPDOWN
     function get_dropdown($access_code){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_sp_dd_by_access_code($access_code)));
    }

    //FORM NAME SUGGESTIONS
    function suggestions(){
        $term = $this->input->post('term',TRUE);
        if(strlen($term) < 2){
            break;
        }
        $rows = $this->call_data->GetAutocomplete(array('keyword' => $term));
        $json_array = array();
        foreach ($rows as $row){
            array_push($json_array, $row->name);
        }
        echo json_encode($json_array);
    }

    function get_callout_form($id){
        $data['id'] = $id;
        $this->load->view('call_tracking/live/callout_form', $data);
    }

    function get_incident_form($id){
        $data['id'] = $id;
        $this->load->view('call_tracking/live/incident_form', $data);
    }
}