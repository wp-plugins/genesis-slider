<?php
/*
	Plugin Name: Genesis Slider
	Plugin URI: http://www.studiopress.com
	Description: A featured slider for the Genesis Framework.
	Author: StudioPress
	Author URI: http://www.studiopress.com

	Version: 0.9.3

	License: GNU General Public License v2.0
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/**
 * Props to Rafal Tomal, Nick Croft, Nathan Rice and Brian Gardner for collaboratively writing this plugin.
 */

define( 'GENESIS_SLIDER_SETTINGS_FIELD', 'genesis_slider_settings' );

add_action( 'genesis_init', 'GenesisSliderInit', 15 );
/**
 * Loads required files and adds image via Genesis Init Hook
 */
function GenesisSliderInit() {

	// translation support
	load_plugin_textdomain( 'genesis-slider', false, '/genesis-slider/languages/' );
	
	/** hook all frontend slider functions here to ensure Genesis is active **/
	add_action( 'wp_enqueue_scripts', 'genesis_slider_scripts' );
	add_action( 'wp_print_styles', 'genesis_slider_styles' );
	add_action( 'wp_head', 'genesis_slider_head', 1 );
	add_action( 'wp_footer', 'genesis_slider_jflow_params' );
	add_filter( 'excerpt_more', 'genesis_slider_excerpt_more' );
	add_action( 'widgets_init', 'Genesis_SliderRegister' );
	
	/** Include Admin file */
	if ( is_admin() ) require_once( dirname( __FILE__ ) . '/admin.php' );

	/** Add new image size */
	add_image_size( 'slider', ( int ) genesis_get_slider_option( 'slideshow_width' ), ( int ) genesis_get_slider_option( 'slideshow_height' ), TRUE );

}

add_action( 'genesis_settings_sanitizer_init', 'genesis_slider_sanitization' );
/**
 * Add settings to Genesis sanitization
 *
 */
function genesis_slider_sanitization() {
	genesis_add_option_filter( 'one_zero', GENESIS_SLIDER_SETTINGS_FIELD,
		array(
			'slideshow_arrows',
			'slideshow_excerpt_show',
		) );
	genesis_add_option_filter( 'no_html', GENESIS_SLIDER_SETTINGS_FIELD,
		array(
			'post_type',
			'posts_term',
			'exclude_terms',
			'include_exclude',
			'post_id',
			'posts_num',
			'posts_offset',
			'orderby',
			'slideshow_timer',
			'slideshow_delay',
			'slideshow_height',
			'slideshow_width',
			'slideshow_excerpt_content',
			'slideshow_excerpt_content_limit',
			'slideshow_more_text',
			'slideshow_excerpt_width',
			'location_vertical',
			'location_horizontal',
		) );
}

/**
 * Load the script files
 */
function genesis_slider_scripts() {

	/** easySlider JavaScript code */
	wp_enqueue_script( 'jflow', WP_PLUGIN_URL . '/genesis-slider/js/jflow.plus.js', array( 'jquery' ), '1.2', TRUE );

}

/**
 * Load the CSS files
 */
function genesis_slider_styles() {

	/** standard slideshow styles */
	wp_register_style( 'slider_styles', WP_PLUGIN_URL . '/genesis-slider/style.css' );
	wp_enqueue_style( 'slider_styles' );

}

/**
 * Loads scripts and styles via wp_head hook.
 */
function genesis_slider_head() {

		$height = ( int ) genesis_get_slider_option( 'slideshow_height' );
		$width = ( int ) genesis_get_slider_option( 'slideshow_width' );

		$slideInfoWidth = ( int ) genesis_get_slider_option( 'slideshow_excerpt_width' );
		$slideNavTop = ( int ) ( ($height - 60) * .5 );

		$vertical = genesis_get_slider_option( 'location_vertical' );
		$horizontal = genesis_get_slider_option( 'location_horizontal' );

		echo '
		<style type="text/css">
			#previous a img { background: transparent url(' . CHILD_URL . '/images/slider-previous.png) no-repeat; }
			#next a img { background: transparent url(' . CHILD_URL . '/images/slider-next.png) no-repeat; }
			#genesis-slider, #slides, .genesis-slider-wrap { height: ' . $height . 'px; width: ' . $width . 'px; }
			.slide-excerpt { width: ' . $slideInfoWidth . 'px; }
			.slide-excerpt { ' . $vertical . ': 0; }
			.slide-excerpt { '. $horizontal . ': 0; }
			.slider-next, .slider-previous { top: ' . $slideNavTop . 'px };
		</style>';
}

/**
 * Outputs slider script on wp_footer hook.
 */
function genesis_slider_jflow_params() {

	$timer = ( int ) genesis_get_slider_option( 'slideshow_timer' );
	$duration = ( int ) genesis_get_slider_option( 'slideshow_delay' );
	$height = ( int ) genesis_get_slider_option( 'slideshow_height' );
	$width = ( int ) genesis_get_slider_option( 'slideshow_width' );

	$output = 'jQuery(document).ready(function($) {
					$(".myController").jFlow({
						controller: ".jFlowControl",
						slideWrapper : "#jFlowSlider",
						slides: "#slides",
						selectedWrapper: "jFlowSelected",
						width: "' . $width . 'px",
						height: "' . $height . 'px",
						timer: ' . $timer . ',
						duration: ' . $duration . ',
						prev: ".slider-previous",
						next: ".slider-next",
						auto: true
					});
				});';

	$output = str_replace( array( "\n", "\t", "\r" ), '', $output );

	echo '<script type=\'text/javascript\'>' . $output . '</script>';

}

/**
 * Registers the slider widget
 */
function Genesis_SliderRegister() {
	register_widget( 'Genesis_SliderWidget' );
}

/** Creates read more link after excerpt */
function genesis_slider_excerpt_more($more) {
	global $post;
	static $read_more = null;

	if ( $read_more === null )
		$read_more = genesis_get_slider_option( 'slideshow_more_text' );

	if ( !$read_more )
		return '';

	return '&hellip; <a href="'. get_permalink($post->ID) . '">' . $read_more . '</a>';
}

/**
 * Slideshow Widget Class
 */
class Genesis_SliderWidget extends WP_Widget {

		function Genesis_SliderWidget() {
			$widget_ops = array( 'classname' => 'genesis_slider', 'description' => __( 'Displays a slideshow inside a widget area', 'genesis-slider' ) );
			$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'genesisslider-widget' );
			$this->WP_Widget( 'genesisslider-widget', __( 'Genesis - Slider', 'genesis-slider' ), $widget_ops, $control_ops );
		}

		function save_settings( $settings ) {
			$settings['_multiwidget'] = 0;
			update_option( $this->option_name, $settings );
		}

		// display widget
		function widget( $args, $instance ) {
			extract( $args );

			echo $before_widget;

			$term_args = array( );

			if ( 'page' != genesis_get_slider_option( 'post_type' ) ) {

				if ( genesis_get_slider_option( 'posts_term' ) ) {

					$posts_term = explode( ',', genesis_get_slider_option( 'posts_term' ) );

					if ( 'category' == $posts_term['0'] )
						$posts_term['0'] = 'category_name';

					if ( 'post_tag' == $posts_term['0'] )
						$posts_term['0'] = 'tag';

					if ( isset( $posts_term['1'] ) )
						$term_args[$posts_term['0']] = $posts_term['1'];

				}

				if ( !empty( $posts_term['0'] ) ) {

					if ( 'category' == $posts_term['0'] )
						$taxonomy = 'category';

					elseif ( 'post_tag' == $posts_term['0'] )
						$taxonomy = 'post_tag';

					else
						$taxonomy = $posts_term['0'];

				} else {

					$taxonomy = 'category';

				}

				if ( genesis_get_slider_option( 'exclude_terms' ) ) {

					$exclude_terms = explode( ',', str_replace( ' ', '', genesis_get_slider_option( 'exclude_terms' ) ) );
					$term_args[$taxonomy . '__not_in'] = $exclude_terms;

				}
			}

			if ( genesis_get_slider_option( 'posts_offset' ) ) {
				$myOffset = genesis_get_slider_option( 'posts_offset' );
				$term_args['offset'] = $myOffset;
			}

			if ( genesis_get_slider_option( 'post_id' ) ) {
				$IDs = explode( ',', str_replace( ' ', '', genesis_get_slider_option( 'post_id' ) ) );
				if ( 'include' == genesis_get_slider_option( 'include_exclude' ) )
					$term_args['post__in'] = $IDs;
				else
					$term_args['post__not_in'] = $IDs;
			}

			$query_args = array_merge( $term_args, array(
				'post_type' => genesis_get_slider_option( 'post_type' ),
				'posts_per_page' => genesis_get_slider_option( 'posts_num' ),
				'orderby' => genesis_get_slider_option( 'orderby' ),
				'order' => genesis_get_slider_option( 'order' ),
				'meta_key' => genesis_get_slider_option( 'meta_key' )
			) );

			$query_args = apply_filters( 'genesis_slider_query_args', $query_args );
?>

		<div id="genesis-slider">
			<div class="genesis-slider-wrap">

				<div id="slides">
					<?php
						$controller = '';
						$slider_posts = new WP_Query( $query_args );
						if ( $slider_posts->have_posts() ) {
							$show_excerpt = genesis_get_slider_option( 'slideshow_excerpt_show' );
							$show_type = genesis_get_slider_option( 'slideshow_excerpt_content' );
							$show_limit = genesis_get_slider_option( 'slideshow_excerpt_content_limit' );
							$more_text = genesis_get_slider_option( 'slideshow_more_text' );
						} 
						while ( $slider_posts->have_posts() ) : $slider_posts->the_post();
						$controller .= '<span class="jFlowControl"></span>';
					?>
					<div>

					<?php if ( $show_excerpt == 1 ) { ?>
						<div class="slide-excerpt">
							<div class="slide-background"></div><!-- end .slide-background -->
							<div class="slide-excerpt-border ">
								<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
								<?php 
									if ( $show_type != 'full' )
										the_excerpt();
									elseif ( $show_limit )
										the_content_limit( (int)$show_limit, esc_html( $more_text ) );
									else
										the_content( esc_html( $more_text ) );
								?>
							</div><!-- end .slide-excerpt-border  -->
						</div><!-- end .slide-excerpt -->
					<?php } ?>

						<div class="slide-image">
							<a href="<?php the_permalink() ?>" rel="bookmark"><?php genesis_image( "format=html&size=slider" ); ?></a>
						</div><!-- end .slide-image -->

					</div>
				<?php endwhile; ?>
				</div><!-- end #slides -->

				<div class="myController">
					<?php echo $controller; ?>
				</div><!-- end #myController -->

				<?php if ( genesis_get_slider_option( 'posts_num' ) >= 2 && genesis_get_slider_option( 'slideshow_arrows' ) ) { ?>
					<div class="slider-previous"></div>
					<div class="slider-next"></div>
				<?php } ?>

			</div><!-- end .genesis-slider-wrap -->
		</div><!-- end #genesis-slider -->

<?php
		echo $after_widget;
		wp_reset_query();

		}

		/** Widget options */
		function form( $instance ) {
			echo '<p>';
			printf( __( 'To configure slider options, please go to the <a href="%s">Slider Settings</a> page.', 'genesis-slider' ), menu_page_url( 'genesis_slider', 0 ) );
			echo '</p>';
		}

}

/**
 * Used to exclude taxonomies and related terms from list of available terms/taxonomies in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $taxonomy 'taxonomy' being tested
 * @return string
 */
function genesis_slider_exclude_taxonomies( $taxonomy ) {

	$filters = array( '', 'nav_menu' );
	$filters = apply_filters( 'genesis_slider_exclude_taxonomies', $filters );

	return ( ! in_array( $taxonomy->name, $filters ) );

}

/**
 * Used to exclude post types from list of available post_types in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $type 'post_type' being tested
 * @return string
 */
function genesis_slider_exclude_post_types( $type ) {

	$filters = array( '', 'attachment' );
	$filters = apply_filters( 'genesis_slider_exclude_post_types', $filters );

	return ( ! in_array( $type, $filters ) );

}

/**
 * Returns Slider Option
 *
 * @param string $key key value for option
 * @return string
 */
function genesis_get_slider_option( $key ) {
	return genesis_get_option( $key, GENESIS_SLIDER_SETTINGS_FIELD );
}

/**
 * Echos Slider Option
 *
 * @param string $key key value for option
 */
function genesis_slider_option( $key ) {

	if ( ! genesis_get_slider_option( $key ) )
		return false;

	echo genesis_get_slider_option( $key );
}