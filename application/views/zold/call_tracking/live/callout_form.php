<a rel="tooltip" title="Add Callout Number" class="toolbox-action-incident" href="javascript:void(0);"><img src="../../assets/img/icons/packs/fugue/16x16/edit-number.png"/></a>
<div class="toolbox-content-incident">
    <div class="block-border">
        <div class="block-header small">
            <h1>Callout #</h1>
        </div>
        <form id="add_co_num" class="block-content form" action="" method="post">
            <p class="inline-mini-label">
                <label for="co_num<?php echo $call->id; ?>">Callout to:</label>
                <input type="text" id="co_num<?php echo $call->id; ?>" name="co_num<?php echo $call->id; ?>" class="required">
            </p> 
                <input type="hidden" id="call<?php echo $call->id; ?>" name="call<?php echo $call->id; ?>" value="<?php echo $call->id; ?>">
            <div class="clear"></div>
            <div class="block-actions">
                <ul class="actions-left">
                    <li><input type="button" class="button red" onClick="cancel(); return false;" value="Cancel"></li>
                </ul>
                <ul class="actions-right">
                    <li><input type="button" class="button" onClick="add_callout(jQNC('#co_num<?php echo $call->id; ?>').val(),<?php echo $call->id; ?>)" value="Add"></li>
                </ul>
            </div>
        </form>
    </div>                                        
</div>