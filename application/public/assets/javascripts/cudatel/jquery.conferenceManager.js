// conference manager

// TODO document me
// TODO simplify event handling on server side to send me easier to handle events

(function($) {
   var energies= {
     0: "Silent Room",
     1: "No Noise",
     2: "Quiet Room",
     3: "Loud Room",
     4: "Very Loud",
     5: "Heavy Noise",
     6: "Extreme Noise",
     7: "Max. Noise"
   };

    var maxEnergy = 7;
    var energyMultiplier = 250;

   var setFlags = function(flags, elem) {
     elem.removeClass('deaf muted floor moderator video talking endConference');
     if (flags.can_hear == 'false') {
       elem.addClass('deaf');
     }
     if (flags.can_speak == 'false') {
       elem.addClass('muted');
     }
     if (flags.has_floor == 'true') {
       elem.addClass('floor');
     }
     //if (flags.is_moderator == 'true') {
     //elem.addClass('moderator');
     //}
     if (flags.has_video == 'true') {
       elem.addClass('video');
     }
     if (flags.talking == 'true') {
       elem.addClass('talking');
     }
     if (flags.end_conference == 'true') {
       elem.addClass('endConference');
     }
   }
   
   var addMember = function(conf, event, elem) {
     var memberelem = $('.template .conferenceMember', elem).clone(true);
     memberelem.data('id', event.member_id);
     memberelem.data('uuid', event['unique-id']);
     memberelem.data('energy', 250);
     memberelem.data('volume_in', 0);
     memberelem.data('volume_out', 0);
	 if (event.caller_caller_id_name && typeof event.caller_caller_id_name !== "object") {
		 memberelem.find('.cidName').text(unescape(event.caller_caller_id_name));
	 } else if (event.caller_caller_id_name) {
		 console_log('jquery.conferenceManager.js: Unknown type for event.caller_caller_id_name:', event.caller_caller_id_name);
	 } else {
		 memberelem.find('.cidName').text(unescape(event.caller_caller_id_number));
	 }
     memberelem.find('.cidNumber').text(format_information(event.caller_caller_id_number));
	 if (event.caller_caller_id_name && typeof event.caller_caller_id_name !== "object") {
		 memberelem.find('.cidName').text(unescape(event.caller_caller_id_name));
	 } else if (event.caller_caller_id_name) {
		 console_log('jquery.conferenceManager.js: Unknown type for event.caller_caller_id_name:', event.caller_caller_id_name);
	 } else {
		 memberelem.find('.cidName').text(unescape(event.caller_caller_id_number));
	 }
     updateFlags(memberelem, event);
     memberelem.appendTo($('.conferenceBody', conf));
     constructPopup(memberelem);
   }
   
   var updateFlags = function(memberelem, event) {
     setFlags({
		can_hear: event.hear,
		can_speak: event.speak,
		has_floor: event.floor,
		has_video: event.video,
		talking: event.talking,
		is_moderator: event.member_type == 'moderator' ? 'true' : 'false' // TODO is this correct?
	      }, memberelem);
   }
   
   var conference_status_event = function(event, elem) {
     switch (event.action) {
     case 'del-member':
       if (event.conference_size == "0") {
	 // remove the whole conference
	 var member = elem.find('.conferenceMember').filter(function() { return $(this).data('id') == event.member_id; });
	 member.trigger('remove');
	 
	 elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; }).remove();
	 if (elem.find('.conferenceList > .conference').length == 0) {
	   // all out of conferences
	   $('.conferenceNoConferences', elem).show();
	 }
       } else {
	 // just remove the member
	 var member = elem.find('.conferenceMember').filter(function() { return $(this).data('id') == event.member_id; });
	 member.trigger('remove');
	 member.remove();
	 // update the listed size
	 var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; })
	 $('.conferenceMemberCount', conf).text(event.conference_size);
       }
       break;
     case 'add-member':
       // try to find the conference
       var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; });
       var newconf = false;
       if (!conf[0]) {
	 // no such conference, lets create it
	 conf = $('.template > .conference', elem).clone(true);
	 conf.data('name', event.conference_name);
	 newconf = true;
	 $.ajax({
		  url: '/cudatel/gui/conference/info',
		  data: {template: 'json', bbx_extension_value: event.conference_name},
		  dataType: 'json',
		  success: function(data) {
		    $('.conferenceName', conf).text(data.data.name);
		    $('.conferenceExtension', conf).text(event.conference_name);
		    conf.data('label', data.data.name);
		    conf.data('extension', event.conference_name);
		    if (data.data.pin_secured) {
		      conf.addClass('secured');
		    }
		    if (data.data.moderator) {
		      conf.addClass('moderator');
		    }
		    $('.conferenceNoConferences', elem).hide();
		    conf.appendTo($('.conferenceList', elem));
		    $('.conferenceMemberCount', conf).text(event.conference_size);
		    addMember(conf, event, elem);
		  },
		  error: function() {
		    // NOOP
		  }
		});
       } else {
	 $('.conferenceMemberCount', conf).text(event.conference_size);
	 addMember(conf, event, elem);
       }
       break;
     case 'start-talking':
     case 'stop-talking':
     case 'mute-member':
     case 'unmute-member':
       updateFlags(elem.find('.conferenceMember').filter(function() { return $(this).data('id') == event.member_id; }), event);
       break;
     case 'floor-change':
       // remove the 'floor' class from everyone else and add it to this member
       var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; });
       var member = conf.find('.conferenceMember').filter(function() { return $(this).data('id') == event['new-id']; }).addClass('floor');
       conf.find('.conferenceMember').not(member).removeClass('floor');
       break;
     case 'lock':
       elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; }).addClass('closed');
       break;
     case 'unlock':
       elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; }).removeClass('closed');
       break;
     case 'energy-level-member':
       var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; });
       conf.find('.conferenceMember').filter(function() { return $(this).data('id') == event['member_id']; }).data('energy', parseInt(event['energy-level']));
       break;
     case 'volume-in-member':
       var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; });
       conf.find('.conferenceMember').filter(function() { return $(this).data('id') == event['member_id']; }).data('volume_in', parseInt(event['volume-level']));
       break;
     case 'volume-out-member':
       var conf = elem.find('.conference').filter(function() { return $(this).data('name') == event.conference_name; });
       conf.find('.conferenceMember').filter(function() { return $(this).data('id') == event['member_id']; }).data('volume_out', parseInt(event['volume-level']));
       break;

       // energy - 
       // TODO lock, unlock, transfer, energy_level, volume_level, gain_level, mute-detect, mute_member, unmute_member, deaf-member, undeaf-member, kick-member
       // energy-level-member, volume-in-member, volume-out-member
     default:
       //console_log('fallthrough', event);
       break;
     }
   }
   
   var opTransferPopup = function (e) {
     e.preventDefault();
     var member = $(this).closest('.conferenceMember');
     var conference = member.closest('.conference');
     var uuid = member.data('uuid');
     var callName = $('.cidName', member).text();
     var callElems = conference.siblings('.conference');
     var callElemsArray = [];
     callElems.each(function () {
		      callElemsArray.push({
					    name: $('.conferenceName',this).text(),
					    number: $('.conferenceExtension',this).text(),
					    id: $(this).data('name')
					  });
		    });
     
     $.getTemplate('/cudatel/ajax-html/conferenceTransferPopup.html', function(html) {
		     var popup = $(html).appendTo('body');
		     $('.destination',popup).focus().extensionPicker();
		     $('.callerUUID',popup).val(uuid);
		     $('.callerName',popup).text(callName);
		     $('.transferForm',popup).one('submit', function (e) {
						    e.preventDefault();
						    $.post('/cudatel/gui/freeswitch/uuid/uuid_transfer',
							   { template: 'json', uuid: uuid, extension: $('.destination', popup).val()} );
						    popup.remove();
						  });
		     
		     $('.cancel', popup).bind('click', function (e) {
						e.preventDefault();
						popup.remove();
					      });
		     
		     
		     if (callElemsArray[0]) {
		       $('.uuidUuidBridgeList',popup).empty();
		       $.each(callElemsArray, function () {
				$('.uuidUuidBridgeList',popup).append('<li><a class="uuidUuidTransfer" href="#' + this.id + '">' + (this.name || this.number) + '</a></li>');
			      });
		       
		     }

		     $('.uuidUuidBridgeList a.uuidUuidTransfer',popup).bind('click',function (e) {
									      e.preventDefault();
									      var destination = $(this).attr('href').replace(/^.*#/,'');
									      $.post('/cudatel/gui/conference/member/transfer', {
										       template: 'json',
										       member_id: member.data('id'),
										       conference_name: conference.data('name'),
										       new_conference_name: destination
										     });
									      popup.remove();
									    });
		   });
   };
   
   var opInvitePopup = function(e) {
     e.preventDefault();
     var conference = $(this).closest('.conference');
     $.getTemplate('/cudatel/ajax-html/conferenceCalloutPopup.html', function(html) {
		     var popup = $(html).appendTo('body');
		     $('.destination',popup).focus().extensionPicker();
		     $('.transferForm',popup).one('submit', function (e) {
			 e.preventDefault();
			 
			 var dest = $('.destination', popup).val();
			 if (dest === '') {
			     return;
			 }

			 $.post('/cudatel/gui/conference/dial',
				{
				    template: 'json',
				    caller_id_name: conference.data('label'),
				    caller_id_number: conference.data('extension'),
				    conference_name: conference.data('name'),
				    destination: dest
				} );
			 popup.remove();
		     });
		     
		     $('.cancel', popup).bind('click', function (e) {
						e.preventDefault();
						popup.remove();
					      });
		   });

   }
   
   var setEnergy = function(member, content) {
     $.ajax({
       url: '/cudatel/gui/conference/member/energy',
	      data: {
		template: 'json',
		member_id: member.data('id'),
		conference_name: member.closest('.conference').data('name'),
		energy: ($('.energySlider', content).slider('value') * energyMultiplier)
	      },
	      dataType: 'json'
	    });
   };

   var setGain = function(member, content) {
     $.ajax({
       url: '/cudatel/gui/conference/member/volume_in',
	      data: {
		template: 'json',
		member_id: member.data('id'),
		conference_name: member.closest('.conference').data('name'),
		volume_in: $('.gainSlider', content).slider('value')
	      },
	      dataType: 'json'
	    });
   };

   var constructPopup = function(member) {
     $('.opConferenceLevels', member).bind('click', function(e) {
						 e.preventDefault();
					       }).qtip({
							 style: {
							   tip: 'rightTop'
							 },
							 content: member.closest('.conferenceManager').find('.template > .levelPopup'),
							 show: {
							   when: {
							     event: 'click'
							   }
							 },
							 hide: {
							   when: {
							     event: 'unfocus'
							   }
							 },
							 position: {
							   target: $('.opConferenceLevels', member),
							   corner: {
							     target: 'leftMiddle',
							     tooltip: 'rightTop'
							   }
							 },
							 api: {
							   onRender: function(ev) {
							     var content = this.elements.content;
							     $('.energySlider', content).slider({
								 orientation: 'vertical',
								 min: 0,
								 max: maxEnergy
							     })
								   .bind('slide', function(ev, ui) {
								       $(this).find('.handleLabel').text(energies[ui.value]);
								   })
								   .bind('slidestop', function(ev, ui) {
								       setEnergy(member, content);
								   });

							     $('.gainSlider', content).slider({
								 orientation: 'vertical',
								 min: -4,
								 max: 4
							     })
								   .bind('slide', function(ev, ui) {
								       $(this).find('.handleLabel').text(ui.value.toString());
								   })
								   .bind('slidestop', function(ev, ui) {
								       setGain(member, content);
								   })
							       

							   },
							   onShow: function(ev) {
							     var content = this.elements.content;
							     var energy = member.data('energy') / energyMultiplier;

							     $('.energySlider', content).slider('value', energy);
							     $('.energySlider', content).find('.handleLabel').text(energies[energy]);
							     $('.gainSlider', content).slider('value', member.data('volume_in'));
							     $('.gainSlider', content).find('.handleLabel').text(member.data('volume_in') || '0');
							   }
							 }
						       });
     member.bind('remove', function() {
		   var qtipelem = $('.opConferenceLevels', member);
		   if (qtipelem.data('qtip')) {
		     $('.opConferenceLevels', member).qtip("destroy");
		   }
     });
   }
   
   
   $.fn.conferenceManager = function(params) {
     var elems = this;
     $.getTemplate('/cudatel/ajax-html/conferenceManager.html', function(html) {
		     elems.each(function() {
			 var elem = $(html).appendTo($(this).empty());

				  $('a.opConferenceMuteAll', elem).bind('click', function(e) {
								       e.preventDefault();
								       var conference = $(this).closest('.conference');

								       $.ajax({
										url: '/cudatel/gui/conference/member/mute',
										data: {
										  member_id: 'all',
										  conference_name: conference.data('name')
										},
										dataType: 'json',
										success: function() {
										  $('.conferenceMember', conference).addClass('muted');
										}
									      });
								     });
				  $('a.opConferenceUnMuteAll', elem).bind('click', function(e) {
								       e.preventDefault();
								       var conference = $(this).closest('.conference');

								       $.ajax({
										url: '/cudatel/gui/conference/member/unmute',
										data: {
										  member_id: 'all',
										  conference_name: conference.data('name')
										},
										dataType: 'json',
										success: function() {
										  $('.conferenceMember', conference).addClass('muted');
										}
									      });
								     });
				  $('a.opConferenceMute', elem).bind('click', function(e) {
								       e.preventDefault();
								       var member = $(this).closest('.conferenceMember');
								       var conference = member.closest('.conference');

								       $.ajax({
										url: '/cudatel/gui/conference/member/mute',
										data: {
										  member_id: member.data('id'),
										  conference_name: conference.data('name')
										},
										dataType: 'json',
										success: function() {
										  member.addClass('muted');
										}
									      });
								     });
				  $('a.opConferenceUnMute', elem).bind('click', function(e) {
									 e.preventDefault();
									 var member = $(this).closest('.conferenceMember');
									 var conference = member.closest('.conference');

									 $.ajax({
										  url: '/cudatel/gui/conference/member/unmute',
										  data: {
										    member_id: member.data('id'),
										    conference_name: conference.data('name')
										  },
										  dataType: 'json',
										  success: function() {
										    member.removeClass('muted');
										  }
										});
								       });
				  $('a.opConferenceLock', elem).bind('click', function(e) {
								       e.preventDefault();
								       var conference = $(this).closest('.conference');
								       
								       if (!conference.hasClass('moderator')) {
									 return;
								       }

								       $.ajax({
										url: '/cudatel/gui/conference/lock',
										data: {
										  conference_name: conference.data('name')
										},
										dataType: 'json',
										success: function() {
										  conference.addClass('closed');
										}
									      });
								     });
				  $('a.opConferenceUnlock', elem).bind('click', function(e) {
									 e.preventDefault();
									 var conference = $(this).closest('.conference');
									 
									 if (!conference.hasClass('moderator')) {
									   return;
									 }

									 $.ajax({
										  url: '/cudatel/gui/conference/unlock',
										  data: {
										    conference_name: conference.data('name')
										  },
										  dataType: 'json',
										  success: function() {
										    conference.removeClass('closed');
										  }
										});
								       });
				  $('a.opConferenceTransfer', elem).bind('click', opTransferPopup);
				  
				  $('a.opConferenceInvite', elem).bind('click', opInvitePopup);
				  
				  //$('a.opConferenceLevels', elem).bind(click, )
				  
				  $('a.opConferenceKick', elem).bind('click', function(e) {
								       e.preventDefault();
								       if (confirm("Remove "+ (0 || 'this person') + ' from the conference?')) {
									 
									 var member = $(this).closest('.conferenceMember');
									 var conference = member.closest('.conference');

									 $.ajax({
										  url: '/cudatel/gui/conference/member/kick',
										  data: {
										    member_id: member.data('id'),
										    conference_name: conference.data('name')
										  },
										  dataType: 'json'
										});
								       }

								     });
				  $.ajax({
					   url: '/cudatel/gui/conference/list',
					   data: { template: 'json' },
					   dataType: 'json',
					   success: function(data) {
					     for(var name in data.data.conference) {
					       var conf = $('.template > .conference', elem).clone(true);
					       $('.conferenceName', conf).text(data.data.conference[name].name);
					       $('.conferenceExtension', conf).text(data.data.conference[name].conference_ext.ext);
					       conf.data('name', name);
					       conf.data('label', data.data.conference[name].name);
					       conf.data('extension', data.data.conference[name].conference_ext.ext);
					       $('.conferenceMemberCount', conf).text(data.data.conference[name]['member-count']);
					       if (data.data.conference[name].pin_secured) {
						 conf.addClass('secured');
					       }
					       if (data.data.conference[name].locked) {
						 conf.addClass('closed');
					       }
					       if (data.data.conference[name].moderator) {
						 conf.addClass('moderator');
					       }
					       for (var id in data.data.conference[name].members.member) {
						 var member = data.data.conference[name].members.member[id];
						 var memberelem = $('.template .conferenceMember', elem).clone(true);
						 memberelem.data('uuid', member.uuid);
						 memberelem.data('id', id);
						 memberelem.data('energy', parseInt(member.energy));
						 memberelem.data('volume_in', parseInt(member.volume_in));
						 memberelem.data('volume_out', parseInt(member.volume_out));
						 memberelem.find('.cidName').text(unescape(member.caller_id_name));
						 memberelem.find('.cidNumber').text(format_information(member.caller_id_number));
						 setFlags(member.flags, memberelem);
						 memberelem.appendTo($('.conferenceBody', conf));
					       }
					       $('.conferenceNoConferences', elem).hide();
					       conf.appendTo($('.conferenceList', elem));
					     }
					     elem.find('.conferenceBody > .conferenceMember').each(function() {
										   constructPopup($(this));
										 });
					     $(window).bind('meteor_conference_status', function(ev, data) {
							      conference_status_event(data.json, elem);
							    });
					   }
					 });
				});
		   });
     return $(this);
   }
 })(jQuery);
