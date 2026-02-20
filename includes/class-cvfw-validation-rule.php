<?php
/**
 * Validation rule model (single rule post).
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
 * CVFW_Validation_Rule.
 *
 * @since 1.0.0
 */
class CVFW_Validation_Rule {

	/** @var int Rule (post) ID */
	protected $id = 0;

	/** @var \WP_Post|null */
	protected $post;

	/** @var string Post meta: rule match (any|all) */
	protected $meta_rule_match = 'cvfw_rule_match';

	/** @var string Post meta: conditions array */
	protected $meta_conditions = 'cvfw_conditions';

	/** @var string Post meta: start date */
	protected $meta_start_date = 'cvfw_start_date';

	/** @var string Post meta: end date */
	protected $meta_end_date = 'cvfw_end_date';

	/** @var string Post meta: custom error message */
	protected $meta_error_message = 'cvfw_error_message';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @param int|\WP_Post $data Post ID or post object.
	 */
	public function __construct( $data ) {
		if ( is_numeric( $data ) ) {
			$this->post = get_post( (int) $data );
		} elseif ( $data instanceof \WP_Post ) {
			$this->post = $data;
		}
		if ( $this->post instanceof \WP_Post ) {
			$this->id = (int) $this->post->ID;
		}
	}

	/**
	 * Get rule ID.
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get rule title.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_title() {
		return $this->post && $this->post->post_title ? $this->post->post_title : '';
	}

	/**
	 * Get post status (publish = enabled).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_status() {
		return $this->post ? $this->post->post_status : '';
	}

	/**
	 * Get rule match mode: 'any' or 'all'.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_rule_match() {
		if ( $this->id <= 0 ) {
			return 'all';
		}
		$v = get_post_meta( $this->id, $this->meta_rule_match, true );
		return in_array( $v, array( 'any', 'all' ), true ) ? $v : 'all';
	}

	/**
	 * Set rule match mode.
	 *
	 * @since 1.0.0
	 * @param string $value 'any' or 'all'.
	 * @return bool
	 */
	public function set_rule_match( $value ) {
		if ( $this->id <= 0 || ! in_array( $value, array( 'any', 'all' ), true ) ) {
			return false;
		}
		return (bool) update_post_meta( $this->id, $this->meta_rule_match, $value );
	}

	/**
	 * Get conditions array. Each item: condition (string), operator (string), value (array).
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_conditions() {
		if ( $this->id <= 0 ) {
			return array();
		}
		$v = get_post_meta( $this->id, $this->meta_conditions, true );
		return is_array( $v ) ? $v : array();
	}

	/**
	 * Set conditions.
	 *
	 * @since 1.0.0
	 * @param array $conditions Conditions array.
	 * @return bool
	 */
	public function set_conditions( $conditions ) {
		if ( $this->id <= 0 ) {
			return false;
		}
		return (bool) update_post_meta( $this->id, $this->meta_conditions, is_array( $conditions ) ? $conditions : array() );
	}

	/**
	 * Get start date (Y-m-d or empty).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_start_date() {
		if ( $this->id <= 0 ) {
			return '';
		}
		return (string) get_post_meta( $this->id, $this->meta_start_date, true );
	}

	/**
	 * Set start date.
	 *
	 * @since 1.0.0
	 * @param string $value Y-m-d or empty.
	 * @return bool
	 */
	public function set_start_date( $value ) {
		if ( $this->id <= 0 ) {
			return false;
		}
		return (bool) update_post_meta( $this->id, $this->meta_start_date, sanitize_text_field( $value ) );
	}

	/**
	 * Get end date (Y-m-d or empty).
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_end_date() {
		if ( $this->id <= 0 ) {
			return '';
		}
		return (string) get_post_meta( $this->id, $this->meta_end_date, true );
	}

	/**
	 * Set end date.
	 *
	 * @since 1.0.0
	 * @param string $value Y-m-d or empty.
	 * @return bool
	 */
	public function set_end_date( $value ) {
		if ( $this->id <= 0 ) {
			return false;
		}
		return (bool) update_post_meta( $this->id, $this->meta_end_date, sanitize_text_field( $value ) );
	}

	/**
	 * Get custom error message.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function get_error_message() {
		if ( $this->id <= 0 ) {
			return '';
		}
		return (string) get_post_meta( $this->id, $this->meta_error_message, true );
	}

	/**
	 * Set custom error message.
	 *
	 * @since 1.0.0
	 * @param string $value Message text.
	 * @return bool
	 */
	public function set_error_message( $value ) {
		if ( $this->id <= 0 ) {
			return false;
		}
		return (bool) update_post_meta( $this->id, $this->meta_error_message, wp_kses_post( $value ) );
	}

	/**
	 * Check if rule is currently active (within date range).
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	public function is_active_by_date() {
		$today = gmdate( 'Y-m-d' );
		$start = $this->get_start_date();
		$end   = $this->get_end_date();
		if ( $start && $today < $start ) {
			return false;
		}
		if ( $end && $today > $end ) {
			return false;
		}
		return true;
	}
}
