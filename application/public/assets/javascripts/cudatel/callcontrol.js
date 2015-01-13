var Ape;
var initAlready = false;

function incrementCount(target) {
    var targetelem = $(target);
    targetelem.text((parseInt(targetelem.html()) + 1).toString());
}

function decrementCount(target) {
    var targetelem = $(target);
    targetelem.text((parseInt(targetelem.html()) - 1).toString());
}

function initCallControl() {
    if (initAlready) { return; }
    initAlready = true;

    Ape.subscribe(['call_event', 'presence_event', 'call_update', 'channel_callstate', 'queue_status', 'conference_status', 'channel_hangup', 'meteor_alive', 'user_'+validUsername]);

    $('#cccTabTabs').microtabs({container: '#cccTabPanels', hashParam: 'ccctab'});
    $('#queueList').queueCalls();
    $('#conferenceManager').conferenceManager();

    $(window).trigger('login');


    $('#callControlClient')
	.bind('callCreate', function() { incrementCount('#callcount'); $('body').addClass('oncall'); })
	.bind('callLastDestroy', function() { $('#callcount').text("0"); $('body').removeClass('oncall'); })
	.bind('callDestroy', function() { decrementCount('#callcount');});

    $('a.call').live('click', function (e) {
	if ($(this).data('number')) {
	    e.preventDefault();
	    $.getJSON('/gui/freeswitch/originate', { destination: $.data(this,'number') }, {});
	} else {
	    console_log('Error: a.call clicked, has no "number" data element', this);
	}
    });

    $('a.call, a.transfer').bind('click', function () {
	$('#search').val('').trigger('keyup').blur();
    });

    $('#search').val(''); // clear the search box
    $('#clientSearchClear').bind('click', function (e,data) {
	$('#search').val('').trigger('keyup').blur();
    });
    $('#search').extensionPicker({searchTypes: ['contact', 'all'], directory: true, textFormat: 'number', hiddenFormat: 'label', categories: false, hidden: $('#searchlabel')});

    $('#search').bind('noselection', function () { $('#searchlabel').val(''); });

    $('#doSearch').bind('submit', function(e) {
	e.preventDefault();
	var dest = $('#search').val();
	$('#search').val('').trigger('keyup').blur();
	$('#search').autocomplete('close');
	$.ajax({
	    url: '/gui/freeswitch/originate',
	    data: {
		destination: dest,
		caller_id_name: $('#searchlabel', $(this)).val()
	    },
	    dataType: 'json',
	    success: function(data) {

	    }
	});
    });

    /* make the damn search thing just do a submit when the user makes a selection OR hits return */
    $('#search').bind('selection', function(e) {
	$('#doSearch').trigger('submit');
    })
	.bind('justdoit', function(e) {
	    $('#doSearch').trigger('submit');
	});


    $('.contactName').bind('click', function(ev) {
	var collapse = $(ev.target.parentNode).hasClass('contactselected');
	$('.contactselected').removeClass('contactselected');
	if(!collapse) {
	    $(ev.target.parentNode).addClass('contactselected');
	}
    });

    $('.queueName').bind('click', function(ev) {
	if (!$(ev.target.parentNode).hasClass('queueRowEmpty')) {
	    $('.queueMemberList', ev.target.parentNode).toggle();
	}
    });

    $('.clientSearchOnly').hide();

    $('.validUsername').html(loginData.bbx_user_username_printable);
    $('body').addClass('loggedin');
    $('#presenceBarLogoutButton').bind('click', function() {

	var template = $('#queueList').queueCalls('getstatus') == 2 ? 'cccLogoutPopup' : 'cccOnlineLogoutPopup';

	$.getTemplate('/ajax-html/'+template+'.html', function (html) {
	    var popup = $(html).appendTo($('body'));

	    $('.cancel',popup).one('click', function (e) {
		e.preventDefault();
		popup.remove();
	    });

	    $('.setUnavailableLogout',popup).one('click', function (e) {
		$.post('/gui/user/status', {bbx_user_status_id:2}, function () {
		    $.ajax({
			url: "/gui/login/logout",
			type: 'POST',
			cache: false,
			success: function(data, status, xhr) {
			    location.reload();
			},
			error: function() {
			    location.reload();
			}
		    });
		});
	    });

	    $('.onlyLogout',popup).one('click', function (e) {
		$.ajax({
		    url: "/gui/login/logout",
		    type: 'POST',
		    cache: false,
		    success: function(data, status, xhr) {
			location.reload();
		    },
		    error: function() {
			location.reload();
		    }
		});
	    });
	});
    });

    $('.clientSearchWrap > #search').inputOverlay();

    $.getREST('/gui/phone/myuserregs', function(regs) {
	$('#onthephone').callMonitorBar({ registrations: regs });
	$('#recentCalls').recentCalls({ registrationData: regs });
    });



    $('#addressList').addressBook();

    var debounceTO;

    $(window).bind('meteor_user_' + validUsername, function (e,d) {
	if (d.json['event-subclass'] === 'vm::maintenance' || d.json.fax_action) {
	    if (debounceTO) {
		return;
	    } else {
		debounceTO = setTimeout(function () {
		    debounceTO = undefined;
		    updateCCCVMFaxCounts();
		}, 2000);
	    }
	}
    });

    updateCCCVMFaxCounts();

} // initCallControl

function updateCCCVMFaxCounts() {
    $.getJSON('/gui/user/message_counts', function(data) {
	$('#vmcount').html(data.data.unread_voicemails);
	$('#faxcount').html(data.data.unread_faxes);
    });
}

function sendLogin(id) {
    $.ajax({
	url: "/gui/login/login",
	type: 'POST',
	dataType: 'json',
	data: $(id).serialize(),
	cache: false,
	success: function(data, status, xhr) {
	    validUsername = data.data.bbx_user_username;
	    validUserID = data.data.bbx_user_id;
            loginData = data.data;
	    loginOK();
	},
	error: function(data, status, xhr) {
	    if (data && data.status == 402) {
		$('#callControlLoginError').html("Please reset your password from your phone");
	    } else {
		$('#callControlLoginError').html("Invalid username or password");
	    }
	}
    });
}

function loginOK() {
    $.getJSON('/gui/user/has_extension', {}, function (data) {
	if (data.data.has_extension) {
	    $('#callControlClient').show();
	    $('.clientLogin').hide();

	    var waitBlanker = showBlanker({ title: 'Connecting...', text: 'Creating a Live Data Connection to your CudaTel Communication Server', icon: false, spinner: true, buttons: false });

	    // Block until Ape starts...
	    Ape = new ApeConnection(function () {
		initCallControl();
		waitBlanker.trigger('closeBlanker');
	    });

	    // Auto-sub to Ape events when binding to a specific context
	    jSoff.eventSys.onContextChange(function(name, ttl, last) {
		// console_log("callcontrol.js: Context Change: " + name + " from " + last + " to " + ttl);
		if (ttl) {
		    Ape.subscribe(name);
		}
	    });

	} else {
	    $('#callControlClient').hide();
	    $('.clientLogin').show();
	    $('#frmLogin').hide();
	    $('#callControlLoginError').html('Administrative users without extensions cannot use the Call Control Client. <a href="javascript:logout()">Log Out</a>');
	}
    });
}

$(document).ready(function() {
    titlebar = "CudaTel Call Control";
    $.ajax({
	url: "/gui/login/status",
	type: "POST",
	dataType: 'json',
	success: function (data, status, xhr) {
	    validUsername = data.data.bbx_user_username;
	    console.log(validUsername);
	    validUserID = data.data.bbx_user_id;

            loginData = data.data;
	    loginOK();
	},
	error: function (xhr, status, error) {
	    $('#callControlClient').hide();
	    $('.clientLogin').show();
	},
	cache: false
    });
});