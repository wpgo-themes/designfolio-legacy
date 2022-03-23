<?php

// --------------------------------------
//  - Members area page - Theme module -
// --------------------------------------

// Members related hooks
add_filter( 'login_redirect', 'redirect_to_member_area' );
add_action( 'widgets_init', 'register_members_only_sidebar', 11 );
add_action( 'after_switch_theme', 'create_members_area_page' );
add_filter( 'pc_custom_primary_sidebar_pages', 'add_custom_members_area_primary_sidebar_pages' );

// *****************************************
// *** Register Members Page Widget Area ***
// *****************************************

/**
 * Add a framework filter to use portfolio-page.php for Portfolio page primary sidebar.
 *
 * @since 0.1.0
 */
function add_custom_members_area_primary_sidebar_pages( $custom_theme_pages ) {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {
		return;
	}

	$custom_theme_pages['members-page.php'] = 'members-only-page-widget-area';

	return $custom_theme_pages;
}

/*  Callback function for => add_action( 'widgets_init', 'register_members_only_sidebar' ); */
function register_members_only_sidebar() {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {

		// Also remove the 'members-only' shortcode via this hook callback function if necessary
		remove_shortcode( 'members-only' );

		return;
	}

	// MEMBERS PAGE WIDGET AREA
	register_sidebar( array(
		'name'          => __( 'Members Only Page', 'presscoders' ),
		'id'            => 'members-only-page-widget-area',
		'description'   => __( 'The members only page widget area', 'presscoders' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
		'width'         => 'normal'
	) );

}

// **************************************************
// *** Create the Members Area Page Automatically ***
// **************************************************

function create_members_area_page() {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {
		return;
	}

	/* Does page with title 'Members Area' already exist? */
	$page_check = get_page_by_title( 'Members Area' );

	/* Create page object if one doesn't already exist. */
	$members_page = array(
		'post_title'     => 'Members Area',
		'post_content'   => '',
		'template'       => 'members-page.php',
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_category'  => null
	);

	/* Create new page if one doesn't exist with the new title, and published post status. */
	if ( ! isset( $page_check ) || ( isset( $page_check ) && $page_check->post_status != "publish" ) ) {
		$new_page_id = wp_insert_post( $members_page );
		if ( ! empty( $members_page['template'] ) ) {
			update_post_meta( $new_page_id, '_wp_page_template', $members_page['template'] );
		}
	}
}

// ********************************************************
// *** Redirect User Login to Members Page, or Homepage ***
// ********************************************************

// Callback for: add_filter('login_redirect','redirect_to_member_area');
function redirect_to_member_area( $redirect_to ) {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {
		return;
	}

	/* If admin is logged in redirect to WordPress admin dashboard. */
	global $user;
	if ( isset( $user->caps ) ) {
		if ( array_key_exists( 'administrator', $user->caps ) ) {
			return get_admin_url();
		}
	}

	/* Does page with title 'Members Area' exist? */
	$page_check = get_page_by_title( 'Members Area' );

	if ( ! isset( $page_check ) ) {
		/* Go to homepage if we can't find members page. */
		return get_home_url();
	} else {
		/* Redirect to members page if exists, or homepage if not. */
		return get_permalink( $page_check->ID );
	}
}

// ************************************************************************
// *** Add a Section to Each Users Profile Page - Visible Only to Admin ***
// ************************************************************************

/* Use of the frontend as get_the_author_meta('something') or the_author_meta('something'). */

if ( is_admin() ) {
	add_action( 'show_user_profile', 'theme_show_user_fields' );
	add_action( 'edit_user_profile', 'theme_show_user_fields' );
	add_action( 'personal_options_update', 'theme_save_user_fields' );
	add_action( 'edit_user_profile_update', 'theme_save_user_fields' );
}

function theme_show_user_fields( $user ) {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {
		return;
	}

	global $current_user;
	wp_get_current_user();

	$caps = $current_user->roles; /* Get all the users capabilities into a single array. */

	/* Grab first name if it exists, otherwise display username. */
	$display_name = ( empty( $current_user->user_firstname ) ) ? $current_user->user_login : $current_user->user_firstname;

	?>

	<h3><?php _e( 'User Member Content', 'presscoders' ) ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="<?php echo PC_THEME_NAME_SLUG; ?>_unique_user_member_content">Content added by
					<strong><?php echo $display_name; ?></strong></label></th>
			<td>
				<?php
				$cont = get_the_author_meta( PC_THEME_NAME_SLUG . '_unique_user_member_content', $user->ID );
				$args = array( 'textarea_name' => PC_THEME_NAME_SLUG . '_unique_user_member_content' );
				wp_editor( $cont, PC_THEME_NAME_SLUG . '_unique_user_member_content', $args );
				?>
				<br><span class="description">Enter your own content here. This will be displayed on your members area page. Administrators can add their own separate content, or edit your entries directly.</span>
			</td>
		</tr>
	</table>

<?php
}

function theme_save_user_fields( $user_id ) {

	if ( current_theme_supports( 'disable-platinum-members-area' ) ) {
		return;
	}

	update_user_meta( $user_id, PC_THEME_NAME_SLUG . '_unique_user_member_content', ( isset( $_POST[PC_THEME_NAME_SLUG . '_unique_user_member_content'] ) ? $_POST[PC_THEME_NAME_SLUG . '_unique_user_member_content'] : '' ) );
}

?>