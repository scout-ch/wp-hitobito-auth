=== Hitobito Authentication WordPress Plugin ===
Contributors: Team MiData
Requires at least: 6.7.2
Tested up to: 6.9.4
Stable tag: 1.3
Requires PHP: 8.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

== Description ==

This plugin allows to authenticate users with Hitobito (e.g. MiData, jubla.db).
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with Hitobito"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while
new users are created in WordPress database.

Much of the documentation can be found on the Settings > Hitobito Auth page.

Please submit issues to the Github repo: https://github.com/scout-ch/wp-hitobito-auth

== Scope ==

This plugin is intended exclusively for authentication against Hitobito (e.g. MiData, jubla.db or other Hitobito instances). For other OpenID Connect providers, please use the original OpenID Connect Generic plugin: https://github.com/oidc-wp/openid-connect-generic

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > Hitobito Auth and configure to meet your needs

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

Then select your instance under Settings > Hitobito Auth > Hitobito URL and save the settings. Configure the OAuth client on that instance with the WordPress redirect URI described above.

= How can I override individual endpoints? =

The endpoints of the selected instance are resolved on WordPress `init`, after the `openid-connect-generic-settings` filter has run. Endpoint values changed through that filter are therefore overwritten. To override a single endpoint, define the matching constant in `wp-config.php` instead, e.g. `OIDC_ENDPOINT_LOGIN_URL`, `OIDC_ENDPOINT_TOKEN_URL`, `OIDC_ENDPOINT_USERINFO_URL`, `OIDC_ENDPOINT_LOGOUT_URL`, `OIDC_ENDPOINT_JWKS_URL` or `OIDC_ISSUER`. Defined constants are never overwritten by the instance selection.

To select the instance itself via code, define `OIDC_ENDPOINT_URL` with the instance key (e.g. `define( 'OIDC_ENDPOINT_URL', 'prod' );`).

== Credits ==

This plugin is a modified version of OpenID Connect Generic 3.11.3 by Jonathan Daggerhart, Tim Nolte and contributors (https://github.com/oidc-wp/openid-connect-generic), licensed under GPLv2 or later. Adapted and simplified for Hitobito by the Swiss Guide and Scout Movement (Team MiData).

Copyright (C) 2015-2023 daggerhart
Copyright (C) 2025-2026 Swiss Guide and Scout Movement
