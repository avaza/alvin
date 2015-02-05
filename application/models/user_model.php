<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();

        $this->table = [
                 'table' => 'users',
                    'id' => [ 'input' => 'user_id' ],
            'auth_email' => [ 'input' => 'email' ],
            'auth_passw' => [ 'input' => 'password' ],
            'auth_atmpt' => [ 'input' => 'count' ],
            'auth_block' => [ 'input' => 'key' ],
            'auth_creds' => [ 'press' => [ 'ext', 'pin' ]],
            'auth_level' => [ 'press' => [ 'roles', 'permissions' ]]
        ];
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