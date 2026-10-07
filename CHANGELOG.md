# MiData WordPress Plugin Changelog

**1.2**

**SECURITY RELEASE** – based on OpenID Connect Generic 3.11.3 (previously 3.10.0)

- Security: ID token signatures are now verified against the Hitobito JWKS (`/oauth/discovery/keys`)
- Security: ID token claims are validated (issuer, audience, expiry)
- Security: Login state is generated with `random_bytes()` instead of `md5( mt_rand() )`
- Security: Requests to the identity provider use `wp_safe_remote_*` (SSRF protection)
- Security: "Disable SSL Verify" only takes effect in local development environments
- Security: Removed the `?debug` output on the settings page, which displayed all settings including the client secret
- Fix: Retry login once for some IDP errors (Safari ITP on iOS)
- Fix: Fallback to a POST request for userinfo when GET fails
- Fix: A corrupted log no longer causes a fatal error
- Fix: WordPress session is no longer cut short when refresh tokens are enabled
- Improvement: Better multisite compatibility (user data stored as user options; existing users are still recognised)
- Developer: JWKS URL and issuer are set automatically from the selected Hitobito instance
- Developer: New dependency `firebase/php-jwt` (bundled in `vendor/`, managed with Composer)
- Developer: New filter `openid-connect-generic-new-state-value`; it receives the state as `['redirect_to' => …, 'created' => …]`
- Chore: Requires PHP 8.0 or later (PHP 7.4 is end of life)

**1.1**

- Feature: Login button text is now configurable in the settings (#1, thanks @Elfangor93)
- Feature: Added "Settings" link to the plugin row on the Plugins page (#3, thanks @masteradhoc)
- Feature: Settings page now credits the original OpenID Connect Generic plugin
- Fix: Endpoint URLs adjusted; end session endpoint is now `oidc/logout` instead of `oauth/logout`
- Fix: Default Hitobito instance (`endpoint_url`) set to `test` for new installations
- Improvement: Clearer error messages for an expired or invalid login session (state)
- Fix: Users are redirected back to the requested page after login again (state redirect was ignored)
- Fix: Login session (state) time limit raised from 15 to 180 seconds; existing installations are updated automatically
- Security: Redirect URL from the legacy redirect cookie is now validated
- Developer: State check now stores a creation timestamp. The action `openid-connect-generic-state-not-found` was replaced by `openid-connect-generic-state-missing`; new actions `openid-connect-generic-state-invalid` and `openid-connect-generic-state-validated`
- Chore: Restored original copyright and author notices of OpenID Connect Generic (GPLv2 compliance)
- Chore: Added Credits, Scope and License sections to readme
- Chore: Added LICENSE file (GPLv2)
- Chore: Tested up to WordPress 6.9.4, requires at least WordPress 6.7.2
- Chore: Regenerated translation template (.pot)

 **1.0**
 
 First version for stabel use with WordPress
 
 **0.2**
 
 For testing with MiData and jubla.db.

**0.1**

Based on WordPress Plugin "OpenID Connect Generic" 3.10.0 by Jonathan Daggerhart, Tim Nolte and contributors (https://github.com/oidc-wp/openid-connect-generic)
Customisation for Swiss Guide and Scout Movement