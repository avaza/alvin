<?php
class Nendtime_model extends CI_Model{
    
    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library('call');
    }


    function time_me(){
        $now = new DateTime(date('Y-m-d H:i:s', strtotime('now')));
        $fft_ago = new DateTime(date('Y-m-d H:i:s', strtotime('-15 minutes')));
        $times = array(
            'Last January 1st' => strtotime('January 1, 2013'),
            'This January 1st' => strtotime('January 1, 2014'),
            '1st of Last Month' => strtotime('the first of last month'),
            '1st of This Month' => strtotime('the first of this month'),
            '1st of April Last Year' => strtotime('April 1, last year'),
            'Yesterday' => strtotime('yesterday'),
            'Today' => strtotime('today'),
            'Now' => strtotime('now'),
            'Happy Hour' => strtotime('happy hour'),
            'Messed up Date' => new DateTime('0000-00-00 00:00:00'),
            'Date with now -15' => new DateTime(date('Y-m-d H:i:s', strtotime('now - 15 minutes'))),
            'Date with now' => new DateTime(date('Y-m-d H:i:s', strtotime('now'))),
            'difference' => $fft_ago->diff($now)
        );
        return $times;
    }



    function morphate($calls){
        foreach($calls as $call){
            $call->access_code = $call->access_code > 0 ? $call->access_code:0;
            $call->language = strlen($call->language) == 3 ? $call->language:NULL;
            $call->intid = strlen($call->intid) == 4 ? $call->intid:0;
            $call->specialf = strlen($call->specialf) > 0 ? $call->specialf:NULL;
            $call->callout = strlen($call->callout) > 0 ? $call->callout:0;
            $call->co_num = strlen($call->co_num) == 10 ? $call->co_num:0;
            $call->drop = strlen($call->drop) > 0 ? $call->drop:0;
            $call->admin = strlen($call->admin) > 0 ? $call->admin:0;
            $call->connected_by = strlen($call->connected_by) == 4 ? $call->connected_by:0;
            $call->r_ext = strlen($call->r_ext) == 4 ? $call->r_ext:0;
            $call->r_or_i = strlen($call->r_or_i) > 0 ? $call->r_or_i:0;
            $call->submitted = strlen($call->submitted) > 0 ? $call->submitted:0;
            $call->link_timestamp != '0000-00-00 00:00:00' ? $call->link_timestamp:$call->answer_timestamp;
            $new_format = array(
                'uuid' => $call->uuid,
                'access_code' => $call->access_code,
                'caller_name' => $call->rep_name,
                'language' => $call->language,
                'interpreter_id' => $call->intid,
                'inv_special' => $call->specialf,
                'callout' => $call->callout,
                'callout_number' => $call->co_num,
                'drop' => $call->drop,
                'admin' => $call->admin,
                'answered_iid' => $call->connected_by,
                'answered_ext' => $call->r_ext,
                'routed_or_queued' => $call->r_or_i,
                'submitted' => $call->submitted,
                'click_timestamp' => $call->start_time,
                'processed' => 0,
                'completed' => 0,
                'invoiced' => 0
                );
            $insert[$call->uuid] = $this->insertMorphated($new_format);
        }
        foreach($insert as $index => $done){
            if(!$done){
                return 'ISSUE WITH ' . $index;
            }
        }
        return $insert;
    }
    
    function insertMorphated($call){
        $this->db->insert('call_links', $call);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }
    
    function fix_callouts(){
        $fixed = 0;
        $callouts = $this->get_callouts();
        foreach($callouts as $uuid){
            $number = $this->get_correct_number($uuid);
            $correct[$uuid] = $number;
        }
        foreach($correct as $uuid => $num){
            $repair = $this->updateCallout($uuid, $num);
            if(!$repair){
                return $uuid;
            }
            $fixed++;
        }
        return $fixed;        
    }

    function get_callouts(){
        $this->db->select('*');
        $this->db->where('callout_number > 0');
        $query = $this->db->get('call_links');
        if($query->result()){
            foreach($query->result() as $call_record){
                $need_fixing[] = $call_record->uuid;
            }
            return $need_fixing;
        } else{
            return 'NO CALLOUTS';
        }
    }

    function get_correct_number($uuid){
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('call_records_t');
        if($query->result()){
            foreach($query->result() as $call_record){
                return intval($call_record->co_num);
            }
        }
        return 'NO NUMBER';
    }

    function updateCallout($uuid, $num){
        $data = array('callout_number' => $num);
        $this->db->where('uuid', $uuid);
        $this->db->update('call_links', $data); 
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return 'NO UPDATE';
        }
    }

    function doesntExist($uuid){
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $q = $this->db->get('call_links');
        if($q->result()){
            return FALSE;
        } else{
            return TRUE;
        }
    }

    //PUBLIC RUN FUNCTIONS
    function viewCall($u, $s){
        $c = new Call($u, $s, TRUE);
        return $c;
        $c = $this->mainCallHandler($c);
        return $c;
    }

    function processCall($u, $s){
        $h = array('uuid' => $u, 'session' => $s, 'view' => FALSE);
        $c = $this->mainCallHandler($h);
        return $c;
    }
    //END PUBLIC RUN FUNCTIONS

    //PRELIMINARY AUTH AND READY FUNCTIONS
    function getCudatelSession(){
        $user = array('u' => '5041', 'p' => '1405');
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/login/login?__auth_user=' . $user['u'] . '&__auth_pass=' . $user['p'];
        $s = $this->curlDataFrom($url, TRUE);
        preg_match_all('~bps_session=(.*?);~s', $s, $m);
        return $m['1']['0'];
        
    }

    function seperateSessionId($s){
        $session = explode(";", $beginning['1']);
        if(isset($session['0']) && strlen($session['0']) == 40){
            return $session['0'];
        } else{
            return FALSE;
        }
    }

    function curlDataFrom($url, $set = FALSE){//$set = remember session cookie
        $cr = curl_init($url);
        curl_setopt($cr, CURLOPT_RETURNTRANSFER, TRUE);
        if($set){
            curl_setopt($cr, CURLOPT_HEADER, TRUE);
            curl_setopt($cr, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cr, CURLOPT_COOKIEJAR, '/tmp/bps_session');
        } else{
            curl_setopt($cr, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($cr, CURLOPT_COOKIEFILE, '/tmp/bps_session');
        }     
        $curl_request_data = curl_exec($cr);
        curl_close($cr);
        return $curl_request_data;
    }
    //END PRELIMINARY AUTH AND READY FUNCTIONS

    //MAIN HANDLER FUNCTIONS
    function mainCallHandler($h){
        $c = $this->collectCallData($h);
        return $c;
        //$c = $this->compileCallData($c);

        //determine what step the call is in by key elements of data and send to the appropriate function or return to the view when errors occur
        //collectCallData()
        //stepCallForward($c)
    }
    //END MAIN HANDLER FUNCTIONS

    //GENERIC DB FUNCTIONS
    function viewDB($s, $c, $t){
        $this->db->select($s);
        foreach($c as $cn => $cv){
            $this->db->where($cn, $cv);
        }
        $q = $this->db->get($t);
        if($q->result()){
            return $q->result();
        } else{
            return FALSE;
        }
    }
    //END GENERIC DB FUNCTIONS


    //ISSUES AND ERROR HANDLING
    function checkCallFor($c){
        /*POSSIBLE ISSUES
        --TIMES SET INCORRECTLY
        --CALCULATIONS IMPOSSIBLE
        --CURRENTLY ERRORS SET ON CALL
        --OVERLAPPING CALLS
        --RETURN THIS DATA TO THE VIEW IMMEDIATELY
        --
        --THIS FUNCTION SIMPLY SENDS THE CALL TO THE PROPER FUNCTION TO CALCULATE AND DETERMINE IF THE DATA IS CORECT (SWITCH)*/


        //returns to stepCallForward
    }
    //END ISSUES AND ERROR HANDLING

    //CALL PROCESSING FUNCTIONS
    function collectCallData($h){//KEY FUNCTION COLLECT
        //this is the call handler that handles collection of all preliminary data before procesing the call
        $c = $this->obtainSubmittedData($h);
        return $c;
        //$c = $this->obtainCudatelData($c);
        //return $c;
        //returns to mainCallHandler
    }

    function obtainSubmittedData($h){
        $p = $this->viewDB('*', array('uuid' => $h['0']), 'call_records_t');
        if(!$p){
            die('CALL NOT FOUND IN DATABASE');///BETTER ERROR
        }
        $c = $p['0'];
        $c->s = $h['1'];
        $c->v = $h['2'];
        return $c;
        //reviewSubmittedData($c);
        //repairSubmittedData($c);
        //returns to collectCallData
    }
}

class SHIT extends CI_Model{

    function __construct($u, $s, $v){
        parent::__construct();
        $this->load->database();
        $this->uuid = $u;
        $this->session = $s;
        $this->view = $v;
        $this->getDbData();
        $this->cURLSetSess($u);
        $this->cURLSetUrls($u, $s);
        $this->cURLGetUrls();
        return $this;
    }

    function getDbData(){
        $this->db->select('*');
        $this->db->where('uuid', $this->uuid);
        $q = $this->db->get('call_records_t');
        if($q->result()){
            foreach($q->row() as $c => $v){
                $this->$c = $v;
            }
        } else{
            $this->error_message = 'CALL NOT FOUND IN DATABASE';
        }
    }

    function cURLSetSess($u){
        $this->curl = array();
        $this->curl['sess'] = curl_init('http://' . $_SERVER['HTTP_HOST'] . '/gui/login/login?__auth_user=5041&__auth_pass=1405');
        curl_setopt($this->curl_sess, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($this->curl_sess, CURLOPT_HEADER, TRUE);
        curl_setopt($this->curl_sess, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($this->curl_sess, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($this->curl_sess, CURLOPT_COOKIEJAR, '/tmp/bps_session');
    }

    function cURLSetUrls($u, $s){
        $core = 'http://' . $_SERVER['HTTP_HOST'] . '/gui/cdr/cdr?&sessionid=' . $s;
        $otbd = $core . '&direction=outbound';
        $uuid = '&uuid=' . $u;
        $buid = '$bleg_uuid=' . $u;
        $builder = curl_init();
        curl_setopt($builder, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($builder, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($builder, CURLOPT_COOKIEFILE, '/tmp/bps_session');
        curl_setopt($builder, CURLOPT_RETURNTRANSFER, TRUE);
        $this->curl['aleg'] = curl_copy_handle($builder);
        $this->curl['bleg'] = curl_copy_handle($builder);
        $this->curl['outa'] = curl_copy_handle($builder);
        $this->curl['outb'] = curl_copy_handle($builder);
        curl_setopt($this->curl['aleg'], CURLOPT_URL, $core . $uuid);
        curl_setopt($this->curl['bleg'], CURLOPT_URL, $core . $buid);
        curl_setopt($this->curl['outa'], CURLOPT_URL, $otbd . $uuid);
        curl_setopt($this->curl['outb'], CURLOPT_URL, $otbd . $buid);
        $this->curl_legs = array('aleg', 'bleg', 'outa', 'outb');
    }

    function cURLGetSess(){
        $s = curl_exec($this->curl_sess);
        curl_close($this->curl_sess);
        preg_match_all('~bps_session=(.*?);~s', $s, $m);
        $this->sess = $m['1']['0'];
    }

    function cURLGetUrls(){
        foreach($this->curl as $leg => $curler){
            if($leg != 'sess'){
                $this->$leg = curl_exec($curler);
                curl_close($curler);
            }            
        }
    }

}

/*
    function reviewSubmittedData($c){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
        //returns to obtainSubmittedData
    }

    function repairSubmittedData($c){
        //repair any pieces of data that can be repaired and return to review function with status
        //returns to reviewSubmittedData
    }

    function obtainCudatelData($c){
        //collect all information needed from the Cudatel for processing the call
        //pull call data from cudatel here
        reviewCudatelData($c)
        repairCudatelData($c)
        //returns to collectCallData
    }

    function reviewCudatelData($c){
        //check that all pieces of call that should be within the data from the cudatel are available and that the call is completed
        //returns to obtainCudatelData
    }

    function repairCudatelData($c){
        //repair any pieces of data that can be repaired and return to review function with status
        //returns to reviewCudatelData
    }

    function compileCallData($c){//KEY FUNCTION COMPILE
        //proceed to place all call details into the scope of the call object in specified contaiers to ensure the call is ready for processing
        obtainClientData();
        obtainStaffData();
        obtainTimestampData();
        //after this function the call will be processed through each function required to complete the call
        //returns to mainCallHandler
    }

    function obtainClientData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
                reviewSubmittedData($c);
        repairSubmittedData($c);
        //returns to compileCallData
    }

    function reviewClientData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
        //returns to obtainClientData
    }

    function repairClientData(){
        //repair any pieces of data that can be repaired and return to review function with status
        //returns to reviewClientData
    }

    function obtainStaffData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
                reviewSubmittedData($c);
        repairSubmittedData($c);
        //returns to compileCallData
    }

    function reviewStaffData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
        //returns to obtainStaffData
    }

    function repairStaffData(){
        //repair any pieces of data that can be repaired and return to review function with status
        //returns to reviewStaffData
    }

    function obtainTimestampData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
        reviewSubmittedData($c);
        repairSubmittedData($c);
        //returns to compileCallData
    }

    function reviewTimestampData(){
        //check that all pieces of call that should have been submitted are submitted and that the call is not taking too long
        //returns to obtainTimestampData
    }

    function repairTimestampData(){
        //repair any pieces of data that can be repaired and return to review function with status
        //returns to reviewTimestampData
    }
    //END CALL PROCESSING FUNCTIONS
 */

?>

