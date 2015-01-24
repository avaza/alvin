<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Mock
 * This controller provides testing routes for to mock the CudaTel System
 * (EXCLUDE FROM PRODUCTION)
 *
 * @author Josh Murray
 */

class Mock extends CI_Controller{

    function __construct()
    {
        parent::__construct();
    }

    function index(){
        $this->load->view('gui', 'mock');
    }
}
