<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Morph{

    private $crypt;
    protected $_ci;

    function __construct()
    {
        $this->_ci =& get_instance();
        $this->crypt = $this->_ci->config->item('encryption_key');
    }

    /**
     * Encrypt the a string with the application's "encryption_key"
     * @param string $string
     *
     * @return string - Encrypted string (40 chars)
     */
    public function hash( $string )
    {
        return sha1( $string . $this->crypt );
    }
}