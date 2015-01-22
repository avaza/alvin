<!--<script src="/js/cc_out3.js"></script>-->
<div class="clear"></div>
<div class="grid_12" id="script">
    <div class="block-border">
        <div id="tab-panel-2">
            <div class="block-header">
                <ul class="tabs">
                <?php if($page != 'interpreter'){ ?>
                    <li><a href="#Interpreters">Interpreter Search</a></li>
                    <?php } ?>
                    <li><a href="#Clients">Client Search</a></li>
                    <?php if($page == 'manager'){ ?>
                    <li><a href="#screen-activecalls">Active/Recent Calls</a></li>                    
                    <li><a href="#screen-agentmgr">Agent Manager</a></li>
                    <?php } ?>
                    <li><a href="#callsView" id="callClick">Today's Calls</a></li>
                    <?php if($page != 'interpreter'){ ?>
                    <li><a href="#screen-agentwall">*NEW INTERPRETERS!</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="block-content tab-container">
                <?php if($page != 'interpreter'){ ?>
                <div id="screen-agentwall" class="screen tab-content" style="display: block;">
                    <h1>New Interpreters use first!!</h1>
                    <p>
                        <table style="border-color:black;">
                        <th><tr><td style="padding: 10px;border-color:black;" style="padding: 10px;">ID</td><td style="padding: 10px;border-color:black;">Name</td><td style="padding: 10px;border-color:black;">Language</td></tr></th>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>4077</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Luma"</td><td style="padding: 10px;border-color:black;"><strong>ARABIC</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>3744</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Suraj"</td><td style="padding: 10px;border-color:black;"><strong>NEPALI</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>4078</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Doris"</td><td style="padding: 10px;border-color:black;"><strong>BURMESE</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>3295</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Van"</td><td style="padding: 10px;border-color:black;"><strong>VIETNAMESE</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>2031</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Mireille"</td><td style="padding: 10px;border-color:black;"><strong>FRENCH</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>2031</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Mireille"</td><td style="padding: 10px;border-color:black;"><strong>SWAHILI</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>2031</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Mireille"</td><td style="padding: 10px;border-color:black;"><strong>LUGANDA</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>3752</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Om"</td><td style="padding: 10px;border-color:black;"><strong>NEPALI</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>3071</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Srbuhi"</td><td style="padding: 10px;border-color:black;"><strong>ARMENIAN</strong></td>
                        </tr>
                        <tr>
                        <td style="padding: 10px;border-color:black;"><strong>3071</strong></td><td style="padding: 10px;border-color:black;" style="color:blue;">"Srbuhi"</td><td style="padding: 10px;border-color:black;"><strong>RUSSIAN</strong></td>
                        </tr>
                        </table>
                    </p>
                </div>
                <?php } ?>
                <?php if($page == 'manager'){ ?>
                <div id="screen-agentmgr" class="screen tab-content" style="display: block;">
                    <div id="stubAgentManager"></div>
                </div>
                <div id="screen-activecalls" class="screen tab-content" style="display: block;">
                    <div id="statsPageActiveChannels"></div>
                </div>
                <?php } ?>
                <?php if($credentials->DR_REP != 1){ ?>
	                <div id="callsView" class="tab-content"> 
	                    <IFRAME id="callsframe" SRC='<?php echo base_url();?>tracker/calls_viewer/<?php echo $page;?>'SCROLLING='no' WIDTH='101%' HEIGHT='525px' style="margin-left:-10px;" FRAMEBORDER='no'></IFRAME>
	                </div>          
	                <div id="Clients" class="tab-content"> 
	                    <IFRAME id="crud_frame" SRC='<?php echo base_url();?>crud/clients_view' SCROLLING='yes' WIDTH='100%' HEIGHT="500px" FRAMEBORDER='no'></IFRAME>
	                </div>
	                <?php if($page != 'interpreter'){ ?>
	                <div id="Interpreters" class="tab-content">
	                    <IFRAME id="crud_frame" SRC='<?php echo base_url();?>crud/interpreters_view' SCROLLING='yes' WIDTH='100%' HEIGHT="500px" FRAMEBORDER='no'></IFRAME>
	                </div>
	                <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
function change_iframe(pixels) {
    jQNC('#crud_frame').css('height', pixels+5+'px');
}
</script>
