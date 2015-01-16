<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class Auth_model
 * This model handles all processing of authentication for the Alvin System
 * @functions authenticated(), authenticate(), deAuthenticate(),
 *
 * @author Josh Murray
 *
 * @property Alvin_Session $session (Session Extension Class)
 */

class Validation_model extends Alvin_Model {

    function __construct()
    {
        parent::__construct();
        $this->_ci =& get_instance();
        $this->_ci->load->model('user_model', '', true);
    }

    public function collect($formName)
    {
        return  call_user_func_array([$this, $formName . 'Form'],[]);
    }

    public function post($formName, $model_function)
    {
        $form = $this->collect($formName);

        foreach($form['fields'] as $column => $field)
        {
            $postValue = $this->input->post($field['name']);
            $field['value'] = isset($postValue) ? $postValue : $field['value'];

        }



        if(isset($user, $pass))
        {
            return $this->_ci->user_model->authenticate($user, $this->hash($pass));
        }

        return $this->_ci->session->messageInvalid('Please Enter a Username AND Password');
    }

    public function build($formName)
    {
        return $formName;
    }

    /**
     * Create a NEW session if credential inputs are valid within Alvin and CudaTel systems
     * Requires POST [username, password]
     *
     * @return array
     */
    public function loginForm()
    {
        $form = [
            'model' => 'user_model',
            'fields' => [
                'auth_usern' => [
                    'name' => 'username',
                    'rules' => '',
                    'print' => 'Username',
                    'value' => ''
                ],
                'auth_passw' => [
                    'name' => 'password',
                    'rules' => '',
                    'print' => 'Password',
                    'value' => ''
                ]
            ]
        ];

        return $form;

    }
}
    
    