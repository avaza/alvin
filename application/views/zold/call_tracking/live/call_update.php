<html>
<head>
	<meta http-equiv="refresh" content="360">
</head>
	<body>
		<style>
		#page-console{
			background-color:#1F1B1B;
			height:300px;
			width:500px;
			color:#3FAE3A;
		}
		#error_console{
			background-color:#1F1B1B;
			height:600px;
			width:500px;
			color:#3FAE3A;
		}
		</style>
		<h3 id="update"></h3>
		<div id="page-console" style="border: 3px; border-color:green;">
		</div>
		<h3 id="errors">Errors</h3>
		<div id="error_console" style="border: 3px; border-color:green;">
		</div>
	</body>
</html>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
var errors = 0;
function update_the_count(count){
	$('#update').text(count);
}

function update_error_count(count){
	$('#errors').text('Errors : ' +count);
}

function output(string){
	var page_console = document.getElementById("page-console");
	$('#page-console').append('<br>' +string);
	if(page_console.hasChildNodes()){
      while(page_console.childNodes.length >= 25){
        page_console.removeChild( page_console.firstChild );       
      } 
	}
}

function error_output(string){
	errors = errors + 1;
	update_error_count(errors);
	var error_console = document.getElementById("error_console");
	$('#error_console').append('<br>' +string);
	if(error_console.hasChildNodes()){
      while(error_console.childNodes.length >= 50){
        error_console.removeChild( error_console.firstChild );       
      } 
	}
}


$(document).ready(function(){
	login();
});

var layer;
var bbx_ids = [];
var auth = '__auth_user=5041&__auth_pass=1405';
var retry = false;
var update_count = 0;
var calls_to_update = [];
var this_call = {};
var bbx_ids = [];
var alegs = [];
var blegs = [];
var possible_uuids = [];
var possible_datas = [];
var checked_uuids =[];
var current_call_index;
var session_id;
var orig_bleg;
var issuedots;

function login(){
	$.ajaxSetup({ cache: false, global: false , error: function(xhr, status, err){
		if (xhr.status == 401 || xhr.status == 400){
			console.log('Login Failed Trying again...');
			login();
			}
		}
	});
	$.getJSON('../live/get_session', function(data){
		if(data){
			session_id = data;
		}
	})
	.done(function(){
		$.get('../login/admin_login/j.murray/password/')
		.done(function(){
			get_calls_to_update();
		});
	});
}

function get_calls_to_update(){
	$.getJSON('../live/check', function(data, status, response){
		if(data.length > 0){
			calls_to_update = data;
			update_count = data.length;
			update_the_count('Calls to Update: '+update_count);
			current_call_index = 0;
			update_calls();
		} else{
			listen_live()
		}		
	});
}

function update_calls(){
	current_uuid = calls_to_update[current_call_index].uuid;
	get_call(current_uuid, session_id);
}

function get_call(uuid, session){
	if(session_id){
		console.log(session_id);
		$.getJSON('http://192.168.1.252/live/endtime_service/' +uuid+ '/' +session_id+ '/', function(data){
			if(data.error && data.error == "NOTAUTHORIZED"){
				console.log('Auth Error Trying again...');
				login();
			} else if(data.issue){
				if(data.ongoing){
					output(data.issue);
				} else{
					error_output(data.issue);
				}				
				next_call();
			} else{
				put_data_in_database(data);
			}
		});
	} else{
		login();
	}
}

function put_data_in_database(this_call){
	update_count--;
	update_the_count('Calls to Update: '+update_count);
	$.post("../live/post_json_call", this_call,
		function(data){
		length = '7bf05d87-0a5f-47c9-9a68-da2603215c0f';
		if(data.length == length.length){
            output('Posted To Database : ' +data);
        } else{
          output(data);
        }
	}, "json");
	next_call();
}

function listen_live(){
	output('Waiting 5 Seconds...');
	msecs = 5000;
	var start = new Date().getTime();
	var cur = start;
	while(cur - start < msecs){
		cur = new Date().getTime();
	}
	get_calls_to_update();
} 


/*
function get_cudatel_data(uuid, url_index){
	if(uuid === ''){
		mark_uuid_checked(uuid);
		get_cudatel_data(possible_uuids[0], 0);
	} else{
		if(uuid !== ""){
			query_url = '../live/get_data/' + url_index + '/' + session_id + '/' + uuid + '/';
			url_index++;
			$.getJSON(query_url, function(data, status, response){
				if(data.cdr){
					$(data.cdr).each(function(index, object){
						if(orig_bleg === false){
							orig_bleg = object.bleg_uuid;
						}
						if($.inArray(object.bbx_cdr_id, bbx_ids) == -1 && object.duration > 0){
							bbx_ids.push(object.bbx_cdr_id);
							possible_datas.push(object);					
							if($.inArray(object.uuid, possible_uuids) == -1 && $.inArray(object.uuid, checked_uuids) == -1){
								possible_uuids.push(object.uuid);
							}
							if($.inArray(object.uuid, alegs) == -1){
								alegs.push(object.uuid);
							}
							if($.inArray(object.bleg_uuid, possible_uuids) == -1 && $.inArray(object.bleg_uuid, checked_uuids) == -1){
								possible_uuids.push(object.bleg_uuid);
							}
							if($.inArray(object.bleg_uuid, blegs) == -1){
								blegs.push(object.bleg_uuid);
							}
						}										
					});
				}
			})
			.done(function(){
				if(url_index == 4 && $.inArray(orig_bleg, alegs) == -1){
					output("Still in progress : " + uuid);
					next_call();
					return false;
				} else{
					if(url_index < 4){
						get_cudatel_data(uuid, url_index);
					} else{
						mark_uuid_checked(uuid);
						if(possible_uuids.length !== 0){
							get_cudatel_data(possible_uuids[0], 0);
						} else{
							process_call_data();
						}
					}
				}
			});
		}
	}	
}

function send_to_php(){
	$.ajax({
    type : "POST",
    url : "process_call",
    data : "{'possible_datas':'"+ JSON.stringify(possible_datas) +"' }",
    contentType : "application/json; charset=utf-8",
    dataType : "json",
    success : function(msg) {
        console.log("Good");
    },
    fail : function(msg) {
        console.log("Bad");
    }
});
}

function get_phone_number(){
	original_leg = get_orig_leg();
	if(original_leg.caller_id_number.length > 5){
		this_call.caller_id_number = original_leg.caller_id_number;
		this_call.caller_id_name = original_leg.caller_id_name;
		console.log(this_call.caller_id_number);
		console.log(this_call.caller_id_name);
	} else{
		console.log(this_call.caller_id_number);
		console.log('PHONE NUMBER FAILURE');
	}
}

function get_orig_leg(){
	earliest_start_timestamp = Infinity;
	$(possible_datas).each(function(index, object){
		if(new Date(object.start_timestamp).getTime() < earliest_start_timestamp){
			earliest_start_timestamp = new Date(object.start_timestamp).getTime();
			earliest_leg = possible_datas[index];
		}
	});
	return earliest_leg;
}

function mark_uuid_checked(uuid){
	uuids_index = $.inArray(uuid, possible_uuids);
	if($.inArray(uuid, checked_uuids) == -1){
		checked_uuids.push(uuid);
		possible_uuids.splice(uuids_index, 1);
	}	
}

var start_times = [];
var answer_times = [];
var end_times = [];
var client_leg = {};

function process_call_data(){
	create_time_arrays();
	answer = get_earliest(answer_times);
	end = get_latest(end_times);
	$(possible_datas).each(function(index, object){
		if(new Date(object.answer_timestamp).getTime() == new Date(answer_times[answer]).getTime() && new Date(object.end_timestamp).getTime() == new Date(end_times[end]).getTime()){
			client_leg = object;
		}
	});
	$.extend(this_call, client_leg);
	get_phone_number();
	if(this_call && this_call.r_or_i !== '1'){
		if(parseInt(this_call.connected_by, 10) == parseInt(this_call.intid, 10)){//ROUTER INTERPRETED
			this_call.start_time = this_call.answer_timestamp;
		} else{
			this_call.start_time = this_call.start_timestamp;//ROUTED TO INTERPRETER
		}
	} else{
		this_call.start_time = this_call.answer_timestamp;//INTERPRETER ONLY
	}
	set_times();
	put_data_in_database();
}

function set_times(){
	if(this_call.start_time && this_call.end_timestamp && this_call.answer_timestamp && this_call.start_timestamp){
		if(this_call.drop == 1){
			admincheck = (new Date(this_call.end_timestamp).getTime() - new Date(this_call.answer_timestamp).getTime())/1000;
			if(admincheck < 120){
				this_call.admin = 1;
				this_call.drop = 0;
				this_call.start_time = this_call.answer_timestamp;
				console.log('ADMIN : '+this_call.call_time+" seconds");
			} else{
				this_call.start_time = this_call.end_timestamp;
			}
		}
		answer = new Date(this_call.answer_timestamp).getTime();
		start = new Date(this_call.start_time).getTime();
		end = new Date(this_call.end_timestamp).getTime();
		this_call.connection_time = (start - answer)/1000;
		this_call.call_time = (end - start)/1000;
		calculate_rate_code();
	}
}

function create_time_arrays(){
	start_times = [];
	answer_times = []
	end_times = [];
	$(possible_datas).each(function(index, item){
 		if($.inArray(item.start_timestamp, start_times) == -1){
 			start_times.push(item.start_timestamp);
 		}
 		if($.inArray(item.answer_timestamp, answer_times) == -1){
 			answer_times.push(item.answer_timestamp);
 		}
 		if($.inArray(item.end_timestamp, end_times) == -1){
 			end_times.push(item.end_timestamp);
 		}
 	});
}

function get_earliest(array){
	earliest = Infinity;
	earliest_index = -1;
	$(array).each(function(index, item){
		if(new Date(item).getTime() < earliest){
			earliest = new Date(item).getTime();
			earliest_index = index;
		}
	});
	return earliest_index;
}

function get_latest(array){
	latest = -Infinity;
	latest_index = -1;
	$(array).each(function(index, item){
		if(new Date(item).getTime() > latest){
			latest = new Date(item).getTime();
			latest_index = index;
		}
	});
	return latest_index;
}

function calculate_rate_code(){
	language = this_call.language;
    hour = new Date(this_call.start_time).getHours();
	if(hour >= 8 && hour < 20){
		timeframe = 'W';
		ls = this_call.language_set;
	} else{
		timeframe = 'O';	
		ls = this_call.language_set + 'A';
	}
	var callout = this_call.callout;
	if(callout == '1'){
		calltype = 'CO';
	} else{
		calltype = '';	
	}
	var	admin = this_call.admin;
	if(admin == '1'){
		adm = 'A'; 
	} else{
		adm = 'G';
	}
	this_call.rate_code = 'N' +timeframe +adm +calltype +ls;
}

function put_data_in_database(){
	update_count--;
	update_the_count('Calls to Update: '+update_count);
	$.post("../live/post_json_call", this_call,
		function(data){
		length = '7bf05d87-0a5f-47c9-9a68-da2603215c0f';
		if(data.length == length.length){
            output('Posted To Database : ' +data);
        } else{
          output(data);
        }
	}, "json");
	next_call();
}

function reset_vars(){
	this_call = {};
	bbx_ids = [];
	alegs = [];
	blegs = [];
	possible_uuids = [];
	possible_datas = [];
	checked_uuids =[];
}*/

function next_call(){
	if(current_call_index < update_count-1){
		current_call_index++;
		update_calls();
	} else{
		current_call_index = 0;
		update_count = 0;
		get_calls_to_update();
	}	
}
</script>