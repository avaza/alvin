<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Alvin_Controller {
    
    function __construct (){
        parent::__construct();
    }

    function index(){
        $data['main_content'] = 'home';
        $this->load->view('templates/standard', $data);
    }
}