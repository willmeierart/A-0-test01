<?php



///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// tonyfelice additions

function elegant_enqueue_css() { wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); }
add_action( 'wp_enqueue_scripts', 'elegant_enqueue_css' );
include('editor/footer-editor.php');
include('editor/login-editor.php');

// allow logout without confirmation
add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result)
{
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : '';
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));;
        header("Location: $location");
        die;
    }
}

// redirect to home after logout
add_action('wp_logout','auto_redirect_after_logout');
function auto_redirect_after_logout(){
	wp_redirect( home_url() );
	exit();
}

// block non-admin access to dashboard
add_action( 'init', 'blockusers_init' );
function blockusers_init() {
    if ( is_admin() && !current_user_can( 'administrator' ) &&
		!( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		wp_redirect( home_url() . "/");
		exit;
    }
}


// get theme directory
$template_directory = get_stylesheet_directory_uri();

// renaming project custom post type
function rename_project_cpt() {
	$portfolioSlug = "Gallery"; ////////////////////////////////////// this is where you edit the portfolio slug
	register_post_type( 'project',
		array(
			'labels' => array(
				'name'          => __( $portfolioSlug, 'divi' ), // change the text portfolio to anything you like
				'singular_name' => __( $portfolioSlug, 'divi' ), // change the text portfolio to anything you like
			),
			'has_archive'  => true,
			'hierarchical' => true,
			'menu_icon'    => 'dashicons-images-alt2',  // you choose your own dashicon
			'public'       => true,
			'rewrite'      => array( 'slug' => strtolower($portfolioSlug), 'with_front' => false ), // change the text portfolio to anything you like
			'supports'     => array(),
		)
	);
}
add_action( 'init', 'rename_project_cpt' );


// override footer credits
if ( ! function_exists( 'et_get_footer_credits' ) ) :
function et_get_footer_credits() {
	$original_footer_credits = et_get_original_footer_credits();
	$disable_custom_credits = et_get_option( 'disable_custom_footer_credits', false );
	if ( $disable_custom_credits ) {
		return '';
	}
	$credits_format = '<p class="az-footerinfo">%1$s</p>';
	$footer_credits = et_get_option( 'custom_footer_credits', '' );
	if ( '' === trim( $footer_credits ) ) {
		return et_get_safe_localization( sprintf( $credits_format, $original_footer_credits ) );
	}
	return et_get_safe_localization( sprintf( $credits_format, $footer_credits ) );
}
endif;

// load child js
if ( !is_admin() ) :
	// register script location, dependencies and version
	wp_register_script('zerojs', $template_directory . '/js/child.js', array('jquery'), '1.0' );

	// enqueue the script
	wp_enqueue_script('zerojs');
endif;


///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// tonyfelice custom modules
function AZ_Custom_Modules(){
	if(class_exists("ET_Builder_Module")){
		include("az-custom-modules.php");
	}
}
function prep_AZ_Custom_Modules(){
	global $pagenow;
	$is_admin = is_admin();
	$action_hook = $is_admin ? 'wp_loaded' : 'wp';
	$required_admin_pages = array( 'edit.php', 'post.php', 'post-new.php', 'admin.php', 'customize.php', 'edit-tags.php', 'admin-ajax.php', 'export.php' ); // list of admin pages where we need to load builder files
	$specific_filter_pages = array( 'edit.php', 'admin.php', 'edit-tags.php' );
	$is_edit_library_page = 'edit.php' === $pagenow && isset( $_GET['post_type'] ) && 'et_pb_layout' === $_GET['post_type'];
	$is_role_editor_page = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'et_divi_role_editor' === $_GET['page'];
	$is_import_page = 'admin.php' === $pagenow && isset( $_GET['import'] ) && 'wordpress' === $_GET['import'];
	$is_edit_layout_category_page = 'edit-tags.php' === $pagenow && isset( $_GET['taxonomy'] ) && 'layout_category' === $_GET['taxonomy'];

	if ( ! $is_admin || ( $is_admin && in_array( $pagenow, $required_admin_pages ) && ( ! in_array( $pagenow, $specific_filter_pages ) || $is_edit_library_page || $is_role_editor_page || $is_edit_layout_category_page || $is_import_page ) ) ) {
		add_action($action_hook, 'AZ_Custom_Modules', 9789);
	}
}
prep_AZ_Custom_Modules();



function get_excerpt(){
$excerpt = get_the_content();
$excerpt = preg_replace(" ([.*?])",'',$excerpt);
$excerpt = strip_shortcodes($excerpt);
$excerpt = strip_tags($excerpt);
$excerpt = substr($excerpt, 0, 50);
$excerpt = substr($excerpt, 0, strripos($excerpt, " "));
$excerpt = trim(preg_replace( '/s+/', ' ', $excerpt));
$excerpt = $excerpt.'... <a href="'.$permalink.'">more</a>';
return $excerpt;
}

