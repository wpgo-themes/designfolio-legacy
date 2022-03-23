<?php

// ------------------------------
//  - [members-only] Shortcode -
// ------------------------------

add_shortcode( 'members-only', 'members_only_shortcode' ); // [members-only]Only logged in users will see this.[/members-only]

// - Callback function for: add_shortcode( 'members-only', 'members_only_shortcode' );
function members_only_shortcode( $atts, $content = null ) {

	/* Extract shortcode attributes, if any specified. */
	extract( shortcode_atts( array(
		'id' => ''
	), $atts ) );

	if ( is_user_logged_in() && ! is_null( $content ) && ! is_feed() ) {
		if ( empty( $id ) ) {
			/* If no user id specified. */
			return wpautop( do_shortcode( $content ) );
		} else {
			$current_user = wp_get_current_user();
			if ( $id == $current_user->ID ) {
				/* Display content for a specific user id. */
				return wpautop( do_shortcode( $content ) );
			} else {
				return '';
			}
		}
	} else {
		return '';
	}

}

?>