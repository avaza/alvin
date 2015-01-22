<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class test_auth_controller
 *
 * @property User_model $user_model
 * @property CI_DB_active_record $db
 * @property CI_Loader $load
 */
class test_auth_controller extends CodeIgniterUnitTestCase {
    
    protected $rand;
    protected $user;

    function __construct()
    {
        parent::__construct('Auth Controller');
        $this->load->model('user_model');
        $this->rand = rand(500,15000);
    }

    public function setUp()
    {
        /*$this->db->truncate('users');

        $insert_data = [
            'auth_email' => 'demo'.$this->rand.'@demo.com',
            'auth_passw' => 'demo_'.$this->rand,
            'auth_creds' => '{ "ext":5041, "pin":1405 }',
            'auth_atmpt' => 0,
            'auth_block' => 0,
            'auth_start' => now()
        ];
        $user = $this->user_model->push($insert_data);
        $this->user = $this->user_model->pull([ 'id' => $user->id ]);*/
    }

    public function tearDown()
    {
        $this->db->truncate('users');
    }

    public function test_included()
    {
        $this->assertTrue(class_exists('auth'));
    }
    
}
    
    