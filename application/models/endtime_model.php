<?php
class Endtime_model extends CI_Model{
    
    function __construct(){
        parent::__construct();
        $this->load->database();
    }

    function get_calls_to_complete(){
    	$this->db->select('uuid');
    	$this->db->where('end_timestamp', NULL);
    	$this->db->where('r_or_i >', 0);
        $this->db->where('error', 0);
    	$this->db->order_by('id', 'asc');
    	$query = $this->db->get('call_records_t');
    	if($query->result()){
    		return $query->result();
    	} else{
    		return FALSE;
    	}
    }

    function get_cudatel_session(){
		$ch = curl_init();
		$user = '5041';
		$pass = '1405';
		$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/login/login?__auth_user=' . $user . '&__auth_pass=' . $pass;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER  ,1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/bps_session');
		$content = curl_exec($ch);
		$beginning = explode("bps_session=", $content);
		$session = explode(";", $beginning['1']);
		$return_data = $session['0'];
		return $return_data;
	}

	function endtime_data_viewer($uuid, $session){
		$call_data = $this->get_call_data($uuid);
        $required_legs = 2;
        if($call_data == FALSE){
        	return array('issue' => 'NO RECORD OF CALL IN DATABASE');
        }
        $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 5, $call_data);
        return $data;
	}

	function call_data_viewer($uuid, $session){
		$call_data = $this->get_call_data($uuid);
        if($call_data == FALSE){
        	return array('issue' => 'NO RECORD OF CALL IN DATABASE');
        }
        $data = $this->endtime_service($uuid, $session, TRUE);
        return $data;
	}

	function endtime_service($uuid=FALSE, $session=FALSE, $view = FALSE){
        if($uuid == FALSE || $session == FALSE){
                return array('issue' => 'UUID MISSING - ERROR');
        } else{
    		$call_data = $this->get_call_data($uuid);
            $required_legs = 2;
            if($call_data == FALSE){
            	return array('issue' => 'NO RECORD OF CALL IN DATABASE');
            }
            if($call_data['0']->intid == 0 && $call_data['0']->r_or_i == 1 && $call_data['0']->drop == 0){
    			$call_data['0']->intid = $call_data['0']->connected_by;
    		}
    		if($call_data['0']->language == 0 && $call_data['0']->intid != 0){
    			$language = $this->lookup_interpreter_language($call_data['0']->intid);
    			if($language != FALSE){
    				$call_data['0']->language = $language;
    			}
    		}
            if(intval($call_data['0']->intid) != intval($call_data['0']->connected_by) && $call_data['0']->r_or_i == 1){
            	$call_data['0']->r_or_i = 2;
            }
            if($call_data['0']->drop == 1){
                //DROPPED
                $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 0, $call_data, $view);
            } else{
                if($call_data['0']->intid == $call_data['0']->connected_by && $call_data['0']->r_or_i == 2){
                    //ROUTER INTERPRETED
                    $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 1, $call_data, $view);
                } else if($call_data['0']->intid == $call_data['0']->connected_by && $call_data['0']->r_or_i == 1){
                    //QUEUE INTERPRETED
                    $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 2, $call_data, $view);
                } else{
                    if($call_data['0']->intid != $call_data['0']->connected_by && $call_data['0']->callout == 1){
                        //CONFERENCE ROOM
                        $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 3, $call_data, $view);
                    } else{
                        //ROUTED
                        $required_legs = 3;
                        $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 4, $call_data, $view);
                    }
            	}
            }
            return $data;
        }
	}

	function get_cudatel_legs($uuids, $session, $required_legs, $process_type, $call_data, $view = FALSE){
		$call_legs = $this->process_uuids($uuids, $session, FALSE);
		if(isset($call_legs->error)){
			return $call_legs;
		}
		foreach($call_legs as $key => $call_leg){
			if(!in_array($call_leg->uuid, $uuids)){
				$uuids[] = $call_leg->uuid;
			}
			if(!in_array($call_leg->bleg_uuid, $uuids)){
				$uuids[] = $call_leg->bleg_uuid;
			}
		}
		$all_call_legs = $this->process_uuids($uuids, $session, TRUE);
		$unique_legs = array();
		foreach($all_call_legs as $key => $value){
			if(!in_array($value->bbx_cdr_id, $unique_legs)){
				$unique_legs[] = $value->bbx_cdr_id;
			} else{
				unset($all_call_legs[$key]);
			}
		}
        foreach($call_legs as $key => $value){
            if(!in_array($value->bbx_cdr_id, $unique_legs)){
                $unique_legs[] = $value->bbx_cdr_id;
            } else{
                unset($call_legs[$key]);
            }
        }
        $all_call_legs = array_merge($all_call_legs, $call_legs);
		if($process_type == 5){
			return $all_call_legs;
		}
		if(count($all_call_legs) >= $required_legs){
			$needed_call_data = $this->process_data($all_call_legs, $process_type, $call_data);
			return $needed_call_data;
		} else{
			if($view == TRUE){
				$needed_call_data = $this->process_data($all_call_legs, $process_type, $call_data);
				return $needed_call_data;
			} else{
				if($call_data['0']->submitted == 0){
					return array('issue' => 'ROUTING CALL: ' . $call_data['0']->uuid, 'ongoing' => TRUE);
				} else if($call_data['0']->start_time != '0000-00-00 00:00:00'){
					if(time() - strtotime($call_data['0']->start_time) > 14400){
                        $set_error = $this->error_call($call_data);
                        if($set_error == TRUE){
                            return array('issue' => 'SET AS ERROR:'. $call_data['0']->uuid);
                        } else{
                           return array('issue' => 'CANT SET AS ERROR:'. $call_data['0']->uuid); 
                        }
                    } else{
                        return array('issue' => 'INTERPRETING CALL: ' . $call_data['0']->uuid, 'ongoing' => TRUE);
                    }
				} 	else{
					return array('issue' => 'PROBLEM WITH CALL : ' . $call_data['0']->uuid);
				}
			}
		}		
	}

    function error_call($call_data){
        $this->db->where('id', $call_data['0']->id);
        $this->db->update('call_records_t', array('error' => 1));
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
        
    }

	function process_uuids($uuids, $session, $recheck){
        $unprocessed_data = array();
        foreach($uuids as $key => $uuid){
            if($recheck == TRUE){
                $type = 2;
                $uuid_data = $this->get_cudatel_data($uuid, $session, $type);
            } else{
                $uuid_data = $this->get_cudatel_data($uuid, $session);
            }
            if(isset($uuid_data->error)){
                return $uuid_data;
            }
            foreach($uuid_data as $k => $v){
                $unprocessed_data[] = $v;
            }       
        }
        return $unprocessed_data;
    }

	function get_cudatel_data($uuid, $session, $type = 0){
		$return_array = array();
		while($type < 4){
			if($type == 0){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 1){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?direction=outbound&uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 2){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?bleg_uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 3){
				$url =           $data = $this->get_cudatel_legs(array($uuid), $session, $required_legs, 4, $call_data, $view);
                    }
            	}
            }
            return $data;
        }
	}

	function get_cudatel_legs($uuids, $session, $required_legs, $process_type, $call_data, $view = FALSE){
		$call_legs = $this->process_uuids($uuids, $session, FALSE);
		if(isset($call_legs->error)){
			return $call_legs;
		}
		foreach($call_legs as $key => $call_leg){
			if(!in_array($call_leg->uuid, $uuids)){
				$uuids[] = $call_leg->uuid;
			}
			if(!in_array($call_leg->bleg_uuid, $uuids)){
				$uuids[] = $call_leg->bleg_uuid;
			}
		}
		$all_call_legs = $this->process_uuids($uuids, $session, TRUE);
		$unique_legs = array();
		foreach($all_call_legs as $key => $value){
			if(!in_array($value->bbx_cdr_id, $unique_legs)){
				$unique_legs[] = $value->bbx_cdr_id;
			} else{
				unset($all_call_legs[$key]);
			}
		}
        foreach($call_legs as $key => $value){
            if(!in_array($value->bbx_cdr_id, $unique_legs)){
                $unique_legs[] = $value->bbx_cdr_id;
            } else{
                unset($call_legs[$key]);
            }
        }
        $all_call_legs = array_merge($all_call_legs, $call_legs);
		if($process_type == 5){
			return $all_call_legs;
		}
		if(count($all_call_legs) >= $required_legs){
			$needed_call_data = $this->process_data($all_call_legs, $process_type, $call_data);
			return $needed_call_data;
		} else{
			if($view == TRUE){
				$needed_call_data = $this->process_data($all_call_legs, $process_type, $call_data);
				return $needed_call_data;
			} else{
				if($call_data['0']->submitted == 0){
					return array('issue' => 'ROUTING CALL: ' . $call_data['0']->uuid, 'ongoing' => TRUE);
				} else if($call_data['0']->start_time != '0000-00-00 00:00:00'){
					if(time() - strtotime($call_data['0']->start_time) > 14400){
                        $set_error = $this->error_call($call_data);
                        if($set_error == TRUE){
                            return array('issue' => 'SET AS ERROR:'. $call_data['0']->uuid);
                        } else{
                           return array('issue' => 'CANT SET AS ERROR:'. $call_data['0']->uuid); 
                        }
                    } else{
                        return array('issue' => 'INTERPRETING CALL: ' . $call_data['0']->uuid, 'ongoing' => TRUE);
                    }
				} 	else{
					return array('issue' => 'PROBLEM WITH CALL : ' . $call_data['0']->uuid);
				}
			}
		}		
	}

    function error_call($call_data){
        $this->db->where('id', $call_data['0']->id);
        $this->db->update('call_records_t', array('error' => 1));
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
        
    }

	function process_uuids($uuids, $session, $recheck){
        $unprocessed_data = array();
        foreach($uuids as $key => $uuid){
            if($recheck == TRUE){
                $type = 2;
                $uuid_data = $this->get_cudatel_data($uuid, $session, $type);
            } else{
                $uuid_data = $this->get_cudatel_data($uuid, $session);
            }
            if(isset($uuid_data->error)){
                return $uuid_data;
            }
            foreach($uuid_data as $k => $v){
                $unprocessed_data[] = $v;
            }       
        }
        return $unprocessed_data;
    }

	function get_cudatel_data($uuid, $session, $type = 0){
		$return_array = array();
		while($type < 4){
			if($type == 0){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 1){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?direction=outbound&uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 2){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?bleg_uuid=' . $uuid . '&sessionid=' . $session;
			} else if($type == 3){
				$url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?direction=outbound&bleg_uuid=' . $uuid . '&sessionid=' . $session;
			} else{
				return "NO TYPE";
			}
			$type++;
			$raw_data = $this->get_call($url);
			if(isset($raw_data->error)){
				return $raw_data;
			} else{
				$raw_leg[] = json_decode($raw_data);
				foreach($raw_leg as $key => $leg){
					if(isset($leg->cdr['0'])){
						foreach($leg->cdr as $k => $v){
							$return_array[] = $v;
						}
					}				
				}
			}			
		}
		return $return_array;		
	}

    function get_call($url){
		$ch = curl_init($url);
		$options = array(CURLOPT_HTTPHEADER => array('Content-type: application/json'), CURLOPT_COOKIEFILE => '/tmp/bps_session', CURLOPT_RETURNTRANSFER => true);
		curl_setopt_array($ch, $options);
		$get_data = curl_exec($ch);
		curl_close($ch);
		$check_error = json_decode($get_data);
		if(isset($check_error->error)){
			return $check_error;
		} else{
			return $get_data;			
		}
    }

    function get_call_data($uuid){
    	$this->db->select('*');
    	$this->db->where('uuid', $uuid);
    	$query = $this->db->get('call_records_t', 1);
    	if($query->result()){
    		return $query->result();
    	} else{
    		return FALSE;
    	}
    }

    function process_data($all_call_legs, $process_type, $call_data){
    	$correct_call_leg = $this->get_correct_leg($all_call_legs);
    	$correct_call_leg['id'] = $call_data['0']->id;
		$correct_call_leg['access_code'] = $call_data['0']->access_code;
		$correct_call_leg['rep_name'] = $call_data['0']->rep_name;
		$correct_call_leg['specialf'] = $call_data['0']->specialf;
		$correct_call_leg['language'] = $call_data['0']->language;
		$correct_call_leg['intid'] = $call_data['0']->intid;
		$correct_call_leg['uuid'] = $call_data['0']->uuid;
		$correct_call_leg['start_time'] = $call_data['0']->start_time;
		$correct_call_leg['drop'] = $call_data['0']->drop;
		$correct_call_leg['callout'] = $call_data['0']->callout;
		$correct_call_leg['admin'] = $call_data['0']->admin;
		$correct_call_leg['co_num'] = $call_data['0']->co_num;
		$correct_call_leg['r_or_i'] = $call_data['0']->r_or_i;
		$correct_call_leg['connected_by'] = $call_data['0']->connected_by;
		$correct_call_leg['r_ext'] = $call_data['0']->r_ext;
		$correct_call_leg['submitted'] = $call_data['0']->submitted;
    	if($process_type == 0){
    		$correct_call_leg['call_type'] = 'DROPPED';
    		//latest end time earliest answer time
    	} else if($process_type == 1){
    		$correct_call_leg['call_type'] = 'SELF INTERPRETED';
    		//start_call button start_timestamp latest end_time
    	} else if($process_type == 2){
    		$correct_call_leg['call_type'] = 'QUEUE INTERPRETED';
    		//latest end time answer timestamp start_timestamp
    	} else if($process_type == 3){
    		$correct_call_leg['call_type'] = 'CONFERENCE CALL';
    		//latest end time 
    	} else if($process_type == 4){
    		$correct_call_leg['call_type'] = 'ROUTED';
    		//latest end time
    	}
    	$times = $this->calculate_times($correct_call_leg, $process_type);
    	$correct_call_leg['connection_time'] = $times['0'];
    	$correct_call_leg['call_time'] = $times['1'];
    	if($correct_call_leg['drop'] == 1 && $correct_call_leg['call_time'] == 0 && $correct_call_leg['connection_time'] <= 120){
    		$correct_call_leg['drop'] = 0;
    		$correct_call_leg['admin'] = 1;
    		$correct_call_leg['call_type'] = 'ADMIN';
    	} else{
    		$correct_call_leg['admin'] = 0;
    	}
    	$correct_call_leg['job_number'] = $correct_call_leg['id'] + 11176;
		$client = $this->lookup_client($correct_call_leg['access_code']);
		if(!empty($client)){
			$correct_call_leg['client_id'] = $client['0']->client_id;
			$correct_call_leg['account_number'] = $client['0']->account_number;
			$correct_call_leg['client_name'] = $client['0']->client_name;
			$correct_call_leg['invoice'] = $client['0']->invoice;
		} else{
			return array('issue' => 'BAD ACCESS CODE : ' . $correct_call_leg['uuid']);
		}
		
		$correct_call_leg['intname'] = $this->lookup_interpreter($correct_call_leg['intid']);
		$rate_data = $this->get_rate_data($call_data['0']->access_code, $call_data['0']->language, $call_data['0']->start_time, $call_data['0']->callout);
		$correct_call_leg['rate'] = $rate_data['0'];
		$correct_call_leg['rate_code'] = $rate_data['1'];
    	return $correct_call_leg;
    }

    function get_caller_id($legs){
        $early = INF;
        $lates = -INF;
        $caller_num = $legs['0']->caller_id_number;
        $caller_nam = $legs['0']->caller_id_name;
        $dest_num = $legs['0']->destination_number;
        $dest_nam = $legs['0']->destination_name;
        foreach($legs as $k => $l){
            if(strtotime($l->answer_timestamp) < $early){
                $early = strtotime($l->answer_timestamp);
            }
        }
        $real_early = INF;
        foreach($legs as $k => $l){
            if(strtotime($l->answer_timestamp) > $early && strtotime($l->answer_timestamp) < $real_early){
                $real_early = strtotime($l->answer_timestamp);
                if(strlen($l->caller_id_number) == 11){
                    $caller_num = $l->caller_id_number;
                    $caller_nam = $l->caller_id_name;
                    $dest_num = $l->destination_number;
                    $dest_nam = $l->destination_name;
                } else {
                    $caller_num = $l->destination_number;
                    $caller_nam = $l->destination_name;
                    $dest_num = $l->caller_id_number;
                    $dest_nam = $l->caller_id_name;
                }

            }
        }
        return array($caller_num, $caller_nam, $dest_num, $dest_nam);
    }

    function get_correct_leg($legs){
    	$early = INF;
    	$lates = -INF;
    	$correct_key = FALSE;
    	$checker = array();
    	$check_legs = array();
    	foreach($legs as $k => $l){
			if(strtotime($l->answer_timestamp) < $early){
				$early = strtotime($l->start_timestamp);
				if(strlen($l->caller_id_number) == 11){
					$caller_num = $l->caller_id_number;
					$caller_nam = $l->caller_id_name;
					$dest_num = $l->destination_number;
					$dest_nam = $l->destination_name;
				} else if (strlen($l->destination_number) == 11){
					$caller_num = $l->destination_number;
					$caller_nam = $l->destination_name;
					$dest_num = $l->caller_id_number;
					$dest_nam = $l->caller_id_name;
				} else{
                    $caller_id_array = $this->get_caller_id($legs);
                    $caller_num = $caller_id_array['0'];
                    $caller_nam = $caller_id_array['1'];
                    $dest_num = $caller_id_array['2'];
                    $dest_nam = $caller_id_array['3'];
                }
    		}
    		$check_legs[] = $checker;
    	}
    	foreach($legs as $key => $leg){
			if(strtotime($leg->end_timestamp) > $lates){
				$lates = strtotime($leg->end_timestamp);
				$correct_key = $key;
    		}
    	}
    	$checker['answer_timestamp'] = $legs[$correct_key]->answer_timestamp;
    	$checker['start_timestamp'] = $legs[$correct_key]->start_timestamp;
    	$checker['end_timestamp'] = $legs[$correct_key]->end_timestamp;
    	$checker['duration'] = $legs[$correct_key]->duration;
    	$checker['billsec'] = $legs[$correct_key]->billsec;
    	$checker['accountcode'] = $legs[$correct_key]->accountcode;
    	$checker['bbx_cdr_id'] = $legs[$correct_key]->bbx_cdr_id;
    	$checker['record_file_name'] = $legs[$correct_key]->record_file_name;
    	$checker['direction'] = $legs[$correct_key]->direction;
    	$checker['caller_id_name'] = $caller_nam;
    	$checker['caller_id_number'] = $caller_num;
    	$checker['destination_name'] = $dest_nam;
    	$checker['destination_number'] = $dest_num;
    	$checker['context'] = $legs[$correct_key]->context;
    	$checker['hangup_cause'] = $legs[$correct_key]->hangup_cause;
    	$checker['read_rate'] = $legs[$correct_key]->read_rate;
    	$checker['read_codec'] = $legs[$correct_key]->read_codec;
    	$checker['write_rate'] = $legs[$correct_key]->writ($conn_ms, $call_ms);
    }

    function update_call($process_data, $orig_uuid){
        $this->db->where('uuid', $orig_uuid);
        $this->db->update('call_records_t', $process_data);
        if($this->db->affected_rows() > 0){
        	return TRUE;
        } else{
        	return FALSE;
        }
    }

    function update_record_db(){
		if($this->input->post('issue')){
			return json_encode($this->input->post('issue'));
		}
		$call_details = array(
	        'id' => $this->input->post('id'),
			'job_number' => $this->input->post('job_number'),
			'access_code' => $this->input->post('access_code'),
			'client_id' => $this->input->post('client_id'),
			'rep_name' => $this->input->post('rep_name'),
			'specialf' => $this->input->post('specialf'),
			'language' => $this->input->post('language'),
			'intid' => $this->input->post('intid'),
			'intname' => $this->input->post('intname'),
			'uuid' => $this->input->post('uuid'),
			'bleg_uuid' => $this->input->post('bleg_uuid'),
			'orig_uuid' => $this->input->post('orig_uuid'),
			'start_time' => $this->input->post('start_time'),
			'answer_timestamp' => $this->input->post('answer_timestamp'),
			'start_timestamp' => $this->input->post('start_timestamp'),
			'end_timestamp' => $this->input->post('end_timestamp'),
			'call_time' => $this->input->post('call_time'),
			'connection_time' => $this->input->post('connection_time'),
			'duration' => $this->input->post('duration'),
			'billsec' => $this->input->post('billsec'),
			'accountcode' => $this->input->post('accountcode'),
			'bbx_cdr_id' => $this->input->post('bbx_cdr_id'),
			'record_file_name' => $this->input->post('record_file_name'),
			'direction' => $this->input->post('direction'),
			'caller_id_name' => $this->input->post('caller_id_name'),
			'caller_id_number' => $this->input->post('caller_id_number'),
			'destination_name' => $this->input->post('destination_name'),
			'destination_number' => $this->input->post('destination_number'),
			'context' => $this->input->post('context'),
			'hangup_cause' => $this->input->post('hangup_cause'),
			'read_rate' => $this->input->post('read_rate'),
			'read_codec' => $this->input->post('read_codec'),
			'write_rate' => $this->input->post('write_rate'),
			'write_codec' => $this->input->post('write_codec'),
			'drop' => $this->input->post('drop'),
			'callout' => $this->input->post('callout'),
			'admin' => $this->input->post('admin'),
			'co_num' => $this->input->post('co_num'),
			'client_name' => $this->input->post('client_name'),
			'account_number' => $this->input->post('account_number'),
			'invoice' => $this->input->post('invoice'),
			'rate' => $this->input->post('rate'),
			'rate_code' => $this->input->post('rate_code'),
			'r_or_i' => $this->input->post('r_or_i'),
			'connected_by' => $this->input->post('connected_by'),
			'r_ext' => $this->input->post('r_ext'),
			'error' => $this->input->post('error'),
			'submitted' => $this->input->post('submitted'),
			'call_type' => $this->input->post('call_type')
		);
    	$this->db->where('id', $this->input->post('id'));
		$this->db->update('call_records_t', $call_details);
		if($this->db->affected_rows() > 0){
			return $this->input->post('uuid');
		} else{
			$return = array($this->input->post('id'), $this->db->_error_message());
			return json_encode($return);
		}
    }
}
?>

