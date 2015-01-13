var interpreter = 1;
var manager = false;
jQNC(document).ready(function(){
    set_my_info();
    jQNC("#tab-panel-1").tabs({ selected: 2 });
    jQNC('#access_code1').hide();
    jQNC('#specialf1').hide();
    jQNC('#co_num1').hide();
    jQNC('.cnum').hide();
    jQNC('.spn').hide();
    jQNC('#co_num_add1').hide();
    jQNC.getScript("../assets/javascripts/application/call_tracking/tracker.js");
});

function set_my_info(){
    jQNC.getJSON('../tracker/get_int_by_intid/'+intid+'/', function(data){
        jQNC('#interpreter1').html('<option value="'+intid+'">'+data[0].name+'</option>');
        jQNC('#interpreter1').val(intid);
        jQNC.uniform.update('#interpreter1');
        jQNC('#interpreter1').prop('disabled', true);
        if(data.length == 1){
            my_language = data[0].language_code;
            jQNC('#language1').val(my_language);
            jQNC.uniform.update('#language1');
            jQNC('#language1').prop('disabled', true);
        } else{
            my_language = data[0].language_code;
            jQNC('#language1').val(my_language);
            jQNC.uniform.update('#language1');
        }
    });
}

