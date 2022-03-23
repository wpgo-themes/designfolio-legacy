<?php

/**
 * Bootstrap file for the Platinum features.
 *
 * @since 0.1.0
 */

/* Load opt-in widget class. */
if ( file_exists( get_template_directory() . '/includes/modules/classes/widgets/widget-opt-in.php' ) ) {
	add_action( 'widgets_init', 'pc_register_platinum_widgets' ); /* Register opt-in widget. */
}

function pc_register_platinum_widgets() {

	require_once( get_template_directory() . '/includes/modules/classes/widgets/widget-opt-in.php' );
	register_widget( 'opt_in_widget' );
}

/* Load content templates. */
if ( file_exists( get_template_directory() . '/includes/modules/content-templates/content-templates.php' ) ) {
	require_once( get_template_directory() . '/includes/modules/content-templates/content-templates.php' );
}

/* Load members features. */
if ( file_exists( get_template_directory() . '/includes/modules/members-area/members_area.php' ) ) {
	require_once( get_template_directory() . '/includes/modules/members-area/members_area.php' );
}

/* Load members shortcode. */
if ( file_exists( get_template_directory() . '/includes/modules/shortcodes/shortcode-members-only.php' ) ) {
	require_once( get_template_directory() . '/includes/modules/shortcodes/shortcode-members-only.php' );
}

?>