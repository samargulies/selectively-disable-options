=== Selectively Disable Options ===

Contributors: Sam Margulies
Tags:
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 0.1
License: GPLv2 or later

Remove settings that you do not want users to changes. Useful for Wordpress Multisite.

== Description ==

As a proof-of-concept this plugin will remove the 'comment_moderation' and 'comment_registration' fields from the discussion options page. To set your own settings to remove, hook them into the `sdo_options()` function. For example, to force the week to start on Sunday and the site to be private, try:
`
function my_disabled_options( $options ) {
	$options['start_of_week'] = '0';
	$options['blog-norobots'] = '0';
	$options['comment_moderation'] = '1';
	return $options;
}
add_filter('selectively_disabled_options', 'my_disabled_options');
`

== Installation ==

You can either install it automatically from the WordPress admin, or do it manually:

1. Unzip the archive and put the 'selectively-disable-options' folder into your plugins folder (/wp-content/plugins/).
2. Activate the plugin from the Plugins menu.


== Changelog ==

= 0.1 =
Initial version