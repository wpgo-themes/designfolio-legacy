<?php

// ************************************************************
// ** Theme Content Templates - Meta Box Callback Functions  **
// ************************************************************
function theme_meta_box_content_templates( $post, $box ) {

	/* Retrieve our custom meta box values. */
	$theme_ct                    = get_post_meta( $post->ID, '_' . PC_THEME_NAME_SLUG . '_ct', true );
	$theme_content_template_save = get_post_meta( $post->ID, '_' . PC_THEME_NAME_SLUG . '_content_template_save', true );

	$image_url = PC_THEME_ROOT_URI . '/includes/modules/content-templates/ct_images/';

	/* Setup the main content template directory, and array that holds all the individual content template arrays. */
	$ct     = array();
	$ct_dir = PC_THEME_ROOT_DIR . "/includes/modules/content-templates/ct_php/";

	/* Loop over the content template files in the /ct_php/ folder. */
	if ( $handle = opendir( $ct_dir ) ) {
		while ( false !== ( $ct_file = readdir( $handle ) ) ) {
			if ( $ct_file != "." && $ct_file != ".." && $ct_file != ".svn" ) {

				/* Current content template filename. */
				$filename = basename( $ct_dir . $ct_file, ".php" ); /* Get file name and strip '.php' suffix. */

				/* Compile the content template title, image, description, html data. */
				$ct_title = ucwords( str_replace( '_', ' ', $filename ) ); /* This needs to be the file name, with '.html' removed and everything before the slash. */
				$ct_img   = $filename . ".png"; /* Filename with .png instead of .html. */

				ob_start(); /* Start buffer. */
				$image = PC_THEME_ROOT_URI . "/includes/modules/images/pixel-placeholder.gif";
				include_once( $ct_dir . $ct_file );
				$ct_php = ob_get_contents(); /* Pass output to a variable. */
				ob_end_clean(); /* End and erase output buffer. */

				/* Create new content template array. */
				$current_ct = array(
					"Title"       => $ct_title,
					"Img"         => $ct_img,
					"Description" => $ct_descr,
					"Html"        => $ct_php
				);

				/* Add content template to the main container. */
				array_push( $ct, $current_ct );
			}
		}
		closedir( $handle );
	}

	/* Custom meta box form elements below. */
	?>

	<script type="text/javascript">

		jQuery(document).ready(function ($) {

			/*	Can insert code in html mode too with this jQuery Plugin:
			 https://plugins.jquery.com/plugin-tags/caret
			 Not sure of the compatibility with jQuery 1.7.
			 */

			/* Get the URL of the theme images folder into JavaScript. */
			var img_url = "<?php echo $image_url; ?>";

			/* Get the PHP array into JavaScript. */
			var jsArray = [ ];
			<?php
				$ct_length = count($ct);
				$i = 0;
				foreach ($ct as $template) {
					echo "jsArray[$i] = new Array(3);\r\n";
					echo "jsArray[$i][0] = '".$template["Title"]."';\r\n";
					echo "jsArray[$i][1] = '".$template["Img"]."';\r\n";
					echo "jsArray[$i][2] = '".$template["Description"]."';\r\n";
					echo "jsArray[$i][3] = ".json_encode($template["Html"]).";\r\n\r\n";
					$i++;
				}
			?>

			/* Add content directly to post editor. */
			$("#insert_tinymce").click(onTinyMCEChange);
			function onTinyMCEChange() {

				/* Get the meta data for the selected content template. */
				var content_template = get_content_template_data();

				/* Check whether the editor is in visual or html mode. */
				if (getUserSetting('editor') == 'html') {
					/* Add content to textarea editor. */
					var sel = $("#content").getSelection();
					$("#content").insertText(content_template[3], sel, true);
				}
				else {
					/* Add content to TinyMCE editor at caret position. */
					var tinymce_instance = tinyMCE.get("content");
					tinymce_instance.insertContent(content_template[3]);
				}

			}

			/* When the select box change event fires, run onSelectChange(). */
			$("#content_template_select").change(onSelectChange);

			/* Update the Content Template Html fields when user selects a new template. */
			function onSelectChange() {
				var content_template = get_content_template_data();

				/* Don't need to set the title manually as the user changes this. */
				$("#content_template_description").html('<strong>Description: </strong> ' + content_template[2]);
				$("#content_template_image_link").attr('href', img_url + content_template[1] + '?height=400');
			}

			/* Get the content template data from the selected drop down index. */
			function get_content_template_data() {
				var selected = $("#content_template_select option:selected");
				var index = selected.index();

				var content_template = new Array();
				content_template[0] = jsArray[index][0];
				/* Title. */
				content_template[1] = jsArray[index][1];
				/* Image. */
				content_template[2] = jsArray[index][2];
				/* Description. */
				content_template[3] = jsArray[index][3];
				/* HTML. */

				return content_template;
			}
		});

	</script>
	<table class="form-table">
		<tbody>
		<tr valign="top">
			<td>
				<span style="font-size: 13px;"><strong>Select Content Template:</strong> </span>
				<select id="content_template_select">
					<?php
					$index = 0;
					foreach ( $ct as $template ) {
						/* Remove spaces, and make title lower case, for the value atrribute. */
						$value = str_replace( ' ', '-', strtolower( $template['Title'] ) );
						if ( $index == 0 ) {
							/* Make sure the first select option is selected by default. */
							echo "<option selected=\"selected\" value=\"" . $value . "\">" . $template['Title'] . "&nbsp;</option>";
						} else {
							echo "<option value=\"" . $value . "\">" . $template['Title'] . "&nbsp;</option>";
						}
						$index ++;
					}
					?>
				</select>

				<div style="margin-top: 15px;">
					<p id="content_template_description" style="font-size: 12px;margin-left: 0px;">
						<strong>Description: </strong><?php echo $ct[0]["Description"]; ?></p>
				</div>
				<div style="margin-top: 15px;">
					<span id="content_template_insert" style="font-size: 12px;margin-left: 0px;"><a class="button-primary" style="cursor:pointer;" id="insert_tinymce"><?php _e( 'Insert into Editor', 'presscoders' ); ?></a></span>
					<span id="content_template_preview" style="font-size: 12px;margin-left: 5px;"><a href="<?php echo $image_url . $ct[0]["Img"] . '?height=400'; ?>" style="text-decoration:none;" class="button thickbox" rel="template-image" id="content_template_image_link"><?php _e( 'Preview', 'presscoders' ); ?></a></span>
				</div>
				<div style="margin-top: 15px;">
					<p id="content_template_suggestmore" style="font-size: 12px;margin-left: 0px;">
						<a href="https://www.presscoders.com/suggest-a-new-content-template/" target="_blank">Suggest a new content template</a>
					</p>
				</div>
			</td>
		</tr>
		</tbody>
	</table>

<?php
}

function theme_content_templates_save_meta_box( $post_id ) {

	/* Process form data if $_POST is set. */
	if ( isset( $_POST[PC_THEME_NAME_SLUG . '_content_template_save'] ) ) {
		/* Save the meta box data as post meta, using the post ID as a unique prefix. */
		update_post_meta( $post_id, '_' . PC_THEME_NAME_SLUG . '_ct', esc_attr( $_POST[PC_THEME_NAME_SLUG . '_ct'] ) );
		update_post_meta( $post_id, '_' . PC_THEME_NAME_SLUG . '_content_template_save', esc_attr( $_POST[PC_THEME_NAME_SLUG . '_content_template_save'] ) );
	}
}

function enqueue_text_inputs() {
	wp_register_script( 'pc_text_inputs', PC_THEME_ROOT_URI . '/includes/modules/js/textinputs_jquery.js', array( 'jquery' ) );
	wp_enqueue_script( 'pc_text_inputs' );
}

function add_enqueue_text_inputs_action() {
	add_action( 'admin_enqueue_scripts', 'enqueue_text_inputs' );
}

add_action( 'load-post.php', 'add_enqueue_text_inputs_action' );
add_action( 'load-post-new.php', 'add_enqueue_text_inputs_action' );
?>