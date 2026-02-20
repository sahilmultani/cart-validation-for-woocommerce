<?php
/**
 * Conditional Validation Rules metabox.
 *
 * @link       https://profiles.wordpress.org/sahilmultani/
 * @since      1.0.0
 *
 * @package    Cart_Validation_For_WooCommerce
 * @subpackage Cart_Validation_For_WooCommerce/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * CVFW_Admin_Rule_Conditions.
 *
 * @since 1.0.0
 */
class CVFW_Admin_Rule_Conditions {

	/** @var string Meta box ID */
	protected $id = 'cvfw-rule-conditions';

	/** @var string[] Screen IDs */
	protected $screens = array( CVFW_RULE_POST_TYPE );

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Get condition types (grouped).
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_condition_types() {
		return array(
			__( 'Location', 'cart-validation-for-woocommerce' ) => array(
				'shipping_country' => __( 'Country', 'cart-validation-for-woocommerce' ),
			),
			__( 'Product', 'cart-validation-for-woocommerce' ) => array(
				'cart_contains_product' => __( 'Cart contains product', 'cart-validation-for-woocommerce' ),
				'cart_contains_category' => __( 'Cart contains category', 'cart-validation-for-woocommerce' ),
			),
			__( 'User', 'cart-validation-for-woocommerce' ) => array(
				'user_role' => __( 'User role', 'cart-validation-for-woocommerce' ),
			),
		);
	}

	/**
	 * Get operators for a condition type.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_operators() {
		return array(
			'is_equal_to' => __( 'Equal to ( = )', 'cart-validation-for-woocommerce' ),
			'not_in'      => __( 'Not equal to ( != )', 'cart-validation-for-woocommerce' ),
		);
	}

	/**
	 * Add meta box.
	 *
	 * @since 1.0.0
	 */
	public function add_meta_box() {
		global $post, $current_screen;
		if ( ! $post || ! $current_screen || ! in_array( $current_screen->id, $this->screens, true ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		add_meta_box(
			$this->id,
			__( 'Conditional Validation Rules', 'cart-validation-for-woocommerce' ),
			array( $this, 'render' ),
			$current_screen->id,
			'normal',
			'default'
		);
	}

	/**
	 * Render metabox.
	 *
	 * @since 1.0.0
	 * @param \WP_Post $post Post object.
	 */
	public function render( $post ) {
		$rule      = new CVFW_Validation_Rule( $post );
		$match     = $rule->get_rule_match();
		$conditions = $rule->get_conditions();

		wp_nonce_field( 'cvfw_rule_conditions', 'cvfw_rule_conditions_nonce' );
		$condition_types = $this->get_condition_types();
		$operators       = $this->get_operators();
		?>
		<div class="cvfw-rule-conditions-panel panel woocommerce_options_panel">
			<div class="cvfw-conditions-header">
				<button type="button" class="button" id="cvfw-add-condition">+ <?php esc_html_e( 'Add Rule', 'cart-validation-for-woocommerce' ); ?></button>
				<select name="cvfw_rule_match" id="cvfw_rule_match">
					<option value="any" <?php selected( $match, 'any' ); ?>><?php esc_html_e( 'Any Rule match', 'cart-validation-for-woocommerce' ); ?></option>
					<option value="all" <?php selected( $match, 'all' ); ?>><?php esc_html_e( 'All Rule match', 'cart-validation-for-woocommerce' ); ?></option>
				</select>
			</div>
			<div class="cvfw-conditions-content">
				<table id="cvfw-conditions-table" class="widefat">
					<tbody>
						<?php
						if ( ! empty( $conditions ) ) {
							foreach ( $conditions as $i => $row ) {
								$cond = isset( $row['condition'] ) ? $row['condition'] : '';
								$op   = isset( $row['operator'] ) ? $row['operator'] : 'is_equal_to';
								$val  = isset( $row['value'] ) && is_array( $row['value'] ) ? $row['value'] : array();
								$this->render_condition_row( $i, $cond, $op, $val, $condition_types, $operators );
							}
						}
						?>
						<tr class="cvfw-no-conditions-row" <?php echo ! empty( $conditions ) ? 'style="display:none;"' : ''; ?>>
							<td colspan="4"><?php esc_html_e( 'Block checkout when the conditions below are met. Add at least one rule.', 'cart-validation-for-woocommerce' ); ?></td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="cvfw_conditions_row_count" id="cvfw_conditions_row_count" value="<?php echo esc_attr( count( $conditions ) ); ?>" />
			</div>
		</div>
		<?php
	}

	/**
	 * Output a single condition row.
	 *
	 * @since 1.0.0
	 * @param int   $i   Row index.
	 * @param string $cond Condition type key.
	 * @param string $op   Operator key.
	 * @param array $val   Selected values.
	 * @param array $condition_types Condition types.
	 * @param array $operators Operators.
	 */
	protected function render_condition_row( $i, $cond, $op, $val, $condition_types, $operators ) {
		?>
		<tr class="cvfw-condition-row" data-row="<?php echo esc_attr( $i ); ?>">
			<td class="condition-type">
				<select name="cvfw_conditions[<?php echo (int) $i; ?>][condition]" class="cvfw-condition-type">
					<?php foreach ( $condition_types as $group => $types ) : ?>
						<optgroup label="<?php echo esc_attr( $group ); ?>">
							<?php foreach ( $types as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $cond, $key ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</optgroup>
					<?php endforeach; ?>
				</select>
			</td>
			<td class="condition-operator">
				<select name="cvfw_conditions[<?php echo (int) $i; ?>][operator]" class="cvfw-condition-operator">
					<?php foreach ( $operators as $k => $v ) : ?>
						<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $op, $k ); ?>><?php echo esc_html( $v ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td class="condition-value" data-condition="<?php echo esc_attr( $cond ); ?>">
				<?php $this->render_value_field( $i, $cond, $val ); ?>
			</td>
			<td class="condition-actions">
				<button type="button" class="button cvfw-remove-condition" title="<?php esc_attr_e( 'Delete', 'cart-validation-for-woocommerce' ); ?>"><span class="dashicons dashicons-trash"></span></button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Render value field for a condition type.
	 *
	 * @since 1.0.0
	 * @param int   $i   Row index.
	 * @param string $cond Condition type.
	 * @param array $selected Selected values.
	 */
	protected function render_value_field( $i, $cond, $selected = array() ) {
		$selected = is_array( $selected ) ? array_map( 'strval', $selected ) : array();
		$name     = 'cvfw_conditions[' . (int) $i . '][value][]';

		switch ( $cond ) {
			case 'shipping_country':
				$countries = WC()->countries->get_countries();
				echo '<select name="' . esc_attr( $name ) . '" class="cvfw-select2 cvfw-value-country" multiple="multiple" data-placeholder="' . esc_attr__( 'Select countries...', 'cart-validation-for-woocommerce' ) . '">';
				foreach ( $countries as $code => $label ) {
					echo '<option value="' . esc_attr( $code ) . '"' . ( in_array( $code, $selected, true ) ? ' selected="selected"' : '' ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'cart_contains_product':
				echo '<select name="' . esc_attr( $name ) . '" class="cvfw-select2 cvfw-value-product cvfw-ajax-search" data-action="cvfw_json_search_products" data-placeholder="' . esc_attr__( 'Search for a product...', 'cart-validation-for-woocommerce' ) . '" multiple="multiple">';
				if ( ! empty( $selected ) ) {
					$ids = array_map( 'absint', $selected );
					$ids = array_filter( array_unique( $ids ) );
					foreach ( $ids as $product_id ) {
						$product = wc_get_product( $product_id );
						if ( $product ) {
							$label = wp_strip_all_tags( $product->get_formatted_name() );
							echo '<option value="' . esc_attr( (string) $product_id ) . '" selected="selected">' . esc_html( $label ) . '</option>';
						}
					}
				}
				echo '</select>';
				break;
			case 'cart_contains_category':
				echo '<select name="' . esc_attr( $name ) . '" class="cvfw-select2 cvfw-value-category cvfw-ajax-search" data-action="cvfw_json_search_categories" data-placeholder="' . esc_attr__( 'Search for a category...', 'cart-validation-for-woocommerce' ) . '" multiple="multiple">';
				if ( ! empty( $selected ) ) {
					$ids = array_map( 'absint', $selected );
					$terms = get_terms( array( 'taxonomy' => 'product_cat', 'include' => $ids, 'hide_empty' => false ) );
					foreach ( $terms as $term ) {
						echo '<option value="' . esc_attr( $term->term_id ) . '" selected="selected">' . esc_html( $term->name ) . '</option>';
					}
				}
				echo '</select>';
				break;
			case 'user_role':
				global $wp_roles;
				$roles = array( 'guest' => __( 'Guest (not logged in)', 'cart-validation-for-woocommerce' ) );
				if ( ! empty( $wp_roles->roles ) ) {
					foreach ( $wp_roles->roles as $slug => $data ) {
						$roles[ $slug ] = $data['name'];
					}
				}
				echo '<select name="' . esc_attr( $name ) . '" class="cvfw-select2 cvfw-value-user-role" multiple="multiple" data-placeholder="' . esc_attr__( 'Select user roles...', 'cart-validation-for-woocommerce' ) . '">';
				foreach ( $roles as $code => $label ) {
					echo '<option value="' . esc_attr( $code ) . '"' . ( in_array( $code, $selected, true ) ? ' selected="selected"' : '' ) . '>' . esc_html( $label ) . '</option>';
				}
				echo '</select>';
				break;
			default:
				echo '<input type="text" name="' . esc_attr( $name ) . '" class="regular-text" placeholder="' . esc_attr__( 'Value', 'cart-validation-for-woocommerce' ) . '" value="' . esc_attr( implode( ',', $selected ) ) . '" />';
				break;
		}
	}

	/**
	 * Save metabox.
	 *
	 * @since 1.0.0
	 * @param int     $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( ! $post || $post->post_type !== CVFW_RULE_POST_TYPE ) {
			return;
		}
		if ( ! isset( $_POST['cvfw_rule_conditions_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cvfw_rule_conditions_nonce'] ) ), 'cvfw_rule_conditions' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$rule = new CVFW_Validation_Rule( $post_id );

		// Nonce verified above.
		$match = isset( $_POST['cvfw_rule_match'] ) ? sanitize_text_field( wp_unslash( $_POST['cvfw_rule_match'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( in_array( $match, array( 'any', 'all' ), true ) ) {
			$rule->set_rule_match( $match );
		}

		// Unslashed here; each condition field sanitized in loop below.
		$raw = isset( $_POST['cvfw_conditions'] ) && is_array( $_POST['cvfw_conditions'] ) ? wp_unslash( $_POST['cvfw_conditions'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$conditions = array();
		foreach ( $raw as $row ) {
			$cond = isset( $row['condition'] ) ? sanitize_text_field( $row['condition'] ) : '';
			$op   = isset( $row['operator'] ) ? sanitize_text_field( $row['operator'] ) : 'is_equal_to';
			$val  = isset( $row['value'] ) ? $row['value'] : array();
			if ( ! is_array( $val ) ) {
				$val = array( $val );
			}
			$val = array_values( array_unique( array_filter( array_map( 'sanitize_text_field', $val ) ) ) );
			if ( $cond ) {
				$conditions[] = array(
					'condition' => $cond,
					'operator'  => in_array( $op, array( 'is_equal_to', 'not_in' ), true ) ? $op : 'is_equal_to',
					'value'     => $val,
				);
			}
		}
		$rule->set_conditions( $conditions );
	}
}
