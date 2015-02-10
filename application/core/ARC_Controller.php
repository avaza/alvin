<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class ARC_Controller
 *
 * @property ARC_Session $session
 * @property User $resource
 */
class ARC_Controller extends CI_Controller {

    protected $content;
    protected $sources;

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
        while( empty( $this->content[ 'messages' ])):
            $this->validate();
            $response = $this->send();
            $this->confirm( $response );

            return $this->index();//TODO User Class must set session validity to true
        endwhile;

        return $this->invalidResponse();
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
     * @return mixed
     */
    protected function send()
    {

        $post = $this->sources[ 'post' ];
        $this->loadSource( $post[ 'type' ], $post[ 'name' ] );

        return cast( $this->resource->post( $this->posted()));
    }

    protected function loadSource( $type, $name )
    {
        $this->load->$type( $name, [], 'resource' );

        return $this;
    }

    /**
     * @param $object
     * @return $this
     */
    protected function confirm( $object )
    {
        if( isset( $object->messages ) && ! empty( $object->messages )) $this->setMessages( $object->messages );

        return $this;
    }

    /**
     * @return mixed
     */
    protected function invalidResponse()
    {
        return call_user_func_array( [ $this, $this->content[ 'view' ] ], [ false ]);
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

    protected function setData( $data )
    {
        $this->content['data'] = $data;

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
     * @param $post
     *
     * @return bool
     */
    protected function getView( $interface, $post )
    {
        if( $post && ! empty( $this->input->post( null, true ))) return $this->post();

        $this->load->view( $interface, $this->content );

        return $this;
    }
}