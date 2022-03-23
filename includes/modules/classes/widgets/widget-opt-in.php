<?php

// ---------------------
//  Opt-In Widget Class
// ---------------------

class opt_in_widget extends WP_Widget {

	// Constructor - process new widget
	function __construct() {
		$widget_ops = array( 'classname' => PC_THEME_NAME_SLUG . '_opt_in_widget', 'description' => __( 'Send your customers free content when they use this widget, to opt-in to some promotion you are running.', 'presscoders' ) );
		parent::__construct( PC_THEME_NAME_SLUG . '_opt_in_widget', __( 'E-mail Opt-In', 'presscoders' ), $widget_ops );
	}

	// Build widget options form
	function form( $instance ) {
		/* Overrides the default opt-in widget title. */
		if ( ! defined( 'PC_OPTIN_TITLE' ) ) {
			define( 'PC_OPTIN_TITLE', __( 'Download your FREE Plan!', 'presscoders' ) );
		}

		/* Overrides the default opt-in widget description. */
		if ( ! defined( 'PC_OPTIN_DESCRIPTION' ) ) {
			define( 'PC_OPTIN_DESCRIPTION', __( 'Simply enter your first name &amp; e-mail below for instant access to your FREE plan!', 'presscoders' ) );
		}

		/* Overrides the default opt-in widget thank you message. */
		if ( ! defined( 'PC_OPTIN_THANKYOU' ) ) {
			define( 'PC_OPTIN_THANKYOU', '<p>Thanks, your email was sent successfully.</p>Click <a href="#">here</a> to download your FREE plan right now!' );
		}

		$defaults          = array( 'title'             => PC_OPTIN_TITLE,
									'admin_email'       => get_bloginfo( 'admin_email' ),
									'subject'           => PC_THEME_NAME . __( ' Opt-In E-mail', 'presscoders' ),
									'optin_description' => PC_OPTIN_DESCRIPTION,
									'thankyou_message'  => PC_OPTIN_THANKYOU,
									'embed_code'        => '',
									'optin_content'     => ''
		);
		$instance          = wp_parse_args( (array) $instance, $defaults );
		$title             = strip_tags( $instance['title'] );
		$optin_description = $instance['optin_description'];
		$admin_email       = strip_tags( $instance['admin_email'] );
		$subject           = strip_tags( $instance['subject'] );
		$thankyou_message  = $instance['thankyou_message'];
		$embed_code        = $instance['embed_code'];
		$optin_content     = $instance['optin_content'];

		?>
		<h3><?php _e( 'Front End Opt-in Form', 'presscoders' ) ?></h3>
		<p><?php _e( 'Title', 'presscoders' ) ?>:
			<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p><?php _e( 'Opt-In Description', 'presscoders' ) ?>:
			<textarea class="widefat" name="<?php echo $this->get_field_name( 'optin_description' ); ?>" rows="3" cols="12"><?php echo esc_attr( $optin_description ); ?></textarea>
		</p>
		<p><?php _e( 'Thankyou Message', 'presscoders' ) ?>:
			<textarea class="widefat" name="<?php echo $this->get_field_name( 'thankyou_message' ); ?>" rows="4" cols="12"><?php echo esc_attr( $thankyou_message ); ?></textarea>
		</p>

		<h3><?php _e( 'Autoresponder E-mail', 'presscoders' ) ?></h3>
		<p><?php _e( 'Admin E-mail', 'presscoders' ) ?>:
			<input class="widefat" name="<?php echo $this->get_field_name( 'admin_email' ); ?>" type="text" value="<?php echo esc_attr( $admin_email ); ?>" />
		</p>
		<p><?php _e( 'E-mail Subject', 'presscoders' ) ?>:
			<input class="widefat" name="<?php echo $this->get_field_name( 'subject' ); ?>" type="text" value="<?php echo esc_attr( $subject ); ?>" />
		</p>
		<p><?php _e( 'E-mail Content', 'presscoders' ) ?>:
			<textarea class="widefat" name="<?php echo $this->get_field_name( 'optin_content' ); ?>" rows="3" cols="12"><?php echo esc_attr( $optin_content ); ?></textarea>
		</p>

		<h3><?php _e( '3rd Party E-mail Service', 'presscoders' ) ?></h3>
		<p><?php _e( 'Embed Code (optional)', 'presscoders' ) ?>:
			<textarea class="widefat" name="<?php echo $this->get_field_name( 'embed_code' ); ?>" rows="2" cols="12"><?php echo esc_attr( $embed_code ); ?></textarea><br /><?php _e( 'Any 3rd party code added (Aweber, Mailchimp etc.) will override the default opt-in widget settings above.', 'presscoders' ) ?>
		</p>
	<?php
	}

	// Save widget settings
	function update( $new_instance, $old_instance ) {
		$instance                      = $old_instance;
		$instance['title']             = strip_tags( $new_instance['title'] );
		$instance['thankyou_message']  = $new_instance['thankyou_message'];
		$instance['optin_description'] = $new_instance['optin_description'];
		$instance['embed_code']        = $new_instance['embed_code'];
		$instance['optin_content']     = $new_instance['optin_content'];

		$admin_email = trim( strip_tags( $new_instance['admin_email'] ) );
		if ( ! isset( $admin_email ) || empty( $admin_email ) ) {
			$admin_email = trim( strip_tags( get_bloginfo( 'admin_email' ) ) );
		}
		$instance['admin_email'] = $admin_email;
		$instance['subject']     = strip_tags( $new_instance['subject'] );

		return $instance;
	}

	// Display widget
	function widget( $args, $instance ) {
		extract( $args );

		// get the widget options
		$title             = $instance['title'];
		$admin_email       = $instance['admin_email'];
		$email_subject     = $instance['subject'];
		$optin_description = $instance['optin_description'];
		$thankyou_message  = $instance['thankyou_message'];
		$embed_code        = $instance['embed_code'];
		$optin_content     = $instance['optin_content'];

		// =============================================
		// START - Handle the submitted opt-in form code
		// =============================================

		$adminError = '';
		$nameError  = '';
		$emailError = '';

		if ( isset( $_POST['submitted'] ) ) {

			$admin_email = trim( $admin_email );
			if ( empty( $admin_email ) ) {
				$adminError = 'Widget admin email is empty. Please enter valid email, and try again.';
				$hasError   = true;
			}

			if ( trim( $_POST['optinName'] ) === '' ) {
				$nameError = 'Please enter your name.';
				$hasError  = true;
				$name      = "";
			} else {
				$name = trim( $_POST['optinName'] );
				$name = esc_attr( $name );
			}

			if ( trim( $_POST['optinEmail'] ) === '' ) {
				$emailError = 'Please enter your email address.';
				$hasError   = true;
				$optinEmail = '';
			} else {
				if ( ! eregi( "^[A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4}$", trim( $_POST['optinEmail'] ) ) ) {
					$emailError = 'You entered an invalid email address.';
					$hasError   = true;
					$optinEmail = '';
				} else {
					$optinEmail = trim( $_POST['optinEmail'] );
					$optinEmail = esc_attr( $optinEmail );
				}
			}

			if ( ! isset( $hasError ) ) {
				$emailToAdmin = $admin_email;
				$emailToUser  = $optinEmail;
				$body_user    = $optin_content;

				if ( ! isset( $emailToAdmin ) || ( $emailToAdmin == '' ) ) {
					$emailToAdmin = get_option( 'admin_email' ); /* Use WordPress admin email as a fallback. */
				}
				$subject    = '[' . $email_subject . '] To ' . $name;
				$body_admin = 'Name: ' . $name . '<br />Email: ' . $optinEmail;

				/* To send HTML in the e-mail, the Content-type header must be set. */
				$content_type_header = 'MIME-Version: 1.0' . "\r\n";
				$content_type_header .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

				$user_headers = $content_type_header;
				$user_headers .= 'To: ' . $name . ' <' . $optinEmail . '>' . "\r\n";
				$user_headers .= 'From: ' . get_bloginfo( 'name' ) . ' <' . $emailToAdmin . '>' . "\r\n";
				$user_headers .= 'Reply-To: ' . $emailToAdmin;

				$admin_headers = $content_type_header;
				$admin_headers .= 'To: ' . get_bloginfo( 'name' ) . ' <' . $emailToAdmin . '>' . "\r\n";
				$admin_headers .= 'From: ' . $name . ' <' . $optinEmail . '>' . "\r\n";
				$admin_headers .= 'Reply-To: ' . $optinEmail;

				mail( $emailToUser, $subject, $body_user, $user_headers ); /* E-mail to user. */
				mail( $emailToAdmin, $subject, $body_admin, $admin_headers ); /* E-mail to admin. */
				$emailSent = true;
			}
		}

		// =============================================
		// END - Handle the submitted opt-in form code
		// =============================================

		echo $before_widget;

		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		};
		?>

		<?php if ( isset( $emailSent ) && $emailSent == true ) { ?>
			<div class="thanks">
				<?php echo $thankyou_message; ?>
			</div>
		<?php } else { ?>
			<?php if ( isset( $hasError ) ) { ?>
				<p class="optin-error"><?php _e( 'Sorry, an error occured.', 'presscoders' ) ?><p>
				<?php if ( $adminError != '' ) { ?>
					<p class="optin-error"><?php echo $adminError; ?></p>
				<?php } ?>
			<?php } ?>

			<div class="opt-in-container">
				<?php if ( empty( $embed_code ) ) : ?>

					<?php
					if ( function_exists( 'currURL' ) ) {
						/* Legacy call to old framework function. */
						$current_url = currURL();
					} else {
						/* New framework function call. */
						$current_url = PC_Utility::currURL();
					}
					?>
					<form action="<?php echo $current_url; /* Current page URL. */ ?>" method="post">

						<p class="optin-description"><?php echo $optin_description; ?></p>

						<div class="optin-field">
							<label for="optinName"><?php _e( 'Name', 'presscoders' ) ?>: </label>
							<input class="widefat required requiredField" type="text" name="optinName" value="<?php if ( isset( $_POST['optinName'] ) ) {
								echo $name;
							} ?>" />
							<?php if ( $nameError != '' ) { ?>
								<span class="optin-error"><?php echo $nameError; ?></span>
							<?php } ?>
						</div>

						<div class="optin-field">
							<label for="optinEmail"><?php _e( 'E-mail', 'presscoders' ) ?>: </label>
							<input class="widefat required requiredField email" type="text" name="optinEmail" value="<?php if ( isset( $_POST['optinEmail'] ) ) {
								echo $optinEmail;
							} ?>" />
							<?php if ( $emailError != '' ) { ?>
								<span class="optin-error"><?php echo $emailError; ?></span>
							<?php } ?>
						</div>
						<div class="optin-field">
							<input type="submit" value="Submit" class="button optinbtn" />
						</div>

						<input type="hidden" name="submitted" value="true" />
					</form>

				<?php else : ?>

					<?php echo $embed_code; ?>

				<?php endif; ?>
			</div> <!-- .opt-in-container -->
		<?php } ?>

		<?php
		echo $after_widget;
	}
}

?>