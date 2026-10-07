=== Hitobito Authentication WordPress Plugin ===
Contributors: Team MiData
Requires at least: 6.7.2
Tested up to: 6.7.2
Stable tag: 1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

== Description ==

This plugin allows to authenticate users with Hitobito (e.g. MiData, jubla.db).
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with Hitobito"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while
new users are created in WordPress database.

Much of the documentation can be found on the Settings > Hitobito Connect Generic dashboard page.

Please submit issues to the Github repo: https://github.com/scout-ch/wp-hitobito-auth

== Scope ==

This plugin is intended exclusively for authentication against Hitobito (e.g. MiData, jubla.db or other Hitobito instances). For other OpenID Connect providers, please use the original OpenID Connect Generic plugin: https://github.com/oidc-wp/openid-connect-generic

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > Hitobito Connect and configure to meet your needs

== Frequently Asked Questions ==

You will find them on:  https://docu.scout.ch/

= What is the client's Redirect URI? =

Most OAuth2 servers will require whitelisting a set of redirect URIs for security purposes. The Redirect URI provided
by this client is like so:  https://example.com/wp-admin/admin-ajax.php?action=openid-connect-authorize

Replace `example.com` with your domain name and path to WordPress.

== Credits ==

This plugin is a modified version of OpenID Connect Generic 3.10.0 by Jonathan Daggerhart, Tim Nolte and contributors (https://github.com/oidc-wp/openid-connect-generic), licensed under GPLv2 or later. Adapted and simplified for Hitobito by the Swiss Guide and Scout Movement (Team MiData).

Copyright (C) 2015-2023 daggerhart
Copyright (C) 2025 Swiss Guide and Scout Movement
