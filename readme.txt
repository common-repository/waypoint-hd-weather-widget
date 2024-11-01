=== Plugin Name ===
Contributors: johngrefe, JamesSteward
Tags: widgets, sidebar, shortcode, openweathermap, weather, weather widget, forecast, global, HD, retina, responsive, HD Weather Widget
Requires at least: 3.5
Tested up to: 3.7
Stable 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Waypoint HD Weather Widget, HD weather, that changes with the outside conditions, over 49 Different "Feels"

== Description ==

Hacking: John Grefe - <a href="http://www.twitter.com/johngrefe">Twitter</a>  <a href="https://plus.google.com/113242995329771585932?rel=author">Google +</a>  
Design: James Steward - <a href="http://www.twitter.com/james_steward">Twitter</a><a href="https://plus.google.com/109941544607118893300?rel=author"> Google +</a>

Group: <a href="http://www.thewaypoint.com">The Waypoint</a> - http://consultants.thewaypoint.com

This plugin uses HD 331dpi images, in beautiful responsive cirlces, to display weather for your location. <strong> Images included in this package now. </strong>

The plugin parses data from the openweather api to display information.  The HD background image changes, based on the 'condition' outside, better named here, the "Feels".  We have provided one Feel in the initial v.1. Use the documentation to xref the api condition statements, against file names.  The images get loaded via css, so they don't interfere with layered objects on the site.

Use the built in widget or add it somewhere else with this shortcode: (Best settings shown)

`[waypoint-weather location="Long Beach" units="F" size="tall" override_title="MTL" forecast_days=hide hide_stats=false]`

---------------------------------------------------

"Hej Dude! I am like totally metric n' stuff, but its not working on the back end."

jajaja, Shortcodes are breaking the build.  You'll want to do this.

`[waypoint-weather location="Oslow" units="C" size="tall" override_title="The Waypoint" forecast_days=hide hide_stats=false]`

--------------------------------------------------

Settings:

*   Location: Enter like Long Beach, CA or just Los Angeles. You may need to try different variations to get the right city.  Don't use zip/postal codes, the API hates them.
*   Units: F (default) or C
*   Size: wide (default) or tall, You should use Tall, girls love tall guys, so does this style.
*   Override Title: Change the title in the header bar to whatever, sometimes it pulls weather from a close city.
*   Forecast Days: Don't use this unless you want the plugin to be ugly.
*   Hide stats: Hide the text stats like humidity, wind, high and lows, etc.
*   Background: You probably don't want this as you want the css to load the dynamic images.

All weather data is provided by http://openweathermap.org and is cached for one hour.


== Installation ==

1. Add plugin to the `/wp-content/plugins/` directory (Don't use Upload in WP admin, it sucks dude.)
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use shortcode or widget.


== Screenshots ==

1. Basic tall layout

== Release History ==

= 1.0 = 
Updated with all images in Repo, this should just chain install from your wp-admin section now, otherwise, always safer to upload as a zip.  It is a beast ~26mg.

= .1 =
First release for proof.  

Lookup the condition code that the plugin is displaying while running such as "Clear Skys" here, then change file name to that code, ex "800.png". http://bugs.openweathermap.org/projects/api/wiki/Weather_Condition_Codes

== Author Ship Slug ==
https://plus.google.com/113242995329771585932?rel=author

Fork this!  'git@github.com:johngrefe/waypoint-hd-weather-widget.git' (Real code is hosted on github anyways, who knows, you'll probably find a better build sooner there!)


 
