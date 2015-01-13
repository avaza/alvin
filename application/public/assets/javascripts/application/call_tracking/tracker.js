//MORE FORMS
var validUsername;
var validUserID;
var jQNC = jQuery.noConflict();
var state;
//CONN TIMER
var flashing = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var timers = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var time = [];
var timer_epochs = [];
//END CONN TIMER

//LIVE HIDER VARIABLES
var hidden = false;
var forms = [1,2,3,4,5,6,7,8,9,10,11,12];
var spshow = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var coshow = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var special_datas = [0,0,0,0,0,0,0,0,0,0,0,0,0];
//END LIVE HIDER VARIABLES
var my_calls = false;
//call linking variables
var currnt_uuids = [];
var linked_uuids = [];
var unlink_uuids = [];
var visual_uuids = [];
var linked_calls = [];
var unlink_calls = [];
var visual_calls = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var relink_bools = [0,0,0,0,0,0,0,0,0,0,0,0,0];
var active_uuid = false;
var active_epoch = false;
//END call linking variables

jQNC(document).ready(function(){
    jQNC.uniform.update();
    set_websocket();
    set_live_tracking();
    set_tabs();
    set_autocomplete();
    set_calls_frame();
    set_hider();
    for (var i = 1; i < 13; i++) {
        set_form_functions(i);
    }
    jQNC("#tracking-table").tableDnD();
    jQNC('.fancybox').fancybox();
    
});
//DOCUMENT READY FUNCTIONS
function set_websocket(){
    $.ajax({
    url: "/gui/login/status",
    type: "POST",
    dataType: 'json',
    success: function (data, status, xhr) {
        validUsername = data.data.bbx_user_username;
        validUserID = data.data.bbx_user_id;
        loginData = data.data;
        Ape = new ApeConnection(function () {
            Ape.subscribe(['global_messages', 'restore', 'provision', 'system_restart', 'ldap_import', 'meteor_alive', 'meteor_user_status', 'meteor_phone_status', 'meteor_queue_status', 'conference_status', 'queue_status', 'user_status', 'call_event', 'presence_event', 'call_update', 'channel_callstate', 'channel_hangup', 'user_'+validUsername]);
            $.getREST('/gui/phone/myuserregs', function(regs) {
                $('#onthephone').callMonitorBar({ registrations: regs });
                /*$("#stubAgentMonitor").empty();
                $("#stubAgentMonitor").agentBoard({ autosize: false });
                $("#stubAgentManager").empty();
                $("#stubAgentManager").queueAgentManager();
                $("#statsPageActiveChannels").empty();
                $("#stubAgentManager").recentActiveCalls();
                //$("#stubAgentMonitor").empty().agentBoard({ autosize: false });
                //$("#stubAgentManager").empty().queueAgentManager();
                //$("#statsPageActiveChannels").empty().recentActiveCalls();*/
            });
        });
    },
    cache: false
    });
}

function set_live_tracking(){
    clearInterval(state, 0);
    state = setInterval(function(){
        if(Ape && Ape.context_bindings){
            my_calls = Ape.context_bindings.livetable_live_calls__[0].userdata.options;
        } else{
            my_calls = false;
        }
        if(my_calls !== false && my_calls.call_monitor_data.length > 0){
            set_calls(my_calls.call_monitor_data);
        } else{
            clear_live_calls();
        }
        set_active_uuid();
        green_or_not();
    },1000);
}

function set_tabs(){
    if(manager === true){
        jQNC("#tab-panel-2").tabs({ selected: 5 });
    } else if(interpreter == 1){
        jQNC("#tab-panel-2").tabs({ selected: 1 });
    } else{
        jQNC("#tab-panel-2").tabs({ selected: 3 });
    }
}

function set_autocomplete(){
    jQNC(this).autocomplete('widget').css('position', 'relative');
    jQNC(this).autocomplete('widget').css('z-index', 100);
}

function set_calls_frame(){
    jQNC('#callClick').click(function(){
        var c = document.getElementById('callsframe');
        c.src = c.src;
    });
}

function set_hider(){
    jQNC('#hider').click(function(){
        if (hidden !== false){
            jQNC("#top").animate({
                marginLeft: "-=261px",
                marginRight: "0px",
                'background-position-x': '-261px'
            }, 500);
            hidden = false;
        } else{
            jQNC("#top").animate({
                marginLeft: "+=261px",
                marginRight: " 0px ",
                'background-position-x': '0px'
            }, 500);
            hidden = true;
        }
    });
}

function set_form_functions(number){
        jQNC("#rep_name" +number).autocomplete({
            source: function(request, response) {
                jQNC.ajax({ 
                    url: '/tracker/suggestions',
                    data: { term: jQNC("#rep_name" +number).val()},
                    dataType: "json",
                    type: "POST",
                    success: function(data){
                        response(data);
                    }
                });
            },
            minLength: 2
        })
            .blur(function(){
                save_rep_name(number);
        });

        jQNC('#specialf' +number).live("change", function(event){
            type = event.target.type;
            save_specialf(number, type);
        });

        //LOOKUP ACCESS CODE
        jQNC('#access_code' +number).blur(function(){
            jQNC('#ac_check' +number).html('');
            ver_acc(number);
        });

        jQNC('#ignore' +number).click(function(){
            ignore(number);
        });

        jQNC('#co_num_add' +number).click(function(){
            add_callout_by_uuid(jQNC('#co_num' +number).val(), jQNC('#uuid' +number).val());
        });

        //CLICK TO HOLD / UNHOLD
        jQNC('#linkup' +number).click(function(){
            if(jQNC('#uuid' +number).val() !== ''){
                if(jQNC('#uuid' +number).val() == active_uuid){
                    hold_this_call(jQNC('#my_uuid' +number).val());
                } else{
                    if(jQNC.inArray(jQNC('#uuid' +number).val(), currnt_uuids) != -1){
                        unhold_this_call(jQNC('#my_uuid' +number).val());
                    } else{
                        return false;
                    }

                }
            }
            
        });
    
        //LOOKUP CALL DATA
        jQNC('#language' +number).change(function(){
            get_interpreters(number);
        });

        jQNC('#callout' +number).click(function(){
            toggle_co_by_id(number);
        });

        jQNC('#ctform' +number).keydown(function(e) {
            if(e.keyCode === 13){
                check_it_and_send_it(number, 0);
            }
        });
        // SUBMIT FORM DATA
        jQNC('#submit' +number).click(function(){
            check_it_and_send_it(number, 0);
        });

        jQNC('#interpreter' +number).change(function(){
            interpreter = jQNC('#interpreter' +number).val();
            /*if(interpreter !== 0){
                get_interpreters_phone(interpreter);
            }*/
        });

        jQNC('#drop' +number).click(function() {
            if(jQNC.inArray(jQNC('#uuid'+number).val(), currnt_uuids) == -1){
                check_it_and_send_it(number, 1);
            } else{
                dropper = confirm("This Call Was Dropped?");
                if (dropper){
                    check_it_and_send_it(number, 1);
                } else{
                   jQNC('#drop'+number).prop('checked', false);
                   jQNC.uniform.update(jQNC('#drop'+number));
                   return false;
                }                
            }
        });
    }
//END DOCUMENT READY FUNCTIONS

//HIDE TOGGLES
function get_interpreters_phone(interpreter){
    jQNC.getJSON('/get_interpreters_phone/' + interpreter + '/', function(data){
        if(data !== false){
            return data.phone;
        } else{
            return false;
        }
    });

}

function toggle_co_by_id(num){
    if(coshow[num] === 0){
        jQNC('#co_num' +num).show();
        jQNC('.cnum').show();
        coshow[num] = 1;
        if(relink_bools[num] == 1){
            jQNC('#co_num_add'+num).show();
        } else{
            jQNC('#co_num_add'+num).hide();
        }
    } else{
        co_show = 0;
        jQNC('#co_num' +num).hide();
        coshow[num] = 0;        
        jQNC.each(coshow, function(index, object){
            co_show = co_show + object;
        });
        if(co_show === 0){
            jQNC('.cnum').hide();
        } else{
            jQNC('.cnum').show();
        }
    }
}

function toggle_sp_by_id(on_off, num, type, msg){
    jQNC('#rep_name'+num).attr("placeholder", "");
    jQNC('#rep_name'+num).css('border-color', '');
    jQNC('#sp_td'+num).empty();
    toggle_special_field(num, on_off);
    markup_special_field(type, num, msg);
    get_interpreters(num); 
}

function toggle_special_field(num, on_off){
    if(on_off === 0){
        jQNC('#specialf'+num).hide();
        spshow[num] = 0;
    } else{
        jQNC('#specialf'+num).show();
        spshow[num] = 1;
    }
    sp_show = 0;
    jQNC.each(spshow,function(index, object){
        sp_show = sp_show + object;
    });
    if(sp_show === 0){
        jQNC('.spn').hide();
    } else{
        jQNC('.spn').show();
    }
}

function markup_special_field(type, num, msg){
    if(type == 1){
        jQNC('#sp_td'+num).html('<input type="text" id="specialf'+num+'" name="specialf'+num+'" placeholder="'+msg+'">');
        jQNC.uniform.update(jQNC('#specialf'+num));
        toggle_special_field(num, 1);
    } else if(type == 3){
        jQNC('#rep_name'+num).attr("placeholder", "WORKER NUMBER");
        jQNC('#rep_name'+num).css("border-color", "red");
        jQNC('#sp_td'+num).html('<select id="specialf'+num+'" name="specialf'+num+'"></select>');
        jQNC('#specialf' +num).empty();
        get_special_dropdown(num, msg);
        jQNC('#uniform-specialf'+num+' span').text('Select County ...');
    } else if(type == 4){
        jQNC('#rep_name'+num).attr("placeholder", msg);
        jQNC('#rep_name'+num).css("border-color", "red");
        toggle_special_field(num, 0);
    } else if(type == 2){        
        jQNC('#sp_td'+num).html('<b style="color: red; font-weight: bold;">'+msg+'</b>');
    } else if(type == 5){        
        jQNC('#sp_td'+num).html('<b style="color: red; font-weight: bold;">'+msg+'</b>');
        jQNC('#rep_name'+num).attr("placeholder", 'First and Last Name');
        jQNC('#rep_name'+num).css("border-color", "red");
    } else if(type == 6){        
        jQNC('#sp_td'+num).html('<input type="text" id="specialf'+num+'" name="specialf'+num+'" placeholder="'+msg+'">');
        jQNC.uniform.update(jQNC('#specialf'+num));
        toggle_special_field(num, 1);
        jQNC('#rep_name'+num).attr("placeholder", 'TITLE, First and Last Name');
        jQNC('#rep_name'+num).css("border-color", "red");
    } else if(type == 7){        
        jQNC('#sp_td'+num).html('<b style="color: red; font-weight: bold;">'+msg+'</b>');
        jQNC('#rep_name'+num).attr("placeholder", 'LA County Employee ID Number');
        jQNC('#rep_name'+num).css("border-color", "red");
    } else{
        jQNC('#sp_td'+num).empty();
        toggle_special_field(num, 0);
    }
}
//END HIDE TOGGLES

//FORM FUNCTIONS
function ver_acc(num){
    access_cde = jQNC('#access_code' +num).val();
    this_uuid =  (jQNC('#uuid' +num).val() !== "") ? jQNC('#uuid' +num).val() : 0;
    if(access_cde === ""){
        jQNC('div#ac_info' +num).empty();
    } else{
        jQNC.getJSON("../tracker/get_access_code/" + access_cde + "/" + this_uuid, function(data){
            if(data !== false){
                jQNC(data).each(function(index, object){
                    jQNC('#ac_check' +num).html("<div id='valid'><a id='ac_info" +num+ "' rel='tooltip-top' href='javascript:void(0);' title='b'>Valid.</a></div>");
                    jQNC('#ac_info' +num).prop('title', 'Agency : ' + object.agency + ' Division : ' + object.division);
                    jQNC('#division').text(object.division + ' with ' + object.agency);
                    jQNC('#division').css('color', 'red');
                    jQNC('#ac_info' +num).tipsy(); 
                    if(object.otp_sp_in == 1 && relink_bools[num] === 0){
                        toggle_sp_by_id(1, num, parseInt(object.sp_type, 10), object.otp_instructions);
                    } else{
                        toggle_sp_by_id(0, num, 0, '');
                    }
                });
            } else{
                jQNC('#ac_check' +num).html("<div id='invalid'><a id='ac_info" +num+ "' rel='tooltip-top' href='javascript:void(0);' title='Invalid Access Code'>Invalid.</a></div>");
                jQNC('#ac_info' +num).tipsy();
                toggle_sp_by_id(0, num, 0, '');
            }
        });
    }
}

function get_special_dropdown(num, msg){
    var acccode = jQNC('#access_code' +num).val();
    if (acccode !== ""){
        jQNC('#specialf'+num).show();
        jQNC('#specialf'+num).uniform();
        var dropdown_url = "get_dropdown/" +acccode;
        jQNC.ajax({
            type: "POST",
            url: dropdown_url,
            dataType: "json",
            success: function(options){
                jQNC('#specialf'+num).append('<option value="0">'+msg+'</option>');
                jQNC.each(options,function(id,option){
                    var opt = jQNC('<option />');
                    opt.val(option);
                    opt.text(option);
                    jQNC('#specialf' +num).append(opt);
                });
                if(special_datas[num] !== 0){
                    jQNC("#specialf"+num).val(special_datas[num]);
                }
            }          
        });
        jQNC.uniform.update(jQNC('#specialf'+num)); 
    } else {
        jQNC('#specialf' +num).empty();
        jQNC.uniform.update(jQNC('#specialf'+num)); 
    }
}

function get_interpreters(num){
    if(interpreter != 1){
        language_code = jQNC('#language' +num).val();
        if(jQNC('#uuid' +num).val() !== ""){
            uuid = jQNC('#uuid' +num).val();
        } else{
            uuid = false;
        }    
        if (language_code !== ""){
            int_list_url = "get_interpreters/" + language_code +"/" +uuid +"/";
            jQNC.ajax({
                type: "POST",
                url: int_list_url,
                dataType: "json",
                success: function(data){
                    jQNC('#interpreter' +num).empty();
                    jQNC('#interpreter'+num).append('<option value="0">Select Interpreter...</option>');
                    jQNC.each(data,function(index, object){
                        var opt = jQNC('<option />');
                        opt.val(object.iid);
                        opt.text(object.iid+' | '+object.name);
                        jQNC('#interpreter' +num).append(opt); 
                    });
                   jQNC.uniform.update();
                }
            });
        } else {
            jQNC('#interpreter' +num).empty();
            jQNC.uniform.update();
        }
    } else{
        set_my_info();
    }
}

function get_interpreter_data(num){
    jQNC('#overlay').hide();
    var id = jQNC('select#interpreter' +num).val();
    if (id != "0"){
        var int_data_url = "get_interpreter_data/" + id;
        jQNC.ajax({
            type: "POST",
            url: int_data_url,
            dataType: "json",
            success: function(interpreters_data){
                jQNC(interpreters_data).each(function(iname,name){
                    name = this.iname;
                    iid = this.iid;
                    ph1_1 = this.ph1_1;
                    ph1_2 = this.ph1_2;
                    language_code = this.language_code;
                    notes = this.notes;
                    jQNC('#intname' +num).val(name);
                    jQNC('#intid' +num).val(iid);
                    if(ph1_2 == 'null'){ph1_2='N1';}
                    if(notes == 'null'){notes='N1';}
                    jQNC('div#in_result' +num).html('Name : ' + name + '<br>' + 'Language : ' + language_code + '<br>' + 'Primary Ph1 : ' + ph1_1 + '<br>' + 'Secondary Ph1 : ' + ph1_2 + '<br>' + 'Notes : ' + notes + '<br>'); 
                });
            }
        });
    } else {
        jQNC('div#in_result' +num).empty();
    }
}

function ignore(num){
    uuid = jQNC('#uuid' +num).val();
    qurl = '../tracker/ignore_uuid/'+uuid;
    jQNC.get(qurl, function(data){
        if(data === true){
            reset_form(num);
        }
    });
}
//END FORM FUNCTIONS

//LIVE CALL TRACKING (WEBSOCKET)
function set_active_uuid(){
    active_uuid = false;
    active_count = 0;
    jQNC(my_calls.call_monitor_data).each(function(index, object){
        if(object.call_state == 'ACTIVE'){
            active_count = 1;
            active_uuid = object._other_leg.uuid;
        }
    });
    if(active_count === 0){
        active_uuid = false;
    }
}

function count_and_set_calls(data){
    linked_count = 0;
    unlink_count = 0;
    currnt_count = 0;
    jQNC(data).each(function(index, object){
        if(object._other_leg){
            if(object._other_leg.popup_url.length > 0){
                linked_count = linked_count + 1;
                currnt_count = currnt_count + 1;
            } else{
                unlink_count = unlink_count + 1;
                currnt_count = currnt_count + 1;
            }
        }
    });
    if(linked_calls.length != linked_count || unlink_calls.length != unlink_count || currnt_uuids.length != currnt_count){
        set_calls(data);
    }
}

function set_calls(data){
    clear_live_calls();
    jQNC(data).each(function(index, object){
        if(object._other_leg){
            if(object._other_leg.popup_url.length > 0){
                set_linked_call(object);
            } else{
                set_unlink_call(object);
            }
        }
    });
    link_calls();
}

function clear_live_calls(){
    currnt_uuids = [];
    linked_uuids = [];
    unlink_uuids = [];
    linked_calls = [];
    unlink_calls = [];
}

function set_linked_call(data){
    if(data){
        call = {};
        call.cid_name = data._my_leg._cid_in.name;
        call.cid_numb = data._my_leg._cid_in.number;
        call.bbx_id = data._my_leg.bbx_phone_id;
        call.state = data._my_leg.callstate;
        call.my_uuid = data._my_leg.uuid;
        call.start_epoch = data._other_leg.created_epoch;
        if(data._other_leg.popup_url.indexOf("http://") > -1){
            data._other_leg.popup_url = data._other_leg.popup_url.replace("http://", "");
        }
        call.popup = data._other_leg.popup_url;
        call.uuid = data._other_leg.uuid;
        if(jQNC.inArray(call.uuid, linked_uuids) == -1){
            linked_calls.push(call);
            linked_uuids.push(call.uuid);
            currnt_uuids.push(call.uuid);
        }
    }
}

function set_unlink_call(data){
    if(data){
        call = {};
        call.cid_name = data._my_leg._cid_in.name;
        call.cid_numb = data._my_leg._cid_in.number;
        call.my_uuid = data._my_leg.uuid;
        call.uuid = data._other_leg.uuid;
        if(jQNC.inArray(call.uuid, unlink_uuids) == -1){
            unlink_calls.push(call);
            unlink_uuids.push(call.uuid);
            currnt_uuids.push(call.uuid);
        }
    }
}

function link_calls(){
    jQNC(linked_calls).each(function(index, object){
        if(jQNC.inArray(object.popup, visual_uuids) == -1){
            set_to_linkable(object.popup, object);
        }
    });
}
//HERESON
function set_to_linkable(uuid, call){
    var rori = interpreter == 1 ? 1:2;
    jQNC.ajax({
        type: "POST",
        url: "link_uuid/" + uuid + '/' + rori + '/',
        dataType: "json",
        success: function(data){
            if(data !== false){
                check_if_linked(uuid, call);
            }
        }
    });
}

function check_if_linked(uuid, call){
    jQNC.ajax({
        type: "POST",
        url: "check_uuid/" + uuid,
        dataType: "json",
        success: function(data){
            if(data && data !== false ){
                if(parseInt(intid, 10) == parseInt(data.intid, 10) && data.relink == 1){
                    relink_this_call(uuid, call, data);
                } else{
                    if(data.relink === 0){              
                        link_this_call(uuid, call, data);
                    }
                }
            }
        }
    });
}

function link_this_call(uuid, call, data){
    for(var i = 1; i < 13; i++){
        if(jQNC.inArray(uuid, visual_uuids) == -1 && jQNC('#uuid' +i).val() === '' && call){
            special_datas[i] = 0;
            if(interpreter == 1){
                visual_uuids[1] = uuid;
                visual_calls[i] = call;
            } else{
                visual_uuids[i] = uuid;
                visual_calls[i] = call;
            }
            jQNC('#form' +i).show();
            jQNC('#uuid' +i).val(uuid);
            if(data.access_code !== '0'){
                jQNC('#access_code'+i).val(data.access_code);
            }
            ver_acc(i);
            if(data.rep_name !== ""){
                jQNC('#rep_name'+i).val(data.rep_name);
            }
            if(data.langauge !== ""){
                jQNC('#language' +i).val(data.language);
                jQNC.uniform.update(jQNC('#language' +i));
            } else{
                jQNC('#language' +i).val('SPA');
                jQNC.uniform.update(jQNC('#language' +i));
            }
            if(data.specialf && data.specialf.length > 0){
                special_datas[i] = data.specialf;
            }
            jQNC('#my_uuid' +i).val(get_my_leg(uuid));
            jQNC('#access_code' +i).show();
            start_timer(i, call.start_epoch);
        }
    }
}

function relink_this_call(uuid, call, data){
    if(data && data !== false){
        link_this_call(uuid, call, data);
        num = jQNC.inArray(uuid, visual_uuids);
        jQNC('#access_code' +num).val(data.access_code);
        jQNC('#access_code' +num).prop('disabled', true);
        jQNC('#rep_name' +num).val(data.rep_name);
        jQNC('#rep_name' +num).prop('disabled', true);
        jQNC('#language' +num).val(data.language);
        jQNC('#language' +num).prop('disabled', true);
        jQNC('#interpreter' +num).val(data.intid);
        jQNC('#interpreter' +num).prop('disabled', true);
        jQNC('#drop' +num).prop('disabled', true);
        relink_bools[num] = 1;
        ver_acc(num);
        jQNC('#submit' +num).prop('disabled', true);
        jQNC('#script').show();
        jQNC('#script-ready').show();
    }
}

function green_or_not(){
    for(var i = 1; i < 13; i++){
        uuid = jQNC('#uuid' +i).val();
        if(uuid !== ''){
            if(jQNC('#access_code' +i).is(':hidden')){
                jQNC('#access_code' +i).show();
            }
            if(uuid == active_uuid){
                jQNC('#linkup' +i).html('<img src="../assets/img/icons/misc/circle-green.png"/>');
                check_fields(i);
            } else{
                if(jQNC.inArray(uuid, currnt_uuids) != -1){
                    jQNC('#linkup' +i).html('<img src="../assets/img/icons/misc/circle-yellow.png"/>');
                    jQNC('#submit' +i).removeClass('tracker-submit-active');
                    jQNC('#submit' +i).addClass('tracker-submit');
                } else{
                    jQNC('#linkup' +i).html('<img src="../assets/img/icons/misc/circle-red.png"/>');
                    jQNC('#submit' +i).removeClass('tracker-submit-active');
                    jQNC('#submit' +i).addClass('tracker-submit');
                    hangup_check(i);
                }
            }
        } else{
            jQNC('#linkup' +i).html('<img src="../assets/img/icons/misc/circle-grey.png"/>');
            jQNC('#submit' +i).removeClass('tracker-submit-active');
            jQNC('#submit' +i).addClass('tracker-submit');
        }
    }
}

function check_fields(num){
    if(relink_bools[num] == 1){
        jQNC('#submit' +num).removeClass('tracker-submit-active');
        jQNC('#submit' +num).addClass('tracker-submit');
    } else{
        jQNC('#submit' +num).removeClass('tracker-submit');
        jQNC('#submit' +num).addClass('tracker-submit-active');
    }    
}

function hangup_check(num){
    if(jQNC('#access_code' +num).val() === '' && jQNC('#rep_name' +num).val() === ''){
        jQNC('#ignore' +num).show();
        stop_timer(num);
    }
    if(relink_bools[num] == 1){
        reset_form(num);
    } else{
        stop_timer(num);
    }
}
//END LIVE CALL TRACKING (WEBSOCKET)

//VALIDATION FUNCTIONS
var checked;

function checked_or_unchecked(fieldname){
    var checkbox_check = jQNC(fieldname);
    if(checkbox_check.is(':checked') === true){
        return 1;
    } else{
        return 0;
    }
}

function validate_blank(fieldname, errormsg){
    var blank_check = jQNC(fieldname).val();
    if(blank_check === '' || blank_check === undefined){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_value(fieldname, errormsg, value){
    var value_check = jQNC(fieldname).val();
    if(value_check == value || value_check === undefined){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_lngth(fieldname, length, errormsg){
    var lngth_check = jQNC(fieldname).val(); 
    if(lngth_check.length != length){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_alpha(fieldname, errormsg){
    var alphaReg = /^[a-zA-Z ]+$/;
    var alpha_check = jQNC(fieldname).val();
    if(!alphaReg.test(alpha_check)){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_numer(fieldname, errormsg){
    var numericReg = /^(0|[1-9][0-9]*)$/;
    var numer_check = jQNC(fieldname).val();
    if(!numericReg.test(numer_check)){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_email(fieldname, errormsg){
    var emailReg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    var email_check = jQNC(fieldname).val();
    if(!emailReg.test(email_check)){
        jQNC.jGrowl(errormsg, { theme: 'error' });
        checked = false;
    } else{
        checked = true;
    }
    return checked;
}

function validate_ajaxd(url, fieldname, errormsg, num){
    var ajaxd_check = jQNC(fieldname).val();
    var check_url = url + ajaxd_check;
    jQNC.ajax({
        type: "POST",
        url: check_url,
        dataType: "json",
        success: function(data){
            if(data === true){
                jQNC.jGrowl(errormsg, { theme: 'error' });
            } else{
                attempt_transfer(num);
            }
        }
    });
}

function validate_form_submit(num){
    connected = true;
    held = true;
    if(jQNC.inArray(jQNC('#uuid'+num).val(), currnt_uuids) == -1){
        connected = confirm("This call is no longer on your phone are you sure it was CONNECTED?");
    }
    if(connected === false){
        return false;
    } else{
        if(jQNC.inArray(jQNC('#uuid'+num).val(), currnt_uuids) != -1 && jQNC('#uuid'+num).val() != active_uuid){
           held = confirm("This call is on HOLD are you sure it is the correct call?");
        }
        if(held === false){
            return false;
        } else{
            var form_invalid_count = 0;
            if(validate_blank('#access_code' +num, "You Must Enter an Access Code.") === false){form_invalid_count = form_invalid_count +1;}
            if(validate_numer('#access_code' +num, "Access Code Must Contain Only Numbers") === false){form_invalid_count = form_invalid_count +1;}
            //INVALID ACCESS CODE CHECK
            if(validate_blank("#rep_name" +num, "You Must Enter a Caller Name.") === false){form_invalid_count = form_invalid_count +1;}
            if(interpreter !== 1){
                if(validate_value("#language" +num, "You Must Select a Language.", 0) === false){form_invalid_count = form_invalid_count +1;}
                if(validate_value("#interpreter" +num, "You Must Select an Interpreter.", 0) === false){form_invalid_count = form_invalid_count +1;}
            }
            if(validate_blank("#uuid" +num, "NO CALL LINKED") === false){form_invalid_count = form_invalid_count +1;}
            if(jQNC("#co_num" +num).is(":visible")){
                if(validate_blank('#co_num' +num, "You Must Enter a Callout Number.") === false){form_invalid_count = form_invalid_count +1;}
                if(validate_numer('#co_num' +num, "Callout Number Must Contain Only Numbers") === false){form_invalid_count = form_invalid_count +1;}
                if(validate_lngth('#co_num' +num, 10, "Callout Number Must have exactly 10 Numbers") === false){form_invalid_count = form_invalid_count +1;}
            }
            if(jQNC("#specialf" +num).is(":visible")){
                if(validate_blank("#specialf" +num, "You Must Enter a Value in the Special Field.") === false){form_invalid_count = form_invalid_count +1;}
            }
            if(form_invalid_count !== 0){
                return false;
            } else{
                validate_ajaxd("check_uuid/", "#uuid" +num, 'CALL ALREADY LOGGED please pick up this call and click to reset call.', num);
            }
        }
    }
}

function validate_form_drop(num){
    form_invalid_count = 0;
    dropped = true;
    if(jQNC.inArray(jQNC('#uuid'+num).val(), currnt_uuids) != -1){
        dropped = confirm("This call is still on your phone are you sure it was DROPPED?");
    }
    if(dropped === true){
        if(validate_blank('#access_code' +num, "You Must Enter an Access Code.") === false){form_invalid_count = form_invalid_count +1;}
        if(validate_blank("#rep_name" +num, "You Must Enter a Caller Name.") === false){form_invalid_count = form_invalid_count +1;}
        if(validate_value("#language" +num, "You Must Select a Language.", 0) === false){form_invalid_count = form_invalid_count +1;}
        if(form_invalid_count !== 0){
            jQNC('#drop'+num).prop('checked', false);
            jQNC.uniform.update(jQNC('#drop'+num));
            return false;
        } else{
            submit_call(num);
        }
    } else{
        jQNC('#drop'+num).prop('checked', false);
        jQNC.uniform.update(jQNC('#drop'+num));
        return false;
    }
}

function create_datastring(num){
    callout = checked_or_unchecked('#callout' +num);
    drop = checked_or_unchecked('#drop' +num);
    if(jQNC("#specialf" +num).val() === undefined){
        specf = '';
    } else{
        specf = jQNC("#specialf" +num).val();
    }
    if(interpreter == 1){
        submit_intid = intid;
        submit_lang = my_language;
    } else{
        submit_intid = jQNC("#interpreter" +num).val();
        submit_lang = jQNC("#language" +num).val();
    }
    var dataString = 'access_code=' + jQNC("#access_code" +num).val() +
                     '&rep_name=' + jQNC("#rep_name" +num).val() +
                     '&specialf=' + specf +
                     '&language=' + submit_lang +
                     '&intid=' + submit_intid +
                     '&uuid=' + jQNC("#uuid" +num).val() +
                     '&co_num=' + jQNC("#co_num" +num).val() +
                     '&processed=1&callout=' + callout +
                     '&drop=' + drop;
    return dataString;
}

function check_it_and_send_it(num, dropped){
    if(dropped == 1){
        validate_form_drop(num);
    } else{
        validate_form_submit(num);
    }
}

function submit_call(num){
    data_string = create_datastring(num);
    jQNC.ajax({
        type: "POST",
        url: "post_call/",
        data: data_string,
        success: function(data) {
            if(data !== false){
                if(data.message){
                    jQNC.jGrowl(data.message, { theme: 'error' });
                    return false;
                } else{
                    jQNC.jGrowl("<strong>Call Data Confirmed.</strong>", { theme: 'success' });
                    if(interpreter == 1 || parseInt(intid, 10) == parseInt(jQNC('#interpreter'+num).val(), 10)){
                        relink_this_call(jQNC('#uuid'+num).val(), visual_calls[num], data);
                    } else{
                        reset_form(num);
                    }
                } 
            } else{
                jQNC.jGrowl('Call Submit Failure', { theme: 'error' });
                return false;
            }
        }
    });
}
//END VALIDATION

//TIMER FUNCTIONS
function start_timer(num, epoch){
    if(timers[num] === 0){
        timers[num] = 1;
        time[num]=setInterval(function(){
            timer(num, epoch);
        }, 1000);
    } else{
        return false;
    }
}

function flasher(num, seconds){
    if (parseInt(seconds, 10) % 2 === 0){
        jQNC('#connection' +num).css('background','#ECBABA');
    } else{
        jQNC('#connection' +num).css('background','#f2f2f2');
    }
}

function timer(num, epoch){
    cut = parseInt(epoch.substring(0,13),10);
    cur = new Date().getTime();
    dif = cur - cut;
    difference = new Date(dif);
    minutes = difference.getUTCMinutes();   
    if(parseInt(difference.getUTCSeconds(), 10) < 10){
        seconds = '0'+difference.getUTCSeconds();
    } else{
        seconds = difference.getUTCSeconds();
    }
    if(parseInt(difference.getUTCHours(), 10) < 10){
        hours = difference.getUTCHours();
        connection = visual_calls[num].cid_numb+' - '+visual_calls[num].cid_name +'</br><strong id="time'+ num +'"><b id="hours'+ num +'">'+ hours +'</b>:<b id="mins'+ num +'">'+ minutes +'</b>:<b id="secs' + num +'">'+ seconds +'</b></strong>';
    } else{
        connection = visual_calls[num].cid_numb+' - '+visual_calls[num].cid_name +'</br><strong id="time'+ num +'"><b id="mins'+ num +'">'+ minutes +'</b>:<b id="secs' + num +'">'+ seconds +'</b></strong>';
    }
    jQNC('#connection' +num).html(connection);
    if(parseInt(minutes, 10) > 2 && relink_bools[num] !== 1){
        flasher(num, seconds);
    }
    if(relink_bools[num] == 1){
        if(parseInt(hours, 10) > 0){
            inthour = '<b>'+hours+'</b>:';
        } else{
            inthour = '';
        }
        jQNC('#connection' +num).html(visual_calls[num].cid_numb.trim()+' - '+visual_calls[num].cid_name.trim() +'</br><strong>Interpreting : '+inthour+'<b>'+ minutes +'</b>:<b>'+ seconds +'</b></strong>');
        jQNC('#connection' +num).css('color','#5FB848');
        jQNC('#connection' +num).css('background','#f2f2f2');
    }
}

function stop_timer(num){
    if(timers[num] == 1){
        timers[num] = 0;
        clearInterval(time[num], 0);
        jQNC('#connection' +num).css('color', 'red');
        jQNC('#connection' +num).css('background','#f2f2f2');
        set_as_ended(num);
    }
    set_live_tracking();
    return false;   
}

function set_as_ended(num){
    uuid = jQNC('#uuid'+num).val();
    jQNC.post("end_call/" + uuid + "/");
}
//END TIMER FUNCTIONS

//FORM CLEAR
function reset_form(num){
    if(relink_bools[num] == 1){
        jQNC('#access_code' +num).attr('disabled', false);
        jQNC('#rep_name' +num).attr('disabled', false);
        jQNC('#language' +num).attr('disabled', false);
        jQNC('#interpreter' +num).attr('disabled', false);
        jQNC('#drop' +num).attr('disabled', false);
        jQNC('#submit' +num).attr('disabled', false);
        jQNC('#co_num_add' +num).hide();
        relink_bools[num] = 0;  
    }
    if(num > 1){
        jQNC('#form' +num).hide();
    }
    visual_calls[num] = 0;
    stop_timer(num);
    toggle_sp_by_id(0, num, 0, '');
    if(jQNC("#co_num" +num).is(":visible")){
        toggle_co_by_id(num);
    }
    jQNC('#access_code' +num).val('');
    jQNC('#linkup' +num).html('<img src="../assets/img/icons/misc/circle-grey.png"/>');
    jQNC('#rep_name' +num).val('');
    jQNC('#connection' +num).html('');
    jQNC('#connection' +num).css('color', '#000000');
    jQNC('#connection' +num).css('background','#f2f2f2');
    jQNC('#specialf' +num).val('');
    jQNC('#language' +num).val('0');
    jQNC('#interpreter' +num).val('0');
    jQNC('#division').text('(Division Name)');
    jQNC('#division').css('color', 'purple');
    jQNC('#co_num' +num).val('');
    jQNC('#client_id' +num).val('');
    jQNC('#line' +num).val('');
    jQNC('#lang_code' +num).val('');
    jQNC('#uuid' +num).val('');
    jQNC('#ac_result' +num).html('');
    jQNC('#in_result' +num).html('');
    jQNC('#ac_check' +num).html('');
    jQNC('#callout'+num).prop('checked', false);
    jQNC('#drop' +num).prop('checked', false);
    jQNC('#ignore' +num).hide();
    if(manager === false){
        jQNC('#access_code' +num).hide();
    }
    if(interpreter != 1){
        jQNC('#script').hide();
        jQNC('#script-ready').hide();
    } else{
        set_my_info();
    }
    jQNC.uniform.update(jQNC('#language' +num));
    jQNC.uniform.update(jQNC('#drop'+num));
    jQNC.uniform.update(jQNC('#callout'+num));
    jQNC('#interpreter' +num).html('<option>Select Interpreter...</option>');
    jQNC.uniform.update(jQNC('#interpreter' +num));
    jQNC.uniform.update();
    return false;
}
//END FORM CLEAR

//SPECIAL FUNCTIONS
function add_callout(co_number, co_id){
    var numericReg = /^(0|[1-9][0-9]*)$/;
    if (co_number.length == 10){
        if(numericReg.test(co_number)){
            var add_co_url = "add_callout/" + co_id + "/" + co_number;
            jQNC.ajax({
                type: "POST",
                url: add_co_url,
                dataType: "json",
                success: function(data){
                    if(data === true){
                        jQNC.jGrowl("Callout number added.", { theme: 'success' });
                        jQNC('.toolbox-content-right').fadeOut();
                    } else{
                        jQNC.jGrowl("Add callout number failed.", { theme: 'error' });
                        jQNC('.toolbox-content-right').fadeOut();
                    }
                }
            });
        } else{
            jQNC.jGrowl("Callout Number Must Contain Only Numbers no letters", { theme: 'error' });
        }
    } else{
        jQNC.jGrowl("You Must Enter a number with exactly 10 digits.", { theme: 'error' });
    }
}

function add_callout_by_uuid(co_number, uuid){
    var numericReg = /^(0|[1-9][0-9]*)$/;
    if (co_number.length == 10){
        if(numericReg.test(co_number)){
            var add_co_url = "add_callout_uuid/" + uuid + "/" + co_number;
            jQNC.ajax({
                type: "POST",
                url: add_co_url,
                dataType: "json",
                success: function(data){
                    if(data === true){
                        jQNC.jGrowl("Callout number added.", { theme: 'success' });
                        jQNC('.toolbox-content-right').fadeOut();
                    } else{
                        jQNC.jGrowl("Add callout number failed.", { theme: 'error' });
                        jQNC('.toolbox-content-right').fadeOut();
                    }
                }
            });
        } else{
            jQNC.jGrowl("Callout Number Must Contain Only Numbers", { theme: 'error' });
        }
    } else{
        jQNC.jGrowl("You Must Enter a number with exactly 10 digits.", { theme: 'error' });
    }
}

function get_my_leg(uuid){
    jQNC.each(my_calls.call_monitor_data, function(index, object){
        if(uuid == object._other_leg.popup_url){
            myleg = object._my_leg.uuid;
        }
    });
    return myleg;
}

function hold_this_call(uuid){
    $.post("/gui/freeswitch/uuid/uuid_phone_hold", { uuid: uuid, template: 'json' });
}

function unhold_this_call(uuid){
    $.post('/gui/freeswitch/uuid/uuid_phone_talk', { uuid: uuid, template: 'json' });
}

function attempt_transfer(num){
    if(interpreter == 1 || parseInt(jQNC('#interpreter'+num).val(), 10) == parseInt(intid,10)){
    } else{
        source = visual_uuids[num];
        destination = get_transfer_to_uuid();
        transfer = false;
        if(destination !== false && jQNC('#callout'+num).is(':checked') === false){
            transfer = confirm('Would you like to transfer this call to '+destination.cid_name+' at '+destination.cid_numb+'?');
            if(transfer === true){
                $.post('/gui/freeswitch/uuid/uuid_bridge', { template: 'json', destination: destination.uuid, source: source }, function(data){
                });
            } else{
                return false;
            }        
        } else{
            jQNC.jGrowl("MANUAL TRANSFER REQUIRED", { theme: 'error' });
        }
    }
    submit_call(num);   
}

function get_transfer_to_uuid(){
    if(unlink_uuids.length == 1){
        return unlink_calls[0];
    } else{
        return false;
    }
}

function save_rep_name(num){
    uuid = jQNC('#uuid'+num).val();
    rep_name = jQNC('#rep_name'+num).val();
    jQNC.getJSON("save_rep_name/" + rep_name + "/" + uuid + "/");     
}

function save_specialf(num, type){
    special = false;
    uuid = jQNC('#uuid'+num).val();
    if(jQNC('#specialf' +num).is(":visible")){
            special = jQNC('#specialf'+num).val();
    }
    if(special !== "" && special !== false){
        jQNC.ajax({
            type: "POST",
            url: "save_specialf/"+uuid+"/",
            data: "specialf="+special
        });
    }   
}

//END SPECIAL FUNCTIONS