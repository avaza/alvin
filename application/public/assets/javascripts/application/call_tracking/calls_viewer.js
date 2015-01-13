var jQNC = jQuery.noConflict();
var shown = [];
var oTable;
var refresh;
jQNC(document).ready(function(){
    oTable = jQNC('#calls-table').dataTable({
        "bLengthChange": false,
        "aLengthMenu":[],
        "iDisplayLength": 10,
        "bSearchable": true,
        "sAjaxSource": "../update_calls_viewer/"+page,
        "aoColumns": [
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          null,
          { "bSearchable": false },
          { "bSearchable": false }
        ] });
    start_refresh();

    jQNC('.toolbox-action-right').live('click', function(){
        stop_refresh();
    });

});

//AUTO UPDATE
function start_refresh(){
    refresh = setInterval(function(){
        jQNC('#calls-table').dataTable().fnReloadAjax();
    }, 5000);
}

function stop_refresh(){
    if(refresh && refresh !== 0){
        clearInterval(refresh);
        refresh = 0;
    }
}
//END AUTO UPDATE

function add_callout(co_number, co_id){
    var numericReg = /^(0|[1-9][0-9]*)$/;
    if (co_number.length == 10){
        if(numericReg.test(co_number)){
            var add_co_url = "../add_callout/" + co_id + "/" + co_number;
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
                    start_refresh();
                }
            });
        } else{
            jQNC.jGrowl("Callout Number Must Contain Only Numbers", { theme: 'error' });
        }
    } else{
        jQNC.jGrowl("You Must Enter an number with exactly 10 digits.", { theme: 'error' });
    }
}

function cancel(){
    jQNC('.toolbox-content-right').fadeOut();
    start_refresh();
}

function cancel_incident(){
    jQNC('.toolbox-content-incident').fadeOut();
    start_refresh();
}
