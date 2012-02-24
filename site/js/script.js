      var url = $('#url'),
         features = document.getElementById('features'),
         formats = document.querySelectorAll('input[name="format"]'),
         options = document.querySelectorAll('input[name="options"]'),
         callbackvalue = document.getElementById('callbackvalue'),
         originalcallbackvalue = '',
         callback = document.getElementById('callback'),
         jsonhelp = $('.js-help-json'),
         lastActive = false;


         var api = {
          'domain': 'http://api.html5please.com/',
          'features': '',
          'format': '',
          'options': '',
          'previewOptions': ''
          } 


          //autocomplete
    $(function() {
      var keywords = ['png-alpha', 'apng', 'video', 'audio', 'contenteditable', 'dragndrop', 'queryselector', 'getelementsbyclassname', 'forms', 'html5semantic', 'offline-apps', 'webworkers', 'fontface', 'eot', 'woff', 'multibackgrounds', 'border-image', 'background-img-opts', 'css-table', 'css-gencontent', 'css-fixed', 'hashchange', 'css-sel2', 'css-sel3', 'css-textshadow', 'css-boxshadow', 'css3-colors', 'css3-boxsizing', 'css-mediaqueries', 'multicolumn', 'border-radius', 'transforms2d', 'use-strict', 'transforms3d', 'sharedworkers', 'css-hyphens', 'css-transitions', 'font-feature', 'css-animation', 'css-gradients', 'css-canvas', 'css-reflections', 'css-masks', 'svg', 'svg-css', 'svg-smil', 'svg-fonts', 'svg-filters', 'svg-html', 'svg-html5', 'canvas', 'canvas-text', 'namevalue-storage', 'sql-storage', 'indexeddb', 'eventsource', 'x-doc-messaging', 'datauri', 'mathml', 'xhtml', 'xhtmlsmil', 'wai-aria', 'geolocation', 'flexbox', 'webgl', 'fileapi', 'websockets', 'script-async', 'cors', 'calc', 'ruby', 'css-opacity', 'form-validation', 'history', 'json', 'classlist', 'text-overflow', 'webm', 'mpeg4', 'ogv', 'wordwrap', 'progressmeter', 'object-fit', 'xhr2', 'minmaxwh', 'details', 'text-stroke', 'inline-block', 'notifications', 'stream', 'svg-img', 'datalist', 'dataset', 'css-grid', 'menu', 'rem', 'ttf', 'touch', 'matchesselector', 'pointer-events', 'blobbuilder', 'filereader', 'filesystem', 'bloburls', 'typedarrays', 'deviceorientation', 'script-defer', 'nav-timing', 'audio-api', 'css-regions', 'fullscreen', 'requestanimationframe', 'matchmedia'];

      function split( val ) {
        return val.split( /\s+/ );
      }
      function extractLast( term ) {
        return split( term ).pop();
      }

      $( features )
      // don't navigate away from the field on tab when selecting an item
      .bind( "keydown", function( event ) {
        if ( event.keyCode === $.ui.keyCode.TAB &&
						$( this ).data( "autocomplete" ).menu.active ) {
					event.preventDefault();
				}
			})
			.autocomplete({
				minLength: 0,
				source: function( request, response ) {
					// delegate back to autocomplete, but extract the last term
					response( $.ui.autocomplete.filter(
						keywords, extractLast( request.term ) ) );
				},
				focus: function() {
					// prevent value inserted on focus
					return false;
				},
				select: function( event, ui ) {
					var terms = split( this.value );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
          terms.push(' ');
					// add placeholder to get the comma-and-space at the end
					this.value = terms.join( " " );
          api.features = this.value.trim().split(' ').join('+').trim();
          refreshOutput();
					return false;
				}
			});
    });

      features.onblur = function() {
        
         api.features = features.value.trim().split(' ').join('+').trim(); 
         refreshOutput();
      };

      [].forEach.call(formats, function(format) {
       format.onclick =  function() {
         api.format = this.value;
         showFormatOptions();
         refreshOutput();
       };
      });

      [].forEach.call(options, function(option) {
        option.onchange =  function() {
          refreshOutput();
        };
      });

      callbackvalue.onfocus = function() {
        console.log(originalcallbackvalue, this.value);
        if(this.value == originalcallbackvalue) {
          this.value = '';
        }
      };

      callbackvalue.onblur = function() {
        if(this.value == '') {
          this.value = originalcallbackvalue;
        }
        callback.value = 'callback=' + this.value;
        refreshOutput();
      };
       
      function showFormatOptions() {
        
        if (lastActive && lastActive.hasClass('active')) { 
          lastActive.removeClass('active'); 
        };
        api.options = '';
        var formatOptions = $('.js-options-'+ api.format);
        if(formatOptions.length > 0) { 
          formatOptions.addClass('active'); 
          lastActive = formatOptions;
        }
      }

      function formattedOptions() {
        var currentOptions = $('.js-options-' + api.format).find('input[name="options"]');
        currentOptions = currentOptions.filter(function(index) { return this.checked; });

        if(currentOptions.length > 0) {
          currentOptions = $.map(currentOptions, function(option) { 
            return option.value; 
          });
          return currentOptions.join('&');
        } else {
          return '';
        }
      } 

      function refreshOutput() {
         if(api.features !== '') {
           api.options = formattedOptions();
          
           url.text(createUrl());
           url.attr('href', url.text());
           if(api.format == 'json') {
             jsonhelp.show();
           } else {
             jsonhelp.hide();
           }
         }
      };

      function createUrl() {
        return Object.keys(api).map(function(key) { 
          var operator = '';
          if(key === 'format') {
            operator = '.'; 
          } else if (key === 'options') {
            operator = '?';
          } 

          return operator + api[key];

        }).join(''); 
      };

      api.features = '';
      api.format = $('input[name="format"][checked]')[0].value; 
      originalcallbackvalue = callbackvalue.value;
      console.log(originalcallbackvalue);
      callbackvalue = 'callback=' + originalcallbackvalue;
      showFormatOptions();
      refreshOutput();

      $('.more-info').click(function(e) {
        $(this.hash).toggleClass('active');
        e.preventDefault();
      });

      /* Smooth Scrolling */
      $toc = $('#toc'),
      $originalnavtop = $toc.position().top;
			$navheight = $toc.outerHeight(true);
			$('#nav_container').height($navheight),
      $stickynavheight = 0;

      $tocLinks = $toc.find('a[href^="#"]'),
			cache = {}, cacheinline = {};
			$docEl = $( document.documentElement ),
			$body = $( document.body ),
			$window = $( window ),
			$scrollable = $body
      
    // find out what the hell to scroll ( html or body )
		// its like we can already tell - spooky
		if ( $docEl.scrollTop() ) {
			$scrollable = $docEl;
		} else {
			var bodyST = $body.scrollTop();
			// if scrolling the body doesn't do anything
			if ( $body.scrollTop( bodyST + 1 ).scrollTop() == bodyST) {
				$scrollable = $docEl;
			} else {
				// we actually scrolled, so, er, undo it
				$body.scrollTop( bodyST - 1 );
			}
		}

		// build cache
		$tocLinks.each(function(i,v) {
			var href =  $( this ).attr( 'href' ),
				$target = $( href );
			if ( $target.length ) {
				cache[ this.href ] = { link: $(v), target: $target };
			}
		});


		// handle nav links
		$toc.delegate( 'a[href^="#"]', 'click', function(e) {
			e.preventDefault(); // if you expected return false, *sigh*
			if ( cache[ this.href ] && cache[ this.href ].target ) {
				$scrollable.animate( { scrollTop: cache[ this.href ].target.position().top - $stickynavheight }, 600, 'swing' );
			}
		});


		// auto highlight nav links depending on doc position
		var deferred = false,
			timeout = false, // so gonna clear this later, you have NO idea
			last = false, // makes sure the previous link gets un-activated
			check = function() {
				var scroll = $scrollable.scrollTop();

				$.each( cache, function( i, v ) {
					// if we're past the link's section, activate it
					if ( scroll + $stickynavheight >  (v.target.position().top - $stickynavheight)  ) {
						last && last.removeClass('active');
						last = v.link.addClass('active');
					} else {
						v.link.removeClass('active');
						return false; // get outta this $.each
					}
				});


				// all done
				clearTimeout( timeout );
				deferred = false;
			};

		// work on scroll, but debounced
		var $document = $(document).scroll( function() {

      if($scrollable.scrollTop() > ($originalnavtop)) {
        $toc.addClass('sticky').css('top', '0');
        $stickynavheight = $toc.outerHeight();
      } else {
        $toc.removeClass('sticky');
      }

			// timeout hasn't been created yet
			if ( !deferred ) {
				timeout = setTimeout( check , 250 ); // defer this stuff
				deferred = true;
			}

			$oldscrolltop = $scrollable.scrollTop();

		});

		// fix any possible failed scroll events and fix the nav automatically
		(function() {
			$document.scroll();
			setTimeout( arguments.callee, 1500 );
		})();


