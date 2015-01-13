<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Crud extends Alvin_Controller {

	function __construct(){
		parent::__construct();
		$this->load->database();		
		$this->load->library('grocery_CRUD');
        $this->load->model('grocery_crud_model','', TRUE);
	}
	//NAVIGATION
    function view_crud($type){
        $data['crud_type'] = $type;
        $data['credentials'] = $this->credentials();
        $data['main_content'] = 'crud/view_crud';
        $this->load->view('includes/templates/crud', $data);
    }
    
    //GENERATE CRUD
    function show_crud($output = null){
        $this->load->view('crud/crud', $output);
    }

    //BUILD CRUD
    function payments_detail(){
        $crud = new grocery_CRUD();
        $crud->set_table('call_records_t');
        $crud->columns('start_time', 'end_timestamp', 'language', 'intid', 'call_time');
        $crud->set_subject('Call');
        $crud->display_as('start_time', 'Start Time');
		$crud->display_as('end_timestamp', 'End Time');
		$crud->display_as('language', 'Language');		
        $crud->display_as('intid', 'Interpreter ID');
        $crud->display_as('call_time', 'Duration');
        $crud->unset_delete();
		$crud->unset_add();
		$crud->unset_edit();
		$output = $crud->render();
        $this->show_crud($output);
    }	

    function invoices_detail(){
        $crud = new grocery_CRUD();
        $crud->set_table('call_records_t');
        $crud->columns('job_number', 'access_code', 'client_id', 'agency', 'division', 'invoice_detail', 'start_time', 'end_timestamp', 'language', 'intid', 'rep_name', 'caller_id_number', 'rate_code', 'call_time', 'rate');
        $crud->set_subject('Client');
		$crud->display_as('job_number', 'Job Number');
        $crud->display_as('access_code', 'Access Code');
        $crud->display_as('client_id', 'Client ID');
        $crud->display_as('agency', 'Agency');
		$crud->display_as('division', 'Division');
		$crud->display_as('invoice_detail', 'Billing Name');
		$crud->display_as('start_time', 'Start Time');
		$crud->display_as('end_timestamp', 'End Time');
		$crud->display_as('language', 'Language');
		$crud->display_as('intid', 'Interpreter ID');
		$crud->display_as('rep_name', 'Contact Name');
		$crud->display_as('caller_id_number', 'Contact Number');
		$crud->display_as('rate_code', 'Rate Code');
		$crud->display_as('call_time', 'Minutes');
        $crud->unset_delete();
		$crud->unset_add();
		$crud->unset_edit();
        $output = $crud->render();
        $this->show_crud($output);
    }	

    function clients_detail(){
        $crud = new grocery_CRUD();
        $crud->set_primary_key('id');
        $crud->set_table('client_data');
        $crud->columns('access_code', 'client_id', 'account_number', 'client_name', 'agency', 'division', 'Invoice_Detail', 'Div_Contact');
        $crud->set_subject('Client');
        $crud->display_as('access_code', 'Access Code');
        $crud->display_as('client_id', 'Client ID');
        $crud->display_as('client_name', 'Client');
        $crud->display_as('Invoice_Detail', 'Invoice Shows');
        $crud->display_as('account_number', 'Account Number');
        $crud->display_as('Div_contact', 'Division Contact');
        $crud->unset_delete();
        $output = $crud->render();
        $this->show_crud($output);
    }   

    function interpreters_detail(){
        $languages = $this->grocery_crud_model->get_languages();
        $crud = new grocery_CRUD();
        $crud->set_primary_key('id');
        $crud->set_table('interpreters');
        $crud->columns('iid', 'name', 'language', 'phone_1', 'notes');
        $crud->set_subject('Interpreter');
        $crud->display_as('iid', 'Interpreter ID');
        $crud->display_as('btg', 'BTG');
        $crud->field_type('language','dropdown', $languages);
        $crud->field_type('lid', 'hidden');
        $crud->field_type('language_code', 'hidden');
        $crud->callback_add_field('btg',array($this,'add_btg_radio'));
        $crud->callback_add_field('order',array($this,'add_order_radio'));
        $crud->callback_edit_field('btg',array($this,'add_btg_radio'));
        $crud->callback_edit_field('order',array($this,'add_order_radio'));
        $output = $crud->render();
        $this->show_crud($output);
    }

    function users_detail(){
        $crud = new grocery_CRUD();
		$crud->set_primary_key('id');
        $crud->set_table('users');
        $crud->columns('fname', 'lname', 'intid', 'ext', 'username', 'email', 'level', 'lang');
        $crud->set_subject('User');
        $crud->display_as('fname', 'First Name');
        $crud->display_as('lname', 'Last Name');
        $crud->display_as('intid', 'Interpreter ID');
        $crud->display_as('ext', 'Extension');
        $crud->display_as('username', 'Username');
        $crud->display_as('email', 'Email Address');
        $crud->display_as('level', 'Permission Level');
        $crud->display_as('lang', 'Primary Language');
        $crud->change_field_type('password', 'password');
        //$crud->unset_delete();
        $crud->callback_before_insert(array($this,'encrypt_password_callback'));
        $crud->callback_before_update(array($this,'encrypt_password_callback'));
        //VALIDATION
        $crud->set_rules('fname', 'Name', 'trim|required');
        $crud->set_rules('lname', 'Last Name', 'trim|required');
        $crud->set_rules('intid', 'Interpreter ID', 'trim|required|numeric');
        $crud->set_rules('email', 'Email', 'trim|required|valid_email');
        $crud->set_rules('username', 'Username', 'trim|required|min_length[4]');
        $crud->set_rules('password', 'Password', 'trim|required|min_length[4]|max_length[32]');
        $crud->set_rules('password2', 'Password Confirmation', 'trim|required|matches[password]');
        $crud->set_rules('ext', 'Extension', 'trim|required|exact_length[4]|numeric');
        $crud->set_rules('pin', 'Extension Pin','trim|required|exact_length[4]|numeric'); 
        $crud->set_rules('langs', 'Number of Languages','trim|required|exact_length[1]|numeric');   
        $crud->set_rules('lang', 'Primary Language', 'required');
        $crud->set_rules('level', 'Permission Level', 'required');
        $output = $crud->render();
        $this->show_crud($output);
    }

    function clients_view(){
        $crud = new grocery_CRUD();
        $crud->set_primary_key('id');
        $crud->set_theme('datatables');
        $crud->set_table('client_data');
        $crud->columns('access_code', 'client_id', 'client_name', 'agency', 'division');
        $crud->set_subject('Client');
        $crud->display_as('access_code', 'Access Code');
        $crud->display_as('client_id', 'Client ID');
        $crud->display_as('client_name', 'Client');
        $crud->unset_delete();
        $crud->unset_add();
        $crud->unset_edit();
        $crud->unset_print();
        $crud->unset_export();
        $output = $crud->render();
        $this->show_crud($output);
    }

    function interpreters_view(){
        $languages = $this->grocery_crud_model->get_languages();
        $crud = new grocery_CRUD();
        $crud->set_primary_key('id');
        $crud->set_theme('datatables');
        $crud->set_table('interpreters');
        $crud->columns('iid', 'name', 'language', 'phone_1', 'notes');
        $crud->set_subject('Interpreter');
        $crud->display_as('iid', 'Interpreter ID');
        $crud->display_as('btg', 'BTG');            
        $crud->field_type('language','dropdown', $languages);
        $crud->field_type('lid', 'hidden');
        $crud->field_type('language_code', 'hidden');
        $crud->callback_add_field('btg',array($this,'add_btg_radio'));
        $crud->callback_add_field('order',array($this,'add_order_radio'));
        $crud->callback_edit_field('btg',array($this,'add_btg_radio'));
        $crud->callback_edit_field('order',array($this,'add_order_radio'));
        $crud->unset_delete();
        $crud->unset_add();
        $crud->unset_edit();
        $crud->unset_print();
        $crud->unset_export();
        $output = $crud->render();
        $this->show_crud($output);
    }

    function encrypt_password_callback($post_array, $primary_key = null){
        $pass = $post_array['password'];
        $post_array['password'] = $this->_prep_password($pass);
        return $post_array;
    }

    function _prep_password($pass){
        return sha1($pass.$this->config->item('encryption_key'));
    }

    //FORM FUNCTIONS
    function get_lang_details($language){    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->grocery_crud_model->get_language_details($language)));
    }

    function get_interpreters($language){
        $this->load->model('call_data','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call_data->get_interpreters_by_language($language)));
    }

    function add_btg_radio(){
        return ' <input type="radio" name=btg value="0" />No
                 <input type="radio" name=btg value="1" />Yes';
    }

    function add_order_radio(){
        return '<input type="radio" name=order value="0" />Panama
                <input type="radio" name=order value="1" />In-House
                <input type="radio" name=order value="2" />Primary Satellite
                <input type="radio" name=order value="3" />Secondary Satellite';
    }
}