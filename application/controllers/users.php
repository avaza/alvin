<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Alvin_Controller {
     
    function __construct(){
        parent::__construct();
        $this->load->library('grocery_CRUD');
    }

    /**
     * Load primary Users View
     *
     * @param null $message
     */
    function index($message = NULL){
        $data['message'] = $message;
        $data['main_content'] = 'dashboard';
        $data['credentials'] = $this->credentials();
        $this->load->view('includes/template', $data);
    }

    //ADD USER
    function add_user($message = NULL){
        $this->load->model('user_m');
        $data['languages'] = $this->user_m->get_language_list(); //populates the language dropdown on pageload.
        $data['message'] = $message;
        $data['main_content'] = 'system/add_user';
        $this->load->view('includes/basic_header');
        $this->load->view('includes/standard_header');
        $this->load->view('includes/standard_sidebar');
        $this->load->view('system/add_user', $data);
        $this->load->view('includes/basic_footer');
       
    }

    function make_user(){
         $this->load->model('user_m','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->user_m->register()));
    }

    function user_details($output = null){ 
        $this->load->view('includes/grid_header');
        $this->load->view('includes/sidebar');  
        $this->load->view('system/details',$output);
        $this->load->view('includes/grid_footer');
    }

    function user_detail(){
        $crud = new grocery_CRUD();

        $crud->set_theme('datatables');
        $crud->set_table('users');
        $crud->columns('fname', 'lname', 'intid', 'ext', 'username', 'email', 'level', 'lang');
        $crud->set_subject('User');
        $crud->display_as('fname', 'First Name');
        $crud->display_as('fname', 'Last Name');
        $crud->display_as('intid', 'Interpreter ID');
        $crud->display_as('ext', 'Extension');
        $crud->display_as('username', 'Username');
        $crud->display_as('email', 'Email Address');
        $crud->display_as('level', 'Permission Level');
        $crud->display_as('lang', 'Primary Language');
        $crud->unset_delete();
        $output = $crud->render();

        $this->user_details($output);
    }

    function create_user(){
        
        if(($this->input->post('ajax')) == '1'){

            $this->load->model('user_m','', TRUE);
            if($query = $this->user_m->register()){
                $message = '<div class="alert success"><span class="hide">x</span><strong>User Added</strong></div>';
                $this->add_user($message);
            } 
        }else{

            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('fname', 'Name', 'trim|required');
            $this->form_validation->set_rules('lname', 'Last Name', 'trim|required');
            $this->form_validation->set_rules('intid', 'Interpreter ID', 'trim|required|numeric');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
            $this->form_validation->set_rules('username', 'Username', 'trim|required|min_length[4]');
            $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
            $this->form_validation->set_rules('password2', 'Password Confirmation', 'trim|required|matches[password]');
            $this->form_validation->set_rules('ext', 'Extension', 'trim|required|exact_length[4]|numeric');
            $this->form_validation->set_rules('pin', 'Extension Pin','trim|required|exact_length[4]|numeric'); 
            $this->form_validation->set_rules('langs', 'Number of Languages','trim|required|exact_length[1]|numeric');   
            $this->form_validation->set_rules('lang', 'Primary Language', 'required');
            $this->form_validation->set_rules('level', 'Permission Level', 'required');

            if($this->form_validation->run() == FALSE){
                $this->add_user();
            } else{           
                $this->load->model('user_m');
                if($query = $this->user_m->register()){
                    $message = '<div class="alert success"><span class="hide">x</span><strong>User Added</strong></div>';
                    $this->add_user($message);
                } else{
                    $this->add_user();          
                }
            }
        }
    }

    function check_intid($intid){
        
        $this->load->model('user_m','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->user_m->check_if_intid_exists($intid)));
    } 

    function check_username($username){
        
        $this->load->model('user_m','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->user_m->check_if_username_exists($username)));
    } 

    function check_extension($extension){
        
        $this->load->model('user_m','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->user_m->check_if_extension_exists($extension)));
    }

}
