<script type="text/javascript" src="/assets/javascripts/application/call_tracking/status_change.js"></script>
<div class="clear"></div>
<div class="grid_12" id="script">
    <div class="block-border">
    	<div class="block-header">
    		<h1>Status</h1><span></span>
    	</div>
        <div class="block-content" style="height:150px;">
    		<select id="status-select">
        		<option value="3">Lunch</option>
        		<option value="6">Break</option>
        		<option value="1">Available</option>
        		<option value="2">Offline</option>
        	</select>
            </br>
            <button id="set_status">Set Status</button>
            </br>
        </div>
    </div>
</div>
<div class="clear"></div>
<script type="text/javascript">
var user=<?php echo $credentials->ext;?>;
var pass=<?php echo $credentials->pin;?>;
jQNC(document).ready(function(){
    cuda_log_me_in(pass, user);
    jQNC('#set_status').click( function(){
        console.log('clicked');
        set_cudatel_status(jQNC('#status-select').val());
    });
});


</script>