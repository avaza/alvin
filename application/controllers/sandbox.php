<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sandbox extends CI_Controller {

    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->load->view('layouts/original_bp');
    }

    public function cudatest()
    {
        //load javascript for websocket
        //post login command to get credentials for CudaTel
        //Open websocket connection
        //create an input to enter desired channel
        //Connect to channels and retrive data
        //Output data to a view to determine what data is available from each channel (Table?)
    }
}