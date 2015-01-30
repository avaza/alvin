<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Alvin_Model
 */
class Alvin_Model extends CI_Model {

    /**
     * @var string
     */
    private $crypt;

    protected $_ci;

    protected $table;

    protected $compress;

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->crypt = $this->config->item('encryption_key');

    }

    /**
     * Encrypt the a string with the application's "encryption_key"
     * @param string $string
     *
     * @return string - Encrypted string (40 chars)
     */
    protected function hash($string)
    {
        return sha1($string . $this->crypt);
    }

    /**
     * Checks if $this->table has been set within the child model before requesting CRUD operations
     *
     * @return void;
     */
    protected function checkTable()
    {
        if ( ! isset($this->table)) die('You must set a "$this->table" value in the model before using CRUD functions');
    }

    /**
     * Inserts an array into the model's database table
     * @param array $params - (Array of values to be inserted into the table)
     *
     * @return mixed
     */
    public function push($params)
    {
        $params = $this->adapt( 'compress', $params );

        switch($this->exists($params))
        {
            case true:
                if($this->update($params))
                {
                    return (object) $params;
                }
                break;
            case false:
                if($this->create($params))
                {
                    return (object) $params;
                }
                break;
        }

        return false;
    }
    /**
     * Returns a database record matching the given "where" criteria OR false
     * No $params will return ALL records
     * @param array $params - Associative array of "where" options [column => value, column2 => value2]
     * @param integer $max - Maximum number of records to return
     *
     * @return mixed
     */
    public function pull($params = null, $max = 0)
    {
        $params = $this->adapt( 'decompress', $params );

        $max = $max > 0 ? $max : null;
        foreach($params as $column => $value)
        {
            $this->db->where($column, $value);
        }
        $query = $this->db->get($this->table, $max);
        if( $query->result())
        {
            return $this->parseResult( $query->result());
        }

        return false;
    }

    /**
     * Destroys a database record where "id" = params["id"]
     * @param mixed $params
     *
     * @return boolean
     */
    public function wipe($params = null)
    {
        $params = $this->adapt( 'compress', $params );

        if($this->exists($params))
        {
            $this->db->where('id', $params['id']);
            $this->db->delete($this->table, $params);
            if($this->db->affected_rows() > 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Creates a NEW database record from $params
     * @param array $params - (Array of values to be located)
     * @param boolean $return - (return result yes/no)
     * @return boolean
     */
    public function exists($params, $return = false)
    {
        $query = $this->pull($params);
        if($query)
        {
            if( $return ) return $query;
            return ( $query );
        }

        if(isset($params['id']))
        {
            $this->db->where('id', $params['id']);
            $query = $this->db->get($this->table, 1);
            return ($query->num_rows() > 0);
        }

        return false;
    }

    /**
     * Creates a NEW database record from $params
     * @param array $params - (Array of values to be inserted into the table)
     *
     * @return boolean
     */
    protected function create($params)
    {
        $this->db->insert($this->table, $params);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Updates an EXISTING database record from $params
     * @param mixed $params
     *
     * @return boolean
     */
    protected function update($params)
    {
        $this->db->where('id', $params['id']);
        $this->db->update($this->table, $params);
        return $this->db->affected_rows() > 0;
    }

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


}
    
    