<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property Alvin_Session $session
 * @property User $user
 */
class Auth extends Alvin_Controller {

    protected $content;

    function __construct()
    {
        parent::__construct();

        $this->content = [
            'view' => 'index',
            'library' => 'user',
            'messages' => []
        ];

    }

    /**
     * URI - Site-wide entry point
     * NO AUTH redirect Location
     *
     * @return mixed
     */
    public function index()
    {
        if( $this->session->valid ) redirect('home');

        return $this->login();
    }

    /**
     * URI - POST Location for system login
     *
     * @return mixed
     */
    public function login()
    {
        $this->setView( 'login' );

        return $this->getView( 'gui' );
    }

    /**
     * URI - POST Location for password reset
     *
     * @return mixed
     */
    public function reset()
    {
        $this->setView( 'reset' );

        return $this->getView( 'gui' );
    }

    /**
     * URI - Location for logout redirect
     *
     * @return mixed
     */
    public function logout()
    {
        $this->setView( 'login' );

        return $this->getView( 'gui' );
    }
}