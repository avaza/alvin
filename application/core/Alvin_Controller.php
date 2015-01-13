<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alvin_Controller extends CI_Controller {
	
    /**
     * @var $controller_vars
     */
    private $controller_vars;

    /**
     * Load AuthModel and check if requester has been authenticated
     */
    function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model', '', true);
        $this->authenticated();
    }

    /**
     * Checks if requester has been authenticated
     *
     * @return redirect
     */
    private function authenticated()
    {
        if(!$this->auth_model->authenticated())
        {
            redirect('auth');
        }
    }

    /**
         * Sets or Un-sets Controller vars by ENVIRONMENT
         * @param string variable name
         * @param mixed variable value
         *
         * @return void
         */
        protected function set_env($var, $value = null)
        {
            $this->controller_vars = ['tes' => [],'dev' => [],'pro' => []];
            $env = substr(ENVIRONMENT, 1, 2);

            if(isset($value))
            {
                $this->controller_vars[$env][$var] = $value;
            }

            if(is_null($value))
            {
                unset($this->controller_vars[$env][$var]);
            }
        }

        /**
         * Retrieve controller ENVIRONMENT based variable values
         * @param string $var variable name
         *
         * @return mixed
         */
        public function get_env($var)
        {
            $env = substr(ENVIRONMENT, 1, 2);

            return isset($this->controller_vars[$env][$var]) ? $this->controller_vars[$env][$var] : null;
        }
}