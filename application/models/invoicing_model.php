<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Invoicing_model extends CI_Model{
    

    function __construct(){
        parent::__construct();
        $this->load->database();
        $this->load->library('PHPExcel');
        $this->load->library('ftp');
    }

    function getInvoiceCode($start, $end){
        $year = date('Y', strtotime(urldecode($start)));
        $month = date('m', strtotime(urldecode($start)));
        return $year . $month;
        $this->db->select('*');
        $this->db->where('link_timestamp >', $start);
        $this->db->where('link_timestamp <', $end);
        $q = $this->db->get('call_records', 1);
        if($q->result()){
            foreach($q->result() as $call){
                return $call->inv_code;
            }
        } else{
            return FALSE;
        }
    }

    function isTNClients($call){
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
		return in_array($call->client_id, $check_array);
    }

//DYNAMIC DROPDOWN FROM DATE RANGE
    function clients_to_be_billed($start, $end){
        $this->db->select('client_id');
        $this->db->distinct();
        $this->db->where('start_timestamp >=', urldecode($start));
        $this->db->where('start_timestamp <', urldecode($end));
        $this->db->where('drop', 0);
        $this->db->where('error', 0);
        $this->db->order_by("client_id", "asc");
        $query = $this->db->get('call_records');
        if($query->result()){
            return $query->result();
        } else {
            return FALSE;
        }
    }
    //END DYNAMIC DROPDOWN FROM DATE RANGE

    //SETUP FOR STANDARD OR SPECIAL INVOICES
    



    function getSpecialLabels($client_id){
    	$this->db->select('*');
    	$this->db->where('client_id', $client_id);
    	$this->db->where('special_invoice IS NOT NULL', null);
    	$query = $this->db->get('client_data', 1);
    	if($query->result()){
    		foreach($query->result() as $client){
    			$special_invoice = json_decode($client->special_invoice);
    			if(isset($special_invoice->format)){
    				$labels['format'] = (array) $special_invoice->format;
    			}
    			foreach($special_invoice->columns as $column => $details){
    				if(isset($details->label)){
    					$labels['values'][$column] = $details->label;
    				}
    			}
    		}
    		return $labels;
    	} else{
    		return FALSE;
    	}
    }


    //TODO NOW:FIX THIS     LATER:add this to the script so it is always right
    function getCallerNumber($possible_numbers){
    	
    }

    function export_all_under_500($start, $end){
        $all_clients = $this->clients_to_be_billed($start, $end);
        $completed_generation = array();
        foreach($all_clients as $key => $client){
            $client_calls = $this->invoice_data($client->client_id, $start, $end);
            if(count($client_calls) < 500){
                $generated = $this->create_invoice($client->client_id, $start, $end);
                $completed_generation[$client->client_id] = $generated;
            }
        }
        return $completed_generation;
    }

    function clients_already_billed($start, $end){
        $time_title = date('MY', strtotime(urldecode($start)));
        $rem_path = '/disk1/RESTRICTED/Invoicing-/NEW INVOICING/';
        $complete = array();
        $clients = $this->clients_to_be_billed($start, $end);
        $this->ftp->connect(array('hostname' => '192.168.1.60', 'username' => 'guest', 'password' => ''));
        $full_names = $this->ftp->list_files($rem_path);
        $files = array();
        foreach ($full_names as $indx => $f){
            $new = str_replace($rem_path, '', $f);
            $files[] = $new;
        }
        foreach($clients as $key => $client){
            $client_data = $this->get_client_data($client->client_id);
            foreach($client_data as $number => $obj){
                $client->agency = $obj->agency;
            }
            $rem_filename_1 = 'TN-' . $client->client_id . '-' . $client->agency . '-' . $time_title . ".xlsx";
            $rem_filename_2 = $client->client_id . '-' . $client->agency . '-' . $time_title . ".xlsx";
            if(in_array($rem_filename_1, $files)){
                $client->billed = TRUE;
                $client->msp = TRUE;
                $client->file = 'TN-' . $client->client_id . '-' . $client->agency . '-' . $time_title;
            } elseif(in_array($rem_filename_2, $files)){
                $client->billed = TRUE;
                $client->msp = FALSE;
                $client->file = $client->client_id . '-' . $client->agency . '-' . $time_title;
            } else{
                $client->billed = FALSE;
                $client->msp = FALSE;
                $client->file = FALSE;
            }
            $complete[] = $client;
        }
        $this->ftp->close();
        return $complete;
    }



    function insert_row($objPHPExcel, $row_array){
        $char = 'A';
        $set_array = array();
        foreach($row_array as $cell){
            $set_array[$char] = $cell;
            $char++;
        }
        return $set_array;
    }

    //MAIN INVOICE DATA SUBFUNCTIONS
    //MAIN FUNCTION
    function create_invoice($client_id, $start, $end){
        $invoice_data = $this->invoice_data($client_id, $start, $end);
        $access_codes = array();
        foreach($invoice_data as $call_data){
            if(!in_array($call_data->access_code, $access_codes)){
                array_push($access_codes, $call_data->access_code);
            }
        }
        $separated_data = array();
        foreach($access_codes as $index => $access_code){
            $separated_data[$access_code] = array();
        }
        foreach($invoice_data as $call_data){
            if($call_data->drop != 1){
                array_push($separated_data[$call_data->access_code], $call_data);
            }
        }
        $invoice_generated = $this->generate_excel_invoice($separated_data, $start, $end);
        return $invoice_generated;
    }

    function invoice_data($client_id, $start, $end){
        $this->db->select('*');
        $this->db->where('client_id', $client_id);
        $this->db->where('start_timestamp >=', urldecode($start));
        $this->db->where('start_timestamp <', urldecode($end));
        $query = $this->db->get('call_records');
        if($query->result()){
            return $query->result();
        } else{
            return FALSE;
        }
    }

    function create_JOL($start, $end){
        //get the data based on dates entered
        //process the data into an excel writer line by line
        //write to excel and return true if complete or false if incomplete
    }

    function get_client_data($client_id){
        $this->db->select('*');
        $this->db->where('client_id', $client_id);
        $query = $this->db->get('client_data', 1);
        if($query->result()){
            return $query->result();
        } else{
            return FALSE;
        }
    }

    
    //END MAIN INVOICE DATA SUBFUNCTIONS

    //INVOICE CREATE FUNCTION

    function get_languages($separated_data){
        $languages = array();
        foreach($separated_data as $section){
            foreach($section as $call){
                if(!in_array($call->language, $languages)){
                    $languages[] = $call->language;
                }
            }
        }
        return $languages;
    }

    function create_pdf($filename){
        $filename = urldecode($filename);
        $rendererName = PHPExcel_Settings::PDF_RENDERER_DOMPDF;
        $rendererLibrary = 'dompdf'; // this is the name of dompdf folder.. 
        $rendererLibraryPath = '/opt/SITES/database/testing/libraries/PHPExcel/Writer/'.$rendererLibrary;
        if(!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)){
            die('NOTICE: Please set the $rendererName and $rendererLibraryPath values<br />at the top of this script as appropriate for your directory structure');
        }
        $local_path = '/var/www/';
        $source = $local_path . $filename . '.pdf';
        $objReader = new PHPExcel_Reader_Excel2007();
        $objPHPExcel  = $objReader->load($local_path . $filename . '.xlsx');
        $objWriter = new PHPExcel_Writer_PDF($objPHPExcel);
        $objWriter->writeAllSheets();
        $objWriter->save($source);
        $this->ftp->connect(array('hostname' => '192.168.1.60', 'username' => 'guest', 'password' => ''));
        $remote_path = '/disk1/RESTRICTED/Invoicing-/NEW INVOICING/';
        $upload = $this->ftp->upload($source, $remote_path . $filename. '.pdf');
        $this->ftp->close();        
    }

    function generate_excel_invoice($separated_data, $start, $end){
        $title_start = date('m-d-y', strtotime(urldecode($start)));
        $title_end = date('m-d-y', strtotime(urldecode($end)));
        $excelObjects = $this->layout_sheet();
        $objPHPExcel = $excelObjects['0'];
        foreach($separated_data as $index => $division_data){
            if(isset($division_data['0']) && is_object($division_data['0'])){
                $client_id = $division_data['0']->client_id;
            } else{
                foreach($division_data as $i => $o){
                    if(is_object($o)){
                        $client_id = $o->client_id;
                    } else{
                        die;
                    }
                }                    
            } 
        }
        $client_data = $this->get_client_data($client_id);
        $objPHPExcel = $this->layout_invoice($objPHPExcel, $client_data, $start, $end);
        //insert sections one at a time
        $current_row = 3;
        $current_itm = 1;
        $languages = $this->get_languages($separated_data);
        $access_codes = array();
        foreach($separated_data as $access_code => $calls){
            $section_layout = $this->layout_section($objPHPExcel, $access_code, $calls, $current_row, $current_itm);
            $objPHPExcel = $section_layout['0'];
            $current_row = $section_layout['1'] + 1;
            $con_time_clients = $section_layout['2'];
            if(!in_array($access_code, $access_codes)){
                $access_codes[] = $access_code;
            }
        }
        //GRAND TOTAL
        $total_sum = '=SUMIF(A:A,">"&0,K:K)';
        $cost_sum = '=SUMIF(A:A,">"&0,L:L)';
        $current_row++;
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $current_row . ':C' . $current_row);
        $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue('SUMMARY');
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue('MINUTES');
        $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue('CHARGES');
        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('summary_row_style'));
        $current_row++;
        $objPHPExcel->getActiveSheet()->mergeCells('E' . $current_row . ':I' . $current_row);
        $objPHPExcel->getActiveSheet()->getCell('E' . $current_row)->setValue('Over-the-phone Interpretation:');
        $report_bottom = $current_row;
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue($total_sum);
        $objPHPExcel->getActiveSheet()->getCell('O1')->setValue($total_sum);
        $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue($cost_sum);
        $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('summary_row2_style'));
        $current_row = $current_row+2;
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('TOTAL CHARGES');
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue($cost_sum);
        $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row . ':L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('grand_total_style'));
        $objPHPExcel->getActiveSheet()->mergeCells('K' . $current_row . ':L' . $current_row);
        $excelObjects['1']->setFitToWidth(1);
        //BOTTOM LANGUAGE AND ACCESS CODE TOTALS
        $whole = floor($current_row/65);
        $this_page_rows_so_far = $current_row/65;
        $fraction = $this_page_rows_so_far - $whole;
        $this_page_rows_left = 65 - ($fraction * 65);
        $broken = FALSE;
        if($this_page_rows_left < count($access_codes) + count($languages) + 10){
            $objPHPExcel->getActiveSheet()->setBreak('A' . $current_row, PHPExcel_Worksheet::BREAK_ROW);
            $broken = TRUE;
        }
        if($broken != TRUE){
            $current_row = $current_row+5;
        } else{
            $current_row = $current_row + 2;
        }  
        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('summary_row_style'));
        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue('REPORTS');
        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':D' . $current_row);
        $current_row = $current_row+2;
        $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue('Access Code');
        $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue('Minutes');
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('Calls');
        $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('% Total');
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue('Charges');
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row . ':K' . $current_row)->applyFromArray($this->get_style_array('bottom_report_header_style'));
        $current_row = $current_row+1;
        $tot = $current_row;
        foreach($access_codes as $acde){
            $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue($acde);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue('=SUMIF(N:N,G' . $current_row . ',K:K)');
            $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('=COUNTIF(N:N,G' . $current_row . ')');
            $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('=H' . $current_row . '/O1');
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(

            	);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGEll('L' . $current_row)->setValue($cost_sum);
        $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('summary_row2_style'));
        $current_row = $current_row+2;
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('TOTAL CHARGES');
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue($cost_sum);
        $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row . ':L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('grand_total_style'));
        $objPHPExcel->getActiveSheet()->mergeCells('K' . $current_row . ':L' . $current_row);
        $excelObjects['1']->setFitToWidth(1);
        //BOTTOM LANGUAGE AND ACCESS CODE TOTALS
        $whole = floor($current_row/65);
        $this_page_rows_so_far = $current_row/65;
        $fraction = $this_page_rows_so_far - $whole;
        $this_page_rows_left = 65 - ($fraction * 65);
        $broken = FALSE;
        if($this_page_rows_left < count($access_codes) + count($languages) + 10){
            $objPHPExcel->getActiveSheet()->setBreak('A' . $current_row, PHPExcel_Worksheet::BREAK_ROW);
            $broken = TRUE;
        }
        if($broken != TRUE){
            $current_row = $current_row+5;
        } else{
            $current_row = $current_row + 2;
        }  
        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('summary_row_style'));
        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue('REPORTS');
        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':D' . $current_row);
        $current_row = $current_row+2;
        $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue('Access Code');
        $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue('Minutes');
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('Calls');
        $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('% Total');
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue('Charges');
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row . ':K' . $current_row)->applyFromArray($this->get_style_array('bottom_report_header_style'));
        $current_row = $current_row+1;
        $tot = $current_row;
        foreach($access_codes as $acde){
            $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue($acde);
            $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue('=SUMIF(N:N,G' . $current_row . ',K:K)');
            $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('=COUNTIF(N:N,G' . $current_row . ')');
            $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('=H' . $current_row . '/O1');
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(

            	);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGEiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue('=SUMIF(F:F,O' . $current_row . ',L:L)');            
            $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
            $objPHPExcel->getActiveSheet()->getCell('O' . $current_row)->setValue($language);
            $current_row++;       
            
        }
        $rminus = $current_row - 1;       
        $totminus = $tot - 1;
        $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue('TOTALS');
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
        $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue('=SUM(H' . $tot . ':H' . $rminus . ')');
        $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('=SUM(I' . $tot . ':I' . $rminus . ')');
        $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
        $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('=SUM(J' . $tot . ':J' . $rminus . ')');
        $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
        $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue('=SUM(K' . $tot . ':K' . $rminus . ')');
        $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
        $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row . ':K' . $current_row . '')->applyFromArray($this->get_style_array('bottom_report_header_style'));
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setVisible(false);
        $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setVisible(false);

        $objPageSetup = $excelObjects['1'];
        $objPageSetup->setPrintArea('A1:L' . $current_row);
        $objPHPExcel->getActiveSheet()->setPageSetup($objPageSetup);        
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        if($current_row > 500){
            $objWriter->setPreCalculateFormulas(false);
        }
        $time_title = date('MY', strtotime(urldecode($start)));
        if(in_array((string)$client_id, $con_time_clients)){
            $filename = 'TN-' . $client_id . '-' . $client_data['0']->agency . '-' . $time_title;
        } else{
            $filename = $client_id . '-' . $client_data['0']->agency . '-' . $time_title;
        }
        $this->ftp->connect(array('hostname' => '192.168.1.60', 'username' => 'guest', 'password' => ''));
        $rem_path = '/disk1/RESTRICTED/Invoicing-/NEW INVOICING/';
        $full_names = $this->ftp->list_files($rem_path);
        $files = array();
        foreach ($full_names as $indx => $f){
            $new = str_replace($rem_path, '', $f);
            $files[] = $new;
        }
        $source = '/var/www/' . $filename . '.xlsx';
        $objWriter->save($source);
        $upload = $this->ftp->upload($source, $rem_path . $filename . '.xlsx');
        $this->ftp->close();
        if($upload == 1){
            return TRUE;
        } else{
            return FALSE;
        }
    }
    //END INVOICE CREATE FUNCTION

    //GET JOL DATA
    function get_jol_data($start, $end){
        $this->db->select('*');
        $this->db->where('start_timestamp >=', $start);
        $this->db->where('end_timestamp <=', $end);
        $query = $this->db->get('call_records');
        if($query->result()){
            return $query->result();
        } else{
            return FALSE;
        }
    }
    
    //CREATE JOL WITH GOTTEN DATA
    function create_jol_report($jol_data){
        //CREATE EXCEL SHEET WITH TODAYS RECORDS
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod);
        //CREATE EXCEL OBJECT
        $objPHPExcel = new PHPExcel();
        //SET TITLE
        
        $created = date('Y-m-d_H-i-s');
        $xltitle = 'JOL-CREATED-' . $created;
        
        //SET PROPERTIES
        $objPHPExcel->getProperties()->setCreator("Avaza_Database");
        $objPHPExcel->getProperties()->setLastModifiedBy("Avaza_Database");
        $objPHPExcel->getProperties()->setTitle($xltitle);
        $objPHPExcel->getProperties()->setSubject("");
        
        //
        //BEGIN SHEET LAYOUT
        //

        //CHOOSE SHEET
        $objPHPExcel->setActiveSheetIndex(0);

        //SET SHEET NAME
        $objPHPExcel->getActiveSheet()->setTitle('Invoice');

        //SET PAGE OPTIONS
        $objPageSetup = new PHPExcel_Worksheet_PageSetup();
        $objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
        $objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);

        //MAIN STYLE        
        $main_style = array(
            'font' => array(
                'name'   => 'Arial',
                'bold'   => false,
                'italic' => false,
                'size'   => 8
            ),
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'wrap'       => true
            )
        );
        //DATES CELL STYLE
        $format_date = array(
            'numberformat' => array(
                'code' => 'MM/DD/YY'
                )
        );

        //APPLY MAIN STYLES
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('A:A')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('B:B')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('C:C')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('D:D')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('E:E')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('F:F')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('G:G')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('H:H')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('I:I')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('J:J')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('K:K')->applyFromArray($main_style);
        $objPHPExcel->getActiveSheet()->getStyle('L:L')->applyFromArray($main_style);

        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("DATE");
        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue("ACCESS CODE");
        $objPHPExcel->getActiveSheet()->getCell('C1')->setValue("ARRIVAL TIME");
        $objPHPExcel->getActiveSheet()->getCell('D1')->setValue("DEPARTMENT NAME");
        $objPHPExcel->getActiveSheet()->getCell('E1')->setValue("Client ID");
        $objPHPExcel->getActiveSheet()->getCell('F1')->setValue("PHONE NUMBER");
        $objPHPExcel->getActiveSheet()->getCell('G1')->setValue("AGENCY REP");
        $objPHPExcel->getActiveSheet()->getCell('H1')->setValue("LAN");
        $objPHPExcel->getActiveSheet()->getCell('I1')->setValue("INT ID#");
        $objPHPExcel->getActiveSheet()->getCell('J1')->setValue("INTEPRETER");
        $objPHPExcel->getActiveSheet()->getCell('K1')->setValue("START TIME");
        $objPHPExcel->getActiveSheet()->getCell('L1')->setValue("END TIME");
        $objPHPExcel->getActiveSheet()->getCell('M1')->setValue("Drop Code");
        $objPHPExcel->getActiveSheet()->getCell('N1')->setValue("NOTES");
        $objPHPExcel->getActiveSheet()->getCell('O1')->setValue("Enter Incident Code");
        $objPHPExcel->getActiveSheet()->getCell('P1')->setValue("Incident Description");
        $objPHPExcel->getActiveSheet()->getCell('Q1')->setValue("CONNECTION TIME");
        $objPHPExcel->getActiveSheet()->getCell('R1')->setValue("DURATION");
        $objPHPExcel->getActiveSheet()->getCell('U1')->setValue("CALLOUT NUMBER");
        $objPHPExcel->getActiveSheet()->getCell('V1')->setValue("DEPARTMENT");

        $ext = 'ALL';
        $current_row = 1;
        foreach($jol_data as $call_row){
            PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

            $current_row++;
            $div_name = $this->get_division_name($call_row->access_code);
            foreach($div_name as $div){
                $objPHPExcel->getActiveSheet()->getCell('D' . $current_row)->setValue($div->division);
            }
            $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue($call_row->start_timestamp);
            $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row)->applyFromArray($format_date);
            $objPHPExcel->getActiveSheet()->getCell('B' . $current_row)->setValue($call_row->access_code);
            $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($call_row->answer_timestamp);
            $objPHPExcel->getActiveSheet()->getStyle('C' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
            $objPHPExcel->getActiveSheet()->getCell('E' . $current_row)->setValue($call_row->client_id);
            $objPHPExcel->getActiveSheet()->getCell('F' . $current_row)->setValue($call_row->caller_id_number);
            $objPHPExcel->getActiveSheet()->getStyle('F' . $current_row)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
            $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue($call_row->rep_name);
            $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue($call_row->language);
            if($call_row->intid != 0){
                $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue($call_row->intid);
            } else{
                $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue('2000');                
            }            
            $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue($call_row->intname);
            $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue($call_row->start_time);
            $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
            $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue($call_row->end_timestamp);
            $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
            $objPHPExcel->getActiveSheet()->getCell('M' . $current_row)->setValue($call_row->drop);
            $objPHPExcel->getActiveSheet()->getCell('N' . $current_row)->setValue("x" . $call_row->r_ext . "/ID:" . $call_row->intid);//Notes
            $objPHPExcel->getActiveSheet()->getCell('O' . $current_row)->setValue("");//Enter Incident Code
            $objPHPExcel->getActiveSheet()->getCell('P' . $current_row)->setValue("");//Incident Description
            $objPHPExcel->getActiveSheet()->getCell('Q' . $current_row)->setValue("=K" . $current_row . "-C" . $current_row);
            $objPHPExcel->getActiveSheet()->getStyle('Q' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);
            $objPHPExcel->getActiveSheet()->getCell('R' . $current_row)->setValue("=L" . $current_row . "-K" . $current_row);
            $objPHPExcel->getActiveSheet()->getStyle('R' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);
            $objPHPExcel->getActiveSheet()->getCell('U' . $current_row)->setValue($call_row->co_num);
            $objPHPExcel->getActiveSheet()->getCell('V' . $current_row)->setValue($call_row->specialf);
  
        }
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        $objWriter->save("/var/www/" . $xltitle . ".xlsx");
        $dest = 'NEW/' . $xltitle . '.xlsx';
        $source = "/var/www/" . $xltitle . ".xlsx";
        $mode = FTP_BINARY;

        if(chmod($source, 0777) == false) {
            return FALSE;
        }
        $connection = ftp_connect('192.168.1.100');
        $login = ftp_login($connection, 'administrate', 'a52Vaza09@');
        if(!$connection || !$login){ 
            return FALSE;
        }
        $upload = ftp_put($connection, $dest, $source, $mode);
        if (!$upload) { 
            return FALSE; 
        } else {
            unlink($source);
        }
        ftp_close($connection);                                                
        return TRUE;                                                         
    }
    
    function get_language($language_code){
        $this->db->select('language');
        $this->db->where('language_code', $language_code);
        $query = $this->db->get('languages', 1);
        if($query->result()){
            foreach($query->result() as $lang){
                return $lang->language;
            }
        } else{
            return FALSE;
        }
    }

        function layout_sheet(){
        //CACHE SETUP
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize' => '128MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        //CREATE EXCEL OBJECT
        $objPHPExcel = new PHPExcel();
        //SET TITLE
        $objPHPExcel->setActiveSheetIndex(0);
        //SET SHEET NAME
        $objPHPExcel->getActiveSheet()->setTitle('Invoice');
        //SET PAGE OPTIONS
        $objPageSetup = new PHPExcel_Worksheet_PageSetup();
        $objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
        $objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $objPageSetup->setFitToWidth(1);
        $objPageSetup->setFitToHeight(0);
        //$objPHPExcel->getActiveSheet()->setPageSetup($objPageSetup);
        //SET MARGINS
        $objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.17);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.17);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.39);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setHeader(0.18);
        $objPHPExcel->getActiveSheet()->getPageMargins()->setFooter(0.18);
        //SET ROW HEIGHT
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(-1);
        //SET COLUMN WIDTH
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(11.25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(9.25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(9.25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(12);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(13.5);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(11);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(11);
        //APPLY MAIN STYLES
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($this->get_style_array('main_style'));
        $objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray($this->get_style_array('header_style'));
        return array($objPHPExcel, $objPageSetup);
    }

    function layout_invoice($objPHPExcel, $client_data, $start, $end){
        $date1 = new DateTime(urldecode($start));
        $title_start = $date1->format('m-d-y');
        $date2 = new DateTime(urldecode($end));
        $title_end = $date2->format('m-d-y');
        $mth_yr = date('F-Y');
        $xltitle = $client_data['0']->client_id . '-' . $mth_yr;
        
        //SET PROPERTIES
        $objPHPExcel->getProperties()->setCreator("Avaza_Database");
        $objPHPExcel->getProperties()->setLastModifiedBy("Avaza_Database");
        $objPHPExcel->getProperties()->setTitle($xltitle);
        $objPHPExcel->getProperties()->setSubject("");

        //SET HEADER/FOOTER DATA
        $account_num  = 'Account Number: ';
        $account_num .= $client_data['0']->account_number;
        $client  = 'Client ID: ';
        $DCS_ARRAY = array(9005485, 9005484, 9005483, 9005482, 9005481, 9005397, 9005396, 9005395, 9005394, 9005393, 9005392, 9005391, 9005354, 9005353, 9005352, 9005351, 9005342, 9005341, 9005328, 9005327, 9005326, 9005325, 9005324, 9005323, 9005322, 9005321, 9005069, 9005068, 9005067, 9005066, 9005065, 9005064, 9005063, 9005062, 9005061, 90050619, 90050618, 90050617, 90050616, 90050615, 90050614, 90050613, 90050612, 90050611, 90050610);
        if(!in_array($client_data['0']->client_id , $DCS_ARRAY)){
            $client .= $client_data['0']->client_id;
        } else{
            $first = substr($client_data['0']->client_id, 0, 6);
            $rest = substr($client_data['0']->client_id, 6);
            $client .= $first .".". $rest;        
        }
        $client_name  = $client_data['0']->agency;
        if($client_data['0']->invoice == 'OCI'){
            $billed_from = 'Open Communications International';
        } else{
            $billed_from = 'Avaza Language Services Corp.';
        }
        $statement_type  = 'Over The Phone';
        $statement_type .= ' Interpretation Statement';
        
        //CONCAT DATA
        $client = $account_num . "\n" . $client . "\n" . $client_name;
        $statement = $billed_from . "\n" . $statement_type;

        //SET HEADER/FOOTER LAYOUT
           //header
           $left_header = '&L&"Arial,Bold"&8' . $client;
         $center_header = '';
          $right_header = '&R&"Arial,Bold"&8' . $statement;
           //footer
           $left_footer = '&L&"Arial,Bold"Confidential';
         $center_footer = '&C&"Arial,Bold"Date of Invoice: &D';
          $right_footer = '&R&"Arial,Bold"Page &P of &N';
        
        //INSERT HEADER/FOOTER
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($left_header . $center_header . $right_header);
        $objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter($left_footer . $center_footer . $right_footer);

        //INSERT COLUMN NAMES
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue("ITEM");
        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue("JOB \nNUMBER");
        $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('C1')->setValue("DATE");
        $objPHPExcel->getActiveSheet()->getCell('D1')->setValue("START \nTIME");
        $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('E1')->setValue("END \nTIME");
        $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('F1')->setValue("LANGUAGE");
        $objPHPExcel->getActiveSheet()->getCell('G1')->setValue("INTERPRETER \nID");
        $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('H1')->setValue("CONTACT \nPERSON");
        $objPHPExcel->getActiveSheet()->getStyle('H1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('I1')->setValue("CONTACT \nNUMBER");
        $objPHPExcel->getActiveSheet()->getStyle('I1')->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('J1')->setValue("RATE CODE");
        $objPHPExcel->getActiveSheet()->getCell('K1')->setValue("MINUTES");
        $objPHPExcel->getActiveSheet()->getCell('L1')->setValue("CHARGE");
        return $objPHPExcel;
    }

    function get_invoice_detail($access_code){
        $this->db->select('invoice_detail');
        $this->db->where('access_code', $access_code);
        $query = $this->db->get('client_data', 1);
        if($query->result()){
            foreach($query->result() as $client){
                return $client->invoice_detail;
            }
        } else{
            return FALSE;
        }
    }

    /*$dcs = array('9005061', '9005062', '9005063', '9005064', '9005065', '9005066', '9005067', '9005068', '9005069', '90050610', '90050611', '90050612', '90050613', '90050614', '90050615', '90050616', '90050617', '90050618', '90050619', '9005321', '9005322', '9005323', '9005324', '9005325', '9005326', '9005327', '9005328', '900533', '9005341', '9005342', '9005351', '9005352', '9005353', '9005354', '900536', '900537', '900538', '9005391', '9005392', '9005393', '9005394', '9005395', '9005396', '9005397', '900542', '900547', '900548', '9005481', '9005482', '9005483', '9005484', '9005485', '900558', '901387', '901425', '700924');
                $dhs = array('900518','900512','900515','900513','900509','900508','900514','900501','900511');
                $other_con = array('900590', '900510', '901432', '900615', '900540', '900592', '600595', '900527', '900544', '901233', '901233', '900560', '900560', '900545', '900504', '900556', '900554', '900525', '901430', '900551');
                $merge_one = array_merge($dcs, $dhs);
                $con_time_clients = array_merge($merge_one, $other_con);*/

    function layout_section($objPHPExcel, $access_code, $calls, $current_row, $current_itm){
        if(is_array($calls) && count($calls) > 0){
	        $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue("Division Name :");          
	        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($calls['0']->inv_detail);
	        $objPHPExcel->getActiveSheet()->mergeCells('A' . $current_row . ':B' . $current_row);
	        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':G' . $current_row);
	        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('division_style_top'));
	        $current_row++;
	        $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue("Access Code :");
	        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($access_code);
	        $objPHPExcel->getActiveSheet()->mergeCells('A' . $current_row . ':B' . $current_row);
	        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':G' . $current_row);
	        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('division_style_bottom'));
	        $current_row++;
	        $first_row = $current_row;
	        $call_count = 0;
            foreach($calls as $index => $call){
                $call_count++;
                PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
                if($current_row %65 == 0){
                    $objPHPExcel = $this->page_break($objPHPExcel, $current_row);
                    $current_row = $current_row + 3;
                }
                $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue($current_itm);
                $objPHPExcel->getActiveSheet()->getCell('B' . $current_row)->setValue('05-' . ($call->id + 11176));
                $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($call->inv_start);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $current_row)->applyFromArray($this->get_style_array('format_date'));
                $objPHPExcel->getActiveSheet()->getCell('D' . $current_row)->setValue($call->inv_start);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                $objPHPExcel->getActiveSheet()->getCell('E' . $current_row)->setValue($call->inv_end);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                $objPHPExcel->getActiveSheet()->getCell('F' . $current_row)->setValue($call->language);
                $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue($call->interpreter_id);    
                $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue($call->caller_name);
                $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue($call->inv_phone != 0 ? $call->inv_phone:'Caller ID Blocked');            
                $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
                $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue($call->rate_code);
                $end_start = "(E" . $current_row . "-D" . $current_row . ")*1440";
                $no_add_clients = array('18662', '186621', '186622', '186623', '186624', '186625', '186626', '186627', '186637', '186632', '186642', '186633', '186643', '186644', '186645', '186646', '186647', '186650', '186653', '186656', '18670', '18673', '18676', '18679', '18682', '18685', '18688', '18691', '18694', '18697', '18700', '18703', '18706', '18709', '18712', '20503');
                if(in_array((string)$call->access_code, $no_add_clients)){
                    $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue("=ROUNDUP(" . $end_start . ",0)");
                } else{
                    $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue("=ROUNDUP(" . $end_start . ",0)+1");
                }
                $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue("=K" . $current_row . "*M" . $current_row);
                $objs['0']->inv_detail);
	        $objPHPExcel->getActiveSheet()->mergeCells('A' . $current_row . ':B' . $current_row);
	        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':G' . $current_row);
	        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('division_style_top'));
	        $current_row++;
	        $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue("Access Code :");
	        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($access_code);
	        $objPHPExcel->getActiveSheet()->mergeCells('A' . $current_row . ':B' . $current_row);
	        $objPHPExcel->getActiveSheet()->mergeCells('C' . $current_row . ':G' . $current_row);
	        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('division_style_bottom'));
	        $current_row++;
	        $first_row = $current_row;
	        $call_count = 0;
            foreach($calls as $index => $call){
                $call_count++;
                PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());
                if($current_row %65 == 0){
                    $objPHPExcel = $this->page_break($objPHPExcel, $current_row);
                    $current_row = $current_row + 3;
                }
                $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue($current_itm);
                $objPHPExcel->getActiveSheet()->getCell('B' . $current_row)->setValue('05-' . ($call->id + 11176));
                $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue($call->inv_start);
                $objPHPExcel->getActiveSheet()->getStyle('C' . $current_row)->applyFromArray($this->get_style_array('format_date'));
                $objPHPExcel->getActiveSheet()->getCell('D' . $current_row)->setValue($call->inv_start);
                $objPHPExcel->getActiveSheet()->getStyle('D' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                $objPHPExcel->getActiveSheet()->getCell('E' . $current_row)->setValue($call->inv_end);
                $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                $objPHPExcel->getActiveSheet()->getCell('F' . $current_row)->setValue($call->language);
                $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue($call->interpreter_id);    
                $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue($call->caller_name);
                $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue($call->inv_phone != 0 ? $call->inv_phone:'Caller ID Blocked');            
                $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
                $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue($call->rate_code);
                $end_start = "(E" . $current_row . "-D" . $current_row . ")*1440";
                $no_add_clients = array('18662', '186621', '186622', '186623', '186624', '186625', '186626', '186627', '186637', '186632', '186642', '186633', '186643', '186644', '186645', '186646', '186647', '186650', '186653', '186656', '18670', '18673', '18676', '18679', '18682', '18685', '18688', '18691', '18694', '18697', '18700', '18703', '18706', '18709', '18712', '20503');
                if(in_array((string)$call->access_code, $no_add_clients)){
                    $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue("=ROUNDUP(" . $end_start . ",0)");
                } else{
                    $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue("=ROUNDUP(" . $end_start . ",0)+1");
                }
                $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue("=K" . $current_row . "*M" . $current_row);
                $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                $objPHPExcel->getActiveSheet()->getCell('M' . $current_row)->setValue($call->rate);
                $objPHPExcel->getActiveSheet()->getCell('N' . $current_row)->setValue($call->access_code);
                $objPHPExcel->getActiveSheet()->getCell('O' . $current_row)->setValue($call->inv_detail);
                $objPHPExcel->getActiveSheet()->getCell('R' . $current_row)->setValue($call->inv_special);
                if($call->callout == 1 && $this->isTNClients($call)){
                	$shifty = clone $call;
                	$shifty->inv_start = date('Y-m-d H:i:s', strtotime($shifty->inv_start)+60);
                	$shifty->inv_phone = $shifty->callout_number;
                	$shifty->caller_name = 'LEP';
                	array_splice($calls, $index+1, 0, array($shifty));
                	$times = array(strtotime($call->link_timestamp), strtotime($call->answer_timestamp), strtotime($call->start_timestamp));
                	sort($times);
                	$objPHPExcel->getActiveSheet()->getCell('D' . $current_row)->setValue(date('Y-m-d H:i:s', $times['0']));
                    $objPHPExcel->getActiveSheet()->getStyle('D' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME3);
                }
                $current_row++;
                $current_itm++;
            }
            $current_row++;
            $sub_minutes = "=sum(K" . $first_row  . ":K" . ($current_row - 1) . ")";
            $sub_cost = "=sum(L" . $first_row  . ":L" . ($current_row - 1) . ")";
            $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue('SUBTOTAL');
            $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue($sub_minutes);
            $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue($sub_cost);
            $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
            $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row . ':L' . $current_row)->applyFromArray($this->get_style_array('total_style'));
            $current_row++;
        }
	    return array($objPHPExcel, $current_row, $con_time_clients);
    }

    function page_break($objPHPExcel, $current_row){
        $objPHPExcel->getActiveSheet()->setBreak('A' . $current_row, PHPExcel_Worksheet::BREAK_ROW);
        $current_row++;
        $objPHPExcel->getActiveSheet()->getStyle('A' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('B' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('C' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('D' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('F' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('J' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('K' . $current_row)->applyFromArray($this->get_style_array('header_style'));
        $objPHPExcel->getActiveSheet()->getStyle('L' . $current_row)->applyFromArray($this->get_style_array('header_style'));

        $objPHPExcel->getActiveSheet()->getCell('A' . $current_row)->setValue("ITEM");
        $objPHPExcel->getActiveSheet()->getCell('B' . $current_row)->setValue("JOB \nNUMBER");
        $objPHPExcel->getActiveSheet()->getStyle('B' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('C' . $current_row)->setValue("DATE");
        $objPHPExcel->getActiveSheet()->getCell('D' . $current_row)->setValue("START \nTIME");
        $objPHPExcel->getActiveSheet()->getStyle('D' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('E' . $current_row)->setValue("END \nTIME");
        $objPHPExcel->getActiveSheet()->getStyle('E' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('F' . $current_row)->setValue("LANGUAGE");
        $objPHPExcel->getActiveSheet()->getCell('G' . $current_row)->setValue("INTERPRETER \nID");
        $objPHPExcel->getActiveSheet()->getStyle('G' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('H' . $current_row)->setValue("CONTACT \nPERSON");
        $objPHPExcel->getActiveSheet()->getStyle('H' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('I' . $current_row)->setValue("CONTACT \nNUMBER");
        $objPHPExcel->getActiveSheet()->getStyle('I' . $current_row)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getCell('J' . $current_row)->setValue("RATE CODE");
        $objPHPExcel->getActiveSheet()->getCell('K' . $current_row)->setValue("MINUTES");
        $objPHPExcel->getActiveSheet()->getCell('L' . $current_row)->setValue("CHARGE");
        return $objPHPExcel;
    }

    function get_style_array($style){
        $hair_border = array('style' => PHPExcel_Style_Border::BORDER_HAIR);
        $thin_border = array('style' => PHPExcel_Style_Border::BORDER_THIN);
        $medm_border = array('style' => PHPExcel_Style_Border::BORDER_MEDIUM);
        $dobl_border = array('style' => PHPExcel_Style_Border::BORDER_DOUBLE);
        $grey_border = array('style' => PHPExcel_Style_Border::BORDER_HAIR, 'color' => array('argb' => 'C0C0C0'));
        $none_border = array('style' => PHPExcel_Style_Border::BORDER_NONE);

        $main_style = array(
            'font' => array(
                'name' => 'Arial', 
                'bold' => false, 
                'italic' => false, 
                'size' => 8), 
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 
                'wrap' => false)
        );
        //MAIN STYLE
        $all_styles = array('main_style' => $main_style);

        $all_styles['header_style'] = $main_style;
        $all_styles['header_style']['borders']['top'] = $thin_border;
        $all_styles['header_style']['borders']['bottom'] = $dobl_border;
        $all_styles['header_style']['borders']['left'] = $none_border;
        $all_styles['header_style']['borders']['right']= $none_border;

        $all_styles['duplicate_style'] = $main_style;
        $all_styles['duplicate_style']['borders']['top'] = $thin_border;
        $all_styles['duplicate_style']['borders']['bottom'] = $thin_border;
        $all_styles['duplicate_style']['borders']['left'] = $thin_border;
        $all_styles['duplicate_style']['borders']['right']= $thin_border;

        //TOTAL ROW STYLE
        $totals_template = $main_style;
        $totals_template['font']['bold'] = true;

        $all_styles['total_style'] = $totals_template;
        $all_styles['total_style']['borders']['top'] = $hair_border;
        $all_styles['total_style']['borders']['bottom'] = $thin_border;
        $all_styles['total_style']['borders']['left'] = $hair_border;
        $all_styles['total_style']['borders']['right']= $hair_border;

        $all_styles['summary_row_style'] = $totals_template;
        $all_styles['summary_row_style']['borders']['top'] 'MINUTES', 
							'L' => 'CHARGES'
						)
					)
				));
    		break;

    		case 'section_head':
    			if(is_array($data)){
    				array_merge($invoice, array(
    					array(),
    					array(
    						'merge' => array(array('A', 'B'), array('C', 'G')),
    						'style' => array('division_style_top' => array('A', 'L')),
    						'values' => array(
	    						'A' => "Division Name :",
	    						'C' => $data['division']
	    					)
    					),
    					array(
    						'merge' => array(array('A', 'B'), array('C', 'G')),
    						'style' => array('division_style_bottom' => array('A', 'L')),
    						'values' => array(
	    						'A' => "Access Code :",
	    						'C' => $data['access_code']
    						)
    					)
    				));    				
    			}
    		break;

    		case 'section_foot':
    			array_merge($invoice, array(
    				array(),
    				array(    				
	    				'style' => array('total_style' => array('J', 'L')),
	    				'format' => array('currency' => array('L')),
	    				'values' => array(
		    				'J' => 'SUBTOTAL',
		    				'K' => $data['call_count'],
		    				'L' => $data['call_count']
	    				)
	    			)
	    		));
    		break;

			case 'totals':
    			array_merge($invoice, array(
					array(
						'merge' => array(array('E', 'I')),
						'style' => array('summary_row2_style' => array('E', 'L')),
						'format' => array('currency' => array('L')),
						'values' => array(
							'E' => 'Over-the-phone Interpretation:',
							'K' => $data['minutes'],
							'L' => $data['charges']
						)
					),
					array(),
					array(
						'merge' => array(array('K', 'L'), array('H', 'L')),
						'style' => array('grand_total_style' => array('H', 'L')),
						'format' => array('currency' => array('K')),
						'values' => array(
							'I' => 'TOTAL CHARGES',
							'C' => $data['charges']
						)
					),
					array(),
					array()
				)); 
    		break;

    		case 'calls':
    			if(isset($data)){
    				foreach($data as $index => $call){
    					$start_inv = $this->getStartInv($call);
    					$end_t_inv = $this->getEndTmInv($call);
    					$minutes = $this->getMinutes($call);
    					$charges = $this->getCharges($call);
    					$caller_number = $this->getCallerId($call);
    					$date = $start_inv;
    					$item = $index + 1;
    					$job_number = '05-' . $call->job_number;
    					array_merge($invoice, array(
							'style' => array(
								'format_date' => array('C'), 
								'summary_row2_style' => array('A', 'L')
							),
							'format' => array(
								'time' => array('D', 'E'),
								'phone' => array('I'),
								'currency' => array('L')
							),
							'values' => array(
								'A' => $item,
								'B' => $job_number,
								'C' => $date,
								'D' => $start_time_inv,
								'E' => $end_time_inv,
								'F' => $call->language,
								'G' => $call->intid,
								'H' => $call->rep_name,
								'I' => $caller_number,
								'J' => $call->rate_code,
								'K' => $minutes,
								'L' => $charges,
								'M' => $call->rate,
								'N' => $call->access_code
							)
						));
					}
    			}    			
    		break;

    		default:
    			while ($data > 0){
    				$data--;
    				array_merge($invoice, array());
    			}
    		break;
    	}
    	return $invoice;
    }
}

    