<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://profiles.wordpress.org/sahilmultani/
 * @since             1.0.0
 * @package           Cart_Validation_For_WooCommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Cart Validation for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/cart-validation-for-woocommerce/
 * Description:       Restrict WooCommerce checkout by products, categories, user roles, and more. Create powerful cart validation rules.
 * Version:           1.0.0
 * Author:            Sahil Multani
 * Author URI:        https://profiles.wordpress.org/sahilmultani/
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       cart-validation-for-woocommerce
 * Domain Path:       /languages
 * Requires Plugins:  woocommerce
 *
 * WC requires at least: 5.0.0
 * WC tested up to: 10.5.2
 * WP tested up to: 6.9.1
 * Requires PHP: 7.4
 * Requires at least: 5.0
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// Define plugin constants.
if ( ! defined( 'CVFW_PLUGIN_FILE' ) ) {
	define( 'CVFW_PLUGIN_FILE', __FILE__ );
}
if ( ! defined( 'CVFW_PLUGIN_DIR' ) ) {
	define( 'CVFW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'CVFW_PLUGIN_URL' ) ) {
	define( 'CVFW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'CVFW_PLUGIN_BASENAME' ) ) {
	define( 'CVFW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Currently plugin version.
 *
 * @since 1.0.0
 */
if ( ! defined( 'CVFW_VERSION' ) ) {
	define( 'CVFW_VERSION', '1.0.0' );
}

/**
 * Custom post type for validation rules.
 *
 * @since 1.0.0
 */
if ( ! defined( 'CVFW_RULE_POST_TYPE' ) ) {
	define( 'CVFW_RULE_POST_TYPE', 'cvfw_validation_rule' );
}

/**
 * Check if WooCommerce is active.
 *
 * @since 1.0.0
 * @return bool
 */
function cvfw_is_woocommerce_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );

	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}

	return in_array( 'woocommerce/woocommerce.php', $active_plugins, true )
		|| array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
}

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 */
function cvfw_activate_cart_validation_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cvfw-activator.php';
	CVFW_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 */
function cvfw_deactivate_cart_validation_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cvfw-deactivator.php';
	CVFW_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'cvfw_activate_cart_validation_for_woocommerce' );
register_deactivation_hook( __FILE__, 'cvfw_deactivate_cart_validation_for_woocommerce' );

/**
 * The core plugin class.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cvfw.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 */
function cvfw_run_cart_validation_for_woocommerce() {
	if ( cvfw_is_woocommerce_active() ) {
		add_action( 'before_woocommerce_init', 'cvfw_declare_woocommerce_compatibility' );
		$plugin = new CVFW_Cart_Validation_For_WooCommerce();
		$plugin->run();
	} else {
		add_action( 'admin_notices', 'cvfw_woocommerce_missing_notice' );
	}
}

/**
 * Declare HPOS and Cart/Checkout Blocks compatibility.
 *
 * @since 1.0.0
 */
function cvfw_declare_woocommerce_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
}

/**
 * Display admin notice if WooCommerce is not active.
 *
 * @since 1.0.0
 */
function cvfw_woocommerce_missing_notice() {
	?>
	<div class="notice notice-error">
		<p>
			<?php
			esc_html_e( 'Cart Validation for WooCommerce requires WooCommerce to be installed and active. You can download', 'cart-validation-for-woocommerce' );
			?>
			<a href="https://woocommerce.com/" target="_blank" rel="noopener noreferrer">WooCommerce</a>
			<?php esc_html_e( 'here.', 'cart-validation-for-woocommerce' ); ?>
		</p>
	</div>
	<?php
}

cvfw_run_cart_validation_for_woocommerce();
