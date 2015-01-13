//TODO we shouldn't be needing to poll on status now that we are using session cookies
//TODO we need a better place to handle ape subscriptions when they actually need to be subscribed

$(window).bind('login', function () {
		 setInterval( function () { $.ajax({
							url: '/cudatel/gui/login/status',
							cache: false,
							error: function() {
							  location.reload(); // we got logged out, redisplay the page
							}
						      }) }, 180000 );
		    
		    Ape.subscribe('user_'+validUsername);
		    $(window).bind('meteor_user_'+validUsername, function (e,data) {
				     if(data.json && data.json.logout) {
				       // someone with our username logged out, check if its us
				       $.ajax({
						url: '/cudatel/gui/login/status',
						cache: false,
						error: function() {
						  location.reload(); // we logged out, redisplay
						}
					      });
				     }
				   });
		  });
