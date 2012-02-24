      var url = $('#url'),
         features = document.getElementById('features'),
         formats = document.querySelectorAll('input[name="format"]'),
         options = document.querySelectorAll('input[name="options"]'),
         callbackvalue = document.getElementById('callbackvalue'),
         callback = document.getElementById('callback'),
         optionsHeading = $('.options h3'),
         cache = {}, lastActive = false;


         var api = {
          'domain': 'http://sandbox.thewikies.com/caniuse/',
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
        if(callbackvalue.value == 'callback') {
          callbackvalue.value = '';
          $(callback).prop('checked', true);
        }
      };

      callbackvalue.onblur = function() {
        if(callbackvalue.value == '') {
          callbackvalue.value = 'callback';
          $(callback).removeProp('checked');
        }
        callback.value = 'callback=' + this.value;
      };
       
      function showFormatOptions() {
        
        if (lastActive && lastActive.hasClass('active')) { 
          lastActive.removeClass('active'); 
          optionsHeading.removeClass('active');
        };
        api.options = '';
        var formatOptions = $('.js-options-'+ api.format);
        if(formatOptions.length > 0) { 
          optionsHeading.addClass('active');
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
          if(api.format == 'html' && (currentOptions.indexOf('text') > -1) && (currentOptions.indexOf('icon') > -1)) {
              currentOptions.splice(currentOptions.indexOf('text'),1);
              currentOptions.splice(currentOptions.indexOf('icon'), 1);
              console.log(currentOptions);
          }
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

      features.focus();
      api.features = '';
      api.format = $('input[name="format"][checked]')[0].value; 
      showFormatOptions();
      refreshOutput();

      $('.more-info').click(function(e) {
        $(this.hash).toggleClass('active');
        e.preventDefault();
      });


