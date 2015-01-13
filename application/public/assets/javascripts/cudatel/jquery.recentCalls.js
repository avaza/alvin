//recent calls list for call control client
//TODO document me
//TODO can we share some code with activeRecentCalls

(function($) {
    $.fn.recentCalls = function(params) {
	var elems = $(this);
	var registratons = {};
	if (params.registrationData) {
	    registrations = params.registrationData.registrations;
	}
	$.getTemplate('/cudatel/ajax-html/client/recentCalls.html', function(html) {
	    elems.each(function() {
		var elem = $(this);
		$.ajax({
		    url: '/cudatel/gui/configure/dynamic/user_dashboard',
		    data: {
			action: 'calls',
			direction: 'combined',
			template: 'json',
			rows: 20
		    },
		    dataType: 'json',
		    success: function(data) {
			html = $(html);
			var calltemplate = html.find('.template > .recentCallsItem');
			calltemplate.find('a.opCall').bind('click', function(e) {
			    e.preventDefault();
			    var closest = $(this).closest('.recentCallsItem');
			    $.getJSON('/cudatel/gui/freeswitch/originate', {
				caller_id_name: closest.data('name'),
				destination: closest.data('number')
			    }, function(data) { });
			});
			var call;
			for(var i in data.data.combined) {
			    call = data.data.combined[i];
			    var idPart = (call.direction_from_phone == 'incoming')?'caller_id':'destination';

			    if (call[idPart + '_name'] === 'Voicemail') {
				continue;
			    }

			    var callitem = calltemplate.clone(true).appendTo(html.find('.recentCallsList'));
			    callitem.addClass(call.direction_from_phone);

			    callitem.data('number', call[idPart + '_number']);
			    callitem.data('name', call[idPart + '_name']);

			    callitem.find('.cid').text(formatCID(call[idPart + '_name'], call[idPart + '_number']));
			    callitem.find('.time').text(call.start_time_past);
			    callitem.find('.duration').text($.timeSince(Math.round(new Date().getTime() / 1000) - call.duration, {format: 'medium'}));
			}
			if (data.data.combined.length != 0) {
			    html.find('.recentCallsNoCalls').hide();
			}
			html.appendTo(elem);
			$(window).bind('meteor_channel_hangup', function(ev, data) {
			    if (data.json.accountcode == validUsername ||
				data.json.bbx_user_id == validUserID ||
				registrations[parseInt(data.json.bbx_phone_registration_id, 10)] ) {

				var call = data.json;
				var direction = 'outgoing';

                                if (trueish(data.json.click_to_call)) {
				    direction = 'outgoing';
				} else if (
				    (data.json.accountcode != validUsername) &&
					(data.json.direction === 'outbound')
				) {
				    direction = 'incoming';
				}

				var idPart = (direction == 'incoming')?'caller_id':'destination';

				if (call[idPart + '_name'] === 'Voicemail') {
				    return;
				}

				var callitem = calltemplate.clone(true).prependTo(html.find('.recentCallsList'));
				
				callitem.addClass((direction == 'incoming')?'incoming':'outgoing');

				callitem.data('number', call[idPart + '_number']);
				callitem.find('.cid').text(formatCID(call[idPart + '_name'], call[idPart + '_number']));
				callitem.find('.time').text(new Date(call.start_timestamp/1000).toString('dddd, h:mm tt'));
				callitem.find('.duration').text($.timeSince(Math.round(new Date().getTime() / 1000) - call.duration, {format: 'medium'}));
				$('.recentCallsList > .recentCallsItem:gt(19)', elem).remove(); // keep only 20 of them
			    }
			});
		    }
		});
	    });
	});
	return elems;
    }
})(jQuery);
