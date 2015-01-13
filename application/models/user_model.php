<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->table = 'users';
    }

    public function validate($username, $password)
    {
        $user = ['username' => $username, 'password' => $this->hash($password)];

        if($this->exists($user))
        {
            $user = $this->pull($user, 1);
            $user->auth_token = $this->newToken();
            $this->push($user);
            $user->valid = true;
            return $user;
        }

        return (object) ['valid' => false, 'message' => 'Invalid Username and/or Password.'];
    }

    public function findToken($token)
    {
        if($this->exists($token))
        {
            return $this->pull($token);
        }

        return (object) ['valid' => false, 'message' => 'Session expired. Please log back in.'];
    }
}