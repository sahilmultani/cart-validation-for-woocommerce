<?php
/**
 * The core plugin class.
 *
 * @link       https://profiles.wordpress.org/sahilmultani/
 * @since      1.0.0
 *
 * @package    Cart_Validation_For_WooCommerce
 * @subpackage Cart_Validation_For_WooCommerce/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The core plugin class.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_Cart_Validation_For_WooCommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    CVFW_Loader $loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $plugin_name
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string $version
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->version     = defined( 'CVFW_VERSION' ) ? CVFW_VERSION : '1.0.0';
		$this->plugin_name = 'cart-validation-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		add_filter( 'plugin_action_links_' . CVFW_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		$this->loader->add_action( 'init', $this, 'init_plugin', 5 );
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cvfw-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cvfw-i18n.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cvfw-post-types.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cvfw-validation-rule.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cvfw-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cvfw-public.php';

		$this->loader = new CVFW_Loader();
	}

	/**
	 * Initialize plugin (post types, etc.).
	 *
	 * @since 1.0.0
	 */
	public function init_plugin() {
		CVFW_Post_Types::init();
	}

	/**
	 * Define the locale for internationalization.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function set_locale() {
		// When hosted on WordPress.org, translations are loaded automatically (WP 4.6+).
		// No manual load_plugin_textdomain() call; Plugin Check discourages it for .org plugins.
	}

	/**
	 * Register all of the hooks related to the admin area.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new CVFW_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		$plugin_public = new CVFW_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Cart validation runs from public class.
		$this->loader->add_action( 'woocommerce_check_cart_items', $plugin_public, 'validate_cart', 10 );
		$this->loader->add_action( 'woocommerce_before_checkout_process', $plugin_public, 'validate_cart', 5 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it.
	 *
	 * @since  1.0.0
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks.
	 *
	 * @since  1.0.0
	 * @return CVFW_Loader The loader.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 1.0.0
	 * @param array $links Plugin action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=cart_validation' ) ) . '" aria-label="' . esc_attr__( 'View Cart Validation settings', 'cart-validation-for-woocommerce' ) . '">' . esc_html__( 'Settings', 'cart-validation-for-woocommerce' ) . '</a>',
		);
		return array_merge( $action_links, $links );
	}
}
