<?php
if( ! defined( 'BASEPATH' )) exit( 'No direct script access allowed' );

class Service {

    public $data;
    protected $_ci;

    function __construct( $params = null )
    {
        $this->_ci =& get_instance();
        $this->_ci->load->model('service_model');
    }


}