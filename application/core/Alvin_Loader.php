<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Loader
 */
class Alvin_Loader extends CI_Loader{
    
    /**
     * @var Object - CodeIgniter Instance ( Used only for parentModel() )
     */
    protected $parent;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets the current CodeIgniter instance and loads $model into it
     * @param string $model - Name of the Model to load to "$this->parent" (Instance)
     *
     * @return boolean
     */
    public function parentModel($model)
    {
        $this->parent =& get_instance();
        $this->parent->load->model($model);

        return $this->parent->load->isComplete($model);
    }

    /**
     * Returns boolean $model is loaded
     * @param string $model - Name of the model to check
     *
     * @return  boolean
     */
    public function isComplete($model)
    {
        return in_array($model, $this->_ci_models, true);
    }
}