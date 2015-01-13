<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Session
 *
 */
class Alvin_Session extends CI_Session {
    
    /**
     * @var Object - CodeIgniter Instance ( Used only for parentLoadModel() )
     */
    protected $parent;

    function __construct()
    {
        parent::__construct();
        $this->parent =& get_instance();
        $this->parent->load->parentModel('user_model', '', true);
        $this->parent->load->parentModel('cudatel_model', '', true);
    }

    /**
     * Create a new session for a valid Alvin user
     * @param $user
     * @param $pass
     *
     * @return array
     */
    function revive($user, $pass)
    {
        $user = $this->parent->user_model->validate($user, $pass);

        return $user;
    }

    /**
     * Destroy the user's Alvin session
     * @return bool
     */
    function murder()
    {
        $this->set_userdata([]);
        $this->sess_destroy();
        $token = $this->userdata('auth_token');

        return (!isset($token));
    }

    /**
     * Create a new session for a valid CudaTel user
     * @param $user - Alvin User Object
     *
     * @return array
     */
    function linkup($user)
    {
        $creds = $this->parent->cudatel_model->validate($user);

        return $creds;
    }

    /**
     * Destroy the user's CudaTel session
     *
     * return boolean
     */
    function unlink()
    {
        //TODO
        return true;
    }

    /**
     * Get status/credentials for Alvin and CudaTel Systems
     */
    function status()
    {
        $token = ['auth_token' => $this->userdata('auth_token')];
        $user = $this->parent->user_model->findToken($token);

        return $this->parent->cudatel_model->validate($user);
    }
}
    
    