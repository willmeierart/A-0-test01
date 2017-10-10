<?php
/**
 * Page Transition main plugin file
 *
 * @package   Page_Transition
 * @author    Numix Technologies <numixtech@gmail.com>
 * @author    Gaurav Padia <gauravpadia14u@gmail.com>
 * @author    Asalam Godhaviya <godhaviya.asalam@gmail.com>
 * @license   GPL-2.0+
 * @link      http://numixtech.com
 * @copyright 2014 Numix Techonologies
 *
 * @wordpress-plugin
 * Plugin Name: 	Page Transition
 * Plugin URI: 		http://numixtech.com
 * Description: 	Page Transition is a simple and easy wordpress plugin used to add page transition on CSS3 animations. Show your page with modern animations.
 * Version: 1.3
 * Author: 			Numix Technologies, Gaurav Padia, Asalam Godhaviya
 * Author URI: 		http://numixtech.com
 * Text Domain: 	page-transition
 * License: 		GPL-2.0+
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'class-page-transition.php';

add_action( 'plugins_loaded', array( 'Page_Transition', 'get_instance' ) );