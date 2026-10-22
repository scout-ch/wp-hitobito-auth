<?php
/**
 * Login button style settings class.
 *
 * @package   Hitobito Auth
 * @category  Settings
 * @author    Swiss Guide and Scout Movement (Team MiData)
 * @copyright 2025-2026 Swiss Guide and Scout Movement
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */

/**
 * OpenID_Connect_Generic_Button_Style class.
 *
 * Settings sub page for the login button colors and icon, and the CSS output
 * for the login button.
 *
 * @package Hitobito Auth
 * @category  Settings
 */
class OpenID_Connect_Generic_Button_Style {

	/**
	 * WordPress option name/key.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'openid_connect_generic_button_style';

	/**
	 * Options page slug.
	 *
	 * @var string
	 */
	const PAGE_NAME = 'openid-connect-generic-button-style';

	/**
	 * Slug of the main plugin settings page.
	 *
	 * @var string
	 */
	const PARENT_PAGE_NAME = 'openid-connect-generic-settings';

	/**
	 * Name of the file upload field for the icon.
	 *
	 * @var string
	 */
	const ICON_UPLOAD_FIELD = 'openid_connect_generic_button_icon';

	/**
	 * Maximum size of an uploaded SVG icon in bytes.
	 *
	 * @var int
	 */
	const ICON_MAX_SIZE = 102400;

	/**
	 * Options page settings group name.
	 *
	 * @var string
	 */
	private $settings_field_group = self::OPTION_NAME . '-group';

	/**
	 * Plugin settings object.
	 *
	 * @var OpenID_Connect_Generic_Option_Settings
	 */
	private $settings;

	/**
	 * The class constructor.
	 *
	 * @param OpenID_Connect_Generic_Option_Settings $settings A plugin settings object instance.
	 */
	public function __construct( OpenID_Connect_Generic_Option_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Hook the settings sub page into WordPress.
	 *
	 * @param OpenID_Connect_Generic_Option_Settings $settings A plugin settings object instance.
	 *
	 * @return void
	 */
	public static function register( OpenID_Connect_Generic_Option_Settings $settings ) {
		$page = new self( $settings );

		add_action( 'admin_menu', array( $page, 'admin_menu' ) );
		add_action( 'admin_head', array( $page, 'hide_submenu_entry' ) );
		add_filter( 'submenu_file', array( $page, 'highlight_parent_submenu' ) );
		add_action( 'admin_init', array( $page, 'admin_init' ) );
		add_action( 'admin_enqueue_scripts', array( $page, 'admin_enqueue_scripts' ) );
	}

	/**
	 * Get the color settings fields definition.
	 *
	 * @return array<string,array<string,string>>
	 */
	public static function get_color_fields() {
		return array(
			'text_color'             => array(
				'title'   => __( 'Text', 'daggerhart-openid-connect-generic' ),
				'section' => 'normal',
				'var'     => 'text',
			),
			'background_color'       => array(
				'title'   => __( 'Background', 'daggerhart-openid-connect-generic' ),
				'section' => 'normal',
				'var'     => 'bg',
			),
			'border_color'           => array(
				'title'   => __( 'Border', 'daggerhart-openid-connect-generic' ),
				'section' => 'normal',
				'var'     => 'border',
			),
			'icon_color'             => array(
				'title'   => __( 'Icon', 'daggerhart-openid-connect-generic' ),
				'section' => 'normal',
				'var'     => 'icon',
			),
			'hover_text_color'       => array(
				'title'   => __( 'Text', 'daggerhart-openid-connect-generic' ),
				'section' => 'hover',
				'var'     => 'text',
			),
			'hover_background_color' => array(
				'title'   => __( 'Background', 'daggerhart-openid-connect-generic' ),
				'section' => 'hover',
				'var'     => 'bg',
			),
			'hover_border_color'     => array(
				'title'   => __( 'Border', 'daggerhart-openid-connect-generic' ),
				'section' => 'hover',
				'var'     => 'border',
			),
			'hover_icon_color'       => array(
				'title'   => __( 'Icon', 'daggerhart-openid-connect-generic' ),
				'section' => 'hover',
				'var'     => 'icon',
			),
		);
	}

	/**
	 * Get the stored button style values, completed with empty defaults.
	 *
	 * @return array<string,string>
	 */
	public static function get_values() {
		$defaults = array_fill_keys( array_keys( self::get_color_fields() ), '' );
		$defaults['icon_svg'] = '';

		return array_merge( $defaults, (array) get_option( self::OPTION_NAME, array() ) );
	}

	/**
	 * Get the stored icon as a data URI usable in CSS.
	 *
	 * @param array<string,string> $values The button style values.
	 *
	 * @return string Empty string if no icon is set.
	 */
	public static function get_icon_data_uri( $values ) {
		if ( empty( $values['icon_svg'] ) ) {
			return '';
		}

		return 'data:image/svg+xml;base64,' . base64_encode( $values['icon_svg'] );
	}

	/**
	 * Build the CSS for the login button.
	 *
	 * The colors are exposed as custom properties on the button so the hover
	 * state only has to swap the variables. Colors which are not configured
	 * keep the styling of the theme (or of the WordPress login page).
	 *
	 * @param string $selector CSS selector of the button.
	 *
	 * @return string
	 */
	public static function get_css( $selector = '.openid-connect-login-button a.hitobito-auth-button' ) {
		$values   = self::get_values();
		$icon_uri = self::get_icon_data_uri( $values );

		$normal = array();
		$hover  = array();
		foreach ( self::get_color_fields() as $key => $field ) {
			$color = sanitize_hex_color( $values[ $key ] );
			if ( empty( $color ) ) {
				continue;
			}

			if ( 'normal' === $field['section'] ) {
				$normal[ $field['var'] ] = $color;
			} else {
				$hover[ $field['var'] ] = $color;
			}
		}

		$properties = array(
			'text'   => 'color',
			'bg'     => 'background',
			'border' => 'border-color',
		);

		$css = '';

		$rules = array();
		foreach ( $properties as $var => $property ) {
			if ( isset( $normal[ $var ] ) ) {
				$rules[] = "{$property}:{$normal[ $var ]};";
			}
		}
		if ( isset( $normal['border'] ) ) {
			$rules[] = 'border-style:solid;border-width:1px;';
		}
		if ( $icon_uri ) {
			$rules[] = 'display:inline-flex;align-items:center;justify-content:center;gap:.5em;';
			$rules[] = '--hitobito-auth-icon:' . ( $normal['icon'] ?? 'currentColor' ) . ';';
		}
		if ( $rules ) {
			$css .= $selector . '{' . implode( '', $rules ) . '}';
		}

		// Unset hover colors fall back to the normal colors, otherwise the
		// hover styles of the theme or the login page would take over.
		$hover = array_merge( $normal, $hover );

		$rules = array();
		foreach ( $properties as $var => $property ) {
			if ( isset( $hover[ $var ] ) ) {
				$rules[] = "{$property}:{$hover[ $var ]};";
			}
		}
		if ( $icon_uri && isset( $hover['icon'] ) ) {
			$rules[] = "--hitobito-auth-icon:{$hover['icon']};";
		}
		if ( $rules ) {
			$css .= "{$selector}:hover,{$selector}:focus{" . implode( '', $rules ) . '}';
		}

		if ( $icon_uri ) {
			$css .= "{$selector} .hitobito-auth-button__icon{"
				. 'display:inline-block;flex:none;width:1.25em;height:1.25em;'
				. 'background-color:var(--hitobito-auth-icon,currentColor);'
				. "-webkit-mask:url({$icon_uri}) center/contain no-repeat;"
				. "mask:url({$icon_uri}) center/contain no-repeat;"
				. '}';
		}

		return $css;
	}

	/**
	 * Output the navigation tabs shared by the plugin settings pages.
	 *
	 * @param string $active The slug of the active page.
	 *
	 * @return void
	 */
	public static function do_nav_tabs( $active ) {
		$tabs = array(
			self::PARENT_PAGE_NAME => __( 'Settings', 'daggerhart-openid-connect-generic' ),
			self::PAGE_NAME        => __( 'Login Button', 'daggerhart-openid-connect-generic' ),
		);
		?>
		<nav class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php print esc_url( admin_url( 'options-general.php?page=' . $slug ) ); ?>"
					class="nav-tab<?php echo ( $slug === $active ) ? ' nav-tab-active' : ''; ?>"><?php print esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Implements hook admin_menu to add the sub page.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Hitobito Auth', 'daggerhart-openid-connect-generic' ),
			__( 'Hitobito Auth Button', 'daggerhart-openid-connect-generic' ),
			'manage_options',
			self::PAGE_NAME,
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Remove the sub page from the menu, it is reachable through the tabs.
	 *
	 * @return void
	 */
	public function hide_submenu_entry() {
		remove_submenu_page( 'options-general.php', self::PAGE_NAME );
	}

	/**
	 * Implements filter submenu_file to highlight the main settings page in
	 * the menu while the sub page is displayed.
	 *
	 * @param string|null $submenu_file The submenu file.
	 *
	 * @return string|null
	 */
	public function highlight_parent_submenu( $submenu_file ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only used to highlight the menu.
		if ( isset( $_GET['page'] ) && self::PAGE_NAME === $_GET['page'] ) {
			return self::PARENT_PAGE_NAME;
		}

		return $submenu_file;
	}

	/**
	 * Implements hook admin_init to register the settings.
	 *
	 * @return void
	 */
	public function admin_init() {
		register_setting(
			$this->settings_field_group,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'normal',
			__( 'Normal', 'daggerhart-openid-connect-generic' ),
			array( $this, 'normal_section_description' ),
			self::PAGE_NAME
		);

		add_settings_section(
			'hover',
			__( 'Hover', 'daggerhart-openid-connect-generic' ),
			array( $this, 'hover_section_description' ),
			self::PAGE_NAME
		);

		add_settings_section(
			'icon',
			__( 'Icon', 'daggerhart-openid-connect-generic' ),
			array( $this, 'icon_section_description' ),
			self::PAGE_NAME
		);

		foreach ( self::get_color_fields() as $key => $field ) {
			$field['key'] = $key;

			add_settings_field(
				$key,
				$field['title'],
				array( $this, 'do_color_field' ),
				self::PAGE_NAME,
				$field['section'],
				$field
			);
		}

		add_settings_field(
			'icon_svg',
			__( 'SVG Icon', 'daggerhart-openid-connect-generic' ),
			array( $this, 'do_icon_field' ),
			self::PAGE_NAME,
			'icon'
		);
	}

	/**
	 * Implements hook admin_enqueue_scripts to load the color picker.
	 *
	 * @param string $hook_suffix The current admin page.
	 *
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_NAME !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Initialize the color pickers and keep the preview button up to date.
		$script = <<<'JS'
jQuery( function( $ ) {
	var preview = document.getElementById( 'hitobito-auth-button-preview' );

	function update( input, color ) {
		var name = input.data( 'preview-var' );
		if ( ! preview || ! name ) {
			return;
		}
		if ( color ) {
			preview.style.setProperty( name, color );
		} else {
			preview.style.removeProperty( name );
		}
	}

	$( '.hitobito-auth-color-field' ).each( function() {
		var input = $( this );
		update( input, input.val() );
		input.wpColorPicker( {
			change: function( event, ui ) { update( input, ui.color.toString() ); },
			clear: function() { update( input, '' ); }
		} );
	} );
} );
JS;
		wp_add_inline_script( 'wp-color-picker', $script );
	}

	/**
	 * Sanitization callback for the settings page.
	 *
	 * @param array $input The submitted settings values.
	 *
	 * @return array
	 */
	public function sanitize_settings( $input ) {
		$input   = (array) $input;
		$current = self::get_values();
		$options = array();

		foreach ( array_keys( self::get_color_fields() ) as $key ) {
			$options[ $key ] = isset( $input[ $key ] ) ? (string) sanitize_hex_color( trim( $input[ $key ] ) ) : '';
		}

		// The icon is never taken from the posted values, only from an upload.
		$options['icon_svg'] = $current['icon_svg'];

		if ( ! empty( $input['remove_icon'] ) ) {
			$options['icon_svg'] = '';
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is verified by options.php.
		if ( ! empty( $_FILES[ self::ICON_UPLOAD_FIELD ]['tmp_name'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.NonceVerification.Missing -- File is validated below.
			$svg = $this->read_uploaded_icon( $_FILES[ self::ICON_UPLOAD_FIELD ] );
			if ( is_wp_error( $svg ) ) {
				add_settings_error( self::OPTION_NAME, $svg->get_error_code(), $svg->get_error_message() );
			} else {
				$options['icon_svg'] = $svg;
			}
		}

		return $options;
	}

	/**
	 * Read and validate an uploaded SVG icon.
	 *
	 * The icon is only ever used as a CSS mask image, so it is never inserted
	 * into the page markup. It still has to be a well-formed SVG document.
	 *
	 * @param array $file The uploaded file entry from $_FILES.
	 *
	 * @return string|WP_Error The SVG markup.
	 */
	private function read_uploaded_icon( $file ) {
		if ( ! empty( $file['error'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'icon_upload', __( 'The icon could not be uploaded.', 'daggerhart-openid-connect-generic' ) );
		}

		if ( 'svg' !== strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ) {
			return new WP_Error( 'icon_type', __( 'The icon must be an SVG file.', 'daggerhart-openid-connect-generic' ) );
		}

		if ( $file['size'] > self::ICON_MAX_SIZE ) {
			return new WP_Error( 'icon_size', __( 'The icon must not be larger than 100 KB.', 'daggerhart-openid-connect-generic' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local uploaded file.
		$content = file_get_contents( $file['tmp_name'] );

		// Reject document type declarations to prevent entity expansion attacks.
		if ( false === $content || false !== stripos( $content, '<!DOCTYPE' ) || false !== stripos( $content, '<!ENTITY' ) ) {
			return new WP_Error( 'icon_invalid', __( 'The icon is not a valid SVG file.', 'daggerhart-openid-connect-generic' ) );
		}

		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument();
		$loaded   = $document->loadXML( $content, LIBXML_NONET );
		libxml_clear_errors();
		libxml_use_internal_errors( $previous );

		if ( ! $loaded || ! $document->documentElement || 'svg' !== strtolower( $document->documentElement->localName ) ) {
			return new WP_Error( 'icon_invalid', __( 'The icon is not a valid SVG file.', 'daggerhart-openid-connect-generic' ) );
		}

		// Browsers only render SVG images with the SVG namespace.
		if ( ! $document->documentElement->hasAttribute( 'xmlns' ) ) {
			$document->documentElement->setAttribute( 'xmlns', 'http://www.w3.org/2000/svg' );
		}

		return $document->saveXML( $document->documentElement );
	}

	/**
	 * Output the settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		$values   = self::get_values();
		$icon_uri = self::get_icon_data_uri( $values );

		$button_text = ! empty( trim( $this->settings->button_txt ?? '' ) )
			? $this->settings->button_txt
			: __( 'Login with Hitobito', 'daggerhart-openid-connect-generic' );
		?>
		<div class="wrap">
			<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

			<?php self::do_nav_tabs( self::PAGE_NAME ); ?>

			<style>
				#hitobito-auth-button-preview {
					display: inline-flex;
					align-items: center;
					gap: .5em;
					color: var(--hitobito-auth-text, #2271b1);
					background: var(--hitobito-auth-bg, #f6f7f7);
					border: 1px solid var(--hitobito-auth-border, #2271b1);
				}
				#hitobito-auth-button-preview:hover,
				#hitobito-auth-button-preview:focus {
					color: var(--hitobito-auth-hover-text, var(--hitobito-auth-text, #0a4b78));
					background: var(--hitobito-auth-hover-bg, var(--hitobito-auth-bg, #f0f0f1));
					border-color: var(--hitobito-auth-hover-border, var(--hitobito-auth-border, #0a4b78));
				}
				#hitobito-auth-button-preview .hitobito-auth-button__icon {
					flex: none;
					width: 1.25em;
					height: 1.25em;
					background-color: var(--hitobito-auth-icon, currentColor);
					-webkit-mask: var(--hitobito-auth-icon-image) center/contain no-repeat;
					mask: var(--hitobito-auth-icon-image) center/contain no-repeat;
				}
				#hitobito-auth-button-preview:hover .hitobito-auth-button__icon,
				#hitobito-auth-button-preview:focus .hitobito-auth-button__icon {
					background-color: var(--hitobito-auth-hover-icon, var(--hitobito-auth-icon, currentColor));
				}
			</style>

			<h3><?php esc_html_e( 'Preview', 'daggerhart-openid-connect-generic' ); ?></h3>
			<p class="description"><?php esc_html_e( 'The preview shows the saved icon. Colors are updated while you edit them. Empty colors keep the styling of your theme.', 'daggerhart-openid-connect-generic' ); ?></p>
			<p>
				<a id="hitobito-auth-button-preview" class="button button-large" href="#" onclick="return false;"
					<?php if ( $icon_uri ) : ?>
						style="--hitobito-auth-icon-image: url(<?php print esc_attr( $icon_uri ); ?>);"
					<?php endif; ?>
					>
					<?php if ( $icon_uri ) : ?>
						<span class="hitobito-auth-button__icon" aria-hidden="true"></span>
					<?php endif; ?>
					<?php print esc_html( $button_text ); ?>
				</a>
			</p>

			<form method="post" action="options.php" enctype="multipart/form-data">
				<?php
				settings_fields( $this->settings_field_group );
				do_settings_sections( self::PAGE_NAME );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Output a color picker field.
	 *
	 * @param array $field The settings field definition array.
	 *
	 * @return void
	 */
	public function do_color_field( $field ) {
		$values      = self::get_values();
		$preview_var = '--hitobito-auth-' . ( 'hover' === $field['section'] ? 'hover-' : '' ) . $field['var'];
		?>
		<input type="text"
			id="<?php print esc_attr( $field['key'] ); ?>"
			class="hitobito-auth-color-field"
			name="<?php print esc_attr( self::OPTION_NAME . '[' . $field['key'] . ']' ); ?>"
			data-preview-var="<?php print esc_attr( $preview_var ); ?>"
			value="<?php print esc_attr( $values[ $field['key'] ] ); ?>">
		<?php
	}

	/**
	 * Output the icon upload field.
	 *
	 * @return void
	 */
	public function do_icon_field() {
		$values = self::get_values();
		?>
		<input type="file"
			id="<?php print esc_attr( self::ICON_UPLOAD_FIELD ); ?>"
			name="<?php print esc_attr( self::ICON_UPLOAD_FIELD ); ?>"
			accept=".svg,image/svg+xml">
		<p class="description">
			<?php esc_html_e( 'Upload an SVG file (max. 100 KB), e.g. the logo of your Hitobito instance like the MiData. The icon is shown left of the button text and colored with the icon colors above; if no icon color is set it uses the text color. Works best with single-color icons.', 'daggerhart-openid-connect-generic' ); ?>
		</p>
		<?php if ( ! empty( $values['icon_svg'] ) ) : ?>
			<p>
				<label>
					<input type="checkbox" name="<?php print esc_attr( self::OPTION_NAME . '[remove_icon]' ); ?>" value="1">
					<?php esc_html_e( 'Remove current icon', 'daggerhart-openid-connect-generic' ); ?>
				</label>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Output the 'Normal' section description.
	 *
	 * @return void
	 */
	public function normal_section_description() {
		esc_html_e( 'Colors of the login button. Leave a color empty to keep the styling of your theme.', 'daggerhart-openid-connect-generic' );
	}

	/**
	 * Output the 'Hover' section description.
	 *
	 * @return void
	 */
	public function hover_section_description() {
		esc_html_e( 'Colors of the login button when the mouse is over it or it has the keyboard focus.', 'daggerhart-openid-connect-generic' );
	}

	/**
	 * Output the 'Icon' section description.
	 *
	 * @return void
	 */
	public function icon_section_description() {
		esc_html_e( 'Personalize the button with the icon of your Hitobito instance.', 'daggerhart-openid-connect-generic' );
	}
}
