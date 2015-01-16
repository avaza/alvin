<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Session
 *
 */
class Alvin_Session extends CI_Session {

    public $user;
    public $cuda;

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->_ci->load->library('cudatel');
    }

    public function setUser($user)
    {
        $this->user = $user;
        $this->setCuda();

        return $this->user;
    }

    public function setCuda()
    {
        $valid = $this->_ci->cudatel->session('create', $creds);

        if($user->valid)
        {
            $this->setCuda($user);
            $this->user = $user;
        }

        return $this->user;
    }

    /**
     * Create a new session for a valid CudaTel user
     *
     * @return array
     */
    function create()
    {
        $user = $this->getUser();
        $this->_ci->cudatel->session('create', $user);

        return $creds;
    }

    /**
     * Create a new session for a valid Alvin user
     * @param $user
     * @param $pass
     *
     * @return array
     */
    function refresh($user, $pass)
    {
        $this->_ci->cudatel->session('refresh');
        $user = $this->_ci->user_model->validate($user, $pass);


        return $user;
    }

    /**
     * Destroy the user's sessions
     * @return bool
     */
    function destroy()
    {
        $this->_ci->cudatel->session('destroy');
        $this->sess_destroy();


        return ( ! isset($this->userdata['session_id']));
    }



    /**
     * Destroy the user's CudaTel session
     *
     * return boolean
     */
    function unlink()
    {

        return true;
    }

    /**
     * Get status/credentials for Alvin and CudaTel Systems
     */
    function status()
    {
        $user = $this->user();
        $cuda = $this->cuda($user);

        return $this->_ci->cudatel_model->validate($user);
    }

    public function checkAndRedirect($to = 'home')
    {
        if($this->user->valid)
        {
            redirect($to);
        }

        redirect('auth');
    }

    public function redirectInValid()
    {
        if( ! $this->user->valid)
        {

        }

        return false;
    }

    public function messageInvalid($message, $valid = false)
    {
        $this->sess_destroy();
        $this->flashdata(compact('message'));

        return (object) compact('valid');
    }


}
    
    