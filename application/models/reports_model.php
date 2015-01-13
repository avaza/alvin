<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Reports_model extends CI_Model{
    
    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library('PHPExcel');
        $this->load->library('ftp');
    }

    function is_time_punch($punch_array){
        if($punch_array['status_type'] == 3 || $punch_array['status_type'] == 0){
            return 1;
        } else{
            if($punch_array['status_type'] == 1){
                $prev_punch = $this->get_punch_before(0, $punch_array);
                if($prev_punch == FALSE){
                    return 1;
                } else{
                    if($prev_punch['0']->status_type == 3 || $prev_punch['0']->status_type == 0){
                        return 1;
                    } else{
                        return 0;
                    }
                }
            } else{
                return 0;
            }
        }
    }

    function status_change($type, $user){
        $insert_array = ['intid' => $user, 'time_of_change' => date("Y-m-d H:i:s"), 'changed_to' => $type];
        $this->db->insert('status_change', $insert_array);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function complete_punch($punch_array){
        $punch_before = $this->get_punch_before(0, $punch_array);
        if($punch_before){
            $before_punched = $this->update_punch_before(0, $punch_array, $punch_before);
            $this->firephp->log($before_punched);
            if($before_punched && $punch_array['time_punch'] == 1){
                $timepunch_before = $this->get_punch_before(1, $punch_array);
                if($timepunch_before){
                    $completed = $this->update_punch_before(1, $punch_array, $timepunch_before);
                }             
            } else{
                $return = array('punched' => FALSE, 'msg' => 'Unable to update Previous Punch');
            }
        } else{
            $return = array('punched' => FALSE, 'msg' => 'Unable to find Previous Punch');
        }
        if($punch_array['type'] == 3 || $punch_array['type'] == 2){//ADD NEW PUNCH FROM TIMESTAMP
            $punch_after = $this->get_punch_after(0, $punch_array);
            if(isset($punch_after['0']->edit_punch)){
                $punch_array['next_punch'] = $punch_after['0']->edit_punch;
            } else if(!isset($punch_after['0']->edit_punch) && isset($punch_after['0']->this_punch)){
                $punch_array['next_punch'] = $punch_after['0']->this_punch;
            }          
            if($punch_array['time_punch'] == 1){
                $timepunch_after = $this->get_punch_after(1, $punch_array);
                if(isset($timepunch_after['0']->edit_punch)){
                    $punch_array['next_time_punch'] = $timepunch_after['0']->edit_punch;
                } else if(!isset($punch_after['0']->edit_punch) && isset($timepunch_after['0']->this_punch)){
                    $punch_array['next_time_punch'] = $timepunch_after['0']->this_punch;
                }
            }
        }
        $this_punch = $this->punch($punch_array);
        if(isset($return)){
            return $return;
        } else{
            return $this_punch;
        }        
    }

    function get_punch_before($time, $punch_array){
        $this->db->select('*');
        if($time == 1){
            $this->db->where('time_punch', 1);
        }
        $this->db->where('this_punch <', $punch_array['this_punch']);
        $this->db->where('intid', $punch_array['intid']);
        $this->db->order_by('this_punch', 'desc');
        $punch_before = $this->db->get('timeclock', 1);
        if($punch_before->result()){
            return $punch_before->result();
        } else{
            return FALSE;
        }
    }

    function update_punch_before($time, $punch_array, $prev_punch_array){
        $last_punch_id = $prev_punch_array['0']->id;
        if($time == 1){
            $update = array('next_time_punch' => $punch_array['this_punch']);
        } else{
            $update = array('next_punch' => $punch_array['this_punch']);
        }
        $this->firephp->log($update);
        $this->db->where('id', $last_punch_id);
        $this->db->update('timeclock', $update);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function get_punch_after($time, $punch_array){
        $this->db->select('*');
        if($time == 1){
            $this->db->where('time_punch', 1);
        }
        $this->db->where('this_punch >', $punch_array['this_punch']);
        $this->db->where('intid', $punch_array['intid']);
        $this->db->order_by('this_punch', 'asc');
        $punch_before = $this->db->get('timeclock', 1);
        if($punch_before->result()){
            return $punch_before->result();
        } else{
            return FALSE;
        }
    }

    function punch($punch_array){        
        $this->firephp->log($punch_array);
        if($punch_array['type'] == 3 || $punch_array['type'] == 1){
            unset($punch_array['type']);
            $this->db->insert('timeclock', $punch_array);
            if($this->db->affected_rows() > 0){
                $this->update_user_punch($punch_array, $this->db->insert_id());
                $return = array('punched' => TRUE);
            } else{
                $return = array('punched' => FALSE);
            }
        } else if($punch_array['type'] == 2){
            unset($punch_array['type']);
            $punch_array['edit_punch'] = $punch_array['this_punch'];
            unset($punch_array['this_punch']);
            $this->db->where('id', $punch_array['id']);
            $this->db->update('timeclock', $punch_array);
            if($this->db->affected_rows() > 0){
                $this->update_user_punch($punch_array, $punch_array['id']);
                $return = array('punched' => TRUE);
            } else{
                $return = array('punched' => FALSE);
            }
        } else{
            $return = array('punched' => FALSE);
        }
        return $return;      
    }

    function update_user_punch($punch_array, $id){
        $update = array('last_punch_id' => $id);
        $this->db->where('intid', $punch_array['intid']);
        $this->db->update('users', $update);
        if($this->db->affected_rows() > 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function get_timepunches(){
        $start = $this->input->post('from');
        $end = $this->input->post('to');
        $intid = $this->input->post('intid');
        $this->db->select('*');
        $this->db->where('intid', $intid);
        $this->db->where('this_punch >=', $start);
        $this->db->where('this_punch <', $end);
        $this->db->order_by('this_punch', 'asc');
        $query = $this->db->get('timeclock'); 
        if($query->result()){
            return $query->result();
        } else{
            if($query->num_rows() == 0){
                $this->db->select('*');
                $this->db->where('intid', $intid);
                $this->db->where('this_punch <', $start);
                $this->db->order_by('this_punch', 'desc');
                $query = $this->db->get('timeclock', 1); 
                if($query->result()){
                    return $query->result();
                } else{
                    return false;
                } 
            }
        }       
    }

    function whos_clocked_in(){
        $this->db->select('*');
        $this->db->where('next_punch', NULL);
        $this->db->where('status_type', '1');
        $this->db->or_where('status_type', '2');
        $this->db->or_where('status_type', '4');
        $this->db->order_by('this_punch', 'asc');
        $query = $this->db->get('timeclock'); 
        if($query->result()){
            return $query->result();
        } else{
            return false;
        } 
    }

    function get_timecard_report_dates($start, $end, $timecard_data, $detailed){
        if($timecard_data != false && count($timecard_data) > 1){
            $tc_data = array();
            foreach($timecard_data as $index => $object){
                if(strtotime($object->timestamp) >= strtotime($start) && strtotime($object->timestamp) <= strtotime($end)){
                    if($detailed == true){
                        array_push($tc_data, $object);
                    } else{
                        if($object->status_type != 2 && $object->status_type != 4 && $timecard_data[$index-1]->status_type != 2 && $timecard_data[$index-1]->status_type != 4){
                            array_push($tc_data, $object);
                        }
                    }
                }
            }
            $smallest = INFINITY;
            if(count($tc_data) > 0){
                foreach($tc_data as $data){
                    if($data->id < $smallest){
                        $smallest = $data->id;
                    }
                }
            }
            $largest = -INFINITY;
            foreach($timecard_data as $last_time){
                if($last_time->id > $largest && $last_time->id < $smallest){
                    $largest = $last_time->id;
                    $last_time_to_get = $last_time;
                }
            }
            array_unshift($tc_data, $last_time_to_get);
            return $tc_data;
        } else{
            return $timecard_data;
        }
    }

    function get_total_secs($data){
        $total = 0;
        foreach($data as $record){
            if(isset($record->seconds)){
                $total = $total + $record->seconds;
            }
        }
        return $total;
    }

    function am_i_clocked_in($intid){
        $this->db->select('*');
        $this->db->where('intid', $intid);
        $this->db->order_by("this_punch", "desc");
        $query = $this->db->get('timeclock', 1);
        if($query->num_rows() > 0){        
            return $query->result();
        } else{
            return false;
        }
    }

    function get_staff(){
        $this->db->select('*');
        $query = $this->db->get('users');
        if($query->num_rows() > 0){        
            return $query->result();
        } else{
            return FALSE;
        }
    }

    function get_staff_member($intid){
        $this->db->select('*');
        $this->db->where('intid', $intid);
        $query = $this->db->get('users');
        if($query->num_rows() > 0){
            return $query->result();
        } else{
            return FALSE;
        }
    }

    function get_edit_punch($id){
        $this->db->select('*');
        $this->db->where('id', $id);
        $query = $this->db->get('timeclock');
        if($query->result()){
            return $query->result();
        } else{
            return false;
        }
    }
}
    
?>