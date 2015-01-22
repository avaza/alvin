<div class="clear"></div>
<div class="grid_12" id="script">
    <div class="block-border">
        <div id="tab-panel-1">
            <div class="block-header">
                <h1>Scripts&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;</h1>
                <ul class="tabs">
                    <li><a href="#Closing">Closing Script</a></li>
                    <li><a href="#Helper">Helper Script</a></li>
                    <li><a href="#Opening">Opening Script</a></li>
                </ul>
            </div>
            <div class="block-content tab-container">
                <div id="Opening" class="tab-content">
                    <p style="color:black; font-size:14px;"><strong>Opening</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Thank you for calling Avaza Language Services, may I please have your access code?”</strong></p>
                    <p style="color:grey;">&nbsp;&nbsp;(If they are unable to provide an access code or the number they give you is invalid)</p>
                    <p style="color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;“I apologize for the inconvenience, but I am unable to access your account, please hold while you are</p>
                    <p style="color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;connected to a language specialist who will process your call.” <strong style="color:red;">Transfer to 5100</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Thank you. And you are calling from <b id=division>(Division Name)</b>?”</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Great, and who am I speaking with please?”</strong></p>
                    <p style="color:grey;">&nbsp;&nbsp;(If spelling is needed) <div style="color:blue;">”Will you please spell that for me?”</div></p>
                    <p style="color:purple;font-size:14px;"><strong>“Can I please confirm that you are calling for a <?php echo $credentials->lang; ?> interpreter?”</strong></p>
                    <p style="color:grey;">&nbsp;&nbsp;(If they request another language)</p>
                    <p style="color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;“I apologize for the inconvenience, but it seems you have been connected to the <?php echo $credentials->lang; ?> line, please</p>
                    <p style="color:blue;">&nbsp;&nbsp;&nbsp;&nbsp;hold while you are connected to a language specialist who will process your call.” <strong style="color:red;">Transfer to 5100</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Thank you, my name is <?php echo $credentials->fname;?> my ID number is <?php echo $credentials->intid; ?> and I’ll be your <?php echo $credentials->lang; ?> interpreter. Start of session,</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>please proceed.”</strong></p>
                </div>
                <div id="Helper" class="tab-content">
                    <p style="color:black; font-size:14px;"><strong>If you are having trouble hearing the Rep/LEP:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter requests that you please speak louder or move closer to the phone.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If you need the Rep/LEP to repeat a statement:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter requests that you please repeat the last statement.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If the rep/LEP is using lengthy statements:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter requests in order to maintain accuracy that you shorten statements as much as possible.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If the rep/LEP is not speaking in first person:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter requests that you please speak directly to the <?php echo $credentials->lang; ?> speaker to enhance communication.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If you are asked to read from a manuscript or to “Use your own words”:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter protocol does not allow reading from a script or use of direct speech during a session. However, I am</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>happy to interpret any statements you wish to make so please continue.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If you need clarification from the LEP:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>“Interpreter needs clarification from the <?php echo $credentials->lang; ?> speaker, please allow a moment for me to clarify their response.”</strong></p>
                    <p style="color:black; font-size:14px;"><strong>If you need clarification on terms used by Rep/LEP:</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>"Interpreter requests clarification for the term _______, could you please explain in simpler words so I can</strong></p>
                    <p style="color:purple; font-size:14px;"><strong>better relay the message to the <?php echo $credentials->lang; ?> Speaker?"</strong></p>
                </div>
                <div id="Closing" class="tab-content">
                    <p style="color:black; font-size:14px;"><strong>Closing</strong></p>
                    <p><strong style="color:purple; font-size:14px;">“Thank you for using Avaza Language Services, once again my name is <?php echo $credentials->fname;?> </strong></p>
                    <p><strong style="color:purple; font-size:14px;">and my ID number is <?php echo $credentials->intid?>. End of session.”</strong></p>
					<br>
                    <a href="https://chrome.google.com/webstore/detail/chrome-remote-desktop/gbchcmhmhahfdphkhkmpfmihenigjmpp">REMOTE LINK</a>
                    <?php if($credentials->DR_REP != 1){?>
					<p><strong style="color:black; font-size:14px;">If I can take just a moment of your time?  As a part of a customer service initiative, I want to invite you to share your thoughts on</strong></p>
					<p><strong style="color:black; font-size:14px;">how well I provided services today. If you have time, please visit us at feedback.avaza.co to fill out a quick survey form.</strong></p>
					<p><strong style="color:black; font-size:14px;">Thank you for your time today.</strong></p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>