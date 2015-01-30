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
        $user = compact('auth_email', 'auth_passw');

        if( $this->invalid( $user )) return invalidWith( 'Invalid Username and/or Password.' );

        $user = $this->pull( $user, 1 );

        if( $this->blocked( $user )) return invalidWith( 'Account Blocked (Too many failed attempts)' );

        $this->attempt($user['auth_email'], '-');

        return validWith( $user );
    }

    private function invalid($user)
    {
        $user['auth_passw'] = $this->hash($user['auth_passw']);

        $this->attempt($user['auth_email'], '+');

        return false == $this->exists( $user );
    }

    private function blocked($user)
    {
        return $user->auth_block == 1 ? true : false;
    }

    private function attempt($auth_email, $action)
    {
        $user = compact('auth_email');

        if($this->exists($user))
        {
            $user = $this->pull($user, 1);
            $user->auth_atmpt = $action == '+' ? $user->auth_atmpt+1 : 0;
            $user->auth_block = $user->auth_atmpt >= 10 ? 1 : 0;
            $this->push($user);
        }

        return $user;
    }
}