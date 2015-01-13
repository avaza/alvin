<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Old_auth_model extends CI_Model{
    
    function __construct()
    {
        parent::__construct();
    }

    function getUserCredentials(){
        $this->db->where('auth_token', $this->session->userdata('auth_token'));
        $query = $this->db->get('users');
        if($query->num_rows() == 1){
            return $query->row();
        } else{
            return FALSE;
        }
    }

    function systemLogin(){
        $db_login = array('valid' => FALSE);
        $this->db->where('username', $this->input->post('username'));
        $this->db->where('password', $this->_prep($this->input->post('password')));
        $query = $this->db->get('users', 1);
        if($query->result()){
            $auth_token = $this->auth_token($query->row()->intid);
            if(!$auth_token){
                $db_login['msg'] = '<font color=red>Cant set authentication token for user.</font><br/>';
            } else{
                if(!$this->ctLogin($query->row()->ext, $query->row()->pin)){
                    $db_login['msg'] = '<font color=red>Unable to login to Cudatel server</font><br/>';
                } else{
                    $db_login['valid'] = $this->checkDBSession();
                    if(!$db_login['valid']){
                        $db_login['msg'] = '<font color=red>Session Data Incorrect, Please Retry</font><br/>';
                    }
                }
            }
        } else{
            $db_login['msg'] = '<font color=red>Invalid username and/or password.</font><br/>';
        }
        return $db_login;
    }

    function ctLogin($e, $p){
        $creds = $this->getUserCredentials();
        $ct = curl_init('http://192.168.1.254/gui/login/login?__auth_user=' . $creds->ext . '&__auth_pass=' . $creds->pin);
        curl_setopt($ct, CURLOPT_COOKIEJAR, '/tmp/cudasess' . $creds->ext);
        curl_setopt($ct, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ct);
        curl_close($ct);
        $ct_login = json_decode($data, TRUE);
        if(isset($ct_login['data']) && isset($ct_login['data']['bbx_user_username']) && $ct_login['data']['bbx_user_username'] == $creds->ext){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function systemLogout(){
        $creds = $this->getUserCredentials();
        $file = '/tmp/cudasess' . $creds->ext;
        if(file_exists($file)){
            unset($file);
        }
        $this->session->set_userdata(array());
        $this->session->sess_destroy();
        if($this->checkDBSession()){
            return FALSE;
        } else{
            return TRUE;
        }
    }

    function checkDBSession(){
        if($this->session->userdata('session_id')){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function get_cudatel_sessionID(){
        $beginning = explode("bps_session=", $this->session->userdata('session_file'));
        $session = explode(";", $beginning['1']);
        $return_data = $session['0'];
    }

    function _prep($s){
        $k = $this->config->item('encryption_key');
        return sha1($s . $k);
    }

    function auth_token($intid){
        $auth_token = $this->_prep(strtotime('now') . $intid);
        $user_token = array('auth_token' => $this->_prep(strtotime('now') . $intid));
        $this->session->set_userdata($user_token);
        $this->db->where('intid', $intid);
        $this->db->update('users', $user_token);
        if($this->db->affected_rows() > 0){
            return $user_token['auth_token'];
        } else{
            return FALSE;
        }
    }
}

?>