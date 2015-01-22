<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="refresh" content="3600">
		<title>Live Call Monitor</title>
		<meta name="description" content="Live Call Monitor"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<link href="/assets/grocery_crud/ace/assets/css/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/assets/grocery_crud/ace/assets/css/font-awesome.min.css" />
		<link rel="stylesheet" href="/assets/grocery_crud/ace/assets/css/ace-fonts.css" />
		<link rel="stylesheet" href="/assets/grocery_crud/ace/assets/css/ace.min.css" />
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script type="text/javascript">	
			var incoming, calls, count = {"total":0, "ended":0}, prcnt = 0;
			var sessn = {"id":false, "attempts":5}
			$(document).ready(function(){
				$.ajaxSetup({ cache: false });
				startScript();
			})
			.ajaxError(function(xhr, txt, err){
			  	if (xhr.status == 401){
						sessn.attempts--;
						console.log('LOGIN FAIL. ' + sessn.attempts + ' MORE TIMES');
						startScript();
				} else{
					console.log('AJAX ERROR');
					console.log(xhr);
					console.log(txt);
					console.log(err);
					return false;
				}
			});

			function startScript(){
				$.when(
					$.post('/live/cuda_sessn/', function(data){
						sessn.id = data ? data:false;
					}, 'json')
				).then( getCallsToProcess );
				return false;
			}

			function getCallsToProcess(){
				$.when(
					$.post('/live/all_uuids/', function(data){
						incoming = data ? data:[];
					})
				).then( processNext );
			}

			function processNext(){
				updateView();
				if(incoming.calls.length > 0){
					processCall(incoming.calls.shift());
				} else{
					waitFor(3);
				}				
			}

			function updateView(){
				count.ended = incoming.progs.length + incoming.fatal.length + incoming.posts.length;
				count.total = incoming.calls.length + count.ended;
				prcnt = ((count.ended / count.total) * 100).toFixed(3);
				$('#total').text(count.total);
				$('#calls').text(incoming.calls.length);
				$('#flags').text(incoming.flags.length);
				$('#error').text(incoming.fatal.length);
				$('#progs').text(incoming.progs.length);
				$('#posts').text(incoming.posts.length);
				$('#prgbar').css('width', prcnt +'%');
				$('#timer').text('Approximately '+ remainsCalc() +' remaining');
				$('#check').text(count.ended +'/'+ count.total + ' calls this month ('+ prcnt +'%)');
			}

			function processCall(uuid){
				$.when(
					$.post('/live/run_call/'+ sessn.id +'/'+ uuid +'/', function(data){
						switch(data.returned){
							case 'processing':
								if($.inArray( uuid, incoming.progs ) == -1){
									incoming.progs.unshift(uuid);
								}								
							break;
							case 'interpreting':
								if($.inArray( uuid, incoming.progs ) == -1){
									incoming.progs.unshift(uuid);
								}
							break;
							case 'fatal':
								incoming.fatal.unshift(uuid);
							break;
							case 'flag':
								incoming.flags.unshift(uuid);
								incoming.posts.unshift(uuid);
							break;
							case 'completed':
								incoming.posts.unshift(uuid);
							break;
						}
					})
				).then( processNext );
			}
			
			function viewWrap(seconds){
				$('#check').text('Waiting '+ seconds +' seconds before checking again...');
				waitFor(seconds);
			}

			function waitFor(seconds){
				var strt = new Date().getTime();
				var crnt = strt;
				if(seconds > 0){
					while(crnt - strt < 1000){
						crnt = new Date().getTime();
					}
					seconds--;
					viewWrap(seconds);
				} else{
					$('#check').text('Checking Calls .. ');
					getCallsToProcess();
				}				
			}
			
			function remainsCalc(){
				m_hour = incoming.calls.length/3600;
				d_hour = Math.floor(m_hour);
				m_mins = ((m_hour - d_hour)*3600)/60;
				d_mins = Math.floor(m_mins);
				d_secs = Math.floor((m_mins - d_mins)*60);
				hour = d_hour < 10 ? '0' + d_hour:d_hour;
				mins = d_mins < 10 ? '0' + d_mins:d_mins;
				secs = d_secs < 10 ? '0' + d_secs:d_secs;
				return hour + ':' + mins + ':' + secs;
			}
		</script>
	</head>
	<body>
		<div class="page-content">
			<div class="row">
				<div class="col-xs-12">
					<h3 class="header smaller lighter blue"><strong id="check">Checking Calls ..</strong><strong>&nbsp;&nbsp;&nbsp;&nbsp;<b id="timer"></b></strong></h3>
					<h3 class="header smaller lighter blue"><strong id="posts">0</strong><strong>&nbsp;calls posted to database</strong></h3>
				</div>
				<div class="col-xs-12">
				<p></p>
				</div>
				<div class="col-xs-12">
					<div class="progress">
						<div id="prgbar" class="progress-bar progress-bar-success" style="width: 0%;"></div>
					</div>
				</div>
				<div class="col-xs-4">
					<h3 class="header smaller lighter blue">Calls in Progress right now</h3>
					<strong id="progs" >0</strong><strong>&nbsp;calls in progress</strong>
				</div>
				<div class="col-xs-4">
					<h3 class="header smaller lighter blue">Flagged Calls This Month</h3>
					<strong id="flags">0</strong><strong>&nbsp;flagged calls this month</strong>
				</div>
				<div class="col-xs-4">
					<h3 class="header smaller lighter red">Calls With Errors This Month</h3>
					<strong id="error">0</strong><strong>&nbsp;calls with errors this month</strong>
				</div>
			</div>
		</div><!-- /.page-content -->
		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='/assets/grocery_crud/ace/assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="/assets/grocery_crud/ace/assets/js/bootstrap.min.js"></script>
	</body>
</html>
