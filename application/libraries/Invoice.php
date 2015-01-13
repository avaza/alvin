<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Invoice Class
 *
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Josh Murray
 */
class Invoice{

    public $invoice;                 // the invoice object
    protected $_ci;                  // CodeIgniter instance
    protected $gets;                 // collected for invoice
    protected $objPHPExcel;          // excel library object
    protected $objPageSetup;         // excel library settings

    function __construct($params = array(FALSE, FALSE)){
        $this->_ci = & get_instance();
        $this->_ci->load->library('PHPExcel');
        $this->_ci->load->library('ftp');
        $this->_ci->load->database();
        return $this;
    }

      //CREATE AN INVOICE FOR A CLIENT
    function createInvoiceForInvoiceCode($invoice_code, $client_id){
        $this->gets->done = FALSE;
        $this->gets->invc_code = $invoice_code;
        $this->gets->client_id = $client_id;
        $this->setClientDataForInvoiceCode();
        $this->setThisClientsInvoiceLayout();
        $this->compileClientInvoiceDetails();
        $this->generateInvoiceDetailReport();
        return $this->gets->done;
    }

    //PULL CALL DATA FROM DATABASE
    function setClientDataForInvoiceCode(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('inv_code', $this->gets->invc_code);
        $this->_ci->db->where('client_id', $this->gets->client_id);
        $this->_ci->db->where('drop', 0);
        $query = $this->_ci->db->get('call_records');
        if($query->result()){
            foreach($query->result() as $call){
                $this->gets->data[] = $call;
            }
        } else{
            $this->gets->data = array();
        }
        return $this;
    }
    //END PULL CALL DATA FROM DATABASE

    //GET STANDARD OR SPECIAL LAYOUT
    function setThisClientsInvoiceLayout(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('client_id', $this->gets->client_id);
        $query = $this->_ci->db->get('client_custom');
        if($query->result()){
            foreach($query->result() as $form){
                $this->gets->columns = $this->generateClientInvoiceLayout($form);
                $this->gets->special_header = $form->inv_header;
            }            
        } else{
            $this->generateClientInvoiceLayout();
        }
        return $this;
    }

    function generateClientInvoiceLayout($form = null){
        $layout = array(
            'frst_columns' => array('item', 'job', 'date', 'start', 'end', 'language', 'interpreter'),
            'last_columns' => array('rate_code', 'minutes', 'charges'),
            'separated' => TRUE,
            'split_report' => FALSE,
            'invoice_sections' => NULL
        );
        if(isset($form)){
            $layout['data_columns'] = strlen($form->inv_column) > 0 ? json_decode($form->inv_column):array('caller_name', 'inv_phone');
            $layout['separated'] = $form->separated == 0 ? FALSE:TRUE;
            $layout['invoice_sections'] = strlen($form->invoice_sections) > 0 ? json_decode($form->invoice_sections):NULL;
        } else{
            $layout['data_columns'] = array('caller_name', 'inv_phone');
        }
        $this->gets->layout = $layout;
        return $this;
    }
    //END GET STANDARD OR SPECIAL LAYOUT

    //PREPARE SHEET AND SETUP FOR GENERATE
    function compileClientInvoiceDetails(){
        $this->setupExcelObject();
        $this->setupPageMargins();
        $this->prepareIteration();
        return $this;
    }

    function setupExcelObject(){
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize' => '256MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objPHPExcel->getActiveSheet()->setTitle('Invoice');
    }

    function setupPageMargins(){
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setTop(0.75);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setRight(0.17);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setLeft(0.17);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setBottom(0.39);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setHeader(0.18);
        $this->objPHPExcel->getActiveSheet()->getPageMargins()->setFooter(0.18);
    }


    function prepareIteration(){
        $this->gets->columns = array_merge($this->gets->layout['frst_columns'], $this->gets->layout['data_columns'], $this->gets->layout['last_columns']);
        $sets = array_slice(range('A', 'Z'), 0, count($this->gets->columns));
        $this->gets->column_data = array_combine($sets, $this->gets->columns);
        $this->gets->current_itm = 1;
        $this->gets->current_row = 1;
    }   
    //END PREPARE SHEET AND SETUP FOR GENERATE

    //ITERATE CALLS AND GENERATE INVOICE
    function generateInvoiceDetailReport(){
        $this->sectionAndSeparateAllCalls();
        $this->applyCallLayoutAndGenerate();
        $this->insertGrandTotal();
        $this->insertCallReport();
        $this->finishPagesSetup();
        $this->generateInvoiceWithinFolder();
        //$this->generatePDFCopyWithinFolder();
    }

    function sectionAndSeparateAllCalls(){
        if(isset($this->gets->layout['invoice_sections'])){
            $sections = $this->gets->layout['invoice_sections'];
            foreach($sections as $section_name => $access_codes){
                foreach($this->gets->data as $call){
                    if($this->gets->layout['separated'] === TRUE){
                        if(in_array($call->access_code, $access_codes)){
                            $iteration_array[$section_name][$call->access_code][] = $call;
                        }                        
                    } else{
                        if(in_array($call->access_code, $access_codes)){
                            $iteration_array[$section_name]['calls'][] = $call;
                        }
                    }                    
                }
            }
        } else if($this->gets->layout['separated'] == TRUE){
            foreach($this->gets->data as $call){
                $iteration_array['calls'][$call->access_code][] = $call;
            }
        } else{
            foreach($this->gets->data as $call){
                $iteration_array['calls']['calls'][] = $call;
            }
        }
        $this->gets->iteration_array = $iteration_array;
        return $this;
    }

    function applyCallLayoutAndGenerate(){
        $this->gets->current_section = 1;
        $this->insertHeadRow();
        //COLUMN/ROW SIZE AND VISIBILITY
		foreach($this->gets->column_dimensions as $col => $dim){
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($col)->setWidth($dim);
		}
		foreach(range('W', 'Z') as $l){
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($l)->setVisible(FALSE);
		}
        $this->gets->current_row++;
        foreach($this->gets->iteration_array as $section){
            $this->gets->section_start = $this->gets->current_row;
            $this->gets->section_starts[$this->gets->current_section] = $this->gets->current_row;
            foreach($section as $separation){
                $this->insertSeparationHead("Division Name :", $separation['0']->inv_detail);
                $this->insertSeparationHead("Access Code :", $separation['0']->access_code);
                $this->gets->separation_start = $this->gets->current_row;
                $call_count = 0;
                foreach($separation as $call){
                    if($this->gets->client_id != 123456){
                        $this->addThisToFinalReport($this->gets->current_section, $call->access_code, $call->language);
                        $this->insertCallRow($call);
                        if($this->isAPageBreakRow()){
                            $this->breakExcelPage();
                        } else{
                            $this->gets->current_row++;
                        }
                        $this->gets->current_itm++;
                        $call_count++;
                    } else{
                        if($call->callout == 0){
                           $this->addThisToFinalReport($this->gets->current_section, $call->access_code, $call->language);
                            $this->insertCallRow($call);
                            if($this->isAPageBreakRow()){
                                $this->breakExcelPage();
                            } else{
                                $this->gets->current_row++;
                            }
                            $this->gets->current_itm++;
                            $call_count++; 
                        }
                    }                    
                }
                $this->gets->section_count[$this->gets->current_section] = $call_count;
                $this->insertSeparationFoot();
            }
            $this->gets->section_ends[$this->gets->current_section] = $this->gets->current_row;
            $this->insertSectionFoot();
            $this->gets->current_section++;
        }
    }

    function addThisToFinalReport($section, $access_code, $language){
        if(!in_array($access_code, $this->gets->access_codes[$section])){
            $this->gets->access_codes[$section][] = $access_code;
        }
        if(!in_array($language, $this->gets->languages[$section])){
            $this->gets->languages[$section][] = $language;
        }
        return $this;
    }

    //COMMON FUNCTIONS
    function getCell($letter){
        $cell = $letter . $this->gets->current_row;
        return $cell;
    }

    function getCellFor($column){
        $letter = $this->getColumnLetterFor($column);
        return $this->getCell($letter);
    }

    function setCellValue($cell, $value, $format = FALSE){
        $this->objPHPExcel->getActiveSheet()->getCell($cell)->setValue($value);
        if($format !== FALSE){
            $this->setCellStyle($cell, $format);
        }
        return $this;
    }

    function setCellStyle($cell, $type = 'minutes'){
        switch ($type) {
            case 'date':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('MM/DD/YY');
                break;
            case 'start':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode("H:MM");
                break;
            case 'end':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode("H:MM");
                break;
            case 'inv_phone':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode("[<=9999999]###\-####;#\(###\)\ ###\-####");
                break;
            case 'charges':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE);
                break;
            case 'percent':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
                break;
            case 'wrap':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment()->setWrapText(true);
                break;
            case 'header':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('header_style'));
                break;
            case 'minutes':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('main_style'));
                break;
            case 'separation_head_1':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('division_style_top'));
                break;
            case 'separation_head_2':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('division_style_bottom'));
                break;
            case 'subtotal':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('total_style'));
                break;
            case 'summary_1':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('summary_row_style'));
                break;
            case 'summary_2':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('summary_row2_style'));
                break;
            case 'grand_total':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('grand_total_style'));
                break;
            case 'report_head':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('bottom_report_header_style'));
                break;
            case 'report_foot':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('bottom_report_footer_style'));
                break;
            case 'report_cell':
                $this->objPHPExcel->getActiveSheet()->getStyle($cell)->applyFromArray($this->get_style_array('bottom_report_cell_style'));
                break;               
            case 'merge':
                $this->objPHPExcel->getActiveSheet()->mergeCells($cell);
                break;
        }
        return $this;
    }

    function isAPageBreakRow(){
        if($this->gets->current_row %65 == 0){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function breakExcelPage($head = TRUE){
        if($this->gets->layout['separated'] === TRUE){
            if($this->gets->current_row != 1){
                $this->objPHPExcel->getActiveSheet()->setBreak($this->getCell('A'), PHPExcel_Worksheet::BREAK_ROW);
                $this->gets->current_row++;
            }
            if($head === TRUE){
                $this->insertHeadRow();
                $this->gets->current_row = $this->gets->current_row + 3;
            }
        } else{
            $this->gets->current_row++;
        }
        return $this;
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

        $totals_template = $main_style;
        $totals_template['font']['bold'] = true;

        $all_styles['total_style'] = $totals_template;
        $all_styles['total_style']['borders']['top'] = $hair_border;
        $all_styles['total_style']['borders']['bottom'] = $thin_border;
        $all_styles['total_style']['borders']['left'] = $hair_border;
        $all_styles['total_style']['borders']['right']= $hair_border;

        $all_styles['summary_row_style'] = $totals_template;
        $all_styles['summary_row_style']['borders']['top'] = $medm_border;
        $all_styles['summary_row_style']['borders']['bottom'] = $medm_border;
        $all_styles['summary_row_style']['borders']['left'] = $hair_border; 
        $all_styles['summary_row_style']['borders']['right'] = $hair_border;

        $all_styles['summary_row2_style'] = $totals_template;
        $all_styles['summary_row2_style']['borders']['bottom'] = $medm_border;
        $all_styles['summary_row2_style']['borders']['left'] = $hair_border;
        $all_styles['summary_row2_style']['borders']['right'] = $hair_border;

        $all_styles['grand_total_style'] = $totals_template;
        $all_styles['grand_total_style']['borders']['top'] = $medm_border;
        $all_styles['grand_total_style']['borders']['bottom'] = $dobl_border;
        $all_styles['grand_total_style']['borders']['left'] = $hair_border;
        $all_styles['grand_total_style']['borders']['right'] = $hair_border;
        $all_styles['grand_total_style']['font']['size'] = 14;

        $all_styles['bottom_report_header_style'] = $totals_template;
        $all_styles['bottom_report_header_style']['borders']['top'] = $thin_border;
        $all_styles['bottom_report_header_style']['borders']['bottom'] = $thin_border;
        $all_styles['bottom_report_header_style']['borders']['left'] = $thin_border;
        $all_styles['bottom_report_header_style']['borders']['right'] = $thin_border;

        $all_styles['bottom_report_cell_style'] = $totals_template;
        $all_styles['bottom_report_cell_style']['borders']['top'] = $hair_border;
        $all_styles['bottom_report_cell_style']['borders']['bottom'] = $hair_border;
        $all_styles['bottom_report_cell_style']['borders']['left'] = $thin_border;
        $all_styles['bottom_report_cell_style']['borders']['right'] = $thin_border;

        $all_styles['division_style_top'] = $totals_template;
        $all_styles['division_style_top']['borders']['top'] = $grey_border;
        $all_styles['division_style_top']['borders']['bottom'] = $none_border;
        $all_styles['division_style_top']['borders']['left'] = $none_border;
        $all_styles['division_style_top']['borders']['right'] = $none_border;
        $all_styles['division_style_top']['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        
        $all_styles['division_style_bottom'] = $totals_template;
        $all_styles['division_style_bottom']['borders']['top'] = $none_border;
        $all_styles['division_style_bottom']['borders']['bottom'] = $grey_border;
        $all_styles['division_style_bottom']['borders']['left'] = $none_border;
        $all_styles['division_style_bottom']['borders']['right'] = $none_border;
        $all_styles['division_style_bottom']['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
        $all_styles['format_date'] = $main_style;
        $all_styles['format_date'] = array('numberformat' => array('code' => 'MM/DD/YY'));
        return $all_styles[$style];
    }
    //END COMMON FUNCTIONS

    //INSERT A HEADER ROW
    function insertHeadRow(){
        foreach($this->gets->column_data as $cell_letter => $column){
            $cell = $this->getCell($cell_letter);
            $this->setCellStyle($cell, 'header');
            $this->setCellValue($cell, $this->getHeadingNameFor($column));
            $this->gets->column_dimensions[$cell_letter] = $this->getColumnDimensionFor($column);

        }
        return $this;
    }

    function getHeadingNameFor($column){
        $titles = array(
            'item'        => "ITEM",
            'job'         => "JOB NUMBER",
            'date'        => "DATE",
            'start'       => "START TIME",
            'end'         => "END TIME",
            'language'    => "LANGUAGE",
            'interpreter' => "INTERPRETER ID",
            'caller_name' => "CONTACT PERSON",
            'inv_phone'   => "CONTACT NUMBER",
            'access_code' => "ACCESS CODE",
            'inv_detail'  => "DIVISION",
            'special'     => $this->gets->special_header,
            'rate_code'   => "RATE CODE",
            'minutes'     => "MINUTES",
            'charges'     => "CHARGE"
        );
        return $titles[$column];
    }

    function getColumnDimensionFor($column){
        $specialDimension = strlen($this->gets->special_header) * 1.1;
        $dimensions = array(
            'item'        => 5,
            'job'         => 10.5,
            'date'        => 10,
            'start'       => 10,
            'end'         => 10,
            'language'    => 10,
            'interpreter' => 13,
            'caller_name' => 16,
            'inv_phone'   => 16,
            'access_code' => 11.5,
            'inv_detail'  => 25,
            'special'     => $specialDimension,
            'rate_code'   => 10,
            'minutes'     => 10,
            'charges'     => 10
        );
        return $dimensions[$column];
    }
    //END INSERT A HEADER ROW
    
    //INSERT A CALL DATA ROW
    function insertCallRow($call){
        $this->gets->column_data['Y'] = 'access_code';
        $this->gets->column_data['Z'] = 'language';
        $this->gets->column_data['W'] = 'section';
        foreach($this->gets->column_data as $cell_letter => $column){
            $cell = $this->getCell($cell_letter);
            $this->setCellRowDataFor($column, $call, $cell);
        }
        unset($this->gets->column_data['Y'], $this->gets->column_data['Z']);
    }

    function setCellRowDataFor($column, $call, $cell){
        $this->setCellStyle($cell);
        $format = FALSE;
        switch ($column) {
            case 'item':
                $data = isset($this->gets->current_itm) ? $this->gets->current_itm:1;
                break;
            case 'job':
                $data = $call->id;
                break;
            case 'date':
                $data = date('m/d/Y', strtotime($call->inv_start));
                $format = TRUE;
                break;
            case 'start':
                $data = date('H:i', $this->getRoundDownTime($call->answer_timestamp));
                $format = TRUE;
                break;
            case 'end':
                $data = date('H:i', $this->getRoundUpTime($call->end_timestamp));
                $format = TRUE;
                break;
            case 'language':
                $data = $call->language;
                break;
            case 'interpreter':
                $data = $call->interpreter_id > 0 ? $call->interpreter_id:2000;
                break;
            case 'caller_name':

                $capitalize = explode(' ', trim($call->caller_name));
                foreach($capitalize as $index => $part){
                    if($index == 0){
                        $data = ucfirst(strtolower($part));
                    } else{
                        $data.= ' ' . ucfirst(strtolower($part));
                    }
                }
                break;
            case 'inv_phone':
                if($call->inv_phone == 0 || $call->inv_phone == ''){
                    $data = "Caller ID Blocked";
                } else{
                    $data = $call->inv_phone;
                }
                $format = TRUE;
                break;
            case 'access_code':
                $data = $call->access_code;
                break;
            case 'inv_detail':
                if($call->client_id == 123456){
                    if(!in_array($call->access_code, $this->gets->dnames)){
                        $this->gets->dnames[$call->access_code] = $this->getDivisionName($call->access_code);
                    }
                    $data = $this->gets->dnames[$call->access_code];
                } else{
                    $data = $call->inv_detail;
                }                       
                break;
            case 'special':
                $title = $this->gets->special_header;
                $data = str_replace('~', '', $call->inv_special);
                break;
            case 'rate_code':
                $data = $call->rate_code;
                break;
            case 'minutes':
                $data = $this->getDurationValue($call);
                break;
            case 'charges':
                $data = $this->getChargesAmount($call);
                $format = TRUE;
                break;
            case 'section':
                $data = $this->gets->current_section;
                break;
        }
        $column = $format === TRUE ? $column:NULL;
        $this->setCellValue($cell, $data, $column);
    }

    function getDivisionName($access_code){
        $this->_ci->db->select('division');
        $this->_ci->db->where('access_code', $access_code);
        $query = $this->_ci->db->get('client_data', 1);
        if($query->result()){
            foreach($query->result() as $client){
                return $client->division;
            }
        } else{
            return FALSE;
        }
    }

    function getRoundDownTime($timestamp){
        $rounded = strtotime($timestamp) - 60;
        return $rounded;
    }

    function getRoundUpTime($timestamp){
        $rounded = strtotime($timestamp);
        return $rounded;
    }

    function getDurationValue($call){
        $start = $this->getRoundDownTime($call->answer_timestamp);
        $end = $this->getRoundUpTime($call->end_timestamp);
        return ceil(($end - $start)/60);
    }

    function getChargesAmount($call){
        $charges = $this->getDurationValue($call) * $call->rate;
        return $charges;
    }
    //END INSERT A CALL DATA ROW
    
    //INSERT A SEPARATION
    function insertSeparationHead($detail, $data){
        if($this->gets->layout['separated'] === TRUE){
            if($detail == "Division Name :"){
                $this->gets->current_row++;
                $this->setCellValue($this->getCell('A'), $detail);
                $this->setCellValue($this->getCell('C'), $data);
                $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('B'), 'merge');
                $this->setCellStyle($this->getCell('C') . ':' . $this->getCell('G'), 'merge'); 
                $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('P'), 'separation_head_1');
                $this->gets->current_row++;
            } else{
                $this->setCellValue($this->getCell('A'), $detail);
                $this->setCellValue($this->getCell('C'), $data);
                $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('B'), 'merge');
                $this->setCellStyle($this->getCell('C') . ':' . $this->getCell('G'), 'merge'); 
                $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('P'), 'separation_head_2');
                $this->gets->current_row++;
            }
                
        }
        return $this;
    }

    function insertSeparationFoot(){
        if($this->gets->layout['separated'] === TRUE){
            $current_row++;
            $sub_minutes = "=sum(" . $this->getColumnLetterFor('minutes') . $this->gets->separation_start  . ":" . $this->getColumnLetterFor('minutes') . ($this->gets->current_row - 1) . ")";
            $sub_cost = "=sum(" . $this->getColumnLetterFor('charges') . $this->gets->separation_start  . ":" . $this->getColumnLetterFor('charges') . ($this->gets->current_row - 1) . ")";
            $this->setCellValue($this->getCell('J'), 'SUBTOTAL');
            $this->setCellValue($this->getCell($this->getColumnLetterFor('minutes')), $this->getSeparationSum($this->getColumnLetterFor('minutes')));
            $this->setCellValue($this->getCell($this->getColumnLetterFor('charges')), $this->getSeparationSum($this->getColumnLetterFor('charges')));
            $this->setCellStyle($this->getCell($this->getColumnLetterFor('charges')), 'charges');
            $this->setCellStyle($this->getCell('J') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'subtotal');
            $current_row++;
        }
        return $this;
    }

    function getSeparationSum($column_letter){
        $formula = "=sum(" . $column_letter . $this->gets->separation_start . ":" . $column_letter . ($this->gets->current_row - 1) . ")";
        return $formula;
    }
    //END INSERT A SEPARATION

    //INSERT A SECTION
    function insertSectionHead(){
        if(isset($this->gets->layout['invoice_sections'])){

        }
        return $this;
    }

    function insertSectionFoot(){
        if(isset($this->gets->layout['invoice_sections'])){
            $this->gets->section_ender = $this->gets->current_row;
            $this->gets->current_row++;
            $this->setCellValue($this->getCell('A'), 'SECTION ' . $this->gets->current_section . ' SUMMARY');
            $this->setCellValue($this->getCellFor('minutes'), 'MINUTES');
            $this->setCellValue($this->getCellFor('charges'), 'CHARGES');
            $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('C'), 'merge');
            $this->setCellStyle($this->getCell('A') . ':' . $this->getCellFor('charges'), 'summary_1');
            $this->gets->current_row++;
            $this->setCellValue($this->getCell('E'), 'Over-the-phone Interpretation:');
            $this->setCellValue($this->getCellFor('minutes'), $this->getSectionSum($this->getColumnLetterFor('minutes')));
            $this->setCellValue($this->getCellFor('charges'), $this->getSectionSum($this->getColumnLetterFor('charges')));
            $this->setCellStyle($this->getCellFor('charges'), 'charges');
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('I'), 'merge');
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCellFor('charges'), 'summary_2');
            $this->gets->current_row++;
            $this->gets->current_row++;
            $this->setCellValue($this->getCell('I'), 'SECTION ' . $this->gets->current_section . ' CHARGES');
            $this->setCellValue($this->getCellFor('minutes'), $this->getSectionSum($this->getColumnLetterFor('charges')));
            $this->setCellStyle($this->getCellFor('minutes') . ':' . $this->getCellFor('minutes'), 'charges');
            $this->setCellStyle($this->getCell('H') . ':' . $this->getCellFor('charges'), 'grand_total');//TODO
            $this->setCellStyle($this->getCellFor('minutes') . ':' . $this->getCellFor('charges'), 'merge');
        }
        return $this;
    }

    function getSectionSum($column_letter){
        $start = $this->gets->section_start;
        $ender = $this->gets->section_ender;
        $formula = '=SUMIF(A' . $start . ':A' . $ender . ',">"&0,' . $column_letter . $start . ':' . $column_letter . $ender . ')';
        return $formula;
    }
    //END INSERT A SECTION

    //INSERT GRAND TOTAL
    function insertGrandTotal(){
        $this->gets->section_ender = $this->gets->current_row;
        $this->gets->current_row++;
        $this->gets->current_row++;
        $this->setCellValue($this->getCell('A'), 'SUMMARY');
        $this->setCellValue($this->getCell($this->getColumnLetterFor('minutes')), 'MINUTES');
        $this->setCellValue($this->getCell($this->getColumnLetterFor('charges')), 'CHARGES');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('C'), 'merge');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'summary_1');
        $this->gets->current_row++;
        $this->setCellValue($this->getCell('E'), 'Over-the-phone Interpretation:');
        $this->setCellValue($this->getCell($this->getColumnLetterFor('minutes')), $this->getTotalSum($this->getColumnLetterFor('minutes')));
        $this->setCellValue($this->getCell($this->getColumnLetterFor('charges')), $this->getTotalSum($this->getColumnLetterFor('charges')));
        $this->setCellStyle($this->getCell($this->getColumnLetterFor('charges')), 'charges');
        $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('I'), 'merge');
        $this->setCellStyle($this->getCell('E') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'summary_2');
        $this->gets->current_row++;
        $this->gets->current_row++;
        $this->setCellValue($this->getCell('I'), 'TOTAL CHARGES');
        $this->setCellValue($this->getCell($this->getColumnLetterFor('minutes')), $this->getTotalSum($this->getColumnLetterFor('charges')));
        $this->setCellStyle($this->getCell($this->getColumnLetterFor('minutes')) . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'charges');
        $this->setCellStyle($this->getCell('H') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'grand_total');
        $this->setCellStyle($this->getCell($this->getColumnLetterFor('minutes')) . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'merge');
    }

    function getTotalSum($column_letter){
        $formula = '=SUMIF(A:A,">"&0,' . $column_letter . ':' . $column_letter . ')';
        return $formula;
    }
    //END INSERT GRAND TOTAL

    //INSERT REPORT
    function insertCallReport(){
        $this->startReportPage();
        foreach($this->gets->access_codes as $section_number => $section){
            $this->insertReportHead('Access Code', $section_number);
            foreach($section as $access_code){
                $this->insertReportRow($access_code, 'Y', $section_number);
            }
            $this->insertReportFoot();
            $this->gets->current_row++;
            $this->gets->current_row++;
        }
        
        foreach($this->gets->languages as $section_number => $section){
            $this->insertReportHead('Language', $section_number);
            foreach($section as $language){
                $this->insertReportRow($language, 'Z', $section_number);
            }
            $this->insertReportFoot();
            $this->gets->current_row++;
            $this->gets->current_row++;
        }
    }

    function startReportPage(){
        $row_now = $this->gets->current_row/65;
        $page_part = ($row_now) - floor($row_now);
        $rows_left = 65 - ($page_part * 65);
        $broken = FALSE;
        if($rows_left < count($this->gets->access_codes) + count($this->gets->languages) + 10){
            $this->breakExcelPage(FALSE);
            $broken = TRUE;
        }
        if($broken != TRUE){
            $this->gets->current_row = $this->gets->current_row + 5;
        } else{
            $this->gets->current_row = $this->gets->current_row + 2;
        }
        $this->setCellValue($this->getCell('A'), 'REPORTS');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'summary_1');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('C'), 'merge');
        $this->gets->current_row = $this->gets->current_row + 2;
    }

    function insertReportHead($name, $section){
        if(isset($this->gets->layout['invoice_sections'])){

            $this->setCellValue($this->getCell('E'), 'SECTION ' . $section);
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('F'), 'report_head');
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('F'), 'merge');
        }
        $this->setCellValue($this->getCell('G'), $name);
        $this->setCellValue($this->getCell('H'), 'Minutes');
        $this->setCellValue($this->getCell('I'), 'Calls');
        $this->setCellValue($this->getCell('J'), '% Total');
        $this->setCellValue($this->getCell('K'), 'Charges');
        $this->setCellStyle($this->getCell('G') . ':' . $this->getCell('K'), 'report_head');
        $this->gets->current_row++;
        $this->gets->report_top_row = $this->gets->current_row;
    }

    function insertReportFoot(){
        $this->gets->report_bot_row = $this->gets->current_row - 1;
        $this->setCellValue($this->getCell('G'), 'TOTALS');
        $this->setCellValue($this->getCell('H'), '=SUM(H' . $this->gets->report_top_row . ':H' . $this->gets->report_bot_row . ')');
        $this->setCe . ':' . $this->getCell('I'), 'merge');
        $this->setCellStyle($this->getCell('E') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'summary_2');
        $this->gets->current_row++;
        $this->gets->current_row++;
        $this->setCellValue($this->getCell('I'), 'TOTAL CHARGES');
        $this->setCellValue($this->getCell($this->getColumnLetterFor('minutes')), $this->getTotalSum($this->getColumnLetterFor('charges')));
        $this->setCellStyle($this->getCell($this->getColumnLetterFor('minutes')) . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'charges');
        $this->setCellStyle($this->getCell('H') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'grand_total');
        $this->setCellStyle($this->getCell($this->getColumnLetterFor('minutes')) . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'merge');
    }

    function getTotalSum($column_letter){
        $formula = '=SUMIF(A:A,">"&0,' . $column_letter . ':' . $column_letter . ')';
        return $formula;
    }
    //END INSERT GRAND TOTAL

    //INSERT REPORT
    function insertCallReport(){
        $this->startReportPage();
        foreach($this->gets->access_codes as $section_number => $section){
            $this->insertReportHead('Access Code', $section_number);
            foreach($section as $access_code){
                $this->insertReportRow($access_code, 'Y', $section_number);
            }
            $this->insertReportFoot();
            $this->gets->current_row++;
            $this->gets->current_row++;
        }
        
        foreach($this->gets->languages as $section_number => $section){
            $this->insertReportHead('Language', $section_number);
            foreach($section as $language){
                $this->insertReportRow($language, 'Z', $section_number);
            }
            $this->insertReportFoot();
            $this->gets->current_row++;
            $this->gets->current_row++;
        }
    }

    function startReportPage(){
        $row_now = $this->gets->current_row/65;
        $page_part = ($row_now) - floor($row_now);
        $rows_left = 65 - ($page_part * 65);
        $broken = FALSE;
        if($rows_left < count($this->gets->access_codes) + count($this->gets->languages) + 10){
            $this->breakExcelPage(FALSE);
            $broken = TRUE;
        }
        if($broken != TRUE){
            $this->gets->current_row = $this->gets->current_row + 5;
        } else{
            $this->gets->current_row = $this->gets->current_row + 2;
        }
        $this->setCellValue($this->getCell('A'), 'REPORTS');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell($this->getColumnLetterFor('charges')), 'summary_1');
        $this->setCellStyle($this->getCell('A') . ':' . $this->getCell('C'), 'merge');
        $this->gets->current_row = $this->gets->current_row + 2;
    }

    function insertReportHead($name, $section){
        if(isset($this->gets->layout['invoice_sections'])){

            $this->setCellValue($this->getCell('E'), 'SECTION ' . $section);
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('F'), 'report_head');
            $this->setCellStyle($this->getCell('E') . ':' . $this->getCell('F'), 'merge');
        }
        $this->setCellValue($this->getCell('G'), $name);
        $this->setCellValue($this->getCell('H'), 'Minutes');
        $this->setCellValue($this->getCell('I'), 'Calls');
        $this->setCellValue($this->getCell('J'), '% Total');
        $this->setCellValue($this->getCell('K'), 'Charges');
        $this->setCellStyle($this->getCell('G') . ':' . $this->getCell('K'), 'report_head');
        $this->gets->current_row++;
        $this->gets->report_top_row = $this->gets->current_row;
    }

    function insertReportFoot(){
        $this->gets->report_bot_row = $this->gets->current_row - 1;
        $this->setCellValue($this->getCell('G'), 'TOTALS');
        $this->setCellValue($this->getCell('H'), '=SUM(H' . $this->gets->report_top_row . ':H' . $this->gets->report_bot_row . ')');
        $this->setCelient  = 'Client ID: ';
        $DCS_ARRAY = array();
        if($this->isDCSClient()){
            $first = substr($this->gets->client_id, 0, 6);
            $rest = substr($this->gets->client_id, 6);
            $client .= $first .".". $rest;  
        } else{
            $client .= $this->gets->client_id;
        }
        $client_name  = $this->gets->client_details->agency;
        if($this->gets->client_details->invoice != 'OCI'){
            $billed_from = 'Avaza Language Services Corp.';
        } else{
            $billed_from = 'Open Communications International';
        }
        $statement_type  = 'Over The Phone Interpretation Statement';
        $client = $account_num . "\n" . $client . "\n" . $client_name;
        $statement = $billed_from . "\n" . $statement_type;
           $left_header = '&L&"Arial,Bold"&8' . $client;
         $center_header = '';
          $right_header = '&R&"Arial,Bold"&8' . $statement;
           $left_footer = '&L&"Arial,Bold"Confidential';
         $center_footer = '&C&"Arial,Bold"Date of Invoice: &D';
          $right_footer = '&R&"Arial,Bold"Page &P of &N';
        $this->objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($left_header . $center_header . $right_header);
        $this->objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter($left_footer . $center_footer . $right_footer);
    }

    function setClientDetails(){
        $this->_ci->db->select('*');
        $this->_ci->db->where('client_id', $this->gets->client_id);
        $query = $this->_ci->db->get('client_data', 1);
        if($query->result()){
            foreach($query->result() as $client){
                $this->gets->client_details = $client;
            }
        } else{
            die('BAD CLIENT - ' . $this->gets->client_id);
        }
        return $this;
    }

    function isDCSClient(){
        $DCS = range('9005321', '9005328');
        $DCS = array_merge(array('9005341', '9005342'), $DCS);
        $DCS = array_merge(range('9005351', '9005354'), $DCS);
        $DCS = array_merge(range('9005391', '9005397'), $DCS);
        $DCS = array_merge(range('9005481', '9005485'), $DCS);
        $DCS = array_merge(range('9005061', '9005069'), $DCS);
        $DCS = array_merge(range('90050610','90050619'), $DCS);
        return in_array($this->gets->client_id, $DCS);
    }

    function isTNClient(){
        $TN = array(
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
        $TN = array_merge(range('900508', '900515'), $TN);
        if($this->isDCSClient() || in_array($this->gets->client_id, $TN)){
            return TRUE;
        } else{
            return FALSE;
        }
    }

    function getColumnLetterFor($data){
        return array_keys($this->gets->column_data, $data)['0'];
    }

    function finalizeExcelPageSetup(){
        //PAGE LAYOUT AND ORIENTATION
        $this->objPageSetup = new PHPExcel_Worksheet_PageSetup();
        $this->objPageSetup->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
        if($this->gets->client_id == 123456){
            $this->objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        } else{
            $this->objPageSetup->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        } 
        $this->objPageSetup->setFitToWidth(1);
        $this->objPageSetup->setFitToHeight(0);
        $this->setPrintArea();
        $this->objPHPExcel->getActiveSheet()->setPageSetup($this->objPageSetup);
        return $this;
    }
    
    function setPrintArea(){
        $alpharray = range('A', 'Z');
        $numb_cols = count($this->gets->columns);
        $alphindex = $numb_cols - 1;
        $last_cell = $this->getCell($alpharray[$alphindex]);
        $this->objPageSetup->setPrintArea('A1:' . $last_cell);
        return $this;
    }
        
    function generateInvoiceWithinFolder(){
        $filename = $this->getFilenameOrTitle();
        $objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $objWriter->setPreCalculateFormulas(false);
        $this->_ci->ftp->connect(array('hostname' => '192.168.1.60', 'username' => 'guest', 'password' => ''));
        $rem_path = '/disk1/RESTRICTED/Invoicing-/NEW INVOICING/';
        $full_names = $this->_ci->ftp->list_files($rem_path);
        $files = array();
        foreach ($full_names as $indx => $f){
            $new = str_replace($rem_path, '', $f);
            $files[] = $new;
        }
        $source = '/var/www/' . $filename . '.xlsx';
        $objWriter->save($source);
        $upload = $this->_ci->ftp->upload($source, $rem_path . $filename . '.xlsx');
        $this->_ci->ftp->close();
        unset($source);
        if($upload == 1){
            $this->gets->done = TRUE;
        } else{
            $this->gets->done = FALSE;
        }
    }

    /*function generatePDFCopyWithinFolder(){
        return TRUE;
    }*/
    
    //SPECIAL JAVASCRIPT FUNCTIONS
    function getClientListForInvoiceCode($invoice_code){
        //get list of all clients with invoice code
    }
    //END SPECIAL JAVASCRIPT FUNCTIONS
}
?>