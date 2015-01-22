<pre id="uuid"></pre>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
function get_uuid(user, session_id){
	query_url = '<?php echo base_url(); ?>live/get_jol_uuid/<?php echo $this->session->userdata("ext"); ?>/' + session_id + '/';
	$.ajaxSetup({ cache: false, global: false , error: function(xhr, status, err){if (xhr.status == 401 || xhr.status == 400){db_login();}}});
	$.getJSON(query_url, function(data, status, response){
		if(data.list.length > 0){
			$(data.list).each(function(index, object){
				if(object.callstate == "ACTIVE" && object.bleg_callstate == "ACTIVE"){
					$('#uuid').text(object.uuid);
				}
			});
		}
	});
}

function get_session(user, pass){
	session_url = '<?php echo base_url(); ?>live/get_jol_session/' + user + '/' + pass + '/';
	$.getJSON(session_url, function(data, status, response){
		if(data.session_id){
			get_uuid(user, data.session_id);
		}
	});
}

function db_login(){
	$.get('<?php echo base_url(); ?>login/admin_login/<?php echo $username . "/" . $password . "/";?>')
	.done(function(data){
		if(data.login === true){
			get_session(data.ext, data.pin);
		} else{
			console.log('FAIL');
		}
	});
}

$(document).ready(function(){
	db_login();
});
</script>