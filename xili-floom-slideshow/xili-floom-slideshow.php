<?php
/**
 * Example of Floom integration in WP

 * @package Akismet

Plugin Name: xili-floom-slideshow
Plugin URI: http://dev.xiligroup.com/xili-floom-slideshow/
Description: xili-floom-slideshow integrate the floom slideshow from Oskar in wordpress theme -
Author: MS dev.xiligroup team
Version: 1.3.2
Author URI: http://dev.xiligroup.com
License:GPLv2
Text Domain: xilifloomslideshow
Domain Path: /languages/
 */

// 1.3.2 - 210325 - mootools 1.6.0 (compressed or not) - published in Github/dev-xiligroup
// 1.3.1 - 210323 - few improvements and WP standard code rules
// 1.3 - 140418 - few improvements for 3.9
// 1.2 - 130524 - caution message in js if images unavailable, __construct
// 1.1 - 120722 - setting to choose images size (default (large, medium, thumbnail) and those defined by add_image_size(); )
// 1.0 - 120411 - pre-tests  WP3.4: fixes metaboxes columns
// 0.9.9 - 111208 - fixes notices
// 0.9.8 - 111204 - enable now to display more than one slideshow
// 0.9.7 - 110601 - OOP and new like sub-select for file due to 3.1
// 0.9.5 - 101109 - integrate child theme better
// 0.9.4 add thumbnail bar with Shortcode [xilifloombar]
// 0.9.3 add gold params
// 0.9.2 add for pictures orderby menu_order - add hooks - fix quoted captions
// 0.9.1 xilitheme-select plugin compatibility, add options to fireEvents, fixes,...
// 0.9.0 First public
define( 'XILIFLOOM_VER', '1.3.2' );

/**
 * Class  xili_floom_activate
 *
 * @since 0.9.7
 */
class Xili_Floom_Activate {

	/** Settings saved

	 @var $xili_settings
	 */
	private $xili_settings = array();
	/** Variable @var */
	private $xilifloom_name_selector = '';

	private $floom_subname = ''; // 0.9.8

	private $xili_singular_images = array();

	private $shortcode_count = 0;

	private $shortcode_content = array();

	private $singular_id = 0; // id of post when no shortcode !

	/** Construct */
	public function __construct() {
		$this->xili_floom_activate();
	}

	/** Activate */
	private function xili_floom_activate() {

		register_activation_hook( __FILE__, array( &$this, 'xili_plugin_activate' ) );

		define( 'XILIFLOOM_ABSPATH', plugin_dir_path( __FILE__ ) );
		define( 'FLOOMPATH', XILIFLOOM_ABSPATH . 'floom' );
		define( 'FLOOMURL', plugins_url( '/floom', __FILE__ ) );

		/* the personalization are saved inside the theme and not plugin */
		define( 'XILIFLOOM_INCURTHEMEURL', get_bloginfo( 'stylesheet_directory' ) ); // 0.9.5 ?
		define( 'XILIFLOOM_INCURTHEMEPATH', get_stylesheet_directory() );
		define( 'XILIFLOOM_SUBFLOOMFOLDER', 'floom' );

		/**
		 *Settings

		 * @updated 0.9.4
		 */
		$this->xili_settings = get_option( 'xilifloomslideshow_settings' );
		if ( empty( $this->xili_settings ) ) {
			$this->xilifloom_default_settings();
			$this->xili_settings = get_option( 'xilifloomslideshow_settings' );
		}

		if ( $this->xili_settings['version'] < '1.5' ) {
			$this->xili_settings['version'] = '1.5';
			$this->xilifloom_default_settings();
			update_option( 'xilifloomslideshow_settings', $this->xili_settings );
		}
		if ( '1.7' > $this->xili_settings['version'] ) {
			$this->xili_settings['version'] = '1.7';
			$this->xili_settings['image_size'] = 'full'; // since 1.1 ?
			update_option( 'xilifloomslideshow_settings', $this->xili_settings );
		}
		if ( '1.8' > $this->xili_settings['version'] ) {
			$this->xili_settings['version'] = '1.8';
			$this->xilifloom_default_settings(); // update keys by default
			$this->xili_settings = get_option( 'xilifloomslideshow_settings' );
		}
		/** Browsing side */
		if ( ! is_admin() ) {
			add_shortcode( 'xilifloombar', array( &$this, 'insert_a_floombar' ) );
			add_action( 'wp_print_scripts', array( &$this, 'xilifloom_header' ) );
			add_action( 'wp_print_styles', array( &$this, 'xilifloom_stylesheet' ) );
			add_shortcode( 'xilifloom', array( &$this, 'insert_a_floom' ) );
			add_action( 'wp_head', array( &$this, 'keep_current_post_id' ), 20 ); // 0.9.8 for backward compatitility
			add_action( 'wp_footer', array( &$this, 'xilifloom_insert_scripts' ), 20 ); // 0.9.8 multiple scripts
		}
		/**
		 * Add admin menu and associated page
		 */
		if ( is_admin() ) {
			add_action( 'admin_menu', array( &$this, 'xili_addfloom_pages' ) );
			add_filter( 'plugin_action_links', array( &$this, 'filter_plugin_actions' ), 10, 2 );
		}

		/**
		* Multilingue for admin pages and menu
		*/
		load_plugin_textdomain( 'xilifloomslideshow', false, 'xili-floom-slideshow/languages' );
	}
	/**
	 * Xili_plugin_activate
	 */
	private function xili_plugin_activate() {
		$this->xili_settings = get_option( 'xilifloomslideshow_settings' );
		if ( empty( $this->xili_settings ) ) {
			$this->xilifloom_default_settings();
		}
	}

	/**
	 * Admin plugin_activate
	 */
	public function xili_addfloom_pages() {

		$this->thehook = add_options_page( __( 'xili floom slideshow', 'xilifloomslideshow' ), __( 'xili floom slideshow', 'xilifloomslideshow' ), 'import', 'floom-slideshow_page', array( &$this, 'xili_floom_active_menu' ) );
		add_action( 'load-' . $this->thehook, array( &$this, 'on_load_page' ) );
	}

	/**
	 * Admin plugin_activate
	 */
	public function on_load_page() {

		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );
		add_meta_box( 'xilifloom-sidebox-1', __( 'Message', 'xilifloomslideshow' ), array( &$this, 'on_sidebox_1_content' ), $this->thehook, 'side', 'core' );
	}

	/**
	 * Default settings
	 */
	private function xilifloom_default_settings() {
		$submitted_settings = array(
			'floom_divs' => 'blinds',
			'prefix' => 'floom_',
			'amount' => '24',
			'animation' => '80',
			'interval' => '8000',
			'axis' => 'vertical',
			'progressbar' => 'false',
			'captions' => 'true',
			'slidesbase' => '',
			'slicefxin' => array( 'top' => '20' ),
			'onslidechange' => 'empty',
			'onpreload' => 'empty',
			'container' => 'blinds-cont',
			'display' => 'block',
			'nonefunction' => '',
			'onfirst' => 'empty',
			'onlast' => 'empty',
			'goldparam' => '',
			'post_column' => 'post_name',
			'title_desc' => '',
			'image_size' => 'full',
			'version' => '1.8',
		);
		update_option( 'xilifloomslideshow_settings', $submitted_settings );
	}

	/**
	 * Shortcode [xilifloombar] adding thumbnail of all attached images in page or single -- Obsolete --
	 *
	 * @ since 0.9.4
	 */
	public function insert_a_floombar( $atts, $content = null ) {

		$arr_result = shortcode_atts(
			array(
				'floombarframe_id' => 'floombarframe_id',
				'floombar_id' => 'floombar_id',
			),
			$atts
		);
		// only if single or page - need code lines && ( array() != $this->xili_singular_images ) !
		if ( is_singular() ) {
			$nb = count( $this->xili_singular_images );
			return '<div style="background-color:00FF00;" >*' . $nb . '*</div>';
		} else {
			return '';
		}
	}


	/**
	 * Insert mootools and floom js in wp_head()
	 */
	public function xilifloom_header() {

			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '-compressed';

			wp_enqueue_script( 'mootools-core', FLOOMURL . '/js/mootools-core' . $suffix . '.js', '', '1.6.0' );
			wp_enqueue_script( 'mootools-more', FLOOMURL . '/js/mootools-more' . $suffix . '.js', array( 'mootools-core' ), '1.6.0' );
			wp_enqueue_script( 'floom', FLOOMURL . '/js/floom-1.0.js', array( 'mootools-core', 'mootools-more' ), '1.0' );
	}

	/**
	 * Select stule sheet

	 * @ updated 0.9.1 for xilitheme-select compatibility
	 */
	public function xilifloom_stylesheet() {
		global $wp_ismobile;
		if ( class_exists( 'xilithemeselector' ) ) {
			if ( $wp_ismobile->iphone ) {
				$themestylefilepath = str_replace( get_option( 'template' ), '', XILIFLOOM_INCURTHEMEPATH ) . $wp_ismobile->newfolder . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
				$themestylefileurl = str_replace( get_option( 'template' ), '', XILIFLOOM_INCURTHEMEURL ) . $wp_ismobile->newfolder . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
				$defaultstylefilepath = FLOOMPATH . '/css/floom_4touch.css';
				$defaultstylefileurl = FLOOMURL . '/css/floom_4touch.css';
			} else {
				$themestylefilepath = XILIFLOOM_INCURTHEMEPATH . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
				$themestylefileurl = XILIFLOOM_INCURTHEMEURL . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
				$defaultstylefilepath = FLOOMPATH . '/css/floom.css';
				$defaultstylefileurl = FLOOMURL . '/css/floom.css';
			}
		} else {
			$themestylefilepath = XILIFLOOM_INCURTHEMEPATH . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
			$themestylefileurl = XILIFLOOM_INCURTHEMEURL . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/floom.css';
			$defaultstylefilepath = FLOOMPATH . '/css/floom.css';
			$defaultstylefileurl = FLOOMURL . '/css/floom.css';
		}
		if ( file_exists( $themestylefilepath ) ) {
			wp_register_style( 'floomStyleSheet', $themestylefileurl, false, '1.0', 'screen' );
			wp_enqueue_style( 'floomStyleSheet' );
		} elseif ( file_exists( $defaultstylefilepath ) ) {
			wp_register_style( 'floomStyleSheet', $defaultstylefileurl, false, '1.0', 'screen' );
			wp_enqueue_style( 'floomStyleSheet' );
		}
	}

	/**
	 * Get values in current post way

	 * @since 0.9.2
	 */
	private function xilifloom_get_values( $post_ID ) {

		$xili_floom_values = array();
		$xili_floom_values['children'] = $post_ID;
		/* some vars can be set inside each post */
		$children = get_post_meta( $post_ID, 'floom_parentID', true ); /* images are kept in another post */
		if ( '' != $children ) {
			$xili_floom_values['children'] = $children;
		}

		$this->xilifloom_name_selector = get_post_meta( $post_ID, 'floom_subname', true ); /* ever lowercase = slug*/

		$title_desc = get_post_meta( $post_ID, 'floom_title_desc', true );
		if ( '' != $title_desc ) {
			$xili_floom_values['title_desc'] = $title_desc;
		}
		$floom_divs = get_post_meta( $post_ID, 'floom_divs', true );
		if ( '' != $floom_divs ) {
			$xili_floom_values['floom_divs'] = $floom_divs;
		}
		$amount = get_post_meta( $post_ID, 'floom_amount', true );
		if ( '' != $amount ) {
			$xili_floom_values['amount'] = $amount;
		}
		$interval = get_post_meta( $post_ID, 'floom_interval', true );
		if ( '' != $interval ) {
			$xili_floom_values['interval'] = $interval;
		}
		$axis = get_post_meta( $post_ID, 'floom_axis', true );
		if ( '' != $axis ) {
			$xili_floom_values['axis'] = $axis;
		}
		$captions = get_post_meta( $post_ID, 'floom_captions', true );
		if ( '' != $captions ) {
			$xili_floom_values['captions'] = $captions;
		}
		$progressbar = get_post_meta( $post_ID, 'floom_progressbar', true );
		if ( '' != $progressbar ) {
			$xili_floom_values['progressbar'] = $progressbar;
		}
		$container = get_post_meta( $post_ID, 'floom_container', true );
		if ( '' != $container ) {
			$xili_floom_values['frame_id'] = $container;
		}
		$display = get_post_meta( $post_ID, 'floom_display', true );
		if ( '' != $display ) {
			$xili_floom_values['display'] = $display;
		}
		$image_size = get_post_meta( $post_ID, 'floom_image_size', true );
		if ( '' != $display ) {
			$xili_floom_values['image_size'] = $image_size; // 1.1
		}

		return $xili_floom_values;
	}

	/**
	 * Get default values for current shortcode

	 * @since 0.9.8
	 */
	private function shortcode_get_values( $post_ID ) {

		$shortcode_floom_values = array();
		/* some vars can be set inside each post */
		$children = get_post_meta( $post_ID, 'floom_parentID', true ); /* images are kept in another post */
		$shortcode_floom_values['children'] = ( '' != $children ) ? $children : $post_ID;

		$title_desc = get_post_meta( $post_ID, 'floom_title_desc', true );
		$shortcode_floom_values['title_desc'] = ( '' != $title_desc ) ? $title_desc : $this->xili_settings['title_desc'];

		$floom_divs = get_post_meta( $post_ID, 'floom_divs', true );
		$shortcode_floom_values['blinds_id'] = ( '' != $floom_divs ) ? $floom_divs : $this->xili_settings['floom_divs']; // the slides id !

		$amount = get_post_meta( $post_ID, 'floom_amount', true );
		$shortcode_floom_values['amount'] = ( '' != $amount ) ? $amount : $this->xili_settings['amount'];

		$interval = get_post_meta( $post_ID, 'floom_interval', true );
		$shortcode_floom_values['interval'] = ( '' != $interval ) ? $interval : $this->xili_settings['interval'];

		$axis = get_post_meta( $post_ID, 'floom_axis', true );
		$shortcode_floom_values['axis'] = ( '' != $axis ) ? $axis : $this->xili_settings['axis'];

		$captions = get_post_meta( $post_ID, 'floom_captions', true );
		$shortcode_floom_values['captions'] = ( '' != $captions ) ? $captions : $this->xili_settings['captions'];

		$progressbar = get_post_meta( $post_ID, 'floom_progressbar', true );
		$shortcode_floom_values['progressbar'] = ( '' != $progressbar ) ? $progressbar : $this->xili_settings['progressbar'];

		$container = get_post_meta( $post_ID, 'floom_container', true );
		$shortcode_floom_values['frame_id'] = ( '' != $container ) ? $container : $this->xili_settings['container']; // the frame id !

		$display = get_post_meta( $post_ID, 'floom_display', true );
		$shortcode_floom_values['display'] = ( '' != $display ) ? $display : $this->xili_settings['display'];

		$image_size = get_post_meta( $post_ID, 'floom_image_size', true );
		$shortcode_floom_values['image_size'] = ( '' != $image_size ) ? $image_size : $this->xili_settings['image_size']; // 1.1

		return $shortcode_floom_values;
	}


	/**
	 * Insert javascript in header

	 * @updated 0.9.3 , 0.9.8
	 */
	public function xilifloom_insert_script( $uid = 0 ) {
		if ( has_filter( 'xili_floom_get_values' ) ) {
			$xili_floom_values = apply_filters( 'xili_floom_get_values', $this->singular_id, $uid ); // for example in theme functions.php !
		} else {
			$xili_floom_values = $this->xilifloom_get_values( $this->singular_id );
		}

		$defaults =& $this->xili_settings;
		$defaults['title_desc'] = 1;
		$floom_atts = ( isset( $this->shortcode_content[ $uid ]['floom_atts'] ) ) ? $this->shortcode_content[ $uid ]['floom_atts'] : array();
		$defaults['frame_id'] = $defaults['container']; // for compatibility; !

		/* if filter images came from other sources */

		$this->floom_subname = ( isset( $this->shortcode_content[ $uid ]['floom_atts']['floom_subname'] ) && '' != $this->shortcode_content[ $uid ]['floom_atts']['floom_subname'] ) ? $this->shortcode_content[ $uid ]['floom_atts']['floom_subname'] : $this->xilifloom_name_selector;

		$the_floom_values = array_merge( $defaults, $xili_floom_values );

		if ( 0 != $uid ) {
			$the_floom_values['children'] = $floom_atts['children'];
		}

		if ( has_filter( 'xili_floom_get_images' ) ) {
			$images = apply_filters( 'xili_floom_get_images', $the_floom_values['children'], $this->floom_subname ); // not attached !
		} else {
			$images = $this->xilifloom_get_images( $the_floom_values['children'], $this->floom_subname );
		}
		if ( is_singular() ) {
			$this->xili_singular_images = $images; // page or post single only and thumbnails div !
		}

		if ( isset( $floom_atts['frame_id'] ) ) {
			$frame_id = $floom_atts['frame_id'];
		}
		if ( isset( $floom_atts['blinds_id'] ) ) {
			$floom_divs = $floom_atts['blinds_id'];
		} else {
			$floom_divs = $the_floom_values['floom_divs'];
		}

		if ( $uid > 1 ) { // to add count to default ID !
			$thefloom = 'theFloom' . $uid;
			if ( 'blinds' == $floom_divs ) {
				$floom_divs = 'blinds-' . $uid;
			}
			if ( 'blinds-cont' == $frame_id ) {
				$frame_id = 'blinds-cont-' . $uid;
			}
		} else {
			$thefloom = 'theFloom';
		}
		?>
		<!-- added by xili-floom-slideshow plugin <?php echo XILIFLOOM_VER; ?> -->
		<script type="text/javascript" >
		window.onload = function(){
			if ( ( '<?php echo $floom_divs; ?>' ) ) {
			<?php
			/** Images src absolute url so slidesBase = '' */
			if ( array() != $images ) {
				echo 'var slides = [';
				$i = 0;
				foreach ( $images as $attach_id => $image ) {
					if ( 0 == $i ) {
						echo "{image: '";
					} else {
						echo ", {image: '";
					}
					switch ( $the_floom_values['title_desc'] ) {
						case 1:
							$caption = addslashes( $image->post_title );
							break;
						case 2:
							$caption = addslashes( $image->post_content );
							break;
						case 3:
							$caption = addslashes( $image->post_title ) . '<br/><span class="' . $this->xili_settings['prefix'] . 'desc">' . addslashes( $image->post_content ) . '</span>';
							break;
					}
					 /**
					 An array containing:
						[0] => url
						[1] => width
						[2] => height
					 */
					 $the_floom_values['size'] = ( isset( $floom_atts['image_size'] ) ) ? $floom_atts['image_size'] : 'full';

					 $image_data = wp_get_attachment_image_src( $attach_id, $size ); // 1.1

					if ( $image_data ) {
						echo $image_data[0] . "', caption: '" . $caption . "' }"; // xss !
					} else {
						echo "unavailable' }";
					}
					 $i++;
				}
				echo '];';
			}
			if ( 0 != $uid ) {
				if ( isset( $floom_atts['display'] ) ) {
					$display = $floom_atts['display'];
				}
			}

			if ( array() == $images ) {
				if ( '' == $nonefunction ) {
					/* container is not displayed */
					?>
					$('<?php echo $frame_id; ?>'). setStyle('display', '<?php echo $display; ?>');
					$('<?php echo $frame_id; ?>').set('html','<p style="color:red"><?php _e( 'no image: see again params', 'xilifloomslideshow'); ?></p>');
				<?php
				} else {
					echo $nonefunction . '();'; // xss !
				}
			} else {
				// if ( $uid != 0 ) { // value in shortcode !
				if ( isset( $floom_atts['axis'] ) ) {
					$axis = $floom_atts['axis'];
				} else {
					$axis = $the_floom_values['axis'];
				}
				if ( isset( $floom_atts['captions'] ) ) {
					$captions = $floom_atts['captions'];
				} else {
					$axis = $the_floom_values['captions'];
				}
				if ( isset( $floom_atts['progressbar'] ) ) {
					$progressbar = $floom_atts['progressbar'];
				} else {
					$axis = $the_floom_values['progressbar'];
				}
				if ( isset( $floom_atts['amount'] ) ) {
					$amount = $floom_atts['amount'];
				} else {
					$axis = $the_floom_values['amount'];
				}
				if ( isset( $floom_atts['interval'] ) ) {
					$interval = $floom_atts['interval'];
				} else {
					$axis = $the_floom_values['interval'];
				}

				?>
				// Floom or xiliFloom
				<?php echo $thefloom; ?> = new <?php echo $this->xili_settings['goldparam']; ?>Floom('<?php echo $floom_divs; ?>',slides, {
				prefix: '<?php echo $this->xili_settings['prefix']; // xss ! ?>',
				amount: <?php echo $amount; ?>,
				animation: <?php echo $this->xili_settings['animation']; /** Only global settings */ ?>,
				interval: <?php echo $interval; ?>,
				axis: '<?php echo $axis; ?>',
				slidesBase: '<?php echo $this->xili_settings['slidesbase']; /** Only global settings */ ?>',
				sliceFxIn: {top: <?php echo $this->xili_settings['slicefxin']['top']; /** Only global settings */ ?>},
				captions: <?php echo $captions; ?>,
				progressbar: <?php echo $progressbar;
				if ( isset( $the_floom_values['onslidechange'] ) && 'empty' != $the_floom_values['onslidechange']) {
					?>,
				onSlideChange: function(curslide){
					<?php echo $the_floom_values['onslidechange']; ?>(curslide);}
				<?php }
				if ( isset($the_floom_values['onpreload'] ) && 'empty' != $the_floom_values['onpreload'] ) {
					?>,
				onPreload: function(curslide){
					<?php echo $the_floom_values['onpreload'] ; ?>(curslide);}
				<?php } /** Other documented events (onFirst, onLast - not yet in floom js */
				/** extended functions */
				if ( '' != $this->xili_settings['goldparam'] ) {
					if ( isset( $the_floom_values['onlast'] ) && 'empty' != $the_floom_values['onlast'] ) {
						?>,
				onLast: function(curslide,s){
						<?php echo $the_floom_values['onlast']; ?>(curslide,s);}
					<?php }
					if ( isset( $the_floom_values['onfirst'] ) && 'empty' != $the_floom_values['onfirst'] ) {
						?>,
				onFirst: function(curslide){
						<?php echo $the_floom_values['onfirst']; ?>(curslide);}
					<?php }
					do_action( 'xili_floom_events', $the_xili_floom_values ); /* to add other events 0.9.3*/
				}
				?>

				});

			<?php } ?>
			};
		};

		</script>
		<?php
	}

	/**
	 * Create list of images (and caption) from post_id

	 * @updated 0.9.2 - add orderby - 0.9.3 - sub-selection as LIKE in sql with one or two %
	 */
	private function xilifloom_get_images( $post_ID, $post_name_selector ) {
		if ( '' != $post_name_selector ) {
			add_filter( 'posts_where_request', array( &$this, 'where_post_name_subselect' ) );
			$images = get_children( 'post_parent=' . $post_ID . '&post_type=attachment&post_mime_type=image&orderby=menu_order&order=asc&suppress_filters=0' );
			remove_filter( 'posts_where_request', array( &$this, 'where_post_name_subselect' ) );
		} else {
			$images = get_children( 'post_parent=' . $post_ID . '&post_type=attachment&post_mime_type=image&orderby=menu_order&order=asc' );
		}

		return $images;
	}

	/**
	 * Filter list of images with post_name

	 * @since 0.9.3 - sub-selection
	 * @updated 0.9.6 - via guid because post_name don't contains name of file as before 3.0
	 * @updated 0.9.8
	 */
	private function where_post_name_subselect( $where ) {
		global $wpdb;
		$column = $this->xili_settings['post_column'];
		if ( 'guid' == $column ) {
			$like_str = ( '%' != substr( $this->floom_subname, 0, 1 ) ) ? '%' . $this->floom_subname : $this->floom_subname;
		} else {
			$like_str = $this->floom_subname;
		}
		$where .= " AND $wpdb->posts.$column LIKE '" . $like_str . "' ";
		return $where;
	}

	/**
	 * Fill shortcode with datas
	 * shortcode params : frame_id = id of the frame's div, blinds_id = id of the images and blinds divs
	 *
	 */
	public function insert_a_floom( $atts, $content = null ) {
		global $post;
		$this->shortcode_count++;  // increment default value !

		$default_atts = $this->shortcode_get_values( $post->ID ); // general if not set in post !
		$default_atts['floom_subname'] = '';

		$arr_result = shortcode_atts( $default_atts, $atts );

		$this->shortcode_content[ $this->shortcode_count ] = array(
			'post_ID' => $post->ID,
			'floom_atts' => $arr_result,
		);

		if ( $this->shortcode_count > 1 && 'blinds-cont' == $arr_result['frame_id'] ) {
			$arr_result['frame_id'] = 'blinds-cont-' . $this->shortcode_count;
			$arr_result['blinds_id'] = 'blinds-' . $this->shortcode_count;
		}
		return '<div id="' . $arr_result['frame_id'] . '"><div id="' . $arr_result['blinds_id'] . '"></div></div>';
	}

	/**
	 * Insert domready and content now in footer with results of shortcodes
	 *
	 * @since 0.9.8
	 *
	 */
	public function xilifloom_insert_scripts() {
		if ( $this->shortcode_count < 1 ) {
			$this->xilifloom_insert_script();
		} else {
			for ( $uid = 1; $uid <= $this->shortcode_count; $uid++ ) {
				$this->xilifloom_insert_script( $uid );
			}
		}
	}

	public function keep_current_post_id() {
		global $post;
		$this->singular_id =& $post->ID; // 0.9.9 notice !
	}




	/*************** admin ****************/
	/**
	 * Add action link(s) to plugins page
	 *
	 * @since 0.9.3
	 * @author MS
	 * @copyright Dion Hulse, http://dd32.id.au/wordpress-plugins/?configure-link and scripts@schloebe.de
	 */
	public function filter_plugin_actions( $links, $file ) {
		static $this_plugin;
		if ( ! $this_plugin ) {
			$this_plugin = plugin_basename( __FILE__ );
		}
		if ( $file == $this_plugin ) {
			$settings_link = '<a href="options-general.php?page=floom-slideshow_page">' . __( 'Settings' ) . '</a>';
			$links = array_merge( array( $settings_link ), $links ); // before other links !
		}
		return $links;
	}
	public function on_sidebox_1_content( $data ) {
		extract( $data );
		?>
		<h4><?php esc_html_e( 'Note:', 'xilifloomslideshow' ); ?></h4>
		<p><?php echo $message; // xss !?></p>
		<p><?php echo $action; // xss !?></p>
		<?php
	}

	public function on_normal_1_content( $data ) {
		// extract( $data ); !
		?>

		<p><cite><a href='http://wordpress.org/plugins/xili-floom-slideshow/' target='_blank'>xili-floom-slideshow</a></cite>&nbsp;&nbsp;<?php esc_html_e( 'plugin integrates the modules of Floom slideshow in your current theme and detect the specific configurations for Floom in your theme.', 'xilifloomslideshow' ); ?></p>
		<p>-&nbsp;
			<?php
			esc_html_e( 'The current theme is here', 'xilifloomslideshow' );
			echo ': <em>' . get_bloginfo( 'stylesheet_directory' ); // xss ok !
			?>
		. </em><br /><br />-&nbsp;
			<?php
			esc_html_e( 'The active floom.css is here', 'xilifloomslideshow' );
			$themejsfolder = XILIFLOOM_INCURTHEMEPATH . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/';
			if ( file_exists( $themejsfolder . '/css/floom.css' ) ) {
				echo ': <em>' . XILIFLOOM_INCURTHEMEURL . '/' . XILIFLOOM_SUBFLOOMFOLDER . '/css/'; // xss ok !
			} else {
				echo ': <em>' . FLOOMURL . '/css/'; // xss ok !
			}
		?>
	. </em><br /><br />-&nbsp;
			<?php
			if ( class_exists( 'xilithemeselector' ) ) {
				esc_html_e( 'xilitheme-select plugin active', 'xilifloomslideshow' );
			}
			?>
			.<br />&nbsp;

		<?php
	}

	public function on_normal_2_content( $data ) {
		$this->xili_settingsaved = get_option( 'xilifloomslideshow_settings' );

		$update_nonce = wp_create_nonce( 'xilifloomoptions' );
		?>
		<p><?php esc_html_e( 'The Floom slideshow javascript contains a lot of parameters. Instead modifying source, here, you can change some parameters for your whole site. Some settings are possible for one post (see docs).', 'xilifloomslideshow' ); ?></p><br/>
		<fieldset style="margin:2px; padding:12px 6px; border:1px solid #ccc;"><legend><?php esc_html_e( 'Parameters as currently saved', 'xilifloomslideshow' ); ?></legend>
		<?php print_r( $this->xili_settingsaved ); ?>
		</fieldset>
		<br/>
		<fieldset style="margin:2px; padding:12px 6px; border:1px solid #ccc;"><legend><?php esc_html_e( 'Parameters list', 'xilifloomslideshow' ); ?></legend>
		<label for="paramname"><?php esc_html_e( 'Parameter:', 'xilifloomslideshow' ); ?>&nbsp;
		<select id="paramname" name="paramname">
		<option>???</option>
		<?php
		$paramnames = array_keys( $this->xili_settingsaved );
		foreach ( $paramnames as $paramname ) {
			if ( 'version' != $paramname && 'slidesbase' != $paramname && 'goldparam' != $paramname ) {
				if ( '' == $this->xili_settingsaved['goldparam'] && 'onfirst' != $paramname && 'onlast' != $paramname ) {
					echo '<option>' . $paramname . '</option>'; // xss ok.
				} elseif ( '' != $this->xili_settingsaved['goldparam'] ) {
					echo '<option>' . $paramname . '</option>'; // xss ok.
				}
			}
		}
		?>
		</select></label>
		<label for="paramval"><input id="paramval" name="paramval" /></label>
		<div class='submit'>
			<input id='updateparams' name='updateparams' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xilifloomslideshow' ); ?>" /></div>
		</fieldset>
		<br/>
		<fieldset style="margin:2px; padding:12px 6px; border:1px solid #ccc;"><legend><?php esc_html_e( 'special gold parameters', 'xilifloomslideshow' ); ?></legend>
		<p><?php esc_html_e( 'These parameters are reserved if extended js class of the original Floom class is used', 'xilifloomslideshow' ); ?></p>
		<label for="goldparam"><?php esc_html_e( 'Parameter:', 'xilifloomslideshow' ); ?>&nbsp;
		<select id="goldparam" name="goldparam">
		<?php $checked = ( 'xili' === $this->xili_settingsaved['goldparam'] ) ? 'selected = "selected"' : ''; ?>
		<option value=""><?php esc_html_e( 'Gold inactive', 'xilifloomslideshow' ); ?></option>
		<option value="xili"
		<?php
		echo $checked;// xss ok !
		?>
		>
		<?php
		esc_html_e( 'Gold active', 'xilifloomslideshow' );
		?>
		</option>
		</select></label>
		<div class='submit'>
			<input id='setgoldparams' name='setgoldparams' type='submit' tabindex='6' value="<?php esc_html_e( 'Update', 'xilifloomslideshow' ); ?>" /></div>
		</fieldset>
		<p><?php _e( "For more info about javascript see Floom documentation. Floom slideshow javascript was designed by Oskar Krawczyk (<a href='http://nouincolor.com/' target='_blank' >http://nouincolor.com/</a>) under MIT license. This plugin for Wordpress don't modify the original js source.", 'xilifloomslideshow' );?></p>
		<?php
		echo wp_nonce_field( 'xilifloomoptions', '_ajax_nonce', true, false ); // xss !
	}

	public function xili_floom_active_menu() {
		$message = '';
		$action = '';
		if ( isset( $_POST['updateparams'] ) ) {
				$action = 'updateparams';
		}
		if ( isset( $_POST['setgoldparams'] ) ) {
				$action = 'setgoldparams';
		}
		$message = $action;
		switch ( $action ) {
			case 'updateparams':
				$paramname = $_POST['paramname'];
				$paramval = $_POST['paramval'];
				if ( $paramname != '???' && '' != $paramval ) {
					if ( 'slicefxin' == $paramname ) {
						$paramvalarr = explode( '=>', $paramval );
						$key = $paramvalarr[0];
						$this->xili_settings[ $paramname ][ $key ] = $paramvalarr[1];
					} else {
						$this->xili_settings[ $paramname ] = $paramval;
					}
					update_option( ' xilifloomslideshow_settings', $this->xili_settings );
					$message .= ' ok (' . $paramname . ' = ' . $paramval . ') ';
				} else {
					$message .= ' incorrect value';
				}
				break;
			case 'setgoldparams': /* since 0.9.3 */
				$paramval = $_POST['goldparam'];
				$this->xili_settings['goldparam'] = $paramval;
				update_option( 'xilifloomslideshow_settings', $this->xili_settings );
				$displaypar = ( '' != $paramval ) ? 'active' : 'inactive';
				$message .= ' ok (gold param changed to ' . $displaypar . ') ';
				break;
			default:
				$message = ' ';
		}

		$data = array(
			'message' => $message,
			'action' => $action,
		);
		/* register the main boxes always available */
			add_meta_box( 'xilifloom-normal-1', __( 'Style Settings', 'xilifloomslideshow' ), array( &$this, 'on_normal_1_content' ), $this->thehook, 'normal', 'core' );
			add_meta_box( 'xilifloom-normal-2', __( 'SlideShow Settings', 'xilifloomslideshow' ), array( &$this, 'on_normal_2_content' ), $this->thehook, 'normal', 'core' );
		?>
		<div id="xilifloom-settings" class="wrap">

			<h2><?php esc_html_e( 'xili-floom-slideshow settings', 'xilifloomslideshow' ); ?></h2>

			<?php
			global $wp_version;
			if ( version_compare( $wp_version, '3.3.9', '<' ) ) {
				$poststuff_class = 'class="metabox-holder has-right-sidebar"';
				$postbody_class = '';
				$postleft_id = '';
				$postright_id = 'side-info-column';
				$postleft_class = '';
				$postright_class = 'inner-sidebar';
			} else { // 3.4
				$poststuff_class = '';
				$postbody_class = 'class="metabox-holder columns-2"';
				$postleft_id = 'id="postbox-container-2"';
				$postright_id = 'postbox-container-1';
				$postleft_class = 'class="postbox-container"';
				$postright_class = 'postbox-container';
			}
			?>


			<form name="add" id="add" method="post" action="options-general.php?page=floom-slideshow_page">

					<?php wp_nonce_field( 'xilifloom-settings' ); ?>
					<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
					<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
					<div id="poststuff"  <?php echo $poststuff_class; ?> >

						<div id="post-body" <?php echo $postbody_class; ?> >
							<div id="<?php echo $postright_id; ?>" class="<?php echo $postright_class; ?>">
								<?php do_meta_boxes( $this->thehook, 'side', $data ); ?>
							</div>
							<div id="post-body-content" >
								<div <?php echo $postleft_id; ?> <?php echo $postleft_class; ?> style="min-width:360px">
									<?php do_meta_boxes( $this->thehook, 'normal', $data ); ?>
								</div>
								<h4><a href="http://wiki.xiligroup.org" title="Plugin page and docs" target="_blank" style="text-decoration:none" ><img style="vertical-align:middle" src="<?php echo plugins_url( 'images/xilifloom-logo-32.png', __FILE__ ); ?>" alt="xili-floom logo"/>  xili-floom-slideshow</a> - © <a href="http://dev.xiligroup.com" target="_blank" title="<?php _e( 'Author' ); ?>" >xiligroup.com</a>™ - msc 2009-2014-21 - v. <?php echo XILIFLOOM_VER; ?></h4>
							</div>
						</div>
					</div>
			</form>
		</div>
		<script type="text/javascript">
				//<![CDATA[
				jQuery(document).ready( function($) {
					// close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					// postboxes setup
					postboxes.add_postbox_toggles('<?php echo $this->thehook; ?>');
				});
				//]]>
			</script>
		<?php
	}
} // end of class

/**
 *Instantiation of xili_floom_activate class
 *
 * @since 0.9.7
 */

$xili_floom_activate = new Xili_Floom_Activate();


/**
 * As previous function before class
 *
 * @since 0.9.7
 * @updated 0.9.8 with post_id as params
 */
function xilifloom_get_values( $post_id = 0 ) {
	global $xili_floom_activate;
	return $xili_floom_activate->xilifloom_get_values( $post_id );
}

/**
 * Replace previous global $xilifloom_name_selector - floom_subname in postmeta
 *
 * @since 0.9.7
 */
function set_xilifloom_name_selector( $thelike = '' ) {
	global $xili_floom_activate;
	$xili_floom_activate->xilifloom_name_selector = $thelike;
}


/* ©xiligroup.com - 2009 - 2014 - rw 2021 */
