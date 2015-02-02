<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Alvin_Controller {
    
    function __construct (){
        parent::__construct();
    }

    function index()
    {
        $this->details['view'] = 'home';

        $this->load->view('gui', $this->details);
    }
}