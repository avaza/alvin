<?php
if( ! defined( 'BASEPATH' )) exit( 'No direct script access allowed' );

class User {

    public $data;
    protected $_ci;

    function __construct( $posted = [])
    {
        $this->_ci =& get_instance();
        $this->_ci->load->library( 'morph' );
        $this->_ci->load->model( 'user_model' );

        $this->data = null;

        if( empty( $posted )) die( 'You must POST data to create a User Instance' );

        $this->setup( $posted );
    }

    protected function setup( $post )
    {
        if( ! method_exists( $this, $post[ 'view' ])) die( 'Not a valid POST URI' );

        return call_user_func_array([ $this, $post[ 'view' ]], [ $post ] );
    }

    protected function login( $posted )
    {
        $match = [ 'email' => null, 'password' => null ];
        $login = array_intersect_key( $posted, $match );
        $this->data = $this->check( $login );

        if( ! $this->data ) return message( 'Invalid Username and/or Password.' );

        if( $this->isBlocked()) return message( 'Account Blocked ( Too many failed attempts )' );

        return $this->session();
    }

    protected function reset( $posted )
    {
        //TODO actually send reset email
        return message( 'A reset link has been sent to ' . $posted[ 'email' ], true );
    }

    protected function logout( $posted )
    {
        $this->_ci->session->valid = false;

        if( ! $posted[ 'refresh' ]) $this->_ci->session->destroy();

        return message( 'Successfully Logged Out', true);
    }

    /**
     * @param $login
     * @return mixed
     */
    private function check( $login )
    {
        $login[ 'password' ] = $this->_ci->morph->hash( $login[ 'password' ]);
        $this->attempted( $login[ 'email' ]);

        return $this->_ci->user_model->exists( $login, true );
    }

    protected function attempted( $auth_email )
    {
        $record = compact( 'auth_email' );
        $user = $this->_ci->user_model->exists( $record, true );

        if( ! $user ) return $this;

        $user->auth_atmpt = $user->auth_atmpt + 1;
        $user->auth_block = $user->auth_atmpt >= 10 ? 1 : 0;

        $this->data = $this->_ci->user_model->push( $user );

        return $this;
    }

    protected function isBlocked()
    {
        if( isset( $this->data->auth_block )) return $this->data->auth_block == 1 ? true : false;

        return true;
    }

    public function session()
    {
        if( is_null( $this->data )) return false;

        if( ! $this->_ci->session->valid ) return $this->set();

        return $this->_ci->session->userdata( 'user');
    }

    private function set()
    {
        $this->_ci->session->set_userdata( 'user', $this->data );
        $this->_ci->session->valid = true;

        return $this->_ci->session->userdata( 'user');
    }

    protected function is( $role )// CHECK ROLE
    {

    }

    protected function isNow( $role )// ADD ROLE
    {

    }

    protected function isNot( $role )// REMOVE ROLE
    {

    }

    protected function can( $action, $resource )// CHECK PERMISSIONS
    {

    }

    protected function canNow( $action, $resource )// ADD PERMISSION
    {

    }

    protected function canNot( $action, $resource )// REMOVE PERMISSION
    {

    }
}