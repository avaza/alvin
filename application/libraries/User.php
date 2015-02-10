<?php
if( ! defined( 'BASEPATH' )) exit( 'No direct script access allowed' );

class User {

    public $data;
    protected $_ci;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->_ci->load->library( 'morph' );
        $this->_ci->load->model( 'user_model' );

        $this->data = null;
    }

    /**
     * @param $sent
     *
     * @return mixed
     */
    public function post( $sent )
    {
        if( ! method_exists( $this, $sent[ 'view' ])) die( 'Not a valid POST URI' );

        $this->data = call_user_func_array([ $this, $sent[ 'view' ]], [ $sent ] );

        return $this;
    }

    /**
     * @param $posted
     *
     * @return array|bool|object
     */
    protected function login( $posted )
    {
        $match = [ 'email' => null, 'password' => null ];
        $login = array_intersect_key( $posted, $match );
        $data = $this->check( $login );

        if( ! $data ) return message( 'Invalid Username and/or Password.' );

        if( $this->isBlocked( $data )) return message( 'Account Blocked ( Too many failed attempts )' );

        return $this->session();
    }

    /**
     * @param $posted
     *
     * @return array|object
     */
    protected function reset( $posted )
    {
        //TODO actually send reset email
        return message( 'A reset link has been sent to ' . $posted[ 'email' ], true );
    }

    /**
     * @param $posted
     *
     * @return array|object
     */
    protected function logout( $posted )
    {
        $this->_ci->session->valid = false;

        if( ! $posted[ 'refresh' ]) $this->_ci->session->destroy();

        return message( 'Successfully Logged Out', true);
    }

    /**
     * @param $login
     *
     * @return mixed
     */
    private function check( $login )
    {
        $login[ 'password' ] = $this->_ci->morph->hash( $login[ 'password' ]);
        $this->attempted( $login[ 'email' ]);

        return $this->_ci->user_model->exists( $login, true );
    }

    /**
     * @param $email
     *
     * @return $this
     */
    protected function attempted( $email )
    {
        $record = compact( 'email' );
        $user = $this->_ci->user_model->exists( $record, true );

        if( ! $user ) return $this;

        $user->attempts = $user->attempts + 1;
        $user->blocked = $user->attempts >= 10 ? 1 : 0;

        $this->data = $this->_ci->user_model->push( $user );

        return $this;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function isBlocked( $data )
    {
        if( isset( $data->blocked )) return $data->blocked == 1 ? true : false;

        return true;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function session( $data = false )
    {
        if( ! $data ) return false;

        $this->data = $data;

        if( ! $this->_ci->session->valid ) return $this->set();

        return $this->_ci->session->userdata( 'user');
    }

    /**
     * @return mixed
     */
    private function set()
    {
        $this->_ci->session->set_userdata( 'user', $this->data );
        $this->_ci->session->valid = true;

        return $this->_ci->session->userdata( 'user');
    }

    /**
     * @param $role
     */
    protected function is( $role )// CHECK ROLE
    {

    }

    /**
     * @param $role
     */
    protected function isNow( $role )// ADD ROLE
    {

    }

    /**
     * @param $role
     */
    protected function isNot( $role )// REMOVE ROLE
    {

    }

    /**
     * @param $action
     * @param $resource
     */
    protected function can( $action, $resource )// CHECK PERMISSIONS
    {

    }

    /**
     * @param $action
     * @param $resource
     */
    protected function canNow( $action, $resource )// ADD PERMISSION
    {

    }

    /**
     * @param $action
     * @param $resource
     */
    protected function canNot( $action, $resource )// REMOVE PERMISSION
    {

    }
}