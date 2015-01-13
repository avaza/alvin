// init.js by Rudy Fleminger
// Functions and handlers for initializing on page load or change
// TODO simplify this to act more like we do in the call control client login process

// Constants...
var LOGIN_OK = 1;
var LOGIN_FAIL = 0;
var LOGIN_NETWORK_FAIL = -1;
var LOGIN_PASS_FAIL = -2;
var LOGIN_TOO_MANY = -3;

var __loginFails = 0;
var __loginKeepalive;
var __loginMessages = '';

var validUsername = false; // For display purposes only.
var validUserID = false;
var loginData = {};

if (typeof CONNECTIVITY_BLANKER === 'undefined') {
  var CONNECTIVITY_BLANKER = false;
}

jQNC(document).bind('noEventConnection', function() {
		   if (CONNECTIVITY_BLANKER) {
		     showBlanker({
		       title: 'Lost Connectivity',
		       text: 'Due to a network or internal problem, the browser has lost its connection to the Communications Server. Please wait while the page is refreshed, or use your browser\'s "Refresh" button.',
		       buttons: false
		     });
		   }
		 });

/*
$(window).bind('login', function () {

});
*/

setTimeout( function () { loadInto($('#preloader'), 'ajax-html/preload.html'); }, 5000 );

jQNC('#noJSWarning').remove();

jQNC(document).ready( function () {
    jQNC(window).bind('hashchange.nav', function() {
	if ((location.href != __prevPage) && __prevPage) {
	    __prevPage = location.href;
	    jQNC(window).trigger('locationChange');
	}
    });
});

jQNC(document).ready( function () {
		     jQNC('#noJSWarning').remove();
		     jQNC('#ulButtonBar>li:not(.logo)').hide();

		     getLoginStatus(autoLoginStatus);
		     
		     for (var i in { safari:1, webkit:1, opera:1, msie:1, mozilla:1 }) { // Lookup table is so I can iterate through values on an anon. array
		       if ($.browser[i]) {
			 // Add every dotted part of the version number as a class (msie msie-7 msie-7-0), so we can be as specific as need be in CSS
			 var version_parts = $.browser.version.split('.');
			 var versionSpec = '';
			 var versionsOut = i + ' ';
			 for (var vpi in version_parts) {
			   var vp = version_parts[vpi].replace(/[^0-9]/g, '-');
			   versionSpec += ('-' + vp);
			   versionsOut += i + versionSpec + ' ';
			 }
			 jQNC('body').addClass(versionsOut);
		       } else {
			 jQNC('body').addClass('not-' + i);
		       }
		     }
		     
		     if ($.browser.msie && $.browser.version < 7 && !getCookie('ignore_unsupported')) {
		       location.href = '/errors/unsupported.html';
		     }
		     
		   });

function loginMessage()
{
  if (location.hash.search(/login=fail/) >= 0 && !getCookie('cookietest')) {
    jQNC('.hook.loginMessage').append('<p id="loginfail">Cookies must be enabled in your browser. Please enable cookies and try again.</p>');
    clearHashParam('login');
  } else if (location.hash.search(/login=fail/) >= 0) {
    jQNC('.hook.loginMessage').append('<p id="loginfail">Your login name or password are incorrect. Please try again.</p>');
    clearHashParam('login');
  } else if (location.hash.search(/login=passfail/) >= 0) {
    jQNC('.hook.loginMessage').append('<p id="loginfail">Your login name may not match your password.  Please change your password from your phone and try again.</p>'); 
    clearHashParam('login');
  } else if (location.hash.search(/login=lockout/) >= 0) {
    jQNC('.hook.loginMessage').append('<p id="loginfail">You have had too many failed login attempts. Please try again in 15 minutes.</p>');
    clearHashParam('login');
  } else if (location.hash.search(/login=timeout/) >= 0) {
    jQNC$('.hook.loginMessage').append('<p id="loginfail">Your session has timed out due to inactivity. Please log in again.</p>');
    clearHashParam('login');
  }

  if (__loginMessages) { $('.hook.loginMessage').append(__loginMessages); }
}

function autoLoginStatus(status, data)
{
  switch (status) {
  case LOGIN_OK:
    __loginFails = 0;
    validUsername = data.data.bbx_user_username;
    validUserID = data.data.bbx_user_id;
    initLoggedIn();
    // Add the username to the presence bar
    jQNC('.hook.validUsername').html(data.data.bbx_user_username_printable);
    jQNC('body').addClass('loggedin');

    if (trueish(data.data.bbx_extension_id)) {
	jQNC('body').addClass('extensions');
    }

    break;
  case LOGIN_TOO_MANY:
      __loginMessages += '<p id="loginfail">You have made too many failed login attempts, and your account is temporarily locked. Please try again later.</p>';
      askForPassword(data);
      break;
  case LOGIN_NETWORK_FAIL:
      __loginMessages += '<p id="loginfail">There was an internal or network error logging you in. If the system was rebooting or completing an install, it may not be completely ready. Otherwise, please check your browser&rsquo;s connection to the Phone Server...</p>';
      loginTestLoop();
      askForPassword(data);
      break;
  case LOGIN_FAIL:
    askForPassword(data);
    break;
  case LOGIN_PASS_FAIL:
    askForPassword(data);
    break;
  }
}

function handleLoginStatus(status)
{
  switch (status) {
  case LOGIN_OK:
    __loginFails = 0;
    location.reload(); // Need to reload to update SSIs
    break;
  case LOGIN_FAIL:
    location.hash = location.hash.replace(/;?login=[^;]+/, '') + ';login=fail';
    location.reload();
    break;
  case LOGIN_TOO_MANY:
      location.hash = location.hash.replace(/;?login=[^;]+/, '') + ';login=lockout';
      location.reload();
      break;
  case LOGIN_PASS_FAIL:
    location.hash = location.hash.replace(/;?login=[^;]+/, '') + ';login=passfail';
    location.reload();
    break;
  case LOGIN_NETWORK_FAIL:
    __loginMessages += '<p id="loginfail">There was an internal or network error logging you in. Please try again.</p>';
    location.reload();
    break;
  }
}

function initApe(handler) {
    Ape = new ApeConnection(handler, { reloadAfter: 2000 } );
    // TODO fix this to be better
    

    // Auto-sub to Ape events when binding to a specific context
    jSoff.eventSys.onContextChange(function(name, ttl, last) {
	// console_log("init.js: Context Change: " + name + " from " + last + " to " + ttl);
	if (ttl) {
	    Ape.subscribe(name);
	}
    });

    // LEGACY WE SHOULD STRIVE TO REMOVE THIS 
    Ape.subscribe(['global_messages', 'restore', 'provision', 'system_status', 'support_tunnel', 'update_status', 'system_restart',
		   'ldap_import','sound_recording', 'meteor_alive', 'provider_status', 'conference_status', 'queue_status',
		   'user_status','port_status', 'call_event', 'admin', 'user_'+validUsername]);
}

var LOGIN_FIRED = false;

function initLoggedIn()
{

    var pw = showBlanker({
	title: 'Establishing Connection',
	text: '\n\nEstablishing a Live Connection to your CudaTel Communication Server...',
	icon: '/images/cudatel_logo.png',
	spinner: true,
        iconCSS: { top: '140px', left: '250px', width: '150px' },
        buttons: false,
	shade: false
    });

    initApe(function() {
	if (!LOGIN_FIRED) {
	    jQNC(window).trigger('login');
	    LOGIN_FIRED = true;
	}
	pw.trigger('closeBlanker');

	jQNC(window).bind('meteor_user_' + validUsername, function (e,d) {
	    if (d.logout && d.session_id === getSessionID()) {
		location.reload();
	    }
	});
    });


  __loginKeepalive = setInterval( function () {
				    jQNC.ajax({
				      url: '/gui/login/status',
				      success: function (data) {
					     if (data && data.data.bbx_user_username != validUsername) {
					       // Our username has changed: Refresh!
					       location.reload();
					     }
					   },
				      error: function () { location.reload(); },
				      dataType: 'json'
				    });
//				  }, 3000 ); // Debug
				  }, 60000 ); // 1 minute keepalive
}

function askForPassword(replyData)
{
  jQNC(window).bind('screenInit', function (e,data) {
		   if (data.screen.name == 'login') {
		     setTimeout(function () { $('#txtLoginUsername').each( function () { this.focus(); } ); }, 200);
		     if (replyData && replyData.data && trueish(replyData.data.demo)) {
		       $('#screen-login .demoModeInstructions').show();
		     }
		   }
		 });
  
  switchScreen('login');
}

function sendLogin(form)
{
  jQNC('.hook.loginMessage').hide();
  jQNC(form).hide().after(PLEASE_WAIT_MESSAGE);

  var doSendLogin = function () {
    var formdata = $(form).serialize();
    
    jQNC(form)[0].reset();
    jQNC.ajax({
	     url: '/gui/login/login',
	     type: 'POST',
	     dataType: 'json',
	     data: formdata,
	     success: function (data, status, xhr) {
	       catchLoginStatus(data, status, handleLoginStatus);
	     },
	     error: function (xhr, status, error) {
	       failLoginStatus(error, status, xhr, handleLoginStatus);
	     },
	     cache: false
	   });
  }
  
  if (jQNC('#txtLoginUsername', form).val() == 'guest') {

    jQNC.ajax({
	     url: '/gui/login/status',
	     dataType: 'json',
	     error: function () { /* No red bar! */ },
	     complete: function (xhr) {
	       var data = $.parseJSON(xhr.responseText);
	       var loginData = data.data;

	       if (loginData.demo) {
		 var popup = showPopup('demo_splash', {}, true);
		 popup.bind('popup_ready', function(e, data) {
			      jQNC('a',popup).bind('click', function (e) {
						  e.preventDefault();
						  if (jQNC(this).closest('.adminDemo')[0]) {
						    // Login as admin
						    jQNC('input[name=__auth_user]', form).val('guestadmin');
						    jQNC('input[name=__auth_pass]', form).val('0000');
						  } else {
						    // Login as user
						    jQNC('input[name=__auth_user]', form).val('guestuser');
						    jQNC('input[name=__auth_pass]', form).val('0000');
						  }
						  doSendLogin();
						});
			    });
	       } else {
		 doSendLogin();
	       }
	     }
	   });
  } else {
    doSendLogin();
  }
}



function getLoginStatus(callback, pData)
{
  var pData = ( pData || '' );
  var pURL = '/gui/login/status';
  
  jQNC.ajax({
	   url: pURL,
	   type: 'POST',
	   dataType: 'json',
	   data: pData,
	   success: function (data, status, xhr) { catchLoginStatus(data, status, callback); },
	   error: function (xhr, status, error) { failLoginStatus(error, status, xhr, callback); },
	   cache: false
	 });
}

function failLoginStatus(data, status, xhr, callback)
{
  var xhrData = {};
  if (xhr.responseText) {
    try {
      var xhrtry = JSON.parse(xhr.responseText);
      xhrData = xhrtry;
    } catch (err) {}
  }
  
  if (xhr.status == 403 || xhr.status == 401) {
      var header = xhr.getResponseHeader('X-Auth-Error') || 'NO HEADER FOUND';

      if (header === 'LOCKED') {
	  callback(LOGIN_TOO_MANY, xhrData);
      } else {
	  callback(LOGIN_FAIL, xhrData);
      }
  } else if (xhr.status == 402) {
    callback(LOGIN_PASS_FAIL, xhrData);    
  } else {
    callback(LOGIN_NETWORK_FAIL, xhrData);
  }
}

function catchLoginStatus(data, status, callback)
{
  // if we get here, we're OK
  loginData = data.data;
  callback(LOGIN_OK, data);
}

function loginTestLoop()
{
  var ltlInterval = setInterval( function () {
				   if (jQNC('#loginTestLoopStatus')[0]) {
				     jQNC('#loginTestLoopStatus').html('<img style="vertical-align: middle" src="/images/wait.gif" width="24" height="24" /> Waiting for the Phone Server to Respond...').show();
				     jQNC('#frmLogin').hide();
				     jQNC.ajax({
					      url: '/gui/login/test',
					      success: function () { // catalyst is up, probably good to go
					       jQNC('#loginTestLoopStatus').html('The Phone Server is ready. You may now log in.');
					       jQNC('#frmLogin').show();
						jQNC('#loginfail').remove();
					       clearTimeout(ltlInterval);
					      },
					      error: function() {},
					      cache: false
					   });
				   } else {
				     clearInterval(ltlInterval);
				   }
				 }, 5000)
}