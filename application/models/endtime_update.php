<?php
class Endtime_update extends CI_Model{
    
    function __construct(){
        parent::__construct();
        $this->load->database();
    }
    // TEST
    function get_a_call_to_complete(){
    	$this->db->select('*');
    	$this->db->where('end_timestamp', NULL);
    	$this->db->where('r_or_i >', 0);
    	$call_query = $this->db->get('call_records_t');
		$calls = array();
		if($call_query->result()){
            foreach($call_query->result() as $call){
                $call_s = array(
            	       'uuid' => $call->uuid,
            	         'id' => $call->id,
            	       'drop' => $call->drop,
                      'admin' => $call->admin,
                     'r_or_i' => $call->r_or_i,
               'connected_by' => $call->connected_by,
                 'start_time' => $call->start_time,
                      'intid' => $call->intid,
                    'intname' => $call->intname,
                   'language' => $call->language,
                   'rep_name' => $call->rep_name,
                  'client_id' => $call->client_id,
                 'access_code'=> $call->access_code,
                      'green' => $call->green,
                    'callout' => $call->callout);
		        $set = $this->get_language_set($call->language);
		        if(isset($set)){
			    	$rate = 'OTP' . $set;
			    } else{
			    	$rate = 'OTPLS1';
			    }
		    	$this->db->select('*');
		    	$this->db->where('access_code', $call->access_code);
		    	$client_info = $this->db->get('client_data', 1);
		    	if($client_info->result()){		    		
		    		foreach($client_info->result() as $data){
		    			if ($data->invoice == 'Avaza'){
							$job_number = '02-';
						} else{
							$job_number = '03-';
						}
						$job_number .= $call->id + 11176;
						$call_s['client_id'] = $data->client_id;
						$call_s['job_number'] = $job_number;
			    		$call_s['account_number'] = $data->account_number;
			    		$call_s['client_name'] = $data->client_name;
			    		$call_s['invoice'] = $data->invoice;
			    		$call_s['rate'] = $data->OTPLS1;
			    		$ls = 'L' . substr($set, -1);
			    		$call_s['language_set'] = $ls;
		    		}        
		        }
		        $calls[] = $call_s;
		    }
            return $calls;
        } else{
        	return false;
        }
    }

    function get_a_call_to_recheck(){
    	$this->db->select('*');
    	$this->db->where('end_timestamp', '0000-00-00 00:00:00');
    	$this->db->where('r_or_i >', 0);
    	$call_query = $this->db->get('call_records_t');
		$calls = array();
		if($call_query->result()){
            foreach($call_query->result() as $call){
                $call_s = array(
            	       'uuid' => $call->uuid,
            	         'id' => $call->id,
            	       'drop' => $call->drop,
                      'admin' => $call->admin,
                     'r_or_i' => $call->r_or_i,
               'connected_by' => $call->connected_by,
                 'start_time' => $call->start_time,
                      'intid' => $call->intid,
                    'intname' => $call->intname,
                   'language' => $call->language,
                   'rep_name' => $call->rep_name,
                  'client_id' => $call->client_id,
                 'access_code'=> $call->access_code,
                      'green' => $call->green,
                    'callout' => $call->callout);
		        $set = $this->get_language_set($call->language);
		        if(isset($set)){
			    	$rate = 'OTP' . $set;
			    } else{
			    	$rate = 'OTPLS1';
			    }
		    	$this->db->select('*');
		    	$this->db->where('access_code', $call->access_code);
		    	$client_id = $this->db->get('client_data', 1);
		    	if($client_id->result()){
		    		
		    		foreach($client_id->result() as $data){
		    			if ($call->invoice == 'Avaza'){
							$job_number = '02-';
						} else{
							$job_number = '03-';
						}
						$job_number .= $call->id + 11176;
						$call_s['job_number'] = $job_number;
			    		$call_s['account_number'] = $data->account_number;
			    		$call_s['client_name'] = $data->client_name;
			    		$call_s['invoice'] = $data->invoice;
			    		$call_s['rate'] = $data->OTPLS1;
			    		$ls = 'L' . substr($set, -1);
			    		$call_s['language_set'] = $ls;
		    		}        
		        }
		        $calls[] = $call_s;
		    }
            return $calls;
        } else{
        	return false;
        }
    }
	
	function get_cudatel_data($type, $uuid, $session){
		if($type == 0){
			$url = 'http://192.168.1.254/gui/cdr/cdr?uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 1){
			$url = 'http://192.168.1.254/gui/cdr/cdr?direction=outbound&uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 2){
			$url = 'http://192.168.1.254/gui/cdr/cdr?bleg_uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 3){
			$url = 'http://192.168.1.254/gui/cdr/cdr?direction=outbound&bleg_uuid=' . $uuid . '&sessionid=' . $session;
		} else{
			return "NO TYPE";
		}
		$cudatel_data = $this->get_call($url);
		return $cudatel_data;		
	}

	function get_cudatel_session(){
		$ch = curl_init();
		$user = '5041';
		$pass = '1405';
		$url = 'http://192.168.1.254/gui/login/login?__auth_user=' . $user . '&__auth_pass=' . $pass;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/bps_session');
		$content = curl_exec($ch);
		$beginning = explode("bps_session=", $content);
		$session = explode(";", $beginning['1']);
		$return_data = array('session_id' => $session['0']);
		return $return_data;
	}

	function get_jol_session($user, $pass){
		$ch = curl_init();
		$url = 'http://192.168.1.254/gui/calls/listmine?__auth_user=' . $user . '&__auth_pass=' . $pass;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/bps_session_' . $user);
		$content = curl_exec($ch);
		$beginning = explode("bps_session=", $content);
		$session = explode(";", $beginning['1']);
		$return_data = array('session_id' => $session['0']);
		return $return_data;
	}

	function get_jol_uuid($user, $session){
		$ch = curl_init();
		$url = 'http://192.168.1.254/gui/calls/listmine?sessionid=' . $session;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, array('Content-type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/bps_session_' . $user);
		$content = curl_exec($ch);
		$content = curl_exec($ch);
		$beginning = explode("text/json", $content);
		$data = json_decode($beginning['1']);
		return $data;
	}

	function get_call($url){
		$this->firephp->log($url);
		$ch = curl_init($url);
		$options = array(CURLOPT_HTTPHEADER => array('Content-type: application/json'), CURLOPT_COOKIEFILE => '/tmp/bps_session', CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $options);
		$get_data = curl_exec($ch);
		curl_close($ch);
		$check_error = json_decode($get_data);		
		$this->firephp->log($get_data);
		if(isset($check_error->error)){
			return FALSE;
		} else{
			return $get_data;				
		}
	}

    function get_language_set($language){
    	$this->db->select('language_set');
    	$this->db->where('language_code', $language);
    	$query = $this->db->get('languages');
    	if($query->result()){
    		foreach($query->result() as $sets){
    			$set = $sets->language_set;
    		}
    		return $set;
    	} else{
    		return false;
    	}
    }

    function update_record_db(){
		$call_details = array(
    	       'accountcode' => $this->input->post('accountcode'),
		        'bbx_cdr_id' => $this->input->post('bbx_cdr_id'),
		_number;
			    		$call_s['account_number'] = $data->account_number;
			    		$call_s['client_name'] = $data->client_name;
			    		$call_s['invoice'] = $data->invoice;
			    		$call_s['rate'] = $data->OTPLS1;
			    		$ls = 'L' . substr($set, -1);
			    		$call_s['language_set'] = $ls;
		    		}        
		        }
		        $calls[] = $call_s;
		    }
            return $calls;
        } else{
        	return false;
        }
    }
	
	function get_cudatel_data($type, $uuid, $session){
		if($type == 0){
			$url = 'http://192.168.1.254/gui/cdr/cdr?uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 1){
			$url = 'http://192.168.1.254/gui/cdr/cdr?direction=outbound&uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 2){
			$url = 'http://192.168.1.254/gui/cdr/cdr?bleg_uuid=' . $uuid . '&sessionid=' . $session;
		} else if($type == 3){
			$url = 'http://192.168.1.254/gui/cdr/cdr?direction=outbound&bleg_uuid=' . $uuid . '&sessionid=' . $session;
		} else{
			return "NO TYPE";
		}
		$cudatel_data = $this->get_call($url);
		return $cudatel_data;		
	}

	function get_cudatel_session(){
		$ch = curl_init();
		$user = '5041';
		$pass = '1405';
		$url = 'http://192.168.1.254/gui/login/login?__auth_user=' . $user . '&__auth_pass=' . $pass;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/bps_session');
		$content = curl_exec($ch);
		$beginning = explode("bps_session=", $content);
		$session = explode(";", $beginning['1']);
		$return_data = array('session_id' => $session['0']);
		return $return_data;
	}

	function get_jol_session($user, $pass){
		$ch = curl_init();
		$url = 'http://192.168.1.254/gui/calls/listmine?__auth_user=' . $user . '&__auth_pass=' . $pass;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/bps_session_' . $user);
		$content = curl_exec($ch);
		$beginning = explode("bps_session=", $content);
		$session = explode(";", $beginning['1']);
		$return_data = array('session_id' => $session['0']);
		return $return_data;
	}

	function get_jol_uuid($user, $session){
		$ch = curl_init();
		$url = 'http://192.168.1.254/gui/calls/listmine?sessionid=' . $session;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, array('Content-type: application/json'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/bps_session_' . $user);
		$content = curl_exec($ch);
		$content = curl_exec($ch);
		$beginning = explode("text/json", $content);
		$data = json_decode($beginning['1']);
		return $data;
	}

	function get_call($url){
		$this->firephp->log($url);
		$ch = curl_init($url);
		$options = array(CURLOPT_HTTPHEADER => array('Content-type: application/json'), CURLOPT_COOKIEFILE => '/tmp/bps_session', CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $options);
		$get_data = curl_exec($ch);
		curl_close($ch);
		$check_error = json_decode($get_data);		
		$this->firephp->log($get_data);
		if(isset($check_error->error)){
			return FALSE;
		} else{
			return $get_data;				
		}
	}

    function get_language_set($language){
    	$this->db->select('language_set');
    	$this->db->where('language_code', $language);
    	$query = $this->db->get('languages');
    	if($query->result()){
    		foreach($query->result() as $sets){
    			$set = $sets->language_set;
    		}
    		return $set;
    	} else{
    		return false;
    	}
    }

    function update_record_db(){
		$call_details = array(
