//shows calls waiting in queue
//TODO document me
//TODO simplify event handling by processing events server side

(function($) {

   var format_cid = function(name, number) {
     if (name == number) {
       return unescape(name);
     } else if (name == 'Unknown') {
       return format_information(number);
     } else {
       return unescape(name) + ' ' + format_information(number);
     }
   }
   var addCall = function (uuid, keyname, queue, cidname, cidnumber, time, elem) {
     var list = $('.queueCallList', elem);
     var call = null;
     list.find('.queueCallItem').each(function() {
					if ($('.waittime', this).data('time') > time) {
					  call = elem.find('.template > .queueCallItem').clone().insertBefore($(this)).data('uuid', uuid);
					  return false; // exit the foreach
					}
				      });
     if (!call) { // oldest call
       call = elem.find('.template > .queueCallItem').clone().appendTo(list).data('uuid', uuid);
     }

     call.data('queue', keyname);
     $('.cidname',call).text(formatCID(cidname, cidnumber));
     $('.waittime', call).data('time', time).text($.timeSince(time, {format: 'short', precision: 4}));

     $('.queue', call).text(queue);
     $('a.opPop', call).bind('click', function(e) {
			       var td = $(this).closest('td').addClass('disabled');
			       var uuid = $(this).closest('div.queueCallItem').data('uuid');
			       var bindID = getUnique('bind');
			       e.preventDefault();

			       $(window).bind('meteor_channel_hangup.'+bindID, function (e,d) {
						if (d.json.call_uuid === uuid) {
						  td.removeClass('disabled');
						  $(window).unbind('meteor_channel_hangup.'+bindID);
						}
					      });

			       $.ajax({
					url: '/cudatel/gui/freeswitch/queue_pick',
					data: {
					  uuid: uuid,
					  template: 'json',
					  queue_key_name: keyname,
					  caller_id_name: cidname,
					  caller_id_number: cidnumber
					},
					dataType: 'json',
					success: function(data) {
					  if (data.failure) {
					    td.removeClass('disabled').addClass('failed');
					  }
					}
				      });
			     });
     $('.queueCallNoCalls', elem).hide();
   }

   var removeCall = function(uuid, elem, reason) {
     var call = elem.find('.queueCallItem').filter(function() { return $(this).data('uuid') == uuid;});
     call.addClass('removed');
     $('.ownerName', call).text(reason);
     setTimeout(function() {
		  call.remove();
		  if (elem.find('.queueCallList .queueCallItem').length < 1 ) {
		    $('.queueCallNoCalls', elem).show();
		  }
		}, 5000);
   }

   var queueEvent = function(e, data, elem, queues) {
     var action = data.json.fifo_action;

     if (action == 'push' && queues[data.json.fifo_name]) {
       addCall(data.json.unique_id, data.json.fifo_name, queues[data.json.fifo_name].name, data.json.caller_caller_id_name,
	       data.json.caller_caller_id_number, Math.round(new Date().getTime() / 1000), elem);
     } else if (action == 'abort' || action == 'timeout' || action == 'consumer_pop') {
       var reason = '';
       var uuid = data.json.unique_id;
       if (action == 'consumer_pop') {
	 reason = formatCID(data.json.bbx_caller_id_name, data.json.bbx_caller_id_number);
	 uuid = data.json.fifo_caller_uuid;
       } else if (action == 'timeout') {
	 reason = 'Timed Out';
       } else if (action == 'abort') {
	 reason = 'Abandoned';
       }
       removeCall(uuid, elem, reason);
     }
   }

   var bootstrap = function(data, elem, queues) {
     var statuses = data.status;
     var statusselect = $('.queueSignIn > .bbx_user_status_id', elem);
     $.each(statuses, function() {
	      if ($('option:first', statusselect).attr('value') > this.bbx_user_status_id) {
		statusselect.prepend('<option value="'+this.bbx_user_status_id+'">'+this.bbx_user_status_name+'</option>');
	      } else {
		statusselect.append('<option value="'+this.bbx_user_status_id+'">'+this.bbx_user_status_name+'</option>');
	      }
	    });

     // bootstrap our queue status
     $.ajax({
       url: '/cudatel/gui/user/status',
       type: 'GET',
       data: {template: 'json'},
       dataType: 'json',
       success: function(data) {
	 statusselect.val(parseInt(data.data.bbx_user_status_id));
	 elem.parent().data('status', parseInt(data.data.bbx_user_status_id));
       }
     });



     for(var queue in data.fifo) {
       var queuename = data.fifo[queue].name;
       queues[queue] = {name: queuename, warning_seconds: data.fifo[queue].warning_seconds, critical_seconds: data.fifo[queue].critical_seconds};
       if (data.fifo[queue].callers) {
	 var callers = data.fifo[queue].callers;
	 for (var i in callers) {
	   addCall(callers[i].uuid, queue, queuename, callers[i].caller_id_name,
		   callers[i].caller_id_number, (Math.round(new Date().getTime() / 1000)) - callers[i].age, elem);
	 }
       }

     }
   }

   var defaults = {
     template: '/cudatel/ajax-html/queueCalls.html'
   };

  jQuery.fn.queueCalls = function(params) {
    if (params == 'getstatus') {
      return this.data('status');
    }
    params = $.extend({}, defaults, params);
    var target = this;
    $.getTemplate(params.template, function(html) {
		    $(target).each(function() {
			             var elem = $(html).appendTo($(this).empty());
				     var template = elem.find('.template > .queueCallItem');
				     var queues = {};

				     $(window).bind('tick', function() {
						      $('.waittime', elem).each(function() {
										  var time;
										  if (time = $(this).data('time')) {
										    $(this).text($.timeSince(time,
													     {format: 'short', precision: 4}));
										    var queue = $(this).closest('.queueCallItem');
										    var critical_seconds = queues[queue.data('queue')].critical_seconds;
										    var warning_seconds = queues[queue.data('queue')].warning_seconds;
										    if (warning_seconds || critical_seconds) {
										      var diff = Math.round(new Date().getTime() / 1000) - time;
										      if (critical_seconds && diff > critical_seconds) {
											queue.addClass('critical').removeClass('warning');
										      } else if (warning_seconds && diff > warning_seconds) {
											queue.addClass('warning').removeClass('critical');
										      } else {
											queue.removeClass('warning critical');
										      }
										    }
										  }
										});
						    });
				     if (params.data) {
				       bootstrap(params.data, elem, queues);
				       $(window).bind('meteor_queue_status', function(ev, data) { queueEvent(ev, data, elem, queues); });
				     } else {
				       $.ajax({
						url: '/cudatel/gui/queue/userlist',
						data: {template: 'json', verbose: 1},
						dataType: 'json',
						success: function(data) {
						  bootstrap(data.data, elem, queues);
						  $(window).bind('meteor_queue_status', function(ev, data) { queueEvent(ev, data, elem, queues); });
						}
					      });
				     }

				     var statusselect = $('.queueSignIn > .bbx_user_status_id', elem);

				     $(window).bind('meteor_user_'+validUsername, function(e, data) {
						      if (data.json.action == 'set_user_status') {
							elem.parent().data('status', parseInt(data.json.status_id));
							statusselect.val(parseInt(data.json.status_id));
						      }
						    });
				   });
		  });
    return $(this);
  }
 })(jQuery);
