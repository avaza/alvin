<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Controller
 *
 * @property Alvin_Session $session
 */
class Alvin_Controller extends CI_Controller {

    protected $details;

    function __construct()
    {
        parent::__construct();
        $this->details[ 'messages' ] = [];
        $this->session->checkAndRedirect();
    }

    protected function data()
    {
        return [ 'details' => $this->details ];
    }
}