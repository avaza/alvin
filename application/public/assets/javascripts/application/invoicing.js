var startDate;
var endDate;
var clientID;
var invoiceIndex = 0;
var invoices;

jQNC(document).ready(function(){
	jQNC.datepicker.formatDate('yy-mm-dd 00:00:00');
	jQNC("#datefrom").datepicker({ dateFormat: 'yy-mm-dd 00:00:00' });
	jQNC("#dateto").datepicker({ dateFormat: 'yy-mm-dd 00:00:00' });
	jQNC.ajaxSetup({async:false});
});


function nextInvoice(){
	if((window.invoiceIndex+1) < invoices.length){
		exportSpecified(parseInt(invoices[window.invoiceIndex]));
	}	
}



function collectInputValues(){
	window.startDate = jQNC('#datefrom').val();
	window.endDate = jQNC('#dateto').val();
	window.clientID = jQNC("#client_id").val();
}


function validateDateRange(){
	collectInputValues();
	if(startDate === "" || endDate === ""){
		jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Choose an Starting Date AND an Ending Date</div>");
		return false;
	}
	return true;
}


function validateClient(){
	collectInputValues();
	if(clientID === "" || clientID == "0"){
		jQNC('#errors').html("<div class='alert error'><span class='hide'>x</span>You Must Select a Client ID</div>");
		return false;
	}
	return true;
}

function getInvoiceDropdownList(){
	jQNC.ajax({
		type: "POST",
		url: '../invoicing/clients_to_be_billed/' + startDate + '/' + endDate,
		dataType: "json",
		success: function(results){
			jQNC('#client_id').html('');
			jQNC('#client_id').append('<option value=0>Select Client ...</option>');
            jQNC.each(results, function(index, object){
                var opt = jQNC('<option />');
                opt.val(object.client_id);
                opt.text(object.client_id);
                jQNC('#client_id').append(opt); 
                jQNC('#client_id').focus();
                jQNC.uniform.update('#client_id');
            });
         }
	});
}

function exportSpecified(client_id){
	if(!client_id){
		validateClient();
		client_id = clientID;
	}
	validateDateRange();
	jQNC.ajax({
        type: "POST",
        url: "../invoicing/create_invoice/" + client_id + "/" + startDate + "/" + endDate,
        success: function(data){
            if(data == 1){
                jQNC.jGrowl("Invoice for " + client_id + " Created!", { theme: 'success' });
                jQNC('#client_id').val('0');
				jQNC.uniform.update(jQNC('#client_id'));
			} else{
				jQNC.jGrowl("Invoice FAILURE", { theme: 'error' });
			}
			window.invoiceIndex++;
			nextInvoice();

		}
    });
    checkInvoiced();
}

function exportCallCountUnder500(){
	if(validateDateRange()){
		jQNC.ajax({
            type: "POST",
            url: "../invoicing/collectInvoicesUnder500/"+ startDate +"/"+ endDate,
            success: function(data){
            	window.invoices = data;
            	console.log(data);
            	nextInvoice();
			}
        });
	} else{
		return false;
    }   
}

function checkInvoiced(){
	df = jQNC('#datefrom').val();
	dt = jQNC('#dateto').val();
	jQNC.ajax({
		type: "POST",
		url: '../invoicing/clients_already_billed/' + df + '/' + dt,
		dataType: "json",
		success: function(results){
			jQNC('#invoiced').html('');
			jQNC('#invoiced').append('<ul id="inv_list"></ul>'); 
            if(results.length > 0){
            	var table = jQNC('<table>');
            	var head = jQNC('<thead>');
            	head.html('<tr><td style="border-color:black;"><strong>STATUS</strong></td><td><strong>Client ID</strong></td><td><strong>Agency</strong></td><td><strong>Generate PDF</strong></td></tr>');
            	table.append(head);
            	var body = jQNC('<tbody>');
            	jQNC.each(results, function(index, object){
            		var row = jQNC('<tr>');
            		var c1 = jQNC('<td style="border-color:black;">');
            		var c2 = jQNC('<td style="border-color:black;">');
            		var c3 = jQNC('<td style="border-color:black;">');
            		var c4 = jQNC('<td style="border-color:black;">');
            		var link = jQNC('<a>');
            		c1.text(object.billed === true ? 'GENERATED':'PENDING');
            		row.append(c1);
            		c2.text(object.client_id);
            		row.append(c2);
            		c3.text(object.agency);
            		row.append(c3);
            		link.text(object.billed === true ? 'MAKE PDF':'');
            		link.attr('href', '../invoicing/create_pdf/' + object.file + '/');
            		c4.append(link);
            		row.append(c4);
            		body.append(row);
	            });
	            table.append(body);
	            jQNC('#inv_list').append(table);
	            jQNC('#complet').children().show();
            } else{
            	jQNC('#complet').children().hide();

            }            
         }
	});
}

function updateDropdown(){
	collectInputValues();
	if(!validateDateRange()){
		return false;
	} else{
		getInvoiceDropdownList();
		checkInvoiced();
	}
}

jQNC('#datefrom').change(function(){
	updateDropdown();
});

jQNC('#dateto').change(function(){
	updateDropdown();
});

jQNC("#invoice_get").click(function(){
	exportSpecified();
});

jQNC("#data_get").click(function(){
	exportCallCountUnder500();
});