<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <!-- Disable Skype Toolbar phone number linking -->
    <meta name="SKYPE_TOOLBAR" content="SKYPE_TOOLBAR_PARSER_COMPATIBLE"/>
    <title>CudaTel Communications Server</title>

    <link id="favicon" rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" href="/images/iCon.png"/>
    <link rel="stylesheet" type="text/css" href="/css/dynatree/ui.dynatree.css" />
    <link rel="stylesheet" type="text/css" href="/css/tablesorter/style.css" />

    <link rel="stylesheet" type="text/css" href="/css/flick/jquery-ui-1.8.6.custom.css" />
    <link rel="stylesheet" type="text/css" href="/css/picbox/picbox.css" />
    <link rel="stylesheet" type="text/css" href="/css/all.css" />

    
        <script type="text/javascript" src="<?php echo base_url(); ?>assets/javascripts/cudatel/cc_out.js"></script>
    

    <script type="text/javascript" src="/js/init.js"></script>

    <script type="text/javascript" src="/js/picbox/picbox.js"></script>
    <script type="text/javascript" src="/js/nav.js"></script>

  </head>
  <body class="full-ui">

    <div class="noCSS" style="height: 1000px; text-align: center">The application&rsquo;s CSS files are loading. If the screen does not properly display, you may need to Refresh or Reload the page.</div>

    <div id="noJSWarning">
      <!-- Cleared by init.js -->
      <p>JavaScript is not enabled in this browser. The CudaTel Communication Server&rsquo;s Web interface requires JavaScript. Please activate JavaScript and remove any script blocking on this domain.</p>
      <p>If you have enabled JavaScript, you may need to refresh this page or clear your browser&rsquo;s cache to continue.</p>
    </div>
    <!-- JS code that removes the box that says that JS is not loaded -->
    <script type="text/javascript">
   $('#noJSWarning').remove();                                     // Remove it immediately if JS is available, to prevent flickering when the page loads
   $(document).ready(function () { $('#noJSWarning').remove(); }); // Try again on ready, just in case the browser didn't recognize the DOM yet
</script>



    <div id="all">
      <div id="allScreen" class="widgetType"></div>
      <div id="allOverlay" class="widgetType"></div>

    </div>
    <div id="preloader"></div>
    <div class="commonBlankerBackground" id="commonBlankerBackground"></div>
    <div id="allPopup"></div>
    <div id="overlaytemplate" style="display: none">
      <div class="popup">
         <div class="popupClose">
     <a style="padding-right: 2px;" class="popupHelpButton" href="#">
       <img width="20" height="20" src="/images/popup_help_icon.png"/>
     </a>
     <a class="popupCloseButton" href="#">
       <img width="20" height="20" src="/images/popup_close_icon.png"/>
     </a>
   </div>
   <div class="popupcontents liveFormWrap">
           <div style="text-align: center; margin-top 50px;">
       <img src="/images/bigwait.gif" width="100" height="100" alt="Please Wait..." />
     </div>
   </div>
      </div>
      <div class="widgetType overlay">
  <img src="/images/back_icon_heavy.png" width="45" height="20" alt="Close" class="closeRight"/>
  <a style="padding-right: 2px;" class="overlayHelpButton helpLink panelAuto" href="#">
    <img width="35" height="20" src="/images/help_icon_heavy.png"/>
  </a>
  <div class="widgetType megaPanel liveFormWrap">
    <div style="text-align: center; padding-top:200px">
      <img src="/images/bigwait.gif" width="100" height="100" alt="Please Wait..." />
    </div>
  </div>
      </div>
    </div>
  </body>
</html>