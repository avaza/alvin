<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Live extends Alvin_Controller {

    protected $login = array('1000', '6842');

    function __construct(){
        parent::__construct();
        $this->load->model('live_model','', TRUE);
        $this->load->library('call', $this->login);
    }

    //PRIMARY FUNCTIONS
    function index(){
        $this->load->view('call_tracking/live/live_view');
    }

    function show(){
        echo $this->live_model->getLastMonth();
    }

    function run_call($sess, $uuid){
        header('Content-Type: application/x-json');
        echo(json_encode($this->call->completeNextCall($sess, $uuid, NULL)));
    }

    function see_call($uuid = NULL, $view = 7, $session = NULL){
        if($uuid == 'next'){
            unset($uuid);
        }
        /*echo '<!DOCTYPE html>
                <html lang="en">
                    <head>
                        <meta http-equiv="refresh" content="1">
                    </head>';*/
        echo '<pre>';
        print_r($this->call->completeNextCall($session, $uuid, $view));
        echo '</pre>';
    }
    //END PRIMARY FUNCTIONS

    //JAVASCRIPT PROCESSING FUNCTIONS
    function cuda_sessn(){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->call->getCudaSess()));
    }

    function all_uuids(){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->live_model->getCallUuids()));
    }
    //END JAVASCRIPT PROCESSING FUNCTIONS
}
?>