// tipsy, facebook style tooltips for jquery
// version 1.0.0a
// (c) 2008-2010 jason frame [jason@onehackoranother.com]
// releated under the MIT license

(function(jQNC) {
    
    function fixTitle(jQNCele) {
        if (jQNCele.attr('title') || typeof(jQNCele.attr('original-title')) != 'string') {
            jQNCele.attr('original-title', jQNCele.attr('title') || '').removeAttr('title');
        }
    }
    
    function Tipsy(element, options) {
        this.jQNCelement = jQNC(element);
        this.options = options;
        this.enabled = true;
        fixTitle(this.jQNCelement);
    }
    
    Tipsy.prototype = {
        show: function() {
            var title = this.getTitle();
            if (title && this.enabled) {
                var jQNCtip = this.tip();
                
                jQNCtip.find('.tipsy-inner')[this.options.html ? 'html' : 'text'](title);
                jQNCtip[0].className = 'tipsy'; // reset classname in case of dynamic gravity
                jQNCtip.remove().css({top: 0, left: 0, visibility: 'hidden', display: 'block'}).appendTo(document.body);
                
                var pos = jQNC.extend({}, this.jQNCelement.offset(), {
                    width: this.jQNCelement[0].offsetWidth,
                    height: this.jQNCelement[0].offsetHeight
                });
                
                var actualWidth = jQNCtip[0].offsetWidth, actualHeight = jQNCtip[0].offsetHeight;
                var gravity = (typeof this.options.gravity == 'function')
                                ? this.options.gravity.call(this.jQNCelement[0])
                                : this.options.gravity;
                
                var tp;
                switch (gravity.charAt(0)) {
                    case 'n':
                        tp = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 's':
                        tp = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'e':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth - this.options.offset};
                        break;
                    case 'w':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
                        break;
                }
                
                if (gravity.length == 2) {
                    if (gravity.charAt(1) == 'w') {
                        tp.left = pos.left + pos.width / 2 - 15;
                    } else {
                        tp.left = pos.left + pos.width / 2 - actualWidth + 15;
                    }
                }
                
                jQNCtip.css(tp).addClass('tipsy-' + gravity);
                
                if (this.options.fade) {
                    jQNCtip.stop().css({opacity: 0, display: 'block', visibility: 'visible'}).animate({opacity: this.options.opacity});
                } else {
                    jQNCtip.css({visibility: 'visible', opacity: this.options.opacity});
                }
            }
        },
        
        hide: function() {
            if (this.options.fade) {
                this.tip().stop().fadeOut(function() { jQNC(this).remove(); });
            } else {
                this.tip().remove();
            }
        },
        
        getTitle: function() {
            var title, jQNCe = this.jQNCelement, o = this.options;
            fixTitle(jQNCe);
            var title, o = this.options;
            if (typeof o.title == 'string') {
                title = jQNCe.attr(o.title == 'title' ? 'original-title' : o.title);
            } else if (typeof o.title == 'function') {
                title = o.title.call(jQNCe[0]);
            }
            title = ('' + title).replace(/(^\s*|\s*$)/, "");
            return title || o.fallback;
        },
        
        tip: function() {
            if (!this.jQNCtip) {
                this.jQNCtip = jQNC('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"/></div>');
            }
            return this.jQNCtip;
        },
        
        validate: function() {
            if (!this.jQNCelement[0].parentNode) this.hide();
        },
        
        enable: function() { this.enabled = true; },
        disable: function() { this.enabled = false; },
        toggleEnabled: function() { this.enabled = !this.enabled; }
    };
    
    jQNC.fn.tipsy = function(options) {
        
        if (options === true) {
            return this.data('tipsy');
        } else if (typeof options == 'string') {
            return this.data('tipsy')[options]();
        }
        
        options = jQNC.extend({}, jQNC.fn.tipsy.defaults, options);
        
        function get(ele) {
            var tipsy = jQNC.data(ele, 'tipsy');
            if (!tipsy) {
                tipsy = new Tipsy(ele, jQNC.fn.tipsy.elementOptions(ele, options));
                jQNC.data(ele, 'tipsy', tipsy);
            }
            return tipsy;
        }
        
        function enter() {
            var tipsy = get(this);
            tipsy.hoverState = 'in';
            if (options.delayIn == 0) {
                tipsy.show();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'in') tipsy.show(); }, options.delayIn);
            }
        };
        
        function leave() {
            var tipsy = get(this);
            tipsy.hoverState = 'out';
            if (options.delayOut == 0) {
                tipsy.hide();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'out') tipsy.hide(); }, options.delayOut);
            }
        };
        
        if (!options.live) this.each(function() { get(this); });
        
        if (options.trigger != 'manual') {
            var binder   = options.live ? 'live' : 'bind',
                eventIn  = options.trigger == 'hover' ? 'mouseenter' : 'focus',
                eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
            this[binder](eventIn, enter)[binder](eventOut, leave);
        }
        
        return this;
        
    };
    
    jQNC.fn.tipsy.defaults = {
        delayIn: 0,
        delayOut: 0,
        fade: false,
        fallback: '',
        gravity: 'n',
        html: false,
        live: false,
        offset: 0,
        opacity: 0.8,
        title: 'title',
        trigger: 'hover'
    };
    
    // Overwrite this method to provide options on a per-element basis.
    // For example, you could store the gravity in a 'tipsy-gravity' attribute:
    // return jQNC.extend({}, options, {gravity: jQNC(ele).attr('tipsy-gravity') || 'n' });
    // (remember - do not modify 'options' in place!)
    jQNC.fn.tipsy.elementOptions = function(ele, options) {
        return jQNC.metadata ? jQNC.extend({}, options, jQNC(ele).metadata()) : options;
    };
    
    jQNC.fn.tipsy.autoNS = function() {
        return jQNC(this).offset().top > (jQNC(document).scrollTop() + jQNC(window).height() / 2) ? 's' : 'n';
    };
    
    jQNC.fn.tipsy.autoWE = function() {
        return jQNC(this).offset().left > (jQNC(document).scrollLeft() + jQNC(window).width() / 2) ? 'e' : 'w';
    };
    
})(jQuery);
