var manager = false;
var interpreter = 0;
jQNC(document).ready(function() {
    jQNC('#example').dataTable({"bSort": false});
    jQNC('#script').hide();
    jQNC('#script-ready').hide();
    jQNC("#tab-panel-1").tabs({ selected: 2 });
    jQNC.getScript("../assets/javascripts/application/call_tracking/tracker.js");
});