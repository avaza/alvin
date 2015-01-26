<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property Alvin_Session $session
 */
class Auth extends CI_Controller {

    protected $details;

    function __construct()
    {
        parent::__construct();
        $this->details['message'] = $this->session->flashdata('message');
    }

    /**
     * Site-wide entry point (All unauthenticated requests are redirected here)
     *
     * @return void
     */
    public function index()
    {
        $this->load->view('gui', 'login');
    }

    /**
     * @return void
     */
    public function login()
    {
        $this->load->library('form_validation');
        $this->form_validation->run();

        $this->session->checkAndRedirect();
    }

    /**
     * @return void
     */
    public function logout()
    {
        $this->session->destroy();
        $this->session->set_flashdata('message', 'Successfully Logged Out');

        $this->session->checkAndRedirect();
    }

    /**
     * @return boolean
     */
    public function authenticate()
    {
        $this->load->model('user_model');
        $user = $this->user_model->authenticate($this->input->post('email'), $this->input->post('password'));
        if($user->valid)
        {
            $this->session->setUser($user);
        }


        $this->session->keep_flashdata('message');
        $this->session->checkAndRedirect();
    }

    public function reset(){
        //TODO setup email
        echo 'email should be sent now to ' . $this->input->post('email');
        echo '<br/><a href="/">Back to Login</a>';
    }
}