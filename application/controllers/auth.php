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
    }

    /**
     * Site-wide entry point (All unauthenticated requests are redirected here)
     *
     * @return mixed
     */
    public function index()
    {
        $this->login();
    }

    /**
     * All unauthenticated requests are redirected here by extension of "index()"
     *
     * @return mixed
     */
    public function login()
    {
        $this->details['view'] = 'login';

        if( ! $this->input->post())
        {
            $user = $this->authenticate();
            if( $user->valid )
            {
                $this->session->setUser($user);
                redirect('home');
            }
        }

        $this->load->view('gui', $this->details);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        $this->session->destroy();

        $this->details['message'] = 'Successfully Logged Out';

        redirect('auth');
    }

    /**
     * @return boolean
     */
    public function authenticate()
    {
        $this->load->model('user_model');
        $this->load->library('form_validation');

        if( ! $this->form_validation->run()) $errors = validation_errors();

        $user = $this->user_model->authenticate(
            $this->input->post('email'),
            $this->input->post('password')
        );

        $this->details['message'] = isset( $errors ) ? $errors : null;

        return $user;
    }

    public function reset(){
        //TODO setup email
        echo 'email should be sent now to ' . $this->input->post('email');
        echo '<br/><a href="/">Back to Login</a>';
    }
}