<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property Alvin_Session $session
 * @property User $user
 */
class Auth extends CI_Controller {

    protected $details;
    protected $user;

    function __construct()
    {
        parent::__construct();
        $this->details[ 'messages' ] = [];
        $this->user = $this->session->user;
    }

    /**
     * Site-wide entry point (All unauthenticated requests are redirected here)
     *
     * @return mixed
     */
    public function index()
    {
        //TODO session user must be false or object
        if( ! $this->user ) return $this->login();

        redirect('home');

        return false;
    }

    /**
     * All unauthenticated requests are redirected here by extension of "index()"
     *
     * @return mixed
     */
    public function login()
    {
        $this->setView('login');
        $this->loadView('gui');
    }

    /**
     * @return mixed
     */
    public function reset()
    {
        $this->setView('reset');
        $this->loadView('gui');
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

    protected function post( $reset = false )
    {
        if( $this->isNotValid( 'form' )) return false;

        $post = $this->input->post( null, true );
        if( ! $reset) $post = array_merge( $post, [ 'reset' => true ]);

        $this->load->library( 'user', $post );
        $user = $this->user->data;

        if( $this->isNotValid( $user )) return false;

        return $user;
    }

    /**
     * @param null $object
     * @return bool
     */
    protected function isNotValid( $object = null )
    {
        while( empty( $this->details[ 'messages' ]) && ! is_null( $object )):

            if( is_object( $object ) && ! empty( $object->messages )) $this->details['messages'] = $object->messages;

            if( $object == 'form')
            {
                $this->load->library('form_validation');
                if( ! $this->form_validation->run()) $this->details[ 'messages' ] = validationMessages();
            }

            return false;
        endwhile;

        return true;
    }

    protected function hasPostData()
    {
        return $this->input->post( null, true ) !== false;
    }

    protected function setView( $content )
    {
        $this->details['view'] = $content;

        return $this;
    }

    protected function loadView( $interface )
    {
        if( $this->hasPostData()) $this->user = $this->post( true );

        if( isset( $this->user->valid ) && $this->user->valid ) return $this->index();

        $this->load->view( $interface, $this->details );

        return false;
    }
}