<?php if( ! defined( 'BASEPATH' )) exit( 'No direct script access allowed' );

class User extends Service{

    function __construct( $params = null )
    {
        $this->_ci->load->model( 'user_model' );

        $default = [ 'email' => null, 'password' => null, 'reset' => false ];

        if( ! is_null( $params )) $params = array_merge( $default, $params );

        $this->set( $params );
    }

    protected function set( $user )
    {
        if( $user[ 'reset' ]) return $this->reset( $user );

        $user = $this->login( $user );

        while( is_null( $user )):
            $user = $this->session();
            die( 'Invalid USER : >' . json_encode( $user ) . '<');
        endwhile;

        $this->data = $user;

        return $this;
    }

    protected function reset( $user )
    {
        //TODO actually send reset email
        return message( 'A reset link has been sent to ' . $user[ 'email' ], null, true );
    }

    protected function login( $user )
    {
        if( ! is_array( $user )) return null;

        $login = [ 'email' => null, 'password' => null ];

        if( array_keys( $user ) == array_keys( $login )) return $this->getIfValid( $user );

        return null;
    }

    protected function session( $user = null )
    {
        if( ! is_null( $user )) $this->_ci->session->user = $user;

        if( ! isset( $this->_ci->session->user )) return null;

        return $this->_ci->session->user;
    }

    protected function getIfValid( $record )
    {
        $user = $this->getIfExists( $record, [ 'user_model' ], [ 'password' ]);

        if( ! $user && isset( $record[ 'email' ]))
        {
            $this->attempted( $record[ 'email' ]);
            return message( 'Invalid Username and/or Password.' );
        }

        if( $this->isBlocked()) return message( 'Account Blocked ( Too many failed attempts )' );

        return $user;
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