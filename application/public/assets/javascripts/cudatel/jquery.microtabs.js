//small tabs used in call control client

//TODO document me

(function($) {
   var getHrefSelector = function(elem) {
     var hrefs = elem.attr('href').match(/#([^#]+)$/);
     if (hrefs && hrefs[1]) {
       return $('#'+hrefs[1]);
     }
     
   }
   
   jQuery.fn.microtabs = function(params) {
     
     return $(this).each(function() {
			   var elem = $(this);
			   var active;
			   if (params.hashParam) {
			     active = elem.find('li > a[href=#'+getHashParam(params.hashParam)+']');
			     elem.find('li.active').removeClass('active');
			   } else {
			     active = elem.find('li.active > a').first();
			   }
			   if (!active[0]) {
			     active = elem.find('li > a').first()
			   }
			   active.parent().addClass('active');
			   var container = $(params.container);
			   if (!container[0]) {
			     container = elem.parent();
			   }
			   var content = $(getHrefSelector(active), container).addClass('active').show();
			   container.children('div').not(content).removeClass('active').hide();
			   elem.find('li > a').bind('click', function(ev) {
						      ev.preventDefault();
						      $('div.active', container).removeClass('active').hide().trigger('tabhide');
						      $(getHrefSelector($(this)), container).addClass('active').show().trigger('tabshow');
						      elem.find('li.active').removeClass('active');
						      $(this).parent().addClass('active');
						      if (params.hashParam) {
							var value = $(this).attr('href').match(/#([^#]+)$/)[1];
							setHashParam(params.hashParam, value);
						      }
						      $(this).blur();
						    });
			 });
   }
   
 })(jQuery);
