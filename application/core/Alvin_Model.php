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

    protected $table;

    protected $form;

    function __construct()
    {
        parent::__construct();
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
    protected function push($params)
    {
        switch($this->exists($params))
        {
            case true:
                if($this->update($params))
                {
                    return $params;
                }
                break;
            case false:
                if($this->create($params))
                {
                    return $params;
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
    protected function pull($params = null, $max = 0)
    {
        $max = $max > 0 ? $max : null;
        foreach($params as $column => $value)
        {
            $this->db->where($column, $value);
        }
        $query = $this->db->get($this->table, $max);
        $result = $query->result();
        if($query->result())
        {
            return $this->parseResult($result);
        }

        return false;
    }

    /**
     * Destroys a database record where "id" = params["id"]
     * @param mixed $params
     *
     * @return boolean
     */
    protected function wipe($params = null)
    {
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
     * @param array $params - (Array of values to be inserted into the table)
     *
     * @return boolean
     */
    protected function exists($params)
    {
        $query = $this->pull($params);
        if($query)
        {
            return ($query->num_rows() > 0);
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
        return ($this->db->affected_rows() > 0);
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
        return ($this->db->affected_rows() > 0);
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
        if(count($options) > 0)
        {
            //TODO
        }

        if(is_array($result) && count($result) === 1)
        {
            return $result['0'];
        }
        return $result;
    }
}
    
    