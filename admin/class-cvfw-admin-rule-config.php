<?php
/**
 * Rule Configuration metabox (status, dates, error message).
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
 * CVFW_Admin_Rule_Config.
 *
 * @since 1.0.0
 */
class CVFW_Admin_Rule_Config {

	/** @var string Meta box ID */
	protected $id = 'cvfw-rule-config';

	/** @var string[] Screen IDs */
	protected $screens = array( CVFW_RULE_POST_TYPE );

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'filter_insert_post_data' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
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
			__( 'Rule Configuration', 'cart-validation-for-woocommerce' ),
			array( $this, 'render' ),
			$current_screen->id,
			'normal',
			'high'
		);
	}

	/**
	 * Render metabox.
	 *
	 * @since 1.0.0
	 * @param \WP_Post $post Post object.
	 */
	public function render( $post ) {
		$rule = new CVFW_Validation_Rule( $post );
		wp_nonce_field( 'cvfw_rule_config', 'cvfw_rule_config_nonce' );

		$start_date = $rule->get_start_date();
		$end_date   = $rule->get_end_date();
		$error_msg  = $rule->get_error_message();
		?>
		<div class="cvfw-rule-config-panel panel woocommerce_options_panel">
			<div class="options_group">
				<p class="form-field cvfw-status-row">
					<label for="cvfw_rule_status"><?php esc_html_e( 'Status', 'cart-validation-for-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Published = rule is active. Draft = rule is disabled.', 'cart-validation-for-woocommerce' ) ) ); ?></label>
					<span class="cvfw-status-toggle">
						<?php
						$status = $post->post_status;
						$is_on  = ( 'publish' === $status );
						?>
						<label class="cvfw-toggle">
							<input type="checkbox" name="cvfw_rule_enabled" value="1" <?php checked( $is_on ); ?> />
							<span class="cvfw-toggle-slider"></span>
						</label>
					</span>
				</p>
				<p class="form-field">
					<label for="cvfw_start_date"><?php esc_html_e( 'Start Date', 'cart-validation-for-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Optional. Rule is active from this date. Leave empty for no start limit.', 'cart-validation-for-woocommerce' ) ) ); ?></label>
					<input type="text" class="cvfw-datepicker" name="cvfw_start_date" id="cvfw_start_date" value="<?php echo esc_attr( $start_date ); ?>" placeholder="YYYY-MM-DD" />
				</p>
				<p class="form-field">
					<label for="cvfw_end_date"><?php esc_html_e( 'End Date', 'cart-validation-for-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Optional. Rule is active until this date. Leave empty for no end limit.', 'cart-validation-for-woocommerce' ) ) ); ?></label>
					<input type="text" class="cvfw-datepicker" name="cvfw_end_date" id="cvfw_end_date" value="<?php echo esc_attr( $end_date ); ?>" placeholder="YYYY-MM-DD" />
				</p>
				<p class="form-field form-field-wide">
					<label for="cvfw_error_message"><?php esc_html_e( 'Error message', 'cart-validation-for-woocommerce' ); ?><?php echo wp_kses_post( wc_help_tip( __( 'Message shown when this rule blocks checkout. Leave empty to use the default message from settings.', 'cart-validation-for-woocommerce' ) ) ); ?></label>
					<textarea name="cvfw_error_message" id="cvfw_error_message" rows="3" class="large-text" placeholder="<?php esc_attr_e( 'Your cart contains items that cannot be purchased together.', 'cart-validation-for-woocommerce' ); ?>"><?php echo esc_textarea( $error_msg ); ?></textarea>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Set rule post status from metabox checkbox before post is saved (avoids recursive wp_update_post in save_post).
	 *
	 * @since 1.0.0
	 * @param array $data    Sanitized post data.
	 * @param array $raw     Raw post data from $_POST.
	 * @return array
	 */
	public function filter_insert_post_data( $data, $raw ) {
		if ( ! isset( $data['post_type'] ) || $data['post_type'] !== CVFW_RULE_POST_TYPE ) {
			return $data;
		}
		// Use $_POST for metabox fields; $raw may not include them when saving from edit screen.
		if ( ! isset( $_POST['cvfw_rule_config_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cvfw_rule_config_nonce'] ) ), 'cvfw_rule_config' ) ) {
			return $data;
		}
		// Nonce verified above.
		$data['post_status'] = ( isset( $_POST['cvfw_rule_enabled'] ) && '1' === $_POST['cvfw_rule_enabled'] ) ? 'publish' : 'draft'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $data;
	}

	/**
	 * Save metabox.
	 *
	 * @since 1.0.0
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save_post( $post_id, $post ) {
		if ( ! $post || $post->post_type !== CVFW_RULE_POST_TYPE ) {
			return;
		}
		if ( ! isset( $_POST['cvfw_rule_config_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cvfw_rule_config_nonce'] ) ), 'cvfw_rule_config' ) ) {
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
		$start = isset( $_POST['cvfw_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['cvfw_start_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$end   = isset( $_POST['cvfw_end_date'] ) ? sanitize_text_field( wp_unslash( $_POST['cvfw_end_date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$msg   = isset( $_POST['cvfw_error_message'] ) ? wp_kses_post( wp_unslash( $_POST['cvfw_error_message'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$rule->set_start_date( $start );
		$rule->set_end_date( $end );
		$rule->set_error_message( $msg );
	}
}
