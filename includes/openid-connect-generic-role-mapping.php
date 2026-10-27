<?php
/**
 * Hitobito role mapping class.
 *
 * @package   Hitobito Auth
 * @category  Settings
 * @author    Swiss Guide and Scout Movement (Team MiData)
 * @copyright 2025-2026 Swiss Guide and Scout Movement
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GPL-2.0+
 */

/**
 * OpenID_Connect_Generic_Role_Mapping class.
 *
 * Assigns WordPress roles based on the Hitobito roles of a user (scope
 * `with_roles`), and provides the settings sub page for the mapping rules.
 *
 * Each role in the Hitobito claim looks like:
 * `{ group_id, group_name, role, role_class, role_name, permissions }`.
 * Hitobito does not include the layer of the group, so a rule has to list
 * every group (e.g. the department and all of its units) it applies to.
 *
 * @package Hitobito Auth
 * @category  Settings
 */
class OpenID_Connect_Generic_Role_Mapping {

	/**
	 * WordPress option name/key.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'openid_connect_generic_role_mapping';

	/**
	 * Options page slug.
	 *
	 * @var string
	 */
	const PAGE_NAME = 'openid-connect-generic-role-mapping';

	/**
	 * Hitobito scope which adds the roles to the user claim.
	 *
	 * @var string
	 */
	const SCOPE = 'with_roles';

	/**
	 * Value of the fallback role which denies the login.
	 *
	 * @var string
	 */
	const DENY = 'deny';

	/**
	 * Options page settings group name.
	 *
	 * @var string
	 */
	private $settings_field_group = self::OPTION_NAME . '-group';

	/**
	 * Hook the settings sub page and the user profile section into WordPress.
	 *
	 * @return void
	 */
	public static function register() {
		$page = new self();

		add_action( 'admin_menu', array( $page, 'admin_menu' ) );
		add_action( 'admin_head', array( $page, 'hide_submenu_entry' ) );
		add_filter( 'submenu_file', array( $page, 'highlight_parent_submenu' ) );
		add_action( 'admin_init', array( $page, 'admin_init' ) );
		add_action( 'show_user_profile', array( $page, 'do_user_profile_section' ) );
		add_action( 'edit_user_profile', array( $page, 'do_user_profile_section' ) );
	}

	/**
	 * Get the stored role mapping values, completed with defaults.
	 *
	 * @return array{enabled:int,rules:string,fallback_role:string}
	 */
	public static function get_values() {
		$defaults = array(
			'enabled'       => 0,
			'rules'         => '',
			'fallback_role' => 'subscriber',
		);

		return array_merge( $defaults, (array) get_option( self::OPTION_NAME, array() ) );
	}

	/**
	 * Whether the role mapping is enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		return ! empty( self::get_values()['enabled'] );
	}

	/**
	 * Add the Hitobito roles scope to the requested scopes if the mapping is enabled.
	 *
	 * @param string $scope The configured scopes, separated by spaces.
	 *
	 * @return string
	 */
	public static function get_scope( $scope ) {
		$scopes = array_filter( explode( ' ', (string) $scope ) );

		if ( self::is_enabled() && ! in_array( self::SCOPE, $scopes, true ) ) {
			$scopes[] = self::SCOPE;
		}

		return implode( ' ', $scopes );
	}

	/**
	 * Get the WordPress roles which may be assigned by the mapping.
	 *
	 * Roles with the capability to manage the site are excluded, so a
	 * Hitobito role can never grant administrator access.
	 *
	 * @return array<string,string> Role slugs and their translated names.
	 */
	public static function get_assignable_roles() {
		$roles = array();

		foreach ( wp_roles()->roles as $slug => $role ) {
			if ( ! empty( $role['capabilities']['manage_options'] ) ) {
				continue;
			}
			$roles[ $slug ] = translate_user_role( $role['name'] );
		}

		return $roles;
	}

	/**
	 * Parse the mapping rules.
	 *
	 * One rule per line: `group ids | role classes | WordPress role`.
	 * Group ids and role classes are comma separated, `*` is a wildcard.
	 * Empty lines and lines starting with `#` are ignored.
	 *
	 * @param string             $text   The rules as entered in the settings.
	 * @param array<int,string>  $errors Receives an error message per invalid line, keyed by line number.
	 *
	 * @return array<int,array{groups:array<string>,role_classes:array<string>,wp_role:string}>
	 */
	public static function parse_rules( $text, &$errors = array() ) {
		$rules            = array();
		$errors           = array();
		$assignable_roles = self::get_assignable_roles();

		foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $index => $line ) {
			$line_number = $index + 1;
			$line        = trim( $line );

			if ( '' === $line || '#' === $line[0] ) {
				continue;
			}

			$parts = array_map( 'trim', explode( '|', $line ) );
			if ( 3 !== count( $parts ) ) {
				$errors[ $line_number ] = __( 'Expected "group ids | role classes | WordPress role".', 'daggerhart-openid-connect-generic' );
				continue;
			}

			$groups       = array_values( array_filter( array_map( 'trim', explode( ',', $parts[0] ) ), 'strlen' ) );
			$role_classes = array_values( array_filter( array_map( 'trim', explode( ',', $parts[1] ) ), 'strlen' ) );
			$wp_role      = $parts[2];

			if ( empty( $groups ) || preg_grep( '/^(\d+|\*)$/', $groups, PREG_GREP_INVERT ) ) {
				$errors[ $line_number ] = __( 'Group ids must be numbers or *.', 'daggerhart-openid-connect-generic' );
				continue;
			}

			if ( empty( $role_classes ) ) {
				$errors[ $line_number ] = __( 'At least one role class or * is required.', 'daggerhart-openid-connect-generic' );
				continue;
			}

			if ( ! isset( $assignable_roles[ $wp_role ] ) ) {
				// translators: %s is the WordPress role slug entered in the rule.
				$errors[ $line_number ] = sprintf( __( 'The WordPress role "%s" does not exist or has administrator capabilities.', 'daggerhart-openid-connect-generic' ), $wp_role );
				continue;
			}

			$rules[] = array(
				'groups'       => $groups,
				'role_classes' => $role_classes,
				'wp_role'      => $wp_role,
			);
		}

		return $rules;
	}

	/**
	 * Determine the WordPress role for a user claim.
	 *
	 * The rules are checked from top to bottom, the first rule matching any
	 * of the Hitobito roles wins. Without a matching rule the fallback role is used.
	 *
	 * @param array $user_claim The authenticated user claim.
	 *
	 * @return string|null The WordPress role slug, self::DENY, or null if the mapping is disabled.
	 */
	public static function get_role_for_claim( $user_claim ) {
		$values = self::get_values();

		if ( empty( $values['enabled'] ) ) {
			return null;
		}

		$hitobito_roles = ( isset( $user_claim['roles'] ) && is_array( $user_claim['roles'] ) ) ? $user_claim['roles'] : array();

		foreach ( self::parse_rules( $values['rules'] ) as $rule ) {
			foreach ( $hitobito_roles as $hitobito_role ) {
				if ( self::rule_matches( $rule, $hitobito_role ) ) {
					return $rule['wp_role'];
				}
			}
		}

		if ( self::DENY === $values['fallback_role'] || isset( self::get_assignable_roles()[ $values['fallback_role'] ] ) ) {
			return $values['fallback_role'];
		}

		return 'subscriber';
	}

	/**
	 * Check whether a rule matches a single Hitobito role.
	 *
	 * @param array{groups:array<string>,role_classes:array<string>} $rule          The parsed rule.
	 * @param mixed                                                    $hitobito_role A role from the user claim.
	 *
	 * @return bool
	 */
	private static function rule_matches( $rule, $hitobito_role ) {
		if ( ! is_array( $hitobito_role ) || ! isset( $hitobito_role['group_id'], $hitobito_role['role_class'] ) ) {
			return false;
		}

		$group_id = (string) $hitobito_role['group_id'];
		if ( ! in_array( '*', $rule['groups'], true ) && ! in_array( $group_id, $rule['groups'], true ) ) {
			return false;
		}

		foreach ( $rule['role_classes'] as $pattern ) {
			if ( fnmatch( $pattern, (string) $hitobito_role['role_class'], FNM_NOESCAPE ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the role of a user must never be changed by the mapping.
	 *
	 * @param WP_User $user The user.
	 *
	 * @return bool
	 */
	public static function is_protected_user( $user ) {
		return user_can( $user, 'manage_options' ) || is_super_admin( $user->ID );
	}

	/**
	 * Assign the mapped WordPress role to a user.
	 *
	 * @param WP_User                              $user       The user.
	 * @param array                                $user_claim The authenticated user claim.
	 * @param OpenID_Connect_Generic_Option_Logger $logger     The plugin logger.
	 *
	 * @return void
	 */
	public static function sync_user_role( $user, $user_claim, $logger ) {
		$role = self::get_role_for_claim( $user_claim );

		if ( null === $role || self::DENY === $role || self::is_protected_user( $user ) ) {
			return;
		}

		if ( array( $role ) === array_values( $user->roles ) ) {
			return;
		}

		$previous_roles = implode( ', ', $user->roles );
		$user->set_role( $role );
		$logger->log( "Role of {$user->user_login} ({$user->ID}) changed from '{$previous_roles}' to '{$role}'", 'role-mapping' );
	}

	/**
	 * Implements hook admin_menu to add the sub page.
	 *
	 * @return void
	 */
	public function admin_menu() {
		add_options_page(
			__( 'Hitobito Auth', 'daggerhart-openid-connect-generic' ),
			__( 'Hitobito Auth Roles', 'daggerhart-openid-connect-generic' ),
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
			return OpenID_Connect_Generic_Button_Style::PARENT_PAGE_NAME;
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
			'role_mapping',
			__( 'Role Mapping', 'daggerhart-openid-connect-generic' ),
			array( $this, 'role_mapping_section_description' ),
			self::PAGE_NAME
		);

		add_settings_field(
			'enabled',
			__( 'Enable role mapping', 'daggerhart-openid-connect-generic' ),
			array( $this, 'do_enabled_field' ),
			self::PAGE_NAME,
			'role_mapping'
		);

		add_settings_field(
			'rules',
			__( 'Rules', 'daggerhart-openid-connect-generic' ),
			array( $this, 'do_rules_field' ),
			self::PAGE_NAME,
			'role_mapping'
		);

		add_settings_field(
			'fallback_role',
			__( 'Users without matching rule', 'daggerhart-openid-connect-generic' ),
			array( $this, 'do_fallback_role_field' ),
			self::PAGE_NAME,
			'role_mapping'
		);
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
		$options = array(
			'enabled'       => empty( $input['enabled'] ) ? 0 : 1,
			'rules'         => isset( $input['rules'] ) ? sanitize_textarea_field( $input['rules'] ) : '',
			'fallback_role' => isset( $input['fallback_role'] ) ? sanitize_key( $input['fallback_role'] ) : 'subscriber',
		);

		if ( self::DENY !== $options['fallback_role'] && ! isset( self::get_assignable_roles()[ $options['fallback_role'] ] ) ) {
			$options['fallback_role'] = 'subscriber';
		}

		// Invalid lines are kept so they can be corrected, but are ignored on login.
		self::parse_rules( $options['rules'], $errors );
		foreach ( $errors as $line_number => $message ) {
			// translators: %1$d is the line number, %2$s the error message.
			add_settings_error( self::OPTION_NAME, 'rule_' . $line_number, sprintf( __( 'Rule on line %1$d is ignored: %2$s', 'daggerhart-openid-connect-generic' ), $line_number, $message ) );
		}

		return $options;
	}

	/**
	 * Output the settings page.
	 *
	 * @return void
	 */
	public function settings_page() {
		?>
		<div class="wrap">
			<h2><?php print esc_html( get_admin_page_title() ); ?></h2>

			<?php OpenID_Connect_Generic_Button_Style::do_nav_tabs( self::PAGE_NAME ); ?>

			<form method="post" action="options.php">
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
	 * Output the 'Role Mapping' section description.
	 *
	 * @return void
	 */
	public function role_mapping_section_description() {
		?>
		<p><?php esc_html_e( 'Assign WordPress roles based on the roles of a user in Hitobito. The role is updated on every login with Hitobito. Administrators are never changed, and no rule can grant administrator access.', 'daggerhart-openid-connect-generic' ); ?></p>
		<p>
			<?php
			// translators: %s is the name of the Hitobito scope.
			printf( esc_html__( 'The mapping requests the additional scope %s. It has to be enabled for the OAuth application in Hitobito before you enable the mapping, otherwise the login fails.', 'daggerhart-openid-connect-generic' ), '<code>' . esc_html( self::SCOPE ) . '</code>' );
			?>
		</p>
		<?php
	}

	/**
	 * Output the enable checkbox.
	 *
	 * @return void
	 */
	public function do_enabled_field() {
		$values = self::get_values();
		?>
		<input type="checkbox"
			id="role_mapping_enabled"
			name="<?php print esc_attr( self::OPTION_NAME . '[enabled]' ); ?>"
			value="1"
			<?php checked( $values['enabled'], 1 ); ?>>
		<?php
	}

	/**
	 * Output the rules textarea.
	 *
	 * @return void
	 */
	public function do_rules_field() {
		$values  = self::get_values();
		$example = "# Leaders of the department\n100, 101, 102 | Group::Abteilung::Abteilungsleitung, Group::Pfadi::Einheitsleitung | editor\n# Everyone else in the department\n100, 101, 102 | * | author";
		?>
		<textarea
			id="role_mapping_rules"
			class="large-text code"
			rows="10"
			name="<?php print esc_attr( self::OPTION_NAME . '[rules]' ); ?>"><?php print esc_textarea( $values['rules'] ); ?></textarea>
		<p class="description">
			<?php esc_html_e( 'One rule per line: group ids | role classes | WordPress role. Separate several group ids or role classes with commas, * matches everything (e.g. Group::Abteilung::*). The rules are checked from top to bottom and the first matching rule wins, so put the rules with more rights first.', 'daggerhart-openid-connect-generic' ); ?>
		</p>
		<p class="description">
			<?php esc_html_e( 'Hitobito only reports the group a role is directly in. To match a whole department, list the department and all of its subgroups. Each user profile shows the Hitobito roles of the last login, including the group ids and role classes.', 'daggerhart-openid-connect-generic' ); ?>
		</p>
		<p class="description">
			<strong><?php esc_html_e( 'Example', 'daggerhart-openid-connect-generic' ); ?>:</strong>
		</p>
		<pre><code><?php print esc_html( $example ); ?></code></pre>
		<?php
	}

	/**
	 * Output the fallback role select.
	 *
	 * @return void
	 */
	public function do_fallback_role_field() {
		$values  = self::get_values();
		$options = array( self::DENY => __( '— Deny login —', 'daggerhart-openid-connect-generic' ) ) + self::get_assignable_roles();
		?>
		<select id="role_mapping_fallback_role" name="<?php print esc_attr( self::OPTION_NAME . '[fallback_role]' ); ?>">
			<?php foreach ( $options as $value => $text ) : ?>
				<option value="<?php print esc_attr( $value ); ?>" <?php selected( $value, $values['fallback_role'] ); ?>><?php print esc_html( $text ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description">
			<?php esc_html_e( 'Role for users whose Hitobito roles match no rule, e.g. everyone outside of your organisation. "Deny login" blocks them instead and no WordPress account is created for them.', 'daggerhart-openid-connect-generic' ); ?>
		</p>
		<?php
	}

	/**
	 * Output the Hitobito roles of the last login on the user profile.
	 *
	 * Only shown to administrators, to look up group ids and role classes for the rules.
	 *
	 * @param WP_User $user The user whose profile is displayed.
	 *
	 * @return void
	 */
	public function do_user_profile_section( $user ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$user_claim = get_user_option( 'openid-connect-generic-last-user-claim', $user->ID );
		if ( ! is_array( $user_claim ) ) {
			return;
		}

		$hitobito_roles = ( isset( $user_claim['roles'] ) && is_array( $user_claim['roles'] ) ) ? $user_claim['roles'] : null;
		$mapped_role    = self::get_role_for_claim( $user_claim );
		?>
		<h2><?php esc_html_e( 'Hitobito Roles', 'daggerhart-openid-connect-generic' ); ?></h2>
		<?php if ( null === $hitobito_roles ) : ?>
			<p class="description">
				<?php
				// translators: %s is the name of the Hitobito scope.
				printf( esc_html__( 'The last login contained no roles. Enable the role mapping (scope %s) and log in again with Hitobito.', 'daggerhart-openid-connect-generic' ), '<code>' . esc_html( self::SCOPE ) . '</code>' );
				?>
			</p>
		<?php else : ?>
			<p class="description"><?php esc_html_e( 'Roles in Hitobito at the last login.', 'daggerhart-openid-connect-generic' ); ?></p>
			<table class="widefat striped" style="max-width: 60em;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Group id', 'daggerhart-openid-connect-generic' ); ?></th>
						<th><?php esc_html_e( 'Group', 'daggerhart-openid-connect-generic' ); ?></th>
						<th><?php esc_html_e( 'Role', 'daggerhart-openid-connect-generic' ); ?></th>
						<th><?php esc_html_e( 'Role class', 'daggerhart-openid-connect-generic' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $hitobito_roles as $hitobito_role ) : ?>
						<tr>
							<td><code><?php print esc_html( (string) ( $hitobito_role['group_id'] ?? '' ) ); ?></code></td>
							<td><?php print esc_html( (string) ( $hitobito_role['group_name'] ?? '' ) ); ?></td>
							<td><?php print esc_html( (string) ( $hitobito_role['role_name'] ?? '' ) ); ?></td>
							<td><code><?php print esc_html( (string) ( $hitobito_role['role_class'] ?? '' ) ); ?></code></td>
						</tr>
					<?php endforeach; ?>
					<?php if ( empty( $hitobito_roles ) ) : ?>
						<tr><td colspan="4"><?php esc_html_e( 'No roles.', 'daggerhart-openid-connect-generic' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php if ( null !== $mapped_role ) : ?>
				<p>
					<strong><?php esc_html_e( 'Mapped WordPress role:', 'daggerhart-openid-connect-generic' ); ?></strong>
					<?php
					if ( self::is_protected_user( $user ) ) {
						esc_html_e( 'unchanged (administrator)', 'daggerhart-openid-connect-generic' );
					} elseif ( self::DENY === $mapped_role ) {
						esc_html_e( 'login denied', 'daggerhart-openid-connect-generic' );
					} else {
						print esc_html( self::get_assignable_roles()[ $mapped_role ] ?? $mapped_role );
					}
					?>
				</p>
			<?php endif; ?>
		<?php endif; ?>
		<?php
	}
}
