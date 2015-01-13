jQNC(document).ready(function(){
	jQNC('#datefrom').change(function(){
		if(jQNC('#datefrom').val() === "" || jQNC('#dateto').val() === ""){
			return false;
		} else{
			get_timepunches(jQNC('#interpreters').val(), jQNC('#datefrom').val(), jQNC('#dateto').val(), '#timetable');
		}		
	});

	jQNC('#dateto').change(function(){
		if(jQNC('#datefrom').val() === "" || jQNC('#dateto').val() === ""){
			return false;
		} else{
			get_timepunches(jQNC('#interpreters').val(), jQNC('#datefrom').val(), jQNC('#dateto').val(), '#timetable');
		}
	});
	get_staff();
	jQNC('#tab-panel-3').tabs({selected:3});
	jQNC("#datefrom").datepicker({ dateFormat: 'yy-mm-dd'});
	jQNC("#dateto").datepicker({ dateFormat: 'yy-mm-dd'});
	jQNC("#slidepunch").datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm:ss' , showSecond: false});
	get_timepunches(jQNC('#interpreters').val(), today, tomorrow,'#punch_table1');
	get_timepunches(jQNC('#interpreters').val(), last_sun, tomorrow, '#punch_table2');
	get_timepunches(jQNC('#interpreters').val(), sun_two, tomorrow,'#punch_table3');
});

function get_timepunches(intid, from, to, table){
	url = '/testing/reports/get_timepunches/' +from+ '/' +to+ '/' +intid+ '/';
	console.log(url);
	jQNC.getJSON(url, function(data){
		jQNC(table).empty();
		total = 0;
		if(data !== false){
			jQNC.each(data, function(index, object){
				//set vars
				var strt_punch = (object.edit_punch !== null) ? object.edit_punch : object.this_punch;
				var stop_punch = (object.next_punch) ? object.next_punch : 'Current';
				var duration = (stop_punch != 'Current') ? new Date(stop_punch).getTime() - new Date(strt_punch).getTime() : new Date().getTime() - new Date(strt_punch).getTime();
				var status = object.status_name;
				if(object.status_type == 1 && object.time_punch == 1){
					total = total + duration;
				}
					var row = jQNC('<tr />');
					var one = jQNC('<td />');
					var two = jQNC('<td />');
					var thr = jQNC('<td />');
					var fou = jQNC('<td />');
					var fiv = jQNC('<td />');
					var hms = moment(duration);
					hms.zone("-00:00");
				if(table == '#timetable1'){
					one.text(status);
					two.text(strt_punch);
					thr.text(stop_punch);
					fou.text(hms.format('HH:mm:ss'));
					fiv.html('<a id="punch'+ object.id +'"href="javascript:edit_punch('+ object.id +');">Edit</a>');
					jQNC(row).append(one);
					jQNC(row).append(two);
					jQNC(row).append(thr);
					jQNC(row).append(fou);
					jQNC(row).append(fiv);
					jQNC(table).append(row);
				} else{
					if(object.status_type == 1 && object.time_punch == 1){
						one.text(status);
						two.text(strt_punch);
						thr.text(stop_punch);
						fou.text(hms.format('HH:mm:ss'));
						fiv.html('<a id="punch'+ object.id +'"href="javascript:edit_punch('+ object.id +');">Edit</a>');
						jQNC(row).append(one);
						jQNC(row).append(two);
						jQNC(row).append(thr);
						jQNC(row).append(fou);
						jQNC(row).append(fiv);
						jQNC(table).append(row);
					}
				}
			});
		}
		//add total
		var row = jQNC('<tr />');
		var one = jQNC('<td />');
		var two = jQNC('<td />');
		var thr = jQNC('<td />');
		var fou = jQNC('<td />');
		var fiv = jQNC('<td />');
		one.html('<h3>Total Hours :</h3>');
		two.html('<h3>'+new Date(from).toString('MMMM dd ,yyyy')+'</h3>');
		thr.html('<h3>'+new Date(to).toString('MMMM dd ,yyyy')+'</h3>');
		fou.html('<h3>'+(total/3600/1000).toFixed(2)+'</h3>');
		jQNC(row).append(one);
		jQNC(row).append(two);
		jQNC(row).append(thr);
		jQNC(row).append(fou);
		jQNC(row).append(fiv);
		jQNC(table).append(row);
	});
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
			jQNC('#interpreters').change(function(){
				get_timepunches(jQNC('#interpreters').val(), today, tomorrow,'#punch_table1');
				get_timepunches(jQNC('#interpreters').val(), last_sun, tomorrow, '#punch_table2');
				get_timepunches(jQNC('#interpreters').val(), sun_two, tomorrow,'#punch_table3');
			});
		}
	});
}

function edit_punch(id){
	console.log(id);
}

function add_punch(){
	jQNC.getJSON('../reports/add_punch/' + jQNC('#interpreters').val() + '/' + jQNC('#slidepunch').val() + '/' + jQNC('#status').val() + '/', function(data){
		if(data !== false){
			console.log(data);
			jQNC.jGrowl('Punch Added', {theme: "success"});
		} else{
			jQNC.jGrowl('Punch Failed', {theme: "error"});
		}
	});
	jQNC('#punchadder').hide();
	jQNC('#hideme').show();
}