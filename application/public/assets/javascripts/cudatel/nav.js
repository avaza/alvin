/* This is the JS file that contains the navigation engine code. Did you mean js/uidef/nav.js? */

(function () {
   var ttl = 30;
   updateScreenFromHash = function () {
     if (ttl-- > 0) { setTimeout(updateScreenFromHash, 100); } else { console_log('Timed out waiting for updateScreenFromHash function'); }
   };
 })();

// Location triggered in helpers.js
$(window).bind('locationChange', function () { updateScreenFromHash(); });

$(window).bind('login', function () {
		 var tpl = '<li id="%s-menu" style="display: list-item; width: 8%;" class=""><a href="#screen=%s"><img class="buttonBarImg" alt="%s" src="/cudatel/images/buttons/%s"><span class="buttonDesc">%s</span></a></li>';

		 var buttonBar = $('#ulButtonBar');

		 $.ajax({
			  type: 'GET',
			  dataType: 'json',
			  url: '/cudatel/gui/nav/nav',
			  accept: 'application/json',
			  success: function (nav) {
			    var inverseNav = {};

			    if (nav.nav) { //only one nav item, force fullscreen mode
			      //$('#divButtonBar').hide();
			      $('#ulButtonBar li').not('.logo').hide();
			      //$('#divSecondaryNav').hide();
			      $('body').addClass('fullscreen');
			    }
			    // Build the buttonbar and setup inverse lookup table
			    $.each(nav.nav, function () {

				     if (!this.items || !this.items[0]) {
				       return;
				     }

				     var visible = $.grep(this.items, function(n) {
							    return !n.hidden;
				     });

				     var $button;
				     if (this.img) {
					 $button = $(sprintf(tpl, this.name, this.items[0].name, this.title, this.img, this.title));
				         $button.toggle(visible.length > 0).appendTo(buttonBar);
				     }

				if ($button && $button[0] && this.name == 'ccc') {
				    var $a = $button.find('a');
				    $a.attr('href', '#').bind('click', function(e) {
					e.preventDefault();
					cccOpen();
				    });
				}


				     var parent = this;
				     $.each(this.items, function() {
					      inverseNav[this.name] = this;
					      this.parent = parent;
					    });
				   });

			    var renderSecondaryNav = function(parent, screen) {
			      // render the secondary nav
			      $('#ulSecondaryNav').empty();
			      $.each(parent.items, function() {
				       if( this.hidden) {
					 return;
				       }
				       $('#ulSecondaryNav').append(sprintf('<li><a href="#screen=%s" class="secondaryNavItem screenname-%s %s">%s</a></li>', this.name, this.name, (this.name==screen ? 'current':'') , this.title));
				     });

			    };

			    updateScreenFromHash = function() {
			      updateOverlaysFromHash();
			      var screen = getHashParam('screen') || nav.nav[0].items[0].name; // default to first screen
			      if (!inverseNav[screen]) {
				// requested screen does not exist, default to first screen
				console_log('unknown screen', screen, 'requested');
				screen = nav.nav[0].items[0].name;
			      }
			      var refresh = getHashParam('refresh');
			      if (screen != getCurrentScreen() || refresh) {
				switchScreen(screen);

				var screenData = inverseNav[screen];

				var parent = screenData.parent;

				renderSecondaryNav(parent, screen);

				var imgUrl = parent.img;

				if ( imgUrl ) {
				  imgUrl = '/cudatel/images/buttons/' + imgUrl.replace(/(\.[^.]+)$/, '-active$1');
				  //$('#secondaryNavIcon')[0].src = imgUrl;
				}
				$('#secondaryNavTitle').text(parent.title);

				highlightButton($('#ulButtonBar > li:not(.logo).current').removeClass('current'), false);

				var button = $('#'+parent.name+'-menu');
				button.addClass('current').find('.buttonBarImg').attr('src', imgUrl);
			      }
			    }; // end updatescreenfromhash

			    unhideNavItem = function(child) {
			      var item = inverseNav[child];
			      if (item) {
				var visible = $.grep(item.parent.items, function(n) {
						       return !n.hidden;
						     });
				item.hidden = 0;

				var nowVisible = $.grep(item.parent.items, function(n) {
						return !n.hidden;
						  });

				if (visible.length == 0) {
				  var btn = $('#'+item.parent.name+'-menu');
				  $('a', btn).attr('href', '#screen='+item.name);
				  btn.show();
				} else if ($('#'+item.parent.name+'-menu.current')[0]) {
				  var screen = getHashParam('screen') || nav.nav[0].items[0].name; // default to first screen
				  renderSecondaryNav(item.parent, screen);
				  // ensure the button points to the first visible child
				  $('#'+item.parent.name+'-menu a').attr('href', '#screen='+nowVisible[0].name);
				} else {
				  // ensure the button points to the first visible child
				  $('#'+item.parent.name+'-menu a').attr('href', '#screen='+nowVisible[0].name);
				}
			      }
			    }

			    bindNavHover();
			    updateScreenFromHash();
			  },
			  error: function () { alert('There was an internal or network error loading the navigation bar. Please try reloading the page.'); }
			});

	       });

$(document).ready(function () {
		    $('#buttonBarLogoLink').contextMenu([
							  { title: 'Visit CudaTel.com', href: 'http://www.cudatel.com/?a=bps_product', target: '_blank' },
							  { title: 'CudaTel Support Information', href: 'http://www.cudatel.com/support/?a=bps_product', target: '_blank' }
							]);
		  });