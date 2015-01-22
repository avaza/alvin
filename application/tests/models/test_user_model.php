<?php

/**
 * Class test_user_model
 *
 * @property User_model $user_model
 * @property CI_DB_active_record $db
 * @property CI_Loader $load
 */
class test_user_model extends CodeIgniterUnitTestCase
{
	protected $rand;
    protected $user;

	public function __construct()
	{
		parent::__construct('User Model');

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
		$user_id = $this->user_model->add_user($insert_data);
		$this->user = $this->user_model->get_user($user_id);*/
    }

    public function tearDown()
	{

    }

	/*public function test_included()
	{
		$this->assertTrue(class_exists('user_model'));
	}

    public function test_will_add_valid_user()
    {
        $insert_data = [
            'auth_email' => 'demo'.$this->rand.'@demo.com',
            'auth_passw' => 'demo_'.$this->rand,
            'auth_creds' => '{ "ext":5041, "pin":1405 }',
            'auth_atmpt' => 0,
            'auth_block' => 0,
            'auth_start' => now()
        ];
        $user = $this->user_model->push($insert_data);
        $this->assertEqual($user->id, 1);
    }

    public function test_will_not_add_invalid_user()
    {
        $insert_data = [
            'auth_email' => 'invalid'.$this->rand.'@email',  //bad email
            'auth_passw' => '3',                             //too short password
            'auth_creds' => '{ "ext":5041, "pin":1405 [[[ }',//bad JSON
            'auth_atmpt' => 100,                             //invalid attempts
            'auth_block' => 7,                               //invalid value
            'auth_start' => 2                                //not a timestamp
        ];
        $user = $this->user_model->push($insert_data);
        $this->assertFalse($user);
    }

    public function test_edit_user()
    	{
    		$insert_data = [
                'id' => 1,
                'auth_email' => 'edit_demo'.$this->rand.'@demo.com',
    		];
    		$user = $this->user_model->push($insert_data);
    		$this->assertTrue($user);
    	}

    	public function test_delete_user()
    	{
    		$user = $this->user_model->wipe([ 'id' => 1 ]);
    		$this->assertTrue($user);
    	}

	public function test_get_user_by_id()
	{
		$user = $this->user_model->pull([ 'id' => 1 ]);
		$this->assertEqual($user->id, 1);
	}

	public function test_get_user_by_email()
	{
		$user = $this->user_model->pull([ 'auth_email' => 'test_'.$this->rand ]);
		$this->assertEqual($user->id, 1);
	}

	public function test_email_exists()
	{
        $user = $this->user_model->exists([ 'auth_email' => 'test_'.$this->rand ]);
		$this->assertTrue($user);
	}

	public function test_email_does_not_exist()
	{
        $user = $this->user_model->exists([ 'auth_email' => 'fake_test_'.$this->rand ]);
		$this->assertFalse($user);
	}*/
}

/* End of file test_user_model.php */
/* Location: ./tests/models/test_user_model.php */
