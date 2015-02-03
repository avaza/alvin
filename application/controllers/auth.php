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
        $this->details[ 'messages' ] = [];
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

        if( ! empty( $this->input->post()))
        {
            $user = $this->authenticate();
            if( $user )
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
    public function reset()
    {
        $this->details['view'] = 'reset';

        if( ! empty( $this->input->post())) $reset = $this->resetPassword();

        $user = isset( $reset ) ? $reset : false;
        if( $user )
        {
            //TODO make this work
            $this->details[ 'messages' ] = message('EMAIL SENT TO : ' . $this->input->post( 'email' ), null, true);
            return false;
        }

        $this->load->view('gui', $this->details);
    }

    /**
     * @return mixed
     */
    public function logout()
    {
        $this->session->destroy();

        $this->details['messages'] = message( 'Successfully Logged Out', null, true );

        redirect('auth');
    }

    /**
     * @return boolean
     */
    protected function authenticate()
    {
        if( $this->isNotValid( 'form' )) return false;

        $this->load->model('user_model');

        $user = $this->user_model->authenticate(
            $this->input->post( 'email' ),
            $this->input->post( 'password' )
        );

        if( $this->isNotValid( $user )) return false;

        return $user;
    }

    protected function resetPassword()
    {
        if( $this->isNotValid( 'form' )) return false;

        $this->load->model('user_model');

        $user = $this->user_model->exists(
            [ 'auth_email' => $this->input->post( 'email' )],
            true
        );

        if( $this->isNotValid( $user )) return false;

        return true;
    }

    /**
     * @param null $object
     * @return bool
     */
    protected function isNotValid( $object = null )
    {
        while( empty( $this->details[ 'messages' ]) && ! is_null( $object )):

            if( ! is_object( $object ) && ! empty( $object->messages )) $this->details['messages'] = $object->messages;

            if( $object == 'form')
            {
                $this->load->library('form_validation');
                if( ! $this->form_validation->run()) $this->details[ 'messages' ] = validationMessages();
            }

            return false;
        endwhile;

        return true;
    }
}