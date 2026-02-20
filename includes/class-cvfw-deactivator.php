<?php

/**
 * Fired during plugin deactivation.
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
 * Fired during plugin deactivation.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_Deactivator {

	/**
	 * Plugin deactivation function.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Placeholder for any cleanup (e.g. flush rewrite rules, clear transients).
	}
}
