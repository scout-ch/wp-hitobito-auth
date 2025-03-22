=== MiData Authentication WordPress Plugin ===
Contributors: Team MiData
Requires at least: 5.0
Tested up to: 6.7.2
Stable tag: 3.10.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple client that provides SSO or opt-in authentication against a generic OAuth2 Server implementation.

== Description ==

This plugin allows to authenticate users with MiData.
Once installed, it can be configured to automatically authenticate users (SSO), or provide a "Login with MiData"
button on the login form. After consent has been obtained, an existing user is automatically logged into WordPress, while
new users are created in WordPress database.

Much of the documentation can be found on the Settings > MiData Connect Generic dashboard page.

Please submit issues to the Github repo: https://github.com/scout-ch/wp-hitobito-auth

== Installation ==

1. Upload to the `/wp-content/plugins/` directory
1. Activate the plugin
1. Visit Settings > MiData Connect and configure to meet your needs

== Frequently Asked Questions ==

You will find them on:  https://docu.scout.ch/

= What is the client's Redirect URI? =

Most OAuth2 servers will require whitelisting a set of redirect URIs for security purposes. The Redirect URI provided
by this client is like so:  https://example.com/wp-admin/admin-ajax.php?action=openid-connect-authorize

Replace `example.com` with your domain name and path to WordPress.