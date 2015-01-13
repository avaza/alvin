<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cuda extends Alvin_Controller {
     
    protected $login = array('1000', '6842');

    function __construct(){
        parent::__construct();
        //$this->load->library('cudatel', $this->login);
    }

    function index()
    {
        $url = $this->input->post('suburl');
        $params = $this->input->post('params');
        header('Content-Type: application/x-json');
        echo($this->cudatel->get($url, $params));
    }

    function info()
    {
        phpinfo();
    }
}