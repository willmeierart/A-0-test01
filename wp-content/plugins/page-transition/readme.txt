=== Page Transition ===
Contributors: numixtech, gauravpadia, asalamwp
Donate link: http://numixtech.com/
Tags:  animate, animations, css3, effects, fade, flip, jquery, page transitions, page transition, rotate, smooth, transition, ui, zoom, animation, transitions, page animations, page animation, wordpress animations, wordpress transitions, css, css3, html5, css animation, fade up, fade in, fade down, zoom, zoom animations, rotate animations, plugins, fade left, fade right, flip page, smooth animations, wordpress site animations, website animations, website transitions ,page effects, social, content animations, content, slider, theme animation, theme, theme transitions, flash animation, flash, flash transitions, post, page, post animations, jquery animations, button, button animations, code animation
Requires at least: 3.6
Tested up to: 4.0
Stable tag: 1.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Page Transition is a simple and easy wordpress plugin used to add page transition using CSS3 animations. Show your page with modern animations.

== Description ==

Page Transition is a simple and easy wordpress plugin used to add page transition using CSS3 animations. Show your page with modern animations.

Set different animations effects for page in (on load) and page out (on unload). You can also set value to "None" to disable page in or page out animation.

Available options and features:

* Set Page In Animation
* Set Page Out Animation
* Set Page In Animation Duration
* Set Page Out Animation Duration
* Set whether to show loading or not
* Set loading text color

= 9 different transition effects available =
* Fade
* Fade Down
* Fade Up
* Fade Left
* Fade Right
* Rotate
* Flip X
* Flip Y
* Zoom

This plugin is using jquery animsition plugin by Bilvesta. Please check demo at <a href="http://git.blivesta.com/animsition/" target="_blank">http://git.blivesta.com/animsition/</a>

== Installation ==

**Installation Instruction & Configuration**  	

1. You can use the built-in installer. OR
Download the zip file and extract the contents. Upload the 'page-transition' folder to your plugins directory (wp-content/plugins/).

2.Activate the plugin through the 'Plugins' menu in WordPress. 	

3.Go to Settings -> Page Transition and set your desired transition effect for page in and page out and animation duration.

== Frequently Asked Questions ==

= I have set transition effects but I can't see effects in front-end ? =

This plugin requires that you call wordpress "body_class" function like `<?php body_class(); ?>` in your body tag in header. Your body tag should be look like `<body <?php body_class(); ?>>`.

== Screenshots ==

1. Admin settings/options screenshot

== Changelog ==

= 1.3 =
* Excluded mailto email address links
* Exclude page out animation on particular link by apppying "no-animation" class to link "a" tag

= 1.2 =
* Added Loading Text color option
* Minor bug fixes

= 1.1 =
* Updated jquery animsition plugin to latest 3.2.1 version
* Added new options for setting adnimation duration and whether to show loading or not.
* Removed modernizr javascript as animsition version 3.2.1 supports checking browser css3 animation support inbuilt
* Added Rate us on wordpress link
 
= 1.0 =
* Initial version.