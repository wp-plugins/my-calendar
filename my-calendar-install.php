<?php

// define global variables;
global $initial_listjs, $initial_caljs, $initial_minijs, $initial_style, $initial_db, $initial_loc_db, $initial_cat_db, $default_template;

  // defaults will go into the options table on a new install
$initial_listjs = 'var $j = jQuery.noConflict();

$j(document).ready(function() {
  $j("#calendar-list li").children().not(".event-date").hide();
  $j("#calendar-list li.current-day").children().show();
  $j(".event-date").toggle(
     function() {
     $j("#calendar-list li").children().not(".event-date").hide();
	 $j(this).parent().children().not(".event-date").show("fast");
     }, 
     function() { 
     $j("#calendar-list li").children().not(".event-date").hide("fast");
     }
     );
});';  
  
$initial_caljs = 'var $j = jQuery.noConflict();

$j(document).ready(function() {
  $j(".calendar-event").children().not("h3").hide();
  $j(".calendar-event h3").toggle(
     function() {
     $j(".calendar-event").children().not("h3").hide();
	 $j(this).parent().children().not("h3").show("fast");
     }, 
     function() { 
     $j(".calendar-event").children().not("h3").hide("fast");
     }
     );
});';  

$initial_minijs = 'var $j = jQuery.noConflict();

$j(document).ready(function() {
  $j(".mini .has-events").children().not(".trigger").hide();
  $j(".has-events .trigger").toggle(
     function() {
     $j(".mini .has-events").children().not(".trigger").hide();
	 $j(this).parent().children().not(".trigger").show("fast");
     }, 
     function() { 
     $j(".mini .has-events").children().not(".trigger").hide("fast");
     }
     );
});';
  
$initial_style = "
#jd-calendar * {
margin: 0;
padding: 0;
line-height: 1.5;
color: #000;
background: #fff;
}
#jd-calendar caption, #jd-calendar .my-calendar-date-switcher  {
background: #edf7ff;
border: 1px solid #a9e3ff;
margin: 2px 0;
font-weight:700;
padding:2px 0;
}

#jd-calendar table {
width:100%;
line-height:1.2;
border-collapse:collapse;
}

#jd-calendar td {
vertical-align:top;
border:1px solid #eee;
text-align:left;
width:13%;
height:70px;
padding:2px!important;
}
.mini td {
height: auto!important;
}
#jd-calendar th {
text-align: center;
padding: 5px 0!important;
letter-spacing: 1px;
}
#jd-calendar th abbr {
border-bottom: none;
}
#jd-calendar h3 {
font-size:.8em;
font-family: Arial, Verdana, sans-serif;
font-weight:700;
margin:3px 0;
padding:0;
width: 100%;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
}
#jd-calendar h3 img {
vertical-align: middle;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}
#jd-calendar .calendar-event .details, #jd-calendar .calendar-events {
position:absolute;
width:50%;
background: #edf7ff;
color:#000;
border:1px solid #9b5;
-moz-border-radius:10px;
-moz-box-shadow:3px 3px 6px #777;
-webkit-box-shadow:3px 3px 6px #777;
box-shadow:3px 3px 6px #777;
padding:5px;
z-index: 3;
}
#jd-calendar .details .close {
float: right;
width: 12px!important;
margin-top: -2px!important;
}
#jd-calendar .calendar-events {
width: 200px!important;
left: 0px;
}
#jd-calendar .list-event .details {
background:#fafafa;
border:1px solid #eee;
-moz-border-radius:5px;
-webkit-border-radius:5px;
border-radius:5px;
margin:5px 0;
padding:5px 5px 0;
color: #333;
}
#jd-calendar #calendar-list {
margin: 0;
padding: 0;
}
#jd-calendar #calendar-list li {
padding:5px;
list-style-type: none;
margin: 0;
}

#jd-calendar #calendar-list .odd {
background:#d3e3e3;
}

#jd-calendar .odd .list-event .details {
background:#e3f3f3;
border:1px solid #c3d3d3;
}

#jd-calendar .current-day {
background:#ffb;
}
#jd-calendar .current-day .mc-date {
color: #000;
background: #eee;
}

#jd-calendar .mc-date {
display:block;
background:#f6f6f6;
margin:-2px -2px 2px;
padding:2px 4px;
}

#jd-calendar .weekend {
background:#bd7;
color: #000;
}

#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}
#jd-calendar .category-key {
background: #edf7ff;
border: 1px solid #a9e3ff;
padding: 5px;
margin: 5px 0;
}
#jd-calendar .category-key ul {
list-style-type: none;
margin: 0;
padding: 0;
}
#jd-calendar .category-key li {
margin: 2px 10px;
}
#jd-calendar .category-key span {
margin-right:5px;
vertical-align:middle;
}
#jd-calendar .category-key .no-icon {
width: 10px;
height: 10px;
display: inline-block;
border: 1px solid #555;
-moz-border-radius: 2px;
-webkit-border-radius: 2px;
border-radius: 2px;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .calendar-event .event-time, #jd-calendar .list-event .event-time {
display:block;
float:left;
height:100%;
margin-right:10px;
margin-bottom:10px;
font-weight:700;
font-size:.9em;
}

#jd-calendar p {
line-height:1.5;
margin:0 0 1em;
padding:0;
}

#jd-calendar .sub-details {
margin-left:6em;
}

#jd-calendar .vcard {
font-size:.9em;
margin:10px 0;
}

#jd-calendar .calendar-event .vcard {
margin:0 0 10px;
}

#jd-calendar,#calendar-list {
clear:left;
background: #fff;
}
#jd-calendar {
position: relative;
}
#jd-calendar img {
border: none;
}
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}

#jd-calendar .my-calendar-nav ul {
height: 2.95em;
list-style-type:none;
margin:0;
padding:0;
}

.mini .my-calendar-nav ul {
height: 2em!important;
}

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}

#jd-calendar .my-calendar-nav li:before {
content:'';
}
#jd-calendar .my-calendar-nav li a {
display:block;
background:#fff;
border:1px solid #9b5;
text-align:center;
padding:1px 20px;
color: #243f82;
}
.mini .my-calendar-nav li a {
padding: 1px 3px!important;
font-size: .7em;
}
#jd-calendar .my-calendar-nav li a:hover {
background:#a9e3ff;
color:#000;
border: 1px solid #243f82;
}
#jd-calendar .my-calendar-next {
margin-left: 4px;
text-align:right;
}
#jd-calendar .my-calendar-next a {
-webkit-border-top-right-radius: 8px;
-webkit-border-bottom-right-radius: 8px;
-moz-border-radius-topright: 8px;
-moz-border-radius-bottomright: 8px;
border-top-right-radius: 8px;
border-bottom-right-radius: 8px;
}
#jd-calendar .my-calendar-prev a {
-webkit-border-top-left-radius: 8px;
-webkit-border-bottom-left-radius: 8px;
-moz-border-radius-topleft: 8px;
-moz-border-radius-bottomleft: 8px;
border-top-left-radius: 8px;
border-bottom-left-radius: 8px;
}
#jd-calendar .day-without-date {
background: #fafafa;
}
#upcoming-events .past-event {
color: #777;
}
#upcoming-events .today {
color: #111;
}
#upcoming-events .future-event {
color: #555;
}
#jd-calendar.mini .my-calendar-date-switcher label {
display: block;
float: left;
width: 6em;
}
#jd-calendar.mini .my-calendar-date-switcher {
padding: 4px;
}
#jd-calendar.mini td .category-icon {
display: none;
}
#jd-calendar.mini h3 {
font-size: 1.1em;
}

#jd-calendar.mini .day-with-date span, #jd-calendar.mini .day-with-date a {
font-family: Arial, Verdana, sans-serif;
font-size: .9em;
padding:1px;
}
#jd-calendar .mini-event .sub-details {
margin: 0;
border-bottom: 1px solid #ccc;
padding: 2px 0 0;
margin-bottom: 5px;
}
#jd-calendar.mini .day-with-date a {
display: block;
margin: -2px;
font-weight: 700;
text-decoration: underline;
}";

$default_template = "<strong>{date}</strong> &#8211; {link_title}<br /><span>{time}, {category}</span>";

$initial_db = "CREATE TABLE " . MY_CALENDAR_TABLE . " ( 
 event_id INT(11) NOT NULL AUTO_INCREMENT,
 event_begin DATE NOT NULL,
 event_end DATE NOT NULL,
 event_title VARCHAR(255) NOT NULL,
 event_desc TEXT NOT NULL,
 event_time TIME,
 event_endtime TIME,
 event_recur CHAR(1),
 event_repeats INT(3),
 event_author BIGINT(20) UNSIGNED,
 event_category BIGINT(20) UNSIGNED,
 event_link TEXT,
 event_link_expires TINYINT(1) NOT NULL,
 event_label VARCHAR(60) NOT NULL,
 event_street VARCHAR(60) NOT NULL,
 event_street2 VARCHAR(60) NOT NULL,
 event_city VARCHAR(60) NOT NULL,
 event_state VARCHAR(60) NOT NULL,
 event_postcode VARCHAR(10) NOT NULL,
 event_country VARCHAR(60) NOT NULL,
 event_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_zoom INT(2) NOT NULL DEFAULT '14',
 PRIMARY KEY  (event_id),
 KEY event_recur (event_recur)
 );";

$initial_cat_db = "CREATE TABLE " . MY_CALENDAR_CATEGORIES_TABLE . " ( 
 category_id INT(11) NOT NULL AUTO_INCREMENT, 
 category_name VARCHAR(255) NOT NULL, 
 category_color VARCHAR(7) NOT NULL, 
 category_icon VARCHAR(128) NOT NULL,
 PRIMARY KEY  (category_id) 
 );";
 
$initial_loc_db = "CREATE TABLE " . MY_CALENDAR_LOCATIONS_TABLE . " ( 
 location_id INT(11) NOT NULL AUTO_INCREMENT, 
 location_label VARCHAR(60) NOT NULL,
 location_street VARCHAR(60) NOT NULL,
 location_street2 VARCHAR(60) NOT NULL,
 location_city VARCHAR(60) NOT NULL,
 location_state VARCHAR(60) NOT NULL,
 location_postcode VARCHAR(10) NOT NULL,
 location_country VARCHAR(60) NOT NULL,
 location_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_zoom INT(2) NOT NULL DEFAULT '14',
 PRIMARY KEY  (location_id) 
 );";

function mc_default_settings( ) {
global $initial_style, $default_template, $initial_listjs, $initial_caljs, $initial_db, $initial_loc_db, $initial_cat_db;
// no arguments
	add_option('can_manage_events','edit_posts');
	add_option('my_calendar_style',"$initial_style");
	add_option('display_author','false');
	add_option('display_jump','false');
	add_option('display_todays','true');
	add_option('display_upcoming','true');
	add_option('display_upcoming_days',7);
	add_option('my_calendar_version','1.4.0');
	add_option('display_upcoming_type','false');
	add_option('display_upcoming_events',3);
	add_option('display_past_days',0);
	add_option('display_past_events',2);
	add_option('my_calendar_use_styles','false');
	add_option('my_calendar_show_months',1);
	add_option('my_calendar_show_map','true');
	add_option('my_calendar_show_address','false');
	add_option('my_calendar_today_template',$default_template);
	add_option('my_calendar_upcoming_template',$default_template);
	add_option('my_calendar_today_title','Today\'s Events');
	add_option('my_calendar_upcoming_title','Upcoming Events');
	add_option('calendar_javascript',0);
	add_option('list_javascript',0);
	add_option('mini_javascript',0);
	add_option('my_calendar_minijs',$initial_minijs);
	add_option('my_calendar_listjs',$initial_listjs);
	add_option('my_calendar_caljs',$initial_caljs);
	add_option('my_calendar_notime_text','N/A');
	add_option('my_calendar_hide_icons','false');	 
	add_option('mc_event_link_expires','no');
	add_option('mc_apply_color','default');
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);	
	
}

function upgrade_db() {
global $initial_db, $initial_loc_db, $initial_cat_db;
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($initial_db);
	dbDelta($initial_cat_db);
	dbDelta($initial_loc_db);	
}
?>