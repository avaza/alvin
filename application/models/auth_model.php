<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth_model
 * This model handles all processing of authentication for the Alvin System
 * @functions authenticated(), authenticate(), deAuthenticate(),
 *
 * @author Josh Murray
 *
 * @property Alvin_Session $session (Session Extension Class)
 * @property Alvin_Loader $load
 */

class Auth_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->table = 'none';
    }

    /**
     * Create a NEW session if credential inputs are valid within Alvin and CudaTel systems
     * Requires POST [username, password]
     *
     * @return array
     */
    public function authenticate()
    {
        $user = $this->input->post('username');
        $pass = $this->input->post('password');

        if(isset($user, $pass))
        {
            return $this->makeSessions($user, $pass);
        }

        return $this->killSessions();
    }

    /**
     * Get status/credentials for Alvin and CudaTel Systems
     *
     * @return object|boolean false
     */
    public function authenticated()
    {
        return $this->session->status();
    }

    /**
     * Authenticate User for Alvin and CudaTel Systems
     * @param $user - Alvin username
     * @param $pass - Alvin password
     *
     * @return array
     */
    private function makeSessions($user, $pass)
    {
        $session = $this->session->revive($user, $pass);
        if($session->valid === true)
        {
            return $this->session->linkup($session);
        }
    }

    /**
     * Destroy User sessions for Alvin and CudaTel Systems
     *
     * @return bool
     */
    private function killSessions()
    {
        return ($this->session->murder() && $this->session->unlink());
    }
}
    
    