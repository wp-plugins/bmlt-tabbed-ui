=== BMLT Tabbed UI ===
Contributors: Jack
Tags: na, meeting list, meeting finder, maps, recovery, addiction, webservant, bmlt
Requires at least: 3.6
Tested up to: 3.6
Stable tag: 4.0
BMLT Tabbed UI implements a jQuery tabbed UI for BMLT.
== Description ==
This plugin provides a jQuery Tabbed UI for the Basic Meeting List Toolbox (BMLT).  You must have BMLT installed and running.  Simply put the shortcode into a Wordpress page to get your very own tabbed interface to BMLT.  Not into a tabbed interface?  There is a template for displaying meetings in a table without tabs.  This plugin also provides various shortcodes to return the number of meetings and groups in specified service bodies.  Please visit settings - BMLT Tabbed UI for shortcode instructions.
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
= 4.0 =* Added required field for BMLT server in settings - BMLT Tabs.  BMLT Tabs can now be used on any BMLT server.* Added header above weekday tabs giving the ability to display meetings by weekday, city, group or location.  Default is with the header.* Added shortcode parameter "has_tabs" to allow meetings to be listed in a table instead of tabs.  This would be beneficial for service bodies with fewer groups. Default is with tabs.* Added shortcode parameter "header" to allow removal of the drop-downs.  This will be helpful for backward compatibility.* Added button for pop-up dialogue of meeting formats legend.* Removed template support for now.  Using shortcode parameters instead.= 3.4 =* Fixed margin-top for format table.= 3.3 =* Fixed margin-top for meeting list table.* Added missing help text.= 3.2 =* Added new template.  There are now 3 templates.* Removed unnecessary styles and styles that over-wrote theme styles.= 3.1 =* Added some missing help text.= 3.0 =* Added shortcode parameter to display meeting in a table.* Changed code to support additional templates in the future.* Changed method in which meetings are fetched from the server to a more efficient JSON query.* Removed unnecessary jquery scripts making code more efficient.= 2.0 =* Added the ability to include multiple service bodies in the tabbed UI list of meetings.
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