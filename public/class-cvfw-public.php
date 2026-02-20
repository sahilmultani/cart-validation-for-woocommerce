<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/sahilmultani/
 * @since      1.0.0
 *
 * @package    Cart_Validation_For_WooCommerce
 * @subpackage Cart_Validation_For_WooCommerce/public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * The public-facing functionality of the plugin.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/cart-validation-for-woocommerce-public.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/cart-validation-for-woocommerce-public.js',
			array( 'jquery' ),
			$this->version,
			false
		);
	}

	/**
	 * Validate the cart against configured rules.
	 *
	 * Hooks: woocommerce_check_cart_items, woocommerce_before_checkout_process, template_redirect (for Block cart/checkout).
	 * On Block-based cart/checkout pages, woocommerce_check_cart_items does not run on initial load; template_redirect
	 * runs validation early so notices are in session before the StoreNotices block renders.
	 *
	 * @since 1.0.0
	 */
	public function validate_cart() {
		if ( 'yes' !== get_option( 'cvfw_enabled', 'yes' ) ) {
			return;
		}

		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}

		$errors = $this->run_validation_rules();

		if ( empty( $errors ) ) {
			return;
		}

		$stop_at_first = 'yes' === get_option( 'cvfw_stop_at_first_validation', 'no' );

		if ( is_array( $errors ) ) {
			foreach ( $errors as $message ) {
				wc_add_notice( $message, 'error' );
				if ( $stop_at_first ) {
					break;
				}
			}
		}
	}

	/**
	 * Run all validation rules and collect error messages.
	 *
	 * @since 1.0.0
	 * @return array List of error messages (empty if valid).
	 */
	protected function run_validation_rules() {
		$errors = array();

		$rules = $this->get_active_rules();
		foreach ( $rules as $rule ) {
			if ( ! $rule->is_active_by_date() ) {
				continue;
			}
			$conditions = $rule->get_conditions();
			if ( empty( $conditions ) ) {
				continue;
			}
			$results = array();
			foreach ( $conditions as $row ) {
				$cond  = isset( $row['condition'] ) ? $row['condition'] : '';
				$op    = isset( $row['operator'] ) ? $row['operator'] : 'is_equal_to';
				$value = isset( $row['value'] ) && is_array( $row['value'] ) ? $row['value'] : array();
				$results[] = $this->evaluate_condition( $cond, $op, $value );
			}
			$match_mode = $rule->get_rule_match();
			$rule_matches = ( 'any' === $match_mode )
				? in_array( true, $results, true )
				: ! in_array( false, $results, true );
			if ( $rule_matches ) {
				$msg = $rule->get_error_message();
				if ( '' === $msg ) {
					$msg = get_option( 'cvfw_default_validation_error' );
					if ( empty( $msg ) ) {
						$msg = __( 'We are unable to process your order at this time. Please contact the administrator for further information.', 'cart-validation-for-woocommerce' );
					}
				}
				$errors[] = $msg;
			}
		}

		/**
		 * Filter validation errors.
		 *
		 * @since 1.0.0
		 * @param array $errors Current validation errors.
		 */
		$errors = apply_filters( 'cvfw_cart_validation_errors', $errors );

		return array_filter( array_map( 'wp_kses_post', $errors ) );
	}

	/**
	 * Get all published validation rules.
	 *
	 * @since 1.0.0
	 * @return CVFW_Validation_Rule[]
	 */
	protected function get_active_rules() {
		$posts = get_posts( array(
			'post_type'      => CVFW_RULE_POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		) );
		$rules = array();
		foreach ( $posts as $post ) {
			$rules[] = new CVFW_Validation_Rule( $post );
		}
		return $rules;
	}

	/**
	 * Evaluate a single condition.
	 *
	 * @since 1.0.0
	 * @param string $condition Condition type key.
	 * @param string $operator  Operator key (is_equal_to, not_in).
	 * @param array  $value     Selected values.
	 * @return bool True if this condition is satisfied.
	 */
	protected function evaluate_condition( $condition, $operator, $value ) {
		$value = array_map( 'strval', array_filter( $value ) );
		switch ( $condition ) {
			case 'shipping_country':
				$country = $this->get_customer_shipping_country();
				if ( '' === $country ) {
					return false;
				}
				$in_list = in_array( $country, $value, true );
				return ( 'is_equal_to' === $operator ) ? $in_list : ! $in_list;
			case 'cart_contains_product':
				$cart_ids = $this->get_cart_product_ids();
				$has_any = ! empty( array_intersect( $cart_ids, $value ) );
				return ( 'is_equal_to' === $operator ) ? $has_any : ! $has_any;
			case 'cart_contains_category':
				$cart_cats = $this->get_cart_category_ids();
				$has_any = ! empty( array_intersect( $cart_cats, $value ) );
				return ( 'is_equal_to' === $operator ) ? $has_any : ! $has_any;
			case 'user_role':
				$user_role = $this->get_current_user_role();
				$in_list = in_array( $user_role, $value, true );
				return ( 'is_equal_to' === $operator ) ? $in_list : ! $in_list;
			default:
				return false;
		}
	}

	/**
	 * Get customer shipping country (from session/customer).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_customer_shipping_country() {
		if ( ! function_exists( 'WC' ) || ! WC()->customer ) {
			return '';
		}
		$country = WC()->customer->get_shipping_country();
		if ( '' !== $country ) {
			return $country;
		}
		return WC()->customer->get_billing_country();
	}

	/**
	 * Get all product IDs (and variation IDs) in the cart.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_cart_product_ids() {
		$ids = array();
		if ( ! WC()->cart ) {
			return $ids;
		}
		foreach ( WC()->cart->get_cart() as $item ) {
			if ( ! empty( $item['product_id'] ) ) {
				$ids[] = (string) $item['product_id'];
			}
			if ( ! empty( $item['variation_id'] ) ) {
				$ids[] = (string) $item['variation_id'];
			}
		}
		return array_unique( $ids );
	}

	/**
	 * Get all product category IDs present in the cart.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_cart_category_ids() {
		$cat_ids = array();
		if ( ! WC()->cart ) {
			return $cat_ids;
		}
		foreach ( WC()->cart->get_cart() as $item ) {
			$product_id = ! empty( $item['product_id'] ) ? $item['product_id'] : 0;
			if ( ! $product_id ) {
				continue;
			}
			$terms = get_the_terms( $product_id, 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$cat_ids[] = (string) $term->term_id;
				}
			}
		}
		return array_unique( $cat_ids );
	}

	/**
	 * Get current user role (or 'guest' if not logged in).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	protected function get_current_user_role() {
		if ( ! is_user_logged_in() ) {
			return 'guest';
		}
		$user = wp_get_current_user();
		if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
			return $user->roles[0];
		}
		return 'guest';
	}
}
