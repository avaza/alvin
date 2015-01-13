var Ape;
var user_extension;
var user_cuda_pin;
var validUsername;
var validUserID;
var loginData;
var my_cudatel_data = [];

function get_userdata(){
    $.getJSON('/login/get_userdata', function(data){
        user_extension = data.user_extension;
        user_cuda_pin = data.user_cuda_pin;
        login();
    });
}

function login(){
    $.post( '/gui/login', { __auth_user: user_extension, __auth_pass: user_cuda_pin }, function(data){ 
        validUsername = data.data.bbx_user_username;
        validUserID = data.data.bbx_user_id;
        loginData = data.data;
        check_extension();
     }, "json");
}

function check_extension(){
    $.getJSON('/gui/user/has_extension', {}, function (data){
        if (data.data.has_extension) {
            Ape = new ApeConnection(function(){
                initCallControl();
            });
        }
    });
}

function blowout_data(){
    setInterval(function(){
        call_data = Ape.context_bindings.livetable_live_calls__[0].userdata.options.call_monitor_data;
        if(call_data.length > 0){
            $.each(call_data, function(index, data){
                if(data._other_leg ){
                    if(data._other_leg.popup_url.length > 0){
                        my_cudatel_data[index] = new Object();
                        my_cudatel_data[index].linkable = true; 
                        my_cudatel_data[index].uuid = data._other_leg.popup_url;
                    } else{
                        my_cudatel_data[index] = new Object();
                        my_cudatel_data[index].linkable = false;
                        my_cudatel_data[index].uuid = data._other_leg.popup_url;
                    }
                } else{
                    console.log('not linkable yet')
                }
            }); 
        } else{
            my_cudatel_data = [];
        }
        console.log(my_cudatel_data);
        console.log(call_data);
    },1000);
}

function initCallControl() {
    Ape.subscribe(['call_event', 'presence_event', 'call_update', 'channel_callstate', 'queue_status', 'conference_status', 'channel_hangup', 'meteor_alive', 'user_'+validUsername]);
    $.getREST('/gui/phone/myuserregs', function(regs) {
        $('#onthephone').callMonitorBar({ registrations: regs });
    });
}

(function ($) {
  var ALLOWABLE_DRIFT = 1000, SERVER_OFFSET = 0;
  var update_server_offset = function(server_milliepoch){
    if (!server_milliepoch || isNaN(Number(server_milliepoch))) {
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
  var get_server_milliepoch = function(){
    return new Date().getTime() + SERVER_OFFSET;
    };

    $.widget('cui2.callMonitorBar', $.extend({}, CUI.dataTableClass, {
  options: {
      call_monitor_data: [],
      destroy_cb: [],
      to: [],
      interval: [],
      user_info: {}
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
      self._dataTableInit();
      self._liveDataTableSubscribe('liveTable_live_calls__', undefined, self.options.user_info);
  },
  
  _afterAddRow: function (data, index) {
      var self = this, $self = this.element;
      update_server_offset(data.current_time ? (parseInt(data.current_time, 10) * 1000) : undefined);
      data = self._process_row_data(data);
      data._index = index;
      self.options.call_monitor_data.splice(index,0,data);
  },
  
  _afterSetOriginalRowData: function () {
      this._afterRowUpdate.apply(this, arguments);
  },

  _afterRowUpdate: function (index, data) {
      var self = this, $self = this.element;
      var $row = self.options.call_monitor_data[index].$row, ringing = self.options.call_monitor_data[index]._ringing;
      data = self._process_row_data(data);
      data._index = index;
      data._ringing = ringing;
      self.options.call_monitor_data[index] = data;
  },
      
  _afterDeleteRow: function (index) {
      var self = this, $self = this.element;
      var row = self.options.call_monitor_data[index];
      if (row) {
      if (row._ticker) {
          $(window).unbind('halfTick.' + row._ticker.bind_ns);
      }
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

  _process_row_data: function (data) {
      var self = this, $self = this.element;
      if(data._processed){ 
        return data; 
      }
      data._a_leg = { _leg: 'a' };
      data._b_leg = { _leg: 'b' };
      data._processed = true;
      
      for (var k in data) {
      if (!data.hasOwnProperty(k)) { 
        continue; 
      }
      var ab_key = k.match(/^([ab])_(.+)/);
      if (ab_key) {
          data['_' + ab_key[1] + '_leg'][ab_key[2]] = data[k];
      }
    }
      if(!data._b_leg.uuid) {
      data._other_leg = undefined;
      delete data._b_leg;
      }
      if(data._a_leg.bbx_user_id == validUserID) {
      data._my_leg = data._a_leg;
      data._other_leg = data._b_leg;
      } else{
      data._my_leg = data._b_leg;
      data._other_leg = data._a_leg;
      }
    var cid_ceid;
      if(!data._my_leg) {
      return data;
      }
      if (data._my_leg._leg === 'b' ) {
      cid_ceid = 'cid';
      } else{
      cid_ceid = 'ceid';
      }
      data._my_leg._cid_in = {
      name:      data[cid_ceid+'_name'],
      number:    data[cid_ceid+'_number'],
      formatted: format_information(data[cid_ceid+'_number'], null)
      };
    return data;
  }
    }));

})(jQuery);
