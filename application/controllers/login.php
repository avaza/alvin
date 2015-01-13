<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login extends Alvin_Controller {
     
    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->model('auth_model');
    }
        
    public function index($msg = NULL){
        echo 'test';
        $data['msg'] = $msg;
        $data['main_content'] = 'authentication/login_form';
        $this->load->view('includes/templates/basic', $data);
    }

    public function do_login(){
        $db_login = $this->auth_model->systemLogin();
        if(!$db_login['valid']){
            $this->index($db_login['msg']);
        } else{
            redirect('dashboard');
        }
    }
    
    public function do_logout(){
        $db_logout = $this->auth_model->systemLogout();
        if($db_logout){
            $this->index('You Have Successfully Logged Out');
        } else{
            $this->index('Logout Incomplete, Please Try Again.');
        }
    }
}