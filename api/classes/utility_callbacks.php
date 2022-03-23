<?php

/**
 * Framework utility callbacks class.
 *
 * Contains general WordPress hook callback functions.
 *
 * @todo  Most of these need moving to better locations within the framework. Maybe tied into add_theme_support() type added functionality?
 *
 * @since 0.1.0
 */
class PC_Utility_Callbacks {

	/**
	 * PC_Utility callbacks class constructor.
	 *
	 * @since 0.1.0
	 */
	public function __construct() {

		add_action( 'login_head', array( &$this, 'theme_custom_login_logo' ), 999 );
		add_filter( 'wp_page_menu_args', array( &$this, 'theme_page_menu_args' ) );
		add_action( 'wp_head', array( &$this, 'theme_favicon' ) );
		add_filter( 'get_the_excerpt', array( &$this, 'theme_trim_excerpt' ) );
		add_filter( 'excerpt_length', array( &$this, 'theme_new_excerpt_length' ) );
		add_action( 'pc_before_head', array( &$this, 'pc_custom_before_head' ) );
		add_action( 'pc_before_head', array( &$this, 'clean_head_tag' ) );
		add_action( 'load-widgets.php', array( &$this, 'load_livequery' ) );
		add_action( 'pc_after_content_open', array( &$this, 'front_page_main_content' ) );
		add_action( 'pre_get_posts', array( $this, 'author_archive_include_cpt' ) );

		/* Priority set to 11 as it needs to run after all other 'pc_before_content' callbacks. */
		add_action( 'pc_before_content', array( &$this, 'sidebar_before' ), 11 );

		/* Priority set to 11 as it needs to run after all other 'pc_after_content' callbacks. */
		add_action( 'pc_after_content', array( &$this, 'sidebar_after' ), 11 );
	}

	/**
	 * Customise the login/register screen.
	 *
	 * Use theme logo if one has been added in theme options. If none specified then it defaults
	 * back to the WordPress logo. The title and logo links are also changed to link to the site
	 * home page, and have a relevant title.
	 *
	 * @since 0.1.0
	 */
	public function theme_custom_login_logo() {

		$options = get_option( PC_OPTIONS_DB_NAME );

		/* @todo When all themes are using the customizer logo uploader then delete this first if statement and contents. Then add this function to the PC_Theme_Customizer class and update the callback reference. */
		if ( defined( 'PC_LOGO_CHK_OPTION_NAME' ) && isset( $options[PC_LOGO_CHK_OPTION_NAME] ) && $options[PC_LOGO_CHK_OPTION_NAME] ) {

			/* Show logo image preview if url specified in textbox and PHP allow_url_open setting enabled. */
			if ( $options[PC_LOGO_URL_OPTION_NAME] != "" && ini_get( 'allow_url_fopen' ) ) {

				$logo_url = $options[PC_LOGO_URL_OPTION_NAME];

				if ( function_exists( 'getimagesize' ) && $size = @getimagesize( $logo_url ) ) { /* We appear to have an image. */

					// Because the admin logo is inside a fixed width div (326px), we have to align the logo with a calculated negative margin. The formula below takes into account images with a size less than, or larger than the fixed width div
					$margin_correction = (int) ( ( ( $size[0] - 326 ) / 2 ) * ( - 1 ) );

					/* Use logo from theme options. */
					?>
					<style type="text/css">
						.login h1 a {
							margin:           10px <?php echo $margin_correction; ?>px;
							background-image: url('<?php echo $options[ PC_LOGO_URL_OPTION_NAME ]; ?>') !important;
							background-size:  <?php echo $size[0]; ?>px <?php echo $size[1]; ?>px;
							width:            <?php echo $size[0]; ?>px !important;
							height:           <?php echo $size[1]; ?>px !important;
						}
					</style>
					<?php

					add_filter( 'login_headerurl', array( &$this, 'custom_wp_login_url' ) ); // Add link to home page, rather than WordPress.org
					add_filter( 'login_headertitle', array( &$this, 'custom_wp_login_title' ) ); // Change the title attribute
				}
			}
		} else {

			global $pc_customizer_defaults;
			$pc_txt_logo_url = get_theme_mod( 'pc_txt_logo_url', $pc_customizer_defaults['pc_txt_logo_url'] );

			// Show image logo if one defined
			if ( ! empty( $pc_txt_logo_url ) ) {

				if ( function_exists( 'getimagesize' ) && $size = @getimagesize( $pc_txt_logo_url ) ) { /* We appear to have an image. */

					// Because the admin logo is inside a fixed width div (326px), we have to align the logo with a calculated negative margin. The formula below takes into account images with a size less than, or larger than the fixed width div
					$margin_correction = (int) ( ( ( $size[0] - 326 ) / 2 ) * ( - 1 ) );

					/* Use logo from theme options. */
					?>
					<style type="text/css">
						.login h1 a {
							margin:           10px <?php echo $margin_correction; ?>px;
							background-image: url('<?php echo $pc_txt_logo_url; ?>') !important;
							background-size:  <?php echo $size[0]; ?>px <?php echo $size[1]; ?>px;
							width:            <?php echo $size[0]; ?>px !important;
							height:           <?php echo $size[1]; ?>px !important;
						}
					</style>
					<?php

					add_filter( 'login_headerurl', array( &$this, 'custom_wp_login_url' ) ); // Add link to home page, rather than WordPress.org
					add_filter( 'login_headertitle', array( &$this, 'custom_wp_login_title' ) ); // Change the title attribute
				}
			}
		}
	}

	/**
	 * Define a default homepage link for our wp_nav_menu() fallback, wp_page_menu().
	 *
	 * @since 0.1.0
	 */
	public function theme_page_menu_args( $args ) {
		$args['show_home'] = true;

		return $args;
	}

	/**
	 * Enable favicon.
	 *
	 * @since 0.1.0
	 */
	public function theme_favicon() {

		$favicon = PC_Utility::get_custom_favicon();

		/* Don't output favicon tag if no favicon found. */
		if ( empty( $favicon ) ) {
			return;
		}
		?>
		<link rel="shortcut icon" href="<?php echo $favicon; ?>" /><?php
	}

	/**
	 * Replace the [...] after the excerpt, if it exists.
	 *
	 * @since 0.1.0
	 */
	public function theme_trim_excerpt( $text ) {
		return str_replace( ' [...]', '..', $text );
	}

	/**
	 * Customize the excerpt length.
	 *
	 * @since 0.1.0
	 */
	public function theme_new_excerpt_length( $length ) {
		return $length;
	}

	/**
	 * Custom login url.
	 *
	 * @since 0.1.0
	 */
	public function custom_wp_login_url( $url ) {
		return home_url();
	}

	/**
	 * Custom login title.
	 *
	 * @since 0.1.0
	 */
	public function custom_wp_login_title( $title ) {
		return get_option( 'blogdescription' );
	}

	/**
	 * Adds HTML 5 doctype and tags to the header.
	 *
	 * @since 0.1.0
	 */
	public function pc_custom_before_head() {
		?><!doctype html>
		<!--[if lt IE 7]>
		<html class="no-js ie6" lang="en"> <![endif]-->
		<!--[if IE 7]>
		<html class="no-js ie7" lang="en"> <![endif]-->
		<!--[if IE 8]>
		<html class="no-js ie8" lang="en"> <![endif]-->
		<!--[if gt IE 8]><!-->
		<html class="no-js" <?php language_attributes(); ?>> <!--<![endif]-->
	<?php
	}

	/**
	 * Adds custom CSS from theme options into the header to override the main theme style sheet.
	 *
	 * @since 0.1.0
	 */
	public function pc_custom_wp_head() {

		$options    = get_option( PC_OPTIONS_DB_NAME );
		$custom_css = $options['txtarea_custom_css'];
		if ( ! empty( $custom_css ) ) {
			echo "<!-- " . PC_THEME_NAME . " user defined custom CSS -->\n";
			echo "<style type=\"text/css\">" . $custom_css . "</style>\n";
		}
	}

	/**
	 * Clean up the header and remove some of the default content added by WordPress.
	 *
	 * @todo  This should be flexible, and each added/removed as required (via add_theme_support?)
	 *
	 * @since 0.1.0
	 */
	public function clean_head_tag() {
		remove_action( 'wp_head', 'feed_links_extra', 3 ); // Displays the links to the extra feeds such as category feeds
		remove_action( 'wp_head', 'feed_links', 2 ); // Displays the links to the general feeds: Post and Comment Feed
		remove_action( 'wp_head', 'rsd_link' ); // Displays the link to the Really Simple Discovery service endpoint, EditURI link
		remove_action( 'wp_head', 'wlwmanifest_link' ); // Displays the link to the Windows Live Writer manifest file.
		remove_action( 'wp_head', 'index_rel_link' ); // Displays the rel index link
		remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version
	}

	/**
	 * Check if color scheme cookie needs setting.
	 *
	 * If a new color scheme has been selected in the color scheme switcher widget then set cookie.
	 * This was originally inside the function to enqueue the color scheme but needed to be run before any
	 * HTML output sent, as the setcookie() function is being used.
	 *
	 * @since 0.1.0
	 */
	public function set_color_scheme_cookie() {

		global $pagenow;

		if ( ! is_admin() && $pagenow != 'wp-login.php' ) { /* Only run this on the front, not admin or login page. */

			if ( isset( $_POST['color_scheme_submitted'] ) ) {
				/* Color scheme switcher widget form was submitted. */

				/* Set cookie to the drop down box value. */
				$expire = time() + 60 * 60 * 24; // 24 hours
				//$expire = time()+60; // 60 seconds, used this expiry time to test the cookie is expiring as it should
				$cs = $_POST[PC_THEME_NAME_SLUG . '_color_scheme_widget_dropdown'];
				setcookie( PC_THEME_NAME_SLUG . "_color_scheme", $cs, $expire, COOKIEPATH, COOKIE_DOMAIN );
				$_COOKIE[PC_THEME_NAME_SLUG . "_color_scheme"] = $cs; /* Needed as the cookie won't be accessible from $_COOKIE until the next page load. */
			}
		}
	}

	/**
	 * Delete color scheme cookie.
	 *
	 * @since 0.1.0
	 */
	public function delete_color_scheme_cookie() {

		/* If color scheme cookie exists, delete it. */
		if ( isset( $_COOKIE[PC_THEME_NAME_SLUG . "_color_scheme"] ) ) {
			setcookie( PC_THEME_NAME_SLUG . "_color_scheme", '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
			$_COOKIE[PC_THEME_NAME_SLUG . "_color_scheme"] = null;
		}
	}

	/**
	 * Controls which color scheme style sheet to enqueue depending on theme options and/or color
	 * switcher widget.
	 *
	 * Add color scheme style sheet to header BEFORE main theme style sheet. Makes it easier use a child theme to override main theme AND color scheme styles.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_color_scheme() {

		global $pagenow;

		/* Only need this test if this function is NOT a callback via the 'wp enqueue script' hook. */
		if ( ! is_admin() && $pagenow != 'wp-login.php' ) { /* Only run this on the front, not admin or login page. */

			/* Add in the color scheme from theme options unless color switcher cookie set, then use it in preference to the theme color option. */
			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( is_array( $sidebars_widgets ) ) {
				foreach ( $sidebars_widgets as $sidebar => $widgets ) {
					if ( 'wp_inactive_widgets' == $sidebar ) {
						continue;
					}
					if ( is_array( $widgets ) ) {
						foreach ( $widgets as $widget ) {
							$pos = strpos( $widget, 'pc_color_scheme_switcher_widget' );
							if ( $pos !== false ) {
								$color_scheme_widget_active = 1;
							}
						}
					}
				}
			}

			if ( ! ( empty( $color_scheme_widget_active ) && isset( $color_scheme_widget_active ) ) ) {
				/* There is at least one instance of the color switcher widget so use the widget color scheme selection in preference to admin (if cookie exists). */
				if ( isset( $_COOKIE[PC_THEME_NAME_SLUG . "_color_scheme"] ) ) {
					/* Set color scheme from cookie value. */
					$color_scheme = $_COOKIE[PC_THEME_NAME_SLUG . "_color_scheme"];
				} else {
					/* Set color scheme from theme options. */
					$options      = get_option( PC_OPTIONS_DB_NAME );
					$color_scheme = $options[PC_COLOR_SCHEME_DROPDOWN];
				}
			} else {
				/* Set color scheme from theme options. */
				$options      = get_option( PC_OPTIONS_DB_NAME );
				$color_scheme = $options[PC_COLOR_SCHEME_DROPDOWN];
			}

			/* Register and Enqueue color scheme style. */
			wp_register_style( 'pc-color-scheme-stylesheet', PC_THEME_ROOT_URI . '/includes/css/color_schemes/' . $color_scheme . '.css' );
			wp_enqueue_style( 'pc-color-scheme-stylesheet' );
		}
	}

	/**
	 * Add main theme style sheet to header AFTER the color scheme style sheet.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_main_style_sheet() {

		/* Register and enqueue main theme style sheet before the color scheme style sheet (if one used in the current theme). */
		wp_register_style( 'pc-theme-stylesheet', get_stylesheet_directory_uri() . '/style.css' );
		wp_enqueue_style( 'pc-theme-stylesheet' );
	}

	/**
	 * Add Live Query to widgets.php to fix the new widget instance bug.
	 *
	 * @todo  Add this to the relevant place if the nivo slider widget is included with the theme
	 * (via add_theme_support).
	 *
	 * @since 0.1.0
	 */
	public function load_livequery() {
		wp_enqueue_script( 'livequery', PC_THEME_ROOT_URI . '/api/js/misc/livequery/jquery.livequery.js', array( 'jquery' ) );
	}

	/**
	 * Renders the sidebar before content div.
	 *
	 * @since 0.1.0
	 */
	public function sidebar_before() {

		PC_Utility::render_sidebar_before( PC_DEFAULT_LAYOUT_THEME_OPTION );
	}

	/**
	 * Renders the sidebar after content div.
	 *
	 * @since 0.1.0
	 */
	public function sidebar_after() {

		PC_Utility::render_sidebar_after( PC_DEFAULT_LAYOUT_THEME_OPTION );
	}

	/**
	 * Add custom defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_calloutbar_theme_option_defaults() {

		global $pc_default_options, $pc_default_off_checkboxes;

		define( "PC_CALLOUT_BAR_TEXT", "Like what you see? What are you waiting for? [button size=\"big\"]Sign Up Now[/button]" ); /* Default call out bar text. */

		$pc_default_options["chk_show_callout_bar"]         = "1";
		$pc_default_options["chk_show_callout_on_homepage"] = "0";
		$pc_default_options["txtarea_callout_bar"]          = PC_CALLOUT_BAR_TEXT;
		$pc_default_options["txt_exclude_callout_bar"]      = "";

		$pc_default_off_checkboxes["chk_show_callout_bar"] = "0";
	}

	/**
	 * Add custom defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_fancybox_theme_option_defaults() {

		global $pc_default_options, $pc_default_off_checkboxes;
		$pc_default_options["chk_enable_fancybox"]        = "1";
		$pc_default_off_checkboxes["chk_enable_fancybox"] = "0";
	}

	/**
	 * Add custom defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_seo_theme_option_defaults() {

		global $pc_default_options;

		if ( ! defined( 'PC_SEO_SETTINGS_CHECKBOX' ) ) {
			define( "PC_SEO_SETTINGS_CHECKBOX", "chk_seo_settings" );
		}

		$pc_default_options[PC_SEO_SETTINGS_CHECKBOX] = null;
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_seo_theme_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>

		<div class="box">
			<label><input id="theme_options_seo_settings" name="<?php echo PC_OPTIONS_DB_NAME; ?>[<?php echo PC_SEO_SETTINGS_CHECKBOX; ?>]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options[PC_SEO_SETTINGS_CHECKBOX] ) ) {
					checked( '1', $options[PC_SEO_SETTINGS_CHECKBOX] );
				} ?> /> Enable SEO Settings
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Uncheck to disable theme SEO settings if you are using a dedicated SEO plugin" />
			</label>
		</div>
	<?php
	}

	/**
	 * Add custom defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_fitvids_theme_option_defaults() {

		global $pc_default_options;
		$pc_default_options["chk_enable_fitvids"] = "0";
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_fitvids_theme_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>
		<div class="box">
			<label><input id="theme_options_fitvids_check" name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_enable_fitvids]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_enable_fitvids'] ) ) {
					checked( '1', $options['chk_enable_fitvids'] );
				} ?> />Enable FitVids?</label>
		</div>
	<?php
	}

	/**
	 * Add FitVids jQuery Plugin for responsive videos.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_fitvids() {

		/* Don't enqueue these scripts on admin pages. */
		if ( ! is_admin() ) {
			wp_register_script( 'fitvids-js', PC_THEME_ROOT_URI . '/api/js/misc/jquery.fitvids.js', array( 'jquery' ) );
			wp_register_script( 'fitvids-custom-js', PC_THEME_ROOT_URI . '/api/js/presscoders/custom-fitvids.js', array( 'fitvids-js' ) );

			wp_enqueue_script( 'fitvids-js' );
			wp_enqueue_script( 'fitvids-custom-js' );
		}
	}

	/**
	 * Add Fancybox lightbox for images and galleries.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_fancybox() {

		/* Don't enqueue these scripts on admin pages. */
		if ( ! is_admin() ) {
			/* Fancybox scripts. */
			wp_register_script( 'fancybox', PC_THEME_ROOT_URI . '/api/js/lightboxes/fancybox-1.3.4/jquery.fancybox-1.3.4.pack.js', array( 'jquery' ) );
			wp_register_script( 'custom-fancybox', PC_THEME_ROOT_URI . '/api/js/presscoders/custom-fancybox.js', array( 'fancybox' ) );
			wp_register_script( 'jquery-easing', PC_THEME_ROOT_URI . '/api/js/lightboxes/fancybox-1.3.4/jquery.easing-1.3.pack.js', array( 'jquery' ) );
			wp_register_script( 'jquery-mousewheel', PC_THEME_ROOT_URI . '/api/js/lightboxes/fancybox-1.3.4/jquery.mousewheel-3.0.4.pack.js', array( 'jquery' ) );
			wp_enqueue_script( 'fancybox' );
			wp_enqueue_script( 'custom-fancybox' );
			wp_enqueue_script( 'jquery-easing' );
			wp_enqueue_script( 'jquery-mousewheel' );

			/* Fancybox styles. */
			wp_register_style( 'fancybox-stylesheet', PC_THEME_ROOT_URI . '/api/js/lightboxes/fancybox-1.3.4/jquery.fancybox-1.3.4.css' );
			wp_enqueue_style( 'fancybox-stylesheet' );
		}
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_fancybox_theme_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>
		<div class="box">
			<label><input id="theme_options_fancybox_check" name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_enable_fancybox]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_enable_fancybox'] ) ) {
					checked( '1', $options['chk_enable_fancybox'] );
				} ?> />Enable Fancybox?</label>
		</div>
	<?php
	}

	/**
	 * Add custom JS/jQuery for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_theme_option_js_callout_bar() {
		?>

		<script language="javascript">
			// Toggle display of callout bar extra options if display callout bar option selected
			jQuery(document).ready(function ($) {
				// Sync the toggle with the state of the callout bar checkbox
				if ($('#theme_options_callout_bar_check').attr('checked'))
					$("#theme_options_callout_bar").css("display", "table-row");
				else
					$("#theme_options_callout_bar").css("display", "none");

				// Toggles the state of the custom logo checkbox and displays the upload text box/buttons
				$("#theme_options_callout_bar_check").click(function () {
					$("#theme_options_callout_bar").toggle("100");
				});
			});
		</script>

	<?php
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * This theme option goes into position of this particular
	 *
	 * @since 0.1.0
	 */
	public function set_callout_theme_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>
		<!-- Callout Bar Option -->
		<div class="ltinfo">

			<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/callout-icon.png'; ?>" width="32" height="32" class="optionsicon" />

			<h3>Callout Bar</h3>

			<p>Display and customize the Callout Bar.</p>

		</div><!-- .ltinfo -->

		<div class="rtoptions">

			<div class="box">
				<label><input id="theme_options_callout_bar_check" name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_show_callout_bar]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_show_callout_bar'] ) ) {
						checked( '1', $options['chk_show_callout_bar'] );
					} ?> />Display callout bar</label>
			</div>

			<div id="theme_options_callout_bar">
				<div class="box">
					<textarea name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_callout_bar]" rows="3" class="gray" type='textarea'><?php echo $options['txtarea_callout_bar']; ?></textarea>
				</div>

				<div class="box">
					<label><input name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_show_callout_on_homepage]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_show_callout_on_homepage'] ) ) {
							checked( '1', $options['chk_show_callout_on_homepage'] );
						} ?> />Show on front page</label>
				</div>

				<div class="box">
					<label>Exclude post/page:
						<input type="text" class="gray" style="width: 189px;" name="<?php echo PC_OPTIONS_DB_NAME; ?>[txt_exclude_callout_bar]" value="<?php echo $options['txt_exclude_callout_bar']; ?>" />
						<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Exclude the callout bar from displaying on posts/pages by entering a comma separated post/page ID's" />
					</label>
				</div>
			</div>
			<!-- #theme_options_callout_bar -->

		</div><!-- .rtoptions -->

		<div class="line"></div>

	<?php
	}

	/**
	 * Add code to footer to render the callout bar if actived in theme.
	 *
	 * This theme option goes into position of this particular
	 *
	 * @since 0.1.0
	 */
	public function render_callout_bar() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		global $pc_post_id;
		global $pc_template;
		global $pc_is_front_page;

		$str_post_IDs  = $options['txt_exclude_callout_bar'];
		$post_id_array = explode( ",", $str_post_IDs );

		// Show the callout bar, if switched on
		if ( isset( $options['chk_show_callout_bar'] ) && $options['chk_show_callout_bar'] ) {

			// ..then for all non excluded post/pages. Front page display optional
			if ( ! ( $pc_is_front_page && empty( $pc_template ) && ! isset( $options['chk_show_callout_on_homepage'] ) ) ) {

				// don't show callout bar if post/page id is in the exclude list
				if ( ! in_array( $pc_post_id, $post_id_array ) ) {

					// show the callout bar if there is some text defined in theme options
					if ( ! empty( $options['txtarea_callout_bar'] ) && $options['txtarea_callout_bar'] != "" ) {
						?>

						<div id="callout">
							<div class="co-inside">
								<?php echo "<h2>" . do_shortcode( $options['txtarea_callout_bar'] ) . "</h2>"; ?>
							</div>
						</div>

					<?php
					} // endif
				}

			} //endif

		} //endif
	}

	/**
	 * Add code to footer to render the footer links (year, privacy policy).
	 *
	 * @since 0.1.0
	 */
	public function render_footer_links() {

		// Add this to a hook (e.g. before_footer_id_close)
		$options = get_option( PC_OPTIONS_DB_NAME );

		// Grabs the footer links from theme options. If they don't exist then add a default.
		$footer_links = $options['txtarea_footer_links'];
		if ( $footer_links != "" ) {
			echo do_shortcode( $footer_links ); // do_action makes sure shortcodes are processed
		}
	}

	/**
	 * Display breadcrumb trail.
	 *
	 * @since 0.1.0
	 */
	public function display_breadcrumb_trail() {

		/* Show breadcrumb trail on all pages apart from front page. */
		if ( ! is_front_page() ) {
			/* Include the bread-crumb trail script */
			if ( file_exists( get_template_directory() . '/includes/misc/breadcrumb-trail.php' ) ) {
				require_once( get_template_directory() . '/includes/misc/breadcrumb-trail.php' );
			}

			$options = get_option( PC_OPTIONS_DB_NAME );
			if ( isset( $options['chk_show_breadcrumbs'] ) ) {
				echo breadcrumb_trail();
			}
		}
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * This theme option goes into position of this particular
	 *
	 * @since 0.1.0
	 */
	public function set_breadcrumb_theme_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>

		<div class="box">
			<label><input name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_show_breadcrumbs]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_show_breadcrumbs'] ) ) {
					checked( '1', $options['chk_show_breadcrumbs'] );
				} ?> />Show Breadcrumb Trail?</label>
		</div>

	<?php
	}

	/**
	 * Add widgets in the before content widget area to the front page only.
	 *
	 * @since 0.1.0
	 */
	public function front_page_before_content() {
		global $pc_is_front_page, $pc_home_page, $pc_page_on_front;

		if ( $pc_is_front_page || ( $pc_home_page && $pc_page_on_front == 0 ) ) {
			if ( is_active_sidebar( 'front-page-before-content-widget-area' ) ) :
				echo "<div id=\"before-content\">";
				echo "<div id=\"front-page-before-content-widget-area\" class=\"widget-area\">";
				dynamic_sidebar( 'front-page-before-content-widget-area' );
				echo "</div>";
				echo "</div>";
			endif;
		}
	}

	/**
	 * Add widgets in the before content widget area to the front page only.
	 *
	 * @since 0.1.0
	 */
	public function render_homepage_bar() {
		global $pc_is_front_page, $pc_home_page, $pc_page_on_front;

		$options = get_option( PC_OPTIONS_DB_NAME );

		if ( $pc_is_front_page || ( $pc_home_page && $pc_page_on_front == 0 ) ) {
			if ( ! empty( $options['txtarea_homepage_bar'] ) ) :
				?>
				<section class="pc-flush home-bar">
					<div class="pc-flush-inside">
						<?php echo $options['txtarea_homepage_bar']; ?>
					</div>
				</section>
			<?php
			endif;
		}
	}

	/**
	 * Add widgets in the main content widget area to the front page only.
	 *
	 * @since 0.1.0
	 */
	public function front_page_main_content() {
		global $pc_is_front_page, $pc_home_page, $pc_page_on_front;

		if ( $pc_is_front_page || ( $pc_home_page && $pc_page_on_front == 0 ) ) {
			?>

			<?php if ( is_active_sidebar( 'front-page-content-widget-area' ) ) : ?>
				<div id="front-page-content-widget-area" class="widget-area">
					<?php dynamic_sidebar( 'front-page-content-widget-area' ); ?>
				</div>
			<?php endif;
		}
	}

	/**
	 * Add theme defaults for theme specific options.
	 *
	 * @todo  This function should be deprecated and deleted in future versions, as the cusomizer should handle the fonts.
	 *
	 * @since 0.1.0
	 */
	public function set_google_fonts_defaults() {

		global $pc_default_options;
		global $pc_default_google_font;

		$pc_default_options["drp_google_fonts"]     = $pc_default_google_font;
		$pc_default_options["txt_custom_font"]      = "";
		$pc_default_options["chk_custom_font"]      = null;
		$pc_default_options["txt_google_font_tags"] = "h1, h2, h3, h4";
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * Google fonts options fields.
	 *
	 * @since 0.1.0
	 */
	public function set_google_fonts_option_fields() {

		global $pc_google_font_list;

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>
		<div class="ltinfo">

			<h3>Google Web Fonts</h3>

			<p class="description">Select the custom font you want to use on your site!</p>

		</div><!-- .ltinfo -->

		<div class="rtoptions">
			<div class="box" id="google_font_option_drp">
				<select name='<?php echo PC_OPTIONS_DB_NAME; ?>[drp_google_fonts]'>
					<option value='none' <?php selected( 'none', $options['drp_google_fonts'] ); ?>>None&nbsp;</option>
					<?php foreach ( $pc_google_font_list as $pc_google_font ) { ?>
						<?php
						$pc_google_font_array = explode( ":", $pc_google_font );
						$pc_google_font_txt   = str_replace( '+', ' ', $pc_google_font_array[0] );
						?>
						<option value='<?php echo $pc_google_font; ?>' <?php selected( $pc_google_font, $options['drp_google_fonts'] ); ?>><?php echo $pc_google_font_txt; ?>&nbsp;</option>
					<?php } ?>
				</select>&nbsp;&nbsp;Google Web Font
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Select a Google web font from the drop down box. Select 'None' to disable Google fonts." />
			</div>

			<div class="box" id="google_font_name_option_txt">
				<p class="google_font_label">See a complete list of the latest Google fonts
					<a href="https://www.google.com/webfonts" target="_blank">here</a>.</p>
				<input size="72" id="txt_custom_font" name="<?php echo PC_OPTIONS_DB_NAME; ?>[txt_custom_font]" type='text' value='<?php echo $options['txt_custom_font']; ?>' />&nbsp;
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Browse the fonts and click the 'Quick-use' link next to the one you want. Copy the stylesheet URL, but not the whole meta tag. It looks something like this: 'https://fonts.googleapis.com/css?family=XXXXXX'." />
			</div>

			<div class="box" id="google_font_option_chk">
				<label><input id="chk_custom_font" name="<?php echo PC_OPTIONS_DB_NAME; ?>[chk_custom_font]" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options['chk_custom_font'] ) ) {
						checked( '1', $options['chk_custom_font'] );
					} ?> /> Use alernative Google font</label>
			</div>

			<div class="box">
				<input id="txt_google_font_tags" name="<?php echo PC_OPTIONS_DB_NAME; ?>[txt_google_font_tags]" type='text' value='<?php echo $options['txt_google_font_tags']; ?>' /> HTML tags to apply font to
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Enter a comma separated list of html tags. The selected Google font will be applied to these tags." />
			</div>
		</div><!-- .rtoptions -->

		<div class="line"></div>

	<?php
	}

	/**
	 * Enqueue main Google font CSS.
	 *
	 * @since 0.1.0
	 */
	public function enqueue_google_font() {

		$options           = get_option( PC_OPTIONS_DB_NAME );
		$theme_google_font = $options['drp_google_fonts'];

		if ( isset( $options['chk_custom_font'] ) && $options['chk_custom_font'] ) {
			/* Add a Google font manually if the entered URL is not empty. */
			if ( ! empty( $options['txt_custom_font'] ) ) {
				wp_enqueue_style( $theme_google_font, $options['txt_custom_font'] );
			}
		} else {
			/* Don't enqueue Google font if none selected, or 'None' specified in theme defaults (functions.php). */
			if ( $theme_google_font == 'none' || $theme_google_font == 'None' ) {
				return;
			}

			/* Use a Google font from the drop down list. */
			$font_uri_base = 'https://fonts.googleapis.com/css?family=';
			//$src = str_replace( ' ', '+', $theme_google_font );
			$src = $theme_google_font;
			wp_enqueue_style( $theme_google_font, $font_uri_base . $src );
		}
	}

	/**
	 * Enqueue custom CSS for Google fonts.
	 *
	 * @since 0.1.0
	 */
	public function theme_google_fonts_css() {

		$options           = get_option( PC_OPTIONS_DB_NAME );
		$google_font_tags  = $options['txt_google_font_tags'];
		$theme_google_font = $options['drp_google_fonts'];

		/* Don't add custom CSS if no Google font selected. */
		if ( ! ( isset( $options['chk_custom_font'] ) && $options['chk_custom_font'] ) && $theme_google_font == 'none' ) {
			return;
		}

		if ( $options['txt_custom_font'] != '' && ( isset( $options['chk_custom_font'] ) && $options['chk_custom_font'] ) ) {
			/* Add a Google font manually. */
			$parsed_url = parse_url( $options['txt_custom_font'] );
			if ( $parsed_url ) {
				$font              = $parsed_url['query'];
				$font              = substr( $font, 7 );
				$font_array        = explode( ":", $font );
				$theme_google_font = str_replace( '+', ' ', $font_array[0] );
				echo "\n<!-- " . PC_THEME_NAME . " Google web font CSS -->\n";
				echo "<style type=\"text/css\">" . $google_font_tags . " { font-family: '" . $theme_google_font . "', serif; }</style>\n";
			}
		} else {
			/* Use a Google font from the drop down list. */
			$theme_google_font = $options['drp_google_fonts'];
			$font_array        = explode( ":", $theme_google_font );
			$theme_google_font = str_replace( '+', ' ', $font_array[0] );

			if ( $google_font_tags != "" && $theme_google_font != "none" ) {
				echo "\n<!-- " . PC_THEME_NAME . " Google web font CSS -->\n";
				echo "<style type=\"text/css\">" . $google_font_tags . " { font-family: '" . $theme_google_font . "', serif; }</style>\n";
			}
		}
	}

	/**
	 * Add custom JS/jQuery for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_theme_option_js_google_fonts() {
		?>

		<script language="javascript">
			jQuery(document).ready(function ($) {
				// Sync the toggle with the state of the callout bar checkbox
				if ($('#chk_custom_font').attr('checked')) {
					$("#google_font_option_drp").css("display", "none");
					$("#google_font_name_option_txt").css("display", "table-row");
				}
				else {
					$("#google_font_option_drp").css("display", "table-row");
					$("#google_font_name_option_txt").css("display", "none");
				}

				// Toggles the state of the custom logo checkbox and displays the upload text box/buttons
				$("#chk_custom_font").click(function () {
					$("#google_font_option_drp, #google_font_name_option_txt").toggle(50);
				});
			});
		</script>

	<?php
	}

	/**
	 * Add theme defaults for theme specific options.
	 *
	 * @todo  Delete this function when all themes use customizer logo upload.
	 *
	 * @since 0.1.0
	 */
	public function set_custom_logo_defaults() {

		global $pc_default_options;

		if ( ! defined( 'PC_LOGO_URL_OPTION_NAME' ) ) {
			define( 'PC_LOGO_URL_OPTION_NAME', "txt_logo_url" ); // logo url text box
		}

		if ( ! defined( 'PC_LOGO_CHK_OPTION_NAME' ) ) {
			define( 'PC_LOGO_CHK_OPTION_NAME', "chk_custom_logo" ); // logo checkbox (to use/not use a custom logo)
		}

		$pc_default_options[PC_LOGO_CHK_OPTION_NAME] = "0";
		$pc_default_options[PC_LOGO_URL_OPTION_NAME] = null;
	}

	/**
	 * Add custom JS/jQuery for theme specific options.
	 *
	 * @todo  Delete this when logo uploader not in theme options for any themes anymore.
	 *
	 * @since 0.1.0
	 */
	public function set_theme_option_js_custom_logo() {
		?>

		<script language="javascript">

			// Toggle display of extra options if custom logo option selected
			jQuery(document).ready(function ($) {
				// Sync the toggle with the state of the custom logo checkbox		
				if ($('#theme_options_custom_logo_check').attr('checked'))
					$("#theme_options_custom_logo").css("display", "table-row");
				else
					$("#theme_options_custom_logo").css("display", "none");

				// Toggles the state of the custom logo checkbox and displays the upload text box/buttons
				$("#theme_options_custom_logo_check").click(function () {
					$("#theme_options_custom_logo").toggle("100");
				});
			});

		</script>

	<?php
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_custom_logo_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );
		?>

		<div class="box">
			<label><input id="theme_options_custom_logo_check" name="<?php echo PC_OPTIONS_DB_NAME . '[' . PC_LOGO_CHK_OPTION_NAME . ']'; ?>" type="checkbox" value="1" class="alignleft" <?php if ( isset( $options[PC_LOGO_CHK_OPTION_NAME] ) ) {
					checked( '1', $options[PC_LOGO_CHK_OPTION_NAME] );
				} ?> /> Use custom image logo
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Check this box to upload a custom logo. For best results use an image 110px high and less than 300px wide." /></label>
		</div>

		<div class="box" id="theme_options_custom_logo">
			<input type="text" id="upload_image" class="gray" size="60" name="<?php echo PC_OPTIONS_DB_NAME . '[' . PC_LOGO_URL_OPTION_NAME . ']'; ?>" value="<?php echo $options[PC_LOGO_URL_OPTION_NAME]; ?>" /><br />

			<?php
			if ( $options[PC_LOGO_URL_OPTION_NAME] != "" ) {
				echo "<div style='margin: 9px 0px 4px 0px;'><a href=\"" . $options[PC_LOGO_URL_OPTION_NAME] . "\" target=\"_blank\" /><img title=\"View full size (new window)\" style=\"width: 300px;height: auto\" src=\"" . $options[PC_LOGO_URL_OPTION_NAME] . "\" /></a></div>";
			} else {
				echo "<p style=\"font-size:11px;\">No image specified. Please add a valid logo URL above from the <a href=\"" . get_admin_url( '', "upload.php" ) . "\" target=\"_blank\">media library</a>. Click </p>";
			}
			?>

		</div><!-- #theme_options_custom_logo -->

	<?php
	}

	/**
	 * Add theme defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_homepage_bar_defaults() {

		global $pc_default_options;

		$pc_default_options["txtarea_homepage_bar"] = "";
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_homepage_bar_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );

		?>
		<div class="ltinfo">
			<h3><?php _e( 'Customize Homepage Bar', 'presscoders' ); ?></h3>

			<p class="description"><?php _e( 'Add/edit the HTML used to display the homepage bar.', 'presscoders' ); ?></p>
		</div><!-- .ltinfo -->

		<div class="rtoptions">
			<div class="box">
				<textarea name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_homepage_bar]" rows="3" class="gray" type='textarea'><?php echo $options['txtarea_homepage_bar']; ?></textarea>

				<p class="description">Leave blank to <strong>NOT</strong> display the homepage bar.</p>
			</div>
		</div><!-- .rtoptions -->

		<div class="line"></div>
	<?php
	}

	/**
	 * Add theme defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_custom_css_defaults() {

		global $pc_default_options;

		$pc_default_options["txtarea_custom_css"] = "";
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_custom_css_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );

		/* Allow the height of the Custom CSS box to vary with the content. */
		$res = PC_UTILITY::get_textarea_rows( $options['txtarea_custom_css'] );
		?>
		<div class="ltinfo">

			<h3>Custom CSS</h3>

			<p class="description">Add custom CSS here to override default styles.</p>

		</div><!-- .ltinfo -->

		<div class="rtoptions">

			<div class="box">
				<textarea name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_custom_css]" rows="<?php echo $res['rows']; ?>" class="<?php echo $res['class']; ?>" type='textarea'><?php echo $options['txtarea_custom_css']; ?></textarea>
			</div>

		</div><!-- .rtoptions -->

		<div class="line"></div>
	<?php
	}

	/**
	 * Add theme defaults for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_hf_code_insert_defaults() {

		global $pc_default_options, $pc_footer_links;

		/* @todo Change this to call a generic function to only allow theme names of a certain length. If the name is made up of multiple words then cut to the end of the last word that doesn't go over the limit. If only one word, that goes over the limit then don't cut off at all, to preserve readability. */
		$footer_theme_name = ( PC_THEME_NAME == 'Designfolio Pro' ) ? 'designfolio' : PC_THEME_NAME;
		$footer_link_path  = ( defined( 'PC_FOOTER_LINK_PATH' ) ) ? PC_FOOTER_LINK_PATH : 'https://www.presscoders.com/' . $footer_theme_name . '/';

		$pc_footer_links = '<div id="site-info"><p class="copyright">&copy; [year] [site-url]</p><p class="pc-link">Powered by <a href="https://wordpress.org/" target="_blank" class="wp-link">WordPress</a> and the <a href="' . $footer_link_path . '" target="blank" title="' . PC_THEME_NAME . ' WordPress Theme">' . PC_THEME_NAME . ' Theme</a>.</p></div><!-- #site-info -->';

		$pc_default_options["txtarea_header"]       = "";
		$pc_default_options["txtarea_footer"]       = "";
		$pc_default_options["txtarea_footer_links"] = $pc_footer_links;
	}

	/**
	 * Add custom form fields for theme specific options.
	 *
	 * @since 0.1.0
	 */
	public function set_hf_code_insert_option_fields() {

		$options = get_option( PC_OPTIONS_DB_NAME );

		/* Allow the height of the header/footer code insert, and footer link textareas to vary with the content. */
		$res_header_insert = PC_UTILITY::get_textarea_rows( $options['txtarea_header'] );
		$res_footer_insert = PC_UTILITY::get_textarea_rows( $options['txtarea_footer'] );
		$res_footer_links  = PC_UTILITY::get_textarea_rows( $options['txtarea_footer_links'] );

		?>
		<div class="line"></div>

		<div class="ltinfo">

			<h3>Header &amp; Footer Inserts</h3>

			<p class="description">Easily insert your analytics code, scripts, etc.</p>

		</div><!-- .ltinfo -->

		<div class="rtoptions">

			<h3>Header Insert
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Any HTML you add here will be inserted into your site's header, right before the closing head tag" />
			</h3>
			<textarea name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_header]" rows="<?php echo $res_header_insert['rows']; ?>" class="<?php echo $res_header_insert['class']; ?>" type='textarea'><?php echo $options['txtarea_header']; ?></textarea>

			<h3>Footer Insert
				<img src="<?php echo PC_THEME_ROOT_URI . '/api/images/icons/tooltip.png'; ?>" width="17" height="16" class="tooltipimg" title="Any HTML you add here will be inserted right before the closing body tag" />
			</h3>
			<textarea name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_footer]" rows="<?php echo $res_footer_insert['rows']; ?>" class="<?php echo $res_footer_insert['class']; ?>" type='textarea'><?php echo $options['txtarea_footer']; ?></textarea>

			<!-- Confirmation dialog before resetting the footer link HTML -->
			<script type="text/javascript">
				function show_confirm_fl() {
					var res = confirm("Reset Footer Links?");
					if (res == true) {
						<?php global $pc_footer_links; ?>
						$("#footer_links_textarea").val('<?php echo $pc_footer_links; ?>');
					}
				}
			</script>

			<h3>Footer Links</h3>

			<textarea id="footer_links_textarea" name="<?php echo PC_OPTIONS_DB_NAME; ?>[txtarea_footer_links]" rows="<?php echo $res_footer_links['rows']; ?>" class="<?php echo $res_footer_links['class']; ?>" type='textarea'><?php echo $options['txtarea_footer_links']; ?></textarea><br />
			<input type="button" style="margin-top:10px;" class="button-secondary" value="<?php _e( 'Reset footer links', 'presscoders' ) ?>" onclick="show_confirm_fl()" />

		</div><!-- .rtoptions -->
	<?php
	}

	/**
	 * Simple debug function to output some global WordPress vars such as the current page template.
	 *
	 * @since 0.1.0
	 */
	public function pc_simple_debug_output() {
		// @todo Add parameter option to output to browser console window (only if console object exists). Make this false by default. */
		global $pc_template, $pc_page_template, $pc_post_id, $pc_show_on_front, $pc_page_on_front, $pc_is_front_page, $pc_home_page;

		echo '$pc_template: ' . $pc_template . '<br />';
		echo 'is_archive(): ' . is_archive() . '<br />';
		echo '$pc_post_id: ' . $pc_post_id . '<br />';
		if ( ! empty( $pc_page_template ) ) {
			echo '$pc_page_template: ' . $pc_page_template . '<br />';
		}
		echo 'is_page(): ' . is_page() . '<br />';
		echo '--is_page_template(): ' . is_page_template() . '<br />';
		echo 'is_post_type_archive(): ' . is_post_type_archive() . '<br />';
		echo '------<br />';
		echo '$pc_show_on_front: ' . $pc_show_on_front . '<br />';
		echo '$pc_page_on_front: ' . $pc_page_on_front . '<br />';
		echo '$pc_is_front_page: ' . $pc_is_front_page . '<br />';
		echo '$pc_home_page: ' . $pc_home_page . '<br />';
		echo '------<br />';
		echo 'is_singular(): ' . is_singular() . '<br />'; // True if is_single(), is_page() or is_attachment()
		echo '--is_single(): ' . is_single() . '<br />';
		echo '--is_page(): ' . is_page() . '<br />';
		echo '--is_attachment(): ' . is_attachment() . '<br />';
		echo 'is_post_type_archive(): ' . is_post_type_archive() . '<br />';
	}

	/**
	 * Include theme/Plugin registered CPT on the author archive page.
	 *
	 * @since 0.1.0
	 */
	public function author_archive_include_cpt( $query ) {

		if ( ! is_admin() && $query->is_author ) {
			//$exclude_cpt = array( 'slide', 'testimonial', 'page', 'attachment', 'revision', 'nav_menu_item' ); // CPT to exclude
			//$registered_cpt = array_diff( get_post_types(), $exclude_cpt );
			//$query->set( 'post_type', $registered_cpt );

			$query->set( 'post_type', array( 'post', 'support' ) );
			remove_action( 'pre_get_posts', 'custom_post_author_archive' );
		}
	}
}

?>