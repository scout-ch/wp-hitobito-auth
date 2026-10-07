# MiData WordPress Plugin Changelog

**1.1**

- Feature: Login button text is now configurable in the settings (#1, thanks @Elfangor93)
- Feature: Added "Settings" link to the plugin row on the Plugins page (#3, thanks @masteradhoc)
- Feature: Settings page now credits the original OpenID Connect Generic plugin
- Fix: Endpoint URLs adjusted; end session endpoint is now `oidc/logout` instead of `oauth/logout`
- Fix: Default Hitobito instance (`endpoint_url`) set to `test` for new installations
- Improvement: Clearer error messages for an expired or invalid login session (state)
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