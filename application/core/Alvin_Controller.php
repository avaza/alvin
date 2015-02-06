<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Controller
 *
 * @property Alvin_Session $session
 */
class Alvin_Controller extends CI_Controller {

    protected $content;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @return bool
     */
    public function index()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function post()
    {
        if( $this->inputInvalid()) redirect('back');

        $library = $this->content[ 'library' ];

        $this->load->library( $library, $this->posted());
        if( $this->inputInvalid( $this->$library->data )) redirect('back');

        return $this->index();//TODO User Class must set session validity to true
    }

    /**
     * @param mixed $object
     *
     * @return bool
     */
    protected function inputInvalid( $object = 'form' )
    {
        while( empty( $this->content[ 'messages' ])):
            if( $object == 'form') $this->validate();
            if( ! is_object( $object )) $object = $this->forceObject( $object );
            if( ! empty( $object->messages )) $this->setMessages( $object->messages );

            return false;
        endwhile;

        return true;
    }

    /**
     * @return $this
     */
    protected function validate()
    {
        $this->load->library('form_validation');
        if( ! $this->form_validation->run()) $this->setMessages( validationMessages());

        return $this;
    }

    /**
     * @param array $messages
     *
     * @return $this
     */
    protected function setMessages( $messages = [] )
    {
        if( ! empty( $messages )) $this->content[ 'messages' ] = $messages;

        return $this;
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    protected function setView( $content = 'index' )
    {
        $this->content[ 'view' ] = $content;

        return $this;
    }

    /**
     * @param string $library
     *
     * @return $this
     */
    protected function setLibrary( $library = 'user' )
    {
        $this->content[ 'library' ] = $library;

        return $this;
    }

    /**
     * @param $interface
     *
     * @return bool
     */
    protected function getView( $interface )
    {
        if( ! empty( $this->input->post( null, true ))) $this->post();

        $this->load->view( $interface, $this->content );

        return false;
    }

    /**
     * @return array
     */
    protected function posted()
    {
        $input = $this->input->post( null, true );

        if( ! $input ) return $this->content;

        return array_merge( $input, $this->content );
    }

    /**
     * @param $object
     *
     * @return object
     */
    protected function forceObject( $object )
    {
        if( ! is_object( $object ) && is_array( $object )) $object = (object) $object;

        if( ! is_object( $object )) $this->setMessages( message( 'Invalid Parameters - Cannot make Object' ));

        return $object;
    }

    /**
     * @param $object
     *
     * @return array
     */
    protected function forceArray( $object )
    {
        if( ! is_array( $object ) && is_object( $object )) $object = (array) $object;

        if( ! is_array( $object )) $this->setMessages( message( 'Invalid Parameters - Cannot make Array' ));

        return $object;
    }
}