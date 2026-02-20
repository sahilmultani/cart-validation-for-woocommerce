<?php

/**
 * Fired during plugin activation.
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
 * Fired during plugin activation.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_Activator {

	/**
	 * Plugin activation function.
	 *
	 * Check for WooCommerce and initialize default settings.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		if ( ! function_exists( 'cvfw_is_woocommerce_active' ) || ! cvfw_is_woocommerce_active() ) {
			deactivate_plugins( CVFW_PLUGIN_BASENAME );
			wp_die(
				esc_html__( 'Cart Validation for WooCommerce requires WooCommerce to be installed and active.', 'cart-validation-for-woocommerce' ),
				esc_html__( 'Error', 'cart-validation-for-woocommerce' ),
				array( 'back_link' => true )
			);
		}

		self::init_default_settings();
	}

	/**
	 * Initialize default plugin settings.
	 *
	 * @since 1.0.0
	 */
	private static function init_default_settings() {
		$defaults = array(
			'cvfw_enabled'                  => 'yes',
			'cvfw_default_validation_error' => __( 'Your cart contains items that cannot be purchased together. Please review the cart and remove incompatible items.', 'cart-validation-for-woocommerce' ),
			'cvfw_stop_at_first_validation' => 'no',
		);

		foreach ( $defaults as $option_name => $default_value ) {
			if ( false === get_option( $option_name, false ) ) {
				update_option( $option_name, $default_value );
			}
		}
	}
}
