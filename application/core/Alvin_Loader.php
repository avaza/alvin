<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Alvin_Loader extends CI_Loader {

    protected $jsonLoaders;

    function __construct()
    {
        parent::__construct();
        $this->jsonLoaders = ['gui', 'api'];
    }

    function view($view, $vars = [], $return = false)
    {
        if( in_array( $view, $this->jsonLoaders ))
        {
            $message = (is_array($vars) && isset($vars[ 'message' ])) ? $vars[ 'message' ] : false;
            $vars = (is_array($vars) && isset($vars[ 'view' ])) ? $vars[ 'view' ] : false;
            $vars = $this->getLoader( $view, $vars, $message);
        }

        return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    }

    protected function getLoader($view, $vars, $message = false)
    {
        if( file_exists('/opt/alvin/views/content/' . $view . 'Loader.json') )
        {
            $viewLoader = json_decode(file_get_contents('/opt/alvin/views/content/' . $view . 'Loader.json'), true);

            $details = $viewLoader[ $vars ]['details'];
            $details['message'] = $message;

            return $details;
        }

        die('The viewLoader ' . $view . 'Loader.json cannot be found');
    }
}