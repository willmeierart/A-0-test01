<?php
/*
Plugin Name: Divi Overlays
Plugin URL: https://divilife.com/
Description: Create unlimited popup overlays using the Divi Builder.
Version: 2.0
Author: Divi Life — Tim Strifler
Author URI: https://divilife.com
*/

// Register the Custom Dive Overlays Post Type
define( 'OVERLAY_PLUGIN_DIR', dirname( __FILE__ ) );
function register_cpt_divi_overlay() {
 
    $labels = array(
        'name' => _x( 'Overlays', 'divi_overlay' ),
        'singular_name' => _x( 'Overlay', 'divi_overlay' ),
        'add_new' => _x( 'Add New', 'divi_overlay' ),
        'add_new_item' => _x( 'Add New Overlay', 'divi_overlay' ),
        'edit_item' => _x( 'Edit Overlay', 'divi_overlay' ),
        'new_item' => _x( 'New Overlay', 'divi_overlay' ),
        'view_item' => _x( 'View Overlay', 'divi_overlay' ),
        'search_items' => _x( 'Search Overlay', 'divi_overlay' ),
        'not_found' => _x( 'No Overlays found', 'divi_overlay' ),
        'not_found_in_trash' => _x( 'No overlays found in Trash', 'divi_overlay' ),
        'parent_item_colon' => _x( 'Parent Overlay:', 'divi_overlay' ),
        'menu_name' => _x( 'Overlays', 'divi_overlay' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        //'description' => 'Divi Overlay Description',
        'supports' => array( 'title', 'editor', 'author' ),
        //'taxonomies' => array( 'genres' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        //'menu_icon' => 'dashicons-format-audio',
        'show_in_nav_menus' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type( 'divi_overlay', $args );
}
 
add_action( 'init', 'register_cpt_divi_overlay' );

/* Add custom column in post type */
add_filter( 'manage_edit-divi_overlay_columns', 'my_edit_divi_overlay_columns' ) ;

function my_edit_divi_overlay_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Title' ),
		'unique_indentifier' => __( 'CSS ID' ),
		'unique_menu_id' => __( 'Menu ID' ),
		'author' => __( 'Author' ),
		'date' => __( 'Date' )
	);

	return $columns;
}
add_action( 'manage_divi_overlay_posts_custom_column', 'my_manage_divi_overlay_columns', 10, 2 );


function my_manage_divi_overlay_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {

		/* If displaying the 'unique-indentifier' column. */
		case 'unique_indentifier' :

			/* Get the post meta. */
			$post_slug = "overlay_unique_id_$post->ID";

			echo $post_slug;

			break;

		case 'unique_menu_id' :

			/* Get the post meta. */
			$post_slug = "unique_overlay_menu_id_$post->ID";

			echo $post_slug;

			break;
		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}
/* Custom column End here */


// Add Divi Theme Builder
add_filter('et_builder_post_types','divi_overlays_enable_builder');

function divi_overlays_enable_builder($post_types){
	$post_types[] = 'divi_overlay';
	return $post_types;
}


// Meta boxes for Divi Overlay //
function et_add_divi_overlay_meta_box() {
	
	$screen = get_current_screen();
	
	if( 'add' != $screen->action ){
		add_meta_box( 'do_manualtriggers', esc_html__( 'Manual Triggers', 'Divi' ), 'do_manualtriggers_callback', 'divi_overlay', 'side', 'high' );
	}
	
	add_meta_box( 'et_overlay_color_picker', 'Overlay Background', 'overlay_color_box_callback', 'divi_overlay');
	add_meta_box( 'et_aniamtion_meta_box', esc_html__( 'Divi Overlay Animation', 'Divi' ), 'et_single_animation_meta_box', 'divi_overlay', 'side', 'high' );
	add_meta_box( 'do_moresettings_meta_box', esc_html__( 'Additional Overlay Settings', 'Divi' ), 'do_moresettings_callback', 'divi_overlay', 'side' );
	add_meta_box( 'do_closecustoms_meta_box', esc_html__( 'Close Button Customizations', 'Divi' ), 'do_closecustoms_callback', 'divi_overlay', 'side' );
	add_meta_box( 'do_automatictriggers', esc_html__( 'Automatic Triggers', 'Divi' ), 'do_automatictriggers_callback', 'divi_overlay', 'side' );
}
add_action( 'add_meta_boxes', 'et_add_divi_overlay_meta_box' );

if ( ! function_exists( 'do_manualtriggers_callback' ) ) :

	function do_manualtriggers_callback( $post ) {
		?>
		<div class="custom_meta_box">
			<p>
				<label class="label-color-field"><p>CSS ID:</label>
				overlay_unique_id_<?php print $post->ID ?></p>
			</p>
			<div class="clear"></div> 
		</div> 
		<div class="custom_meta_box">
			<p>
				<label class="label-color-field"><p>Menu ID:</label>
				unique_overlay_menu_id_<?php print $post->ID ?></p>
			</p>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;


if ( ! function_exists( 'do_closecustoms_callback' ) ) :

	function do_closecustoms_callback( $post ) {
		
		wp_nonce_field( 'do_closecustoms', 'do_closecustoms_nonce' );
		
		$textcolor = get_post_meta( $post->ID, 'post_closebtn_text_color', true );
		$bgcolor = get_post_meta( $post->ID, 'post_closebtn_bg_color', true );
		$fontsize = get_post_meta( $post->ID, 'post_closebtn_fontsize', true );
		$borderradius = get_post_meta( $post->ID, 'post_closebtn_borderradius', true );
		$padding = get_post_meta( $post->ID, 'post_closebtn_padding', true );
		
		if( !isset( $fontsize ) ) {
			
			$fontsize = 25;
		}
		
		$hideclosebtn = get_post_meta( $post->ID, 'post_do_hideclosebtn' );
		if( !isset( $hideclosebtn[0] ) ) {
			
			$hideclosebtn[0] = '0';
		}
		
		$customizeclosebtn = get_post_meta( $post->ID, 'post_do_customizeclosebtn' );
		if( !isset( $customizeclosebtn[0] ) ) {
			
			$customizeclosebtn[0] = '0';
		}
		
		?>
		<div class="custom_meta_box">
			<p>
				<input name="post_do_hideclosebtn" type="checkbox" id="post_do_hideclosebtn" value="1" <?php checked( $hideclosebtn[0], 1 ); ?> /> Hide Main Close Button
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_do_customizeclosebtn" type="checkbox" id="post_do_customizeclosebtn" value="1" class="enable_custombtn" <?php checked( $customizeclosebtn[0], 1 ); ?> /> Customize Close Button
			</p>
			<div class="enable_customizations<?php if ( $customizeclosebtn[0] == 1 ) { ?> do-show<?php } ?>">
				<div class="custom_meta_box">
					<p>
						<label class="label-color-field">Text color:</label>
						<input class="closebtn-text-color" type="text" name="post_closebtn_text_color" value="<?php echo $textcolor; ?>"/>
					</p>
					<div class="clear"></div> 
				</div> 
				<div class="custom_meta_box">
					<p>
						<label class="label-color-field">Background color:</label>
						<input class="closebtn-bg-color" type="text" name="post_closebtn_bg_color" value="<?php echo $bgcolor; ?>"/>
					</p>
					<div class="clear"></div> 
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Font size:</label>
						<input class="post_closebtn_fontsize" type="text" name="post_closebtn_fontsize" value="<?php echo $fontsize; ?>" readonly="readonly" > px
					</p>
					<div id="slider-closebtn-fontsize" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Border radius:</label>
						<input class="post_closebtn_borderradius" type="text" name="post_closebtn_borderradius" value="<?php echo $borderradius; ?>" readonly="readonly" > %
					</p>
					<div id="slider-closebtn-borderradius" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Padding:</label>
						<input class="post_closebtn_padding" type="text" name="post_closebtn_padding" value="<?php echo $padding; ?>" readonly="readonly" > px
					</p>
					<div id="slider-closebtn-padding" class="slider-bar"></div>
				</div>
				<div class="custom_meta_box">
					<p>
						<label>Preview:</label>
					</p>
					<button type="button" class="overlay-customclose-btn"><span>&times;</span></button>
				</div>
			</div>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;		


if ( ! function_exists( 'do_moresettings_callback' ) ) :

	function do_moresettings_callback( $post ) {
	
		wp_nonce_field( 'do_mainpage_preventscroll', 'do_mainpage_preventscroll_nonce' );
		
		$preventscroll = get_post_meta( $post->ID, 'post_do_preventscroll' );
		$css_selector = get_post_meta( $post->ID, 'post_css_selector', true );
		$enableurltrigger = get_post_meta( $post->ID, 'post_enableurltrigger' );
		$enableurltrigger_pages = get_post_meta( $post->ID, 'post_enableurltrigger_pages', true );
		$selectedpages = get_post_meta( $post->ID, 'post_dolistpages' );
		
		$post_types = get_post_types();
		
		$posts = array();
		
		foreach( $post_types as $post_type) {
			
			if ( $post_type == 'attachment'
				|| $post_type == 'revision'
				|| $post_type == 'nav_menu_item'
				|| $post_type == 'custom_css'
				|| $post_type == 'et_pb_layout'
				|| $post_type == 'project'
				|| $post_type == 'divi_overlay'
				|| $post_type == 'customize_changeset' ) {
					
				continue;
			}
			
			$args = array(
				'post_type' => $post_type,
				'cache_results'  => true,
				'posts_per_page' => -1
			);
			$query = new WP_Query( $args );
			
			$get_posts = $query->get_posts();
			
			$posts = array_merge($posts, $get_posts);
		}
		
		if( !isset( $preventscroll[0] ) ) {
			
			$preventscroll[0] = '0';
		}
		
		if( !isset( $enableurltrigger[0] ) ) {
			
			$enableurltrigger[0] = '0';
		}
		
		if( $enableurltrigger_pages == '' ) {
			
			$enableurltrigger_pages = 'all';
		}
		?>
		<div class="custom_meta_box">
			<p>
				<label>CSS Selector Trigger:</label>
				<input class="css_selector" type="text" name="post_css_selector" value="<?php echo $css_selector; ?>"/>
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_do_preventscroll" type="checkbox" id="post_do_preventscroll" value="1" <?php checked( $preventscroll[0], 1 ); ?> /> Prevent main page scrolling
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<label for="post_enableurltrigger"></label>
				<input name="post_enableurltrigger" type="checkbox" class="enableurltrigger" value="1" <?php checked( $enableurltrigger[0], 1 ); ?> /> Enable URL Trigger
			</p>
			<div class="enableurltrigger_filters<?php if ( $enableurltrigger[0] == 1 ) { ?> do-show<?php } ?>">
				<select name="post_enableurltrigger_pages" class="enableurltrigger_pages chosen overlay-filter-by-pages">
					<option value="all"<?php if ( $enableurltrigger_pages == 'all' ) { ?> selected="selected"<?php } ?>>All pages</option>
					<option value="specific"<?php if ( $enableurltrigger_pages == 'specific' ) { ?> selected="selected"<?php } ?>>Only specific pages</option>
				</select>
				<div class="do-list-pages-container<?php if ( $enableurltrigger_pages == 'specific' ) { ?> do-show<?php } ?>">
					<select name="post_dolistpages[]" class="do-list-pages" data-placeholder="Choose posts or pages..." multiple tabindex="3">
				<?php
					if ( isset( $posts[0] ) ) {
						
						foreach( $posts as $post ) {
							
							if ( in_array( $post->ID, $selectedpages[0]) ) {
								$selected = ' selected="selected"';
							}
							
							print '<option value="' . $post->ID . '"' . $selected . '>' . $post->post_title . '</option>';
							
							$selected = '';
						}
					}
				?>
					</select>
				</div>
			</div>
			<div class="clear"></div> 
		</div>
		<?php
	}
	
endif;


if ( ! function_exists( 'do_automatictriggers_callback' ) ) :

	function do_automatictriggers_callback( $post ) {
		
		$post_id = get_the_ID();
		$disablemobile = get_post_meta( $post_id, 'overlay_automatictrigger_disablemobile' );
		$onceperload = get_post_meta( $post_id, 'overlay_automatictrigger_onceperload' );
		$at_pages = get_post_meta( $post->ID, 'at_pages', true );
		$selectedpages = get_post_meta( $post->ID, 'at_pages_selected' );
		
		$post_types = get_post_types();
		
		$posts = array();
		
		foreach( $post_types as $post_type) {
			
			if ( $post_type == 'attachment'
				|| $post_type == 'revision'
				|| $post_type == 'nav_menu_item'
				|| $post_type == 'custom_css'
				|| $post_type == 'et_pb_layout'
				|| $post_type == 'project'
				|| $post_type == 'divi_overlay'
				|| $post_type == 'customize_changeset' ) {
					
				continue;
			}
			
			$args = array(
				'post_type' => $post_type,
				'cache_results'  => true,
				'posts_per_page' => -1
			);
			$query = new WP_Query( $args );
			
			$get_posts = $query->get_posts();
			
			$posts = array_merge($posts, $get_posts);
		}
		
		if( $at_pages == '' ) {
			
			$at_pages = 'all';
		}
		
		$overlay_at_selected = get_post_meta( $post_id, 'overlay_automatictrigger', true );
		$overlay_ats = array(
			'overlay-timed'   => esc_html__( 'Timed Delay', 'Divi' ),
			'overlay-scroll'    => esc_html__( 'Scroll Percentage', 'Divi' ),
			'overlay-exit' => esc_html__( 'Exit Intent', 'Divi' ),
		);
		
		if( !isset( $disablemobile[0] ) ) {
			
			$disablemobile[0] = '1';
		}
		
		if( !isset( $onceperload[0] ) ) {
			
			$onceperload[0] = '1';
		}
		?>
		<p class="divi_automatictrigger_settings et_pb_single_title">
			<label for="post_overlay_automatictrigger"></label>
			<select id="post_overlay_automatictrigger" name="post_overlay_automatictrigger" class="post_overlay_automatictrigger chosen">
				<option value="">None</option>
			<?php
			foreach ( $overlay_ats as $at_value => $at_name ) {
				printf( '<option value="%2$s"%3$s>%1$s</option>',
					esc_html( $at_name ),
					esc_attr( $at_value ),
					selected( $at_value, $overlay_at_selected, false )
				);
			} ?>
			</select>
		</p>
		
		<?php
		
			$at_timed = get_post_meta( $post->ID, 'overlay_automatictrigger_timed_value', true );
			$at_scroll_from = get_post_meta( $post->ID, 'overlay_automatictrigger_scroll_from_value', true );
			$at_scroll_to = get_post_meta( $post->ID, 'overlay_automatictrigger_scroll_to_value', true );
		?>
		<div class="divi_automatictrigger_timed<?php if ( $overlay_at_selected == 'overlay-timed' ) { ?> do-show<?php } ?>">
			<p>
				<label>Specify timed delay (in seconds):</label>
				<input class="post_at_timed" type="text" name="post_at_timed" value="<?php echo $at_timed; ?>"/>
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="divi_automatictrigger_scroll<?php if ( $overlay_at_selected == 'overlay-scroll' ) { ?> do-show<?php } ?>">
			<p>Specify in pixels or percentage:</p>
			<div class="at-scroll-settings">
				<label for="post_at_scroll_from">From:</label>
				<input class="post_at_scroll" type="text" name="post_at_scroll_from" value="<?php echo $at_scroll_from; ?>"/>
				<label for="post_at_scroll_to">to:</label>
				<input class="post_at_scroll" type="text" name="post_at_scroll_to" value="<?php echo $at_scroll_to; ?>"/>
			</div> 
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_at_disablemobile" type="checkbox" id="post_at_disablemobile" value="1" <?php checked( $disablemobile[0], 1 ); ?> /> Disable On Mobile
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<p>
				<input name="post_at_onceperload" type="checkbox" id="post_at_onceperload" value="1" <?php checked( $onceperload[0], 1 ); ?> />
				 Display once per page load
			</p>
			<div class="clear"></div> 
		</div>
		
		<div class="custom_meta_box">
			<div class="at_pages">
				<select name="post_at_pages" class="at_pages chosen overlay-filter-by-pages">
					<option value="all"<?php if ( $at_pages == 'all' ) { ?> selected="selected"<?php } ?>>All pages</option>
					<option value="specific"<?php if ( $at_pages == 'specific' ) { ?> selected="selected"<?php } ?>>Only specific pages</option>
				</select>
				<div class="do-list-pages-container<?php if ( $at_pages == 'specific' ) { ?> do-show<?php } ?>">
					<select name="post_at_pages_selected[]" class="chosen" data-placeholder="Choose posts or pages..." multiple tabindex="3">
				<?php
					if ( isset( $posts[0] ) ) {
						
						foreach( $posts as $post ) {
							
							if ( in_array( $post->ID, $selectedpages[0]) ) {
								$selected = ' selected="selected"';
							}
							
							print '<option value="' . $post->ID . '"' . $selected . '>' . $post->post_title . '</option>';
							
							$selected = '';
						}
					}
				?>
					</select>
				</div>
			</div>
			<div class="clear"></div> 
		</div>
		
		<?php
	}
	
endif;


if ( ! function_exists( 'et_single_animation_meta_box' ) ) :

	function et_single_animation_meta_box($post) {
		
		$post_id = get_the_ID();
		$overlay_effect = get_post_meta( $post_id, '_et_pb_overlay_effect', true );
		$overlay_effects = array(
			'overlay-hugeinc'   => esc_html__( 'Fade & Slide', 'Divi' ),
			'overlay-corner'    => esc_html__( 'Corner', 'Divi' ),
			'overlay-slidedown' => esc_html__( 'Slide down', 'Divi' ),
			'overlay-scale' => esc_html__( 'Scale', 'Divi' ),
			'overlay-door' => esc_html__( 'Door', 'Divi' ),
			'overlay-contentpush' => esc_html__( 'Content Push', 'Divi' ),
			'overlay-contentscale' => esc_html__( 'Content Scale', 'Divi' ),
			'overlay-cornershape' => esc_html__( 'Corner Shape', 'Divi' ),
			'overlay-boxes' => esc_html__( 'Little Boxes', 'Divi' ),
			'overlay-simplegenie' => esc_html__( 'Simple Genie', 'Divi' ),
			'overlay-genie' => esc_html__( 'Genie', 'Divi' ),
		);
		?>
		<p class="et_pb_page_settings et_pb_single_title">
			<label for="et_pb_page_layout" style="display: block; font-weight: bold; margin-bottom: 5px;"><?php esc_html_e( 'Select Overlay Animation', 'Divi' ); ?>: </label>
			<select id="et_pb_overlay_effect" name="et_pb_overlay_effect" class="chosen">
			<?php
			foreach ( $overlay_effects as $overlay_value => $overlay_name ) {
				printf( '<option value="%2$s"%3$s>%1$s</option>',
					esc_html( $overlay_name ),
					esc_attr( $overlay_value ),
					selected( $overlay_value, $overlay_effect, false )
				);
			} ?>
			</select>
		</p>
		
	<?php }
	
endif;


// Save Meta Box Value //
/*========================= Color Picker ============================*/
function overlay_color_box_callback( $post ) {	
	wp_nonce_field( 'overlay_color_box', 'overlay_color_box_nonce' );
	$color = get_post_meta( $post->ID, 'post_overlay_bg_color', true );
	$fontcolor = get_post_meta( $post->ID, 'post_overlay_font_color', true );
	?>
	<div class="custom_meta_box">
		<p>
			<label class="label-color-field">Select Overlay Background Color: </label>
			<input class="cs-wp-color-picker" type="text" name="post_bg" value="<?php echo $color; ?>"/>
		</p>
		<div class="clear"></div> 
	</div> 
	<div class="custom_meta_box">
		<p>
			<label class="label-color-field">Select Overlay Font Color: </label>
			<input class="color-field" type="text" name="post_font_color" value="<?php echo $fontcolor; ?>"/>
		</p>
		<div class="clear"></div> 
	</div> 
	<script>
		(function( $ ) {
			// Add Color Picker to all inputs that have 'color-field' class
			$(function() {
			$('.color-field').wpColorPicker();
			});
		})( jQuery );
	</script>
	<?php
}
function Colorpicker_js(){
	// enqueue style
    	wp_enqueue_style( 'wp-color-picker' );
    	wp_enqueue_style( 'cs-wp-color-picker', plugins_url( 'overlay-effects/css/cs-wp-color-picker.min.css', __FILE__ ), array( 'wp-color-picker' ), '1.0.0', 'all' );

    	// enqueue scripts
    	wp_enqueue_script( 'wp-color-picker' );
    	wp_enqueue_script( 'cs-wp-color-picker', plugins_url( 'overlay-effects/js/cs-wp-color-picker.min.js', __FILE__ ), array( 'wp-color-picker' ), '1.0.0', true );
}
add_action('admin_enqueue_scripts', 'Colorpicker_js');

function divi_overlay_config($hook) {
	
	if ( $hook != 'post.php' && $hook != 'post-new.php' ) {
		return;
	}
	
	wp_register_style( 'chosen', plugins_url( 'overlay-effects/libraries/chosen/chosen.min.css', __FILE__ ), array(), '1.0.0', 'all' );
	wp_register_style( 'divi-overlay-admin', plugins_url( 'overlay-effects/css/admin.css', __FILE__ ), array(), '1.0.0', 'all' );
	wp_register_script( 'chosen', plugins_url( 'overlay-effects/libraries/chosen/chosen.jquery.min.js', __FILE__ ), array(), '1.0.0', true );
	wp_register_script( 'divi-overlay-admin-functions', plugins_url( 'overlay-effects/js/admin-functions.js', __FILE__ ), array( 'chosen' ), '1.0.0', true );
	
	wp_enqueue_style( 'chosen' );
	wp_enqueue_style( 'divi-overlay-admin' );
	wp_enqueue_script( 'chosen' );
	wp_enqueue_script( 'divi-overlay-admin-functions' );
}
add_action('admin_enqueue_scripts', 'divi_overlay_config');
/*===================================================================*/

// Save Meta Box Value //
function et_divi_overlay_settings_save_details( $post_id, $post ){
	global $pagenow;

	if ( 'post.php' != $pagenow ) return $post_id;

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return $post_id;

	$post_type = get_post_type_object( $post->post_type );
	if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) )
		return $post_id;
	
	$post_value = '';

	if ( isset( $_POST['et_pb_overlay_effect'] ) ) {
		update_post_meta( $post_id, '_et_pb_overlay_effect', sanitize_text_field( $_POST['et_pb_overlay_effect'] ) );
	} else {
		delete_post_meta( $post_id, '_et_pb_overlay_effect' );
	}
	if ( isset( $_POST['post_bg'] ) ) {
		update_post_meta( $post_id, 'post_overlay_bg_color', sanitize_text_field( $_POST['post_bg'] ) );
	}
	if ( isset( $_POST['post_font_color'] ) ) {
		update_post_meta( $post_id, 'post_overlay_font_color', sanitize_text_field( $_POST['post_font_color'] ) );
	}
	
	if ( isset( $_POST['post_do_preventscroll'] ) ) {
		
		$post_do_preventscroll = 1;
		
	} else {
		
		$post_do_preventscroll = 0;
	}
	update_post_meta( $post_id, 'post_do_preventscroll', $post_do_preventscroll );
	
	
	if ( isset( $_POST['post_css_selector'] ) ) {
		update_post_meta( $post_id, 'post_css_selector', sanitize_text_field( $_POST['post_css_selector'] ) );
	}
	
	
	if ( isset( $_POST['post_enableurltrigger'] ) ) {
		
		$post_enableurltrigger = 1;
		
		if ( isset( $_POST['post_enableurltrigger_pages'] ) ) {
			$post_value = sanitize_text_field( $_POST['post_enableurltrigger_pages'] );
			update_post_meta( $post_id, 'post_enableurltrigger_pages', $post_value );
		}
		
		if ( $post_value == 'specific' ) {
			
			if ( isset( $_POST['post_dolistpages'] ) ) {
				update_post_meta( $post_id, 'post_dolistpages', $_POST['post_dolistpages'] );
			}
		}
		else {
			
			update_post_meta( $post_id, 'post_dolistpages', '' );
		}
		
	} else {
		
		$post_enableurltrigger = 0;
	}
	update_post_meta( $post_id, 'post_enableurltrigger', $post_enableurltrigger );
	
	
	if ( isset( $_POST['post_overlay_automatictrigger'] ) ) {
		
		update_post_meta( $post_id, 'overlay_automatictrigger', sanitize_text_field( $_POST['post_overlay_automatictrigger'] ) );
	
		if ( isset( $_POST['post_at_timed'] ) ) {
			update_post_meta( $post_id, 'overlay_automatictrigger_timed_value', sanitize_text_field( $_POST['post_at_timed'] ) );
		}
		
		if ( isset( $_POST['post_at_scroll_from'] ) || isset( $_POST['post_at_scroll_to'] ) ) {
			update_post_meta( $post_id, 'overlay_automatictrigger_scroll_from_value', sanitize_text_field( $_POST['post_at_scroll_from'] ) );
			update_post_meta( $post_id, 'overlay_automatictrigger_scroll_to_value', sanitize_text_field( $_POST['post_at_scroll_to'] ) );
		}
		
		if ( isset( $_POST['post_at_disablemobile'] ) ) {
			
			$post_at_disablemobile = 1;
			
		} else {
			
			$post_at_disablemobile = 0;
		}
		
		if ( isset( $_POST['post_at_onceperload'] ) ) {
			
			$post_at_onceperload = 1;
			
		} else {
			
			$post_at_onceperload = 0;
		}
		
		update_post_meta( $post_id, 'overlay_automatictrigger_onceperload', $post_at_onceperload );
		
		if ( isset( $_POST['post_at_pages'] ) ) {
			
			$post_value = sanitize_text_field( $_POST['post_at_pages'] );
			update_post_meta( $post_id, 'at_pages', $post_value );
		}
		
		if ( $post_value == 'specific' ) {
			
			if ( isset( $_POST['post_at_pages_selected'] ) ) {
				update_post_meta( $post_id, 'at_pages_selected', $_POST['post_at_pages_selected'] );
			}
		}
		else {
			
			update_post_meta( $post_id, 'at_pages_selected', '' );
		}
		
		
	} else {
		
		$post_at_disablemobile = 0;
	}
	update_post_meta( $post_id, 'overlay_automatictrigger_disablemobile', $post_at_disablemobile );
	
	
	/* Close Button Customizations */
	if ( isset( $_POST['post_do_hideclosebtn'] ) ) {
		
		$post_do_hideclosebtn = 1;
		
	} else {
		
		$post_do_hideclosebtn = 0;
	}
	update_post_meta( $post_id, 'post_do_hideclosebtn', $post_do_hideclosebtn );
	
	if ( isset( $_POST['post_do_customizeclosebtn'] ) ) {
		
		$post_do_customizeclosebtn = 1;
		
	} else {
		
		$post_do_customizeclosebtn = 0;
	}
	update_post_meta( $post_id, 'post_do_customizeclosebtn', $post_do_customizeclosebtn );
	
	if ( isset( $_POST['post_closebtn_text_color'] ) ) {
		update_post_meta( $post_id, 'post_closebtn_text_color', sanitize_text_field( $_POST['post_closebtn_text_color'] ) );
	}
	
	if ( isset( $_POST['post_closebtn_bg_color'] ) ) {
		update_post_meta( $post_id, 'post_closebtn_bg_color', sanitize_text_field( $_POST['post_closebtn_bg_color'] ) );
	}
	
	if ( isset( $_POST['post_closebtn_fontsize'] ) ) {
		update_post_meta( $post_id, 'post_closebtn_fontsize', sanitize_text_field( $_POST['post_closebtn_fontsize'] ) );
	}
	
	if ( isset( $_POST['post_closebtn_borderradius'] ) ) {
		update_post_meta( $post_id, 'post_closebtn_borderradius', sanitize_text_field( $_POST['post_closebtn_borderradius'] ) );
	}
	
	if ( isset( $_POST['post_closebtn_padding'] ) ) {
		update_post_meta( $post_id, 'post_closebtn_padding', sanitize_text_field( $_POST['post_closebtn_padding'] ) );
	}
}
add_action( 'save_post', 'et_divi_overlay_settings_save_details', 10, 2 );

add_action( 'widgets_init', 'theme_slug_widgets_init' );
function theme_slug_widgets_init() {
    register_sidebar( array(
        'name' => __( 'Divi Overlays: Global Overlays', 'theme-slug' ),
        'id' => 'custom_footer_sidebar',
        'description' => __( 'Use a Text Widget and paste the content shortcodes for any overlays you want to trigger from the main menu, the sidebar, or footer.', 'theme-slug' )
    ) );
}
add_action('wp_enqueue_scripts', 'fwds_scripts');

function fwds_scripts() {
	wp_enqueue_script('jquery');
	wp_register_style('normalize_css', plugins_url('overlay-effects/css/normalize.css', __FILE__));
    wp_enqueue_style('normalize_css');
	wp_register_style('custom_style_css', plugins_url('overlay-effects/css/style.css', __FILE__));
    wp_enqueue_style('custom_style_css');
	wp_register_script('snap_svg_js', plugins_url('overlay-effects/js/snap.svg-min.js', __FILE__),array("jquery"));
	wp_enqueue_script('snap_svg_js');
	wp_register_script('modernizr_js', plugins_url('overlay-effects/js/modernizr.custom.js', __FILE__),array("jquery"));
    wp_enqueue_script('modernizr_js');
}

// Deprecated function - leave for backward compatibility
add_shortcode("overlay_content", "overlay_content_function");
function overlay_content_function($atts) {
	return '';
}

function showOverlay( $overlay_id = NULL ) {
	
	ob_start();
	
    if ( !is_numeric( $overlay_id ) )
        return NULL;
	
	$overlay_id = (int) $overlay_id;
    
	$post_data = get_post( $overlay_id );
	
	$overlay_effect = get_post_meta($post_data->ID,'_et_pb_overlay_effect',true);
	
	if ( $overlay_effect == '' ) {
		
		$overlay_effect = 'overlay-hugeinc';
	}
	
	global $wp_embed;
	
	$wp_embed->post_ID = $post_data->ID;
	
	// [embed] shortcode
	$wp_embed->run_shortcode( $post_data->post_content );
	
	// plain links on their own line
	$wp_embed->autoembed( $post_data->post_content );
	
	// Enable shortcodes
	$output = do_shortcode( $post_data->post_content );
	
	$bgcolor = get_post_meta( $post_data->ID, 'post_overlay_bg_color', true );
	$fontcolor = get_post_meta( $post_data->ID, 'post_overlay_font_color', true );
	
	$preventscroll = get_post_meta( $post_data->ID, 'post_do_preventscroll' );
	if ( isset( $preventscroll[0] ) ) {
		
		$preventscroll = $preventscroll[0];
		
	} else {
		
		$preventscroll = 0;
	}
	
	$hideclosebtn = get_post_meta( $post_data->ID, 'post_do_hideclosebtn' );
	if ( isset( $hideclosebtn[0] ) ) {
		
		$hideclosebtn = $hideclosebtn[0];
		
	} else {
		
		$hideclosebtn = 0;
	}
	
	$data_path_to = null;
	$svg = null;
	
	if ( $overlay_effect == 'overlay-cornershape' ) {
		
		$data_path_to = 'data-path-to = "m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z"';
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path class="overlay-path" d="m 0,0 1439.999975,0 0,805.99999 0,-805.99999 z"/>
			</svg>';
	}
	if ( $overlay_effect == 'overlay-boxes' ) {
		
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="101%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path d="m0.005959,200.364029l207.551124,0l0,204.342453l-207.551124,0l0,-204.342453z"/>
				<path d="m0.005959,400.45401l207.551124,0l0,204.342499l-207.551124,0l0,-204.342499z"/>
				<path d="m0.005959,600.544067l207.551124,0l0,204.342468l-207.551124,0l0,-204.342468z"/>
				<path d="m205.752151,-0.36l207.551163,0l0,204.342437l-207.551163,0l0,-204.342437z"/>
				<path d="m204.744629,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z"/>
				<path d="m204.744629,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z"/>
				<path d="m204.744629,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z"/>
				<path d="m410.416046,-0.36l207.551117,0l0,204.342437l-207.551117,0l0,-204.342437z"/>
				<path d="m410.416046,200.364029l207.551117,0l0,204.342453l-207.551117,0l0,-204.342453z"/>
				<path d="m410.416046,400.45401l207.551117,0l0,204.342499l-207.551117,0l0,-204.342499z"/>
				<path d="m410.416046,600.544067l207.551117,0l0,204.342468l-207.551117,0l0,-204.342468z"/>
				<path d="m616.087402,-0.36l207.551086,0l0,204.342437l-207.551086,0l0,-204.342437z"/>
				<path d="m616.087402,200.364029l207.551086,0l0,204.342453l-207.551086,0l0,-204.342453z"/>
				<path d="m616.087402,400.45401l207.551086,0l0,204.342499l-207.551086,0l0,-204.342499z"/>
				<path d="m616.087402,600.544067l207.551086,0l0,204.342468l-207.551086,0l0,-204.342468z"/>
				<path d="m821.748718,-0.36l207.550964,0l0,204.342437l-207.550964,0l0,-204.342437z"/>
				<path d="m821.748718,200.364029l207.550964,0l0,204.342453l-207.550964,0l0,-204.342453z"/>
				<path d="m821.748718,400.45401l207.550964,0l0,204.342499l-207.550964,0l0,-204.342499z"/>
				<path d="m821.748718,600.544067l207.550964,0l0,204.342468l-207.550964,0l0,-204.342468z"/>
				<path d="m1027.203979,-0.36l207.550903,0l0,204.342437l-207.550903,0l0,-204.342437z"/>
				<path d="m1027.203979,200.364029l207.550903,0l0,204.342453l-207.550903,0l0,-204.342453z"/>
				<path d="m1027.203979,400.45401l207.550903,0l0,204.342499l-207.550903,0l0,-204.342499z"/>
				<path d="m1027.203979,600.544067l207.550903,0l0,204.342468l-207.550903,0l0,-204.342468z"/>
				<path d="m1232.659302,-0.36l207.551147,0l0,204.342437l-207.551147,0l0,-204.342437z"/>
				<path d="m1232.659302,200.364029l207.551147,0l0,204.342453l-207.551147,0l0,-204.342453z"/>
				<path d="m1232.659302,400.45401l207.551147,0l0,204.342499l-207.551147,0l0,-204.342499z"/>
				<path d="m1232.659302,600.544067l207.551147,0l0,204.342468l-207.551147,0l0,-204.342468z"/>
				<path d="m-0.791443,-0.360001l207.551163,0l0,204.342438l-207.551163,0l0,-204.342438z"/>
			</svg>';
	}
	
	if ( $overlay_effect == 'overlay-genie' ) {
		
		$data_path_to = 'data-steps = "m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z;m 698.9986,728.03569 41.23353,0 -3.41953,77.8735 -34.98557,0 z;m 687.08153,513.78234 53.1506,0 C 738.0505,683.9161 737.86917,503.34193 737.27015,806 l -35.90067,0 c -7.82727,-276.34892 -2.06916,-72.79261 -14.28795,-292.21766 z;m 403.87105,257.94772 566.31246,2.93091 C 923.38284,513.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 455.17312,480.07689 403.87105,257.94772 z;M 51.871052,165.94772 1362.1835,168.87863 C 1171.3828,653.78233 738.73561,372.23931 737.27015,806 l -35.90067,0 C 701.32034,404.49318 31.173122,513.78234 51.871052,165.94772 z;m 52,26 1364,4 c -12.8007,666.9037 -273.2644,483.78234 -322.7299,776 l -633.90062,0 C 359.32034,432.49318 -6.6979288,733.83462 52,26 z;m 0,0 1439.999975,0 0,805.99999 -1439.999975,0 z"';
		$svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1440 806" preserveAspectRatio="none">
				<path class="overlay-path" d="m 701.56545,809.01175 35.16718,0 0,19.68384 -35.16718,0 z"/>
			</svg>';
	}
	
		$customizeclosebtn = get_post_meta( $post_data->ID, 'post_do_customizeclosebtn' );
		if( !isset( $customizeclosebtn[0] ) ) {
			
			$customizeclosebtn[0] = '0';
		}
	
	?>
	<div id="divi-overlay-container-<?php echo $overlay_id;?>" class="overlay-container">
		<div id="overlay-<?php echo $post_data->ID;?>" class="overlay <?php echo $overlay_effect;?>" <?php echo $data_path_to;?> data-bgcolor="<?php echo $bgcolor;?>" data-fontcolor="<?php echo $fontcolor;?>" data-preventscroll="<?php print $preventscroll ?>" data-scrolltop="">
			
			<?php echo $svg; ?>
			
			<?php if ( $hideclosebtn == 0 ) { ?>
			<button type="button" class="overlay-close overlay-customclose-btn-<?php echo $overlay_id ?>"><span class="<?php if ( $customizeclosebtn[0] == 1 ) { ?>custom_btn<?php } ?>">&times;</span></button>
			<?php } ?>
			
			<?php 
				
				// is divi theme builder ?
				if ( is_singular() && 'on' === get_post_meta( $post_data->ID, '_et_pb_use_builder', true ) ) {
					
					echo decodeInequalitySigns($output);
					
				} else {
					?>
					<div class="et_pb_section  et_section_regular">
						<div class="et_pb_row et_pb_row_0">
							<div class="et_pb_column et_pb_column_4_4  et_pb_column_0">
								<?php echo decodeInequalitySigns( $output ); ?>
							</div>
						</div>
					</div>
					<?php
				}
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

function decodeInequalitySigns( $html ) {
	
	return $html;
}

function setHeightWidthSrc($s, $width, $height)
{
  return preg_replace(
    '@^<iframe\s*title="(.*)"\s*width="(.*)"\s*height="(.*)"\s*src="(.*?)"\s*(.*?)</iframe>$@s',
    '<iframe title="\1" width="' . $width . '" height="' . $height . '" src="\4?wmode=transparent" \5</iframe>',
    $s
  );
}

add_action('wp_head','custom_css_function');
function custom_css_function(){
	?>
	<style type="text/css">
		a {
cursor: pointer !important;
}	
	</style>
	<?php
}
add_action('wp_footer','custom_js_function');
function custom_js_function() {
	
	print '<div id="sidebar-overlay">';
	
	/* Search Divi Overlay in current post */
	global $post;
	$post_content = $post->post_content;
	$matches = array();
	$pattern = '/id="(.*?overlay_[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_overlay_ = $matches[1];
	
	$matches = array();
	$pattern = '/id="(.*?overlay_unique_id_[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_overlay_unique_id_ = $matches[1];
	
	$matches = array();
	$pattern = '/class="(.*?overlay\-[0-9]+)"/';
	preg_match_all($pattern, $post_content, $matches);
	
	$overlays_class_overlay = $matches[1];
	
	$overlays_in_post = $overlays_overlay_ + $overlays_overlay_unique_id_ + $overlays_class_overlay;
	
	$overlays_in_post = array_filter( array_map( "prepareOverlays", $overlays_in_post ) );
	
	if ( is_array( $overlays_in_post ) && count( $overlays_in_post ) > 0 ) {
		
		$overlays_in_post = array_flip( $overlays_in_post );
		
	}
	
	
	/* Search Divi Overlay in active menus */
	$theme_locations = get_nav_menu_locations();
	
	$overlays_in_menus = array();
	
	if ( is_array( $theme_locations ) && count( $theme_locations ) > 0 ) {
		
		$overlays_in_menus = array();
		
		foreach( $theme_locations as $theme_location => $theme_location_value ) {
			
			$menu = get_term( $theme_locations[$theme_location], 'nav_menu' );
			
			// menu exists?
			if( !is_wp_error($menu) ) {
				
				$menu_items = wp_get_nav_menu_items($menu->term_id);
				
				foreach ( (array) $menu_items as $key => $menu_item ) {
					
					$url = $menu_item->url;
					
					$extract_id = prepareOverlays( $url );
					
					if ( $extract_id ) {
						
						$overlays_in_menus[ $extract_id ] = 1;
					}
					
					/* Search Divi Overlay in menu classes */
					if ( count( $menu_item->classes ) > 0 && $menu_item->classes[0] != '' ) {
						
						foreach ( $menu_item->classes as $key => $class ) {
							
							if ( $class != '' ) {
								
								$extract_id = prepareOverlays( $class );
								
								if ( $extract_id ) {
								
									$overlays_in_menus[ $extract_id ] = 1;
								}
							}
						}
					}
				}
			}
		}
	}
	
	$overlays_in_menus = array_filter( $overlays_in_menus );
	
	
	/* Search CSS Triggers in all Divi Overlays */
	global $wp_query;
	
	$args = array(
		'meta_key'   => 'post_css_selector',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	$overlays_with_css_trigger = array();
	
	if ( isset( $posts[0] ) ) {
		
		print '<script type="text/javascript">var overlays_with_css_trigger = {';
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$get_css_selector = get_post_meta( $post_id, 'post_css_selector' );
			
			$css_selector = $get_css_selector[0];
			
			if ( $css_selector != '' ) {
				
				print '\'' . $post_id . '\': \'' . $css_selector . '\',';
				
				$overlays_with_css_trigger[ $post_id ] = $css_selector;
			}
		}
		
		print '};</script>';
	}
	
	
	/* Search URL Triggers in all Divi Overlays */
	$args = array(
		'meta_key'   => 'post_enableurltrigger',
		'meta_value' => '1',
		'meta_compare' => '=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	$overlays_with_url_trigger = array();
	
	if ( isset( $posts[0] ) ) {
		
		$display_in_current = false;
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$enableurltrigger_pages = get_post_meta( $post_id, 'post_enableurltrigger_pages' );
			
			$display_in_posts = $enableurltrigger_pages[0];
			
			if ( $display_in_posts == 'specific' ) {
				
				$in_posts = get_post_meta( $post_id, 'post_dolistpages' );
				
				foreach( $in_posts[0] as $in_post => $the_id ) {
					
					if( $the_id == $post->ID ) {
						
						$display_in_current = true;
						
						break;
					}
				}
			}
			
			if ( $display_in_posts == 'all' || $display_in_current ) {
				
				$overlays_with_url_trigger[ $post_id ] = 1;
			}
		}
	}
	$overlays_with_url_trigger = array_filter( $overlays_with_url_trigger );
	
	
	/* Search Automatic Triggers in all Divi Overlays */
	
	// Server-Side Device Detection with Browscap
	require_once( plugin_dir_path( __FILE__ ) . 'php-libraries/Browscap/Browscap.php' );
	$browscap = new Browscap( plugin_dir_path( __FILE__ ) . '/php-libraries/Browscap/Cache/' );
	$browscap->doAutoUpdate = false;
	$current_browser = $browscap->getBrowser();
	
	$isMobileDevice = $current_browser->isMobileDevice;
	
	$overlays_with_automatic_trigger = array();
	
	$args = array(
		'meta_key'   => 'overlay_automatictrigger',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	if ( isset( $posts[0] ) ) {
		
		print '<script type="text/javascript">var overlays_with_automatic_trigger = {';
		
		$display_in_current = false;
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$at_pages = get_post_meta( $post_id, 'at_pages' );
			
			$display_in_posts = ( $at_pages[0] == NULL ) ? 'all' : $at_pages[0];
			
			if ( $display_in_posts == 'specific' ) {
				
				$in_posts = get_post_meta( $post_id, 'at_pages_selected' );
				
				foreach( $in_posts[0] as $in_post => $the_id ) {
					
					if( $the_id == $post->ID ) {
						
						$display_in_current = true;
						
						break;
					}
				}
			}
			
			if ( $display_in_posts == 'all' || $display_in_current ) {
				
				$at_disablemobile = get_post_meta( $post_id, 'overlay_automatictrigger_disablemobile' );
				$onceperload = get_post_meta( $post_id, 'overlay_automatictrigger_onceperload', true );
				
				if ( isset( $onceperload[0] ) ) {
					
					$onceperload = $onceperload[0];
					
				} else {
					
					$onceperload = 1;
				}
				
				if ( isset( $at_disablemobile[0] ) ) {
					
					$at_disablemobile = $at_disablemobile[0];
					
				} else {
					
					$at_disablemobile = 1;
				}
				
				$printSettings = 1;
				if ( $at_disablemobile && $isMobileDevice ) {
					
					$printSettings = 0;
				}
				
				if ( $printSettings ) {
					
					$at_type = get_post_meta( $post_id, 'overlay_automatictrigger', true );
					$at_timed = get_post_meta( $post_id, 'overlay_automatictrigger_timed_value', true );
					$at_scroll_from = get_post_meta( $post_id, 'overlay_automatictrigger_scroll_from_value', true );
					$at_scroll_to = get_post_meta( $post_id, 'overlay_automatictrigger_scroll_to_value', true );
					
					if ( $at_type != '' ) {
						
						switch ( $at_type ) {
							
							case 'overlay-timed':
								$at_value = $at_timed;
							break;
							
							case 'overlay-scroll':
								$at_value = $at_scroll_from . ':' . $at_scroll_to;
							break;
							
							default:
								$at_value = $at_type;
						}
						
						$at_settings = json_encode( array( 'at_type' => $at_type, 'at_value' => $at_value, 'at_onceperload' => $onceperload ) );
						
						print '\'' . $post_id . '\': \'' . $at_settings . '\',';
						
						$overlays_with_automatic_trigger[ $post_id ] = $at_type;
					}
				}
			}
		}
		
		print '};</script>';
	}
	$overlays_with_automatic_trigger = array_filter( $overlays_with_automatic_trigger );
	
	
	/* Search Divi Overlays with Custom Close Buttons */
	$args = array(
		'meta_key'   => 'post_do_customizeclosebtn',
		'meta_value' => '',
		'meta_compare' => '!=',
		'post_type' => 'divi_overlay',
		'cache_results'  => false
	);
	$query = new WP_Query( $args );
	
	$posts = $query->get_posts();
	
	if ( isset( $posts[0] ) ) {
		
		print '<style type="text/css">';
		
		foreach( $posts as $dv_post ) {
			
			$post_id = $dv_post->ID;
			
			$cbc_textcolor = get_post_meta( $post_id, 'post_closebtn_text_color', true );
			$cbc_bgcolor = get_post_meta( $post_id, 'post_closebtn_bg_color', true );
			$cbc_fontsize = get_post_meta( $post_id, 'post_closebtn_fontsize', true );
			$cbc_borderradius = get_post_meta( $post_id, 'post_closebtn_borderradius', true );
			$cbc_padding = get_post_meta( $post_id, 'post_closebtn_padding', true );
			
			$customizeclosebtn = get_post_meta( $post_id, 'post_do_customizeclosebtn' );
			if ( isset( $customizeclosebtn[0] ) ) {
				
				$customizeclosebtn = $customizeclosebtn[0];
				
			} else {
				
				continue;
			}
			
			if ( $customizeclosebtn ) {
				
				print '
				.overlay-customclose-btn-' . $post_id . ' {
					color:' . $cbc_textcolor . ' !important;
					background-color:' . $cbc_bgcolor . ' !important;
					font-size:' . $cbc_fontsize . 'px !important;
					padding:' . $cbc_padding . 'px !important;
					-moz-border-radius:' . $cbc_borderradius . '% !important;
					-webkit-border-radius:' . $cbc_borderradius . '% !important;
					-khtml-border-radius:' . $cbc_borderradius . '% !important;
					border-radius:' . $cbc_borderradius . '% !important;
				}
				';
			}
		}
		
		print '</style>';
	}
	
	
	/* Ignore repeated ids and print overlays */
	$overlays = $overlays_in_post + $overlays_in_menus + $overlays_with_css_trigger + $overlays_with_url_trigger + $overlays_with_automatic_trigger;
	
	if ( is_array( $overlays ) && count( $overlays ) > 0 ) {
		
		foreach( $overlays as $overlay_id => $idx ) {
			
			print showOverlay( $overlay_id );
		}
	}
	
	print '</div>';
	
	?>
	<script type="text/javascript">
    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
	var diviAjaxUrl = '<?php print plugins_url( 'ajax-handler-wp.php' , __FILE__ ); ?>';
    </script>
	<?php
	
	wp_register_script('exit-intent', plugins_url( 'overlay-effects/js/jquery.exitintent.min.js', __FILE__),array("jquery"));
	wp_register_script('custom_js', plugins_url('overlay-effects/js/custom.js', __FILE__),array("jquery"));
	
	wp_enqueue_script('exit-intent');
    wp_enqueue_script('custom_js');
}


function get_all_wordpress_menus(){
    return get_terms( 'nav_menu', array( 'hide_empty' => true ) ); 
}


function prepareOverlays( $key = NULL )
{
    if ( !$key ) {
        return NULL;
	}
	
	// it is an url with hash overlay?
	if ( strpos( $key, "#" ) !== false ) {
		
		$exploded_url = explode( "#", $key );
		
		if ( isset( $exploded_url[1] ) ) {
			
			$key = str_replace( 'overlay-', '', $exploded_url[1] );
		}
	}
	
	$key = str_replace( 'overlay_', '', $key );
	$key = str_replace( 'unique_id_', '', $key );
	$key = str_replace( 'divioverlay-', '', $key );
	
    if ( $key == '' ) {
        return NULL;
	}
	
	if ( !overlayIsPublished( $key ) ) {
		
		return NULL;
	}
	
	return $key;
}

function overlayIsPublished( $key ) {
	
	$post = get_post_status( $key );
	
	if ( $post != 'publish' ) {
		
		return FALSE;
	}
	
	return TRUE;
}

add_action('wp_ajax_my_action', 'my_action_callback');
add_action('wp_ajax_nopriv_my_action', 'my_action_callback');
function my_action_callback() {	
	$post_data = get_post($_POST['overlay_id']);
	//print_r($post_data);
	//$overlay_effect = get_post_meta($post_data->ID,'_et_pb_overlay_effect',true);
	echo $post_data->post_content;
	die;
}

// Load the API Key library if it is not already loaded. Must be placed in the root plugin file.
if ( ! class_exists( 'do_lm' ) ) {

    //require_once( plugin_dir_path( __FILE__ ) . 'do_lm.php' );

    // Uncomment next line if this is a theme
    // require_once( get_stylesheet_directory() . 'am-license-menu.php' );

    /**
     * @param string $file             Must be __FILE__ from the root plugin file, or theme functions file.
     * @param string $software_title   Must be exactly the same as the Software Title in the product.
     * @param string $software_version This product's current software version.
     * @param string $plugin_or_theme  'plugin' or 'theme'
     * @param string $api_url          The URL to the site that is running the API Manager. Example: https://www.toddlahman.com/
     *
     * @return \AM_License_Submenu|null
     */
    //do_lm::instance( __FILE__, 'Divi Overlays', '2.0', 'plugin', 'https://divilife.com/' );
}
?>