<?php
/**
 * Plugin Name:			Storefront Horizontal Tabs
 * Plugin URI:			https://wordpress.org/plugins/storefont-horizontal-tabs/
 * Description:			Storefront Horizontal Tabs changes the layout of the tabs from vertical to horizontal - Similar what is used on the main WooCommerce.com website
 * Version:				1.0
 * Author:				Riaan K. | WooCommerce
 * Author URI:			http://woocommerce.com/
 * Requires at least:	5.0
 * Tested up to:		5.2
 *
 * Text Domain: storefont-horizontal-tabs
 * Domain Path: /languages/
 *
 * @package Storefront_Horizontal_Tabs
 * @category Core
 * @author Tiago Noronha
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Returns the main instance of Storefront_Horizontal_Tabs to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Storefront_Horizontal_Tabs
 */
function Storefront_Horizontal_Tabs() {
	return Storefront_Horizontal_Tabs::instance();
} // End Storefront_Horizontal_Tabs()

Storefront_Horizontal_Tabs();

/**
 * Main Storefront_Horizontal_Tabs Class
 *
 * @class Storefront_Horizontal_Tabs
 * @version	1.0.0
 * @since 1.0.0
 * @package	Storefront_Horizontal_Tabs
 */
final class Storefront_Horizontal_Tabs {
	/**
	 * Storefront_Horizontal_Tabs The single instance of Storefront_Horizontal_Tabs.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {
		$this->token 			= 'storefont-horizontal-tabs';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'sht_load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'sht_setup' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'sht_plugin_links' ) );
	}

	/**
	 * Main Storefront_Horizontal_Tabs Instance
	 *
	 * Ensures only one instance of Storefront_Horizontal_Tabs is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Storefront_Horizontal_Tabs()
	 * @return Main Storefront_Horizontal_Tabs instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function sht_load_plugin_textdomain() {
		load_plugin_textdomain( 'storefont-horizontal-tabs', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Plugin page links
	 *
	 * @since  1.0.0
	 */
	public function sht_plugin_links( $links ) {
		$plugin_links = array(
			'<a href="https://woocommerce.com/my-account/tickets/">' . __( 'Support', 'storefont-horizontal-tabs' ) . '</a>',
		);

		return array_merge( $plugin_links, $links );
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Setup all the things.
	 * Only executes if Storefront or a child theme using Storefront as a parent is active and the extension specific filter returns true.
	 * Child themes can disable this extension using the storefront_horizontal_tabs_supported filter
	 * @return void
	 */
	public function sht_setup() {
		$theme = wp_get_theme();

		if ( 'Storefront' == $theme->name || 'storefront' == $theme->template && apply_filters( 'storefront_horizontal_tabs_supported', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'sht_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'sht_add_customizer_css' ), 999 );
			add_action( 'customize_preview_init', array( $this, 'sht_customize_preview_js' ) );
			add_filter( 'body_class', array( $this, 'sht_body_class' ) );
		}
		 else {
			add_action( 'admin_notices', array( $this, 'sht_install_storefront_notice' ) );
		}
	}

	/**
	 * Storefront install
	 * If the user activates the plugin while having a different parent theme active, prompt them to install Storefront.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sht_install_storefront_notice() {
		echo '<div class="notice is-dismissible updated">
				<p>' . __( 'Storefront Horizontal Tabs requires that you use Storefront as your parent theme.', 'storefont-horizontal-tabs' ) . ' <a href="' . esc_url( wp_nonce_url( self_admin_url( 'update.php?action=install-theme&theme=storefront' ), 'install-theme_storefront' ) ) .'">' . __( 'Install Storefront now', 'storefont-horizontal-tabs' ) . '</a></p>
			</div>';
	}

	/**
	 * Enqueue CSS and custom styles.
	 * @since   1.0.0
	 * @return  void
	 */
	public function sht_styles() {
		wp_enqueue_style( 'shm-styles', plugins_url( '/assets/css/style.css', __FILE__ ), '', $this->version );
		wp_enqueue_script( 'shm-scripts', plugins_url( '/assets/js/frontend.js', __FILE__ ), array( 'jquery' ), $this->version, true );

		$translation_array = array(
			'close' => __( 'Close', 'storefont-horizontal-tabs' )
		);

		wp_localize_script( 'shm-scripts', 'sht_i18n', $translation_array );
	}

	/**
	 * Add CSS in <head> for styles handled by the Customizer
	 *
	 * @since 1.0.0
	 */
	public function sht_add_customizer_css() {
		$header_background_color	= sanitize_text_field( get_theme_mod( 'storefront_header_background_color', apply_filters( 'storefront_default_header_background_color', '#2c2d33' ) ) );

		$header_link_color			= sanitize_text_field( get_theme_mod( 'storefront_header_link_color', apply_filters( 'storefront_default_header_link_color', '#cccccc' ) ) );

		$header_text_color			= sanitize_text_field( get_theme_mod( 'storefront_header_text_color', apply_filters( 'storefront_default_header_text_color', '#404040' ) ) );

		$wc_style = '
			@media screen and (min-width: 768px) {

				.storefont-horizontal-tabs-active #content .woocommerce-MyAccount-navigation {
					background-color: ' . storefront_adjust_color_brightness( $header_background_color, -13 ) . ';
				}
				.storefont-horizontal-tabs-active #content .woocommerce-MyAccount-navigation li.is-active {
					background-color: ' . $header_background_color . ';
				}

				.storefont-horizontal-tabs-active #content .woocommerce-MyAccount-navigation li a {
					color: ' . $header_link_color . ';
				}

				.storefont-horizontal-tabs-active #content .woocommerce-MyAccount-navigation li.is-active a {
					color: ' . $header_text_color . ';
				}

			}
		';

		wp_add_inline_style( 'storefront-style', $wc_style );
	}

	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 *
	 * @since  1.0.0
	 */
	public function sht_customize_preview_js() {
		wp_enqueue_script( 'shm-customizer', plugins_url( '/assets/js/customizer.js', __FILE__ ), array( 'customize-preview' ), $this->version, true );
	}

	/**
	 * Storefront Extension Boilerplate Body Class
	 * Adds a class based on the extension name and any relevant settings.
	 */
	public function sht_body_class( $classes ) {
		global $storefront_version;

		if ( version_compare( $storefront_version, '2.5.0', '>=' ) ) {
			$classes[] = 'storefront-2-5';
		}

		$classes[] = 'storefont-horizontal-tabs-active';

		return $classes;
	}
} // End Class
