<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://campuspress.com
 * @since      1.0.0
 *
 * @package    Divi_Accessibility
 * @subpackage Divi_Accessibility/public/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( $this->can_load( 'dropdown_keyboard_navigation' ) ) {

?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			var hoverClasses = 'et-hover et-show-dropdown';
			var currentListItem = '';

			/**
			 * Update top navigation classes.
			 */
			function update_navigation_classes(el) {
				var currentLink = el.find('a:focus');
				currentListItem = currentLink.closest('li');

				// check if focused on top level nav item
				if (el.is(currentListItem.closest('ul')) || el.find('a:focus').length === 0) {
					el.find('li').removeClass(hoverClasses);
				}

				// add appropriate divi hover classes if nav item has children
				if ($(currentListItem).children('ul').length) {
					currentListItem.addClass(hoverClasses);
				}
			}

			/**
			 * Generate search form styles.
			 *
			 * @since Divi v3.0.23
			 */
			function et_set_search_form_css() {
				var search_container = $('.et_search_form_container');
				var body = $('body');

				if (search_container.hasClass('et_pb_search_visible')) {
					var header_height = $('#main-header').innerHeight();
					var menu_width = $('#top-menu').width();
					var font_size = $('#top-menu li a').css('font-size');

					search_container.css({ height: header_height + 'px' });
					search_container.find('input').css('font-size', font_size);

					if (!body.hasClass('et_header_style_left')) {
						search_container.css('max-width', menu_width + 60);
					} else {
						search_container.find('form').css('max-width', menu_width + 60);
					}
				}
			}

			/**
			 * Show the search.
			 *
			 * @since Divi v3.0.23
			 */
			function show_search() {
				var search_container = $('.et_search_form_container');

				if (search_container.hasClass('et_pb_is_animating')) {
					return;
				}

				$('.et_menu_container').removeClass('et_pb_menu_visible et_pb_no_animation').addClass('et_pb_menu_hidden');
				search_container.removeClass('et_pb_search_form_hidden et_pb_no_animation').addClass('et_pb_search_visible et_pb_is_animating');

				setTimeout(function () {
					$('.et_menu_container').addClass('et_pb_no_animation');
					search_container.addClass('et_pb_no_animation').removeClass('et_pb_is_animating');
				}, 1000);

				search_container.find('input').focus();

				et_set_search_form_css();
			}

			/**
			 * Hide the search.
			 *
			 * @since Divi v3.0.23
			 */
			function hide_search() {
				if ($('.et_search_form_container').hasClass('et_pb_is_animating')) {
					return;
				}

				$('.et_menu_container').removeClass('et_pb_menu_hidden et_pb_no_animation').addClass('et_pb_menu_visible');
				$('.et_search_form_container').removeClass('et_pb_search_visible et_pb_no_animation').addClass('et_pb_search_form_hidden et_pb_is_animating');

				setTimeout(function () {
					$('.et_menu_container').addClass('et_pb_no_animation');
					$('.et_search_form_container').addClass('et_pb_no_animation').removeClass('et_pb_is_animating');
				}, 1000);
			}

			$(this).keyup(function () {

				var nav = $('.nav');
				var menu = $('.menu');

				update_navigation_classes(nav);
				update_navigation_classes(menu);

				$('.et-search-field').focus(function () {
					show_search();
				}).blur(function () {
					hide_search();
				});
			});
		});
	})(jQuery);
	</script>

<?php

} // End if().

if ( $this->can_load( 'skip_navigation_link' ) ) {

?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			/**
			 * Add skiplink to page.
			 */
			function skipTo(target) {
				var skiplink = '<a href="' + target + '" class="skip-link da11y-screen-reader-text">Skip to content</a>';

				$(target).attr('tabindex', -1);

				$('body').prepend(skiplink);
			}
			skipTo('#main-content');

			/**
			 * Use js to focus for internal links.
			 */
			$('a[href^="#"]').click(function () {
				var content = $('#' + $(this).attr('href').slice(1));

				content.focus();
			});

		});
	})(jQuery);
	</script>

<?php

} // End if().

if ( $this->can_load( 'keyboard_navigation_outline' ) ) {

?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			var lastKey = new Date();
			var lastClick = new Date();

			/**
			 * Only apply focus styles for keyboard usage.
			 */
			$(this).on('focusin', function (e) {
				$('.keyboard-outline').removeClass('keyboard-outline');

				var wasByKeyboard = lastClick < lastKey;

				if (wasByKeyboard) {
					$(e.target).addClass('keyboard-outline');
				}
			});
			$(this).on('mousedown', function () {
				lastClick = new Date();
			});
			$(this).on('keydown', function () {
				lastKey = new Date();
			});
		});
	})(jQuery);
	</script>

<?php

} // End if().

if ( $this->can_load( 'focusable_modules' ) ) { ?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			/**
			 * Allow Accordion & Toggle Divi modules to be focusable.
			 *
			 * @divi-module  Accordion, Toggle
			 */
			$('.et_pb_toggle').each(function () {
				$(this).attr('tabindex', 0);
			});

			$(document).keyup(function (e) {
				// Enter.
				if (e.which === 13) {
					// Expand Accordion & Toggle modules when enter is hit while focused.
					$('.et_pb_toggle:focus .et_pb_toggle_title').trigger('click');
				}
			});
		});
	})(jQuery);
	</script>

<?php

} // End if().

if ( $this->can_load( 'fix_labels' ) ) {

?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			/**
			 * Add unique ID to search module input with matching label.
			 *
			 * @divi-module  Search
			 */
			$('.et_pb_search').each(function (e) {
				$(this).find('label').attr('for', 'et_pb_search_module_input_' + e);
				$(this).find('input.et_pb_s').attr('id', 'et_pb_search_module_input_' + e);
			});

			/**
			 * Add unique ID to search module input with matching label.
			 *
			 * @divi-module  Contact
			 */
			$('.et_pb_contact_form').each(function (e) {
				var captchaQuestion = $(this).find('.et_pb_contact_captcha_question');
				$(this).find('input.et_pb_contact_captcha').attr('id', 'et_pb_contact_module_captcha_' + e);

				captchaQuestion.wrap('<label for="et_pb_contact_module_captcha_' + e + '"></label>');
			});
		});
	})(jQuery);
	</script>

<?php
}

if ( $this->can_load( 'aria_support' ) ) {

?>

	<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {

			/**
			 * Add role="tabList".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls').each(function () {
				$(this).attr('role', 'tabList');
			});

			/**
			 * Add role="presentation".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls li').each(function () {
				$(this).attr('role', 'presentation');
			});

			/**
			 * Add role="tab".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls a').each(function () {
				$(this).attr('role', 'tab');
			});

			/**
			 * Add role="tabpanel".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tab').each(function () {
				$(this).attr('role', 'tabpanel');
			});

			/**
			 * Add inital state:
			 *
			 * aria-selected="false"
			 * aria-expanded="false"
			 * tabindex=-1
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls li:not(.et_pb_tab_active) a').each(function () {
				$(this).attr('aria-selected', 'false');
				$(this).attr('aria-expanded', 'false');
				$(this).attr('tabindex', -1);
			});

			/**
			* Add inital state:
			*
			* aria-selected="true"
			* aria-expanded="true"
			* tabindex=-1
			*
			* @divi-module  Tab
			 */
			$('.et_pb_tabs_controls li.et_pb_tab_active a').each(function () {
				$(this).attr('aria-selected', 'true');
				$(this).attr('aria-expanded', 'true');
				$(this).attr('tabindex', 0);
			});

			/**
			 * Add unique ID to tab controls.
			 * Add aria-controls="x".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls a').each(function (e) {
				$(this).attr('id', 'et_pb_tab_control_' + e);
				$(this).attr('aria-controls', 'et_pb_tab_panel_' + e);
			});

			/**
			 * Add unique ID to tab panels.
			 * Add aria-labelledby="x".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tab').each(function (e) {
				$(this).attr('id', 'et_pb_tab_panel_' + e);
				$(this).attr('aria-labelledby', 'et_pb_tab_control_' + e);
			});

			/**
			 * Set initial inactive tab panels to aria-hidden="false".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tab.et_pb_active_content').each(function () {
				$(this).attr('aria-hidden', 'false');
			});

			/**
			 * Set initial inactive tab panels to aria-hidden="true".
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tab:not(.et_pb_active_content)').each(function () {
				$(this).attr('aria-hidden', 'true');
			});

			/**
			 * Add unique ID to tab module.
			 * Need to use data attribute because a regular ID somehow interferes with Divi.
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs').each(function (e) {
				$(this).attr('data-da11y-id', 'et_pb_tab_module_' + e);
			});

			/**
			 * Update aria-selected attribute when tab is clicked or when hitting enter while focused.
			 *
			 * @divi-module  Tab
			 */
			$('.et_pb_tabs_controls a').on('click', function () {
				var id = $(this).attr('id');
				var namespace = $(this).closest('.et_pb_tabs').attr('data-da11y-id'); // Used as a selector to scope changes to current module.

				// Reset all tab controls to be aria-selected="false" & aria-expanded="false".
				$('[data-da11y-id="' + namespace + '"] .et_pb_tabs_controls a')
					.attr('aria-selected', 'false')
					.attr('aria-expanded', 'false')
					.attr('tabindex', -1);

				// Make active tab control aria-selected="true" & aria-expanded="true".
				$(this)
					.attr('aria-selected', 'true')
					.attr('aria-expanded', 'true')
					.attr('tabindex', 0);

				// Reset all tabs to be aria-hidden="true".
				$('#' + namespace + ' .et_pb_tab')
					.attr('aria-hidden', 'true');

				// Label active tab panel as aria-hidden="false".
				$('[aria-labelledby="' + id + '"]')
					.attr('aria-hidden', 'false');
			});

			// Arrow navigation for tab modules
			$('.et_pb_tabs_controls a').keyup(function (e) {
				var namespace = $(this).closest('.et_pb_tabs').attr('data-da11y-id');
				var module = $('[data-da11y-id="' + namespace + '"]');

				if (e.which === 39 || e.which === 40) { // Down & Right.
					var next = module.find('li.et_pb_tab_active').next();

					if (next.length > 0) {
						next.find('a').trigger('click');
					} else {
						module.find('li:first a').trigger('click');
					}
				} else if (e.which === 37 || e.which === 38) { // Up & Left.
					var next = module.find('li.et_pb_tab_active').prev();

					if (next.length > 0) {
						next.find('a').trigger('click');
					} else {
						module.find('li:last a').trigger('click');
					}
				}

				$('.et_pb_tabs_controls a').removeClass('keyboard-outline');
				module.find('li.et_pb_tab_active a').addClass('keyboard-outline');
			});

			/**
			 * Add unique ID to search module.
			 * Need to use data attribute because a regular ID somehow interferes with Divi.
			 *
			 * @divi-module  Search
			 */
			$('.et_pb_search').each(function (e) {
				$(this).attr('data-da11y-id', 'et_pb_search_module_' + e);
			});

			/**
			 * Add aria-required="true" to inputs.
			 *
			 * @divi-module  Contact Form
			 */
			$('[data-required_mark="required"]').each(function () {
				$(this).attr('aria-required', 'true');
			});

			/**
			 * Add role="menubar" to top level unorderd lists.
			 */
			$('.nav').each(function () {
				$(this).attr('role', 'menubar');
			});

			/**
			 * Add role="menubar" to top level unorderd lists.
			 */
			$('.bottom-nav').each(function () {
				$(this).attr('role', 'menubar');
			});

			/**
			 * Add role="menubar" to top level unorderd lists.
			 */
			$('.menu').each(function () {
				$(this).attr('role', 'menubar');
			});

			/**
			 * Add role="menubar" to top level unorderd lists.
			 */
			$('.sub-menu').each(function () {
				$(this).attr('role', 'menu');
			});
		});
	})(jQuery);
	</script>

<?php

} // End if().
