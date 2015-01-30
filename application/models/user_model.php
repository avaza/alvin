<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//TODO auth_email
class User_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->_ci->load->library('session');
        $this->table = 'users';
    }

    public function authenticate($auth_email, $auth_passw)
    {
        $auth_passw = $this->hash($auth_passw);
        $user = compact('auth_email', 'auth_passw');

        if($this->exists($user))
        {
            $user = $this->pull($user, 1);
            $user = $this->authorize($user);
            return $user;
        }

        $this->attempts($auth_email, '+');

        return invalidWith( 'Invalid Username and/or Password.' );
    }

    private function authorize($user)
    {
        $blocked = $user->auth_block == 1 ? true : false;
        if( ! $blocked)
        {
            $this->_ci->session->setUser( validWith( $user ));
            $this-> attempts($user->auth_email, '-');
            return $user;
        }

        return invalidWith( 'Account Blocked (Too many failed attempts)' );
    }

    private function attempts($auth_email, $action)
    {
        $user = compact('auth_email');

        if($this->exists($user))
        {
            $user = $this->pull($user, 1);
            $user->auth_atmpt = $action == '+' ? $user->auth_atmpt+1 : 0;
            $user->auth_block = $user->auth_atmpt >= 10 ? 1 : 0;
            $this->push($user);
        }

        return;
    }
}