var status_names = ['CLOCKED-OUT', 'AVAILABLE', 'BREAK', 'LUNCH', 'LEAVE-BLDG'];
jQNC(document).ready(function(){
	jQNC('#datefrom').change(function(){
		if(jQNC('#datefrom').val() === "" || jQNC('#dateto').val() === ""){
			return false;
		} else{
			get_timepunches(jQNC('#datefrom').val(), jQNC('#dateto').val(), '#timetable');
		}		
	});
	jQNC('#dateto').change(function(){
		if(jQNC('#datefrom').val() === "" || jQNC('#dateto').val() === ""){
			return false;
		} else{
			get_timepunches(jQNC('#datefrom').val(), jQNC('#dateto').val(), '#timetable');
		}
	});
	show_hide(true, status);
	jQNC("#datefrom").datepicker({ dateFormat: 'yy-mm-dd'});
	jQNC("#dateto").datepicker({ dateFormat: 'yy-mm-dd'});
	whos_clocked_in();
	jQNC('#whos_clocked').click(function(){
		whos_clocked_in();
	});

});

var session = getSessionID();
var bbx_user_id;
function login(pass, user){
	jQNC.post('/gui/login/login', { template: 'json', __auth_user: user, __auth_pass: pass}, function(data){
		bbx_user_id = data.data.bbx_user_id;
	});
}

function getSessionID(){
    return getCookie("bps_session");
}

function get_all_timepunches(){
		get_timepunches(today, tomorrow,'#timetable1');
		get_timepunches(last_sun, tomorrow, '#timetable2');
		get_timepunches(sun_two, tomorrow,'#timetable3');
}

function getCookie(a) {
    a = a + "=";
    for (var e = document.cookie.split(";"), f = 0; f < e.length; f++) {
        for (var j = e[f]; j.charAt(0) == " ";) j = j.substring(1, j.length);
        if (j.indexOf(a) === 0) return j.substring(a.length, j.length);
    }
    return null;
}

function send_punch(type){
	if(jQNC('#editor_id').val() && jQNC('#editor_id').val() !== ''){
		id = jQNC('#editor_id').val();
		ts = jQNC('#editor_punch').val();
	} else if(jQNC('#slidepunch').val() && jQNC('#slidepunch').val() !== ''){
		id = 0;
		ts = jQNC('#slidepunch').val();
	} else{
		id = 0;
		ts = 0;
	}
	intid = jQNC('#interpreters').val();
	jQNC.post("../reports/send_punch/", { type: type, status_name: status_names[type], intid: intid, id: id, timestamp: ts })
        .done(function(data){
        if(data !== false){
            if(data.msg){
              jQNC.jGrowl(data.msg, { theme: 'success' });
            }
            show_hide(true, type);
        } else{
            if(data.msg){
                jQNC.jGrowl(data.msg, { theme: 'error' });
            }
            jQNC.jGrowl('Punch Failed', { theme: 'error' });
            show_hide(false, type); 
        }
        jQNC('#slidepunch').val('');
        jQNC('#editor_id').val('');
        jQNC('#editor_punch').val('');
        get_all_timepunches();
    });
}

function edit_punch(id){
	jQNC('#editor').show();
	jQNC.getJSON('../reports/get_edit_punch/' + id + '/',function(data){
		if(data[0]){
			jQNC('#editor_id').val(id);
			jQNC('#editor_punch').val(data[0].this_punch);
			jQNC('#editor_status').val(data[0].status_type);
			jQNC('#editor_punch').datetimepicker({ timeFormat: 'HH:mm:ss' , dateFormat: 'yy-mm-dd', showSecond: false});
		}
	});
}

function get_timepunches(from, to, table){
	intid = jQNC('#interpreters').val();
	jQNC.post("../reports/get_timepunches/", { from: from, to: to, intid: intid })
        .done(function(data){
        create_table(table, data);
        create_total(from, to, table, data);
    });
}

function create_table(table, data){
	jQNC(table).empty();
	jQNC.each(data, function(index, object){
		time = 0;
		if(table != '#timetable1'){
			time = 1;
		}
		row = add_punch_row(time, object);
		if(row !== false){
			jQNC(table).append(row);
		}		
	});
}

function add_punch_row(time, object){
	if(time == 1){
		hms = time_format((object.next_time_punch) ? new Date(object.next_time_punch).getTime() - new Date(object.this_punch).getTime() : new Date().getTime() - new Date(object.this_punch).getTime());
		if(object.time_punch == 1 && object.status_type == 1){
			row = jQNC('<tr />');
			jQNC(row).append(jQNC('<td />').text(object.status_name));
			jQNC(row).append(jQNC('<td />').text((object.edit_punch !== null) ? object.edit_punch : object.this_punch));
			jQNC(row).append(jQNC('<td />').text((object.next_punch) ? object.next_punch : 'Current'));
			jQNC(row).append(jQNC('<td />').text(hms));
			if(me != 1){
				jQNC(row).append(jQNC('<td />').html('<a href="javascript:edit_punch('+object.id+');">Edit</a>'));
			}
		} else{
			return false;
		}
	} else{
		hms = time_format((object.next_punch) ? new Date(object.next_punch).getTime() - new Date(object.this_punch).getTime() : new Date().getTime() - new Date(object.this_punch).getTime());
		row = jQNC('<tr />');
		jQNC(row).append(jQNC('<td />').text(object.status_name));
		jQNC(row).append(jQNC('<td />').text((object.edit_punch !== null) ? object.edit_punch : object.this_punch));
		jQNC(row).append(jQNC('<td />').text((object.next_punch) ? object.next_punch : 'Current'));
		jQNC(row).append(jQNC('<td />').text(hms));
		if(me != 1){
			jQNC(row).append(jQNC('<td />').html('<a href="javascript:edit_punch('+object.id+');">Edit</a>'));
		}
	}	
	return row;
}

function time_format(msecs){
	secs = msecs / 1000;
	seconds = 0;
	minutes = 0;
	hours = 0;
	while(secs > 0){
		seconds = seconds + 1;
		if(seconds == 59){
			minutes = minutes + 1;
			seconds = 0;
		}
		if(minutes == 59){
			hours = hours + 1;
			minutes = 0;
			seconds = 0;
		}
		secs = secs - 1;
	}
	if(seconds < 10){
		seconds = '0' + seconds;
	}
	if(minutes < 10){
		minutes = '0' + minutes;
	}
	if(hours < 10){
		hours = '0' + hours;
	}
	return hours + ":" + minutes + ":" + seconds;
}

function add_total_row(from, to, total, table){
	row = jQNC('<tr />');
	jQNC(row).append(jQNC('<td />').html('<h3>Total Hours :</h3>'));
	jQNC(row).append(jQNC('<td />').html('<h3>'+moment(new Date(from)).format("MMM Do YYYY")+'</h3>'));
	jQNC(row).append(jQNC('<td />').html('<h3>'+moment(new Date(to)).format("MMM Do YYYY")+'</h3>'));
	jQNC(row).append(jQNC('<td />').html('<h3>'+(total/3600/1000).toFixed(2)+'</h3>'));
	if(me != 1){
		jQNC(row).append(jQNC('<td />').html('<input type="button" download="my-data.csv" value="Save" onclick="get_csv(jQNC(\''+table+'\').table2CSV({delivery:\'value\'}));"/>'));
	}
	return row;
}

function get_csv(string){
	jQNC.post('../reports/timeclock', {csv_data: string});
}

function create_total(from, to, table, data){
	total = 0;
	jQNC.each(data, function(index, object){
		if(object.time_punch == 1 && object.status_type == 1){
			add = (object.next_time_punch) ? new Date(object.next_time_punch).getTime() - new Date(object.this_punch).getTime() : new Date().getTime() - new Date(object.this_punch).getTime();
			total = total + add;
		}
	});
	totalRow = add_total_row(from, to, total, table);
	jQNC(table).append(totalRow);
}

function show_hide(punched, type){
	if(punched === true){
		if(type == 1){
			jQNC('#buttons').show();
			jQNC('#clock_in').hide();
			jQNC('#lunch_back').hide();
			jQNC('#break_back').hide();
			jQNC('#leave_back').hide();
			if(me == 1){
				cudatel_status(1);
			}
		} else if(type == 2){
			jQNC('#buttons').hide();
			jQNC('#clock_in').hide();
			jQNC('#lunch_back').hide();
			jQNC('#leave_back').hide();
			jQNC('#back').show();
			jQNC('#break_back').show();
			if(me == 1){
				cudatel_status(6);
			}
		} else if(type == 3){
			jQNC('#buttons').hide();
			jQNC('#back').show();
			jQNC('#clock_in').hide();
			jQNC('#lunch_back').show();
			jQNC('#leave_back').hide();
			jQNC('#break_back').hide();
			if(me == 1){
				cudatel_status(3);
			}
		} else if(type == 4){
			jQNC('#buttons').hide();
			jQNC('#back').show();
			jQNC('#clock_in').hide();
			jQNC('#leave_back').show();
			jQNC('#lunch_back').hide();
			jQNC('#break_back').hide();
			if(me == 1){
				cudatel_status(4);
			}
		} else{
			jQNC('#buttons').hide();
			jQNC('#clock_in').show();
			jQNC('#back').hide();
			jQNC('#lunch_back').hide();
			jQNC('#break_back').hide();
			jQNC('#leave_back').hide();
			if(me == 1){
				cudatel_status(2);
			}
		}
	} else{
		return false;
	}
}

function cudatel_status(type){
	if(clss == 1){	
		jQNC.post('/gui/user/status', { template: 'json', bbx_user_status_id: type, sessionid: session});
	} else if(clss == 2){
		group_json = { bbx_user_id: bbx_user_id, bbx_group_id: 14, sessionid: session};
		if(type == 1){
			jQNC.post('/gui/group/members', group_json);
		} else{
			jQNC.ajax({
                url: '/gui/group/members',
                type: 'DELETE',
                data: group_json
			});
		}		
	} else{
		return false;
	}
}

function get_staff(){
	jQNC.getJSON('../reports/get_staff', function(data){
		if(data !== false){
			jQNC('#interpreters').empty();
			jQNC.each(data, function(index, object){
				var option = jQNC('<option />');
				option.text(object.intid + ' - ' + object.fname + ' ' + object.lname);
				jQNC(option).val(object.intid);
				jQNC('#interpreters').append(option);
			});
			jQNC.uniform.update(jQNC('#interpreters'));
			get_all_timepunches();
			jQNC('#interpreters').change(function(){
				get_all_timepunches();
			});
		}
	});
}

function whos_clocked_in(){
	jQNC.getJSON('../reports/whos_clocked_in/', function(data){
		jQNC('#clocked_in').empty();
		jQNC.each(data, function(index, object){
			set_clocked_in_staff_details(object);			
		});
	});
}

function set_clocked_in_staff_details(object){
	staff_member = object;
	jQNC.getJSON('../reports/get_staff_member/' + staff_member.intid + '/', function(data){
		if(data !== false){
			staff_member.fname = data[0].fname;
			staff_member.lname = data[0].lname;
			var row = add_clocked_row(staff_member);
			jQNC('#clocked_in').append(row);
		}
	});
}

function add_clocked_row(object){
	row = jQNC('<tr />');
	jQNC(row).append(jQNC('<td />').text(object.fname + ' ' + object.lname));
	jQNC(row).append(jQNC('<td />').text(object.status_name));
	jQNC(row).append(jQNC('<td />').text(time_format(new Date().getTime() - new Date(object.this_punch).getTime())));
	return row;
}