<div class="container_12" id="tracking_page">
    <div class="grid_12">
        <div class="block-border">
            <div class="block-header">
                <h1>Call Tracking</h1><span></span>
            </div>
            <div class="block-content">
                <table id="tracking-table" class="table">
                    <thead>
                        <tr>
                            <th>Link</th>
                            <th>Connection</th>
                            <th>Access Code</th>
                            <th>Validate</th>
                            <th>Caller Name</th>
                            <th class="spn">Special Data</th>
                            <th>Language</th>
                            <th>Interpreter</th>
                            <th>Drop</th>
                            <th>Callout</th>
                            <th class="cnum">Callout Number</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tracking_forms">
<?php
function open_form($f){ 
    return '<tr id="form' . $f . '"><form id="ctform' . $f . '">';
}

function link_cell($f){ 
    return '<td>
                <a href="javascript:void(0);" id="linkup' . $f . '"><img src="' . base_url() . 'assets/img/icons/misc/circle-grey.png"/></a>
                <a href="javascript:void(0);" id="ignore' . $f . '"><img src="' . base_url() . 'assets/img/icons/misc/x.png"/></a>
            </td>';
}

function conn_cell($f){
    return '<td id="connection' . $f . '">
                <strong id="time' . $f . '"></strong>
            </td>';
}

function accd_cell($f){ 
    return '<td>
                <input type="text" id="access_code' . $f . '" name="access_code' . $f . '">
              </td>';
}

function acck_cell($f){ 
    return '<td id="ac_check' . $f . '"></td>';
}

function repn_cell($f){ 
    return '<td>
                <input type="text" style="position: relative; z-index: 6;" id="rep_name' . $f . '" name="rep_name' . $f . '">
            </td>';
}
function spec_cell($f){ 
    return '<td id="sp_td' . $f . '" class="spn">
                <input type="text" id="specialf' . $f . '" name="specialf' . $f . '">
            </td>';
}

function inid_cell($f){ 
    return '<td>
                <select id="interpreter' . $f . '" name="interpreter' . $f . '">
                    <option value="0">Select Interpreter ...</option>
                </select>
            </td>';
}

function drop_cell($f){ 
    return '<td>
                <input type="checkbox" id="drop' . $f . '" class="drop" name="drop' . $f . '">
            </td>';
}

function calo_cell($f){ 
    return '<td>
                <input type="checkbox" id="callout' . $f . '" name="callout' . $f . '">
            </td>';
}

function cnum_cell($f){ 
    return '<td class="cnum">
                <span>
                    <input style="float:left;" type="text" id="co_num' . $f . '" name="co_num' . $f . '" >
                    <a style="float:right;" href="javascript:void(0);" id="co_num_add' . $f . '">
                        <img src="' . base_url() . 'assets/img/icons/misc/plus.png"/>
                    </a>
                </span>
            </td>';
}

function sbmt_cell($f){ 
    return '<td>
                <input id="submit' . $f . '" type="button" value="Start Call"/>
            </td>';
}

function uuid_hide($f){ 
    return '<input type="hidden" name="uuid' . $f . '" id="uuid' . $f . '">';
}

function myid_hide($f){ 
    return '<input type="hidden" name="my_uuid' . $f . '" id="my_uuid' . $f . '">';
}

function rori_hide($f){ 
    return '<input type="hidden" name="r_or_i' . $f . '" id="r_or_i' . $f . '" value="1">';
}

function subm_hide($f){ 
    return '<input type="hidden" name="submitted' . $f . '" id="submitted' . $f . '" value="1">';
}

function clse_form($f){ 
    return '</form></tr>';
}
function lang_opts(){
    foreach($languages as $language){
        echo '<option value="' . $language->language_code . '">' . $language->language . '</option>';
    }
}

function lngo_cell($f){ 
    return '<td>
            <select id="language' . $f . '" name="language' . $f . '">
                <option value="0">Select Language ...</option>';
}

function lngc_cell($f){ 
    return '</select>
          </td>';
}
$f = 1;
if($page_type == 'Interpreter'){
   $nf = 2; 
} else{
   $nf = 13; 
}
while($f != $nf){
    echo open_form($f);
    echo link_cell($f);
    echo conn_cell($f);
    echo accd_cell($f);
    echo acck_cell($f);
    echo repn_cell($f);
    echo spec_cell($f);
    echo lngo_cell($f);
    foreach($languages as $language){
        echo '<option value="' . $language->language_code . '">' . $language->language . '</option>';
    }
    echo lngc_cell($f);
    echo inid_cell($f);
    echo drop_cell($f);
    echo calo_cell($f);
    echo cnum_cell($f);
    echo sbmt_cell($f);
    echo uuid_hide($f);
    echo myid_hide($f);
    echo rori_hide($f);
    echo subm_hide($f);
    echo clse_form($f);
    $f++;
}
?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>