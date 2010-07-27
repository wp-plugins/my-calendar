=== My Calendar ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: calendar, dates, times, events, scheduling
Requires at least: 2.7
Tested up to: 3.0
Stable tag: trunk

Accessible WordPress event calendar plugin. Show events from multiple calendars on pages, in posts, or in widgets.

== Description ==

This calendar is branched from [Kieran O'Shea's Calendar plugin](http://wordpress.org/extend/plugins/calendar/). The output has been pretty much completely re-written, the settings methods have been revamped, and the widgets have been completely revamped. The information you can provide for an event has been expanded to include location information. The UI has been completely revamped.

In short, there isn't actually much left of the original plugin.

Features:

*	Monthly view of events
*	List view of events; multiple months can be viewed at once.
*	Events can display their author (optional)
*	Events can span more than one day
*	Events can include location information
*	Event listings can show address and/or a link to a Google Map with the address
*	Locations can be shown in [hCard format](http://microformats.org/wiki/hcard).
*	Multiple events per day possible
*	Events can repeat on a daily, weekly, monthly or yearly basis
*	Repeats can occur indefinitely or a limited number of times
*	Easy to use events manager
*	Widget to show today's events
*	Highly configurable widget to show upcoming events 
*	Widget templating to control what information is displayed in widget output.
*	Extensive settings panel for administration
*	Edit or disable default CSS and default JavaScript from the style editor
*	Optional drop down boxes to quickly change month and year
*	User groups other than admin can be permitted to manage events
*	Events can be placed into categories
*	Calendar can be displayed including a single category, all categories, or a selection of categories
*	Events can be links pointing to a location of your choice
*   Import method from Kieran's Calendar plugin
* 	Help information within the plugin for shortcodes and widget templates.

Languages available:

* American English (Default)
* Brazilian Portuguese ([Daniel Prata](daniel@grudaemmim.com.br))
* Spanish ([Esteban Truelsegaard](http://www.netmdp.com))

== Installation ==

1. Upload the `/my-calendar/` directory into your WordPress plugins directory.

2. Activate the plugin on your WordPress plugins page

3. Configure My Calendar using the following pages in the admin panel:

   My Calendar -> Add/Edit Events
   My Calendar -> Manage Categories
   My Calendar -> Manage Locations
   My Calendar -> Settings   
   My Calendar -> Style Editor

   
4. Edit or create a page on your blog which includes the shortcode [my_calendar] and visit
   the page you have edited or created. You should see your calendar. Visit My Calendar -> Help for assistance
   with shortcode options or widget configuration.

== Changelog ==

= 1.4.1 =

* Database upgrade didn't run for some users in 1.4.0. Added manual check and upgrade if necessary.

= 1.4.0 =

* Bug fixed: Today's Events widget was not taking internationalized time as it's argument
* Added end time field for events
* Added option for links to expire after events have occurred.
* Added options for alternate applications of category colors in output.
* Added ability to use My Calendar shortcodes in text widgets.
* Added GPS location option for locations
* Added zoom selection options for map links
* Lengthened maximum length for category and event titles
* Added a close link on opened events details boxes.
* Added an option for a mini calendar display type in shortcode
* Optimized some SQL queries and reduced total number of queries significantly.
* Extended the featured to show CSS only on certain pages to include JavaScript as well.
* Upcoming events widget only allowed up to 99 events to be shown forward or back. Changed to 999.
* Attempted to solve a problem with infinitely recurring events not appearing in upcoming events. Let me know.
* Added setting to change Previous Month/Next Month text.
* Yeah, that's enough for now.

= 1.3.8 = 

* Fixed problem with CSS editing which effectively disabled CSS unless a specific choice had been made for pages to show CSS

= 1.3.7 =

* Aren't you enjoying the daily upgrades? I made a mistake in 1.3.5 which hid text in an incorrect way, causing problems in some contexts.

= 1.3.6 =

* Fixed an issue where not having defined Pages to show CSS resulted in a PHP warning for some configs.

= 1.3.5 =

* Fix for flash of unstyled content issue.
* Added configuration for time text on events with non-specific time.
* Fixed bug where, in list views with multiple months, events occurring on days which did not exist in the previous month were not rendered. (Such as March 30th where previous month was February.)
* Fixed bug where the multi-month view setting for lists caused previous/next events buttons to skip months in calendar view.
* Added option to disable category icons.
* Added option to insert text in calendar caption/title area, appended to the month/year information.
* Fixed a bug where it was not possible to choose the "Show by days" option in the upcoming events widget.
* Updated documentation to match
* Fixed a bug where upcoming events in Days mode did not display correct date
* Added an option to define text to be displayed in place of Today's Events widget if there are no events scheduled.
* Minor changes to default CSS
* Ability to show CSS and JavaScript only on selected pages.

= 1.3.4 =

* Fixed a bug with map link and address display which I forgot to deal with in previous release.

= 1.3.3 = 

* Fixed bug with upgrade path which caused locations database to be created on every activation (also cause of errors with some other plugins). (Thanks to Steven J. Kiernan)
* Made clone object PHP 4 compatible (Thanks to Peder Lindkvist)
* Corrected errors in shortcode functions for today's events
* Corrected rendering of non-specific time events as happening at midnight in widget output

= 1.3.2 = 

* Fixed bugs with unstripped slashes in output
* Fixed a bug where users could not add location information in events if they had not added any recurring locations
* Removed requirement that address string must be five characters to display a link

= 1.3.1 = 

* Corrected incorrect primary key in upgrade path.
* Added version incrementing in upgrade path.

= 1.3.0 = 

* Fixed a CSS class which was applied to an incorrect element.
* Revisions to the Calendar import methods
* Moved style editing to its own page
* Added JavaScript editing to allow for customization of jQuery behaviors.
* Internationalized date formats
* Shortcode support for multiple categories.
* Shortcode support for custom templates in upcoming and today's events
* Added a settings option to eliminate the heading in list format display.
* Fixed a bug which treated the event repetition value as a string on event adding or updating, not allowing some users to use '0' as an event repetition.
* Made events listing sortable in admin view
* Minor revisions in admin UI.
* Added database storage for frequently used venues or event locations.
* Modified JavaScript for list display to automatically expand events scheduled for today.

= 1.2.1 = 

* Corrected a typo which broke the upcoming events widget.

= 1.2.0 = 

* Added shortcodes to support inserting upcoming events and todays events lists into page/post content.
* Added option to restrict upcoming events widgets by category
* More superficial CSS changes
* Added Brazilian Portuguese language files
* Fixed bug where I reversed the future and past variable values for upcoming events widgets
* Fixed bug in multi-user permissions.
* Added feature to look for a custom location for icons to prevent overwriting of custom icons on upgrade.

= 1.1.0 =

* Fixed some problems with Upcoming Events past events not scrolling off; hopefully all!
* Fixed some problems with fuzzy interpretations of the numbers of past/future events displayed in Upcoming Events.
* Added Bi-weekly events
* Added restrictions so that admin level users can edit any events but other users can only edit their own events
* Removed character restrictions on event titles
* Revised default stylesheet 

= 1.0.2 =

* Fixed problems with editing and deleting events or categories in multiblog installation
* Fixed escaping/character set issue
* Fixed issue when blog address and wp address did not match (introduced in 1.0.1)
* Added import method to transfer events and categories from Kieran O'Shea's Calendar plugin

= 1.0.1 =

* Added missing template code for event end dates.
* Changed defaults so that styles and javascript are initially turned on.
* Removed function collisions with Calendar
* Fixed bug where My Calendar didn't respect the timezone offset in identifying the current day.
* Fixed bug where multiblog installations in WP 3.0 were unable to save events and settings.
* Added Spanish translation, courtesy of [Esteban Truelsegaard](http://www.netmdp.com). Thanks!

= 1.0.0 =

* Initial launch.

== Frequently Asked Questions ==

= This looks terrible with my template! You suck! =

Hey, give me a break. I'm only going to release this with one default CSS - it works pretty well with Kubrick or Twenty Ten, and should be usable in many other themes. However, I'm not about to make any guarantees that it'll work with your theme. If you want it to look a particular way, you'll need to do some customization.

= Why are past events showing up in my upcoming events widget? =

The upcoming events widget has a number of options, including the choice to display any number of past or future events. The default settings allow for both, so if you only want future events to be shown you'll need to change the settings.

= I don't want to show event categories in my widgets. How can I change that? =

The widgets both use templates to determine what they'll display. You can edit those templates to show whatever you need within your list of events. The available shortcodes can be found on the plugin's Help page.



== Screenshots ==

1. Calendar using calendar list format.
2. Calendar using monthly calendar format.
3. Event management page
4. Category management page
5. Settings page

== Upgrade Notice ==