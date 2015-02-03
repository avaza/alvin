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

    /**
     * @param null $object
     * @return bool
     */
    protected function isNotValid( $object = null )
    {
        while( empty( $this->details[ 'messages' ]) && ! is_null( $object )):

            if( ! is_object( $object ) && ! empty( $object->messages )) $this->details['messages'] = $object->messages;

            if( $object == 'form')
            {
                $this->load->library('form_validation');
                if( ! $this->form_validation->run()) $this->details[ 'messages' ] = validationMessages();
            }

            return false;
        endwhile;

        return true;
    }

}