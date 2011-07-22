<?php
/**
 * Creates settings and outputs admin menu and settings page
 */

/**
 * Return the defaults array
 *
 * @since 0.9
 */
function genesis_slider_defaults() {
	return array(
		'post_type' => 'post',
		'posts_term' => '',
		'exclude_terms' => '',
		'include_exclude' => '',
		'post_id' => '',
		'posts_num' => 5,
		'posts_offset' => 0,
		'orderby' => 'date',
		'slideshow_timer' => 3000,
		'slideshow_delay' => 400,
		'slideshow_height' => 400,
		'slideshow_excerpt_width' => 400,
		'slideshow_width' => 870,
		'location_vertical' => 'bottom',
		'location_horizontal' => 'right'
	);
}

add_action( 'admin_init', 'register_genesis_slider_settings' );
/**
 * This registers the settings field
 */
function register_genesis_slider_settings() {
	
	register_setting( GENESIS_SLIDER_SETTINGS_FIELD, GENESIS_SLIDER_SETTINGS_FIELD );
	add_option( GENESIS_SLIDER_SETTINGS_FIELD, genesis_slider_defaults(), '', 'yes' );
	
	if ( ! isset($_REQUEST['page']) || $_REQUEST['page'] != 'genesis_slider' )
		return;

	if ( genesis_get_slider_option( 'reset' ) ) {
		update_option( GENESIS_SLIDER_SETTINGS_FIELD, genesis_slider_defaults() );

		genesis_admin_redirect( 'genesis_slider', array( 'reset' => 'true' ) );
		exit;
	}
	
}

add_action('admin_notices', 'genesis_slider_notice');
/**
 * This is the notice that displays when you successfully save or reset
 * the slider settings.
 */
function genesis_slider_notice() {

	if ( ! isset( $_REQUEST['page'] ) || $_REQUEST['page'] != 'genesis_slider' )
		return;

	if ( isset( $_REQUEST['reset'] ) && 'true' == $_REQUEST['reset'] )
		echo '<div id="message" class="updated"><p><strong>' . __( 'Settings reset.', 'genesis' ) . '</strong></p></div>';
	elseif ( isset( $_REQUEST['settings-updated'] ) && $_REQUEST['settings-updated'] == 'true' )
		echo '<div id="message" class="updated"><p><strong>' . __( 'Settings saved.' ) . '</strong></p></div>';

}

add_action( 'admin_menu', 'genesis_slider_settings_init', 15 );
/**
 * This is a necessary go-between to get our scripts and boxes loaded
 * on the theme settings page only, and not the rest of the admin
 */
function genesis_slider_settings_init() {
	global $_genesis_slider_settings_pagehook;

	// Add "Design Settings" submenu
	$_genesis_slider_settings_pagehook = add_submenu_page( 'genesis', __( 'Slider Settings', 'genesis' ), __( 'Slider Settings', 'genesis' ), 'manage_options', 'genesis_slider', 'genesis_slider_settings_admin' );

	add_action( 'load-' . $_genesis_slider_settings_pagehook, 'genesis_slider_settings_scripts' );
	add_action( 'load-' . $_genesis_slider_settings_pagehook, 'genesis_slider_settings_boxes' );
}

/**
 * Loads the scripts required for the settings page
 */
function genesis_slider_settings_scripts() {
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
	wp_enqueue_script( 'genesis_slider_admin_scripts', WP_PLUGIN_URL . '/genesis-slider/js/admin.js', array( 'jquery' ), '1.0', TRUE );
}

/*
 * Loads the Meta Boxes
 */
function genesis_slider_settings_boxes() {
	global $_genesis_slider_settings_pagehook;

	add_meta_box( 'genesis-slider-options', __( 'Genesis Slider Settings', 'genesis' ), 'genesis_slider_options_box', $_genesis_slider_settings_pagehook, 'column1' );
}


add_filter( 'screen_layout_columns', 'genesis_slider_settings_layout_columns', 10, 2 );
/**
 * Tell WordPress that we want only 1 column available for our meta-boxes
 */
function genesis_slider_settings_layout_columns( $columns, $screen ) {
	global $_genesis_slider_settings_pagehook;

	if ( $screen == $_genesis_slider_settings_pagehook ) {
		// This page should have 1 column settings
		$columns[$_genesis_slider_settings_pagehook] = 1;
	}

	return $columns;
}

/**
 * This function is what actually gets output to the page. It handles the markup,
 * builds the form, outputs necessary JS stuff, and fires <code>do_meta_boxes()</code>
 */
function genesis_slider_settings_admin() {
		global $_genesis_slider_settings_pagehook, $screen_layout_columns;

		$width = "width: 99%;";
		$hide2 = $hide3 = " display: none;";
?>
		<div id="gs" class="wrap genesis-metaboxes">
		<form method="post" action="options.php">

			<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
			<?php settings_fields( GENESIS_SLIDER_SETTINGS_FIELD ); // important!  ?>

			<?php screen_icon( 'plugins' ); ?>
			<h2>
				<?php _e( 'Genesis - Slider' ); ?>
				<input type="submit" class="button-primary genesis-h2-button" value="<?php _e('Save Settings', 'genesis') ?>" />
				<input type="submit" class="button-highlighted genesis-h2-button" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[reset]" value="<?php _e('Reset Settings', 'genesis'); ?>" onclick="return genesis_confirm('<?php echo esc_js( __('Are you sure you want to reset?', 'genesis') ); ?>');" />
			</h2>

			<div class="metabox-holder">
				<div class="postbox-container" style="<?php echo $width; ?>">
					<?php do_meta_boxes( $_genesis_slider_settings_pagehook, 'column1', null ); ?>
				</div>
			</div>
			
			<div class="bottom-buttons">
				<input type="submit" class="button-primary" value="<?php _e('Save Settings', 'genesis') ?>" />
				<input type="submit" class="button-highlighted" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[reset]" value="<?php _e('Reset Settings', 'genesis'); ?>" />
			</div>

		</form>
		</div>
		<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready( function($) {
				// close postboxes that should be closed
				$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
				// postboxes setup
				postboxes.add_postbox_toggles('<?php echo $_genesis_slider_settings_pagehook; ?>');
			});
			//]]>
		</script>

<?php
}

/**
 * This function generates the form code to be used in the metaboxes
 *
 * @since 0.9
 */
function genesis_slider_options_box() {
?>

			<div id="genesis-slider-content-type">

				<h4>Type of Content</h4>

				<p><label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_type]"><?php _e( 'Would you like to use posts or pages', 'genesis' ); ?>?</label>
					<select id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_type]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_type]">
<?php

						$post_types = get_post_types( array( 'public' => true ), 'names', 'and' );
						$post_types = array_filter( $post_types, 'genesis_slider_exclude_post_types' );

						foreach ( $post_types as $post_type ) { ?>

							<option style="padding-right:10px;" value="<?php echo esc_attr( $post_type ); ?>" <?php selected( esc_attr( $post_type ), genesis_get_slider_option( 'post_type' ) ); ?>><?php echo esc_attr( $post_type ); ?></option><?php } ?>

					</select></p>

			</div>

			<div id="genesis-slider-content-filter">

				<div id="genesis-slider-taxonomy">

					<p><strong style="display: block; font-size: 11px; margin-top: 10px;"><?php _e( 'By Taxonomy and Terms', 'genesis' ); ?></strong><label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_term]"><?php _e( 'Choose a term to determine what slides to include', 'genesis' ); ?>.</label>

						<select id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_term]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_term]" style="margin-top: 5px;">

							<option style="padding-right:10px;" value="" <?php selected( '', genesis_get_slider_option( 'posts_term' ) ); ?>><?php _e( 'All Taxonomies and Terms', 'genesis' ); ?></option>
			<?php
						$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );

						$taxonomies = array_filter( $taxonomies, 'genesis_slider_exclude_taxonomies' );
						$test = get_taxonomies( array( 'public' => true ), 'objects' );

						foreach ( $taxonomies as $taxonomy ) {
							$query_label = '';
							if ( !empty( $taxonomy->query_var ) )
								$query_label = $taxonomy->query_var;
							else
								$query_label = $taxonomy->name;
			?>
								<optgroup label="<?php echo esc_attr( $taxonomy->labels->name ); ?>">

									<option style="margin-left: 5px; padding-right:10px;" value="<?php echo esc_attr( $query_label ); ?>" <?php selected( esc_attr( $query_label ), genesis_get_slider_option( 'posts_term' ) ); ?>><?php echo $taxonomy->labels->all_items; ?></option><?php
								$terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=1' );
								foreach ( $terms as $term ) {
				?>
									<option style="margin-left: 8px; padding-right:10px;" value="<?php echo esc_attr( $query_label ) . ',' . $term->slug; ?>" <?php selected( esc_attr( $query_label ) . ',' . $term->slug, genesis_get_slider_option( 'posts_term' ) ); ?>><?php echo '-' . esc_attr( $term->name ); ?></option><?php } ?>
								
								</optgroup> <?php } ?>

						</select>
					</p>
					
					<p><strong style="display: block; font-size: 11px; margin-top: 10px;"><?php _e( 'Include or Exclude by Taxonomy ID', 'genesis' ); ?></strong></p>
					
					<p>
						<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[exclude_terms]"><?php printf( __( 'List which category, tag or other taxonomy IDs to include/exclude. (1,2,3,4 for example)', 'genesis' ), '<br />' ); ?></label>
					</p>

					<p>
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[exclude_terms]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[exclude_terms]" value="<?php echo esc_attr( genesis_get_slider_option( 'exclude_terms' ) ); ?>" style="width:60%;" />
					</p>

				</div>

				<p>
					<strong style="font-size:11px;margin-top:10px;"><label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[include_exclude]"><?php printf( __( 'Include or Exclude by %s ID', 'genesis' ), genesis_get_slider_option( 'post_type' ) ); ?></label></strong>
				</p>

				<p><?php _e( 'Choose the include/exclude slides using their post/page ID in a comma-separated list. (1,2,3,4 for example)', 'genesis' ); ?></p>

				<p>
					<select style="margin-top: 5px;" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[include_exclude]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[include_exclude]">

						<option style="padding-right:10px;" value="" <?php selected( '', genesis_get_slider_option( 'include_exclude' ) ); ?>><?php _e( 'Select', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="include" <?php selected( 'include', genesis_get_slider_option( 'include_exclude' ) ); ?>><?php _e( 'Include', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="exclude" <?php selected( 'exclude', genesis_get_slider_option( 'include_exclude' ) ); ?>><?php _e( 'Exclude', 'genesis' ); ?></option>
					</select>
				</p>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_id]">List which <strong><?php echo genesis_get_slider_option( 'post_type' ) . ' ' . __( 'ID', 'genesis' ); ?>s</strong> to include/exclude. (1,2,3,4 for example)</label></p>
				<p>
					<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_id]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[post_id]" value="<?php echo esc_attr( genesis_get_slider_option( 'post_id' ) ); ?>" style="width:60%;" />
				</p>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_num]"><?php _e( 'Number of Slides to Show', 'genesis' ); ?>:</label>
					<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_num]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_num]" value="<?php echo esc_attr( genesis_get_slider_option( 'posts_num' ) ); ?>" size="2" />
				</p>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_offset]"><?php _e( 'Number of Posts to Offset', 'genesis' ); ?>:</label>
					<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_offset]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[posts_offset]" value="<?php echo esc_attr( genesis_get_slider_option( 'posts_offset' ) ); ?>" size="2" />
				</p>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[orderby]"><?php _e( 'Order By', 'genesis' ); ?>:</label>
					<select id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[orderby]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[orderby]">
						<option style="padding-right:10px;" value="date" <?php selected( 'date', genesis_get_slider_option( 'orderby' ) ); ?>><?php _e( 'Date', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="title" <?php selected( 'title', genesis_get_slider_option( 'orderby' ) ); ?>><?php _e( 'Title', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="ID" <?php selected( 'ID', genesis_get_slider_option( 'orderby' ) ); ?>><?php _e( 'ID', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="rand" <?php selected( 'rand', genesis_get_slider_option( 'orderby' ) ); ?>><?php _e( 'Random', 'genesis' ); ?></option>
					</select>
				</p>

			</div>
			
			<hr class="div" />

			<h4><?php _e( 'Transition Settings', 'genesis' ); ?></h4>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_timer]"><?php _e( 'Time Between Slides (in milliseconds)', 'genesis' ); ?>:
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_timer]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_timer]" value="<?php echo genesis_get_slider_option( 'slideshow_timer' ); ?>" size="5" /></label>
				</p>

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_delay]"><?php _e( 'Slide Transition Speed (in milliseconds)', 'genesis' ); ?>:
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_delay]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_delay]" value="<?php echo genesis_get_slider_option( 'slideshow_delay' ); ?>" size="5" /></label>
				</p>

			<hr class="div" />

			<h4><?php _e( 'Display Settings', 'genesis' ); ?></h4>
			
				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_width]"><?php _e( 'Slide Show Width (in pixels)', 'genesis' ); ?>:
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_width]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_width]" value="<?php echo genesis_get_slider_option( 'slideshow_width' ); ?>" size="5" /></label>
				</p>			

				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_height]"><?php _e( 'Slide Show Height (in pixels)', 'genesis' ); ?>:
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_height]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_height]" value="<?php echo genesis_get_slider_option( 'slideshow_height' ); ?>" size="5" /></label>
				</p>
				
			<hr class="div" />
				
			<h4><?php _e( 'Excerpt Settings', 'genesis' ); ?></h4>
				
				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_excerpt_width]"><?php _e( 'Slide Show Excerpt Width (in pixels)', 'genesis' ); ?>:
						<input type="text" id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_excerpt_width]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[slideshow_excerpt_width]" value="<?php echo genesis_get_slider_option( 'slideshow_excerpt_width' ); ?>" size="5" /></label>
				</p>
				
				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_vertical]"><?php _e( 'Excerpt Location (vertical)', 'genesis' ); ?>:</label>
					<select id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_vertical]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_vertical]">
						<option style="padding-right:10px;" value="top" <?php selected( 'top', genesis_get_slider_option( 'location_vertical' ) ); ?>><?php _e( 'Top', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="bottom" <?php selected( 'bottom', genesis_get_slider_option( 'location_vertical' ) ); ?>><?php _e( 'Bottom', 'genesis' ); ?></option>
					</select>
				</p>	
				
				<p>
					<label for="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_horizontal]"><?php _e( 'Excerpt Location (horizontal)', 'genesis' ); ?>:</label>
					<select id="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_horizontal]" name="<?php echo GENESIS_SLIDER_SETTINGS_FIELD; ?>[location_horizontal]">
						<option style="padding-right:10px;" value="left" <?php selected( 'left', genesis_get_slider_option( 'location_horizontal' ) ); ?>><?php _e( 'Left', 'genesis' ); ?></option>
						<option style="padding-right:10px;" value="right" <?php selected( 'right', genesis_get_slider_option( 'location_horizontal' ) ); ?>><?php _e( 'Right', 'genesis' ); ?></option>
					</select>
				</p>							
<?php
}

/*
 * Echos form submit button for settings page.
 */
function genesis_slider_form_submit( $args = array( ) ) {
	echo '<p><input type="submit" class="button-primary" value="' . __( 'Save Changes', 'genesis' ) . '" /></p>';
}