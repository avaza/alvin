<?php
class Call_data extends CI_Model{
    
    function __construct(){
        parent::__construct();
        $this->load->model('auth_model');
    }

    function end_call($uuid){
        $update = array('ended' => 1);
        $this->db->where('uuid', $uuid);
        $this->db->update('call_links', $update);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }


    function update_calls_viewer_data($page){
        $creds = $this->auth_model->getUserCredentials();
        $me = $creds->intid;
        $this->db->select('*');
        if($page == 'interpreter'){
            $this->db->where('interpreter_id', $me);
        }
        if($page == 'router'){
            $this->db->where('answer_iid', $me);
        }
        $this->db->where('start_timestamp >=', date("Y-m-d 00:00:00"));
        $query = $this->db->get('call_records');
        if($query->result()){
            $aaData = array();
            foreach($query->result() as $call){
                if($call->drop == 0){
                    $drop_data = 'NO';
                } else{
                    $drop_data = 'YES';
                }
                if($call->callout_number == 0){
                    $co_data = $this->callout_form($call->id);
                } else{
                    $co_data = $call->callout_number;
                }
                $inc_data = $this->incident_form($call->id);
                $aaData[] = array(
                    $call->answer_iid,
                    '05-' .  ($call->id + 11178),
                    $call->access_code,
                    $call->start_timestamp,
                    $call->client_id,
                    $call->inv_phone,
                    $call->caller_name,
                    $call->language,
                    $call->interpreter_id,
                    $call->interpreter_name,
                    $call->inv_start,
                    $call->inv_end,
                    $drop_data,
                    $co_data,
                    $inc_data
                );
            }
            return array('aaData' => $aaData);
        } else{
            return array('sEcho' => 1, 'iTotalRecords' => '0', 'iTotalDisplayRecords' => '0', 'aaData' => array());
        }
    }

    function save_rep_name($caller_name, $uuid){   
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $add_caller_name = array('caller_name' => $caller_name);
        $update = $this->db->update('call_links', $add_caller_name);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function save_specialf($inv_special_a, $uuid){   
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $add_inv_special_a = array('inv_special_a' => $inv_special_a);
        $update = $this->db->update('call_links', $add_inv_special_a);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function get_all_jol_data_se($start, $end){
        $this->db->select('*');
        $this->db->where('start_timestamp >=', $start);
        $this->db->where('start_timestamp <', $end);
        $this->db->where('checked <', 3);
        $query = $this->db->get('call_records');
        if($query->result()){
            return $query->result();
        } else{
            return false;
        }
    }

    function add_co_num_to_db($number, $id){
        $callout_number_add = array(
            'callout_number' => $number);
        $this->db->where('id', $id);
        $this->db->update('call_links', $callout_number_add);
        if ($this->db->affected_rows() > 0){
            return true;
        } else{
            return false;
        }
    }

    function add_co_num_by_uuid($number, $uuid){
        $callout_number_add = array('callout_number' => $number);
        $this->db->where('uuid', $uuid);
        $this->db->update('call_links', $callout_number_add);
        if ($this->db->affected_rows() > 0){
            return true;
        } else{
            return false;
        }
    }
//fix js to send r_or_i
    function link_uuid($uuid, $r){
        $creds = $this->auth_model->getUserCredentials();
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('call_links');
        if($query->num_rows() > 0){
            return TRUE;
        } else{
            $add_call_data = array(
                'uuid' => $uuid,
                'answer_iid' => $creds->intid,
                'answer_ext' => $creds->ext,
                'routed_or_queued' => $r
                );
            $insert = $this->db->insert('call_links', $add_call_data);
            if($this->db->affected_rows() > 0){
                return TRUE;
            } else{
                return FALSE;
            }
        }
    }

    function ignore_uuid($uuid){
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('call_links');
        if($query->num_rows() > 0){
            $delete_data = array('deleted' => 1);
            $this->db->where('uuid', $uuid);
            $this->db->update('call_links', $delete_data);
            if($this->db->affected_rows() > 0){
                return TRUE;
            } else{
                return FALSE;
            }
        } else{
            return TRUE; 
        }
    }

    function check_if_uuid_exists($uuid){
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('call_links', 1);
        $data = FALSE;
        if($query->num_rows() > 0){
            $call = $query->row();
            if($call->processed == 0){
                $data = array(
                    'relink' => 0,
                    'access_code' => isset($call->access_code) ? trim($call->access_code) : null,
                    'rep_name' => isset($call->caller_name) ? trim($call->caller_name) : null,
                    'language' => isset($call->language) ? trim($call->language) : null,
                    'specialf' => isset($call->inv_special_a) ? trim($call->inv_special_a) : null
                );
            } else{
                $data = array(
                    'relink' => 1,
                    'access_code' => isset($call->access_code) ? trim($call->access_code) : null,
                    'rep_name' => isset($call->caller_name) ? trim($call->caller_name) : null,
                    'intid' => isset($call->interpreter_id) ? trim($call->interpreter_id) : null,
                    'language' => isset($call->language) ? trim($call->language) : null,
                    'specialf' => isset($call->inv_special_a) ? trim($call->inv_special_a) : null
                );
            }
        } 
        return $data;
    }

    function get_uuid_from_db(){
        $this->db->select('*');
        $this->db->where('uuid', $uuid);
        $query = $this->db->get('call_links');
        if($query->num_rows() > 0){
            $this->firephp->log($query);
            return true;
        } else{
            return false;
        }
    }

    //PRIMARY GET FOR TRACKING
    function get_language(){//GETS LANGUAGES FOR DROPDOWN
        $query = $this->db->query('SELECT DISTINCT language_code, language FROM interpreters ORDER BY language asc');
        return $query->result();
    }
 
    function get_my_languages(){//GETS LANGUAGES FOR INTERPRETER PAGE
        $creds = $this->auth_model->getUserCredentials();
        if($creds->intid != ''){
            $query = $this->db->query('SELECT DISTINCT language_code, language FROM interpreters WHERE iid = ' . $creds->intid . ';');
            return $query->result();
        } else{
            return FALSE;
        }
    }

    function get_lang_code_from_database($lang){//BACKEND LANGUAGE SUBMIT
        $this->db->select('language_code');
        $this->db->where('language', $lang);
        $language_codes = array();
        $query = $this->db->get('languages');
        if($query->result()){
            foreach ($query->result() as $language_code){
                $language_codes['language_code'] = $language_code->language_code;
            }
        return $language_codes;
        $this->firephp->log($language_codes);
        }
    }

    function get_interpreters_by_language($tree = null, $uuid){//GETS INTERPRETERS ONCE LANGUAGE SELECTED
        $this->db->select('*');
        if($tree != NULL){
            $this->db->where('language_code', $tree);
        }
        $this->db->order_by('order', 'asc');
        $query = $this->db->get('interpreters');
        $interpreters = array();
        if($query->result()){
            $add_language = array('language' => $tree);
            $this->db->where('uuid', $uuid);
            $this->db->update('call_links', $add_language);
            return $query->result();
        } else {
            return FALSE;
        }
    }

    function get_interpreter_by_intid($intid){
        $this->db->select('*');
        $this->db->where('iid', $intid);
        $query = $this->db->get('interpreters');
        if($query->result()){
            return $query->result();
        } else {
            return FALSE;
        }
    }

    function get_interpreters_data($id = null){//GETS DATA ONCE INTERPRETER SELECTED
        $this->db->select('name, iid, phone_1, phone_2, language_code, notes');      
        if($id != NULL){
            $this->db->where('id', $id);
            $this->db->limit(1);
        }
        $query = $this->db->get('interpreters');
        $interpreters_data = array();
        if($query->result()){
            foreach ($query->result() as $interpreter_data) {
                $interpreters_data['name'] = $interpreter_data->name;
                $interpreters_data['iid'] = $interpreter_data->iid;
                $interpreters_data['phone_1'] = $interpreter_data->phone_1;
                $interpreters_data['phone_2'] = $interpreter_data->phone_2;
                $interpreters_data['language_code'] = $interpreter_data->language_code;
                $interpreters_data['notes'] = $interpreter_data->notes;

            }
            return $interpreters_data;
        } else {
            return FALSE;
        }
    } 

    function get_division_name($access_code){
        $this->db->select('division');
        $this->db->where('access_code', $access_code);
        $query = $this->db->get('client_data');
        return $query->result();
    }

    function check_if_access_code_exists($access_code){
        $this->db->select('id');
        $this->db->where('access_code', $access_code);
        $query = $this->db->get('client_data');
        if ($query->num_rows() > 0){
            return false;
        } else{
            return true;
        }
    }
    
    function get_valid_access_code($access_code = null, $uuid){
        $this->db->select('access_code, otp_instructions, otp_sp_in, sp_type, agency, division');
        if($access_code != NULL){
            $this->db->where('access_code', $access_code);
        }
        $query = $this->db->get('client_data', 1);
        $clients_data = array();
        if($query->result()){
            $add_call_data = array('access_code' => $access_code); 
            $this->db->where('uuid', $uuid);
            $update = $this->db->update('call_links', $add_call_data);
            return $query->result();
        } else {
            return FALSE;
        }
    } 
    
    //NAME SUGGESTION FUNCTION
    function GetAutocomplete($options = array()){
        $this->db->select('name');
        $this->db->like('name', $options['keyword'], 'after');
        $query = $this->db->get('names', 5);
        return $query->result();
    }

    //SPECIAL REQUEST DROPDOWN
    function get_sp_dd_by_access_code(){
        $this->db->select('*');
        $query = $this->db->get('special_dropdown');
        $options = array();
        if($query->result()){
            foreach ($query->result() as $option) {
                $options[$option->counties] = $option->counties;
            }
            return $options;
        } else {
            return FALSE;
        }
    }
    
    //NEEDED???
    function cuda_login($user, $pin){
        $url = 'http://192.168.1.254/gui/login/login';
        $ch = curl_init($url);
        $options = array(CURLOPT_POST => TRUE, CURLOPT_POSTFIELDS => '__auth_user=' . $user . '&__auth_pass=' . $pin, CURLOPT_COOKIEFILE => '/tmp/cudasess5041', CURLOPT_RETURNTRANSFER => TRUE);
        curl_setopt_array($ch, $options);
        $output = curl_exec ($ch);
        $this->firephp->log($ch);
        curl_close($ch);
        $login_data = (json_decode($output, TRUE));
        if(isset($login_data['error']) && $login_data['error'] == 'NOTAUTHORIZED'){
            return FALSE;
        } else{
            if(isset($login_data['data'])){
                return $login_data;
            }
        }
    }

    function add_all(){
        $v_language = $this->input->post('language');
        $data = array('id' => null, 'language' => $v_language);
        return $data;
    }

    function post_call_to_database(){
        $add_call_data = array(
            'access_code'      => intval($this->input->post('access_code')), 
            'caller_name'      => $this->input->post('rep_name'),
            'inv_special_a'    => $this->input->post('specialf') != "" ? $this->input->post('specialf') : NULL,
            'language'         => $this->input->post('language'),
            'interpreter_id'   => intval($this->input->post('intid')),
            'drop'             => intval($this->input->post('drop')),
            'callout'          => intval($this->input->post('callout')),
            'callout_number'   => intval($this->input->post('co_num')),
            'submit_timestamp' => date("Y-m-d H:i:s", strtotime('now')),
            'processed'        => $this->input->post('processed')

        );
        $this->db->where('uuid', $this->input->post('uuid'));
        $update = $this->db->update('call_links', $add_call_data);
        if($this->db->affected_rows() > 0){
            $data = array(
                'access_code' => $this->input->post('access_code'),
                'rep_name' => $this->input->post('rep_name')
                );
        } else{
            $data = FALSE;
        }
        return $data;
    }
    
    function callout_form($id){
        return '<a rel="tooltip" title="Add Callout Number" class="toolbox-action-right" href="javascript:void(0);"><img src="' . base_url() . 'assets/img/icons/packs/fugue/16x16/edit-number.png" /></a>
                <div class="toolbox-content-right">
                    <div class="block-border">
                        <div class="block-header small">
                            <h1>Callout #</h1>
                        </div>
                        <form id="add_callout_number" class="block-content form" action="" method="post">
                            <p class="inline-mini-label">
                                <label for="callout_number' . $id . '">Callout to:</label>
                                <input type="text" id="callout_number' . $id . '" name="callout_number' . $id . '" class="required">
                            </p>
                                <input type="hidden" id="call' . $id . '" name="call' . $id . '" value="' . $id . '">
                            <div class="block-actions">
                                <ul class="actions-left">
                                    <li><input type="button" class="button red" onClick="cancel(); return false;" value="Cancel"></li>
                                </ul>
                                <ul class="actions-right">
                                    <li><input type="button" class="button" onClick="add_callout(jQNC("#callout_number' . $id . '").val(),' . $id . ')" value="Add"></li>
                                </ul>
                            </div>
                        </form>
                    </div>                                    
                </div>';
    }

    function incident_form($id){
        return '<a rel="tooltip-left" title="Report Incident" class="toolbox-action-incident" href="javascript:void(0);"><img src="' . base_url() . 'assets/img/icons/packs/fugue/16x16/clipboard--exclamation.png" /></a>
                <div class="toolbox-content-incident">
                    <div class="block-border">
                        <div class="block-header small">
                            <h1>Report Incident</h1>
                        </div>
                        <form id="add_incident" class="block-content form" action="" method="post">  
                            <table style="width:100%">
                                <tr>
                                    <td>Rude To Router</td><td><input type="checkbox" name="incident' . $id . '[]" value="1"></td>
                                    <td>Offended if asked to repeat</td><td><input type="checkbox" name="incident' . $id . '[]" value="9"></td>
                                </tr>
                                <tr>
                                    <td>Rude to LEP</td><td><input type="checkbox" name="incident' . $id . '[]" value="2"></td>
                                    <td>Not speaking in first person</td><td><input type="checkbox" name="incident' . $id . '[]" value="10"></td>
                                </tr>
                                <tr>
                                    <td>No Access Code</td><td><input type="checkbox" name="incident' . $id . '[]" value="3"></td>
                                    <td>Not speaking in short sentences</td><td><input type="checkbox" name="incident' . $id . '[]" value="11"></td>
                                </tr>
                                <tr>
                                    <td>Could not identify Language</td><td><input type="checkbox" name="incident' . $id . '[]" value="4"></td>
                                    <td>Impatient/constant interruption</td><td><input type="checkbox" name="incident' . $id . '[]" value="12"></td>
                                </tr>
                                <tr>
                                    <td>Refused to spell Name</td><td><input type="checkbox" name="incident' . $id . '[]" value="5"></td>
                                    <td>Long Hold Time</td><td><input type="checkbox" name="incident' . $id . '[]" value="13"></td>
                                </tr>
                                <tr>
                                    <td>Rushing</td><td><input type="checkbox" name="incident' . $id . '[]" value="6"></td><td>Misdirect</td>
                                    <td><input type="checkbox" name="incident' . $id . '[]" value="14"></td>
                                </tr>
                                <tr>
                                    <td>Video Conference not working</td><td><input type="checkbox" name="incident' . $id . '[]" value="7"></td>
                                    <td>Technical Issues</td><td><input type="checkbox" name="incident' . $id . '[]" value="15"></td>
                                </tr>
                                <tr>
                                    <td>Hard to hear/Speakerphone</td><td><input type="checkbox" name="incident' . $id . '[]" value="8"></td>
                                    <td>Language Not Available</td><td><input type="checkbox" name="incident' . $id . '[]" value="16"></td>
                                </tr>
                            </table>
                            <div class="clear"></div>
                            <div class="block-actions">
                                <ul class="actions-left">
                                    <li><input type="button" class="button red" onClick="cancel_incident();" value="Cancel"></li>
                                </ul>
                                <ul class="actions-right">
                                    <li><input type="button" class="button" onClick="check_data(' . $id . ')" value="Report"></li>
                                </ul>
                            </div>
                        </form>
                    </div>         
                </div>';
    }
    
}
?>