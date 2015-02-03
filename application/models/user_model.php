<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->_ci->load->library('session');

        $this->table = 'users';

        $this->compress = [
            'columns' => [ 'auth_creds' ],
            'auth_creds' => [ 'ext', 'pin' ]
        ];

        $this->parser = [
            'email' => 'auth_email',
            'password' => 'auth_passw'
        ];
    }

    /**
     * @param $auth_email
     * @param $auth_passw
     * @return \stdClass
     */
    public function authenticate( $auth_email, $auth_passw )
    {
        $user = compact( 'auth_email', 'auth_passw' );

        $user = $this->getOrFail( $user );

        if( ! $user ) return message( 'Invalid Username and/or Password.' );

        if( $this->blocked( $user )) return message( 'Account Blocked ( Too many failed attempts )' );

        $this->attempt( $user[ 'auth_email' ], '-' );

        return message( null, $user, true );

    }

    /**
     * @param $user
     * @return mixed
     */
    private function getOrFail( $user )
    {
        $user[ 'auth_passw' ] = $this->hash( $user[ 'auth_passw' ]);

        $this->attempt( $user[ 'auth_email' ], '+' );

        return $this->exists( $user, true );
    }

    /**
     * @param $user
     * @return bool
     */
    private function blocked( $user )
    {
        return $user->auth_block == 1 ? true : false;
    }

    /**
     * @param $auth_email
     * @param $action
     * @return array|mixed
     */
    private function attempt( $auth_email, $action )
    {
        $user = compact( 'auth_email' );

        if( $this->exists( $user ))
        {
            $user = $this->pull( $user, 1 );
            $user->auth_atmpt = $action == '+' ? $user->auth_atmpt+1 : 0;
            $user->auth_block = $user->auth_atmpt >= 10 ? 1 : 0;
            $this->push( $user );
        }

        return $user;
    }

    protected function parseInput()
    {
        if( ! isset( $this->parser )) return $this->input->post( null, true );

        $parsed = ['input' => $this->input->post( null, true )];

        foreach($this->parser as $input => $parse):
            $parsed[ $parse ] = $this->input->post( $input, true );
        endforeach;

        return $parsed;
    }
}