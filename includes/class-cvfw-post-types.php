<?php
/**
 * Registers custom post types for Cart Validation for WooCommerce.
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
 * CVFW Post Types.
 *
 * @since 1.0.0
 */
class CVFW_Post_Types {

	/**
	 * Initialize and register custom post types.
	 *
	 * @since 1.0.0
	 */
	public static function init() {
		self::register_post_types();
		self::set_capabilities();
		add_filter( 'post_updated_messages', array( __CLASS__, 'updated_messages' ) );
		add_filter( 'bulk_post_updated_messages', array( __CLASS__, 'bulk_updated_messages' ), 10, 2 );
	}

	/**
	 * Register custom post types.
	 *
	 * @since 1.0.0
	 */
	public static function register_post_types() {
		$labels = array(
			'name'               => __( 'Validation Rules', 'cart-validation-for-woocommerce' ),
			'singular_name'      => __( 'Validation Rule', 'cart-validation-for-woocommerce' ),
			'menu_name'          => _x( 'Validation Rules', 'Admin menu name', 'cart-validation-for-woocommerce' ),
			'add_new'            => __( 'Add Rule', 'cart-validation-for-woocommerce' ),
			'add_new_item'       => __( 'Add New Rule', 'cart-validation-for-woocommerce' ),
			'edit_item'          => __( 'Edit Rule', 'cart-validation-for-woocommerce' ),
			'new_item'           => __( 'New Rule', 'cart-validation-for-woocommerce' ),
			'view_item'          => __( 'View Rule', 'cart-validation-for-woocommerce' ),
			'search_items'       => __( 'Search Rules', 'cart-validation-for-woocommerce' ),
			'not_found'          => __( 'No rules found', 'cart-validation-for-woocommerce' ),
			'not_found_in_trash' => __( 'No rules found in trash', 'cart-validation-for-woocommerce' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Cart validation rules for WooCommerce.', 'cart-validation-for-woocommerce' ),
			'public'              => false,
			'show_ui'             => true,
			'capability_type'     => CVFW_RULE_POST_TYPE,
			'map_meta_cap'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_menu'        => false,
			'hierarchical'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'title' ),
			'show_in_nav_menus'   => false,
		);

		register_post_type( CVFW_RULE_POST_TYPE, $args );
	}

	/**
	 * Set capabilities for shop_manager and administrator.
	 *
	 * @since 1.0.0
	 */
	private static function set_capabilities() {
		$wp_roles = wp_roles();
		if ( ! is_object( $wp_roles ) ) {
			return;
		}

		$args = (object) array(
			'map_meta_cap'    => true,
			'capability_type' => CVFW_RULE_POST_TYPE,
			'capabilities'     => array(),
		);
		$caps = get_post_type_capabilities( $args );
		foreach ( (array) $caps as $cap ) {
			$wp_roles->add_cap( 'shop_manager', $cap );
			$wp_roles->add_cap( 'administrator', $cap );
		}
	}

	/**
	 * Customize updated messages for the CPT.
	 *
	 * @since 1.0.0
	 * @param array $messages Messages.
	 * @return array
	 */
	public static function updated_messages( $messages ) {
		$messages[ CVFW_RULE_POST_TYPE ] = array(
			0  => '',
			1  => __( 'Rule updated.', 'cart-validation-for-woocommerce' ),
			2  => __( 'Custom field updated.', 'cart-validation-for-woocommerce' ),
			3  => __( 'Custom field deleted.', 'cart-validation-for-woocommerce' ),
			4  => __( 'Rule updated.', 'cart-validation-for-woocommerce' ),
			5  => '',
			6  => __( 'Rule published.', 'cart-validation-for-woocommerce' ),
			7  => __( 'Rule saved.', 'cart-validation-for-woocommerce' ),
			8  => '',
			9  => '',
			10 => __( 'Rule draft updated.', 'cart-validation-for-woocommerce' ),
		);
		return $messages;
	}

	/**
	 * Bulk updated messages.
	 *
	 * @since 1.0.0
	 * @param array $messages Messages.
	 * @param array $bulk_counts Counts.
	 * @return array
	 */
	public static function bulk_updated_messages( $messages, $bulk_counts ) {
		$messages[ CVFW_RULE_POST_TYPE ] = array(
			'updated'   => _n( '%s rule updated.', '%s rules updated.', $bulk_counts['updated'], 'cart-validation-for-woocommerce' ),
			'locked'    => _n( '%s rule not updated, somebody is editing it.', '%s rules not updated.', $bulk_counts['locked'], 'cart-validation-for-woocommerce' ),
			'deleted'   => _n( '%s rule permanently deleted.', '%s rules permanently deleted.', $bulk_counts['deleted'], 'cart-validation-for-woocommerce' ),
			'trashed'   => _n( '%s rule moved to the Trash.', '%s rules moved to the Trash.', $bulk_counts['trashed'], 'cart-validation-for-woocommerce' ),
			'untrashed' => _n( '%s rule restored from the Trash.', '%s rules restored.', $bulk_counts['untrashed'], 'cart-validation-for-woocommerce' ),
		);
		return $messages;
	}
}
