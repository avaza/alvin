<?php
class Live_model extends CI_Model{

    function __construct(){
        parent::__construct();
        $this->load->database();
    }

    function getCallsFromMonthListedHere(){
        //return $this->getThisMonth();
        return $this->getLastMonth();
    }

    function getThisMonth(){
        return date('Y-m-d', strtotime('first day of this month'));
    }

    function getLastMonth(){
        return date('Y-m-d', strtotime('first day of last month'));
    }

    function getCallUuids(){
        $incoming = array(
            'calls' => $this->getIncmp(),
            'flags' => $this->getFlags(),
            'fatal' => $this->getError(),
            'progs' => $this->getProgs(),
            'posts' => $this->getPosts()
        );
        return $incoming;
    }

    function getIncmp(){
        $this->db->select('*');
        $this->db->where('error !=', 1);
        $this->db->where('deleted', 0);
        $this->db->where('completed', 0);
        $this->db->where('link_timestamp >', $this->getCallsFromMonthListedHere());
        $q = $this->db->get('call_links');
        if($q->result()){
            foreach($q->result() as $incmp){
                $uuids[] = $incmp->uuid;
            }
            return $uuids;
        } else{
            return array();
        }
    }

    function getFlags(){
        if($this->getCallsFromMonthListedHere() == $this->getLastMonth())
        {
            return [];
        }
        $this->db->select('*');
        $this->db->where('error = ', 1);
        $this->db->where('deleted', 0);
        $this->db->where('link_timestamp >', $this->getCallsFromMonthListedHere());
        $q = $this->db->get('call_records');
        if($q->result()){
            foreach($q->result() as $flag){
                $uuids[] = $flag->uuid;
            }
            return $uuids;
        } else{
            return array();
        }
    }

    function getError(){
        $this->db->select('*');
        $this->db->where('deleted', 0);
        $this->db->where('error', 1);
        $this->db->where('link_timestamp >', $this->getCallsFromMonthListedHere());
        $q = $this->db->get('call_links');
        if($q->result()){
            foreach($q->result() as $error){
                $uuids[] = $error->uuid;
            }
            return $uuids;
        } else{
            return array();
        }
    }

    function getProgs(){
        $this->db->select('*');
        $this->db->where('error', 2);
        $this->db->where('deleted', 0);
        $this->db->where('link_timestamp >', $this->getCallsFromMonthListedHere());
        $q = $this->db->get('call_links');
        if($q->result()){
            foreach($q->result() as $prog){
                $uuids[] = $prog->uuid;
            }
            return $uuids;
        } else{
            return array();
        }
    }

    function getPosts(){
        $this->db->select('*');
        $this->db->where('link_timestamp >', $this->getCallsFromMonthListedHere());
        $q = $this->db->get('call_records');
        if($q->result()){
            foreach($q->result() as $post){
                $uuids[] = $post->uuid;
            }
            return $uuids;
        } else{
            return array();
        }
    }



    function count($table, $where = NULL){
        $where = isset($where) ? ' WHERE ' . $where:'';
        $q = $this->db->query('SELECT COUNT(*) FROM ' . $table . $where);
        $result = $q->result();
        foreach($result['0'] as $count){
            return $count;
        }
    }
}
?>