      var $url = $('#url'),
         $features = $('#features'),
         $options = $('#get-api').find('input'),
         $callback = '?callback=h5pCaniuse&',
         $h5pMessage = $('#h5p-message'),
         $body = $( document.body ),
         $lastscript = null,
         cache = {};


     var api = {
          'domain': 'http://api.html5please.com/',
          'features': '',
          'format': '.json',
          'options': '',
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
        function getUnusedKeywords( val ) {
          var terms = split( val );
          return $.grep( keywords, function( elem ) {
            return $.inArray( elem, terms ) === -1;
          });
        }

        $features
        .bind( "keydown", function( event ) {
          if ( event.keyCode === $.ui.keyCode.TAB &&
              $( this ).data( "autocomplete" ).menu.active ) {
            event.preventDefault();
          }
        })
        .autocomplete({
          minLength: 0,
          source: function( request, response ) {
            response( $.ui.autocomplete.filter(
              getUnusedKeywords( this.element[0].value ), extractLast( request.term ) ) );
          },
          focus: function() {
            return false;
          },
          select: function( event, ui ) {
            var terms = split( this.value );
            // remove the current input
            terms.pop();
            // add the selected item
            terms.push( ui.item.value );
            terms.push(' ');
            this.value = terms.join(' ');
            api.features = this.value.trim().split(' ').join('+').trim();
            // Save the select state for use in the close event, which is called
            // after the menu is closed, and therefore can't be prevented.
            $( this ).data( 'selected', true );
            refreshOutput();
            return false;
          },
          close: function( event, ui ) {
          }
        })
        .focus(function() {
          $(this).autocomplete('search', '');
        });
      });

      $features.blur(function() {
         api.features = $features.attr('value').trim().split(' ').join('+').trim(); 
         refreshOutput();
      });

      $options.change(function() {
          refreshOutput();
      });

      function formattedOptions() {
        var currentOptions = $options.filter(function(index) { return this.checked; });

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
           $script = $('<script>'),
           api.options = $callback + formattedOptions() + '&html';
           $url = createUrl(),
           $lastscript && $lastscript.remove();
           if(cache[$url]) {
             $h5pMessage.html(cache[$url].html);
           } else {
             $body.append($script.attr('src', createUrl()));
           }
           $lastscript = $script;
         }
      };

      function createUrl() {
        return Object.keys(api).map(function(key) { 
          return api[key];
        }).join(''); 
      };

      window.h5pCaniuse = function(data) {
        if(data.supported){
          $h5pMessage.text('this browser supports these features');
        } else {
          $h5pMessage.html(data.html);
        }
        cache[createUrl()] = data;
      }

      refreshOutput();


      /* Smooth Scrolling */
      $toc = $('#toc'),
      $originalnavtop = $toc.position().top;
			$navheight = $toc.outerHeight(true);
			$('#nav_container').height($navheight),
      $stickynavheight = 0;

      $tocLinks = $toc.find('a[href^="#"]'),
			cache = {}, cacheinline = {};
			$docEl = $( document.documentElement ),
			$window = $( window ),
			$scrollable = $body
      
		if ( $docEl.scrollTop() ) {
			$scrollable = $docEl;
		} else {
			var bodyST = $body.scrollTop();
			if ( $body.scrollTop( bodyST + 1 ).scrollTop() == bodyST) {
				$scrollable = $docEl;
			} else {
				$body.scrollTop( bodyST - 1 );
			}
		}

		$tocLinks.each(function(i,v) {
			var href =  $( this ).attr( 'href' ),
				$target = $( href );
			if ( $target.length ) {
				cache[ this.href ] = { link: $(v), target: $target };
			}
		});


		$toc.delegate( 'a[href^="#"]', 'click', function(e) {
			e.preventDefault(); 
      if ( cache[ this.href ] && cache[ this.href ].target ) {
				$scrollable.animate( { scrollTop: cache[ this.href ].target.position().top - $stickynavheight }, 600, 'swing' );
			}
		});


		var deferred = false,
			timeout = false,
      last = false, 
      check = function() {
				var scroll = $scrollable.scrollTop();

				$.each( cache, function( i, v ) {
					if ( scroll + $stickynavheight >  (v.target.position().top - $stickynavheight)  ) {
						last && last.removeClass('active');
						last = v.link.addClass('active');
					} else {
						v.link.removeClass('active');
						return false; 					}
				});


				clearTimeout( timeout );
				deferred = false;
			};

		var $document = $(document).scroll( function() {

      if($scrollable.scrollTop() > ($originalnavtop)) {
        $toc.addClass('sticky').css('top', '0');
        $stickynavheight = $toc.outerHeight();
      } else {
        $toc.removeClass('sticky');
      }

			if ( !deferred ) {
				timeout = setTimeout( check , 250 ); 
        deferred = true;
			}

			$oldscrolltop = $scrollable.scrollTop();

		});

		(function() {
			$document.scroll();
			setTimeout( arguments.callee, 1500 );
		})();

