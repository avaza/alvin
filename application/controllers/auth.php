<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth
 *
 * @property ARC_Session $session
 * @property User $user
 */
class Auth extends ARC_Controller {

    protected $content;

    function __construct()
    {
        parent::__construct();

        $this->sources = [
            'post' => [
                'type' => 'library',
                'name' => 'user'
            ]
        ];

        $this->content = [
            'view' => 'index',
            'data' => null,
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
     * @param $post
     *
     * @return mixed
     */
    public function login( $post = true )
    {
        $this->setView( 'login' );

        return $this->getView( 'gui', $post );
    }

    /**
     * URI - POST Location for password reset
     *
     * @param $post
     *
     * @return mixed
     */
    public function reset( $post = true )
    {
        $this->setView( 'reset' );

        return $this->getView( 'gui', $post  );
    }

    /**
     * URI - Location for logout redirect
     *
     * @param $post
     *
     * @return mixed
     */
    public function logout( $post = true )
    {
        $this->setView( 'login' );

        return $this->getView( 'gui', $post  );
    }
}