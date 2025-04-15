<?php
/**
 * Plugin Admin settings page class.
 *
 * @package   OpenID_Connect_Generic
 * @category  Settings
 * @author    Jonathan Daggerhart <jonathan@daggerhart.com>
 * @copyright 2015-2023 daggerhart
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */

/**
 * OpenID_Connect_Generic_Settings_Page class.
 *
 * Admin settings page.
 *
 * @package OpenID_Connect_Generic
 * @category  Settings
 */
class OpenID_Connect_Generic_Settings_Page {

	/**
	 * Local copy of the settings provided by the base plugin.
	 *
	 * @var OpenID_Connect_Generic_Option_Settings
	 */
	private $settings;

	/**
	 * Instance of the plugin logger.
	 *
	 * @var OpenID_Connect_Generic_Option_Logger
	 */
	private $logger;

	/**
	 * The controlled list of settings & associated defined during
	 * construction for i18n reasons.
	 *
	 * @var array
	 */
	private $settings_fields = array();

	/**
	 * Options page slug.
	 *
	 * @var string
	 */
	private $options_page_name = 'openid-connect-generic-settings';

	/**
	 * Options page settings group name.
	 *
	 * @var string
	 */
	private $settings_field_group;

	/**
	 * Settings page class constructor.
	 *
	 * @param OpenID_Connect_Generic_Option_Settings $settings The plugin settings object.
	 * @param OpenID_Connect_Generic_Option_Logger   $logger   The plugin logging class object.
	 */
	public function __construct( OpenID_Connect_Generic_Option_Settings $settings, OpenID_Connect_Generic_Option_Logger $logger ) {

		$this->settings             = $settings;
		$this->logger               = $logger;
		$this->settings_field_group = $this->settings->get_option_name() . '-group';

		$fields = $this->get_settings_fields();

		// Some simple pre-processing.
		foreach ( $fields as $key => &$field ) {
			$field['key']  = $key;
			$field['name'] = $this->settings->get_option_name() . '[' . $key . ']';
		}

		// Allow alterations of the fields.
		$this->settings_fields = $fields;
	}

	/**
	 * Hook the settings page into WordPress.
	 *
	 * @param OpenID_Connect_Generic_Option_Settings $settings A plugin settings object instance.
	 * @param OpenID_Connect_Generic_Option_Logger   $logger   A plugin logger object instance.
	 *
	 * @return void
	 */
	public static function register( OpenID_Connect_Generic_Option_Settings $settings, OpenID_Connect_Generic_Option_Logger $logger ) {
		$settings_page = new self( $settings, $logger );

		// Add our options page the the admin menu.
		add_action( 'admin_menu', array( $settings_page, 'admin_menu' ) );

		// Register our settings.
		add_action( 'admin_init', array( $settings_page, 'admin_init' ) );
	}

	/**
	 * Implements hook admin_menu to add our options/settings page to the
	 *  dashboard menu.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Hitobito Auth', 'daggerhart-openid-connect-generic' ),
			__( 'Hitobito Auth', 'daggerhart-openid-connect-generic' ),
			'manage_options',
			$this->options_page_name,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Implements hook admin_init to register our settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting(
			$this->settings_field_group,
			$this->settings->get_option_name(),
			array(
				$this,
				'sanitize_settings',
			)
		);

		add_settings_section(
			'client_settings',
			__( 'Client Settings', 'daggerhart-openid-connect-generic' ),
			array( $this, 'client_settings_description' ),
			$this->options_page_name
		);

		add_settings_section(
			'user_settings',
			__( 'WordPress User Settings', 'daggerhart-openid-connect-generic' ),
			array( $this, 'user_settings_description' ),
			$this->options_page_name
		);

		add_settings_section(
			'log_settings',
			__( 'Log Settings', 'daggerhart-openid-connect-generic' ),
			array( $this, 'log_settings_description' ),
			$this->options_page_name
		);

		// Preprocess fields and add them to the page.
		foreach ( $this->settings_fields as $key => $field ) {
			// Make sure each key exists in the settings array.
			if ( ! isset( $this->settings->{ $key } ) ) {
				$this->settings->{ $key } = null;
			}

			// Determine appropriate output callback.
			switch ( $field['type'] ) {
				case 'checkbox':
					$callback = 'do_checkbox';
					break;

				case 'select':
					$callback = 'do_select';
					break;

				case 'text':
				default:
					$callback = 'do_text_field';
					break;
			}

			// Add the field.
			add_settings_field(
				$key,
				$field['title'],
				array( $this, $callback ),
				$this->options_page_name,
				$field['section'],
				$field
			);
		}
	}

	/**
	 * Get the plugin settings fields definition.
	 *
	 * @return array
	 */
	private function get_settings_fields() {

		/**
		 * Simple settings fields have:
		 *
		 * - title
		 * - description
		 * - type ( checkbox | text | select )
		 * - section - settings/option page section ( client_settings | authorization_settings )
		 * - example (optional example will appear beneath description and be wrapped in <code>)
		 */
		$fields = array(
			'client_id'         => array(
				'title'       => __( 'Client ID', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'The ID this client will be recognized as when connecting the to Identity provider server.', 'daggerhart-openid-connect-generic' ),
				'example'     => 'my-wordpress-client-id',
				'type'        => 'text',
				'disabled'    => defined( 'OIDC_CLIENT_ID' ),
				'section'     => 'client_settings',
			),
			'client_secret'     => array(
				'title'       => __( 'Client Secret Key', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'Arbitrary secret key the server expects from this client. Can be anything, but should be very unique.', 'daggerhart-openid-connect-generic' ),
				'type'        => 'text',
				'disabled'    => defined( 'OIDC_CLIENT_SECRET' ),
				'section'     => 'client_settings',
			),
			'endpoint_url' => array(
				'title'       => __( 'Hitobito URL', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'For testing please use: XXX.puzzle.ch/ and for production please use e.g. db.scout.ch', 'daggerhart-openid-connect-generic' ),
				'type'        => 'select',
				'options'     => array(
					'test' => __( 'TEST MiData [pbs.puzzle.ch]', 'daggerhart-openid-connect-generic' ),
					'prod' => __( 'PRODUCTION MiData [db.scout.ch]', 'daggerhart-openid-connect-generic' ),
					'jubla' => __( 'PRODUCTION jubla.db [db.jubla.ch]', 'daggerhart-openid-connect-generic' ),
					'jublatest' => __( 'TEST jubla.db [jubla.puzzle.ch]', 'daggerhart-openid-connect-generic' ),
				),
				'disabled'    => defined( 'OIDC_ENDPOINT_URL' ),
				'section'     => 'client_settings',
			),
			'create_if_does_not_exist'   => array(
				'title'       => __( 'Create user if does not exist', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'If the user identity is not linked to an existing WordPress user, it is created. If this setting is not enabled, and if the user authenticates with an account which is not linked to an existing WordPress user, then the authentication will fail.', 'daggerhart-openid-connect-generic' ),
				'type'        => 'checkbox',
				'disabled'    => defined( 'OIDC_CREATE_IF_DOES_NOT_EXIST' ),
				'section'     => 'user_settings',
			),
			'enable_logging'    => array(
				'title'       => __( 'Enable Logging', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'Very simple log messages for debugging purposes.', 'daggerhart-openid-connect-generic' ),
				'type'        => 'checkbox',
				'disabled'    => defined( 'OIDC_ENABLE_LOGGING' ),
				'section'     => 'log_settings',
			),
			'log_limit'         => array(
				'title'       => __( 'Log Limit', 'daggerhart-openid-connect-generic' ),
				'description' => __( 'Number of items to keep in the log. These logs are stored as an option in the database, so space is limited.', 'daggerhart-openid-connect-generic' ),
				'type'        => 'number',
				'disabled'    => defined( 'OIDC_LOG_LIMIT' ),
				'section'     => 'log_settings',
			),
		);

		return apply_filters( 'openid-connect-generic-settings-fields', $fields );
	}

	/**
	 * Sanitization callback for settings/option page.
	 *
	 * @param array $input The submitted settings values.
	 *
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$options = array();

		// Loop through settings fields to control what we're saving.
		foreach ( $this->settings_fields as $key => $field ) {
			if ( isset( $input[ $key ] ) ) {
				$options[ $key ] = sanitize_text_field( trim( $input[ $key ] ) );
			} else {
				$options[ $key ] = '';
			}
		}

		return $options;
	}

	/**
	 * Output the options/settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		wp_enqueue_style( 'daggerhart-openid-connect-generic-admin', plugin_dir_url( __DIR__ ) . 'css/styles-admin.css', array(), OpenID_Connect_Generic::VERSION, 'all' );

		$redirect_uri = admin_url( 'admin-ajax.php?action=openid-connect-authorize' );

		if ( $this->settings->alternate_redirect_uri ) {
			$redirect_uri = site_url( '/openid-connect-authorize' );
		}
		?>
		<div class="wrap">
			<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( $this->settings_field_group );
				do_settings_sections( $this->options_page_name );
				submit_button();

				// Simple debug to view settings array.
				if ( isset( $_GET['debug'] ) ) {
					var_dump( $this->settings->get_values() );
				}
				?>
			</form>

			<h4><?php esc_html_e( 'Notes', 'daggerhart-openid-connect-generic' ); ?></h4>

			<p class="description">
				<strong><?php esc_html_e( 'Redirect URL:', 'daggerhart-openid-connect-generic' ); ?></strong>
				<code><?php print esc_url( $redirect_uri ); ?></code>
			</p>
			<p class="description">
				<strong><?php esc_html_e( 'Login Button Shortcode', 'daggerhart-openid-connect-generic' ); ?></strong>
				<code>[openid_connect_generic_login_button]</code>
			</p>
			<p class="description">
				<strong><?php esc_html_e( 'Authentication URL Shortcode', 'daggerhart-openid-connect-generic' ); ?></strong>
				<code>[openid_connect_generic_auth_url]</code>
			</p>
			<p class="description">
				<strong><?php esc_html_e( 'MiData Docu', 'daggerhart-openid-connect-generic' ); ?></strong>
				<code><a href="https://docu.scout.ch/">docu.scout.ch/</a></code>
			</p>
			<p class="description">
				<strong><?php esc_html_e( 'This Plugin was developed by Schlumpf and Vivo during the Hitobito Hackathon 2025. ', 'daggerhart-openid-connect-generic' ); ?></strong>
			</p>
			<p class="description">
				<strong><?php esc_html_e( '(c) 2025 Swiss Guide and Scout Movement', 'daggerhart-openid-connect-generic' ); ?></strong>
			</p>
			<?php if ( $this->settings->enable_logging ) { ?>
				<h2><?php esc_html_e( 'Logs', 'daggerhart-openid-connect-generic' ); ?></h2>
				<div id="logger-table-wrapper">
					<?php print wp_kses_post( $this->logger->get_logs_table() ); ?>
				</div>

			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Output a standard text field.
	 *
	 * @param array $field The settings field definition array.
	 *
	 * @return void
	 */
	public function do_text_field( $field ) {
		?>
		<input type="<?php print esc_attr( $field['type'] ); ?>"
			id="<?php print esc_attr( $field['key'] ); ?>"
			class="large-text<?php echo ( ! empty( $field['disabled'] ) && boolval( $field['disabled'] ) === true ) ? ' disabled' : ''; ?>"
			name="<?php print esc_attr( $field['name'] ); ?>"
			<?php echo ( ! empty( $field['disabled'] ) && boolval( $field['disabled'] ) === true ) ? ' disabled' : ''; ?>
			value="<?php print esc_attr( $this->settings->{ $field['key'] } ); ?>">
		<?php
		$this->do_field_description( $field );
	}

	/**
	 * Output a checkbox for a boolean setting.
	 *  - hidden field is default value so we don't have to check isset() on save.
	 *
	 * @param array $field The settings field definition array.
	 *
	 * @return void
	 */
	public function do_checkbox( $field ) {
		$hidden_value = 0;
		if ( ! empty( $field['disabled'] ) && boolval( $field['disabled'] ) === true ) {
			$hidden_value = intval( $this->settings->{ $field['key'] } );
		}
		?>
		<input type="hidden" name="<?php print esc_attr( $field['name'] ); ?>" value="<?php print esc_attr( strval( $hidden_value ) ); ?>">
		<input type="checkbox"
			   id="<?php print esc_attr( $field['key'] ); ?>"
				 name="<?php print esc_attr( $field['name'] ); ?>"
				 <?php echo ( ! empty( $field['disabled'] ) && boolval( $field['disabled'] ) === true ) ? ' disabled="disabled"' : ''; ?>
			   value="1"
			<?php checked( $this->settings->{ $field['key'] }, 1 ); ?>>
		<?php
		$this->do_field_description( $field );
	}

	/**
	 * Output a select control.
	 *
	 * @param array $field The settings field definition array.
	 *
	 * @return void
	 */
	public function do_select( $field ) {
		$current_value = isset( $this->settings->{ $field['key'] } ) ? $this->settings->{ $field['key'] } : '';
		?>
		<select
			id="<?php print esc_attr( $field['key'] ); ?>"
			name="<?php print esc_attr( $field['name'] ); ?>"
			<?php echo ( ! empty( $field['disabled'] ) && boolval( $field['disabled'] ) === true ) ? ' disabled' : ''; ?>
			>
			<?php foreach ( $field['options'] as $value => $text ) : ?>
				<option value="<?php print esc_attr( $value ); ?>" <?php selected( $value, $current_value ); ?>><?php print esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		$this->do_field_description( $field );
	}

	/**
	 * Output the field description, and example if present.
	 *
	 * @param array $field The settings field definition array.
	 *
	 * @return void
	 */
	public function do_field_description( $field ) {
		?>
		<p class="description">
			<?php print wp_kses_post( $field['description'] ); ?>
			<?php if ( isset( $field['example'] ) ) : ?>
				<br/><strong><?php esc_html_e( 'Example', 'daggerhart-openid-connect-generic' ); ?>: </strong>
				<code><?php print esc_html( $field['example'] ); ?></code>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Output the 'Client Settings' plugin setting section description.
	 *
	 * @return void
	 */
	public function client_settings_description() {
		esc_html_e( 'Enter your OpenID Connect identity provider settings.', 'daggerhart-openid-connect-generic' );
	}

	/**
	 * Output the 'WordPress User Settings' plugin setting section description.
	 *
	 * @return void
	 */
	public function user_settings_description() {
		esc_html_e( 'Modify the interaction between your Hitobito and WordPress the users.', 'daggerhart-openid-connect-generic' );
	}

	/**
	 * Output the 'Authorization Settings' plugin setting section description.
	 *
	 * @return void
	 */
	public function authorization_settings_description() {
		esc_html_e( 'Control the authorization mechanics of the site.', 'daggerhart-openid-connect-generic' );
	}

	/**
	 * Output the 'Log Settings' plugin setting section description.
	 *
	 * @return void
	 */
	public function log_settings_description() {
		esc_html_e( 'Log information about login attempts through OpenID Connect Generic.', 'daggerhart-openid-connect-generic' );
	}
}
