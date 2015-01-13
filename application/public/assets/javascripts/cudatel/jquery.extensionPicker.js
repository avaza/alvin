/* Sample API
   $(textbox).extensionPicker({
   searchTypes: [
   '(any extension filter)',
   '(any extension filter):primary',
   'extensions',
   'contact',
   'contact:public',
   'contact:private',
   'contact:user'
   ],
   requirePhone: false | true, // Require the extension to have phones (only for an extension search of users)
   directory: false | true, // If true, this hides any "Do not show this user in the directory" users
   hiddenName: 'name' | false,
   hidden: $(jq) | '#selector' | false,
   hiddenFormat: '{object_type}' | '{object_id}' | '{bbx_extension_id}' | '{number}' | '{object_type}:{object_id}' | etc.,
   textFormat: ...ditto...,
   mustValidate: true | false,
   validate: 'id' | 'routable' | function (text, hidden) { return true; },
   autoSelectFirst: true | false,
   forcePickFirst: false | true, // If true, selects the first showing item in the list upon blur, if nothing else was selected
   initialSearch: { bbx_extension_id: ... }, // The actual Extension.pm search to perform
   headers: true | false,
   emptyTriggersSelection: false | true
   });

   Bindings:
   selection (no data)
*/

(function($) {
    var typeIcons = {
	unknown: '/cudatel/images/typeicons/phone.png',
	user: '/cudatel/images/typeicons/user.png',
	group: '/cudatel/images/typeicons/group.png',
	auto_attendant: '/cudatel/images/typeicons/autoattend.png',
	queue: '/cudatel/images/typeicons/queue.png',
	conference: '/cudatel/images/typeicons/conference.png',
	phone: '/cudatel/images/typeicons/phone.png',
	queue_service: '/cudatel/images/typeicons/queue_service.png',
	auto_attendant_non_interactive: '/cudatel/images/typeicons/listrouter.png',
	router: '/cudatel/images/typeicons/branchrouter.png',
	gateway: '/cudatel/images/typeicons/provider.png',
	parking: '/cudatel/images/typeicons/parking.png'
    };

    var typeNames = {
	unknown: 'Unknown Type',
	user: 'Local Extension',
	group: 'Group',
	auto_attendant: 'Automated Attendant',
	queue: 'Queue',
	conference: 'Conference',
	phone: 'Unassigned Phone',
	queue_service: 'Queue Service Extension',
	router: 'Call Router',
	gateway: 'Gateway',
	parking: 'Call Parking'
    };


    var formatString = function(formatstring, object) {
	var re = /\{([-a-zA-X0-9_]+)}/;
	var res = re.exec(formatstring);
	if (res) {
	    while (res = re.exec(formatstring)) {
		formatstring = formatstring.replace(res[0], (object[res[1]] || ''));
	    }
	    return formatstring;
	} else {
	    return (object[formatstring] || '');
	}
    };

    var highlight = function(term, string) {
	var $segments = $();

	if (term.search(/^[0-9]+$/) > -1) {
	    term = term.replace(/([0-9])/g, '[x() -]?$1');
	}
	
	var rx = new RegExp(term, 'gi'), match, last = 0, unmatched_len;
	while ((match = rx.exec(string)) !== null) {
	    var unmatched_len = match.index - last;
	    if (unmatched_len) {
		var unmatched = string.substr(last, unmatched_len);
		$segments = $segments.add($(document.createTextNode( unmatched )));
	    }
	    $segments = $segments.add($('<span class="highlight" />').text( match[0] ));
	    last = match.index + match[0].length;
	}

	unmatched_len = string.length - last;

	if (unmatched_len) {
	    $segments = $segments.add($(document.createTextNode( string.substr(last, unmatched_len) )));
	}
	return $segments;
    }

    var termToNumber = function(term) {
	if (term.search(/^sip:/) > -1) {
	    return term.replace(/[\'\"\`;=]/g, '');
	} else {
	    return term.replace(/[abc]/gi,'2').replace(/[def]/gi,'3').replace(/[ghi]/gi,'4').replace(/[jkl]/gi,'5').replace(/[mno]/gi,'6').replace(/[pqrs]/gi,'7').replace(/[tuv]/gi,'8').replace(/[wxyz]/gi,'9').replace(/[^0-9]/gi,'');
	}
    }

    var squashResults = function(results, term, params) {
	var categories = {};
	var count = 0;
	for (var i in results) {
	    if (categories[results[i].category]) {
		categories[results[i].category].push(results[i]);
	    } else {
		count += 1;
		categories[results[i].category] = [results[i]];
	    }
	}
	var cutoff = Math.ceil(params.maxValues / count);
	var free = 0;
	for (var category in categories) {
	    if (categories[category].length < cutoff) {
		free += cutoff - categories[category].length;
		//delete categories[category];
		count -= 1;
	    }
	}
	cutoff += Math.ceil(free / count);
	var newresults = []
	var lowerterm = term.toLowerCase();
	var elided = 0;
	for (var category in categories) {
	    categories[category].sort(function(a, b) {
		return Math.max(a.label.toLowerCase().indexOf(lowerterm), a.number.toLowerCase().indexOf(lowerterm)) -
		    Math.max(b.label.toLowerCase().indexOf(lowerterm), b.number.toLowerCase().indexOf(lowerterm));
	    });
	    if (categories[category].length <= cutoff) {
		newresults = newresults.concat(categories[category]);
	    } else {
		// we need to trim this to be less than cutoff
		newresults = newresults.concat(categories[category].slice(0, cutoff));
		elided += categories[category].length - cutoff;
	    }
	}
	if (!params.categories) {
	    newresults.sort(function(a, b) {
		return cmp(a.label.toLowerCase(), b.label.toLowerCase());
	    })
		.sort(function(a, b) {
		    return Math.max(a.label.toLowerCase().indexOf(lowerterm), a.number.toLowerCase().indexOf(lowerterm)) -
			Math.max(b.label.toLowerCase().indexOf(lowerterm), b.number.toLowerCase().indexOf(lowerterm));
		});
	}
	if (elided) {
	    newresults.push({elided: elided});
	}
	return newresults;
    }

    var searchextensions = function(term, params, fun, results) {
	var primaryFlag = 0;

	var searchtypes = $.map(params.searchTypes, function(e) {
	    if (e.match(/^contact/)) {
		return null;
	    } else {
		if (e.search(/:primary/) > -1) {
		    primaryFlag = 1;
		    return e.replace(/:primary/,'');
		} else {
		    return e;
		}
	    }
	});

	if (searchtypes.length > 0) {
	    var getParams = { rows: 30, type: searchtypes, search_string: term, primary: primaryFlag };

	    // Optional pass-thru params
	    if (params.directory) { getParams.directory = 1; }
	    if (params.requirePhone) { getParams.registered_phones = 1; }

	    $.getJSON('/cudatel/gui/extension/list', getParams, function(response) {
		var extensions = response.list;

		$.each(extensions, function (i, ext) {
		    result = {
			label: ext.show_name || '(Unnamed Extension)',
			value: ext.bbx_extension_id,
			category: 'Local Extensions',
			number: ext.bbx_extension_block_begin,
			type: typeNames[ext.type] || typeNames.unknown,
			extension_type: ext.type,
			icon: typeIcons[ext.type] || typeIcons.unknown,
			object_type: 'bbx_' + ext.type + '_id',
			object_id: ext['bbx_' + ext.type + '_id'],
			bbx_extension_id: ext.bbx_extension_id
		    };

		    if (!result[result.object_type]) {
			result[result.object_type] = result.object_id;
		    }

		    results.push(result);
		});

		results = squashResults(results, term, params);
		fun(results);
	    });
	} else {
	    results = squashResults(results, term, params);
	    fun(results);
	}
    };

    var searchcontacts = function(term, params, fun, results) {
	var re = /^contact:(.+)$/;
	var searchtypes = $.map(params.searchTypes, function(e) {
	    var res;
	    if (e == 'contact') {
		return 'private,user,public';
	    } else if (res = e.match(re)) {
		return res[1];
	    }
	    return null;
	});
	if (searchtypes.length > 0) {
	    $.getJSON('/cudatel/gui/contact/search', {scope: searchtypes.join(','), template:'json', rows: 30, search_string: term, extra_search: term}, function(response) {
		var contacts = response.contact;
		$.each(contacts, function (i, contact) {
		    var tels = contact.bbx_contact_labels;

		    $.each($.grep(tels || [], function (obj) { return obj.bbx_contact_label_property === 'TEL'; }), function (j, tel) {
			results.push({
			    label: contact.bbx_contact_first_name + ' ' + contact.bbx_contact_last_name,
			    value: contact.bbx_contact_id,
			    type: tel.bbx_contact_label_type, //.join(', '),
			    number: tel.bbx_contact_label_value,
			    icon: typeIcons[contact.bbx_contact_scope] || typeIcons.unknown,
			    category: scope2label[contact.bbx_contact_scope],
			    object_type: 'bbx_contact_id',
			    object_id: contact.bbx_contact_id,
			    bbx_contact_id: contacts
			});
		    });
		});
		searchextensions(term, params, fun, results);
	    });
	} else {
	    searchextensions(term, params, fun, results);
	}
    };

    var defaults = {
	searchTypes: ['all'], //[contact', 'user'],
	hiddenFormat: '{object_type}:{object_id}',
	textFormat: 'number',
	validate: ['routable'],
	mustValidate: false,
	maxValues: 10,
	categories: true
    };

    var scope2label = {
	'private': 'Address Book',
	'public': 'Global Contacts',
	user: 'Local Users'
    };

    var methods = {
	search: function (searchParams) {
	    var elem = $(this);
	    if (!elem.is('.extensionPickerReady')) {
		elem.one('extensionPickerReady', function () {
		    methods.search.call(elem[0], searchParams);
		});
		return;
	    }

	    var params = elem.data('params');

	    $.getREST('/cudatel/gui/extension/extension', searchParams, function (data) {
		var result = data.extension[0];

		if (result) {
		    result.value = result.bbx_extension_id;
		    result.number = result.bbx_extension_block_begin;
		    elem.val(formatString(params.textFormat, result)).trigger('change', { autoinit: true });
		    params.hiddenElem.val(formatString(params.hiddenFormat, result)).trigger('change', { autoinit: true });
		}
	    });
	},
	
	init: function(params) {

	    params = $.extend({}, defaults, params);

	    if (typeof params.searchTypes == 'string') {
		params.searchTypes = [params.searchTypes];
	    }

	    var callback = function(req, fun) {
		var results = [];
		searchcontacts(req.term, params, fun, results);
	    };

	    return $(this).each(function() {
		var elem = $(this);

		// Only allow this to happen once-- TODO: Should have a more elegant solution to this, but this should work for now.
		if (elem.data('extensionPicker')) {
		    return;
		} else {
		    elem.data('extensionPicker', true);
		}

		if (params.hidden) {
		    params.hiddenElem = $(params.hidden);
		} else {
		    if (params.hiddenName) {
			params.hiddenElem = $('<input type="hidden">').attr('name', params.hiddenName).insertAfter($(this));
		    } else {
			params.hiddenElem = $('<input type="hidden"/>').insertAfter($(this));
		    }
		}

		if (params.initialSearch) {
		    methods.search.call(elem[0], params.initialSearch);
		}

		var haspicked = false;
		var willpick = false;
		var lastSelected = false;

		var pick = function (uiItem) {
		    
		    params.hiddenElem.val(formatString(params.hiddenFormat, uiItem));
		    elem.val(formatString(params.textFormat, uiItem));
		    
		    params.hiddenElem.trigger('dirty');
		    params.hiddenElem.trigger('selection', uiItem);
		    
		    elem.trigger('dirty')
		    elem.trigger('selection', uiItem);
		    haspicked = true;
		}; // END INTERNAL FUNCTION pick

		var autocomplete = $(this).autocomplete({
		    source: callback,
		    minLength: 1,
		    select: function(event, ui) {
			pick(ui.item);
			return false;
		    },
		    focus: function(event, ui) {
			if (params.forcePickFirst) {
			    willpick = true;
			}
			return false;
		    },
		    close: function (e,ui) {
			if (params.forcePickFirst && elem.val() !== '') {
			    if (!haspicked && lastSelected) {
				pick(lastSelected);
			    }
			}
			
			willpick = false;
		    },
		    open: function (e,ui) {
			haspicked = false;
			params.hiddenElem.val('');

			var menu = $(this).data('autocomplete').menu;
			var firstItem = menu.element.find('.ui-menu-item:first');

			if (params.forcePickFirst && firstItem.data('item.autocomplete')) {
			    lastSelected = firstItem.data('item.autocomplete');
			}

			if (params.autoSelectFirst) {
			    menu.activate( $.Event({ type: 'mouseenter' }),
					   firstItem );
			}
		    }
		})
		    .bind('focus', function(e) {
			$(e.target).css('border-color', '');
		    })
		    .bind('blur', function(e) {
			if (params.mustValidate && !params.emptyTriggersSelection && !haspicked && params.hiddenElem.val() == '') {
			    $(e.target).css('border-color', 'red');
			}
			
			if (params.emptyTriggersSelection && elem.val() === '') {
			    pick({});
			} else if (!haspicked) {
			    elem.trigger("noselection");
			}
		    })
		    .data('autocomplete');

		autocomplete._renderMenu = function( ul, items ) {
		    var self = this, currentCategory;
		    $.each( items, function( index, item ) {
			if (item.elided) {
			    ul.append( "<li>And " + parseInt(item.elided, 10) + " more..</li>" );
			    return;
			} else if ( item.category != currentCategory && params.categories ) {
			    ul.append($('<li class="ui-autocomplete-category" />').text(item.category));
			    currentCategory = item.category;
			}
			self._renderItem( ul, item );
		    });
		};
		
		autocomplete._renderItem = function( ul, item ) {
		    if (item.number && item.type != undefined) {
			var $li = $('<li><a><img /><span class="label" /><br /><span class="extPickerNumber" /> <span class="extPickerItemType" /></a></li>');
			var $a = $li.find('> a');
			$li.data("item.autocomplete", item);
			$a.find('> img').attr('src', item.icon);
			$a.find('> .label').append( highlight(this.term, item.label) ); // highlight will sanitize
			$a.find('> .extPickerNumber').append( highlight(this.term, format_information(item.number)) ); // highlight will sanitize
			$a.find('> .extPickerItemType').text(item.type);
			$li.appendTo(ul);
			return $li;
		    } else {
			var $li = $('<li><a /></li>');
			$li.data('item.autocomplete', item);
			$li.find('> a').text(item.label);
			$li.appendTo(ul);
			return $li;
		    }
		};

		elem.data('params', params);
		elem.addClass('extensionPickerReady').trigger('extensionPickerReady');
	    });
	}
    };

    $.fn.extensionPicker = function( method ) {
	if ( methods[method] ) {
	    return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
	} else if ( typeof method === 'object' || ! method ) {
	    return methods.init.apply( this, arguments );
	} else {
	    $.error( 'Method ' +  method + ' does not exist on jQuery.extensionPicker' );
	}

    };
})( jQuery );

