# Hitobito Auth Plugin Info
Hitobito Auth
- Contributors: Team MiData (Swiss Guide and Scout Movement); based on work by Jonathan Daggerhart, Tim Nolte and contributors
- Requires at least: 6.7.2 
- Tested up to: 6.9.4 
- Stable tag: 1.3
- Requires PHP: 8.0
- License: GPLv2 or later 
- License URI: http://www.gnu.org/licenses/gpl-2.0.html 
- Based on: https://github.com/oidc-wp/openid-connect-generic

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

## Updates

**Important!**, Hitobito Auth Plugins Updates will be released only on GitHub.
As recomendation, watch the repository and star it. More: https://docs.github.com/en/get-started/exploring-projects-on-github/saving-repositories-with-stars

## Description

This plugin allows to authenticate users with Hitobito (e.g. MiData, jubla.db).
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with Hitobito"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while
new users are created in WordPress database.

Much of the documentation can be found on the Settings > Hitobito Auth page.

Please submit issues to the Github repo: https://github.com/scout-ch/wp-hitobito-auth

## Scope

This plugin is intended exclusively for authentication against **Hitobito**
(e.g. MiData, jubla.db or other Hitobito instances). Contributions that improve
support for Hitobito-based organisations are very welcome.

If you need to connect WordPress to another OpenID Connect provider, please use the
original [OpenID Connect Generic](https://github.com/oidc-wp/openid-connect-generic) plugin.

## Installation

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > Hitobito Auth and configure to meet your needs

## Frequently Asked Questions

You will find them on:  https://docu.scout.ch/

### What is the client's Redirect URI?

Most OAuth2 servers will require whitelisting a set of redirect URIs for security purposes. The Redirect URI provided
by this client is like so:  https://example.com/wp-admin/admin-ajax.php?action=openid-connect-authorize

Replace `example.com` with your domain name and path to WordPress.

### How can I add a custom Hitobito instance?

This example requires a plugin version that provides the `openid-connect-generic-hitobito-instances` filter. Versions with a fixed instance list do not support this filter.

Add the following snippet to a custom plugin, an MU plugin, or your child theme's `functions.php`:

```php
add_filter( 'openid-connect-generic-hitobito-instances', function ( $instances ) {
	$instances['custom_hitobito'] = array(
		'label' => 'Custom Hitobito [hitobito.example.org]',
		'url'   => 'https://hitobito.example.org/',
	);
	return $instances;
} );
```

Replace `custom_hitobito` with a unique instance key, `label` with the display name, and `url` with your Hitobito instance's HTTPS base URL, including the trailing slash. Keep the key stable once the instance is selected. Returning `$instances` preserves the existing entries.

Then select your instance under Settings > Hitobito Auth > Hitobito URL and save the settings. Configure the OAuth client on that instance with the WordPress redirect URI described above.

### How can I override individual endpoints?

The endpoints of the selected instance are resolved on WordPress `init`, after the `openid-connect-generic-settings` filter has run. Endpoint values changed through that filter are therefore overwritten. To override a single endpoint, define the matching constant in `wp-config.php` instead, e.g. `OIDC_ENDPOINT_LOGIN_URL`, `OIDC_ENDPOINT_TOKEN_URL`, `OIDC_ENDPOINT_USERINFO_URL`, `OIDC_ENDPOINT_LOGOUT_URL`, `OIDC_ENDPOINT_JWKS_URL` or `OIDC_ISSUER`. Defined constants are never overwritten by the instance selection.

To select the instance itself via code, define `OIDC_ENDPOINT_URL` with the instance key (e.g. `define( 'OIDC_ENDPOINT_URL', 'prod' );`).

### How can I assign WordPress roles based on Hitobito roles?

1. Ask the administrators of your Hitobito instance to enable the scope `with_roles` for your OAuth application. Without it the login fails as soon as the role mapping is enabled.
2. Create the WordPress roles you need (e.g. with a role editor plugin).
3. Under Settings > Hitobito Auth > Role Mapping, enable the mapping and enter one rule per line:

```
# Leaders of the department
100, 101, 102 | Group::Abteilung::Abteilungsleitung, Group::Pfadi::Einheitsleitung | leiter
# Everyone else in the department
100, 101, 102 | * | eltern
```

Each rule consists of `group ids | role classes | WordPress role`. Several group ids or role classes are separated by commas, and `*` matches everything (e.g. `Group::Abteilung::*`). The rules are checked from top to bottom and the first rule matching one of the user's Hitobito roles wins, so put the rules with more rights first. Users without a matching rule get the fallback role (e.g. Subscriber), or their login is denied.

Notes:

- Hitobito only reports the group a role is directly in, not its department (layer). To match a whole department, list the department and all of its subgroups.
- Match on the role class (e.g. `Group::Abteilung::Abteilungsleitung`), not on the displayed role name, which is translated.
- After a login with Hitobito, administrators see the user's Hitobito roles with group ids and role classes on the user's profile page.
- The role is only updated when the user logs in with Hitobito. A user who leaves the organisation keeps their WordPress role until the next login.
- Administrators are never changed by the mapping, and roles with administrator capabilities cannot be assigned.

## Credits & License

This plugin is a modified version of
[OpenID Connect Generic](https://github.com/oidc-wp/openid-connect-generic) (version 3.11.3)
by Jonathan Daggerhart, Tim Nolte and contributors, licensed under GPLv2 or later.

It has been adapted and simplified for authentication against Hitobito by the
Swiss Guide and Scout Movement (Team MiData).

Copyright (C) 2015-2023 daggerhart
Copyright (C) 2025-2026 Swiss Guide and Scout Movement

This program is free software; you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software Foundation;
either version 2 of the License, or (at your option) any later version.
See [LICENSE](LICENSE) for the full license text.
