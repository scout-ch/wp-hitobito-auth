# Hitobito Auth Plugin Info
Hitobito Auth
- Contributors: Team MiData (Swiss Guide and Scout Movement); based on work by Jonathan Daggerhart, Tim Nolte and contributors 
- Requires at least: 6.7.2 
- Tested up to: 6.9.4 
- Stable tag: 1.2 
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
