/*
 * Accordion Menu
 */
(function(jQNC){
	jQNC.fn.initMenu = function() {  
	    return this.each(function(){
	        var theMenu = jQNC(this).get(0);
	        
	        jQNC('li:has(ul)',this).each(function() {
				jQNC('>a', this).append("<span class='arrow'></span>");
			});
	        
	        jQNC('.sub', this).hide();
	        jQNC('li.expand > .sub', this).show();
	        jQNC('li.expand > .sub', this).prev().addClass('active');
	        jQNC('li a', this).click(
	            function(e) {
	                e.stopImmediatePropagation();
	                var theElement = jQNC(this).next();
	                var parent = this.parentNode.parentNode;
	                if(jQNC(this).hasClass('active-icon')) {
	                	jQNC(this).addClass('non-active-icon');
	                	jQNC(this).removeClass('active-icon');
	                }else{
	                	jQNC(this).addClass('active-icon');
	                	jQNC(this).removeClass('non-active-icon');
	                }
	                if(jQNC(parent).hasClass('noaccordion')) {
	                    if(theElement[0] === undefined) {
	                        window.location.href = this.href;
	                    }
	                    jQNC(theElement).slideToggle('normal', function() {
	                        if (jQNC(this).is(':visible')) {
	                            jQNC(this).prev().addClass('active');
	                        }
	                        else {
	                            jQNC(this).prev().removeClass('active');
	                            jQNC(this).prev().removeClass('active-icon');
	                        }    
	                    });
	                    return false;
	                }
	                else {
	                    if(theElement.hasClass('sub') && theElement.is(':visible')) {
	                        if(jQNC(parent).hasClass('collapsible')) {
	                            jQNC('.sub:visible', parent).first().slideUp('normal', 
	                            function() {
	                                jQNC(this).prev().removeClass('active');
	                                jQNC(this).prev().removeClass('active-icon');
	                            }
	                        );
	                        return false;  
	                    }
	                    return false;
	                }
	                if(theElement.hasClass('sub') && !theElement.is(':visible')) {         
	                    jQNC('.sub:visible', parent).first().slideUp('normal', function() {
	                        jQNC(this).prev().removeClass('active');
	                        jQNC(this).prev().removeClass('active-icon');
	                    });
	                    theElement.slideDown('normal', function() {
	                        jQNC(this).prev().addClass('active');
	                    });
	                    return false;
	                }
	            }
	        }
	    );
		});
	};
})(jQuery);
/*
 * Sliding Entrys
 */

(function(jQNC){
	jQNC.fn.slideList = function(options) {
		return jQNC(this).each(function() {
			var padding_left = jQNC(this).css("padding-left");
      		var padding_right = jQNC(this).css("padding-right");

			jQNC(this).hover(
				function() {
					jQNC(this).animate({
						paddingLeft:parseInt(padding_left) + parseInt(5) + "px"
					}, 130);
				},
				function() {
					bc_hover = jQNC(this).css("background-color");
					jQNC(this).animate({
						paddingLeft: padding_left,
						paddingRight: padding_right
					}, 130);
				}
			);
      });
	};
})(jQuery);

/*
 * Create Alert Boxes
 */

(function(jQNC){
	jQNC.fn.alertBox = function(message, options){
		var settings = jQNC.extend({}, jQNC.fn.alertBox.defaults, options);
		
		this.each(function(i){
			var block = jQNC(this);
			
			var alertClass = 'alert ' + settings.type;
			if (settings.noMargin) {
				alertClass += ' no-margin';
			}
			if (settings.position) {
				alertClass += ' top';
			}
			var alertMessage = '<div id="alertBox-generated" style="display:none" class="' + alertClass + '">' + message + '</div>';
			
			var alertElement = block.prepend(alertMessage);
			
			jQNC('#alertBox-generated').fadeIn();
		});
	};
	
	// Default config for the alertBox function
	jQNC.fn.alertBox.defaults = {
		type: 'info',
		position: 'top',
		noMargin: true
	};
})(jQuery);

/*
 * Remove Alert Boxes
 */

(function(jQNC){
	jQNC.fn.removeAlertBoxes = function(message, options){
		var block = jQNC(this);
		
		var alertMessages = block.find('.alert');
		alertMessages.remove();
	};
})(jQuery);

/*
 * Placeholder
 */

jQNC('[placeholder]').focus(function() {
  var input = jQNC(this);
  if (input.val() == input.attr('placeholder')) {
    input.val('');
    input.removeClass('placeholder');
  }
}).blur(function() {
  var input = jQNC(this);
  if (input.val() == '' || input.val() == input.attr('placeholder')) {
    input.addClass('placeholder');
    input.val(input.attr('placeholder'));
  }
}).blur().parents('form').submit(function() {
  jQNC(this).find('[placeholder]').each(function() {
    var input = jQNC(this);
    if (input.val() == input.attr('placeholder')) {
      input.val('');
    }
  })
});

/**
 * Form Reset
 * Resets the form data.  Causes all form elements to be reset to their original value.
 */
jQNC.fn.resetForm = function() {
	jQNC(this).removeAlertBoxes();
	return this.each(function() {
		// guard against an input with the name of 'reset'
		// note that IE reports the reset function as an 'object'
		if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType))
			this.reset();
	});
};

/*
 * Tabs
 */

(function(jQNC){
	jQNC.fn.createTabs = function(){
		var container = jQNC(this);
		
		container.find('.tab-content').hide();
		container.find("ul.tabs li:first").addClass("active").show();
		container.find(".tab-content:first").show();
		
		container.find("ul.tabs li").click(function() {
	
			container.find("ul.tabs li").removeClass("active");
			jQNC(this).addClass("active");
			container.find(".tab-content").hide();
	
			var activeTab = jQNC(this).find("a").attr("href");
			jQNC(activeTab).fadeIn();
			return false;
		});
		
	};
})(jQuery);



