=== My Calendar ===
Contributors: joedolson
Donate link: http://www.joedolson.com/donate.php
Tags: calendar, dates, times, event, events, scheduling, schedule, event manager, event calendar, class, concert, conference, meeting, venue, location, box office, tickets, registration
Requires at least: 3.9.8
Tested up to: 4.3.0
Stable tag: 2.4.8
License: GPLv2 or later

Accessible WordPress event calendar plugin. Show events from multiple calendars on pages, in posts, or in widgets.

== Description ==

My Calendar does WordPress event management with richly customizable ways to display events. The plug-in supports individual event calendars within WordPress Multisite, multiple calendars displayed by categories, locations or author, or simple lists of upcoming events. 

Easy to use for anybody, My Calendar provides enormous flexibility for designers and developers needing a custom calendar.

* 	[Buy the User's Guide](http://www.joedolson.com/my-calendar/users-guide/) for extensive help with set up and use.
*   [Buy My Calendar Pro](https://www.joedolson.com/my-calendar/pro/), the premium extension for My Calendar
*   [Use My Tickets](https://wordpress.org/plugins/my-tickets/) and sell tickets to your My Calendar events

= Features: =

*	Standard calendar grid and list views of events
* 	Show events in monthly, weekly, or daily view.
* 	Mini-calendar view for compact displays (as widget or as shortcode)
*	Widgets: today's events, upcoming events, compact calendar, event search
*	Custom templates for event output
*	Limit views by categories, location, author, or host
*	Disable default CSS and default JavaScript or display only on specific Pages/Posts
*	Editable CSS styles and JavaScript behaviors
*	Schedule a wide variety of recurring events.
*	Edit individual occurrences of recurring events
* 	Rich permissions handling to restrict access to parts of My Calendar
* 	Email notification to administrator when events are scheduled or reserved
* 	Post to Twitter when events are created. (with [WP to Twitter](http://wordpress.org/extend/plugins/wp-to-twitter/))
*	Location Manager for frequently used venues
*   Fetch events from a remote MySQL database. (Sharing events in a network of sites.)
*   Import events from [Kieran O'Shea's Calendar plugin](http://wordpress.org/extend/plugins/calendar/)
* 	Integrated Help page to guide in use of shortcodes and template tags
* 	Shortcode Generator to help create customized views of My Calendar
*   [Developer Documentation](http://www.joedolson.com/doc-category/my-calendar-3/)

= What's in My Calendar Pro? =

* Let your site visitors submit events to your site (pay to post or free!).
* Let logged-in users edit their events from the front-end.
* Create events when you publish a blog post
* Publish a blog post when you create an event
* Advanced search features

= Translations =

Available translations (in order of completeness):
Czech, Polish, Portuguese (Portugal), Spanish (Spain), French, Danish, Swedish, Japanese, Dutch, German, Slovak, Russian, Italian, Hebrew, Galician, Portuguese (Brazil), Hindi, Turkish, Finnish, Slovenian, Ukrainian, Romanian, Norwegian (Bokmal), Catalan, Hungarian, Afrikaans, Persian, Icelandic

Visit the [My Calendar translations site](http://translate.joedolson.com/projects/my-calendar) to check the progress of a translation.

Translating my plug-ins is always appreciated. Visit <a href="http://translate.joedolson.com">my translations site</a> to start getting your language into shape!

<a href="http://www.joedolson.com/translator-credits/">Translator Credits</a>

== Installation ==

1. Upload the `/my-calendar/` directory into your WordPress plugins directory.

2. Activate the plugin on your WordPress plugins page 

3. Configure My Calendar using the settings pages in the admin panel:

   My Calendar -> Manage Events
   My Calendar -> Add New Event
   My Calendar -> Manage Categories
   My Calendar -> Manage Locations
   My Calendar -> Manage Event Groups
   My Calendar -> Style Editor   
   My Calendar -> Script Manager
   My Calendar -> Template Editor
   My Calendar -> Settings
   My Calendar -> Help
   
4. Edit or create a page on your blog which includes the shortcode [my_calendar] and visit
   the page you have edited or created. You should see your calendar. Visit My Calendar -> Help for assistance
   with shortcode options or widget configuration.

== Changelog ==

= Future =

* Make annual view in list mode configurable on a calendar-specific basis rather than globally; effect date switcher; see https://www.joedolson.com/forums/topic/annual-calendar/#post-5427
* Refactor options storage
* Update event taxonomies if category changed/source event taxonomy data from post
* Custom link targets using mc_customize_details_link & template_redirect filter as pointer.
* Handle stylesheet editing as additive (child styles), rather than editing the original stylesheet.
* Update pickadate to version 3.6, when it's out. 3.5.6 has a regression that makes it useless for me.

= 2.4.9 =

* Bug fix: Make iCal support elimination of holiday collisions

= 2.4.8 =

* Bug fix: Md5 hash on arguments includes format & timeframe, so switching between options broke CID references
* Bug fix: clear undefined index notice occurring only under specific unusual server configurations

= 2.4.7 =

* Update Italian translation
* Bug fix: Ensure that mini calendar widgets have unique IDs
* Eliminate an obsolete variable.

= 2.4.6 =

* Bug fix: I just can't stop making stupid mistakes in print view. Sheesh.

= 2.4.5 =

* Mislabeled form field on date switcher.
* Add primary sort filter to main event function [props @ryanschweitzer]
* New filters on navigation tools.
* Bug fix: Print view loaded when iCal requested [broken in 2.4.4]
* Bug fix: Changes to Upcoming Events widget to better limit upcoming events lists.
* Language updates: Czech, Swedish, Finnish

= 2.4.4 =

* Bug fix: Stray character return in Print view
* Bug fix: Print view did not respect date changes
* Bug fix: Logic error in sort direction switching in admin when setting not configured
* Change: Print view no longer driven by feed API.
* Change: Added option to disable "More" link from settings

= 2.4.3 =

* Bug fix: reversed filter name/value pairing in SQL query.

= 2.4.2 =

* Bug fix: in Upcoming Events shortcode (mismatch between documentation & reality).

= 2.4.1 =

* Bug fix: Missing style in print.css
* Bug fix: Broken <head> in print view.

= 2.4.0 =

New features:

* Set upcoming event class based on time, rather than date.
* Add past/present classes to today's events widget
* Assign Custom All Day label for each event.
* Support hiding 'Host' field as option.
* Made primary sort order of events filterable: 'mc_primary_sort'
* Added action to location saving handling updated locations
* Added arguments to from/to filters in Upcoming Events
* Enabled option to turn on permalinks
* Custom canonical URL for event pages
* Added 'date' parameter to today's events list & shortcode accepting any string usable in strtotime()
* Added 'from' and 'to' parameter to upcoming events list & shortcode accepting any string usable in strtotime
* Added year/month/day parameter to main shortcode to target specific months for initial display.
* Make BCC field filterable
* Add filters to search query parameters
* New option: switch to mini calendar on mobile devices instead of list view.
* Add [day] select field to date switcher if in 'day' view.
* Option to set default sort direction
* Ability to set three separate event title templates: grid, list, and single. 
* Added admin-bar link to view calendar.
* Added option to customize permalink slug on permalink page
* Single event pages as permalinks use the same template as main if custom template isn't enabled.
* New template tag: {color_css} and {close_color_css} to wrap a block with the category background color.
* Add category classes to Upcoming & Today's events widgets
* Redirect page to main calendar if event is private
* Improved labeling of cell dates

Bug fixes:

* Stop setting all day events to end at midnight; use 11:59:59 and filter output
* Rewrite iCal output so that the iCal download eliminates Holiday cancellations [todo]
* Bug fix: Prevent extraneous variables from leaking into the navigation output.
* Rendering post template in permalinks only applies within Loop.
* Template attribute preg_match could only pick up 2 parameters
* Prevent an invalid mc_id value from returning errors.
* Prevent deprecation notice when getting text_direction
* Default to not showing navigation options in print view.
* Better loading of text domain.
* Prevent mini calendar from switching to list format.
* Change class construction to PHP 5 syntax
* Close button is now a button rather than a link.
* Fixed display of text diff for stylesheet comparisons
* Two different filters with different names.
* mc_after_event filter not running with custom templates.
* With My Tickets active, enter key did not submit Add/Edit event form
* Fixed documentation error with ical template tags.
* Improved efficiency of WP shortcode processing in templates.
* A multi-day event crossing the current day was counted as a future event in upcoming events
* If event instance was split from recurring event, showed same recurring settings as original event.
* If events were mass deleted, the corresponding event post was not also deleted.
* Prevent single event pages from displaying content if the event is in a private category.

Important Changes:

* Removed references to #jd_calendar and generate custom IDs. [breaking change
* Revision of settings page [reorganize settings into tabs]
* Reorganized settings pages.

Other:

* Moved changelog for versions prior to 2.3.0 into changelog.txt

Translations:

* Updated Polish, Portuguese (Portugal), Dutch, Turkish, Slovak, Norwegian, Hungarian, German, Spanish, Persian, Czech, Danish

= 2.3.32 =

* Bug fix: end time for events auto-toggled to midnight, instead of +1 hour when end time omitted.

= 2.3.31 =

* Added escaping in 2.3.30 broke location & category limits (escape placed on wrong string.)

= 2.3.30 =

* Security Fix: Arbitrary File Override
* Security Fix: Reflected XSS
* Thanks for Tim Coen for responsibly disclosing these issues.
* All issues apply for authenticated users with access to My Calendar settings pages.
* Language updates: Updated Polish, Swedish, Galician, Czech, Norwegian, Italian
* Added Slovak, Icelandic, Hebrew

= 2.3.29 =

* Security Fix: XSS issue applying to improper use of add_query_arg(). See https://yoast.com/tools/wrong-use-of-add_query_arg-and-remove_query_arg-causing-xss/

= 2.3.28 =

* Bug fix: Problem saving My Calendar URI if My Calendar is intended for use behind a secured location.
* Update languages: French, German, Catalan

= 2.3.27 =

* Bug fix: Things that happen when you failed to write down a minor change - you don't test it. Couldn't choose a preset location when creating an event in 2.3.26. 

= 2.3.26 =

* Typo in aria-labelledby.
* Bug fix: fatal error if wp_remote returns WP_error.
* Bug fix: could not set calendar URI if site is password protected.
* Bug fix: category key fetched icons using a different path generation than main calendar that could result in a broken link.
* Bug fix: ensure that all image template tags exist in the array, even if the event post does not exist.
* Bug fix: make print view respect current category/location filters
* Bug fix: make iCal download respect current category/location filters
* Added class on event data container for root ID of events.
* Added 'current' class for currently selected category in category key if category filter applied.

= 2.3.25 =

* Bug fix: Escape URL for search form request URL
* Bug fix in check whether event had a valid post container.
* Bug fix to handle problem with weeks calculation on the first of the month.
* Bug fix: Display problem in single-page event view in twentyfifteen.css
* Bug fix: If My Calendar URL is invalid, re-check when settings page is loaded.
* Bug fix: Don't display update notice on new installs.
* Change: My Calendar automatically generates calendar page on installation.
* Change to Upcoming Events default template to make usage more robust.
* Change: mc-mini JS to auto close all open panels if a new one is opened.
* Rearrange a few settings for better usability.
* Added ability to use Upcoming Events widget to show the nth future month. (e.g., show events for the 6th month out from today.)
* Deprecated upgrade cycles prior to version 1.11.0.
* Improve accessibility of tab panels used in My Calendar UI.
* Language updates: Updated Russian, Added Afrikaans

= 2.3.24 =

* Bug fix: In mini widget, date is not displayed if only event on date is private
* Bug fix: Improved fix to year rendering (roughly fixed in 2.3.23)
* Bug fix: Improved rendering of structured event data.
* Bug fix: [my_calendar_now] incorrectly checked the current time.
* Bug fix: "Archive" link pointed to wrong location in event manager.
* Bug fix: Was no way to reverse archiving an event; added method
* Bug fix: Shortcode generator produced incorrect Upcoming Events shortcode.
* Bug fix: Overlapping occurrences warning inappropriately showed on events recurring on a month by day basis
* Bug fix: If only event on date is private, don't add class 'has-events'
* Bug fix: Save default values for top/bottom nav on install.
* Bug fix: Restore default template array when plug-in is deleted and re-installed
* Minor style change to twentyfourteen.css
* New default theme: twentyfifteen.css
* Feature add: AJAX control to delete individual instances of a recurring event from the event editor.
* Feature change: Events post type content filter now replaces content instead of repeating. Use 'mc_event_content' filter to override.
* Improvement: Show overlapping occurrences warnings in manage events view.
* Improvement: List/Grid button only shows on month and week views. 
* Misc. UI improvements.
* Performance fix: Hide overlapping recurring events on front-end. (They can consume massive amounts of memory.)
* Language updates: French, Spanish, Japanese, Dutch, German, Ukrainian, Swedish

ISSUE: What's causing templates to not be set?

= 2.3.23 =

* Bug fix: Calendar rendering 2014 at beginning of 2015.
* Bug fix: Set Holiday category when adding new categories.
* Bug fix: Search widget title heading HTML not rendered.
* Bug fix: mc-ajax.js was not compatible with heading filter for output.
* Language updates: French, Spanish, Ukrainian

= 2.3.22 = 

* Edit: Allow integers up to 12 in the 'every' field for recurring events. (Previously 9)
* Bug fix: Incorrect sprintf call in {recurs} template, effecting recurring events by month.
* Language updates: German, Russian, Portuguese (Portugal), Hungarian, Ukrainian

= 2.3.21 =

* Plug-in conflict fix: CSS override to fix conflict with Ultimate Social Media Icons
* Bug fix: Allow {image_url} to fall back to thumbnail size if no medium / create _url equivalents for each size.
* Bug fix: Allow location controls to be entered with only keys.
* Bug fix: Entering default value for controlled locations is empty value, instead of 'none'.
* Bug fix: If value of location field is 'none', don't display.
* Bug fix: Use Location URL as map link if URL is provided and no other mappable location information
* Bug fix: if editing single instance, delete link will delete just that instance.
* Bug fix: If recurring event fields were hidden, but event recurred, recurrences would be deleted.
* Bug fix: Limiting locations did not work in Upcoming Events using 'events' mode.
* Bug fix: Allow limiting locations but all event location fields.
* Bug fix: Limiting locations accepts numeric values for limiting.
* Bug fix: {recurs} template tag indicates frequency ("Weekly", vs "every 3 weeks")
* Bug fix: fixed templating issue when custom templates used a tag multiple times with different attribute parameters.
* Add filter to modify the title information shown in list view to hint at hidden events ('mc_list_event_title_hint')
* Add filter: number of months shown in list view filterable on 'mc_show_months'
* Feature: Add shortcode/function to display a current event. [my_calendar_now]
* Feature: Add search results page option to calendar search widget.
* Removed all remaining code related to user settings, which are no longer in use.
* Language updates: French, Danish, Russian, Swedish, Portuguese/Brazil, Portuguese/Portugal, Norwegian Bokmal, Hungarian

= 2.3.20 =

* Bug fix: Escaped $ variable in custom JS wrapper
* Bug fix: has-events class appearing in calendar on days after all-day events
* Bug fix: Reset stylesheet applied outside calendar HTML. Eliminated elements not used by MC.
* Bug fix: Missing required argument for My Calendar search form widget
* Bug fix: 'Approve' link broken
* Bug fix: Details link could return expired event links.
* Translation updates: Spanish, Slovenian

= 2.3.19 =

* Bug fix: Could not un-check show today's events in Upcoming Events widget
* Bug fix: Could not turn off event recurrences section in event manager
* Bug fix: stripped HTML tags out of upcoming events & today's events template fields

= 2.3.18 =

* Bug in rendering of custom JS causing visible rendering of code.
* Bug in saving Today's Events widget settings

= 2.3.17 =

* 2.3.16 bug fix was incomplete, triggered new error. Sorry for rushing!

= 2.3.16 =

* Bug fix: Upcoming events did not show for logged-in users if site did not have private categories defined.
* Cleared a PHP notice.

= 2.3.15 =

* Bug fix: Controlled locations not input correctly from Add Event form
* Bug fix: Use force_balance_tags() when saving descriptions & short descriptions to prevent descriptions from breaking layout
* Bug fix: My Calendar reset stylesheet missing .mc-main on buttons; causing display issues with submit buttons.
* Bug fix: shortcode generator produced results in disabled form field; changed to readonly because Firefox does not permit selecting text in disabled fields.
* Bug fix: Widget navigation automatically reset itself if you saved widget form after clearing data
* Bug fix: category classes for multi-day, all-day events showed on termination date
* Bug fix: Checkbox states on JS scripts not retained
* Bug fix: Show default values in upcoming events widget
* Bug fix: Default values not saved on new installation
* Bug fix: Admin event manager should sort by Date/Time instead of Date/Title
* Documented [my_calendar_search] shortcode
* Added 'current' option for author/host to shortcode generator.
* Extensive code clean up
* Feature: Default view next month option in calendar and upcoming events lists.
* Deprecated upgrade cycles prior to version 1.10.0.
* Language updates: Japanese, Dutch, Italian, Spanish, Finnish, Swedish, Norwegian

= 2.3.14 =

* Bug fix: Disabled front-end event editing links for logged-in users.
* Language updates: Spanish, Norwegian, Hungarian

= 2.3.13 =

* Bug fix: Failed to handle "open links to event details" option in updated JS handling.

= 2.3.12 =

* Bug fix: change of option name meant that you couldn't enable/disable scripts.
* Bug fix: shortcode generator generates a 'readonly' textarea instead of disabled so it can be copied in Firefox.
* Accessibility: handle assignment of focus on AJAX navigation

= 2.3.11 =

* Change: Modified default JS saving so that only custom JS gets handled in editor.
* Change: toggle to enable/disable custom JS; default to off
* Change: Moved scripting into files.
* Notice: admin notice to inform users of need to activate JS if using custom
* Bug fix: Modify default JS so wpautop doesn't cause problems with toggles.
* Bug fix: External links displaying is_external boolean instead of classes.
* Bug fix: mysql error if location_type not defined but location_value is.
* Bug fix: page_id unset when default permalinks in use. [Ick. Don't use default permalinks.]
* Bug fix: My Calendar navigation panel could not disable top/bottom navigation.
* Feature: * Add Bcc notification list
* Accessibility: improvements to pop-up event details: focus & closing, ARIA
* Filter: headers filter for My Calendar email notifications.
* Filter: Add detection to pass custom JS from custom directory/theme directory
* Updated French, Spanish translations.
* Removed .po files from repository; reduces file size by over 2 MB.

= 2.3.10 =

* New filter: mc_jumpbox_future_years - alter the number of years into the future shown in the calendar date switcher.
* New filter: mc_add_events_url - alter URL for Add Events in adminbar; return URL
* New filter: mc_locate_events_page: alter menu parent of Add Events in admin menu; return menu slug or null
* Bug fix: ltype and lvalue not passed from shortcode into handler for upcoming events.
* Bug fix: disable comments by default for event post storage.
* Bug fix: misnamed variable in filter; resolves notice on line 239 of my-calendar-output.php
* Bug fix: do search and replace on default scripting as well when script fields are blank
* Bug fix: Check default option for import data from remote database; verify the default will be false
* Added template tag: {linking_title}; same as {link_title}, but falls back to details link if no URL input for event.
* Change default widget template to use {linking_title}.
* Security: Two XSS vulnerabilities fixed. Thanks <a href="http://www.timhurley.net/">Tim Hurley</a>
* Update Translation: Russian

= 2.3.9 =

* Bug fix: Minor event templates ( title, detail, etc. ) were not properly escaped in admin forms.
* Bug fix: use reply-to email header in support messages
* Bug fix: Mass approval of pending events broken.
* Bug fix: {linking} template tag referenced wrong event URL.
* Bug fix: My Calendar API RSS no longer dependent on default RSS data.
* Bug fix: Replace mysql_* functions for PHP 5.5 compatibility.
* Bug fix: Incorrect template tag in Single view template: {gcal} instead of {gcal_link}
* Bug fix: PHP notice on $map
* Language updates: Japanese, German, Italian

= 2.3.8 =

* Added {link_image} to add an image linked to the event URL in templates.
* Bug fix: extended caption value saved but not shown.
* Bug fix: For multi-day events ending at midnight, last date automatically extended one day at save.
* Bug fix: on copy, if start date is changed, but end date isn't, increment end date to match length of original event.
* Change: Eliminate error on empty title fields or invalid recurrence values. Set to default value instead. 

= 2.3.7 =

* Did not enqueue jQuery on front-end unless Google Maps was enabled. (Incorrect condition nesting...) Whoops.

= 2.3.6 =

* Error in yesterday's bug fix for upcoming events. 
* Bug fix: Email notifications broken.

= 2.3.5 =

* Bug fix: Notice in today's events widget
* Bug fix: Images from pre 2.3.0 configuration did not display in default Single event view.
* Bug fix: Upcoming events list could return too few events.
* Bug fix: Display default date format if format not set.
* Bug fix: Fallback to default JS if custom JS not defined.
* Filter: added filter to Google Maps code; mc_gmap_html
* Option: enabled option to disable Google Maps output.

= 2.3.4 =

* Bug fix: Week date format wouldn't save.
* Bug fix: Event posts & custom field data not saved on copy action
* Bug fix: HTML errors in {hcard} address format.
* Bug fix: Manage events search form overlapped pagination links
* Bug fix: Events ending at midnight in Today's Events lists appeared twice

= 2.3.3 =

* Bug fix: Notice on access_options filter.
* Bug fix: Invalid date values if no parameters set for iCal
* Bug fix: Invalid nonce check in location entry prevented creation of new locations. One missing exclamation point. Sigh.
* Bug fix: If location controls are on, allow old values to be saved, but raise notice that value is not part of controlled set.
* Feature: add sync=true to root iCal URL to connect apps for scheduled syncing. (http://example.com/feeds/my-calendar-ics/?sync=true)
* Updated: Polish translation

= 2.3.2 =

* Bug fix: label change to clarify entry format for location controls
* Bug fix: Missing end tag on <time> element
* Bug fix: my_calendar_search_title can handle missing 2nd argument
* Bug fix: Add "active" class span on time toggle active case.
* Bug fix: Recurring all-day events showing twice
* Bug fix: Non-editable fields for date/time input broke occurrences & restricted time options
* Bug fix: Category filtering broken when holiday categories enabled
* Bug fix: Double check whether categories exist and throw error if not, after attempting to create default category.
* Feature: Mass delete locations

= 2.3.1 =

* Bug fix: PHP warning on event save
* Bug fix: PHP Notices generated on deleted author/host value.
* Bug fix: Pop-up calendar for date entry had incorrect day labels
* Bug fix: Editing individual date instances issues.
* Bug fix: {image} fallback for pre 2.3.0 uploaded images
* Added: secondary sort filter for main calendar views; default event_title ASC. Field and direction must be provided to change.
* Updated my-calendar.pot

= 2.3.0 =

This is a major revision.

* Bug fix: Manage events screen showed no data for users without manage_events permissions.
* Bug fix: if single event set, could not filter to time period views.
* Bug fix: 'single' template ID not passed into template filter.
* Bug fix: events in private categories appeared in time-based upcoming events lists.
* Bug fix: RSS feed encoding.
* Bug fix: Turn-of-year issues with week view.
* Bug fix: Added new locations multiple times if added with multiple occurrences of an event.
* Bug fix: In some browsers, time selector added invalid data.
* Bug fix: List of search results not wrapped in a list element.
* Bug fix: Trim spaces on above/below navigation strings.
* Bug fix: If an event ends at midnight, automatically end tomorrow unless set for a later date.
* Bug fix: Don't show events on both days if they end at midnight.
* Bug fix: Don't attempt to enqueue jquery.charcount.js if WP to Twitter not installed.
* Bug fix: Dates didn't strip links in list view when JS disabled for that view.

* New template tag: {runtime} to show human language version of length of event.
* New template tag: {excerpt} to create autoexcerpt from description field, using shortdesc if it exists.

* New feature: Accessibility features for locations.
* New feature: Specify accessibility services for events.
* New feature: ticketing link field
* New feature: event registration information fields
* New feature: my_calendar_event shortcode can query templates by keyword (list,mini,single,grid).
* New feature: filter events by available accessibility services
* New feature: Combined filter shortcode to group all filters into a single form. [mc_filters show='locations,categories,access']
* New feature: new API for adding custom fields to events.
* New feature: data API to fetch event data in JSON, CSV, or RSS formats. 
* New feature: Archive events to hide from admin events list. 
* New feature: Control input options for multiple types of location input data. 
* New feature: Shortcode generator for primary, upcoming, and today's events shortcodes.
* New feature: admin-side event search
* New feature: category key now acts as quick links to filter by category
* New feature: Option to add title to Event Search widget.

* New filter: mc_date_format for customizing date formats.
* New filter: customize search results page: mc_search_page
* New filter: mc_use_permalinks to enable use of custom post type permalinks for single event pages.
* New filter: mc_post_template to customize template used in single event shortcode automatically inserted into custom post type pages.

* New design: new stylesheet available: twentyfourteen.css

* Updated: added more fields to search on events.
* Updated: updated image uploader to use add media panel and store attachment ID
* Updated: <title> template supports all template tags (but strips HTML.).
* Updated: Various aspects of UI
* Updated: Date/time selectors. See http://amsul.ca/pickadate.js/, MIT license.

* Reorganized default output template code.
* Import all used locations into location manager.
* Removed User settings fields.
* Moved Holiday category assignment to Category Manager.
* Improved get current URL function.
* iCal output in multiple-month view outputs all displayed months.
* {map} template tag to display a Google Map using the Google Maps API. (Not available in pop-up displays.)
* Scheduled removal of showkey, shownav, toggle, and showjump shortcode attributes.
* Removed upgrade support for 1.6.x & 1.7.x series of My Calendar.

== Frequently Asked Questions ==

= Hey! Why don't you have any Frequently Asked Questions here! =

Because the majority of users end up on my web site asking for help anyway -- and it's simply more difficult to maintain two copies of my Frequently Asked Questions. Please visit [my web site FAQ](http://www.joedolson.com/my-calendar/faq/) to read my Frequently Asked Questions!

= This plug-in is really complicated. Why can't you personally help me figure out how to use it? =

I can! Just not in person. I've written a User's Guide for My Calendar, which you can [purchase at my web site](https://www.joedolson.com/my-calendar/users-guide/) for $25. This helps defray the thousands of hours I've spent developing the plug-in and providing support. Please, consider buying the User's Guide or [making a donation](https://www.joedolson.com/donate.php) before asking for support!

= Can my visitors or members submit events? =

I've written a paid plug-in that adds this feature to My Calendar, called My Calendar Pro. [Buy it today](https://www.joedolson.com/my-calendar/pro/)!

= Is there an advanced search feature? =

The search feature in My Calendar is pretty basic; but buying My Calendar Pro gives you a richer search feature, where you can narrow by dates, categories, authors, and more to refine your event search.

== Screenshots ==

1. Calendar using calendar list format.
2. Calendar using monthly calendar format.
3. Event management page
4. Category management page
5. Settings page
6. Location management
7. Style editing
8. Mini calendar
9. Script/behavior editing
10. Template editing

== Upgrade Notice ==

* 2.4.0 is a major new release; lots of bug fixes and feature enhancements. 2.4.4: event manager default sort direction fixed, print view date selection, option to hide 'more' link in event view.