<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Controller
 *
 * @property Alvin_Session $session
 */
class Alvin_Controller extends CI_Controller {
	
    protected $message;
    protected $details;
    protected $content;

    function __construct()
    {
        parent::__construct();
        $this->session->checkAndRedirect();
        $this->message = $this->session->flashdata('message');
    }

    public function form($action)
    {

    }


    protected function create()
        {
            //TODO should take the form array (from model) and create all form fields for it empty and ready to add a new record
            //TODO should include classes, messages, and load the standard form view "form"
            //TODO $details will include the default values
            //TODO $messages will include validation errors
        }

    protected function update()
    {
        if($this->input->post('whatever')){
            $this->formValidate();
        }
        //TODO should take the form array (from model) and create all form fields for it complete with current values
        //TODO should include classes, messages, and load the standard form view "form"
        //TODO $details will include the current values
        //TODO $messages will include validation errors
    }

    protected function formValidate()
    {
        //TODO should run the model's validate function and return any messages back to the view OR redirect to success page
    }

}