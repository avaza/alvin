<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Model
 */
class Alvin_Model extends CI_Model {

    protected $_ci;
    protected $table;
    private $structure;

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
    }

    /**
     * @param $name
     * @param bool $json
     *
     * @return mixed|string
     */
    public function collection( $name , $json = false )
    {
        $collection = '/opt/alvin/core/collections/' . $name . '.json';

        if( ! file_exists( $collection )) die( 'No collection by name : ' . $name );

        $data = file_get_contents( $collection );

        if( $json ) return $data;

        return json_decode( $data, true );
    }

    /**
     * @return string
     */
    protected function ready()
    {
        if ( ! isset( $this->table )) die('NO CRUD - Requires defined ( $table )');

        $this->structure = $this->collection( 'structures' )[$this->table];

        return $this->table;
    }

    /**
     * @param array $record
     * @param int $max
     *
     * @return array
     */
    public function find( $record = [], $max = 0 )
    {
        if( ! is_array( $record )) $record = ['id' => $record ];

        $limit = $max <= 0 ? null : [ 'limit' => $max ];
        $find = array_merge( $record, $limit );

        return $this->pull( $find );
    }

    /**
     * @param array $params
     * @param bool $return
     *
     * @return array|bool
     */
    public function exists( $params = [], $return = false )
    {
        $record = $this->pull( $params );

        if( ! $record ) return false;
        if( ! $return ) return true;

        return $record;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function push( $params = [] )
    {
        if( $this->exists( $params )) return $this->edit( $params );

        return $this->make( $params );
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function pull( $params = [] )
    {
        $table = $this->ready();
        $limit = isset( $params[ 'limit' ]) ? $params[ 'limit' ] : null;

        $search = $this->adaptMessage( $params );

        $response = $this->read( $table, $search, $limit );

        return $this->adaptResponse( $response );
    }

    /**
     * @param $record
     *
     * @return mixed
     */
    public function wipe( $record )
    {
        $delete = $this->adaptMessage( $record );

        if( $this->exists( $record )) return $this->kill( $delete );

        return true;//TODO tell me there was no record
    }

    /**
     * @param $search
     * @param $limit
     *
     * @return mixed
     */
    private function read( $table, $search, $limit )
    {
        foreach( $search as $column => $value ):
            $this->db->where( $column, $value );
        endforeach;

        $query = $this->db->get( $table, $limit );

        $result = $query->result();
        if( ! $result ) return false;

        return $this->errorsOrResult( $result );
    }

    /**
     * @param $record
     *
     * @return mixed
     */
    private function make( $record )
    {
        $table = $this->ready();
        $insert = $this->adaptMessage( $record );

        $this->db->insert($table, $insert);

        if( $this->db->affected_rows() <= 0 ) return false;

        return $this->errorsOrResult( $record );
    }

    /**
     * @param $record
     *
     * @return mixed
     */
    private function edit( $record )
    {
        $table = $this->ready();
        $update = $this->adaptMessage( $record );

        $this->db->where( 'id', $update[ 'id' ]);
        $this->db->update( $table, $update );

        if( $this->db->affected_rows() <= 0 ) return false;

        return $this->errorsOrResult( $record );
    }

    /**
     * @param $delete
     *
     * @return mixed
     */
    private function kill( $delete )
    {
        $table = $this->ready();

        $this->db->where( 'id', $delete[ 'id' ]);
        $this->db->delete( $table, $delete );

        if( $this->db->affected_rows() <= 0 ) return false;

        return $this->errorsOrResult( true );
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function errorsOrResult( $data )
    {
        //TODO Error messages to tell you what's wrong
        return $data;
    }

    /**
     * @param array $message
     *
     * @return array
     */
    protected function adaptMessage( $message = [])
    {
        $adapted = [];

        if( empty( $message )) return $message;

        $adapted = $this->rename( $message, $adapted );
        $adapted = $this->compress( $message, $adapted );

        return $adapted;
    }

    /**
     * @param array $response
     *
     * @return array
     */
    protected function adaptResponse( $response = [])
    {
        $adapted = [];

        if( empty( $response )) return $response;

        $adapted = $this->rename( $response, $adapted, true );
        $adapted = $this->decompress( $response, $adapted );

        return $adapted;
    }

    /**
     * @param $message
     * @param $adapted
     * @param bool $read
     *
     * @return mixed
     */
    private function rename( $message, $adapted, $read = false )
    {
        $names = $this->structure[ 'input' ];

        if( $read ) $names = array_flip( $this->structure[ 'input' ]);



        foreach( $names as $to => $from ):
            if( isset( $message[ $from ])) $adapted[ $to ] = $message[ $from ];
        endforeach;

        return $adapted;
    }

    /**
     * @param $message
     * @param $adapted
     *
     * @return mixed
     */
    private function compress( $message, $adapted )
    {
        $press = $this->structure[ 'press' ];

        foreach( $press as $to => $unpressed ):
            $adapted[ $to ] = $this->encode( $message, $unpressed );
        endforeach;

        return $adapted;
    }

    /**
     * @param $message
     * @param $unpressed
     *
     * @return string
     */
    private function encode( $message, $unpressed )
    {
        $pressed = [];

        foreach( $unpressed as $item ):
            if( isset( $message[ $item ])) $pressed[ $item ] = $message[ $item ];
        endforeach;

        return json_encode( $pressed );
    }

    /**
     * @param $response
     * @param $adapted
     *
     * @return array
     */
    private function decompress( $response, $adapted )
    {
        $press = $this->structure[ 'press' ];

        foreach( $press as $from => $pressed ):
            $adapted = $this->decode( $adapted, $response[ $from ] );
        endforeach;

        return $adapted;
    }

    /**
     * @param $adapted
     * @param $pressed
     *
     * @return array
     */
    private function decode( $adapted, $pressed )
    {
        $unpressed = json_decode( $pressed, true );

        return array_merge( $adapted, $unpressed );
    }

}
    
    