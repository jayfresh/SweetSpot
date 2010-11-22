=== MapPress Easy Google Maps ===
Contributors: chrisvrichardson
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4339298
Tags: google maps,google,map,maps,easy,poi,mapping,mapper,gps,lat,lon,latitude,longitude
Requires at least: 2.7
Tested up to: 3.0
Stable tag: 1.6.1

MapPress is the easiest way to create great-looking Google Maps and driving directions in your blog.

== Description ==

MapPress adds an interactive map to the wordpress editing screens.  When editing a post or page just enter any addresses you'd like to map.

The plugin will automatically insert a great-looking interactive map into your blog. Your readers can get directions right in your blog and you can even create custom HTML for the map markers (including pictures, links, etc.)!

* What features would you like to see next? [Take the Poll](http://www.wphostreviews.com/mappress).
* For questions and suggestions: [contact me](http://wphostreviews.com/chris-contact) using the web form or email me (chrisvrichardson@gmail.com)

= News =
* The 'GoogleBar' feature is deprecated (the bar that says search the map).  If you need it, let me know.
* Added FAQ for fixing issues where map won't display

= Key Features =
* Easily create maps right in the standard post edit and page edit screens
* Add markers for any address, place or latitude/longitude location.
* Create your own custom text and HTML for the markers, including photos, links, etc.
* Your readers can zoom the maps, scroll them, and get driving directions right on your blog
* A full set of map controls and map types (street, terrain, satellite, or hybrid) are available

= NEW! =
* WYSIWYG map preview during editing
* Edit map markers using full HTML - embed photos, links, etc. into your markers!
* Draggable markers
* Enter markers by address or by latitude/longitude
* Automatic address correction
* Javascript is loaded only on pages that have a map
* Option to turn off map zooming with the mousewheel
* And much more (see the release notes)


**[Download now!](http://www.wphostreviews.com/mappress)**

[Home Page](http://www.wphostreviews.com/mappress) |
[Documentation](http://www.wphostreviews.com/mappress-documentation-144) |
[FAQ](http://www.wphostreviews.com/mappress-faq) |
[Support](http://www.wphostreviews.com/mappress-faq)

== Screenshots ==
1. Options screen
2. Visual map editor in posts and pages
3. Edit map markers in the post editor
4. Get directions from any map marker
5. Inline directions are displayed right in your blog

= Localization =
Please [Contact me](http://wphostreviews.com/chris-contact) if you'd like to provide a translation or an update.  Special thanks to:

* Finnish - Jaska K.
* German - Stefan S. and Stevie
* Dutch	- Wouter K.
* Chinese / Taiwanese - Y.Chen
* Simplified Chinese - Yiwei
* Swedish - Mikael N.

== Installation ==

See full [installation intructions and Documentation](http://www.wphostreviews.com/mappress-documentation-144)

1. Unzip into a directory in `/wp-content/plugins/`, for example `/wp-content/plugins/mappress-google-maps-for-wordpress.zip`.  Be sure to put all of the files in this directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Google Maps API key and other options using the the 'MapPress' menu - it's right under the standard 'Settings' menu.
1. That's it - now you'll see a MapPress meta box in in the 'edit posts' screen.  You can use it to add maps to your posts just by entering the address to display and an (optional) comment for that address.

== Frequently Asked Questions ==

Please read the **[FAQ](http://www.wphostreviews.com/mappress-faq)**

== Screenshots ==

1. Options screen
2. Visual map editor in posts and pages
3. Edit map markers in the post editor
4. Get directions from any map marker
5. Inline directions are displayed right in your blog

== Changelog ==
1.6.1
=
* Removed the GoogleBar, it's just wasting space on the map.  Tell me if you need this option restored!
* Removed the automatic centering checkbox in post edit screen.  Click the 'center map' button to center instead.
* Added donate links

1.6
=
* Fixed plugin URL retrieval, localization and warnings when running in WP_DEBUG
* Default GoogleBar to off for new installations


1.5 - New Features
=
* SPEED!  Javascript is now compressed and loads ONLY on pages with a map
* You can now edit marker titles and body using full HTML!  Look for a visual editor in the next version...
* Removed 'directions' tabs and went back to links.  This just seems simpler to me, but let me know if you object.
* You can now add markers by lat/lng.  Use the fields to precisely enter the location or, if you want the marker to 'snap' to the nearest street address, then enter the lat/lng in the address field instead, e.g. "-35.03, -32.001"
* Directions link display has been enhanced
* Added option for address correction.  If you choose 'as entered' the addresses will appear just as you enter them.  Choose 'corrected' to display a corrected version from Google.  For example, the corrected version of "1 infinite loop" is "1 Infinite Loop, Cupertino, CA, 95014, USA".  Look for more correction options in the next version...
* Internationalization has been improved and all visible texts should now be available for translation
* Changed 'caption' to 'title'.  If you have CSS assigned to class "mapp-overlay-caption" please change it to "mapp-overlay-title"
* By default, MapPress will zoom your map to show all markers when you save the map.  If you don't want this function, you can set a checkbox to manually set the map center and zoom
* You can set the map type (hybrid, street, etc.) by selecting it in the post-edit or page-edit screen.  Whatever you select is what will be displayed. * Markers are now draggable during editing
* Option added to force map language - this is useful if, for example, your blog is in Spanish but many your readers have their browsers defaulted to English.  Set the option to force Google to display all map controls in that language.
* Option added to turn mouse wheel scrolling on/off
* We now have WYSIWYG map preview during post editing - map shows exactly as it'll appear in your blog
* When requesting directions, MapPress will replace invalid directions with the nearest match if it's an obvious match.  For example "1 infnte loop, coopertino" will be replaed with "1 Infinite Loop, Cupertino, CA".
* For less obvious matches, MapPress will provide "did you mean: " links.  For example, entering "ab" will result in a link "did you mean: Alberta, Canada""
1.5.8.x
=
* Bug fixes that prevented map display!
* Added additional debugging information
* Fixed: bug where json_encode fails in older versions of php
1.5.1 - 1.5.7
=
* Fixed: bug where foreign characters, accents or single quotes could prevent map display
* Fixed: when editing an infowindow after editing the page/post text a message "are you sure you want to navigate away..." would appear
* Fixed: when editing an infowindow in IE8 the cursor position jumped around (this is actually an IE8 bug, but I've implemented a workaround)
* Fixed: CSS issues for Firefox/IE7 display
1.4.2
=
* Additional fixes to support PHP 4

1.4.1
=
* Added internationalization; language files are in the 'languages' directory

1.4
=
* Added PHP 4 support
* New minimap in post edit

1.3.2
=
* Easy entry of multiple addresses
* Multiple marker icons
* Address checking and correction
* Edit maps without changing shortcodes
* High-speed geocoding with 500% faster map save and display

1.2.4
=
* Added GoogleBar feature
* Improved CSS to prevent issues with some custom themes

1.2.2 (2009-04-03) and 1.2.3
=
* Added JSON library for PHP4
* Fixed naming error when plugin extracted to wrong directory

1.2.1 (2009-04-02)
=
* removed '%s' text from options screen
* enhanced check to suppress mappress javascript on other admin pages
* fixed multiple messages when api key invalid

1.2 (2009-04-01)
=
* Added support for multiple markers
* Easier driving directions

1.1 (2009-03-15)
=
* Several bug fixes; adjusted map zoom

1.0 (2009-02-01)
=
* Initial version