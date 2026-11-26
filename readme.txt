=== Hitobito OAuth Authentication ===
Contributors: laurinberli, elfangor, masteradhoc
Tags: hitobito, sso, oauth
Requires at least: 6.7.2
Tested up to: 7.1
Stable tag: 1.3
Requires PHP: 8.0
License: GPL-2.0-or-later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

== Description ==

This plugin allows to authenticate users with Hitobito (e.g. MiData, jubla.db).
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with Hitobito"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while
new users are created in WordPress database.

Much of the documentation can be found on the Settings > Hitobito OAuth Authentication page.

Please submit issues to the Github repo: https://github.com/scout-ch/wp-hitobito-auth

== Scope ==

This plugin is intended exclusively for authentication against Hitobito (e.g. MiData, jubla.db or other Hitobito instances). For other OpenID Connect providers, please use the original OpenID Connect Generic plugin: https://github.com/oidc-wp/openid-connect-generic

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > Hitobito OAuth Authentication and configure to meet your needs

== Frequently Asked Questions ==

You will find them on:  https://docu.scout.ch/

= What is the client's Redirect URI? =

Most OAuth2 servers will require whitelisting a set of redirect URIs for security purposes. The Redirect URI provided
by this client is like so:  https://example.com/wp-admin/admin-ajax.php?action=openid-connect-authorize

Replace `example.com` with your domain name and path to WordPress.

= How can I add a custom Hitobito instance? =

This example requires a plugin version that provides the `openid-connect-generic-hitobito-instances` filter. Versions with a fixed instance list do not support this filter.

Add the following snippet to a custom plugin, an MU plugin, or your child theme's `functions.php`:

`
add_filter( 'openid-connect-generic-hitobito-instances', function ( $instances ) {
	$instances['custom_hitobito'] = array(
		'label' => 'Custom Hitobito [hitobito.example.org]',
		'url'   => 'https://hitobito.example.org/',
	);
	return $instances;
} );
`

Replace `custom_hitobito` with a unique instance key, `label` with the display name, and `url` with your Hitobito instance's HTTPS base URL, including the trailing slash. Keep the key stable once the instance is selected. Returning `$instances` preserves the existing entries.

Then select your instance under Settings > Hitobito OAuth Authentication > Hitobito URL and save the settings. Configure the OAuth client on that instance with the WordPress redirect URI described above.

= How can I override individual endpoints? =

The endpoints of the selected instance are resolved on WordPress `init`, after the `openid-connect-generic-settings` filter has run. Endpoint values changed through that filter are therefore overwritten. To override a single endpoint, define the matching constant in `wp-config.php` instead, e.g. `OIDC_ENDPOINT_LOGIN_URL`, `OIDC_ENDPOINT_TOKEN_URL`, `OIDC_ENDPOINT_USERINFO_URL`, `OIDC_ENDPOINT_LOGOUT_URL`, `OIDC_ENDPOINT_JWKS_URL` or `OIDC_ISSUER`. Defined constants are never overwritten by the instance selection.

To select the instance itself via code, define `OIDC_ENDPOINT_URL` with the instance key (e.g. `define( 'OIDC_ENDPOINT_URL', 'prod' );`).

== Changelog ==

= Unreleased =

* New settings sub page "Role Mapping" to assign WordPress roles based on the Hitobito roles of a user (scope `with_roles`). The role is updated on every login; administrators are never changed and no rule can grant administrator access.
* Users whose Hitobito roles match no rule get a configurable fallback role, or their login is denied.
* The user profile shows the Hitobito roles of the last login (group ids and role classes) to administrators.

= 1.3 =

* New settings sub page "Login Button" to configure the colors of the login button (text, background, border and icon, each for normal and hover state).
* An SVG icon (e.g. the logo of your Hitobito instance) can be uploaded and is shown in the login button.
* New filter `openid-connect-generic-hitobito-instances` to add or change Hitobito instances (#6).
* Users can be created without a nickname; the username is used as fallback (#7).
* Removed unused login button rules from the admin stylesheet.
* Documented how to override individual endpoints.

= 1.2 =

* Security release based on OpenID Connect Generic 3.11.3 (previously 3.10.0).
* ID token signatures are verified against the Hitobito JWKS (`/oauth/discovery/keys`).
* ID token claims are validated (issuer, audience, expiry).
* Login state is generated with `random_bytes()` instead of `md5( mt_rand() )`.
* Requests to the identity provider use `wp_safe_remote_*` (SSRF protection).
* "Disable SSL Verify" only takes effect in local development environments.
* Removed the `?debug` output on the settings page, which displayed all settings including the client secret.
* Retry login once for some IDP errors (Safari ITP on iOS).
* Fallback to a POST request for userinfo when GET fails.
* A corrupted log no longer causes a fatal error.
* WordPress session is no longer cut short when refresh tokens are enabled.
* Better multisite compatibility (user data stored as user options; existing users are still recognised).
* JWKS URL and issuer are set automatically from the selected Hitobito instance.
* New dependency `firebase/php-jwt` (bundled in `vendor/`, managed with Composer).
* New filter `openid-connect-generic-new-state-value`; it receives the state as `['redirect_to' => …, 'created' => …]`.
* Requires PHP 8.0 or later (PHP 7.4 is end of life).

= 1.1 =

* Login button text is now configurable in the settings (#1).
* Added "Settings" link to the plugin row on the Plugins page (#3).
* Settings page now credits the original OpenID Connect Generic plugin.
* Endpoint URLs adjusted; end session endpoint is now `oidc/logout` instead of `oauth/logout`.
* Default Hitobito instance (`endpoint_url`) set to `test` for new installations.
* Clearer error messages for an expired or invalid login session (state).
* Users are redirected back to the requested page after login again (state redirect was ignored).
* Login session (state) time limit raised from 15 to 180 seconds; existing installations are updated automatically.
* Redirect URL from the legacy redirect cookie is now validated.
* State check now stores a creation timestamp. The action `openid-connect-generic-state-not-found` was replaced by `openid-connect-generic-state-missing`; new actions `openid-connect-generic-state-invalid` and `openid-connect-generic-state-validated`.
* Restored original copyright and author notices of OpenID Connect Generic (GPLv2 compliance).
* Added Credits, Scope and License sections to readme.
* Tested up to WordPress 6.9.4, requires at least WordPress 6.7.2.

= 1.0 =

* First version for stable use with WordPress.

= 0.2 =

* For testing with MiData and jubla.db.

= 0.1 =

* Based on WordPress Plugin "OpenID Connect Generic" 3.10.0 by Jonathan Daggerhart, Tim Nolte and contributors (https://github.com/oidc-wp/openid-connect-generic).
* Customisation for Swiss Guide and Scout Movement.

== Credits ==

This plugin is a modified version of OpenID Connect Generic 3.11.3 by Jonathan Daggerhart, Tim Nolte and contributors (https://github.com/oidc-wp/openid-connect-generic), licensed under GPLv2 or later. Adapted and simplified for Hitobito by the Swiss Guide and Scout Movement (Team MiData).

Copyright (C) 2015-2023 daggerhart
Copyright (C) 2025-2026 Swiss Guide and Scout Movement
