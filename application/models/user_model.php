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
            'columns' => [ 'auth_creds', 'auth_perms' ],
            'auth_creds' => [ 'ext', 'pin' ],
            'auth_perms' => [ 'roles', 'permissions' ],
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

    protected function role( $user, $name, $add = false )
    {
        if( ! in_array( $name, $this->roles())) die( $name . ' is not a valid role.');

        $user->roles = array_merge( $user->roles, [ $name ] );

        if( ! $add ) unset( $user->roles[ $name ]);

        return $user;
    }

    protected function roles()
    {
        return array_shift( array_keys( $this->collection( 'access' )));
    }






    protected function permission( $user, $resource, $action, $value = 0 )
    {


        if( ! in_array( $resource, $permissions[ 'actions' ])) die( $action . ' is not a valid action.');
        $access = $this->collection( 'access' );

        if( ! in_array( $action, $permissions[ 'actions' ])) die( $action . ' is not a valid action.');

        $user->permissions[ $resource ] = array_merge(
            $user->permissions[ $resource ],
            [ $action => $value ]
        );

        return $user;
    }

    protected function resources( $role )
    {
        return $this->collection( 'access' )[ $role ];
    }

    protected function actions()
    {
        return $this->collection( 'access' )[ 'actions' ];
    }
}