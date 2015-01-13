<a rel="tooltip-left" title="Report Incident" class="toolbox-action-incident" href="javascript:void(0);"><img src="../../assets/img/icons/packs/fugue/16x16/clipboard--exclamation.png"/></a>
<div class="toolbox-content-incident">
    <div class="block-border">
        <div class="block-header small">
            <h1>Report Incident</h1>
        </div>
        <form id="add_incident" class="block-content form" action="" method="post">  
            <table style="width:100%">
                <tr><td>Rude To Router</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="1"></td><td>Offended if asked to repeat</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="9"></td></tr>
                <tr><td>Rude to LEP</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="2"></td><td>Not speaking in first person</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="10"></td></tr>
                <tr><td>No Access Code</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="3"></td><td>Not speaking in short sentences</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="11"></td></tr>
                <tr><td>Could not identify Language</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="4"></td><td>Impatient/constant interruption</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="12"></td></tr>
                <tr><td>Refused to spell Name</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="5"></td><td>Long Hold Time</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="13"></td></tr>
                <tr><td>Rushing</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="6"></td><td>Misdirect</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="14"></td></tr>
                <tr><td>Video Conference not working</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="7"></td><td>Technical Issues</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="15"></td></tr>
                <tr><td>Hard to hear/Speakerphone</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="8"></td><td>Language Not Available</td><td><input type="checkbox" name="incident<?php echo $call->id; ?>[]" value="16"></td></tr>
            </table>
            <div class="clear"></div>
            <div class="block-actions">
                <ul class="actions-left">
                    <li><input type="button" class="button red" onClick="cancel_incident();" value="Cancel"></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="button" class="button" onClick="check_data(<?php echo $call->id; ?>)" value="Report"></li>
                </ul>
            </div>
        </form>
    </div>         
</div>