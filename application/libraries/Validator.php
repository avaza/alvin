<?php 
class Validator extends CI_Form_validation{

    protected $_ci;

    function __construct()
    {
        $this->_ci =& get_instance();
    }

    //TODO check user permissions
    //TODO get form fields/rules
    //TODO set rules
    //TODO make getting the rules easy as hell (JSON?)
    //TODO get rid of grocery CRUD
    
}