=== BMLT Tabbed UI ===

Contributors: Jack

Tags: na, meeting list, meeting finder, maps, recovery, addiction, webservant, bmlt

Requires at least: 2.6

Tested up to: 3.5.1

Stable tag: 2.0

BMLT Tabbed UI implements a jQuery tabbed UI for BMLT.

== Description ==

This plugin provides a jQuery tabbed UI for the Basic Meeting List Toolbox (BMLT).  You must have BMLT installed and running.  Simply put the shortcode into a Wordpress page to get your very own tabbed interface to BMLT.  This plugin also provides a shortcode to return the number of meetings in specified service bodies.

== Installation ==

1. Place the 'bmlt-tabbed-ui' folder in your '/wp-content/plugins/' directory.

2. Activate bmlt-tabbed-ui.

3. Enter shortcode into a new or existing Wordpress page.

4. For shortcode usage see Settings - BMLT Tabs.

5. View your site.

6. Adjust the CSS of your theme as needed.

== Screenshots ==

<a href="http://orlandona.org/meetings/">Go to this Web page to get an idea of how this works.</a>

== Changelog ==

= 2.0 =

* Added the ability to include multiple service bodies in the tabbed UI list of meetings.

* Added the ability to include meetings from parent service bodies from the BMLT database in the tabbed UI list of meetings.

* Added a new shortcode [bmlt_count] to return the number of meetings in a specific service body, muliple service bodies or all meetings.

* Added a feature in which the tabbed UI interface defaults to the current day of week.

* Changed the theme for the tabbed UI user interface.

= 1.5 =

* Fixed accordian effect on slower connections by initial setting of display:none in class css-panes.

= 1.4 =

* Left out jquery dependency for tabs.js on 1.3

= 1.3 =

* Removed jquery.tools.min.js (which was being loaded from maxcdn which keeps going down)

* Added tabs.js to plugin directory (which is a component of jquery.tools library)

= 1.2 =

* Fixed URI on the screenshots page

* Changed Plugin URI to Wordpress plugin directory

= 1.1 =

* Complete rewrite of plugin to conform with Wordpress standards

= 1.0 =

* Released on January 20, 2012