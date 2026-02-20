<?php
/**
 * The admin-specific functionality of the plugin.
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
 * The admin-specific functionality of the plugin.
 *
 * @since 1.0.0
 * @package    Cart_Validation_For_WooCommerce
 */
class CVFW_Admin {

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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->load_rule_classes();
		new CVFW_Admin_Rule_Config();
		new CVFW_Admin_Rule_Conditions();

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_cart_validation', array( $this, 'settings_tab' ) );
		add_action( 'woocommerce_update_options_cart_validation', array( $this, 'update_settings' ) );
		add_action( 'woocommerce_sections_cart_validation', array( $this, 'output_sections' ) );
		add_action( 'all_admin_notices', array( $this, 'output_woocommerce_settings_tabs_on_rules' ), 5 );
		add_filter( 'parent_file', array( $this, 'set_rule_screen_parent_file' ), 10, 1 );
		add_filter( 'submenu_file', array( $this, 'set_rule_screen_submenu_file' ), 10, 2 );
		add_action( 'wp_ajax_cvfw_json_search_products', array( $this, 'ajax_search_products' ) );
		add_action( 'wp_ajax_cvfw_json_search_categories', array( $this, 'ajax_search_categories' ) );

		// Rules list table: Start Date, End Date, Status columns
		add_filter( 'manage_edit-' . CVFW_RULE_POST_TYPE . '_columns', array( $this, 'rules_list_columns' ) );
		add_action( 'manage_' . CVFW_RULE_POST_TYPE . '_posts_custom_column', array( $this, 'rules_list_column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'rules_list_row_actions' ), 10, 2 );
	}

	/**
	 * Load rule-related admin classes.
	 *
	 * @since 1.0.0
	 */
	private function load_rule_classes() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cvfw-validation-rule.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-cvfw-admin-rule-config.php';
		require_once plugin_dir_path( __FILE__ ) . 'class-cvfw-admin-rule-conditions.php';
	}

	/**
	 * Make WooCommerce > Settings active in the sidebar when on Add/Edit Rule screen (same as Manage Rules list).
	 *
	 * @since 1.0.0
	 * @param string $parent_file Parent menu file.
	 * @return string
	 */
	public function set_rule_screen_parent_file( $parent_file ) {
		global $pagenow, $typenow, $post_type;
		if ( 'post-new.php' === $pagenow && CVFW_RULE_POST_TYPE === $typenow ) {
			return 'woocommerce';
		}
		if ( 'post.php' === $pagenow && CVFW_RULE_POST_TYPE === $post_type ) {
			return 'woocommerce';
		}
		return $parent_file;
	}

	/**
	 * Highlight Settings submenu when on Add/Edit Rule screen.
	 *
	 * @since 1.0.0
	 * @param string $submenu_file Submenu file.
	 * @param string $parent_file  Parent file.
	 * @return string
	 */
	public function set_rule_screen_submenu_file( $submenu_file, $parent_file ) {
		if ( 'woocommerce' !== $parent_file ) {
			return $submenu_file;
		}
		global $pagenow, $typenow, $post_type;
		if ( 'post-new.php' === $pagenow && CVFW_RULE_POST_TYPE === $typenow ) {
			return 'wc-settings';
		}
		if ( 'post.php' === $pagenow && CVFW_RULE_POST_TYPE === $post_type ) {
			return 'wc-settings';
		}
		return $submenu_file;
	}

	/**
	 * Output navigation on rule screens: back button on add/edit, tabs on list.
	 *
	 * @since 1.0.0
	 */
	public function output_woocommerce_settings_tabs_on_rules() {
		global $typenow, $pagenow;
		if ( CVFW_RULE_POST_TYPE !== $typenow ) {
			return;
		}
		$rules_list_url = admin_url( 'admin.php?page=wc-settings&tab=cart_validation&section=rules' );

		// Add/Edit rule: only show back to list button (no WooCommerce settings tabs).
		if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
			?>
			<div class="wrap cvfw-rule-screen-header">
				<p class="cvfw-back-to-list">
					<a href="<?php echo esc_url( $rules_list_url ); ?>" class="button"><?php esc_html_e( '&larr; Back to list page', 'cart-validation-for-woocommerce' ); ?></a>
				</p>
			</div>
			<?php
			return;
		}

		// Rules list (edit.php): show WooCommerce tabs for consistency with settings.
		if ( ! class_exists( 'WC_Admin_Settings', false ) && defined( 'WC_ABSPATH' ) ) {
			require_once WC_ABSPATH . 'includes/admin/class-wc-admin-settings.php';
		}
		if ( class_exists( 'WC_Admin_Settings', false ) && method_exists( 'WC_Admin_Settings', 'get_settings_pages' ) ) {
			WC_Admin_Settings::get_settings_pages();
		}
		$tabs = apply_filters( 'woocommerce_settings_tabs_array', array() );
		?>
		<div class="wrap woocommerce">
			<nav class="nav-tab-wrapper woo-nav-tab-wrapper">
				<?php foreach ( $tabs as $name => $label ) : ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . $name ) ); ?>" class="nav-tab <?php echo ( 'cart_validation' === $name ) ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<ul class="subsubsub">
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=cart_validation' ) ); ?>"><?php esc_html_e( 'General', 'cart-validation-for-woocommerce' ); ?></a> |</li>
				<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=cart_validation&section=messages' ) ); ?>"><?php esc_html_e( 'Error Messages', 'cart-validation-for-woocommerce' ); ?></a> |</li>
				<li><a href="<?php echo esc_url( $rules_list_url ); ?>" class="current"><?php esc_html_e( 'Manage Rules', 'cart-validation-for-woocommerce' ); ?></a></li>
			</ul>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		// Tab used only for enqueue context (no state change). Nonce not required for GET.
		$current_tab   = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_settings   = ( 'woocommerce_page_wc-settings' === $screen->id && 'cart_validation' === $current_tab );
		$is_rule_screen = ( 'cvfw_validation_rule' === $screen->post_type );
		if ( ! $is_settings && ! $is_rule_screen ) {
			return;
		}
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/cart-validation-for-woocommerce-admin.css',
			array(),
			$this->version,
			'all'
		);
		if ( $is_rule_screen ) {
			wp_enqueue_style(
				'cvfw-jquery-ui-datepicker',
				plugin_dir_url( __FILE__ ) . 'css/jquery-ui-datepicker.css',
				array(),
				$this->version
			);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}
		// Tab used only for enqueue context (no state change). Nonce not required for GET.
		$current_tab   = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$is_settings   = ( 'woocommerce_page_wc-settings' === $screen->id && 'cart_validation' === $current_tab );
		$is_rule_screen = ( 'cvfw_validation_rule' === $screen->post_type );
		if ( $is_settings ) {
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/cart-validation-for-woocommerce-admin.js',
				array( 'jquery' ),
				$this->version,
				false
			);
		}
		if ( $is_rule_screen ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'selectWoo' );
			wp_enqueue_style( 'select2' );
			wp_enqueue_script(
				'cvfw-conditional-rules',
				plugin_dir_url( __FILE__ ) . 'js/cvfw-conditional-rules.js',
				array( 'jquery', 'selectWoo', 'jquery-ui-datepicker' ),
				$this->version,
				false
			);
			$countries = function_exists( 'WC' ) && WC()->countries ? WC()->countries->get_countries() : array();
			$roles     = array( 'guest' => __( 'Guest (not logged in)', 'cart-validation-for-woocommerce' ) );
			if ( function_exists( 'wp_roles' ) ) {
				$wp_roles = wp_roles();
				if ( isset( $wp_roles->roles ) ) {
					foreach ( $wp_roles->roles as $slug => $data ) {
						$roles[ $slug ] = $data['name'];
					}
				}
			}
			$conditions_class = new CVFW_Admin_Rule_Conditions();
			wp_localize_script( 'cvfw-conditional-rules', 'cvfw_rules_vars', array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'search_nonce'    => wp_create_nonce( 'cvfw-search' ),
				'firstDay'        => absint( get_option( 'start_of_week', 0 ) ),
				'condition_types' => $conditions_class->get_condition_types(),
				'operators'       => $conditions_class->get_operators(),
				'countries'       => $countries,
				'roles'           => $roles,
				'i18n'            => array(
					'search_products'   => __( 'Search for a product...', 'cart-validation-for-woocommerce' ),
					'search_categories' => __( 'Search for a category...', 'cart-validation-for-woocommerce' ),
					'select_countries'  => __( 'Select countries...', 'cart-validation-for-woocommerce' ),
					'select_roles'      => __( 'Select user roles...', 'cart-validation-for-woocommerce' ),
					'delete'            => __( 'Delete', 'cart-validation-for-woocommerce' ),
				),
			) );
		}
	}

	/**
	 * Add a new settings tab to the WooCommerce settings tabs array.
	 *
	 * @since 1.0.0
	 * @param array $settings_tabs Array of WooCommerce setting tabs.
	 * @return array
	 */
	public function add_settings_tab( $settings_tabs ) {
		$settings_tabs['cart_validation'] = __( 'Cart Validation', 'cart-validation-for-woocommerce' );
		return $settings_tabs;
	}

	/**
	 * Get all the settings sections for Cart Validation tab.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''         => __( 'General', 'cart-validation-for-woocommerce' ),
			'messages' => __( 'Error Messages', 'cart-validation-for-woocommerce' ),
			'rules'    => __( 'Manage Rules', 'cart-validation-for-woocommerce' ),
		);
		return apply_filters( 'cvfw_settings_sections', $sections );
	}

	/**
	 * Output the settings sections for Cart Validation tab.
	 *
	 * @since 1.0.0
	 */
	public function output_sections() {
		global $current_section;

		$sections = $this->get_sections();
		if ( empty( $sections ) || 1 === count( $sections ) ) {
			return;
		}

		echo '<ul class="subsubsub">';
		$array_keys = array_keys( $sections );
		foreach ( $sections as $id => $label ) {
			$url = admin_url( 'admin.php?page=wc-settings&tab=cart_validation&section=' . sanitize_title( $id ) );
			$cls = $current_section === $id ? 'current' : '';
			echo '<li><a href="' . esc_url( $url ) . '" class="' . esc_attr( $cls ) . '">' . esc_html( $label ) . '</a> ' . ( end( $array_keys ) === $id ? '' : '|' ) . ' </li>';
		}
		echo '</ul><br class="clear" />';
	}

	/**
	 * Output the settings for the current section.
	 *
	 * @since 1.0.0
	 */
	public function settings_tab() {
		global $current_section, $post_type, $post_type_object;
		if ( 'rules' === $current_section ) {
			// Hide WooCommerce "Save changes" button on the rules listing (no settings to save here).
			$GLOBALS['hide_save_button'] = true;
			$this->maybe_process_rules_bulk_action();
			$this->output_rules_list_table();
			return;
		}
		woocommerce_admin_fields( $this->get_settings( $current_section ) );
	}

	/**
	 * Process bulk actions for the rules list when form is submitted to the settings page.
	 *
	 * @since 1.0.0
	 */
	protected function maybe_process_rules_bulk_action() {
		$action = $this->get_current_bulk_action();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified below before processing.
		if ( ! $action || empty( $_REQUEST['post'] ) || ! is_array( $_REQUEST['post'] ) ) {
			return;
		}
		check_admin_referer( 'bulk-posts' );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified by check_admin_referer above.
		$post_ids = array_map( 'intval', $_REQUEST['post'] );
		$post_ids = array_filter( $post_ids );
		if ( empty( $post_ids ) ) {
			return;
		}
		$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'locked', 'ids' ), wp_get_referer() );
		if ( ! $sendback ) {
			$sendback = admin_url( 'admin.php?page=wc-settings&tab=cart_validation&section=rules' );
		}
		$sendback = add_query_arg( 'paged', $this->get_list_table_pagenum(), $sendback );

		switch ( $action ) {
			case 'trash':
				$trashed = 0;
				foreach ( $post_ids as $id ) {
					if ( current_user_can( 'delete_post', $id ) && wp_trash_post( $id ) ) {
						++$trashed;
					}
				}
				$sendback = add_query_arg( 'trashed', $trashed, $sendback );
				break;
			case 'untrash':
				$untrashed = 0;
				foreach ( $post_ids as $id ) {
					if ( current_user_can( 'delete_post', $id ) && wp_untrash_post( $id ) ) {
						++$untrashed;
					}
				}
				$sendback = add_query_arg( 'untrashed', $untrashed, $sendback );
				break;
			case 'delete':
				$deleted = 0;
				foreach ( $post_ids as $id ) {
					if ( current_user_can( 'delete_post', $id ) && wp_delete_post( $id, true ) ) {
						++$deleted;
					}
				}
				$sendback = add_query_arg( 'deleted', $deleted, $sendback );
				break;
			default:
				return;
		}
		wp_safe_redirect( $sendback );
		exit;
	}

	/**
	 * Get current bulk action from request (for rules list).
	 *
	 * @since 1.0.0
	 * @return string|false
	 */
	protected function get_current_bulk_action() {
		// Caller verifies nonce before processing. Reading action for routing only.
		if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return false;
	}

	/**
	 * Get current pagenum for list table (mirrors WP_Posts_List_Table::get_pagenum()).
	 *
	 * @since 1.0.0
	 * @return int
	 */
	protected function get_list_table_pagenum() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Display/routing only; used after bulk nonce check when processing.
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;
		return max( 1, $pagenum );
	}

	/**
	 * Add Start Date, End Date, and Status columns to the rules list table.
	 *
	 * @since 1.0.0
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function rules_list_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( 'title' === $key ) {
				$new_columns['cvfw_start_date'] = __( 'Start Date', 'cart-validation-for-woocommerce' );
				$new_columns['cvfw_end_date']   = __( 'End Date', 'cart-validation-for-woocommerce' );
				$new_columns['cvfw_status']    = __( 'Status', 'cart-validation-for-woocommerce' );
			}
		}
		return $new_columns;
	}

	/**
	 * Output content for Start Date, End Date, and Status columns in the rules list table.
	 *
	 * @since 1.0.0
	 * @param string $column   Column key.
	 * @param int    $post_id  Post ID.
	 */
	public function rules_list_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'cvfw_start_date':
				$rule = new CVFW_Validation_Rule( $post_id );
				$start = $rule->get_start_date();
				if ( $start && strtotime( $start ) ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $start ) ) );
				} else {
					echo '&ndash;';
				}
				break;
			case 'cvfw_end_date':
				$rule = new CVFW_Validation_Rule( $post_id );
				$end  = $rule->get_end_date();
				if ( $end && strtotime( $end ) ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $end ) ) );
				} else {
					echo '&ndash;';
				}
				break;
			case 'cvfw_status':
				if ( 'publish' === get_post_status( $post_id ) ) {
					printf( '<mark class="order-status status-processing"><span>%s</span></mark>', esc_html__( 'Enabled', 'cart-validation-for-woocommerce' ) );
				} else {
					printf( '<mark class="order-status status-failed"><span>%s</span></mark>', esc_html__( 'Disabled', 'cart-validation-for-woocommerce' ) );
				}
				break;
		}
	}

	/**
	 * Rules list row actions: only Edit and Delete (remove Quick Edit and Trash).
	 *
	 * @since 1.0.0
	 * @param array   $actions Row actions.
	 * @param WP_Post $post    Post object.
	 * @return array
	 */
	public function rules_list_row_actions( $actions, $post ) {
		if ( ! $post || $post->post_type !== CVFW_RULE_POST_TYPE ) {
			return $actions;
		}
		$new_actions = array();
		if ( isset( $actions['edit'] ) ) {
			$new_actions['edit'] = $actions['edit'];
		}
		if ( current_user_can( 'delete_post', $post->ID ) ) {
			$delete_url = get_delete_post_link( $post->ID, '', true );
			$new_actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" onclick="return confirm(%s);">%s</a>',
				esc_url( $delete_url ),
				esc_attr( "'" . esc_js( __( 'Are you sure you want to permanently delete this rule?', 'cart-validation-for-woocommerce' ) ) . "'" ),
				esc_html__( 'Delete', 'cart-validation-for-woocommerce' )
			);
		}
		return $new_actions;
	}

	/**
	 * Output bulk action success messages for the rules list (trashed, untrashed, deleted).
	 *
	 * @since 1.0.0
	 * @param \WP_Post_Type $post_type_object Post type object.
	 */
	protected function output_rules_list_bulk_messages( $post_type_object ) {
		// Redirect query args from our own bulk action handler; display only. absint() used for sanitization.
		$bulk_counts = array(
			'updated'   => isset( $_REQUEST['updated'] ) ? absint( $_REQUEST['updated'] ) : 0,   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'locked'    => isset( $_REQUEST['locked'] ) ? absint( $_REQUEST['locked'] ) : 0,    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'deleted'   => isset( $_REQUEST['deleted'] ) ? absint( $_REQUEST['deleted'] ) : 0,   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'trashed'   => isset( $_REQUEST['trashed'] ) ? absint( $_REQUEST['trashed'] ) : 0,   // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			'untrashed' => isset( $_REQUEST['untrashed'] ) ? absint( $_REQUEST['untrashed'] ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		);
		$bulk_messages = array(
			'updated'   => _n( '%s rule updated.', '%s rules updated.', $bulk_counts['updated'], 'cart-validation-for-woocommerce' ),
			'locked'    => _n( '%s rule not updated, somebody is editing it.', '%s rules not updated.', $bulk_counts['locked'], 'cart-validation-for-woocommerce' ),
			'deleted'   => _n( '%s rule permanently deleted.', '%s rules permanently deleted.', $bulk_counts['deleted'], 'cart-validation-for-woocommerce' ),
			'trashed'   => _n( '%s rule moved to the Trash.', '%s rules moved to the Trash.', $bulk_counts['trashed'], 'cart-validation-for-woocommerce' ),
			'untrashed' => _n( '%s rule restored from the Trash.', '%s rules restored.', $bulk_counts['untrashed'], 'cart-validation-for-woocommerce' ),
		);
		$messages = array();
		foreach ( $bulk_counts as $key => $count ) {
			if ( $count > 0 && isset( $bulk_messages[ $key ] ) ) {
				$messages[] = sprintf( $bulk_messages[ $key ], number_format_i18n( $count ) );
			}
		}
		if ( empty( $messages ) ) {
			return;
		}
		wp_admin_notice(
			implode( ' ', $messages ),
			array(
				'id'                 => 'message',
				'additional_classes' => array( 'updated' ),
				'dismissible'        => true,
			)
		);
	}

	/**
	 * Output the Validation Rules list table inline (so it appears below Cart Validation sub-tabs).
	 *
	 * @since 1.0.0
	 */
	protected function output_rules_list_table() {
		$rule_post_type        = CVFW_RULE_POST_TYPE;
		$rule_post_type_object = get_post_type_object( $rule_post_type );
		if ( ! $rule_post_type_object || ! current_user_can( $rule_post_type_object->cap->edit_posts ) ) {
			return;
		}

		// wp_edit_posts_query() uses $_GET; without post_type it defaults to 'post'. Force our CPT.
		$get_backup = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- List table display context.
		if ( empty( $_GET['post_type'] ) || $_GET['post_type'] !== CVFW_RULE_POST_TYPE ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$_GET['post_type'] = CVFW_RULE_POST_TYPE;
		}
		// Ensure list table query params from form (search, filter, paged) are in $_GET.
		foreach ( array( 'post_status', 'paged', 'orderby', 'order', 's', 'm', 'cat' ) as $key ) {
			if ( isset( $_REQUEST[ $key ] ) && ! isset( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$_GET[ $key ] = in_array( $key, array( 'paged', 'cat' ), true )
					? absint( wp_unslash( $_REQUEST[ $key ] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					: sanitize_text_field( wp_unslash( $_REQUEST[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}
		$list_table = _get_list_table( 'WP_Posts_List_Table', array( 'screen' => 'edit-' . CVFW_RULE_POST_TYPE ) );
		$list_table->prepare_items();
		$_GET = $get_backup;

		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=cart_validation&section=rules' );
		$post_new_url = admin_url( 'post-new.php?post_type=' . CVFW_RULE_POST_TYPE );
		?>
		<div class="wrap cvfw-rules-list-wrap">
			<h1 class="wp-heading-inline"><?php echo esc_html( $rule_post_type_object->labels->name ); ?></h1>
			<?php if ( current_user_can( $rule_post_type_object->cap->create_posts ) ) : ?>
				<a href="<?php echo esc_url( $post_new_url ); ?>" class="page-title-action"><?php echo esc_html( $rule_post_type_object->labels->add_new_item ); ?></a>
			<?php endif; ?>
			<?php
			$search_query = isset( $_REQUEST['s'] ) && is_string( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( '' !== $search_query ) :
				?>
				<span class="subtitle">
				<?php
				printf(
					/* translators: %s: Search query. */
					esc_html__( 'Search results for: %s', 'cart-validation-for-woocommerce' ),
					'<strong>' . esc_html( $search_query ) . '</strong>'
				);
				?>
				</span>
			<?php endif; ?>
			<hr class="wp-header-end" />
			<?php $this->output_rules_list_bulk_messages( $rule_post_type_object ); ?>
			<form id="posts-filter" method="get" action="<?php echo esc_url( $settings_url ); ?>">
				<input type="hidden" name="page" value="wc-settings" />
				<input type="hidden" name="tab" value="cart_validation" />
				<input type="hidden" name="section" value="rules" />
				<?php wp_nonce_field( 'bulk-posts' ); ?>
				<?php $list_table->search_box( $rule_post_type_object->labels->search_items, 'post' ); ?>
				<?php $post_status_value = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<input type="hidden" name="post_status" class="post_status_page" value="<?php echo esc_attr( $post_status_value ? $post_status_value : 'all' ); ?>" />
				<?php
				// Do not add post_type to the form: submitting with post_type in the URL causes
				// WordPress to set $typenow and look for the wrong menu parent, triggering "Cannot load wc-settings."
				// The list table still shows our CPT because we set $_GET['post_type'] above before prepare_items().
				?>
				<?php $list_table->display(); ?>
			</form>
			<?php
			if ( $list_table->has_items() ) {
				$list_table->inline_edit();
			}
			?>
			<div id="ajax-response"></div>
		</div>
		<?php
	}

	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 */
	public function update_settings() {
		global $current_section;
		if ( 'rules' === $current_section ) {
			return;
		}
		woocommerce_update_options( $this->get_settings( $current_section ) );
	}

	/**
	 * Get settings for the Cart Validation tab.
	 *
	 * @since 1.0.0
	 * @param string $current_section Current section ID.
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		if ( 'messages' === $current_section ) {
			return $this->get_messages_settings();
		}
		if ( 'rules' === $current_section ) {
			return array();
		}
		return $this->get_general_settings();
	}

	/**
	 * Get General settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_general_settings() {
		$settings = array(
			array(
				'title' => __( 'General Settings', 'cart-validation-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => __( 'Configure cart validation behavior. You can define product, category, and user-based restrictions in upcoming sections.', 'cart-validation-for-woocommerce' ),
				'id'    => 'cvfw_general_options',
			),
			array(
				'title'   => __( 'Enable Cart Validation', 'cart-validation-for-woocommerce' ),
				'desc'    => __( 'Enable cart validation rules', 'cart-validation-for-woocommerce' ),
				'id'      => 'cvfw_enabled',
				'type'    => 'checkbox',
				'default' => 'yes',
			),
			array(
				'title'   => __( 'Stop at first validation error', 'cart-validation-for-woocommerce' ),
				'desc'    => __( 'When enabled, only the first validation error is shown. When disabled, all validation errors are collected and displayed.', 'cart-validation-for-woocommerce' ),
				'id'      => 'cvfw_stop_at_first_validation',
				'type'    => 'checkbox',
				'default' => 'no',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'cvfw_general_options',
			),
		);
		return apply_filters( 'cvfw_general_settings', $settings );
	}

	/**
	 * Get Error Messages settings.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	protected function get_messages_settings() {
		$settings = array(
			array(
				'title' => __( 'Default Error Messages', 'cart-validation-for-woocommerce' ),
				'type'  => 'title',
				'desc'  => __( 'Customize the default message shown when cart validation fails. You can override this per rule when you add validation rules.', 'cart-validation-for-woocommerce' ),
				'id'    => 'cvfw_messages_options',
			),
			array(
				'title'       => __( 'Default validation error message', 'cart-validation-for-woocommerce' ),
				'desc'        => __( 'Shown when the cart fails validation and no rule-specific message is set.', 'cart-validation-for-woocommerce' ),
				'id'          => 'cvfw_default_validation_error',
				'type'        => 'textarea',
				'default'     => __( 'Your cart contains items that cannot be purchased together. Please review the cart and remove incompatible items.', 'cart-validation-for-woocommerce' ),
				'css'         => 'width: 100%; min-height: 60px;',
				'desc_tip'    => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'cvfw_messages_options',
			),
		);
		return apply_filters( 'cvfw_messages_settings', $settings );
	}

	/**
	 * AJAX search products (for conditional rule value).
	 *
	 * @since 1.0.0
	 */
	public function ajax_search_products() {
		check_ajax_referer( 'cvfw-search', 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-validation-for-woocommerce' ) ) );
		}
		// Nonce verified above. GET params from Select2 AJAX request.
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $term ) ) {
			$term = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( strlen( $term ) < 2 ) {
			wp_send_json( array() );
		}
		$limit = absint( apply_filters( 'woocommerce_json_search_limit', 30 ) );
		$args  = array(
			'post_type'   => array( 'product', 'product_variation' ),
			'post_status' => 'publish',
			's'           => $term,
			'posts_per_page' => $limit,
			'orderby'     => 'title',
			'order'       => 'ASC',
			'fields'      => 'ids',
		);
		$query = new WP_Query( $args );
		$results = array();
		if ( $query->posts ) {
			foreach ( $query->posts as $id ) {
				$product = wc_get_product( $id );
				if ( $product && $product->is_visible() ) {
					$results[ (string) $id ] = wp_strip_all_tags( $product->get_formatted_name() );
				}
			}
		}
		wp_send_json( $results );
	}

	/**
	 * AJAX search product categories (for conditional rule value).
	 *
	 * @since 1.0.0
	 */
	public function ajax_search_categories() {
		check_ajax_referer( 'cvfw-search', 'security' );
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-validation-for-woocommerce' ) ) );
		}
		// Nonce verified above. GET params from Select2 AJAX request.
		$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $term ) ) {
			$term = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( strlen( $term ) < 2 ) {
			wp_send_json( array() );
		}
		$terms = get_terms( array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'search'    => $term,
			'number'    => 30,
		) );
		$results = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $t ) {
				$results[ (string) $t->term_id ] = $t->name;
			}
		}
		wp_send_json( $results );
	}
}
