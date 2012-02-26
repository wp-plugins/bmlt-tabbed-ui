=== BMLT Tabbed UI ===
Contributors: Jack
Tags: na, meeting list, meeting finder, maps, recovery, addiction, webservant, bmlt
Requires at least: 2.6
Tested up to: 3.3.1
Stable tag: 1.3

BMLT Tabbed UI implements a jQuery tabbed UI for BMLT.

== Description ==

This plugin provides a jQuery tabbed UI for the Basic Meeting List Toolbox (BMLT).  You must have BMLT installed and running.  Just put the shortcode into a Wordpress page to get your very own tabbed interface to BMLT.  There are no settings as of yet.

== Installation ==

1. Place the 'bmlt-tabbed-ui' folder in your '/wp-content/plugins/' directory.
2. Activate bmlt-tabbed-ui.
3. Create or edit a wordpress page.
4. Enter shortcode into a new or existing page.

Shortcode usage

[bmlt_tabs service_body="1"]

service body = service body ID

If you don't know your service body ID, ask your BMLT administrator.

5. View your site.
6. Adjust the CSS of your theme as needed.

== Screenshots ==

<a href="http://orlandona.org/meetings/">Go to this Web page to get an idea of how this works.</a>

== Changelog ==

= 1.3 =
* removed jquery.tools.min.js (which was being loaded from maxcdn which keeps going down)
* added tabs.js to plugin directory (which is a component of jquery.tools library)

= 1.2 =
* Fixed URI on the screenshots page
* Changed Plugin URI to Wordpress plugin directory

= 1.1 =
* Complete rewrite of plugin to conform with Wordpress standards

= 1.0 =
* Released on January 20, 2012