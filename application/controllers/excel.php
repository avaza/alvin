<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Excel extends Alvin_Controller {
	
	function __construct(){
        parent::__construct();
        	$this->load->database();
        	$this->load->library('session');
        	$this->load->library('PHPExcel');
    }

    function export_all_JOL($start=NULL, $end=NULL){
    	$this->load->model('call_data');
    	$this->firephp->log('model loaded');
    	//ESTABLISH CURRENT DATE AND SELECT RECORDS
    	if($start == NULL){
	    	$startunix = mktime(0, 0, 0, 3, 31, 2013);
	    	$start = date('Y-m-d H:i:s', $startunix);
			$this->firephp->log($start);
	    	$endunix = mktime(0, 0, 0, 5, 1, 2013);
	    	$end = date('Y-m-d H:i:s', $endunix);
	    	$this->firephp->log($end);
	    }
    	$records = $this->call_data->get_all_jol_data_se($start, $end);
    	$this->firephp->log($records);
    	//CREATE EXCEL SHEET WITH TODAYS RECORDS
    	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);


		//CREATE EXCEL OBJECT
		$objPHPExcel = new PHPExcel();
		//SET TITLE
		$mth_yr = date('F-Y');
		$xltitle = 'ALL-' . $mth_yr;
		
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
		$r = 1;
		foreach($records as $call_row){
			PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

			$r++;
			$div_name = $this->call_data->get_division_name($call_row->access_code);
			foreach($div_name as $div){
				$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue($div->division);
			}
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue($call_row->start_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r)->applyFromArray($format_date);
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue($call_row->access_code);
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($call_row->answer_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue($call_row->client_id);
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue($call_row->caller_id_number);
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
			$objPHPExcel->getActiveSheet()->getCell('G' . $r)->setValue($call_row->rep_name);
			$objPHPExcel->getActiveSheet()->getCell('H' . $r)->setValue($call_row->language);
			$objPHPExcel->getActiveSheet()->getCell('I' . $r)->setValue($call_row->intid);
			$objPHPExcel->getActiveSheet()->getCell('J' . $r)->setValue($call_row->intname);
			$objPHPExcel->getActiveSheet()->getCell('K' . $r)->setValue($call_row->start_time);
			$objPHPExcel->getActiveSheet()->getStyle('K' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue($call_row->end_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('M' . $r)->setValue($call_row->drop);
			$objPHPExcel->getActiveSheet()->getCell('N' . $r)->setValue("x" . $call_row->r_ext . "/ID:" . $call_row->intid);//Notes
			$objPHPExcel->getActiveSheet()->getCell('O' . $r)->setValue("");//Enter Incident Code
			$objPHPExcel->getActiveSheet()->getCell('P' . $r)->setValue("");//Incident Description
			$objPHPExcel->getActiveSheet()->getCell('Q' . $r)->setValue("=K" . $r . "-C" . $r);
			$objPHPExcel->getActiveSheet()->getStyle('Q' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);
			$objPHPExcel->getActiveSheet()->getCell('R' . $r)->setValue("=L" . $r . "-K" . $r);
			$objPHPExcel->getActiveSheet()->getStyle('R' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);
			$objPHPExcel->getActiveSheet()->getCell('U' . $r)->setValue($call_row->co_num);
			$objPHPExcel->getActiveSheet()->getCell('V' . $r)->setValue($call_row->specialf);
  
		}
		$return = " Write to Excel2007 format<br>";

		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save("/var/www/" . $ext . "-" . date('n-j-Y') . "data.xlsx");

		
		$server = '192.168.1.100';	
		$ftp_user_name = 'administrate';
		$ftp_user_pass = 'a52Vaza09@';
		$dest = 'NEW/' . $ext . '-' . date('n-j-Y') . 'data.xlsx';
		$source = "/var/www/" . $ext . "-" . date('n-j-Y') . "data.xlsx";
		$mode = FTP_BINARY;

		if(chmod($source, 0777) !== false) {
			$return .= "$source source chmoded successfully to 777\n";
		} else {
			$return .= "source could not chmod $source\n";
		}

		$connection = ftp_connect($server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		if (!$connection || !$login) { 
			die('<br>Connection attempt failed!'); 
		}
		$upload = ftp_put($connection, $dest, $source, $mode);
		if (!$upload) { 
			$return .= '<br>FTP upload failed!'; 
		}else {
			unlink($source);
			$return .= '<br>File Cleared.';
		}
		ftp_close($connection);	
																	
		return $return;															
    }

    function get_my_JOL(){
    	//ESTABLISH IDENTITY
    	
    	$myext = $this->session->userdata('ext');

    	$this->load->model('call_data','', TRUE);

    	//ESTABLISH CURRENT DATE AND SELECT RECORDS
    	   $today = date("Y-m-d 00:00:00");// current date
    	 $add_day = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
    	$tomorrow = date('Y-m-d H:i:s', $add_day);// tomorrow's date

    	$records = $this->call_data->get_my_jol_data($today, $tomorrow);

    	//CREATE EXCEL SHEET WITH TODAYS RECORDS
    	$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod);


		//CREATE EXCEL OBJECT
		$objPHPExcel = new PHPExcel();
		//SET TITLE
		$mth_yr = date('F-Y');
		$xltitle = $myext . '-' . $mth_yr;
		
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

		$ext = $this->session->userdata('ext');
		$r = 1;
		foreach($records as $call_row){
			PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

			$r++;
			$div_name = $this->call_data->get_division_name($call_row->access_code);
			foreach($div_name as $div){
				$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue($div->division);
			}
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue($call_row->start_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r)->applyFromArray($format_date);
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue($call_row->access_code);
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($call_row->answer_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue($call_row->client_id);
			// if statement to get phone number inbound/outbound

			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue($call_row->caller_id_number);

			//
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
			$objPHPExcel->getActiveSheet()->getCell('G' . $r)->setValue($call_row->rep_name);
			$objPHPExcel->getActiveSheet()->getCell('H' . $r)->setValue($call_row->language);
			$objPHPExcel->getActiveSheet()->getCell('I' . $r)->setValue($call_row->intid);
			$objPHPExcel->getActiveSheet()->getCell('J' . $r)->setValue($call_row->intname);
			$objPHPExcel->getActiveSheet()->getCell('K' . $r)->setValue($call_row->start_time);
			$objPHPExcel->getActiveSheet()->getStyle('K' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue($call_row->end_timestamp);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME8);
			$objPHPExcel->getActiveSheet()->getCell('M' . $r)->setValue($call_row->drop);
			$objPHPExcel->getActiveSheet()->getCell('N' . $r)->setValue("x" . $call_row->r_ext . "/ID:" . $call_row->intid);//Notes
			$objPHPExcel->getActiveSheet()->getCell('O' . $r)->setValue("");//Enter Incident Code
			$objPHPExcel->getActiveSheet()->getCell('P' . $r)->setValue("");//Incident Description
			$objPHPExcel->getActiveSheet()->getCell('Q' . $r)->setValue("=K" . $r . "-C" . $r);
			$objPHPExcel->getActiveSheet()->getStyle('Q' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);
			$objPHPExcel->getActiveSheet()->getCell('R' . $r)->setValue("=L" . $r . "-K" . $r);
			$objPHPExcel->getActiveSheet()->getStyle('R' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME5);

  
		}
		$return = " Write to Excel2007 format<br>";

		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save("/var/www/" . $ext . "-" . date('n-j-Y') . ".xlsx");

		
		$server = '192.168.1.100';	
		$ftp_user_name = 'administrate';
		$ftp_user_pass = 'a52Vaza09@';
		$dest = 'NEW/' . $ext . '-' . date('n-j-Y') . '.xlsx';
		$source = "/var/www/" . $ext . "-" . date('n-j-Y') . ".xlsx";
		$mode = FTP_BINARY;

		if(chmod($source, 0777) !== false) {
			$return .= "$source source chmoded successfully to 777\n";
		} else {
			$return .= "source could not chmod $source\n";
		}

		$connection = ftp_connect($server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		if (!$connection || !$login) { 
			die('<br>Connection attempt failed!'); 
		}
		$upload = ftp_put($connection, $dest, $source, $mode);
		if (!$upload) { 
			$return .= '<br>FTP upload failed!'; 
		}else {
			unlink($source);
			$return .= '<br>File Cleared.';
		}
		ftp_close($connection);	
																	
		return $return;															
    }

    function get_header(){
    	$objPHPexcel = PHPExcel_IOFactory::load('c:/xampp/htdocs/test.xlsx');
		$objWorksheet = $objPHPexcel->getActiveSheet();
		var_dump($objWorksheet->getHeaderFooter()->getOddFooter());
		var_dump($objWorksheet->getHeaderFooter()->getEvenFooter());
		var_dump($objWorksheet->getHeaderFooter()->getOddHeader());
		var_dump($objWorksheet->getHeaderFooter()->getEvenHeader());
	}

    function get_cell_width(){
    	$objPHPexcel = PHPExcel_IOFactory::load('c:/template.xlsx');
		$objWorksheet = $objPHPexcel->getActiveSheet();
		var_dump($objWorksheet->getStyle('B1')->getNumberFormat()->getFormatCode());
		var_dump($objWorksheet->getStyle('B1')->getNumberFormat()->getHashCode());
		//var_dump($objWorksheet->getStyle('B1')->getNumberFormat()->getFormatCode());

		$return .= '<br>';
    }

	function create_invoice(){
		
		$this->load->model('invc_all','', TRUE);    
        header('Content-Type: application/x-json; charset=utf-8');
		$client_and_time = $this->invc_all->set_invoice_data();
		
			$client_id = $client_and_time['client_id'];
			$start = $client_and_time['start'];
			$finish = $client_and_time['finish'];

		// Initiate cache
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
		$cacheSettings = array( 'memoryCacheSize' => '32MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		//$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_serialized;
		//PHPExcel_Settings::setCacheStorageMethod($cacheMethod);

		$return = '';
 		//CREATE EXCEL OBJECT
		$objPHPExcel = new PHPExcel();
		//SET TITLE
		$mth_yr = date('F-Y');
		$xltitle = $client_id . '-' . $mth_yr;
		
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
		$objPageSetup->setFitToWidth(1);
		$objPageSetup->setFitToHeight(0);
		$objPHPExcel->getActiveSheet()->setPageSetup($objPageSetup);
		
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
		$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
		$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(9);
		
		//STYLE ARRAYS

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

		//TOTAL ROW STYLE
		$total_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_HAIR),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_HAIR),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_HAIR)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		$summary_row_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_HAIR),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_HAIR)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		$summary_row2_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_HAIR),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_HAIR)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		$grand_total_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_HAIR),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_HAIR)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		$bottom_report_header_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_MEDIUM)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		$bottom_report_cell_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_THIN)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		//SEPERATION ROW STYLE
		$division_style_top = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => 'C0C0C0')),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_NONE),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_NONE),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_NONE)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);
		
		$division_style_bottom = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => true,
				'italic' => false,
				'size'   => 7
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_NONE),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_HAIR, 'color' => array('argb' => 'C0C0C0')),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_NONE),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_NONE)

			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);

		//COLUMN HEADER STYLE
		$header_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => false,
				'italic' => false,
				'size'   => 8
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_DOUBLE),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_NONE),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_NONE)
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'       => false
			)
		);
		
		//DUPLICATE CELL STYLE
		$duplicate_style = array(
			'font' => array(
				'name'   => 'Arial',
				'bold'   => false,
				'italic' => false,
				'size'   => 8
			),
			'borders' => array(
				'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'left'   => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				'right'  => array('style' => PHPExcel_Style_Border::BORDER_THIN)
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
		$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('D1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('F1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('G1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('H1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('I1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('J1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('K1')->applyFromArray($header_style);
		$objPHPExcel->getActiveSheet()->getStyle('L1')->applyFromArray($header_style);
		
		
		//APPLY CELL FORMATTING


		//END SHEET LAYOUT

		//
		//BEGIN DATA INSERT
		//

		//SET HEADER/FOOTER DATA
		//GET CLIENT DATA
		$client_data = $this->invc_all->get_client_data($client_id);
			   $account_num  = 'Account Number: ';
			   $account_num .= $client_data['account_number'];
			     	$client  = 'Client ID: ';
			     	$client .= $client_id;
			   $client_name  = $client_data['agency'];
			   
			   if($client_data['invoice']=='Avaza'){
			   		$billed_from = 'Avaza Language Services Corp.';
			   }
			   else if($client_data['invoice']=='OCI'){
			   		$billed_from = 'Open Communications International';
			   }

		$statement_type  = 'Over The Phone';
		$statement_type .= ' Interpretation Statement';
		
		//CONCAT DATA
		   $client_data = $account_num . "\n" . $client . "\n" . $client_name;
		$statement_data = $billed_from . "\n" . $statement_type;

		//SET HEADER/FOOTER LAYOUT
		   //header
		   $left_header = '&L&"Arial,Bold"&8' . $client_data;
		 $center_header = '';
		  $right_header = '&R&"Arial,Bold"&8' . $statement_data;
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

		
		//GET ACCESS CODES
		$access_codes = $this->invc_all->get_billing_access_codes($client_id, $start, $finish);
  		
  		//BEGIN SECTIONS
  		$r=3;
  		$i=1;
  		$array_count = 0;
		//SEPERATE BY ACCESS CODE AND CREATE INVOICE
		foreach($access_codes as $ac){
			$access_code = $ac->access_code;
			$division_info = $this->invc_all->get_client_info($access_code);
			$div_name = $division_info['division'];
			$div_ac = $division_info['access_code'];			
			
			//INSERT DIVISION NAME
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("Division Name :");
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($div_name);
			//MERGE DIVISION NAME CELLS
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $r . ':B' . $r);
			$objPHPExcel->getActiveSheet()->mergeCells('C' . $r . ':G' . $r);
			//SET STYLE FOR DIVISION NAME CELLS
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r . ':L' . $r)->applyFromArray($division_style_top);
			
			//INSERT ACCESS CODE
			$r++;
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("Access Code :");
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($div_ac);
			//MERGE ACCESS CODE CELLS
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $r . ':B' . $r);
			$objPHPExcel->getActiveSheet()->mergeCells('C' . $r . ':G' . $r);
			//SET STYLE FOR ACCESS CODE CELLS
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r . ':L' . $r)->applyFromArray($division_style_bottom);

			
			$r++;
			
			$call_data_by_ac = $this->invc_all->get_calls_by_ac($access_code, $start, $finish);
			//ADD CALL DETAIL BY ACCESS CODE
			$beg_sub = count($call_data_by_ac);
			
			foreach($call_data_by_ac as $call_row){

					$inc = base_url();
					
					
					//ADD MINUTE TO ENDTIME
					PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

					$r++;
					if($r %65 == 0){
  						$objPHPExcel->getActiveSheet()->setBreak('A' . $r, PHPExcel_Worksheet::BREAK_ROW);
  						$r = $r +1;

  						$objPHPExcel->getActiveSheet()->getStyle('A' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('G' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('H' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('I' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('J' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('K' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->applyFromArray($header_style);


  						$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("ITEM");
						$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue("JOB \nNUMBER");
						$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue("DATE");
						$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue("START \nTIME");
						$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue("END \nTIME");
						$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue("LANGUAGE");
						$objPHPExcel->getActiveSheet()->getCell('G' . $r)->setValue("INTERPRETER \nID");
						$objPHPExcel->getActiveSheet()->getStyle('G' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('H' . $r)->setValuccess_codes = $this->invc_all->get_billing_access_codes($client_id, $start, $finish);
  		
  		//BEGIN SECTIONS
  		$r=3;
  		$i=1;
  		$array_count = 0;
		//SEPERATE BY ACCESS CODE AND CREATE INVOICE
		foreach($access_codes as $ac){
			$access_code = $ac->access_code;
			$division_info = $this->invc_all->get_client_info($access_code);
			$div_name = $division_info['division'];
			$div_ac = $division_info['access_code'];			
			
			//INSERT DIVISION NAME
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("Division Name :");
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($div_name);
			//MERGE DIVISION NAME CELLS
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $r . ':B' . $r);
			$objPHPExcel->getActiveSheet()->mergeCells('C' . $r . ':G' . $r);
			//SET STYLE FOR DIVISION NAME CELLS
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r . ':L' . $r)->applyFromArray($division_style_top);
			
			//INSERT ACCESS CODE
			$r++;
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("Access Code :");
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue($div_ac);
			//MERGE ACCESS CODE CELLS
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $r . ':B' . $r);
			$objPHPExcel->getActiveSheet()->mergeCells('C' . $r . ':G' . $r);
			//SET STYLE FOR ACCESS CODE CELLS
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r . ':L' . $r)->applyFromArray($division_style_bottom);

			
			$r++;
			
			$call_data_by_ac = $this->invc_all->get_calls_by_ac($access_code, $start, $finish);
			//ADD CALL DETAIL BY ACCESS CODE
			$beg_sub = count($call_data_by_ac);
			
			foreach($call_data_by_ac as $call_row){

					$inc = base_url();
					
					
					//ADD MINUTE TO ENDTIME
					PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_AdvancedValueBinder());

					$r++;
					if($r %65 == 0){
  						$objPHPExcel->getActiveSheet()->setBreak('A' . $r, PHPExcel_Worksheet::BREAK_ROW);
  						$r = $r +1;

  						$objPHPExcel->getActiveSheet()->getStyle('A' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('G' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('H' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('I' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('J' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('K' . $r)->applyFromArray($header_style);
						$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->applyFromArray($header_style);


  						$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue("ITEM");
						$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue("JOB \nNUMBER");
						$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue("DATE");
						$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue("START \nTIME");
						$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue("END \nTIME");
						$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue("LANGUAGE");
						$objPHPExcel->getActiveSheet()->getCell('G' . $r)->setValue("INTERPRETER \nID");
						$objPHPExcel->getActiveSheet()->getStyle('G' . $r)->getAlignment()->setWrapText(true);
						$objPHPExcel->getActiveSheet()->getCell('H' . $r)->setValuL" . $begin  . ":L" . $end . ")";
			$objPHPExcel->getActiveSheet()->getCell('J' . $r)->setValue('SUBTOTAL');
			$objPHPExcel->getActiveSheet()->getCell('K' . $r)->setValue($sub_minutes);
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue($sub_cost);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle('J' . $r . ':L' . $r)->applyFromArray($total_style);
			$r++;

			
		}	
			$end++;
			
			//GRAND TOTAL
			$total_sum = '=SUMIF(J:J,"SUBTOTAL",K1:K' . $end . ')';
			$cost_sum = '=SUMIF(J:J,"SUBTOTAL",L1:L' . $end . ')';
			$r = $r+1;
			$objPHPExcel->getActiveSheet()->mergeCells('A' . $r . ':C' . $r);
			$objPHPExcel->getActiveSheet()->getCell('A' . $r)->setValue('SUMMARY');
			$objPHPExcel->getActiveSheet()->getCell('K' . $r)->setValue('MINUTES');
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue('CHARGES');
			$objPHPExcel->getActiveSheet()->getStyle('A' . $r . ':L' . $r)->applyFromArray($summary_row_style);
			$r = $r+1;
			$objPHPExcel->getActiveSheet()->mergeCells('E' . $r . ':I' . $r);
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('Over-the-phone Interpretation:');
			$objPHPExcel->getActiveSheet()->getCell('K' . $r)->setValue($total_sum);
			$objPHPExcel->getActiveSheet()->getCell('O1')->setValue($total_sum);
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue($cost_sum);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r . ':L' . $r)->applyFromArray($summary_row2_style);
			$r = $r+2;
			$objPHPExcel->getActiveSheet()->getCell('I' . $r)->setValue('TOTAL CHARGES');
			$objPHPExcel->getActiveSheet()->getCell('L' . $r)->setValue($cost_sum);
			$objPHPExcel->getActiveSheet()->getStyle('L' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle('I' . $r . ':L' . $r)->applyFromArray($grand_total_style);
			//BOTTOM LANGUAGE AND ACCESS CODE TOTALS
			$r = $r+2;
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue('Access Code');
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('Minutes');
			$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('Calls');
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('% Total');
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('Charges');
			$objPHPExcel->getActiveSheet()->getStyle('B' . $r . ':F' . $r)->applyFromArray($bottom_report_header_style);
			$r = $r+1;
			$tot = $r;
		foreach($access_codes as $ac){
			$acde = $ac->access_code;
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue($acde);
			$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('=SUMIF(N:N,B' . $r . ',K:K)');
			$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('=COUNTIF(N:N,B' . $r . ')');
			$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('=C' . $r . '/O1');
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('=SUMIF(N:N,B' . $r . ',L:L)');
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->applyFromArray($bottom_report_cell_style);
			$bottom = $r;
			$r++;
		}
		$rminus = $r - 1;
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue('TOTALS');
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('=SUM(C' . $tot . ':C' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('=SUM(D' . $tot . ':D' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('=SUM(E' . $tot . ':E' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('=SUM(F' . $tot . ':F' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getStyle('B' . $r . ':F' . $r . '')->applyFromArray($bottom_report_header_style);


		$r = $r+2;
		$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue('Language');
		$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('Minutes');
		$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('Calls');
		$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('% Total');
		$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('Charges');
		$objPHPExcel->getActiveSheet()->getStyle('B' . $r . ':F' . $r)->applyFromArray($bottom_report_header_style);
		$r = $r+1;
		$languages = $this->invc_all->get_languages_by_client($client_id, $start, $finish);
		$tot = $r;
		foreach($languages as $language){
			$lng = $language->language;
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue($lng);
			$objPHPExcel->getActiveSheet()->getStyle('B' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('=SUMIF(F:F,B' . $r . ',K:K)');
			$objPHPExcel->getActiveSheet()->getStyle('C' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('=COUNTIF(F:F,B' . $r . ')');
			$objPHPExcel->getActiveSheet()->getStyle('D' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('=C' . $r . '/O1');
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->applyFromArray($bottom_report_cell_style);
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('=SUMIF(F6:F' . $bottom . ',B' . $r . ',L:L)');
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
			$objPHPExcel->getActiveSheet()->getStyle('F' . $r)->applyFromArray($bottom_report_cell_style);
			$r++;		
			
		}
		$rminus = $r - 1;		
		$totminus = $tot - 1;
			$objPHPExcel->getActiveSheet()->getCell('B' . $r)->setValue('TOTALS');
			$objPHPExcel->getActiveSheet()->getCell('C' . $r)->setValue('=SUM(C' . $tot . ':C' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getCell('D' . $r)->setValue('=SUM(D' . $tot . ':D' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getCell('E' . $r)->setValue('=SUM(E' . $tot . ':E' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getStyle('E' . $r)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
			$objPHPExcel->getActiveSheet()->getCell('F' . $r)->setValue('=SUM(F' . $tot . ':F' . $rminus . ')');
			$objPHPExcel->getActiveSheet()->getStyle('B' . $r . ':F' . $r . '')->applyFromArray($bottom_report_header_style);
		
		$objPageSetup->setPrintArea('A1:L' . $r);

		//END DATA INSERT
		
		//OUTPUT DATA TO FILE
		$return .= " Write to Excel2007 format<br>";

		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->setPreCalculateFormulas(false);
		$filename = $client_id . "-" . $start . '-' .  $finish . ".xlsx";
		$objWriter->save("/var/www/" . $filename);

		
		$server = '192.168.1.100';	
		$ftp_user_name = 'invoice';
		$ftp_user_pass = 'a52Vaza09@';
		$dest = $filename;
		$source = "/var/www/" . $filename;
		$mode = FTP_BINARY;

		if(chmod($source, 0777) !== false) {
			$return .= "$source source chmoded successfully to 777\n";
		} else {
			$return .= "source could not chmod $source\n";
		}

		$connection = ftp_connect($server);
		$login = ftp_login($connection, $ftp_user_name, $ftp_user_pass);
		if (!$connection || !$login) { 
			die('<br>Connection attempt failed!'); 
		}
		$upload = ftp_put($connection, $dest, $source, $mode);
		if (!$upload) { 
			$return .= '<br>FTP upload failed!'; 
		}else {
			unlink($source);
			$return .= '<br>File Cleared.';
		}
		ftp_close($connection);
		return $return;
	}
	
}