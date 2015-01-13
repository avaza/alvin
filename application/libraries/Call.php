<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Call Class
 *
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Josh Murray
 */
class Call{///CREATES A CALL - Once "completeNextCall" is run, a fully processed call is recorded in the database.

    protected $_ci;                  // CodeIgniter instance
    protected $gets;                 // data collected during build of call object
    public $call;                    // actual details of the call object

    function __construct($params = array(FALSE, FALSE)){
        $this->_ci = & get_instance();
        $this->_ci->load->database();
        $this->setCudaSess($params['0'], $params['1']);
        return $this;
    }

    //CORE FUNCTIONS
    public function completeNextCall($sess = NULL, $uuid = NULL, $view = NULL){
        $this->gets->stage = 1;
        $this->gets->uuid = isset($uuid) ? $uuid:$this->getNextColdUuid();
        $this->gets->sess = isset($sess) ? $sess:$this->getCudaSess();
        $this->gets->view = $view;
        while($this->isStillOK()){
            $this->nextStage();
        }
        return $this->call;
    }

    protected function isStillOK(){
        if($this->call->error != 2 && $this->gets->stage <= 6){
            if(!isset($this->gets->view)){
                return TRUE;
            } else if(($this->gets->stage - 1) != $this->gets->view){
                return TRUE;
            } else{
                return FALSE;
            }
        } else{
            return FALSE;
        }
    }

    protected function nextStage(){
        switch ($this->gets->stage){
            case 1:
                $this->prepareLegs();
                break;
            case 2:
                $this->collectLegs();
                break;
            case 3:
                $this->dissectLegs();
                break;
            case 4:
                $this->correctLegs();               
                break;
            case 5:
                $this->compactLegs();
                break;   
            case 6:
                $this->injectCallToDB();
                break;
        }
        $this->gets->stage++;
        return $this;
    }

    protected function run($functions){
        $index = 0;
        while($this->isStillOK() && $index < count($functions)){
            call_user_func(array($this, $functions[$index]));
            $index++;
        }
        return $this;
    }
    //END CORE FUNCTIONS

    //SESSION CURL FUNCTIONS
    protected function setCudaSess($user, $pass){
        if(!$user || !$pass){
            die(json_encode(array('ISSUE' => 'INVALID REQUEST')));
        } else{
            $sess = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/gui/login/login?__auth_user=' . $user . '&__auth_pass=' . $pass);
        }        
        curl_setopt($sess, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($sess, CURLOPT_HEADER, TRUE);
        curl_setopt($sess, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($sess, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($sess, CURLOPT_COOKIEJAR, '/tmp/bps_session');
        $this->gets->curl->sess = $sess;
        return $this;
    }

    public function getCudaSess(){
        $s = curl_exec($this->gets->curl->sess);
        curl_close($this->gets->curl->sess);
        preg_match_all('~bps_session=(.*?);~s', $s, $m);
        return $m['1']['0'];
    }
    //END SESSION CURL FUNCTIONS

    //ERROR HANDLERS
    protected function isInProgress($m){
       $this->call->error = 2;
       $this->call->error_message = $m;
       $this->call->returned = $m;
       $this->updateInProgress($m);
       return $this;
    }

    protected function fatalError($m){
        $this->call->error = 2;
        $this->call->error_message = $m;
        $this->updateLinkError($m);
    }

    protected function flagMessage($m){
        $this->call->error = 1;
        $this->call->error_message = isset($this->call->error_message) ? $this->call->error_message . '<br>' . $m:$m;
        return $this;
    }

    protected function clearErrors(){
        $this->call->error = 0;
        $this->call->error_message = NULL;     
        return $this;
    }
    //END ERROR HANDLERS

    //PREPARE DATA FOR PROCESSING
    protected function prepareLegs(){
        $functions = array(
            'setDBInputs',
            'checkStatus',
            'checkTimestamps',
            'checkDBRepairable',
            'chkDBInputs',
            'checkDBCritical'
        );
        $this->run($functions);
        return $this;
    }

    protected function setDBInputs(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('uuid', $this->gets->uuid);
        $q = $this->_ci->db->get('call_links');
        if($q->result()){
            foreach($q->result() as $call){
                $this->call = $call;
                $this->gets->original = clone $call;
            }
            $this->clearErrors();
        } else{
            $this->fatalError('CALL NOT IN DB');
        }
        return $this;
    }

    protected function checkStatus(){
    	if($this->call->deleted == 1){
    		$this->fatalError('DELETED BY USER');
    	}
    }

    protected function checkTimestamps(){
        $link = strtotime($this->call->link_timestamp);
        $subm = strtotime($this->call->submit_timestamp);
        if($this->isAValidTime($link) === FALSE){
            $this->gets->repair[] = 'link_timestamp';
        }
        if($this->isAValidTime($subm) === FALSE){
            $this->gets->repair[] = 'submit_timestamp';
        }
        return $this;
    }

    protected function isAValidTime($time){
        if($time === FALSE || $time <= 1367323199){
            return FALSE;
        } else{
            return TRUE;
        }
    }

    protected function checkDBCritical(){
        $acc = $this->call->access_code;
        $rep = $this->call->caller_name;
    	if(intval($acc) <= 0 || $this->accessCodeExists($acc) === FALSE){
        	$this->gets->irep = TRUE;
            $this->fatalError('INVALID ACCESS CODE');
        }
        if($this->isStillOK() && mb_strlen($rep) == 0){
        	$this->gets->irep = TRUE;
            $this->fatalError('INVALID REP NAME');
        }      
        return $this;
    }

    protected function accessCodeExists($input){
        $this->_ci->db->where('access_code', $input);
        $q = $this->_ci->db->get('client_data');
        if($q->result()){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function checkDBRepairable(){
        $intid = $this->call->interpreter_id;
        $langg = $this->call->language;
        if($this->call->drop == 0){
        	if(intval($intid) <= 0 || $this->interpreterExists($intid) === FALSE){
	            $this->gets->repair[] = 'interpreter_id';
	        }
        }        
        if(mb_strlen($langg) != 3 || $this->languageExists($langg) === FALSE){
            $this->gets->repair[] = 'language';
        }
        return $this;
    }

    protected function interpreterExists($input){
        $this->_ci->db->where('interpreter_id', $input);
        $q = $this->_ci->db->get('interpreter_archive');
        if($q->result()){
            return TRUE;
        } else{
            $this->_ci->db->where('iid', $input);
        	$q = $this->_ci->db->get('interpreters');
        	if($q->result()){
	            foreach($q->result() as $int){
                    if(intval($int->iid) == $input){
                        return TRUE;
                    } else{
                        return FALSE;
                    }
                }              
	        } else{
	            return FALSE;
	        }
        }
    }

    protected function languageExists($input){
        $this->_ci->db->where('language_code', $input);
        $q = $this->_ci->db->get('languages');
        if($q->result()){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function chkDBInputs(){
        if($this->couldBeInProgress()){
        	if($this->hasTakenTooLongTo('process')){
        		$this->autoSubmit();
        	} e

    protected function clearErrors(){
        $this->call->error = 0;
        $this->call->error_message = NULL;     
        return $this;
    }
    //END ERROR HANDLERS

    //PREPARE DATA FOR PROCESSING
    protected function prepareLegs(){
        $functions = array(
            'setDBInputs',
            'checkStatus',
            'checkTimestamps',
            'checkDBRepairable',
            'chkDBInputs',
            'checkDBCritical'
        );
        $this->run($functions);
        return $this;
    }

    protected function setDBInputs(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('uuid', $this->gets->uuid);
        $q = $this->_ci->db->get('call_links');
        if($q->result()){
            foreach($q->result() as $call){
                $this->call = $call;
                $this->gets->original = clone $call;
            }
            $this->clearErrors();
        } else{
            $this->fatalError('CALL NOT IN DB');
        }
        return $this;
    }

    protected function checkStatus(){
    	if($this->call->deleted == 1){
    		$this->fatalError('DELETED BY USER');
    	}
    }

    protected function checkTimestamps(){
        $link = strtotime($this->call->link_timestamp);
        $subm = strtotime($this->call->submit_timestamp);
        if($this->isAValidTime($link) === FALSE){
            $this->gets->repair[] = 'link_timestamp';
        }
        if($this->isAValidTime($subm) === FALSE){
            $this->gets->repair[] = 'submit_timestamp';
        }
        return $this;
    }

    protected function isAValidTime($time){
        if($time === FALSE || $time <= 1367323199){
            return FALSE;
        } else{
            return TRUE;
        }
    }

    protected function checkDBCritical(){
        $acc = $this->call->access_code;
        $rep = $this->call->caller_name;
    	if(intval($acc) <= 0 || $this->accessCodeExists($acc) === FALSE){
        	$this->gets->irep = TRUE;
            $this->fatalError('INVALID ACCESS CODE');
        }
        if($this->isStillOK() && mb_strlen($rep) == 0){
        	$this->gets->irep = TRUE;
            $this->fatalError('INVALID REP NAME');
        }      
        return $this;
    }

    protected function accessCodeExists($input){
        $this->_ci->db->where('access_code', $input);
        $q = $this->_ci->db->get('client_data');
        if($q->result()){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function checkDBRepairable(){
        $intid = $this->call->interpreter_id;
        $langg = $this->call->language;
        if($this->call->drop == 0){
        	if(intval($intid) <= 0 || $this->interpreterExists($intid) === FALSE){
	            $this->gets->repair[] = 'interpreter_id';
	        }
        }        
        if(mb_strlen($langg) != 3 || $this->languageExists($langg) === FALSE){
            $this->gets->repair[] = 'language';
        }
        return $this;
    }

    protected function interpreterExists($input){
        $this->_ci->db->where('interpreter_id', $input);
        $q = $this->_ci->db->get('interpreter_archive');
        if($q->result()){
            return TRUE;
        } else{
            $this->_ci->db->where('iid', $input);
        	$q = $this->_ci->db->get('interpreters');
        	if($q->result()){
	            foreach($q->result() as $int){
                    if(intval($int->iid) == $input){
                        return TRUE;
                    } else{
                        return FALSE;
                    }
                }              
	        } else{
	            return FALSE;
	        }
        }
    }

    protected function languageExists($input){
        $this->_ci->db->where('language_code', $input);
        $q = $this->_ci->db->get('languages');
        if($q->result()){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function chkDBInputs(){
        if($this->couldBeInProgress()){
        	if($this->hasTakenTooLongTo('process')){
        		$this->autoSubmit();
        	} else{
        		$this->isInProgress('processing');
        	}
        }
        return $this;
    }

    protected function couldBeInProgress(){
        if(count($this->gets->repair) > 0){
        	if(in_array('submit_timestamp', $this->gets->repair) && in_array('interpreter_id', $this->gets->repair) && $this->call->drop == 0){
        		return TRUE;
        	} else{
        		return FALSE;
        	}
        }
    }

    protected function hasTakenTooLongTo($a){
        $alw = $a == 'process' ? 30:300;
        $tim = strtotime($this->call->link_timestamp);
        $now = strtotime("now");
        $tfm = ($now - $tim)/60;
        if($tfm >= $alw){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function autoSubmit(){
        $this->call->submit_timestamp = $this->call->link_timestamp;
        $this->flagMessage('AUTO CLICK');
    }
    //END PREPARE DATA FOR PROCESSING

    //COLLECT INITIAL DATA
    protected function collectLegs(){
        $this->runCurlGetFor($this->gets->uuid);
        $cycles = 0;
        while($this->collectedCriticalLegs() === FALSE && $cycles <= 3){
            foreach($this->call->data as $leg){
                $this->runCurlGetFor($leg->uuid);
                $this->runCurlGetFor($leg->bleg_uuid);
            }
            $cycles++;
        }
        if($this->collectedCriticalLegs() === FALSE){
			if($this->hasTakenTooLongTo('complete')){
            	$this->fatalError('MISSING CUDATEL DATA - 1' . json_encode($this->gets->slam));
            } else{
            	$this->isInProgress('interpreting');    
            }
        }
        return $this;
    }

    protected function collectedCriticalLegs(){
        if(count(array_intersect($this->gets->apull, $this->gets->bpull)) == 2){
            $this->setCriticalLegs();
            if($this->call->data)
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function setCriticalLegs(){
        $long = -INF;
        foreach($this->call->data as $leg){
        	if($leg->duration > $long){
        		$long = $leg->duration;
        		$cuid = $leg->uuid;
        	}
        }
        $this->gets->cleg = $this->call->data[$cuid];
        $this->gets->ileg = $this->call->data[$this->gets->cleg->bleg_uuid];
        return $this;
    }

    protected function runCurlGetFor($uuid){
        if(!in_array($uuid, $this->gets->pulled)){
            $this->gets->pull = $uuid;
            $this->setCudaUrls();
            $this->getCudaData();
        }             
    }

    protected function setCudaUrls(){
        if(!in_array($this->gets->pull, $this->gets->apull)){
            $this->gets->curl->to_pull[] = 'aleg';
            $this->gets->curl->to_pull[] = 'aout';
        }
        if(!in_array($this->gets->pull, $this->gets->bpull)){
            $this->gets->curl->to_pull[] = 'bleg';
            $this->gets->curl->to_pull[] = 'bout';
        }
        foreach($this->gets->curl->to_pull as $leg){
            $url = $this->getCudaUrls($leg);
            $this->gets->curl->$leg = curl_init($url);
            $lu = $leg . '_url';
            $this->gets->curl->$lu = $url;
            curl_setopt($this->gets->curl->$leg, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($this->gets->curl->$leg, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($this->gets->curl->$leg, CURLOPT_COOKIEFILE, '/tmp/bps_session');
        }
        return $this;
    }

    protected function neededLegs($recheck = FALSE){
        if($recheck === FALSE){
            return array('aleg', 'bleg', 'aout', 'bout');
        } else{
            return array('bleg', 'bout');
        }  
    }

    protected function getCudaUrls($f){
        $core = base_url() . 'gui/cdr/cdr?';
        $sess = '&sessionid=' . $this->gets->sess;
        $otbd = '&direction=outbound';
        $auid = $core . '&uuid=' . $this->gets->pull . $sess;
        $buid = $core . '&bleg_uuid=' . $this->gets->pull . $sess;
        $urls = array(
            'aleg' => $auid,
            'bleg' => $buid,
            'aout' => $auid . $otbd,
            'bout' => $buid . $otbd
        );
        return $urls[$f];
    }

    protected function getCudaData(){
        $this->gets->pulled[] = $this->gets->pull;
        foreach($this->gets->curl->to_pull as $leg){
            $mined = $this->cURLPull($leg);
            if(substr($leg, 0, 1) == 'a' && !in_array($this->gets->pull, $this->gets->apull)){
                $this->gets->apull[] = $this->gets->pull;
            } else if(substr($leg, 0, 1) == 'b' && !in_array($this->gets->pull, $this->gets->bpull)){
                $this->gets->bpull[] = $this->gets->pull;
            } else{
            	$this->gets->slam = $this->gets->pull;
            }
            foreach($mined as $gem){
                if(!in_array($gem->bbx_cdr_id, $this->gets->collectedLegs)){
                    $this->gets->collectedLegs[] = $gem->bbx_cdr_id;
                    $this->call->data[$gem->uuid] = $gem;
                    $this->gets->bcount[$gem->bleg_uuid] = isset($this->gets->bcount[$gem->bleg_uuid]) ? $this->gets->bcount[$gem->bleg_uuid]:0;
                    $this->gets->bcount[$gem->bleg_uuid]++;
                }
            }
            if($this->collectedCriticalLegs() === TRUE){
                break;
            }
        }
        return $this;
    }

    protected function cURLPull($leg){
        $pull = json_decode(curl_exec($this->gets->curl->$leg));
        if(isset($pull->error) && $pull->error == 'NOTAUTHORIZED'){
            $pull = json_decode(curl_exec($this->gets->curl->$leg));
        }
        $data = $pull->cdr;
        return $data;
    }
    //END COLLECT INITIAL DATA

    //CHECK INPUT DATA REPAIR IF INCORRECT
    protected function dissectLegs(){
        while($this->needsRepairs() && $this->canBeRepaired()){
            $field = $this->gets->repair['0'];
            $this->locateAndRepair($field);
        }
        if($this->canBeRepaired()){
            $this->call->processed = 1;
            $this->locateCallType();
        }
        return $this;
    }

    protected function needsRepairs(){
        if(isset($this->gets->repair) && count($this->gets->repair) > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function canBeRepaired(){
        if(!isset($this->gets->irep)){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function locateAndRepair($field){
        switch ($field) {
            case 'interpreter_id':
                $this->interpIdRepair();
                break;
            case 'language':
                $this->languageRepair();
                break;
            case 'link_timestamp':
                $this->timeStampRepair('link');
                break;
            case 'submit_timestamp':
                $this->timeStampRepair('submit');
                break;
        }
        if($this->canBeRepaired()){
            unset($this->gets->repair['0']);
            sort($this->gets->repair);
        }

        return $this;
    }

    protected function interpIdRepair(){
        if($this->call->drop == 0){
            $this->setDataFromCID();
            if(isset($this->gets->possible)){
                $this->call->interpreter_id = $this->gets->possible->interpids['0'];
                unset($this->gets->possible->interpids['0']);
                if(count($this->gets->possible->interpids) > 0){
                    $this->flagMessage('CHECK INTID:' . json_encode($this->gets->possible->interpids));
                }
                $this->setInterpIDFromDB($intid);
            } else{   
                    $this->gets->irep = TRUE;
                    $this->fatalError('CANT REPAIR INTID');
            }
        }
        return $this;
    }

    protected function languageRepair(){
        $intid = $this->call->interpreter_id;
        if(isset($intid)){
            $this->setInterpIDFromDB($intid);
            if(!isset($this->gets->interpreter_data)){
                $this->setDataFromCID();
                $posbl = isset($this->get else{
        	$this->_ci->db->select('*');
	        $this->_ci->db->where('phone_1', $numb);
	        $query = $this->_ci->db->get('interpreter_archive');
	        if($query->result()){
	            if($query->num_rows() > 0){
	                foreach($query->result() as $int){
	                    $intid = $int->interpreter_id;
	                    $this->gets->possible->languages[$intid] = $int->language;
	                    $this->gets->possible->interpnms[$intid] = $int->name;
	                    if(!in_array($intid, $this->gets->possible->interpids)){
	                        $this->gets->possible->interpids[] = $intid;
	                    }
	                }
	            }
	        }
        }
        return $this;
    }

    protected function checkCudatelfor($find){
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/extension/list?primary=1&type=router&sortby=bbx_extension_value&&search_string=' . $this->call->interpreter_id . '&sessionid=' . $this->gets->sess;
        $this->gets->cuda_list_pull = curl_init($url);
        curl_setopt($this->gets->cuda_list_pull, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->gets->cuda_list_pull, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($this->gets->cuda_list_pull, CURLOPT_COOKIEFILE, '/tmp/bps_session');
        $this->gets->cuda_list = curl_exec($this->gets->cuda_list_pull);
        $list = json_decode($this->gets->cuda_list);
        foreach($list->list as $ext){
            if($this->call->interpreter_id == mb_substr($ext->show_name, 3, 4)){
                if($find == 'interpnm'){
                    $found_name = mb_substr($ext->show_name, 9);
                } elseif($find == 'language'){
                    $found_lang = mb_substr($ext->show_name, 0, 3);
                }
            }
        }
        if(isset($found_name)){
            return $found_name;
        } elseif(isset($found_lang)){
            return $found_lang;
        } else{
            return FALSE;
        }
    }

    protected function getIntPhones(){
        $this->_ci->db->select('*');
        $query = $this->_ci->db->get('interpreter_archive');
        if($query->result()){
            foreach($query->result() as $int){
                $p1 = $int->phone_1 > 0 ? '1' . $int->phone_1:null;
                if(isset($p1) && mb_strlen($p1, 'utf-8' ) == 11){
                    $all[] = $p1;
                    if(array_key_exists($p1, $byi) && $byi[$p1] != $int){
                        $byi[$p1] = $int;
                    } else{
                        $byi[mb_substr($p1, 1, 10)] = $int;
                    }
                }
            }  
        }
        return array('all' => $all, 'byi' => $byi);
    }
    //INPUT REPAIR FUNCTIONS
    

    protected function setLanguageFromDB($language){
        $this->_ci->db->select('*');
        $this->_ci->db->where('language_code', $language);
        $query = $this->_ci->db->get('languages', 1);
        if($query->result()){
            foreach($query->result() as $lang){
                $this->call->language = $language;
                $lang->set_number = mb_substr($lang->language_set, 2, 1);
                $this->gets->language_data = $lang;
                return $this;
            }
        } else{
            $this->flagMessage('BAD LANG');
        }
    }

    protected function setInterpIDFromDB($intid){
        if($this->setDataCheckBothTablesForInterpreter($intid) === FALSE){
            $name = $this->checkCudatelfor('interpnm');
            if($name === FALSE){
                $this->fatalError('BAD INTID CUDATEL CHECKED');
            } else{
                $this->gets->interpreter_data->name = $name;
                $this->gets->interpreter_data->language = $this->checkCudatelfor('language');
            }
        }
        return $this;
    }

    protected function setDataCheckBothTablesForInterpreter($intid){
        $this->_ci->db->select('*');
        $this->_ci->db->where('interpreter_id', $intid);
        $query = $this->_ci->db->get('interpreter_archive', 1);
        if($query->result()){
            foreach($query->result() as $details){
                $this->call->interpreter_id = $intid;
                $this->gets->interpreter_data = $details;
                $this->gets->interpreter_data->langs[] = $details->language;
                return $this;
            }
        } else{
            $this->_ci->db->select('*');
            $this->_ci->db->where('iid', $intid);
            $query = $this->_ci->db->get('interpreters', 1);
            if($query->result()){
                foreach($query->result() as $details){
                    $this->call->interpreter_id = $intid;
                    $details->language = $details->language_code;
                    $this->gets->interpreter_data = $details;
                    $this->gets->interpreter_data->langs[] = $details->language;
                    return $this;
                }
            } else{
                return FALSE;
            }
        }
    }
    //END INPUT REPAIR FUNCTIONS
        
    //CALL TYPE
    protected function locateCallType(){
        $this->setCallTimes();

        $stamps = array('at' => 'answer_timestamp', 'st' => 'start_timestamp', 'et' => 'end_timestamp');
        foreach($stamps as $label => $stamp){
            if(!in_array(strtotime($this->gets->cleg->$stamp), $times)){
                $times[] = strtotime($this->gets->cleg->$stamp);
            } else{
                $times[] = strtotime($this->gets->cleg->$stamp) + 1;
            }
            $il[$label] = strtotime($this->gets->ileg->$stamp);
        }
        $cl = $this->sortThese($times);
        $this->call->inv_start = date('Y-m-d H:i:s', $cl['connected']);
        $this->call->inv_end = date('Y-m-d H:i:s', $cl['completed']);
        $this->call->secs_on_hold = $cl['connected'] - $cl['beginning'];
        $this->call->inv_duration = $cl['completed'] - $cl['connected'];
        $this->call->tot_duration = $cl['completed'] - $cl['beginning'];
        if($cl['beginning'] > $il['at']){
            $this->flagMessage('CHECK ILEG 1ST');
        }
        if($this->call->drop == 1){
            $this->checkIfReallyDropped();
            $this->call->call_type = 'DROP';
            if($this->call->tot_duration < 120){
                $this->call->call_type = 'ADMIN';
                $this->call->drop = 0;
                $this->call->admin = 1;
            } else{
                $this->call->admin = 0;
            }
            $this->call->inv_start = date('Y-m-d H:i:s', $cl['beginning']);
        } else{
            if($this->call->routed_or_queued == 1){ 
            	$this->call->call_type = 'QUEUE';
            } else if($this->call->routed_or_queued == 2){
            	if($this->call->answer_iid == $this->call->interpreter_id){
	                $this->call->call_type = 'SELF';
	                $this->call->inv_start = $this->call->submit_timestamp;
	                $this->call->secs_on_hold = strtotime($this->call->submit_timestamp) - $cl['beginning'];
	                $this->call->inv_duration = $cl['completed'] - strtotime($this->call->submit_timestamp);
	            } else if(count($this->call->data) >= 3){
	                $this->call->call_type = 'ROUTE';
	            } else{
	            	if($this->collectedCriticalLegs() === FALSE){
						if($this->hasTakenTooLongTo('complete')){
			            	$this->fatalError('MISSING CUDATEL DATAS');
			            } else{
			            	$this->isInProgress('interpreting');    
			            }
			        } else{
			        	$this->call->call_type = 'CONFRNC';
			        }	                
	            }
	        } else{
	        	$this->isInProgress('processing');
	        }
        }
        return $this;
    }

    protected function setCallTimes(){
    	$end = strtotime($this->gets->cleg->end_timestamp);
    	$times[] = strtotime($this->gets->cleg->answer_timestamp);
    	$times[] = in_array(strtotime($this->gets->cleg->start_timestamp), $times) ? strtotime($this->gets->cleg->start_timestamp) + 1:strtotime($this->gets->cleg->start_timestamp);
    	sort($times);
    	$beg = $times['0'];
    	$con = $times['1'];
    	$this->call->inv_start = date('Y-m-d H:i:s', $con);
        $this->call->inv_end = date('Y-m-d H:i:s', $end);
        $this->call->secs_on_hold = $con - $beg;
        $this->call->inv_duration = $end - $end;
        $this->call->tot_duration = $end - $beg;
    }

    protected function sortThese($times){
        foreach($times as $unix){
            $sorted[] = $unix;
        }
        sort($sorted);
        $ordered['beginning'] = $sorted['0'];
        $ordered['connected'] = $sorted['1'];
        $ordered['completed'] = $sorted['2'];
        return $ordered;
    }

    protected function callWasInQueue(){
        $names = array($this->call->cleg->caller_id_name, $this->call->cleg->destination_name, $this->call->ileg->caller_id_name, $this->call->ileg->destination_name);
        foreach($names as $name){
            if(mb_substr($name, mb_strpos($name, '['), 14) == '[SPANISH 5120]'){
                return TRUE;
            }
        }
        return FALSE;
    }
    //END CALL TYPE
    //END CHECK INPUT DATA REPAIR IF INCORRECT

    //COLLECT ADDITIONAL DATA REPAIR IF INCORRECT
    protected function correctLegs(){
        //CLIENT
        $this->setClientFromDB();
        $this->setSpecial();
        $this->setRecordingName();
        $this->setMinutes();
        $this->setInvPhone();
        $this->setRateData();
        $this->setInterpName();
        $this->call->client_id = $this->gets->client_data->client_id;
        $this->call->inv_detail = $this->gets->client_data->Invoice_Detail;
        $this->call->answer_timestamp = $this->gets->cleg->answer_timestamp;
        $this->call->start_timestamp = $this->gets->cleg->start_timestamp;
        $this->call->end_timestamp = $this->gets->cleg->end_timestamp;
        $this->call->caller_id_name = $this->gets->cleg->caller_id_name;
        $this->call->caller_id_number = $this->gets->cleg->caller_id_number;
        $this->call->destination_name = $this->gets->cleg->destination_name;
        $this->call->destination_number = $this->gets->cleg->destination_number;
        $this->call->bleg_uuid = $this->gets->cleg->bleg_uuid;
        $this->setInvCode();
        return $this;
    }

    protected function checkIfReallyDropped(){
        if(count($this->gets->cleg->hold_events) > 0){
            $last_hold_ends = $this->gets->cleg->hold_events['0']['1'];
            $ends_array = range((round($last_hold_ends / 1000)-2), (round($last_hold_ends / 1000)+2));
            if(in_array(strtotime($this->gets->cleg->end_timestamp), $ends_array)){
                $this->call->drop = 1;
            } else if(count($this->call->data) > 2){
                if($this->call->interpreter_id |= $this->call->answered_iid){
                    $this->call->drop = 1;
                } else{
                    $this->call->drop = 0;
                    $this->flagMessage('MARKED AS NOT DROPPED');
                }
            } else{
                $this->call->drop = 0;
                $this->flagMessage('MARKED AS NOT DROPPED');
            }
        }
    }   

    protected function setClientFromDB(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('access_code', $this->call->access_code);
        $query = $this->_ci->db->get('client_data', 1);
        if($query->result()){
            foreach($query->result() as $client){
                $this->call->client_id = $client->client_id;
                $this->call->inv_detail = $client->Invoice_Detail;
                $this->gets->client_data = $client;
            }
        } else{
            $this->fatalError('BAD ACCESS CODE');
        }
        return $this;
    }

    protected function setRateData(){
        if(!isset($this->gets->language_data) || !isset($this->gets->language_data->set_number)){
            $this->setLanguageFromDB($this->call->language);
        }
        $time = strtotime($this->call->inv_start);
        $setn = $this->gets->language_data->set_number;
        $dayt = in_array(date('H', $time), range('8', '20')) ? 1:0;
        $wknd = in_array(date('N', $time), range('1', '5')) ? 1:0;
        $ahrs = ($ah + $we) == 0 ? 'S':'A';
        $calt = $this->call->callout == 1 ? 'CO':'G';
        $lset = $ahrs == 'S' ? 'OTPLS' . $setn:'OTPLS' . $setn . 'A';
        $this->call->rate_code = 'N' . $ahrs . $calt . 'L' . $setn;
        if($this->gets->client_data->$lset < 0.49 && intval($setn) > 0 && intval($setn) < 5){
            $this->fatalError('BAD RATE');
        } else{
            $this->call->rate = $this->gets->client_data->$lset;
        }
        return $this;
    }

    protected function setInterpName(){
        $this->setInterpIDFromDB($this->call->interpreter_id);
        if(isset($this->gets->interpreter_data)){
            $this->call->interpreter_name = $this->gets->interpreter_data->name;
        }        
        return $this;
    }

    protected function setSpecial(){
        $client = $this->gets->client_data;
        $this->call->inv_special = isset($this->call->inv_special_b) ? $this->call->inv_special_a . '~~' .  $this->call->inv_special_b:$this->call->inv_special_a;
        unset($this->call->inv_special_a);
        unset($this->call->inv_special_b);
        if($client->otp_sp_in == 1){
            if($client->sp_type == 6 || $client->sp_type == 1){
                if(mb_strlen($this->call->inv_special) <= 2){
                    $this->flagMessage('SHORT SPECIAL');
                }
            }
        }
        return $this;
    }

    protected function setRecordingName(){
        if(mb_strlen($this->gets->cleg->record_file_name, 'utf-8' ) > 0){
            $this->call->record_file_name = $this->gets->cleg->record_file_name;
        } elseif(mb_strlen($this->gets->ileg->record_file_name, 'utf-8') > 0){
            $this->call->record_file_name = $this->gets->ileg->record_file_name;
        } else{
            $this->call->record_file_name = 'Unknown Recording Name';
        }
        return $this;
    }

    protected function setMinutes(){
        $this->call->inv_minutes = ceil($this->call->inv_duration/60);
        return $this;
    }   

    protected function setInvPhone(){
        foreach($this->call->data as $leg){
            $this->setValidPhones($leg->caller_id_number);
            $this->setValidPhones($leg->destination_number);
        }
        $int_db = $this->getIntPhones();
        foreach($this->gets->valid_phones as $number){
            if(!in_array($number, $int_db['all']) && !in_array($number, $possible)){
                $possible[] = $number;
            }
        }
        if(count($possible) > 0){
            if(count($this->gets->likely_phones) > 0){
                $this->gets->likely_phones = array_intersect($possible, $this->gets->likely_phones);
                $this->gets->unlikely_phones = array_diff($possible, $this->gets->likely_phones);
                $this->call->inv_phone = $this->gets->likely_phones['0'];
                unset($this->gets->likely_phones['0']);
                if(count($this->gets->likely_phones) > 0){
                    $this->flagMessage('PHONE POSS:' . json_encode($this->gets->likely_phones));
                }
            } else{
                $this->call->inv_phone = $possible['0'];
                if(count($possible) > 1){
                    $this->flagMessage('PHONE POSS:' . json_encode($possible));
                }
            }
            
        } else{
            $this->call->inv_phone = 'ID Blocked';
            $this->flagMessage('ID BLOCK');
        }
        return $this;
    }

    protected function setValidPhones($number){
        $cleaned = str_replace('+', '', $number);
        $g = $this->gets;
        if(mb_strlen($cleaned, 'utf-8' ) == 11 && !in_array($cleaned, $g->valid_phones)){
            $g->valid_phones[] = $cleaned;//ADD TO
            if($number == $g->cleg->caller_id_number || $number == $g->cleg->destination_number && !in_array($cleaned, $g->likely_phones)){
                $g->likely_phones[] = $cleaned;//ADD TO
            }
            if($number == $g->ileg->caller_id_number || $number == $g->ileg->destination_number && !in_array($cleaned, $g->likely_phones)){
                $g->likely_phones[] = $cleaned;//ADD TO
            }
        }
        return $this;
    }

    protected function setInvCode(){
        if(!isset($this->call->inv_code) || mb_strlen($this->call->inv_code, 'utf-8') != 6){
            $this->call->inv_code = intval(date('Ym', strtotime($this->call->inv_start)));
        }
    }
    //END COLLECT ADDITIONAL DATA REPAIR IF INCORRECT
    
    //FINALIZE AND REMOVE EXTRA UNNEEDED DATA
    protected function compactLegs(){
        /*$collect = array(
            'cudalegs' => isset($this->call->data) ? $this->call->data:array(),
            'involves' => isset($this->gets->touched) ? $this->gets->touched:array(),
            'pri_phns' => isset($this->gets->likely_phones) ? $this->gets->likely_phones:array(),
            'sec_phns' => isset($this->gets->unlikely_phones) ? $this->gets->unlikely_phones:array()
        );
        $this->call->collected = json_encode($collect);*/
        $this->call->invoiced = 0;
        $this->call->fulfilled = 0;
        $this->call->deleted = 0;
        unset($this->call->data);
        unset($this->call->inv_special_a);
        unset($this->call->inv_special_b);
        unset($this->call->processed);
        unset($this->call->completed);       
        //$this->overlapsCheck();
        $this->callerIdCheck();
        return $this;
    }
    
    protected function overlapsCheck(){
        $call_bef = $this->getCall('before');
        $call_aft = $this->getCall('after');
        if($call_bef !== FALSE && $call_aft !== FALSE){
            $ends = strtotime($this->call->inv_end);
            $after = strtotime($call_aft->link_timestamp);
            $starts = strtotime($this->call->inv_start);
            $before = strtotime($call_bef->inv_end);
            $ths_rep = mb_substr($this->call->caller_name, 0) . mb_substr($this->call->caller_name, -1);
            $bef_rep = mb_substr($call_bef->caller_name, 0) . mb_substr($call_bef->caller_name, -1);
            $aft_rep = mb_substr($call_aft->caller_name, 0) . mb_substr($call_aft->caller_name, -1);
            if($this->timeIsInvalid($after) === FALSE){
                if($starts < $before && $ths_rep == $bef_rep){
                    $this->fatalError($call_bef->id . 'OVERLAPS B');
                } elseif($ends > $after && $ths_rep == $aft_rep){
                    $this->fatalError($call_aft->id . 'OVERLAPS A');
                }
            }
        }
        return $this;
    }

    protected function getCall($before_or_after){
        $cmpr = $before_or_after == 'before' ? ' <':' >';
        $sort = $before_or_after == 'before' ? 'desc':'asc';
        $tabl = $before_or_after == 'before' ? 'call_records':'call_links';
        $this->_ci->db->select('*');
        $this->_ci->db->where('access_code', $this->call->access_code);
        $this->_ci->db->where('id' . $cmpr, $this->call->id);
        $this->_ci->db->order_by('id', $sort);
        $query = $this->_ci->db->get($tabl);
        if($query->result()){
            return $query->row();
        } else{
            return FALSE;
        }
    }

    protected function callerIdCheck(){
        if(ctype_digit($this->call->inv_phone)){
            $area_codes = $this->getClientAreaCodes();
            $area_code = mb_substr($this->call->inv_phone, 1, 3);
            if(!in_array($area_code, $area_codes)){
                foreach($this->gets->valid_phones as $phone){
                    $numb = mb_substr($phone, 1, 3);
                    if(in_array($numb, $area_codes)){
                        $this->call->inv_phone = $phone;
                    }
                }
                if($area_code == mb_substr($this->call->inv_phone, 1, 3)){
                    $this->flagMessage('AREA CODE');
                }
            }
        }
        return $this;
    }

    protected function getClientAreaCodes(){
        $bstate = mb_strlen($this->gets->client_data->Bill_State, 'utf-8' ) == 2 ? $this->gets->client_data->Bill_State:NULL;
        $dstate = mb_strlen($this->gets->client_data->Div_State, 'utf-8' ) == 2 ? $this->gets->client_data->Div_State:NULL;
        if(!isset($bstate, $dstate)){
            $this->flagMessage('NO AREA CODE');
        } else{
            $state = isset($dstate) ? $dstate:$bstate;
            $this->_ci->db->select('*');
            $this->_ci->db->where('state', $state);
            $query = $this->_ci->db->get('us_area_codes');
            if($query->result()){
                foreach($query->result() as $row){
                    $codes[] = intval($row->code);
                }
            } else{
                $codes = array();
                $this->fatalError('BAD STATE');
            }
        }
        return $codes;   
    }
    //END FINALIZE AND REMOVE EXTRA UNNEEDED DATA

    //SPECIAL CLIENT FUNCTIONS
    protected function cameFromTNClient(){
        $check_array = array(
            '600595','900547',
            '700924','900548',
            '900501','900551',
            '900504','900554',
            '900518','900556',
            '900525','900558',
            '900527','900560',
            '900533','900590',
            '900536','900592',
            '900537','900615',
            '900538','901233',
            '900540','901387',
            '900542','901425',
            '900544','901430',
            '900545','901432'
        );
        $check_array = array_merge(range('900508', '900515'), $check_array);
        $check_array = array_merge(range('9005321', '9005328'), $check_array);
        $check_array = array_merge(array('9005341', '9005342'), $check_array);
        $check_array = array_merge(range('9005351', '9005354'), $check_array);
        $check_array = array_merge(range('9005391', '9005397'), $check_array);
        $check_array = array_merge(range('9005481', '9005485'), $check_array);
        $check_array = array_merge(range('9005061','9005069'), $check_array);
        $check_array = array_merge(range('90050610','90050619'), $check_array);
        return in_array($this->call->client_id, $check_array);
    }
    //END SPECIAL CLIENT FUNCTIONS

    protected function cleanDataForDB($object){
    	foreach($object as $field => $value){
    		$cleanedObject[$field] = urldecode($value);
    	}
    	return (object) $cleanedObject;
    }

    //DATABASE FUNCTIONS
    protected function getNextUuid(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('completed', 0);
        $this->_ci->db->where('error !=', 1);
        $q = $this->_ci->db->get('call_links', 1);
        if($q->result()){
            foreach($q->result() as $call){
                return $call->uuid;
            }
        } else{
            die(json_encode(array('ISSUE' => 'NO MORE UUIDS')));
        }
    }

    protected function getNextColdUuid(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('completed', 0);
        $this->_ci->db->where('deleted', 0);
        $this->_ci->db->where('error', 0);
        $this->_ci->db->where('error !=', 2);
        $this->_ci->db->where('link_timestamp >', '2014-12-01');
        $this->_ci->db->where('link_timestamp <', '2015-01-01');
        $q = $this->_ci->db->get('call_links', 1);
        if($q->result()){
            foreach($q->result() as $call){
                return $call->uuid;
            }
        } else{
            die(json_encode(array('ISSUE' => 'NO MORE UUIDS')));
        }
    }

    protected function getNextBadUuid(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('start_timestamp IS NULL');
        $q = $this->_ci->db->get('call_records', 1);
        if($q->result()){
            foreach($q->result() as $call){
                return $call->uuid;
            }
        } else{
            die(json_encode(array('ISSUE' => 'NO MORE UUIDS')));
        }
    }

    protected function updateLinkError($m){
        $this->gets->original->error = 1;
        $this->gets->original->error_message = $m;      
        if($this->gets->original->inv_code != $this->call->inv_code){
            $this->gets->original->inv_code = $this->call->inv_code;
        }
        $this->gets->original = $this->cleanDataForDB($this->gets->original);
        $this->_ci->db->where('uuid', $this->gets->uuid);
        $this->_ci->db->update('call_links', $this->gets->original);
        if($this->_ci->db->affected_rows() > 0){
            $this->call->returned = 'fatal';
        }
        return $this;       
    }

    protected function updateInProgress($m){
        $this->gets->original->error = 2;
        $this->gets->original->error_message = $m;
        if($this->gets->original->inv_code != $this->call->inv_code){
            $this->gets->original->inv_code = $this->call->inv_code;
        }
        $this->gets->original = $this->cleanDataForDB($this->gets->original);
        $this->_ci->db->where('uuid', $this->gets->original->uuid);
        $this->_ci->db->update('call_links', $this->gets->original);
        return $this;       
    }

    protected function injectCallToDB(){
        $this->gets->original = $this->cleanDataForDB($this->gets->original);
		$this->call = $this->cleanDataForDB($this->call);
        $this->_ci->db->trans_start();
        $this->injectArchive($this->gets->original);
        $this->injectCallRow($this->call);
        $this->updateOriginl($this->gets->uuid);
        $this->_ci->db->trans_complete();
        if($this->_ci->db->trans_status() === FALSE){
            $this->fatalError('DB ERROR');
        } else{
        	if($this->call->error == 0){
    			$this->call->returned = 'completed';
        	} else{
        		$this->call->returned = 'flag';
        	}
            
        }
        return $this;
    }

    protected function updateOriginl($uuid){
        $update = array('completed' => 1, 'deleted' => 1, 'error' => 0, 'error_message' => null);
        if($this->gets->original->inv_code != $this->call->inv_code){
            $update['inv_code'] = $this->call->inv_code;
        }
        $this->_ci->db->where('uuid', $this->gets->uuid);
        $this->_ci->db->update('call_links', $update);
        if($this->_ci->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function injectCallRow(){
        if(isset($this->call->error) && $this->call->error == 2){
            return FALSE;
        } else{
            $this->_ci->db->where('id', $this->call->id);
            $q = $this->_ci->db->get('call_records');
            if($q->num_rows() > 0){
                return $this->updateCallRow();
            } else{
                return $this->insertCallRow();
            }
        }
    }

    protected function insertCallRow(){
        $this->_ci->db->insert('call_records', $this->call);
        if($this->_ci->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function updateCallRow(){
        $this->_ci->db->where('id', $this->call->id);
        $this->_ci->db->update('call_records', $this->call);
        if($this->_ci->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function injectArchive(){
        $this->gets->original->processed = 1;
        unset($this->gets->original->error);
        unset($this->gets->original->error_message);
        $this->_ci->db->where('id', $this->gets->original->id);
        $query = $this->_ci->db->get('verified_call_archive');
        if($query->num_rows() > 0){
            return $this->updateCallArchive();
        } else{
            return $this->insertCallArchive();
        }
    }

    protected function insertCallArchive(){
        $this->_ci->db->insert('verified_call_archive', $this->gets->original);
        if($this->_ci->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    protected function updateCallArchive(){
        $this->_ci->db->where('id', $this->gets->original->id);
        $this->_ci->db->update('verified_call_archive', $this->gets->original);
        if($this->_ci->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }
    //END DATABASE FUNCTIONS
}
?>