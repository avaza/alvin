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

    protected $details;

    function __construct()
    {
        parent::__construct();
    }

    function index(){
        $this->load->view('gui', 'mock');
    }

    function gui()
    {
        $this->details['template'] = 'main';
        $this->details['view'] = 'mock';

        $this->load->view( 'gui', $this->details );
    }
}
