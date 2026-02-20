<?php

/**
 * Define the internationalization functionality.
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
 * Define the internationalization functionality.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * When the plugin is hosted on WordPress.org, translations are loaded automatically.
	 * This method is kept for compatibility; no manual load_plugin_textdomain() call is needed.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		// Translations are loaded automatically by WordPress for plugins on WordPress.org.
	}
}
