=== RW Remote Auth Client ===
Contributors: f.staude, johappel
Tags: singleSignOn, wordpress network, cloud blogging
Requires at least: 5.5
Tested up to: 5.6.2
Stable tag: 0.4.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html



== Description ==
depends on a CAS Client Plugin


== Hooks ==

= Filter =

= Actions =


== Defines ==



== Installation ==


== Frequently Asked Questions ==

= Question =

Answer


== Screenshots ==


== Changelog ==
= 0.4.0 =
* change to depencies to cas plugin Authorizer(recomended)


= 0.3.0 =
* add buddypress behavior: redirect user after login on home to last visited page
* add ability to diconnect connected buddypress groups
* add automatic check for new buddypress group members

= 0.2.12 =
* fix user add autocomplete
* fix missing user creation on login server after activation

= 0.2.11 =
* redirect frontend if no access
* disable register form, displays admins message, when link to auth-server fails

= 0.2.10 =
* fix lost password after registration

= 0.2.9 =
* fix undelete cookie

= 0.2.8 =
* add frond-end login form && register form via plugin user-registration support


= 0.2.7 =
* add user from loginserver to blog

= 0.2.6 =
* fix password overwrite on edit user profile
* disable register link, when link to auth-server fails (needs update on RPI Multi-Instanz Navigation )
* overwrite wpmu_active_signup when link to auth-server fails

= 0.2.5 =
* fix password overwrite

= 0.2.4 =
* add german language, improve usability

= 0.2.3 =
* add users from external buddypress groups (needs rw_group_blogs installed on external buddypress instance)

= 0.2.2 =
* add username validation

= 0.2.1
* add connection check
* add automatic api-key fetcher
* add error messaging from authserver in admin UI
* add several tests
* minor fixes

= 0.2.0
* add api key
* multisite settingspage
* add insert user from remote server
* fix password reset

= 0.1.14 =
* Fix: cookie delete

= 0.1.13 =
* Fix: cookie delete on non BuddyPress sites

= 0.1.12 =
* added redirect to buddypress activity slug if buddypress installed and user logins from site base url

= 0.1.11 =
* fixed: new user not login after user creation via cas client when it redirected to referrerpage instead wp-admin


= 0.1.10 =
* typo in readme
* fixes Undefined index: HTTP_REFERER ( #14 )
* check CAS Maestro bypass ( #16 )
* implement selftest ( #17 )
* optional bypass password overwrite on admin users ( #7 )
* check responses at WP_Error ( #5 )

= 0.1.9 =
* fixes problem with transmit wrong passwords and user duplicate on server

= 0.1.8 =
* internal version

= 0.1.7 =
* internal version

= 0.1.6 =
* internal version

= 0.1.5 =
* internal version

= 0.1.4 =
* internal version

= 0.1.3 =
* Added support for WordPress Plugin GitHub updater

= 0.1.2 =
* Added: save referrer on loginpage and redirct user to referrer page after login

= 0.1 =
* First version published


== Credits ==




