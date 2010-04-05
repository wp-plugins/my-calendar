<?php
function my_calendar_help() {

?>
<div class="wrap">
<h2><?php _e('How to use My Calendar','my-calendar'); ?></h2>
<?php show_support_box(); ?>

<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Shortcode Syntax','my-calendar'); ?></h3>
	<div class="inside">	

<ul>
<li><code>[my_calendar]</code><br />
<?php _e('This basic shortcode will show the calendar on a post or page including all categories and the category key, in a traditional month-by-month format.','my-calendar'); ?>
</li>
<li><code>[my_calendar category="General" format="list" showkey="no"]</code><br />
<?php _e('The shortcode supports three attributes, <code>category</code>, <code>format</code> and <code>showkey</code>. There is currently only one alternate option for <code>format</code> &mdash; <code>list</code> &mdash; which will show the calendar in a list format, skipping dates without any events. The <code>category</code> attribute requires either the name of or ID number one of your event categories (the name is case-sensitive). This will show a calendar only including events in that category. Setting <code>showkey</code> to <code>no</code> will prevent the category key from being displayed &mdash; this can be useful with single-category output.','my-calendar'); ?>
</li>
</ul>
</div>
</div>
</div>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
<h3><?php _e('Category Icons','my-calendar'); ?></h3>
	<div class="inside">	
<p>
<?php _e('My Calendar is designed to manage multiple calendars. The basis for these calendars are categories; you can easily setup a calendar page which includes all categories, or you can dedicate separate pages to calendars in each category. For an example, this might be useful for you in managing the tour calendars for multiple bands; event calendars for a variety of locations, etc.','my-calendar'); ?>
</p>
<p>
<?php _e('The pre-installed category icons may not be especially useful for your needs or design. I\'m assuming that you\'re going to upload your own icons -- all you need to do is upload them to the plugin\'s icons folder, and they\'ll be available for immediate use.','my-calendar'); ?> <?php _e('Your icons folder is:','my-calendar'); ?> <code><?php echo WP_PLUGIN_DIR; ?>/my-calendar/icons/</code>
</p>
</div>
</div>
</div>

<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
<h3 id="template"><?php _e('Widget Templating','my-calendar'); ?></h3>
	<div class="inside">
<p>
<?php _e('These codes are available in calendar widgets to create your own custom calendar format.'); ?>
</p>
<dl>
<dt><code>{category}</code></dt>
<dd><?php _e('Displays the name of the category the event is in.','my-calendar'); ?></dd>

<dt><code>{title}</code></dt>
<dd><?php _e('Displays the title of the event.','my-calendar'); ?></dd>

<dt><code>{time}</code></dt>
<dd><?php _e('Displays the start time for the event.','my-calendar'); ?></dd>

<dt><code>{date}</code></dt>
<dd><?php _e('Displays the date on which the event begins.','my-calendar'); ?></dd>

<dt><code>{author}</code></dt>
<dd><?php _e('Displays the WordPress author who posted the event.','my-calendar'); ?></dd>

<dt><code>{link}</code></dt>
<dd><?php _e('Displays the URL provided for the event.','my-calendar'); ?></dd>

<dt><code>{description}</code></dt>
<dd><?php _e('Displays the description of the event.','my-calendar'); ?></dd>

<dt><code>{link_title}</code></dt>
<dd><?php _e('Displays title of the event as a link if a URL is present, or the title alone if no URL is available.','my-calendar'); ?></dd>

<dt><code>{location}</code></dt>
<dd><?php _e('Displays the name of the location of the event.','my-calendar'); ?></dd>

<dt><code>{street}</code></dt>
<dd><?php _e('Displays the first line of the site address.','my-calendar'); ?></dd>

<dt><code>{street2}</code></dt>
<dd><?php _e('Displays the second line of the site address.','my-calendar'); ?></dd>

<dt><code>{city}</code></dt>
<dd><?php _e('Displays the city for the event.','my-calendar'); ?></dd>

<dt><code>{state}</code></dt>
<dd><?php _e('Displays the state for the event.','my-calendar'); ?></dd>

<dt><code>{postcode}</code></dt>
<dd><?php _e('Displays the postcode for the event.','my-calendar'); ?></dd>

<dt><code>{country}</code></dt>
<dd><?php _e('Displays the country for the event location.','my-calendar'); ?></dd>

<dt><code>{hcard}</code></dt>
<dd><?php _e('Displays the event address in <a href="http://microformats.org/wiki/hcard">hcard</a> format.','my-calendar'); ?></dd>

<dt><code>{link_map}</code></dt>
<dd><?php _e('Displays a link to a Google Map of the event, if sufficient address information is available. If not, will be empty.','my-calendar'); ?></dd>
</div>
</div>
</div>
</div>
<?php } ?>