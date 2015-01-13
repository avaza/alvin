<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Login_model extends CI_Model{
    
    function __construct(){
        parent::__construct();
    }

    function validate($username, $pass){
        $password = $this->_prep_password($pass);
        $this->db->where('username', $username);
        $this->db->where('password', $password);
        $query = $this->db->get('users', 1);
        if ($query->num_rows() == 1){
            $row = $query->row();
            $session_file = '/tmp/cudasess' . $row->ext;
            $data = array(
                    'userid'        => $row->id,
                    'intid'         => $row->intid,
                    'fname'         => $row->fname,
                    'lname'         => $row->lname,
                    'cls'           => $row->class,
                    'level'         => $row->level,
                    'ext'           => $row->ext,
                    'pin'           => $row->pin,
                    'username'      => $row->username,
                    'langs'         => $row->langs,
                    'lang'          => $row->lang,             
                    'session_file'  => $session_file,
                    'DR'            => $row->DR_REP,
                    'validated'     => TRUE
                    );
            $this->session->set_userdata($data);
            return TRUE;
        } else{
        return FALSE;
        }
    }

    function cudatel_login(){
        $url = 'http://192.168.1.254/gui/login';
        $ch = curl_init($url);
        $options = array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => '__auth_user=' . $this->session->userdata('ext') . '&__auth_pass=' . $this->session->userdata('pin'), CURLOPT_COOKIEJAR => $this->session->userdata('session_file'), CURLOPT_RETURNTRANSFER => TRUE);
        curl_setopt_array($ch, $options);
        $output = curl_exec ($ch);
        curl_close($ch);
        $login_data = (json_decode($output, TRUE));
        if(isset($login_data['error']) && $login_data['error'] == 'NOTAUTHORIZED'){
            return FALSE;
        } else{
            if(isset($login_data['data'])){
                $logged = $login_data['data'];
            }
            if(isset($logged['bbx_user_username']) && $logged['bbx_user_username'] == $this->session->userdata('ext')){
                return TRUE;
            }
        }
    }

    function get_cudatel_sessionID(){
        $beginning = explode("bps_session=", $this->session->userdata('session_file'));
        $session = explode(";", $beginning['1']);
        $return_data = $session['0'];
    }

    function _prep_password($pass){
        return sha1($pass.$this->config->item('encryption_key'));
    }

    function get_language_list(){
        $this->db->select('id, language');
        $query = $this->db->get('languages');
        return $query->result();
    }

    function check_if_username_exists($username){
        $this->db->select('username');
        $this->db->where('username', $username);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0){
            return true;
        } else{
            return false;
        }
    }

    function check_if_intid_exists($intid){
        $this->db->select('intid');
        $this->db->where('intid', $intid);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0){
            return true;
        } else{
            return false;
        }
    }

    function check_if_extension_exists($extension){
        $this->db->select('ext');
        $this->db->where('ext', $extension);
        $query = $this->db->get('users');
        if ($query->num_rows() > 0){
            return true;
        } else{
            return false;
        }
    }


    //END EDIT TIMCLOCK PUNCHES

    //ADD NEW PUNCHES INLINE
    function add_punch($intid, $timestamp, $type){
        $timestamp = urldecode($timestamp);
        $prev_punch_id = $this->get_prev_punch_id($intid, $timestamp);
        if($prev_punch_id == FALSE){
            $this->add_first_punch($intid, $timestamp, $type);
        } else{
            $next_punch_ts = $this->get_next_punch_ts($intid, $timestamp);
            $time_punch = FALSE;
            if($type == 1){
                $status_name = 'AVAILABLE';
                if($prev_punch_id['type'] != 2 && $prev_punch_id['type'] !== 4){
                    $time_punch = TRUE;
                }                        
            } else if($type == 0){
                $time_punch = TRUE;
                $status_name = 'CLOCKED-OUT';
            } else if($type == 3){
                $time_punch = TRUE;
                $status_name = 'LUNCH';
            } else if($type == 2){
                $status_name = 'BREAK';
            } else if($type == 4){
                $status_name = 'LEAVE-BLDG';
            }
            $punch = array(
                'this_punch' => $timestamp,
                'time_punch' => $time_punch,
                'status_type' => $type,
                'intid' => $intid,
                'status_name' => $status_name);
            $this->db->insert('timeclock', $punch);
            if($this->db->affected_rows() > 0){
                $update_last = $this->update_last_punch($prev_punch_id['0']->id, $timestamp);
                
                return TRUE;
            } else{
                return FALSE;
            }
        }
    }

    function add_first_punch($intid, $timestamp, $type){
        $time_punch = FALSE;
        $time_punch = TRUE;
        $status_name = 'AVAILABLE';        
        $punch = array(
            'this_punch' => $timestamp,
            'time_punch' => $time_punch,
            'status_type' => $type,
            'intid' => $intid,
            'status_name' => $status_name);
        $this->db->insert('timeclock', $punch);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function get_prev_punch_id($intid, $timestamp){
        $this->db->select('*');
        $this->db->where('intid', $intid);
        $this->db->where('this_punch <', $timestamp);
        $this->db->order_by('this_punch', 'desc');
        $query = $this->db->get('timeclock', 1);
        if($query->result()){
            return $query->result();
        } else{
            return FALSE;
        }
    }

    function get_next_punch_ts($intid, $timestamp){
        $this->db->select('this_punch');
        $this->db->where('intid', $intid);
        $this->db->where('this_punch >', $timestamp);
        $this->db->order_by('this_punch', 'asc');
        $query = $this->db->get('timeclock', 1);
        if($query->result()){
            $details = $query->result();
            return $details['0']->this_punch;
        } else{
            return FALSE;
        }
    }
    //END ADD NEW PUNCHES INLINE

    //END TIMECLOCK
}

?>