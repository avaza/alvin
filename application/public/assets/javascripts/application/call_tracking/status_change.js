var session = getMySessionID();
var bbx_user_id;

function cuda_log_me_in(pass, user){
    jQNC.post('/gui/login/login', { template: 'json', __auth_user: user, __auth_pass: pass}, function(data){
		bbx_user_id = data.data.bbx_user_id;
        console.log('logged in');
	});
}

function getMySessionID(){
    return getMyCookie("bps_session");
}

function getMyCookie(a) {
    a = a + "=";
    for (var e = document.cookie.split(";"), f = 0; f < e.length; f++) {
        for (var j = e[f]; j.charAt(0) == " ";) j = j.substring(1, j.length);
        if (j.indexOf(a) === 0) return j.substring(a.length, j.length);
    }
    return null;
}

function set_cudatel_status(type){	
	jQNC.post('/gui/user/status', { template: 'json', bbx_user_status_id: type, sessionid: session}, function(data){
        if(data.data.bbx_user_status_id && data.data.bbx_user_status_id == type){
           jQNC.jGrowl("Status Change Successful", { theme: 'success' });
           jQNC.post('../reports/status_change/' + type +  '/' + intid + '/');
        } else{
           jQNC.jGrowl("Status Change Failed", { theme: 'error' }); 
        }      
    });
    
}