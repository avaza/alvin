$(document).ready( function () {
    $.ajax({
	url: "http://192.168.1.252/cudatel/gui/login/status",
	type: "GET",
	dataType: 'json',
	success: function (data, status, xhr) {
	    validUsername = data.data.bbx_user_username;
	    Ape = new ApeConnection(false, { reloadAfter: 2000 });
	    Ape.subscribe(['queue_status', 'user_status']);
	    $(window).trigger('login');
	    $('#wallboardContent').agentBoard({ autosize: true });
	    setInterval( function () {
                $.ajax({
		    url: 'http://192.168.1.252/cudatel/gui/login/status',
		    success: function (data) {
			return;
                    },
		    error: function () {
			location.reload();
		    },
		    dataType: 'json'
                });
            }, 60000 ); // 1 minute keepalive
	},
	error: function (xhr, status, error) {
	    showError("Your login session has expired");
	},
	cache: false
    });
});