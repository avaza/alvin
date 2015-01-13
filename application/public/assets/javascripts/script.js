var jQNC = jQuery.noConflict();
jQNC(document).ready(function() {
	
	/*
	 * Accordion Menu
	 */
	jQNC('.menu').initMenu();
	
	
	
	/*
	 * Slide Effect
	 *
	 */
	jQNC('.menu li a').slideList();
	
	
	/*
	 * Form Elements
	 */
	jQNC("select, input:checkbox, input:text, input:password, input:radio, input:file, textarea").uniform();
		
	/*
	 * Closable Alert Boxes
	 */
	jQNC('span.hide').click(function() {
		jQNC(this).parent().slideUp();					   
	});	
	
	/*
	 * Toolbox
	 */
	/*jQNC('.toolbox-action').click(function() {
		jQNC('.toolbox-content').fadeOut();
		jQNC(this).next().fadeIn();
		
        return false;
	});*/
	
	jQNC('.close-toolbox').click(function() {
		jQNC(this).parents('.toolbox-content').fadeOut();
	});
	
	/*
	 * Toolbox
	 */
	jQNC('.toolbox-action-right').live('click',function() {
		jQNC('.toolbox-content-right').fadeOut();
		jQNC(this).next().fadeIn();
        return false;
	});
	
	jQNC('.close-toolbox').live('click',function() {
		jQNC(this).parents('.toolbox-content-right').fadeOut();
	});

	jQNC('.toolbox-action-incident').live('click', function() {
        jQNC('.toolbox-content-incident').fadeOut();
        jQNC(this).next().fadeIn();
        if(refresh && refresh !== 0){
	        clearInterval(refresh);
	        refresh = 0;
	        console.log('stopped');
	    }
        return false;
    });

	/*
	 * Dropdown-menu for left sidebar
	 */
	jQNC('.user-button').click(function() {
		jQNC('.dropdown-username-menu').slideToggle();
	});
	
	jQNC(document).click(function(e){
		if (!jQNC(e.target).is('.user-button, .arrow-link-down, .dropdown-username-menu *')) {
			jQNC('.dropdown-username-menu').slideUp();
		}
	});
	
	var ddumTimer;
	
	jQNC('.user-button, ul.dropdown-username-menu').mouseleave(function(e) {
		ddumTimer = setTimeout(function() {
			jQNC('.dropdown-username-menu').slideUp();
		},400);
	});
	
	jQNC('.user-button, ul.dropdown-username-menu').mouseenter(function(e) {
		clearTimeout(ddumTimer);
	});
	
	
	
	/*
	 * Closable Content Boxes
	 */
	jQNC('.block-border .block-header span').click(function() {
		if(jQNC(this).hasClass('closed')) {
			jQNC(this).removeClass('closed');
		} else {
			jQNC(this).addClass('closed');
		}
		jQNC(this).parent().parent().children('.block-content').slideToggle();
	});

	
	
	/*
	 * Tooltips
	 */
	jQNC('a[rel=tooltip]').tipsy({fade: true});
	jQNC('a[rel=tooltip-bottom]').tipsy({fade: true});
	jQNC('a[rel=tooltip-right]').tipsy({fade: true, gravity: 'w'});
	jQNC('a[rel=tooltip-top]').tipsy({fade: true, gravity: 's'});
	jQNC('a[rel=tooltip-left]').tipsy({fade: true, gravity: 'e'});
	jQNC('a[rel=tooltip-html]').tipsy({fade: true, html: true});
	jQNC('div[rel=tooltip]').tipsy({fade: true});
});

























