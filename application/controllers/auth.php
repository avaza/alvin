<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
    }

    /**
     * Site-wide entry point (All unauthenticated requests point here)
     * @param string $message - Auth message for view display
     *
     * @return void
     */
    public function index($message = null)
    {
        if($this->auth_model->authenticated())
        {
            redirect('home');
        }

        $data['message'] = $message;
        $data['main_content'] = 'auth';
        $this->load->view('auth', $data);
    }

    function create_first_user()
    {
    }

    /**
     * Retrieve session credentials and display as JSON string
     *
     * @return void
     */
    public function creds()
    {
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->auth_model->authenticated()));
    }

    /**
     * Login : if POST [username, password]
     * Logout: if no POST values
     *
     * @return void
     */
    public function set()
    {
        $auth = $this->auth_model->authenticate();
        if($auth->valid){
            redirect('home');
        }

        $this->index($auth->message);
    }
}