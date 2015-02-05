<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Model
 */
class Alvin_Model extends CI_Model {

    protected $_ci;
    protected $table;

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->table = [];
    }

    /**
     * Checks if $this->table has been set within the child model before requesting CRUD operations
     *
     *
     *    TODO     $params = $this->adaptMessage( $params );

           TODO  $params = $this->adaptResponse( $params );
     *
     * @return string
     */
    protected function ready()
    {
        if ( ! isset( $this->table['table'] )) die('NO CRUD - Requires structure array in the model ($table)');

        return $this->table['table'];
    }

    public function find( $record = [], $max = 0 )
    {
        if( ! is_array( $record )) $record = ['id' => $record ];

        $limit = $max <= 0 ? null : [ 'limit' => $max ];
        $find = array_merge( $record, $limit );

        return $this->pull( $find );
    }

    public function exists( $params = [], $return = false )
    {
        $record = $this->pull( $params );

        if( ! $record ) return false;
        if( ! $return ) return true;

        return $record;
    }

    /**
     * Inserts a database record matching the $params or updates the record matching the "id" param
     * @param array $params - (Array of values to be inserted into the table)
     *
     * @return mixed
     */
    public function push( $params = [] )
    {
        $table = $this->ready();

        if( $this->exists( $params )) return $this->edit( $params );

        return $this->make( $params );
    }

    /**
     * Returns a database record matching the $params = [column => value, column2 => value2]
     * @param array $params - assoc array
     *
     * @return mixed
     */
    public function pull( $params = [] )
    {
        $table = $this->ready();
        $search = $this->adaptMessage( $params );

        foreach( $search as $column => $value ):
            $this->db->where( $column, $value );
        endforeach;

        $query = $this->db->get( $table, $params[ 'limit' ]);
        if( ! $query->result()) return false;

        return $this->adaptResponse( $query->result());
    }

    public function wipe( $record )
    {
        $table = $this->ready();
        $delete = $this->adaptMessage( $record );

        if( $this->exists( $record ))
        {
            $this->db->where( 'id', $delete[ 'id' ]);
            $this->db->delete( $table, $delete );

            if( $this->db->affected_rows() <= 0 ) return false;
        }

        return true;
    }

    private function make( $record )
    {
        $table = $this->ready();
        $insert = $this->adaptMessage( $record );

        $this->db->insert($table, $insert);

        if( $this->db->affected_rows() <= 0 ) return false;

        return $record;
    }

    private function edit( $record )
    {
        $table = $this->ready();
        $update = $this->adaptMessage( $record );

        $this->db->where( 'id', $update[ 'id' ]);
        $this->db->update( $table, $update );

        if( $this->db->affected_rows() <= 0 ) return false;

        return $record;
    }


/*      $this->table = [
                 'table' => 'users',
                    'id' => [ 'input' => 'user_id' ],
            'auth_email' => [ 'input' => 'email' ],
            'auth_passw' => [ 'input' => 'password' ],
            'auth_atmpt' => [ 'input' => 'count' ],
            'auth_block' => [ 'input' => 'key' ],
            'auth_creds' => [ 'press' => [ 'ext', 'pin' ]],
            'auth_level' => [ 'press' => [ 'roles', 'permissions' ]]
        ];*/


    protected function adapt( $action = 'compress', $object )
    {
        if( ! is_object( $object ) && ! is_array( $object )) return false;

        if( is_array( $object )) $adapted = (object) $object;

        if( ! isset( $adapted )) $adapted = $object;

        if( isset( $this->compress ) && ! empty( $this->compress[ 'columns' ]))
        {
            $object = call_user_func_array([ $this, $action ], [ $adapted, $this->compress[ 'columns' ]]);
        }

        return $object;
    }

    protected function compress($object, $columns)
    {
        foreach( $columns as $column ):

            $items = isset( $compress[ $column ]) ? $compress[ $column ] : [];

            $object = $this->compressColumn( $object, $column, $items );

        endforeach;

        $object->$column = json_encode( $object->$column );

        return $object;
    }

    private function compressColumn( $object, $column, $items )
    {
        foreach( $items as $item ):

            $object->$column[ $item ] = $this->compressItem($object, $item);

        endforeach;

        return $object;
    }

    private function compressItem( $object, $item, $value = null )
    {
        if( isset( $object->$item ))
        {
            $value =  $object->$item;
            unset( $object->$item );
        }

        return $value;
    }

    protected function decompress($object, $columns)
    {
        foreach( $columns as $column ):

            if( ! isset( $object->$column )) $object->$column = json_encode([]);

            $compressed = json_decode( $object->$column );

            $items = isset( $compressed ) ? $compressed : [];

            $object = $this->decompressColumn( $object, $compressed, $items);

        endforeach;

        return $object;
    }

    private function decompressColumn( $object, $compressed, $items )
    {
        foreach( $items as $item ):

            $object->$item = $this->decompressItem( $compressed, $item );

        endforeach;

        return $object;
    }

    private function decompressItem( $compressed, $item, $value = null )
    {
        if( isset( $compressed[ $item ]))
        {
            $value =  $compressed[ $item ];
            unset( $compressed[ $item ] );
        }

        return $value;
    }

    /**
     * Parses database query results with the provided options
     * @param mixed $result
     * @param array $options
     *
     * @return mixed
     */
    protected function parseResult($result, $options = [])
    {
        $default = [ 'raw' => false, 'as' => 'object', 'custom' => [] ];
        $options = array_merge( $default, $options );

        if( $options['raw'] ) return $result;

        $result = call_user_func_array(
            [ $this, $options[ 'as' ] . 'Results' ],
            [ $result, $options[ 'custom' ]]
        );

        return $result;
    }

    protected function objectResults( $result, $custom = [] )
    {
        if( is_object( $result )) return $result;

        if( ! is_array( $result )) return false;

        if( count( $result ) === 1 && is_object( $result['0'] )) return $result['0'];

        if( empty( $custom )) return $result;
    }

    protected function arrayResults( $result, $custom = [] )
    {
        if( is_object( $result )) return (array) $result;

        if( ! is_array( $result )) return false;

        if( count( $result ) === 1 && is_object( $result['0'] )) return (array) $result['0'];

        if( empty( $custom )) return (array) $result;
    }

    protected function jsonResults( $result, $custom = [] )
    {
        if( is_object( $result )) return json_encode( $result );

        if( ! is_array( $result )) return json_encode( false );

        if( count( $result ) === 1 && is_object( $result['0'] )) json_encode( $result['0'] );

        if( empty( $custom )) return json_encode( $result );
    }



    protected function validate($formData)
    {
        return $this->_ci->validator->execute($this->table, $formData);
    }

    public function parseInput()
    {
        if( ! isset( $this->parser )) return $this->input->post( null, true );

        $parsed = ['input' => $this->input->post( null, true )];

        foreach($this->parser as $input => $parse):
            $parsed[ $parse ] = $this->input->post( $input, true );
        endforeach;

        return $parsed;
    }

    public function collection( $name , $json = false )
    {
        $collection = '/opt/alvin/core/collections/' . $name . '.json';

        if( ! file_exists( $collection )) die( 'No collection by name : ' . $name );

        $data = file_get_contents( $collection );

        if( $json ) return $data;

        return json_decode( $data, true );
    }
}
    
    