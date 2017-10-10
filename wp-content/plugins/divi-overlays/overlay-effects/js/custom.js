jQuery( function ( $ ) {
	
	if ( $('div.overlay-container').length ) {
		
		// Find all iframes inside the overlays
		var $iframes = $( "#sidebar-overlay .overlay iframe" );

		// Find &#x26; save the aspect ratio for all iframes
		$iframes.each(function () {
		  $( this ).data( "ratio", this.height / this.width )
			// Remove the hardcoded width &#x26; height attributes
			.removeAttr( "width" )
			.removeAttr( "height" );
		});

		// Resize the iframes when the window is resized
		$( window ).resize( function () {
		  $iframes.each( function() {
			// Get the parent container&#x27;s width
			var width = $( this ).parent().width();
			$( this ).width( width )
			  .height( width * $( this ).data( "ratio" ) );
		  });
		// Resize to fix all iframes on page load.
		}).resize();
		
		$(document).keyup(function(e) {
			
			if (e.keyCode == 27) {
				
				closeActiveOverlay();
			}
		});
		
		$(window).load(function() {
			
			if ( window.location.hash ) {
				
				var hash = window.location.hash.substring( 1 );
				var idx_overlay = hash.indexOf('overlay'); 
				
				if ( idx_overlay !== -1 ) {
					
					var idx_overlayArr = hash.split('-');
					
					if ( idx_overlayArr.length > 1 ) {
						
						var overlay_id = idx_overlayArr[1];
						
						showOverlay( overlay_id );
					}
				}
			}
		});
		
		var overlay_container = $( 'div.overlay-container' );
		var container = $( 'div#page-container' );
		
		// Remove any duplicated overlay
		$( overlay_container ).each(function () {
			$('[id="' + this.id + '"]:gt(0)').remove();
		});
		
		$('body [id^="overlay_"]').on('click', function () {
			
			var overlayArr = $(this).attr('id').split('_');
			var overlay_id = overlayArr[3];
			
			showOverlay( overlay_id );
		});
		
		$('body [rel^="unique_overlay_"]').on('click', function () {
			
			var overlayArr = $(this).attr('rel').split('_');
			var overlay_id = overlayArr[4];
			
			showOverlay( overlay_id );
		});
		
		$('body [class*="divioverlay-"], body [class*="overlay-"]').on('click', function () {
			
			var overlayArr = $(this).attr('class').split(' ');
			
			$( overlayArr ).each(function( index,value ) {
				
				idx_overlay = value.indexOf('overlay');
				
				if ( idx_overlay !== -1 ) {
					
					var idx_overlayArr = value.split('-');
					
					if ( idx_overlayArr.length > 1 ) {
						
						var overlay_id = idx_overlayArr[1];
						
						showOverlay( overlay_id );
					}
				}
			});
		});
		
		if (typeof overlays_with_css_trigger !== 'undefined') {
			
			if ( $( overlays_with_css_trigger ).length > 0 ) {
				
				$.each( overlays_with_css_trigger, function( overlay_id, selector ) {
					
					$( selector ).on('click', function () {
						
						showOverlay( overlay_id );
					});
				});
			}
		}
		
		if (typeof overlays_with_automatic_trigger !== 'undefined') {
			
			if ( $( overlays_with_automatic_trigger ).length > 0 ) {
				
				$.each( overlays_with_automatic_trigger, function( overlay_id, at_settings ) {
					
					var at_settings_parsed = jQuery.parseJSON( at_settings );
					var at_type_value = at_settings_parsed.at_type;
					var at_onceperload = at_settings_parsed.at_onceperload;
					
					if ( at_onceperload == 1 ) {
						
						showOverlayOnce( overlay_id );
					}
					
					if ( at_type_value == 'overlay-timed' ) {
						
						var time_delayed = at_settings_parsed.at_value * 1000;
						
						setTimeout( function() {
							
							showOverlay( overlay_id );
							
						}, time_delayed);
					}
					
					
					if ( at_type_value == 'overlay-scroll' ) {
						
						var overlayScroll = at_settings_parsed.at_value, refScroll;
						
						if ( overlayScroll.indexOf('%') || overlayScroll.indexOf('px') ) {
							
							if ( overlayScroll.indexOf('%') ) {
								
								overlayScroll = overlayScroll.replace(/%/g, '');
								refScroll = '%';
							}
							
							if ( overlayScroll.indexOf('px') ) {
								
								overlayScroll = overlayScroll.replace(/px/g, '');
								refScroll = 'px';
							}
							
							overlayScroll = overlayScroll.split(':');
							var overlayScrollFrom = overlayScroll[0];
							var overlayScrollTo = overlayScroll[1];
							
							$(window).scroll(function(e) {
							
								var s = getScrollTop(),
									d = $(document).height(),
									c = $(window).height();
								
								if ( refScroll == '%' ) {
									
									wScroll = (s / (d-c)) * 100;
								
								} else if ( refScroll == 'px' ) {
									
									wScroll = s;
									
								} else {
									
									return;
								}
								
								if ( overlayScrollFrom > 0 && overlayScrollTo > 0 ) {
									
									if ( overlayScrollFrom <= wScroll && overlayScrollTo >= wScroll ) {
										
										if ( !isActiveOverlay( overlay_id ) ) {
											
											showOverlay( overlay_id );
										}
									}
									else if ( isActiveOverlay( overlay_id ) ) {
										
										closeActiveOverlay( overlay_id );
									}
								}
								
								if ( overlayScrollFrom > 0 && overlayScrollTo == '' ) {
									
									if ( overlayScrollFrom <= wScroll ) {
										
										if ( !isActiveOverlay( overlay_id ) ) {
											
											showOverlay( overlay_id );
										}
									}
									else if ( isActiveOverlay( overlay_id ) ) {
										
										closeActiveOverlay( overlay_id );
									}
								}
								
								if ( overlayScrollFrom == '' && overlayScrollTo > 0 ) {
									
									if ( overlayScrollTo >= wScroll ) {
										
										if ( !isActiveOverlay( overlay_id ) ) {
											
											showOverlay( overlay_id );
										}
									}
									else if ( isActiveOverlay( overlay_id ) ) {
									
										closeActiveOverlay( overlay_id );
									}
								}
							});
						}
					}
					
					
					if ( at_type_value == 'overlay-exit' ) {
						
						$.exitIntent('enable', { 'sensitivity': 100 });
						
						$(document).bind('exitintent',
							function() {
								
								if ( !isActiveOverlay( overlay_id ) ) {
									
									showOverlay( overlay_id );
								}
							});
					}
				});
			}
		}
		
		
		$('.nav a, .mobile_nav a').each(function( index,value ) {
			
			href = $( value ).attr('href');
			
			if ( href !== undefined ) {
			
				idx_overlay = href.indexOf('overlay');
				
				if ( idx_overlay !== -1 ) {
					
					var idx_overlayArr = href.split('-');
					
					if ( idx_overlayArr.length > 1 ) {
						
						var overlay_id = idx_overlayArr[1];
						
						$(this).attr('data-overlayid', overlay_id);
						
						$(this).on('click', function () {
							
							overlay_id = $(this).data('overlayid');
							
							showOverlay( overlay_id );
						});
					}
				}
			}
		});
		
		transEndEventNames = {
			'WebkitTransition': 'webkitTransitionEnd',
			'MozTransition': 'transitionend',
			'OTransition': 'oTransitionEnd',
			'msTransition': 'MSTransitionEnd',
			'transition': 'transitionend'
		},
		transEndEventName = transEndEventNames[ Modernizr.prefixed( 'transition' ) ],
		support = { transitions : Modernizr.csstransitions };
		
		function shuffle(array) {
			var currentIndex = array.length
			, temporaryValue
			, randomIndex
			;

			// While there remain elements to shuffle...
			while (0 !== currentIndex) {
				// Pick a remaining element...
				randomIndex = Math.floor(Math.random() * currentIndex);
				currentIndex -= 1;
				// And swap it with the current element.
				temporaryValue = array[currentIndex];
				array[currentIndex] = array[randomIndex];
				array[randomIndex] = temporaryValue;
			}
			return array;
		}
		
		function toggleOverlay( overlay_id ) {
			
			var overlay_selector = '#overlay-' + overlay_id;
			var overlay_cache_selector = '#overlay-' + overlay_id;
			var overlay_container = '#divi-overlay-container-' + overlay_id;
			var overlay = $('body').find( overlay_cache_selector );
			var oid = overlay.attr('id');
			var prevent_mainpage_scroll = overlay.data('preventscroll');
			var displayonceperload = overlay.data('displayonceperload');
			var overlay_active_selector = 'div.overlay-container div.overlay-body';
			var preventOpen = overlay.attr('data-preventopen');
			
			if ( $( overlay_cache_selector ).hasClass("overlay-cornershape") ) {
				
				var s = Snap( document.querySelector( overlay_cache_selector ).querySelector( 'svg' ) );
				var original_s = Snap( document.querySelector( overlay_selector ).querySelector( 'svg' ) );
				
				var path = s.select( 'path' );
				var original_path = original_s.select( 'path' );
				
				var pathConfig = {
					from : original_path.attr( 'd' ),
					to : document.querySelector( overlay_cache_selector ).getAttribute( 'data-path-to' )
				};
			}
			
			if ( $( overlay_cache_selector ).hasClass("overlay-boxes") ) {
				
				paths = [].slice.call( document.querySelector( overlay_cache_selector ).querySelectorAll( 'svg > path' ) ),
				pathsTotal = paths.length;
			}
			
			if ( $( overlay_cache_selector ).hasClass("overlay-genie") ) {
				
				var s1 = Snap( document.querySelector( overlay_cache_selector ).querySelector( 'svg' ) ), 
				path1 = s1.select( 'path' ),
				steps = document.querySelector( overlay_cache_selector ).getAttribute( 'data-steps' ).split(';'),
				stepsTotal = steps.length;
			}
			
			if ( $( overlay_cache_selector ).hasClass('overlay-boxes') ) {
				
				var cnt = 0;
				
				shuffle( paths );
			}
			
			if ( $( overlay ).hasClass('open') ) {
				
				$( overlay ).removeClass('open');
				$( overlay ).addClass('close');
				
				if ( $( overlay_cache_selector ).hasClass('overlay-hugeinc') 
					|| $( overlay_cache_selector ).hasClass('overlay-corner') 
					|| $( overlay_cache_selector ).hasClass('overlay-scale') ) {
						
					$( overlay_cache_selector ).css('opacity',0);
				}
				
				if ( $( overlay_cache_selector ).hasClass('overlay-contentpush') ) {
					
					$( container ).removeClass('overlay-contentpush-open');
					
					setTimeout( function() { 
					
						$( container ).removeClass( 'container2' );
						$("html,body").removeAttr('style'); 
						
					}, 1000);
				}
				
				if ( $( overlay_cache_selector).hasClass('overlay-contentscale') ) {
					
					$( container ).removeClass('overlay-contentscale-open');
					
					setTimeout( function() {
						
						$( container ).removeClass( 'container3' );
						
					}, 1000);
				}
				
				if ( $( overlay_cache_selector ).hasClass('overlay-cornershape') ) {
					
					var onEndTransitionFn = function( ev ) {
						
						$( overlay ).removeClass( 'close' );
					};
					path.animate( { 'path' : pathConfig.from }, 400, mina.linear, onEndTransitionFn );
				}
				else if ( $( overlay_cache_selector ).hasClass('overlay-boxes') ) {
					
					paths.forEach( function( p, i ) {
						setTimeout( function() {
							++cnt;
							p.style.display = 'none';
							if( cnt === pathsTotal ) {
								
								$( overlay ).removeClass( 'close' );
							}
						}, i * 30 );
					});
				}
				else if ( $(  overlay_cache_selector ).hasClass('overlay-genie') ) {
					
					var pos = stepsTotal-1;
					var onEndTransitionFn = function( ev ) {
						
						$( overlay ).removeClass( 'close' );
					},
					nextStep = function( pos ) {
						pos--;
						if( pos < 0 ) return;
						path1.animate( { 'path' : steps[pos] }, 60, mina.linear, function() { 
							if( pos === 0 ) {
								onEndTransitionFn();
							}
							nextStep( pos );
						} );
					};

					nextStep( pos );
				}
				else {
					
					overlay = document.querySelector( overlay_cache_selector );
					
					var onEndTransitionFn = function( ev ) {
						if( support.transitions ) {
							if( ev.propertyName !== 'visibility' ) return;
							this.removeEventListener( transEndEventName, onEndTransitionFn );
						}
						
						$( overlay ).removeClass( 'close' );
					};
					
					if ( support.transitions ) {
						
						overlay.addEventListener( transEndEventName, onEndTransitionFn );
					}
					else {
						
						onEndTransitionFn();
					}
				}
				
				if ( prevent_mainpage_scroll ) {
					
					$( 'html,body' ).removeClass('prevent_mainpage_scroll');
					$( 'body' ).removeClass('prevent_mainpage_scroll_mobile');
					$( '#page-container' ).removeClass('prevent_content_scroll');
					$('html, body').scrollTop( $( overlay ).attr('data-scrolltop') );
					$( overlay ).attr('data-scrolltop', '' );
				}
				
				setTimeout( function() {
					
					$( overlay_cache_selector ).removeAttr('style');
					$( overlay_cache_selector + ' path' ).removeAttr('style');
					
					if ( !isActiveOverlay() ) {
						
						$( "#page-container .container" ).css('z-index','1');
						$( "#page-container #main-header" ).css('z-index','99989');
						$( "#sidebar-overlay" ).css('z-index','-15');
					}
					
					if ( displayonceperload ) {
						
						$( overlay_container ).remove();
					}
					else {
						
						togglePlayableTags( '#overlay-' + overlay_id );
					}
					
				}, 500);
			}
			else if( !$( overlay ).hasClass('close') && !preventOpen ) {
				
				if ( displayonceperload ) {
					
					overlay.attr('data-preventopen', 1);
				}
				
				overlay.attr('data-scrolltop', getScrollTop() );
				
				$( "#page-container .container" ).css('z-index','0');
				$( "#page-container #main-header" ).css('z-index','-1');
				$( "#sidebar-overlay" ).css('z-index','16777271');
				
				setTimeout( function() {
					
					$( overlay ).addClass('open');
					
					if ( overlay.attr('data-bgcolor') != "") {
						$( overlay_cache_selector ).css( { 'background-color': overlay.attr('data-bgcolor') } );
					}
					
					if ( overlay.attr('data-fontcolor') != "") {
						$( overlay_cache_selector ).css( 'color', overlay.attr('data-fontcolor') );
					}
					
					if ( $( overlay_cache_selector ).hasClass('overlay-contentpush') ) {
						
						$( "html,body" ).css('overflow-x','hidden');
						
						$( overlay_cache_selector ).css('opacity',1);
						
						container.attr('class', 'container2');
						
						$( container ).addClass( 'overlay-contentpush-open' );
					}
					
					if ( $( overlay_cache_selector ).hasClass('overlay-contentscale')) {
						
						container.attr('class', 'container3');
						
						$( container ).addClass('overlay-contentscale-open');
					}
					
					if ( $( overlay_cache_selector ).hasClass('overlay-cornershape')) {
						
						$( overlay_cache_selector ).css({"background":"transparent none repeat scroll 0 0"});
						
						path.animate( { 'path' : pathConfig.to }, 400, mina.linear );
						$( overlay_cache_selector + ' .overlay-path' ).css({"fill": overlay.attr('data-bgcolor')});
					}
					
					if ( $(  overlay_cache_selector ).hasClass('overlay-boxes') ) {
						
						$( overlay_cache_selector ).css({"background":"transparent none repeat scroll 0 0"});
						paths.forEach( function( p, i ) {
							setTimeout( function() {
								p.style.display = 'block';
								p.style.fill = overlay.attr('data-bgcolor');
							}, i * 30 );
						});
					}
					
					if ( $( overlay_cache_selector ).hasClass('overlay-genie') ) {
						
						$( overlay_cache_selector ).css({"background":"transparent none repeat scroll 0 0"});
						
						var pos = 0;
						
						$( overlay ).addClass( 'open' );
						
						var nextStep = function( pos ) {
							pos++;
							if( pos > stepsTotal - 1 ) return;
							path1.animate( { 'path' : steps[pos] }, 60, mina.linear, function() { nextStep(pos); } );
							
							$( overlay_cache_selector + ' .overlay-path' ).css({"fill": overlay.attr('data-bgcolor')});
						};
						
						nextStep(pos);
					}
					
					if ( prevent_mainpage_scroll ) {
						
						$( 'html,body' ).addClass('prevent_mainpage_scroll');
						$( 'body' ).addClass('prevent_mainpage_scroll_mobile');
						$( '#page-container' ).addClass('prevent_content_scroll');
					}
					
				}, 200);
			}
		}
		
		
		function getScrollTop() {
			
			if ( typeof pageYOffset!= 'undefined' ) {
				
				// most browsers except IE before #9
				return pageYOffset;
			}
			else {
				
				var B = document.body; // IE 'quirks'
				var D = document.documentElement; // IE with doctype
				D = ( D.clientHeight ) ? D: B;
				
				return D.scrollTop;
			}
		}
		
		
		function showOverlay( overlay_id ) {
			
			if ( !isInt( overlay_id ) )
				return;
			
			var data = {
				action: 'divioverlay_content',
				overlay_id: overlay_id
			};
			
			divi_overlay_container_selector = '#divi-overlay-container-' + overlay_id;
			
			if ( $( divi_overlay_container_selector ).length ) {
			
				overlay_body = $( divi_overlay_container_selector ).find( '.overlay' );
				
				toggleSrcInPlayableTags( overlay_body );
				
				toggleOverlay( overlay_id );
			}
		}
		
		function showOverlayOnce( overlay_id ) {
			
			if ( !isInt( overlay_id ) )
				return;
			
			overlay = '#overlay-' + overlay_id;
			
			$( overlay ).attr( 'data-displayonceperload', 1 );
		}
		
		function toggleSrcInPlayableTags( str ) {
			
			str.find("iframe").each(function() { 
				var src = $(this).data('src');
				$(this).attr('src', src);  
			});
			
			return str;
		}
		
		$('body').on('click', '.overlay.open, .overlay-close, .overlay-close span, .close-divi-overlay', function(e) {
			
			if ( e.target !== e.currentTarget ) return;
			
			closeActiveOverlay();
		});
		
		function closeActiveOverlay( overlay_id ) {
			
			// find active overlay
			var overlay = $( 'body' ).find( '.overlay.open' );
			var displayonceperload = overlay.data('displayonceperload');
			
			if ( overlay.length ) {
				
				if ( overlay_id == null ) {
					
					var overlayArr = overlay.attr('id').split('-');
					overlay_id = overlayArr[1];
				}
				
				showOverlay( overlay_id );
			}
		}
		
		function getActiveOverlay( onlyNumber ) {
			
			// find active overlay
			overlay = $( 'body' ).find( '.overlay.active' );
			overlay_id = null;
			
			if ( overlay.length ) {
				
				var overlayArr = overlay.attr('id').split('-');
				var overlay_id = overlayArr[1];
			}
			
			return overlay_id;
		}
		
		function isOpeningOverlay( overlay_id ) {
			
			if ( !overlay_id ) {
				
				return null;
			}
			
			var overlay = $( '#overlay-' + overlay_id );
			
			if ( $( overlay ).css('opacity') < 1 ) {
				
				return true;
			}
			
			return false;
		}
		
		function isClosingOverlay( overlay_id ) {
			
			if ( !overlay_id ) {
				
				return null;
			}
			
			var overlay = $( '#overlay-' + overlay_id );
			
			if ( $( overlay ).hasClass('close') ) {
				
				return false;
			}
			
			return true;
		}
		
		function isActiveOverlay( overlay_id ) {
			
			if ( !overlay_id ) {
				
				var overlay = $( '.overlay.open' );
			}
			else {
				
				var overlay = $( '#overlay-' + overlay_id );
			}
			
			if ( $( overlay ).hasClass('open') ) {
				
				return true;
			}
			
			return false;
		}
		
		
		function togglePlayableTags( overlay_id ) {
			
			if ( !overlay_id  ) {
				
				overlay_id = '';
			}
			
			/* Prevent playable tags load content before overlay call */
			$( overlay_id + '.overlay').find("iframe").each(function() { 
				var src = $(this).attr('src');
				$(this).attr('data-src', src);
				$(this).attr('src', 'about:blank');  
			});
			
			$( overlay_id + '.overlay').find("video").each(function() {
				$(this).get(0).pause();
			});
			
			$( overlay_id + '.overlay').find('audio').each(function() {
				
				this.pause();
				this.currentTime = 0;
			});
		}
		
		togglePlayableTags();
	}
});


function isInt(value) {
    var x;
    return isNaN(value) ? !1 : (x = parseFloat(value), (0 | x) === x);
}