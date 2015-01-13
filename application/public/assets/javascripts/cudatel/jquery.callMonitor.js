(function ($) {
    var OPERATION_BUTTONS = [
	{ name: 'answer', className: 'opAnswer', src: '/images/client/calls/call.png', title: 'Answer Call', fn: '_answer' },
	{ name: 'transfer', className: 'opTransfer', src: '/images/client/calls/transfer.png', title: 'Transfer Call', fn: '_show_transfer_popup' },
	{ name: 'hold', className: 'opHold', src: '/images/client/calls/hold.png', title: 'Hold', fn: '_hold' },
	{ name: 'unhold', className: 'opResume', src: '/images/client/calls/resume.png', title: 'Resume', fn: '_unhold' },
	{ name: 'kill', className: 'opKill', src: '/images/client/calls/kill.png', title: 'Hang Up', fn: '_kill' },
	{ name: 'killmulti', className: 'opKillMulti', src: '/images/client/calls/kill.png', title: 'Hang Up', fn: '_kill_multi' }
    ];

    var STATE_ICONS = {
	incoming: { className: 'stateIncoming', src: '/images/client/state/incoming.png', title: 'Call In' },
	outgoing: { className: 'stateOutgoing', src: '/images/client/state/outgoing.png', title: 'Call Out' },
	onhold:   { className: 'stateOnhold', src: '/images/client/state/onhold.png', title: 'On Hold' },
	ringin:   { className: 'stateRingin', src: '/images/client/state/ringin.png', title: 'Ringing' },
	ringout:  { className: 'stateRingout', src: '/images/client/state/ringout.png', title: 'Waiting' }
    };

    var ROW_HTML_STRUCTURE = '<div class="callObject"><span class="state" /><span class="name" /><span class="number" /><div class="callMonitorOps" /><div class="callMonitorTimer" /><div class="callMonitorMulti" />';
    
    var PREVENT_DEFAULT = function (e) { e.preventDefault(); };
    var ALLOWABLE_DRIFT = 1000, SERVER_OFFSET = 0;
    
    var update_server_offset = function (server_milliepoch) {
	if (!server_milliepoch || Number(server_milliepoch) === NaN) {
	    console_log('jquery.callMonitor.js: No server time given to update_server_offset, using system time without an offset');
	    server_milliepoch = new Date().getTime();
	}

	server_milliepoch = Number(server_milliepoch);

	var new_offset = server_milliepoch - new Date().getTime();

	if (Math.abs(new_offset - SERVER_OFFSET) > ALLOWABLE_DRIFT) {
	    SERVER_OFFSET = new_offset;
	}

	return SERVER_OFFSET;
    };
    
    var get_server_milliepoch = function () {
	return new Date().getTime() + SERVER_OFFSET;
    };
    
    $.widget('cui2.callMonitorBar', $.extend({}, CUI.dataTableClass, {
	options: {
	    call_monitor_data: [],
	    
	    destroy_cb: [],
	    to: [],
	    interval: [],
	    
	    user_info: {
/*		'page' : 1,
		'page_size' : 100,
		'sort_by' : 'id',
		'distinct_on' : 'id',
		'allow_null_distinct' : false,
		'order_by' : 'id',
		'search' : {} */
	    }
	},

	_init: function () {
	    var self = this, $self = this.element;
	    self.options.$container = $('<div class="callMonitorContainer" />').appendTo($self);
	    self.options.live_ident = getUnique('ltref');
	    self.options.widget_id = getUnique('callMonitor');
	    self.options.live_table = 'live_calls';
	    self.options.live_table_key = '';

	    self.options.user_info.search = {
		a_bbx_user_id: '^' + validUserID + '$',
		b_bbx_user_id: '^' + validUserID + '$'
	    };
	    
	    // console_log('Init:', self.options);

	    self._dataTableInit();
	    self._liveDataTableSubscribe('liveTable_live_calls__', undefined, self.options.user_info);
	},
	
	_generate_answer_list: function (uuid) {
	    var self = this, $self = this.element;
	    // TODO: How are we indicating multi-answer calls?
	},
	
	// DTW hook-ins
	_afterAddRow: function (data, index) {
	    var self = this, $self = this.element;
	    
	    // console_log('EVENT: Add row:', index, data);

	    // Update server time offset -- if no time value is available, use the client time
	    update_server_offset(data.current_time ? (parseInt(data.current_time, 10) * 1000) : undefined);
	    
	    data = self._process_row_data(data);
	    data._index = index;
	    
	    self.options.call_monitor_data.splice(index,0,data);

	    if (!self.options._in_bootstrap) {
		var $row = self._insert_row(data, index);
		if (!$row) { return; } // This is a duplicate row
		$row.hide();
		self._update_height();

		setTimeout(function() {
		    if ($row.closest('body')[0]) {
			$row.show();
			self._update_height();
		    }
		}, 750);
	    }
	},
	
	_afterSetOriginalRowData: function () {
	    this._afterSetRowData.apply(this, arguments);
	},

	_afterSetRowData: function (index, data) {
	    var self = this, $self = this.element;
	    var $row = self.options.call_monitor_data[index].$row, ringing = self.options.call_monitor_data[index]._ringing;
	    
	    // console_log('EVENT: Update row:', index, data);

	    data = self._process_row_data(data);
	    data._index = index;
	    data._ringing = ringing;
	    data.$row = $row;
	    
	    self.options.call_monitor_data[index] = data;

	    if (data._my_leg) {
		// Don't update if there's no _my_leg -- this only happens when the call is in the process of being torn down
		self._update_row(index, data);
	    }
	},
	    
	_afterDeleteRow: function (index) {
	    var self = this, $self = this.element;
	    var row = self.options.call_monitor_data[index];

	    // console_log('EVENT: Delete row:', index, row);
	    
	    if (row) {
		if (row._ticker) {
		    $(window).unbind('halfTick.' + row._ticker.bind_ns);
		}
		self._delete_row(index);
		self.options.call_monitor_data.splice(index, 1);
	    }
	},

	_clear: function () {
	    var self = this, $self = this.element, cmd = self.options.call_monitor_data;
	    var i = cmd.length;

	    while (i--) {
		if (cmd[i].ticker) {
		    $(window).unbind('halfTick.' + cmd[i].ticker.bind_ns);
		}

		self._delete_row(i);
	    }

	    self.options.call_monitor_data = [];
	},
	    
	// Minimal DTW compatibility functions
	_validateRESTContainer: function (d) { return this.options.rest_container || ''; },
	_setTimeout: function (f,t) { var id = setTimeout(f,t); this.options.to.push(id); return id; },
	_setInterval: function (f,i) { var id = setInterval(f,i); this.options.to.push(id); return id; },
	_clearTimeout: function (id) { return clearTimeout(id); },
	_clearInterval: function (id) { return clearInterval(id); },
	_addDestroyCallback: function (cb) {
	    this.options.destroy_cb.push(cb);
	},
	
	_insert_row: function (data, index) {
	    var self = this, $self = this.element, $container = self.options.$container;
	    var existing_ringing_index = self._match_existing_ringing(data);

	    if (existing_ringing_index !== false) {
		self._add_ringing_phone(data, existing_ringing_index);
		return;
	    } else {
		data.$row = self._create_$row(data);
		
		if (index == 0) {
		    data.$row.prependTo($container);
		} else {
		    var $existing = $container.children('.callObject');
		    
		    if ($existing.length <= index) {
			data.$row.appendTo($container);
		    } else {
			data.$row.insertAfter($existing[index]);
		    }
		}
		self._update_height();
	    }
	    return data.$row;
	},
	
	_update_row: function (index, data) {
	    var self = this, $self = this.element, $container = self.options.$container;
	    var $row;
	    var $existing = data.$row;
	    
	    if (!$existing) {
		// Row does not have DOM-- it's likely a "ringing" row. If it's not, create DOM for it, because it's a "ringing" row that got picked up.
		if (self._match_existing_ringing(data) === false) {
		    self._insert_row(data, index);
		}
		return;
	    }

	    data.$row = self._create_$row(data);
	    data.$row.replaceAll($existing);
	    self._update_height();
	},
	
	_delete_row: function (index) {
	    var self = this, $self = this.element;
	    var $row = self.options.call_monitor_data[index].$row;
	    if ($row) {
		$row.remove();
		self._update_height();
	    }
	},
	    
	_update_height: function () {
	    var self = this, $self = this.element, $container = self.options.$container;
	    $self.height($container.outerHeight());
	},

	_process_row_data: function (data) {
	    var self = this, $self = this.element;

	    if (data._processed) { return data; }

	    data._a_leg = { _leg: 'a' };
	    data._b_leg = { _leg: 'b' };
	    data._processed = true;
	    
	    for (var k in data) {
		if (!data.hasOwnProperty(k)) { continue; }
		var ab_key = k.match(/^([ab])_(.+)/);
		if (ab_key) {
		    data['_' + ab_key[1] + '_leg'][ab_key[2]] = data[k];
		}
	    }
	    
	    if (!data._b_leg.uuid) {
		data._other_leg = undefined;
		delete data._b_leg;
	    }
	    
	    if (data._a_leg.bbx_user_id == validUserID) {
		data._my_leg = data._a_leg;
		data._other_leg = data._b_leg;
	    } else {
		data._my_leg = data._b_leg;
		data._other_leg = data._a_leg;
	    }

	    var cid_ceid;

	    if (data._other_leg && data._other_leg.popup_url) {
		self._do_popup(data._other_leg.popup_url);
	    }

	    if (!data._my_leg) {
		// Bail out early if there's no _my_leg -- this only happens when a call is being torn down
		return data;
	    }

	    if (data._my_leg._leg === 'b' ) {
		cid_ceid = 'cid';
	    } else {
		cid_ceid = 'ceid';
	    }

	    data._my_leg._cid_in = {
		name:      data[cid_ceid+'_name'],
		number:    data[cid_ceid+'_number'],
		formatted: format_information(data[cid_ceid+'_number'], null)
	    }

	    
	    return data;
	},

	_do_popup: function(url) {
	    console.log('linking');
	},

	_match_existing_ringing: function (call) {
	    var self = this, $self = this.element;

	    if (!(call._my_leg.callstate in { RINGING:1, EARLY:1, DOWN:1 })) {
		return false;
	    }
	    
	    for (var i=0; i<self.options.call_monitor_data.length; i++) {
		var other_call = self.options.call_monitor_data[i];

		if (
		    call.row_id !== other_call.row_id && (
			(call.originator && call.originator === other_call.originator) ||
			(call.other_uuid && call.other_uuid === other_call.other_uuid) ||
			(call._my_leg.click_to_call_uuid && call._my_leg.click_to_call_uuid === other_call._my_leg.click_to_call_uuid)
		    )
		) {
		    return i;
		}
	    }
	    return false;

	    
	},

	_add_ringing_phone: function (call, existing_index) {
	    var self = this, $self = this.element;
	    var existing = self.options.call_monitor_data[existing_index];
	    
	    existing._ringing = existing._ringing || [];
	    existing._ringing.push(call);

	    self._update_row(existing_index, existing);
	},

	_create_$row: function (data) {
	    var self = this, $self = this.element;
	    var $row = $(ROW_HTML_STRUCTURE);
	    
	    $row = this._fill_row(data, $row);
	    return $row;
	},

	_fill_row: function (data, $row) {
	    var self = this, $self = this.element;
	    data._ticker = { $element: $row.find('.callMonitorTimer'), start: parseInt(data._my_leg.created_epoch, 10) / 1000 || (data.current_time * 1000), bind_ns: getUnique('ticker') };

	    $(window).bind('tick.' + data._ticker.bind_ns, function () {
		data._ticker.$element.text(hhmmss((get_server_milliepoch() - data._ticker.start) / 1000)); // helpers.js
	    });

	    // Copy (btn_array) and lookup-table (btn) the OPERATION_BUTTONS array
	    var btn = {}, btn_array = $.extend(true, [], OPERATION_BUTTONS), i = btn_array.length;
	    while (i--) {
		btn[btn_array[i].name] = btn_array[i];
	    }

	    var state_icon;

	    switch (data._my_leg.callstate) {
	    case 'RINGING':
	    case 'EARLY':
	    case 'DOWN':
		// Show KILL, opt. ANSWER
		state_icon = (data._my_leg.direction === 'inbound' ? 'ringout' : 'ringin');
		$self.addClass('ringing');
		
		// Multi-ring state--
		if (data._ringing) {
		    var _ringing_copy = $.extend([], data._ringing);
		    var answerable = false;

		    _ringing_copy.push(data);
		    _ringing_copy = _ringing_copy.sort(function (a,b) {
			return cmp(a._my_leg.phone_name, b._my_leg.phone_name);
		    });

		    var $cmm_list = $row.find('.callMonitorMulti');

		    for (var ringing_i=0; ringing_i < _ringing_copy.length; ringing_i++) {
			var ringing_data = _ringing_copy[ringing_i];

			if (trueish(ringing_data._my_leg.click_to_answer, { allowStringFalse: true })) {
			    answerable = true;
			} else {
			    continue;
			}

			var $ringing_link = $('<a href="javascript:false"></a>')
			    .append(document.createTextNode(unescape(ringing_data._my_leg.phone_name) || 'Unknown Phone'))
			    .data('uuid', ringing_data._my_leg.uuid);

			$ringing_link.bind('click', function (e) {
			    e.preventDefault();
			    self._answer_uuid($(this).data('uuid'));
			});

			$cmm_list.append($ringing_link);
		    }

		    if (answerable) {
			$row.addClass('ringingMulti');
			btn.killmulti.show = true;
		    } else {
			btn.kill.show = true;
		    }

		    btn.answer.show = false;
		} else {
		    // Single-ring state--
		    btn.kill.show = true;
		    btn.answer.show = trueish(data._my_leg.click_to_answer, { allowStringFalse: true }) && data._my_leg.direction === 'outbound';
		}
		
		break;
	    case 'HELD':
		// Show KILL, UNHOLD, opt. TRANSFER
		state_icon = 'onhold';
		$self.addClass('held');
		btn.kill.show = true;
		btn.unhold.show = true;
		
		if (data._other_leg) {
		    btn.transfer.show = true;
		}
		break;
	    case 'ACTIVE':
	    case 'UNHOLD':
		// Show KILL, opt HOLD, opt TRANSFER
		if (data._my_leg.click_to_call_uuid) {
		    state_icon = (data._my_leg.direction === 'inbound' ? 'incoming' : 'outgoing');
		} else {
		    state_icon = (data._my_leg.direction === 'inbound' ? 'outgoing' : 'incoming');
		}

		$self.addClass('answered');
		btn.kill.show = true;

		if (data._other_leg) {
		    btn.transfer.show = true;
		    btn.hold.show = trueish(data._my_leg.click_to_hold, { allowStringFalse: true });
		} else {
		    btn.hold.show = trueish(data._my_leg.click_to_hold, { allowStringFalse: true });
		}
		break;
	    default:
		console_log('Unknown call state: ', data._my_leg.callstate, ' on ', data);
		break;
	    }
	    
	    if (data._my_leg._cid_in.name || data._my_leg._cid_in.formatted || data._my_leg._cid_in.number) {
		$row.find('.name').text(titleCase(unescape(data._my_leg._cid_in.name || '')));

		if (!data._my_leg._cid_in.name || (data._my_leg._cid_in.name && data._my_leg._cid_in.name == data._my_leg._cid_in.number)) {
		    $row.find('.name').hide();
		}

		$row.find('.number').text(data._my_leg._cid_in.formatted || format_information(data._my_leg._cid_in.number) || '');
	    } else {
		$row.find('.name').html('<span class="noValue">No Information</div>');
	    }

	    if (state_icon) {
		$('<img width="15" height="15" />')
		    .attr({
			src: STATE_ICONS[state_icon].src,
			title: STATE_ICONS[state_icon].title,
			'class':  STATE_ICONS[state_icon].className
		    })
		    .appendTo($row.children('.state'));
	    }

	    for (var i=0; i<btn_array.length; i++) {
		if (btn_array[i].show) {
		    // Factory function here, because we need to use btn_array[i] in the callback
		    (function (btn) {
			var $btn = $('<a href="javascript:false" class="op"><img width="15" height="15" /></a>');
			$btn
			    .attr('title', btn.title)
			    .addClass(btn.className)
			    .children('img')
			    .attr('src', btn.src);
			$btn
			    .bind('click', function (e) { e.preventDefault(); self[btn.fn](data); })
			
			$row
			    .find('.callMonitorOps')
			    .append($btn);
		    })(btn_array[i]);
		}
	    }

	    return $row;
	},

	// Operations
	
	_kill: function (call) {
	    var self = this, $self = this.element, cause;

	    switch (call._my_leg.callstate) {
	    case 'RINGING':
		cause = 'CALL_REJECTED';
		break;
	    default:
		cause = 'NORMAL_CLEARING';
		break;
	    }

	    $.ajax({
		url: '/gui/freeswitch/uuid/uuid_kill',
		data: { uuid: call._my_leg.uuid, cause: cause, template: 'json' },
		error: function() {
		    return;
		},
		success: function () {
		}
	    });
	    
	},

	_kill_multi: function (call) {
	    var self = this, $self = this.element;
	    
	    var all_calls = [call].concat(call._ringing || []);

	    for (var ac_idx=0; ac_idx<all_calls.length; ac_idx++) {
		$.ajax({
		    url: '/gui/freeswitch/uuid/uuid_kill',
		    data: { uuid: all_calls[ac_idx]._my_leg.uuid, cause: 'CALL_REJECTED', template: 'json' },
		    error: function() {
			return;
		    },
		    success: function () {
		    }
		});
	    }
	    
	},

	_answer: function (call) {
	    var self = this, $self = this.element;
	    self._answer_uuid(call._my_leg.uuid);
	},

	_answer_uuid: function (uuid) {
	    $.post('/gui/freeswitch/uuid/uuid_phone_talk', { uuid: uuid, template: 'json' });
	},

	_hold: function (call) {
	    var self = this, $self = this.element;
	    $.post('/gui/freeswitch/uuid/uuid_phone_hold', { uuid: call._my_leg.uuid, template: 'json' });
	},

	_unhold: function (call) {
	    var self = this, $self = this.element;
	    self._answer(call);
	},

	_show_transfer_popup: function (call) {
	    var self = this, $self = this.element;

	    $.getTemplate('/ajax-html/callMonitorTransferPopup.html', function(html) {
		var popup = $(html).appendTo('body');
		$('.destination',popup)
		    .focus()
		    .extensionPicker({ directory: true });

		$('.callerUUID',popup).val(call._my_leg.uuid);
		$('.callerName',popup).text(unescape(call._my_leg._cid_in.name));
		$('.transferForm',popup)
		    .bind('submit', PREVENT_DEFAULT)
		    .one('submit', function (e) {
			$.post('/gui/freeswitch/uuid/uuid_transfer', { template: 'json', uuid: call._my_leg.uuid, extension: $('.destination', popup).val(), bleg: 1 });
			popup.remove();
		    });
		
		$('.cancel', popup).bind('click', function (e) {
		    e.preventDefault();
		    popup.remove();
		});

		for (var c_idx=0; c_idx<self.options.call_monitor_data.length; c_idx++) {
		    var existing_call = self.options.call_monitor_data[c_idx];
		    if (existing_call === call) { continue; }

		    switch (existing_call._my_leg.callstate) {
		    case 'RINGING':
		    case 'EARLY':
		    case 'DOWN':
			break;
		    default:
			has_bridgable = true;
			var $br_call = $('<li><a class="uuidUuidTransfer" href="javascript:false">' + (existing_call._my_leg._cid_in.name || existing_call._my_leg._cid_in.number) + '</a></li>');
			$br_call.find('a').data('transfer_uuid', existing_call._other_leg ? existing_call._other_leg.uuid : existing_call._my_leg.uuid);
			$('.uuidUuidBridgeList',popup).append($br_call);
		    };
		}

		var $xfer_links = $('.uuidUuidBridgeList a.uuidUuidTransfer',popup);
		if ($xfer_links[0]) {
		    $('.uuidBridgeListNoOtherCalls', popup).remove();
		    $xfer_links
			.bind('click',function (e) {
			    e.preventDefault();
			    
			    var dest_uuid = $(this).data('transfer_uuid');
			    var source = call._other_leg.uuid || call._my_leg.uuid;
			    
			    $.post('/gui/freeswitch/uuid/uuid_bridge', {
				template: 'json',
				destination: $(this).data('transfer_uuid'),
				source: source
			    });
			    
			    popup.empty().remove();
			});
		}
	    });
	},

	// Destructor

	destroy: function () {
	    var self = this, $self = this.element, cb_idx;

	    if (self.options.destroy_cb && self.options.destroy_cb.length) {
		for (cb_idx = 0; cb_idx < self.options.destroy_cb.length; cb_idx++) {
		    self.options.destroy_cb[cb_idx].call(self);
		}
	    }

	    $self.empty();
	    $self.removeClass('ringing ringingMulti held answered');

	    $.Widget.prototype.destroy.apply(self, arguments);
	}
    }));

})(jQuery);