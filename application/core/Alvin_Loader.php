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
        if( is_array($vars) || ! in_array( $view, $this->jsonLoaders ))
        {
            return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
        }

        $vars = $this->getLoader( $view, $vars );
        return $this->view( $view, $vars );
    }

    protected function getLoader($view, $vars)
    {
        if( file_exists('/opt/alvin/views/content/' . $view . 'Loader.json') )
        {
            $viewLoader = json_decode(file_get_contents('/opt/alvin/views/content/' . $view . 'Loader.json'), true);

            return $viewLoader[ $vars ]['details'];
        }

        die('The viewLoader ' . $view . 'Loader.json cannot be found');
    }
}