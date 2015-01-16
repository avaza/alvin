<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property Alvin_Session $session
 */
class Auth extends CI_Controller {

    protected $message;
    protected $details;
    protected $content;

    function __construct()
    {
        parent::__construct();
        $this->load->model('validation_model');
        $this->message = $this->session->flashdata('message');
    }

    /**
     * Site-wide entry point (All unauthenticated requests point here)
     *
     * @return void
     */
    public function index()
    {
        $this->content = 'auth';

        $this->session->checkAndRedirect();

        $this->load->view('gui', $this->data());
    }

    /**
     * Retrieve session credentials and display as JSON string
     *
     * @return void
     */
    public function creds()
    {
        $this->details = $this->session->user;

        $this->load->view('api', $this->data());
    }

    /**
     * Login : if POST [username, password]
     * Logout: if no POST values
     *
     * @return void
     */
    public function set()
    {
        $this->validation_model->authenticate();

        $this->index();
    }

    protected function data()
    {
        $data = [];
        $vars = [ 'content', 'details', 'message' ];

        foreach($vars as $var)
        {
            if( isset($this->$var) ) $data[$var] = $this->$var;
        }

        return $data;
    }

}