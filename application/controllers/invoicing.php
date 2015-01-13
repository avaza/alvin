<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Invoicing extends Alvin_Controller {

	function __construct(){
		parent::__construct();		
		$this->load->database();	
		$this->load->model('invoicing_model','', TRUE);
        $this->load->library('invoice');
	}

    function way(){
        $calls = $this->invoicing_model->collectAllInvoicesFromRange('123456');
        echo '<pre>';
        print_r($calls);
        echo '</pre>';
    }

    //NAVIGATION
    function inv_bill(){
        $data['main_content'] = 'invoicing/bill';
        $this->load->view('includes/templates/standard', $data);
    }    

    function inv_pay(){
        $data['main_content'] = 'invoicing/pay';
        $this->load->view('includes/templates/standard', $data);
    }

    function inv_archive(){
        $data['main_content'] = 'invoicing/archive';
        $this->load->view('includes/templates/standard', $data);
    }

    function generate_old_invoice($client_id){
        $this->invoicing_model->create_invoice($client_id, '2014-09-01 00:00:00', '2014-10-01 00:00:00');
    }

	//JAVASCRIPT FUNCTIONS
	function clients_to_be_billed($start, $end){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->invoicing_model->clients_to_be_billed($start, $end)));
    }

    function clients_already_billed($start, $end){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->invoicing_model->clients_already_billed($start, $end)));
    }
    //REPORT CREATORS

    function create_invoice($client_id, $start = '2014-01-01 00:00:00', $end = '2014-02-01 00:00:00'){
        ini_set("memory_limit","1024M");
        header('Content-Type: application/x-json; charset=utf-8');
        $invoice_code = $this->invoicing_model->getInvoiceCode($start, $end);
        $this->createNewInvoice($invoice_code, $client_id);
    }

    function collectInvoicesUnder500($start = '2014-01-01 00:00:00', $end = '2014-02-01 00:00:00'){
        header('Content-Type: application/x-json; charset=utf-8');
        $clients_to_bill = $this->invoicing_model->clients_to_be_billed($start, $end);
        $invoice_code = $this->invoicing_model->getInvoiceCode($start, $end);
        foreach($clients_to_bill as $client_to_bill){
            $client_calls = $this->invoicing_model->invoice_data($client_to_bill->client_id, $start, $end);
            if(count($client_calls) < 500){
                $clients[] = $client_to_bill->client_id;
            }
        }
        echo(json_encode($clients));
    }

    function export_all_under_500($start, $end){
    	header('Content-Type: application/x-json; charset=utf-8');
        $clients_to_bill = $this->invoicing_model->clients_to_be_billed($start, $end);
        $invoice_code = $this->invoicing_model->getInvoiceCode($start, $end);
        foreach($clients_to_bill as $client_to_bill){
            $client_calls = $this->invoicing_model->invoice_data($client_to_bill->client_id, $start, $end);
            if(count($client_calls) < 500){
                $clients[] = $client_to_bill->client_id;
            }
        }
        foreach($clients as $client_id){
            $completed[$client_id] = $this->createNewInvoiceReturn($invoice_code, $client_id);
        }
        echo(json_encode($completed));
    }

    function test_row_insert(){
        $call_array = range(1, 100);
        $type = 
        print_r($this->invoicing_model->insert_row(false, $call_array));
    }

    function test_pdf(){
        $this->invoicing_model->test_pdf();
    }

    function suh(){
        echo phpinfo();
    }

    function sub(){
        echo ini_get("memory_limit")."\n";
        ini_set("memory_limit","1024M");
        echo ini_get("memory_limit")."\n";
    }

    function createNewInvoice($invoice_code, $client_id){
        header('Content-Type: application/x-json; charset=utf-8');
        echo(json_encode($this->invoice->createInvoiceForInvoiceCode($invoice_code, $client_id)));
    }

    function createNewInvoiceReturn($invoice_code, $client_id){
        return $this->invoice->createInvoiceForInvoiceCode($invoice_code, $client_id);
    }   
}