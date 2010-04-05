<?php
/*
Plugin Name: My Calendar
Plugin URI: http://www.joedolson.com/articles/my-calendar/
Description: Accessible WordPress event calendar plugin. Show events from multiple calendars on pages, in posts, or in widgets.
Author: Joseph C Dolson
Author URI: http://www.joedolson.com
Version: 1.0.0
*/
/*  Copyright 2009  Joe Dolson (email : joe@joedolson.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Enable internationalisation
$plugin_dir = basename(dirname(__FILE__));
load_plugin_textdomain( 'my-calendar','wp-content/plugins/'.$plugin_dir, $plugin_dir);

// Define the tables used in My Calendar
define('MY_CALENDAR_TABLE', $table_prefix . 'my_calendar');
define('MY_CALENDAR_CATEGORIES_TABLE', $table_prefix . 'my_calendar_categories');
// Create a master category for My Calendar and its sub-pages
add_action('admin_menu', 'my_calendar_menu');
// Add the function that puts style information in the header
add_action('wp_head', 'my_calendar_wp_head');
// Add the function that deals with deleted users
add_action('delete_user', 'wp_deal_with_deleted_user');
// Add the widgets if we are using version 2.8
add_action('widgets_init', 'init_my_calendar_today');
add_action('widgets_init', 'init_my_calendar_upcoming');

function jd_calendar_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/my-calendar.php'))
		$links[] = "<a href='admin.php?page=my-calendar-config'>" . __('Settings', 'my-calendar') . "</a>";
	return $links;
}
add_filter('plugin_action_links', 'jd_calendar_plugin_action', -10, 2);

include(dirname(__FILE__).'/my-calendar-settings.php' );
include(dirname(__FILE__).'/my-calendar-categories.php' );
include(dirname(__FILE__).'/my-calendar-help.php' );
include(dirname(__FILE__).'/my-calendar-event-manager.php' );
include(dirname(__FILE__).'/my-calendar-widgets.php' );
include(dirname(__FILE__).'/date-utilities.php' );


// Before we get on with the functions, we need to define the initial style used for My Calendar

function show_support_box() {
?>
<div class="resources">
<ul>
<li><a href="http://www.joedolson.com/articles/my-calendar/"><?php _e("Get Support",'my-calendar'); ?></a></li>
<li><a href="http://www.joedolson.com/donate.php"><?php _e("Make a Donation",'my-calendar'); ?></a></li>
<li><form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<div>
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="hidden" name="hosted_button_id" value="8490399" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="Donate" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
</div>
</form></li>
</ul>

</div>
<?php
}

// Function to deal with events posted by a user when that user is deleted
function deal_with_deleted_user($id) {
  global $wpdb;
  // This wouldn't work unless the database was up to date. Lets check.
  check_calendar();
  // Do the query
  $wpdb->get_results( "UPDATE ".MY_CALENDAR_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=".$id );
}

// Function to add the calendar style into the header
function my_calendar_wp_head() {
  global $wpdb;
  // If the calendar isn't installed or upgraded this won't work
  check_calendar();
  $styles = stripcslashes(get_option('my_calendar_style'));
	if ( get_option('my_calendar_use_styles') != 'true' ) {
echo "
<style type=\"text/css\">
<!--
// Styles from My Calendar - Joseph C Dolson http://www.joedolson.com/
$styles
-->
</style>";
	}
}

// Function to deal with adding the calendar menus
function my_calendar_menu() {
  global $wpdb;
  // We make use of the My Calendar tables so we must have installed My Calendar
  check_calendar();
  // Set admin as the only one who can use My Calendar for security
  $allowed_group = 'manage_options';
  // Use the database to *potentially* override the above if allowed
  $allowed_group = get_option('can_manage_events');


  // Add the admin panel pages for My Calendar. Use permissions pulled from above
	if (function_exists('add_menu_page')) {
		add_menu_page(__('My Calendar','my-calendar'), __('My Calendar','my-calendar'), $allowed_group, 'my-calendar', 'edit_calendar');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('my-calendar', __('Add/Edit Events','my-calendar'), __('Add/Edit Events','my-calendar'), $allowed_group, 'my-calendar', 'edit_calendar');
		add_action( "admin_head", 'my_calendar_write_js' );		
		add_action( "admin_head", 'my_calendar_add_styles' );
		// Note only admin can change calendar options
		add_submenu_page('my-calendar', __('Manage Categories','my-calendar'), __('Manage Categories','my-calendar'), 'manage_options', 'my-calendar-categories', 'manage_categories');
		add_submenu_page('my-calendar', __('Settings','my-calendar'), __('Settings','my-calendar'), 'manage_options', 'my-calendar-config', 'edit_my_calendar_config');
		add_submenu_page('my-calendar', __('My Calendar Help','my-calendar'), __('Help','my-calendar'), 'manage_options', 'my-calendar-help', 'my_calendar_help');
	}
}
add_action( "admin_menu", 'my_calendar_add_javascript' );

// Function to add the javascript to the admin header
function my_calendar_add_javascript() { 
	if ($_GET['page'] == 'my-calendar') {
		wp_enqueue_script('jquery-ui-datepicker',WP_PLUGIN_URL . '/my-calendar/js/ui.datepicker.js', array('jquery','jquery-ui-core') );
	}
	if ($_GET['page'] == 'my-calendar-categories') {
		wp_enqueue_script('jquery-colorpicker',WP_PLUGIN_URL . '/my-calendar/js/jquery-colorpicker.js', array('jquery') );	
	}
}
function my_calendar_write_js() {
if ($_GET['page']=='my-calendar') {
	echo '
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($) {
    $("#event_begin").datepicker({
		numberOfMonths: 2,
		dateFormat: "yy-mm-dd"
	});
    $("#event_end").datepicker({
		numberOfMonths: 2,
		dateFormat: "yy-mm-dd"
	});
});
//]]>	 
</script>
';
}
if ($_GET['page']=='my-calendar-categories') {
?>
<script type=\"text/javascript\">
//<![CDATA[
//jQuery(document).ready(function($) {
//$('#category_color').colorpicker({ flat: true });
//]]>	 
</script>
<?php
}
}
function my_calendar_add_display_javascript() {
		wp_enqueue_script('jquery');
}
add_action('init','my_calendar_add_display_javascript');

function my_calendar_calendar_javascript() {
	if ( get_option('calendar_javascript') != 1 ) {
?>
<script type='text/javascript'>
var $j = jQuery.noConflict();

$j(document).ready(function() {
  $j('.calendar-event').children().not('h3').hide();
  $j('.calendar-event h3').toggle(
     function() {
     $j('.calendar-event').children().not('h3').hide();
	 $j(this).parent().children().not('h3').show('fast');
     }, 
     function() { 
     $j('.calendar-event').children().not('h3').hide('fast');
     }
     );
});
</script>
<?php
	}
	if ( get_option('list_javascript') != 1 ) {
?>
<script type='text/javascript'>
var $j = jQuery.noConflict();

$j(document).ready(function() {
  $j('#calendar-list li').children().not('.event-date').hide();
  $j('.event-date').toggle(
     function() {
     $j('#calendar-list li').children().not('.event-date').hide();
	 $j(this).parent().children().not('.event-date').show('fast');
     }, 
     function() { 
     $j('#calendar-list li').children().not('.event-date').hide('fast');
     }
     );
});
</script>
<?php	
	}
}
add_action('wp_head','my_calendar_calendar_javascript');

function my_calendar_add_styles() {
?>
<link type="text/css" rel="stylesheet" href="<?php echo WP_PLUGIN_URL; ?>/my-calendar/js/ui.datepicker.css" />
<?php
	echo '  
<style type="text/css">
<!--
.jd-my-calendar {
margin-right: 150px!important;
}
#my-calendar legend {
font-weight: 700;
font-size: 1em;
}
.resources {
float: right;
border: 1px solid #aaa;
padding: 10px 10px 0;
margin-left: 10px;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
border-radius: 5px;
background: #fff;
text-align: center;
}
.resources form {
margin: 0!important;
}	
#category_icon option {
padding: 5px 0 5px 24px;
}
#my-calendar-admin-table .delete {
background: #a00;
color: #fff;
padding: 2px 8px;
font-size: .8em;
border: 1px solid #fff;
-moz-border-radius: 8px;
-webkit-border-radius: 8px;
border-radius: 8px;
text-decoration: none;
}
#my-calendar-admin-table .delete:hover, #my-calendar-admin-table .delete:focus {
border: 1px solid #999;
background: #b11;
}
.n4 {width: 16px;}
.n5 {width: 32px;}
.n6 {width: 64px;}
.n7 {width: 128px;}
.n8 {width: 256px;}
//-->
</style>';
}

function my_calendar_insert($atts) {
	extract(shortcode_atts(array(
				'name' => 'all',
				'format' => 'calendar',
				'category' => 'all',
				'showkey' => 'yes',
			), $atts));
	return my_calendar($name,$format,$category,$showkey);
}
// add shortcode interpreter
add_shortcode('my_calendar','my_calendar_insert');

// Function to check what version of My Calendar is installed and install if needed
function check_calendar() {
  // Checks to make sure My Calendar is installed, if not it adds the default
  // database tables and populates them with test data. If it is, then the 
  // version is checked through various means and if it is not up to date 
  // then it is upgraded. (Or will be, once there's a need.)

  // Lets see if this is first run and create a table if it is!
  global $wpdb, $initial_style;

  // default styles will go into the options table on a new install
$initial_style = "
#jd-calendar caption {
margin-top:-8px;
background:#f6f6f6;
border:1px solid #ddd;
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
width:60px;
height:70px;
padding:2px!important;
}

#jd-calendar h3 {
font-size:1em;
font-weight:700;
margin:3px 0;
padding:0;
}
#jd-calendar h3 img {
vertical-align: bottom;
margin: 0 3px 0 0!important;
}
#jd-calendar #calendar-list h3 img {
vertical-align: middle;
}

#jd-calendar .list-event h3 {
font-size:1.2em;
margin:0;
}

#jd-calendar .calendar-event .details {
position:absolute;
width:300px;
background:#cae0f5;
color:#000;
border:1px solid;
-moz-border-radius:10px;
-moz-box-shadow:4px 4px 12px #777;
-webkit-box-shadow:4px 4px 12px #777;
box-shadow:4px 4px 12px #777;
padding:5px;
z-index: 3;
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
background:#ffd;
}

#jd-calendar td span {
display:block;
background:#f6f6f6;
margin:-2px -2px 2px;
padding:2px 4px;
}

#jd-calendar .calendar-event span {
display:inline;
background:none;
margin:0;
padding:0;
}

#jd-calendar .weekend {
font-weight:700;
background:#fdd;
}

#jd-calendar th {
font-size:.8em;
text-transform:uppercase;
padding:2px 4px 2px 0;
}

.category-icon {
margin-right:5px;
margin-bottom:5px;
vertical-align:middle;
}

#calendar-list li {
text-indent:0;
margin:0;
padding:0;
}

#jd-calendar .event-time {
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
padding: 5px;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
border-radius: 5px;
}
#jd-calendar img {
border: none;
}
.category-color-sample img {
margin-right: 5px;
vertical-align: top;
}
.my-calendar-nav {
height:1em;
}

#jd-calendar .my-calendar-nav ul {
list-style-type:none;
height:2.2em;
border-bottom:1px solid #ccc;
margin:0;
padding:0;
}

#jd-calendar .my-calendar-nav li {
float:left;
list-style-type: none;
}

#jd-calendar .my-calendar-nav li:before {
content:'';
}

my-calendar-nav .my-calendar-next {
text-align:right;
}

.my-calendar-nav li a {
display:block;
background:#eee;
border:1px solid #ddd;
-moz-border-radius:5px 5px 0 0;
-webkit-border-radius:5px 5px 0 0;
border-radius:5px 5px 0 0;
border-bottom:none;
text-align:center;
padding:1px 20px;
}

.my-calendar-nav li a:hover {
background:#fff;
}";

$default_template = "<strong>{date}</strong> &#8211; {link_title}<br /><span>{time}, {category}</span>";
	 
  // Assume this is not a new install until we prove otherwise
  $new_install = false;

  $my_calendar_exists = false;
  $upgrade_path = false;

  // Determine the calendar version
  $tables = $wpdb->get_results("show tables;");
  foreach ( $tables as $table ) {
      foreach ( $table as $value )  {
		  if ( $value == MY_CALENDAR_TABLE ) {
		      $my_calendar_exists = true;
			  $current_version = get_option('my_calendar_version');
			  // check whether installed version matches most recent version, establish upgrade process.
		    }
       }
    }
  if ( $my_calendar_exists == false ) {
      $new_install = true;
    }

  // Now we've determined what the current install is or isn't 
  if ( $new_install == true ) {
      $sql = "CREATE TABLE " . MY_CALENDAR_TABLE . " (
                                event_id INT(11) NOT NULL AUTO_INCREMENT ,
                                event_begin DATE NOT NULL ,
                                event_end DATE NOT NULL ,
                                event_title VARCHAR(60) NOT NULL ,
                                event_desc TEXT NOT NULL ,
                                event_time TIME ,
                                event_recur CHAR(1) ,
                                event_repeats INT(3) ,
                                event_author BIGINT(20) UNSIGNED,
								event_category BIGINT(20) UNSIGNED,
								event_link TEXT,
								event_label VARCHAR(60) NOT NULL ,
								event_street VARCHAR(60) NOT NULL ,
								event_street2 VARCHAR(60) NOT NULL ,
								event_city VARCHAR(60) NOT NULL ,
								event_state VARCHAR(60) NOT NULL ,
								event_postcode VARCHAR(10) NOT NULL ,
								event_country VARCHAR(60) NOT NULL ,
                                PRIMARY KEY (event_id)
                        )";
      $wpdb->get_results($sql);
      add_option('can_manage_events','edit_posts');
      add_option('my_calendar_style',"$initial_style");
      add_option('display_author','false');
      add_option('display_jump','false');
      add_option('display_todays','true');
      add_option('display_upcoming','true');
      add_option('display_upcoming_days',7);
      add_option('my_calendar_version','1.0');
      add_option('display_upcoming_type','false');
      add_option('display_upcoming_events',3);
      add_option('display_past_days',0);
      add_option('display_past_events',2);
	  add_option('my_calendar_use_styles','true');
	  add_option('my_calendar_show_months',1);
	  add_option('my_calendar_show_map','true');
	  add_option('my_calendar_show_address','false');
	  add_option('my_calendar_today_template',$default_template);
	  add_option('my_calendar_upcoming_template',$default_template);
	  add_option('my_calendar_today_title','Today\'s Events');
	  add_option('my_calendar_upcoming_title','Upcoming Events');
	  add_option('calendar_javascript',1);
	  add_option('list_javascript',1);
      $sql = "UPDATE " . MY_CALENDAR_TABLE . " SET event_category=1";
      $wpdb->get_results($sql);
	  
      $sql = "CREATE TABLE " . MY_CALENDAR_CATEGORIES_TABLE . " ( 
                                category_id INT(11) NOT NULL AUTO_INCREMENT, 
                                category_name VARCHAR(30) NOT NULL , 
                                category_color VARCHAR(30) NOT NULL , 
								category_icon VARCHAR(128) NOT NULL ,
                                PRIMARY KEY (category_id) 
                             )";
      $wpdb->get_results($sql);
      $sql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_color='#ffffff', category_icon='event.png'";
      $wpdb->get_results($sql);
    }
	
	// placeholder for future upgrades
	
	switch ($upgrade_path) {
		case $upgrade_path == FALSE:
		break;
		default:
		break;
	}

 }
function jd_cal_checkCheckbox( $theFieldname,$theValue ){
	if( get_option( $theFieldname ) == $theValue ){
		echo 'checked="checked"';
	}
}
function jd_cal_checkSelect( $theFieldname,$theValue ) {
	if ( get_option( $theFieldname ) == $theValue) {
		echo 'selected="selected"';
	}
}

// Function to return a prefix which will allow the correct 
// placement of arguments into the query string.
function permalink_prefix() {
  // Get the permalink structure from WordPress
  $p_link = get_permalink();

  // Work out what the real URL we are viewing is
  $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : ""; 
  $protocol = substr(strtolower($_SERVER["SERVER_PROTOCOL"]), 0, strpos(strtolower($_SERVER["SERVER_PROTOCOL"]), "/")).$s;
  $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
  $real_link = $protocol.'://'.$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI'];

  // Now use all of that to get the correctly craft the My Calendar link prefix
  if (strstr($p_link, '?') && $p_link == $real_link) {
      $link_part = $p_link.'&';
    } else if ($p_link == $real_link) {
      $link_part = $p_link.'?';
    } else if (strstr($real_link, '?')) {
		if (isset($_GET['month']) && isset($_GET['yr'])) {
			$new_tail = split("&", $real_link);
			foreach ($new_tail as $item) {
				if (!strstr($item, 'month') && !strstr($item, 'yr')) {
					$link_part .= $item.'&amp;';
				}
			}
			if (!strstr($link_part, '?')) {
				$new_tail = split("month", $link_part);
				$link_part = $new_tail[0].'?'.$new_tail[1];
		    }
		} else {
		$link_part = $real_link.'&amp;';
		}
    } else {
      $link_part = $real_link.'?';
    }
  return $link_part;
}

// Configure the "Next" link in the calendar
function next_link($cur_year,$cur_month) {
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $next_year = $cur_year + 1;

$num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1) {  
	  if ($cur_month == 12) {
	      return '<a href="' . permalink_prefix() . 'month=jan&amp;yr=' . $next_year . '" rel="nofollow">'.__('Next Events','my-calendar').' &raquo;</a>';
	    } else {
	      $next_month = $cur_month + 1;
	      $month = $mod_rewrite_months[$next_month];
	      return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '" rel="nofollow">'.__('Next Events','my-calendar').' &raquo;</a>';
	    }
	} else {
		if (($cur_month + $num_months) > 12) {
		$next_month = ($cur_month + $num_months) - 12;
		} else {
		$next_month = $cur_month + $num_months;
		}
		$month = $mod_rewrite_months[$next_month];	
		if ($cur_month >= (12-$num_months)) {	  
		  return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $next_year . '" rel="nofollow">'.__('Next Events','my-calendar').' &raquo;</a>';
		} else {
		  return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '" rel="nofollow">'.__('Next Events','my-calendar').' &raquo;</a>';
		}	
	}
}

// Configure the "Previous" link in the calendar
function prev_link($cur_year,$cur_month) {
  $mod_rewrite_months = array(1=>'jan','feb','mar','apr','may','jun','jul','aug','sept','oct','nov','dec');
  $last_year = $cur_year - 1;
  
$num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1) {  
		if ($cur_month == 1) {
	      return '<a href="' . permalink_prefix() . 'month=dec&amp;yr='. $last_year .'" rel="nofollow">&laquo; '.__('Previous Events','my-calendar').'</a>';
	    } else {
	      $next_month = $cur_month - 1;
	      $month = $mod_rewrite_months[$next_month];
	      return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '" rel="nofollow">&laquo; '.__('Previous Events','my-calendar').'</a>';
	    }
	} else {
		if ($cur_month > $num_months) {
			$next_month = $cur_month - $num_months;
		} else {
			$next_month = ($cur_month - $num_months) + 12;
		}
		$month = $mod_rewrite_months[$next_month];	
		if ($cur_month <= $num_months) {	  
		  return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $last_year . '" rel="nofollow">&laquo; '.__('Previous Events','my-calendar').'</a>';
		} else {
		  return '<a href="' . permalink_prefix() . 'month='.$month.'&amp;yr=' . $cur_year . '" rel="nofollow">&laquo; '.__('Previous Events','my-calendar').'</a>';
		}	
	}	
}

// Used to draw multiple events
function draw_events($events, $type) {
  // We need to sort arrays of objects by time
  usort($events, "time_cmp");
	foreach($events as $event) {
		$output .= draw_event($event, $type);
	}
  return $output;
}

// Used to draw an event to the screen
function draw_event($event, $type="calendar") {
  global $wpdb;

  // My Calendar must be updated to run this function
  check_calendar();
                                     
  $display_author = get_option('display_author');
  $display_map = get_option('my_calendar_show_map');
  $display_address = get_option('my_calendar_show_address');
    $sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
    $cat_details = $wpdb->get_row($sql);
    $style = "background-color:".$cat_details->category_color.";";
	    if ($cat_details->category_icon != "") {
			$image = '<img src="'.WP_PLUGIN_URL.'/my-calendar/icons/'.$cat_details->category_icon.'" alt="" class="category-icon" style="background:'.$cat_details->category_color.';" />';
		} else {
			$image = "";
		}
    $location_string = $event->event_street.$event->event_street2.$event->event_city.$event->event_state.$event->event_postcode.$event->event_country;		
	if (($display_address == 'true' || $display_map == 'true') && strlen($location_string) > 5) {
		$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;	
		
		$address .= '<div class="address vcard">';
		
			if ($display_address == 'true' && strlen($location_string) > 5) {
				$address .= "<div class=\"adr\">";
					if ($event->event_label != "") {
						$address .= "<strong class=\"org\">".$event->event_label."</strong><br />";
					}					
					if ($event->event_street != "") {
						$address .= "<div class=\"street-address\">".$event->event_street."</div>";
					}
					if ($event->event_street2 != "") {
						$address .= "<div class=\"street-address\">".$event->event_street2."</div>";
					}
					if ($event->event_city != "") {
						$address .= "<span class=\"locality\">".$event->event_city.",</span>";
					}						
					if ($event->event_state != "") {
						$address .= " <span class=\"region\">".$event->event_state."</span> ";
					}
					if ($event->event_postcode != "") {
						$address .= " <span class=\"postal-code\">".$event->event_postcode."</span>";
					}	
					if ($event->event_country != "") {
						$address .= "<div class=\"country-name\">".$event->event_country."</div>";
					}	
				$address .= "</div>";			
			}
			if ($display_map == 'true') {
				if (strlen($location_string) > 5) {
					$map_string = str_replace(" ","+",$map_string);
					if ($event->event_label != "") {
						$map_label = $event->event_label;
					} else {
						$map_label = $event->event_title;
					}
					$map = "<a href=\"http://maps.google.com/maps?f=q&amp;z=15&amp;q=$map_string\">Map<span> to $map_label</span></a>";
					$address .= "<div class=\"url map\">$map</div>";
				}
			}
		$address .= "</div>";
	}

$my_calendar_directory = get_bloginfo( 'wpurl' ) . '/' . PLUGINDIR . '/' . dirname( plugin_basename(__FILE__) );

  $header_details .=  "\n<div class='$type-event'>\n";
		if ($type == "calendar") {
		$header_details .= "<h3 class='event-title'>$image".$event->event_title." <a href='#'><img src='$my_calendar_directory/images/event-details.png' alt='".__('Event Details','my-calendar')."' /></a></h3>\n";
		}	
	$header_details .= "<div class='details'>"; 
		if ($event->event_time != "00:00:00") {
			$header_details .= "<span class='event-time'>".date(get_option('time_format'), strtotime($event->event_time)) . "</span>\n";
		} else {
			$header_details .= "<span class='event-time'><abbr title='".__('Not Applicable','my-calendar')."'>".__('N/A','my-calendar')."</abbr></span>\n";
		}
		$header_details .= "<div class='sub-details'>";
		if ($type != "calendar") {
			$header_details .= "<h3 class='event-title'>$image".$event->event_title."</h3>\n";
		}
		if ($display_author == 'true') {
			$e = get_userdata($event->event_author);
			$header_details .= '<span class="event-author">'.__('Posted by', 'my-calendar').': <span class="author-name">'.$e->display_name."</span></span><br />\n		";
		}	
	if ($display_address == 'true' || $display_map == 'true') {
		$header_details .= $address;
	}
  
  if ($event->event_link != '') { $linky = $event->event_link; } else { $linky = '#'; }
	if ($linky != "#") {
  $details = "\n". $header_details . '' . wpautop($event->event_desc,1) . '<p><a href="'.$linky.'" class="event-link">' . $event->event_title . '&raquo; </a></p>'."</div></div></div>\n";
	} else {
  $details = "\n". $header_details . '' . wpautop($event->event_desc,1) . "</div></div></div>\n";	
	}
  return $details;
}
// used to generate upcoming events lists
function get_all_events() {
global $wpdb;
    $events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE);
	$date = date('Y').'-'.date('m').'-'.date('d');	 
    if (!empty($events)) {
        foreach($events as $event) {
			if ($event->event_recur != "S") {
				$orig_begin = $event->event_begin;
				$orig_end = $event->event_end;
				$numback = 0;
				$numforward = $event->event_repeats;				
				if ($event->event_repeats != 0) {				
					switch ($event->event_recur) {
						case "D":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = add_date($orig_begin,$i,0,0);
								$end = add_date($orig_end,$i,0,0);		
								${$i} = clone $event;
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "W":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = add_date($orig_begin,($i*7),0,0);
								$end = add_date($orig_end,($i*7),0,0);
								${$i} = clone $event;
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "M":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = add_date($orig_begin,0,$i,0);
								$end = add_date($orig_end,0,$i,0);
								${$i} = clone $event;
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "Y":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = add_date($orig_begin,0,0,$i);
								$end = add_date($orig_end,0,0,$i);
								${$i} = clone $event;
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
					}
				} else {
					switch ($event->event_recur) {
						case "D":
							$event_begin = $event->event_begin;
							$today = date('Y').'-'.date('m').'-'.date('d');
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							if ( date_comp($event_begin, $today) ) { // compare first date against today's date 	
								if (date_comp( $event_begin, add_date($this_date,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = add_date($event_begin,($realStart),0,0);
										if ( date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone $event;
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
							$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = add_date($event_begin,$realDays,0,0);
									if ( date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone $event;
										${$realDays}->event_begin = $this_date;
										$arr_events[] = ${$realDays};
									}
								}
							}
							} else {
								break;
							}							
						break;
						
						case "W":
							$event_begin = $event->event_begin;
							$today = date('Y').'-'.date('m').'-'.date('d');
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							
							if ( date_comp($event_begin, $today) ) { // compare first date against today's date 
								if (date_comp( $event_begin, add_date($this_date,-($nDays*7),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_weeks = $diff/(86400*7);
									$weeks = explode(".",$diff_weeks);
									$realStart = $weeks[0] - $nDays;
									$realFinish = $weeks[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = add_date($event_begin,($realStart*7),0,0);
										if ( date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone $event;
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = add_date($event_begin,($realDays*7),0,0);
									if ( date_comp( $event->event_begin,$this_date ) ) {
										${$realDays} = clone $event;
										${$realDays}->event_begin = $this_date;
										$arr_events[] = ${$realDays};
									}
								}
								}
							} else {
								break;
							}						
						break;
						
						case "M":
							$event_begin = $event->event_begin;
							$today = date('Y').'-'.date('m').'-'.date('d');
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							
							if ( date_comp($event_begin, $today) ) { // compare first date against today's date 	
								if (date_comp( $event_begin, add_date($this_date,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400*30);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = add_date($event_begin,0,$realStart,0);
										if ( date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone $event;
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = add_date($event_begin,0,$realDays,0);
									if ( date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone $event;
										${$realDays}->event_begin = $this_date;
										$arr_events[] = ${$realDays};
									}
								}
								}
							} else {
								break;
							}						
						break;
						
						case "Y":
							$event_begin = $event->event_begin;
							$today = date('Y').'-'.date('m').'-'.date('d');
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
								
							if ( date_comp($event_begin, $today) ) { // compare first date against today's date 		
								if (date_comp( $event_begin, add_date($this_date,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400*365);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = add_date($event_begin,0,0,$realStart);
										if ( date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone $event;
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = add_date($event_begin,0,0,$realDays);
									if ( date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone $event;
										${$realDays}->event_begin = $this_date;
										$arr_events[] = ${$realDays};
									}
								}
								}
							} else {
								break;
							}
						break;
					}
				}
			} else {
				$arr_events[]=$event;
			}					
		}				
	} 
	return $arr_events;
}
// Grab all events for the requested date from calendar
function grab_events($y,$m,$d,$category=null) {
     global $wpdb;

	 if ( $category!=null ) {
		if (is_numeric($category)) {
		$select_category = "event_category = $category AND";
		} else {
		$cat = $wpdb->get_row("SELECT category_id FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_name = '$category'");
		$category_id = $cat->category_id;
			if (!$category_id) {
				//if the requested category doesn't exist, fail silently
				$select_category = "";
			} else {
				$select_category = "event_category = $category_id AND";
			}
		}
	 }
     $arr_events = array();

     // Get the date format right
     $date = $y . '-' . $m . '-' . $d;
     
     // First we check for conventional events. These will form the first instance of a recurring event
     // or the only instance of a one-off event
     $events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_begin <= '$date' AND event_end >= '$date' AND event_recur = 'S' ORDER BY event_id");
     if (!empty($events)) {
         foreach($events as $event) {
			$arr_events[]=$event;
         }
     }

	// Deal with forever recurring year events
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0 ORDER BY event_id");

	if (!empty($events)) {
			foreach($events as $event) {
			// Technically we don't care about the years, but we need to find out if the 
			// event spans the turn of a year so we can deal with it appropriately.
			$year_begin = date('Y',strtotime($event->event_begin));
			$year_end = date('Y',strtotime($event->event_end));

				if ($year_begin == $year_end) {
					if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) && 
						date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
							$arr_events[]=$event;
					}
				} else if ($year_begin < $year_end) {
					if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) || 
						date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
							$arr_events[]=$event;
					}
				}
			}
     	}
	
	// Now the ones that happen a finite number of times
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (EXTRACT(YEAR FROM '$date')-EXTRACT(YEAR FROM event_begin)) <= event_repeats ORDER BY event_id");
	if (!empty($events)) {
       	  foreach($events as $event) {
	    // Technically we don't care about the years, but we need to find out if the 
	    // event spans the turn of a year so we can deal with it appropriately.
	    $year_begin = date('Y',strtotime($event->event_begin));
	    $year_end = date('Y',strtotime($event->event_end));

		    if ($year_begin == $year_end) {
				if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) && 
					date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
			      		$arr_events[]=$event;
				}
		    } else if ($year_begin < $year_end) {
				if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) || 
					date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
			      		$arr_events[]=$event;
				}
		    }
          }
     	}	
	// The monthly events that never stop recurring
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0 ORDER BY event_id");
	if (!empty($events)) {
       	  foreach($events as $event) {

	    // Technically we don't care about the years or months, but we need to find out if the 
	    // event spans the turn of a year or month so we can deal with it appropriately.
	    $month_begin = date('m',strtotime($event->event_begin));
	    $month_end = date('m',strtotime($event->event_end));

		    if ($month_begin == $month_end) {
				if (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) && 
					date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) {
			      		$arr_events[]=$event;
				}
		    } else if ($month_begin < $month_end) {
				if ( ($event->event_begin <= date('Y-m-d',strtotime($date))) && (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) || 
					date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) )	{
			      		$arr_events[]=$event;
				}
		    }
          }
     	}

	// Now the ones that happen a finite number of times
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date'),EXTRACT(YEAR_MONTH FROM event_begin))) <= event_repeats ORDER BY event_id");
	if (!empty($events)) {
       	  foreach($events as $event) {

	    // Technically we don't care about the years or months, but we need to find out if the 
	    // event spans the turn of a year or month so we can deal with it appropriately.
	    $month_begin = date('m',strtotime($event->event_begin));
	    $month_end = date('m',strtotime($event->event_end));

		    if ($month_begin == $month_end) {
				if (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) && 
					date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) {
				        $arr_events[]=$event;
				}
		    } else if ($month_begin < $month_end) {
				if ( ($event->event_begin <= date('Y-m-d',strtotime($date))) && (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) || 
					date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) ) {
			      		$arr_events[]=$event;
				}
		    }
          }
     	}

	/* 
	  Weekly - well isn't this fun! We need to scan all weekly events, find what day they fell on
	  and see if that matches the current day. If it does, we check to see if the repeats are 0. 
	  If they are, display the event, if not, we fast forward from the original day in week blocks 
	  until the number is exhausted. If the date we arrive at is in the future, display the event.
	*/

	// The weekly events that never stop recurring
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'W' AND '$date' >= event_begin AND event_repeats = 0 ORDER BY event_id");
	if (!empty($events))
     	{
       	  foreach($events as $event)
          {
	    // This is going to get complex so lets setup what we would place in for 
	    // an event so we can drop it in with ease

	    // Now we are going to check to see what day the original event
	    // fell on and see if the current date is both after it and on 
	    // the correct day. If it is, display the event!
	    $day_start_event = date('D',strtotime($event->event_begin));
	    $day_end_event = date('D',strtotime($event->event_end));
	    $current_day = date('D',strtotime($date));

	    $plan = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4,"Fri"=>5,"Sat"=>6,"Sun"=>7);

	    if ($plan[$day_start_event] > $plan[$day_end_event]) {
			if (($plan[$day_start_event] <= $plan[$current_day]) || ($plan[$current_day] <= $plan[$day_end_event]))	{
			$arr_events[]=$event;
	    	}
	    } else if (($plan[$day_start_event] < $plan[$day_end_event]) || ($plan[$day_start_event]== $plan[$day_end_event])) {
			if (($plan[$day_start_event] <= $plan[$current_day]) && ($plan[$current_day] <= $plan[$day_end_event]))	{
			$arr_events[]=$event;
	    	}		
	    }
	    
          }
     	}

	// The weekly events that have a limit on how many times they occur
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'W' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*7) >= (TO_DAYS('$date') - TO_DAYS(event_end)) ORDER BY event_id");
	if (!empty($events)) {
		foreach($events as $event) {

		// Now we are going to check to see what day the original event
		// fell on and see if the current date is both after it and on 
		// the correct day. If it is, display the event!
		$day_start_event = date('D',strtotime($event->event_begin));
		$day_end_event = date('D',strtotime($event->event_end));
		$current_day = date('D',strtotime($date));

		$plan = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4,"Fri"=>5,"Sat"=>6,"Sun"=>7);

			if ($plan[$day_start_event] > $plan[$day_end_event]) {
				if (($plan[$day_start_event] <= $plan[$current_day]) || ($plan[$current_day] <= $plan[$day_end_event]))	{
				$arr_events[]=$event;
				}
			} else if (($plan[$day_start_event] < $plan[$day_end_event]) || ($plan[$day_start_event]== $plan[$day_end_event])) {
				if (($plan[$day_start_event] <= $plan[$current_day]) && ($plan[$current_day] <= $plan[$day_end_event])) {
				$arr_events[]=$event;
				}		
			}

		}
    }
 
 
 // The daily events that never stop recurring
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'D' AND '$date' >= event_begin AND event_repeats = 0 ORDER BY event_id");
	if (!empty($events)) {
       	  foreach($events as $event) {
			// checking events which recur by day is easy: just shove 'em all in there!
			$arr_events[]=$event;
          }
     	}

	// The daily events that have a limit on how many times they occur
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'D' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats) >= (TO_DAYS('$date') - TO_DAYS(event_end)) ORDER BY event_id");
	if (!empty($events)) {
       	  foreach($events as $event) {
	   		// checking events which recur by day is easy: just shove 'em all in there!
			$arr_events[]=$event;
          }
     	}
	// end daily events
    return $arr_events;
}

function month_comparison($month) {
	$current_month = strtolower(date("M", time()));
	if (isset($_GET['yr']) && isset($_GET['month'])) {
		if ($month == $_GET['month']) {
			return ' selected="selected"';
		  }
	} elseif ($month == $current_month) { 
		return ' selected="selected"'; 
	}
}
function year_comparison($year) {
		$current_year = strtolower(date("Y", time()));
		if (isset($_GET['yr']) && isset($_GET['month'])) {
			if ($year == $_GET['yr']) {
				return ' selected="selected"';
			}
		} else if ($year == $current_year) {
			return ' selected="selected"';
		}
}
function build_date_switcher() {
	$my_calendar_body = "";
	$my_calendar_body .= '<div class="my-calendar-date-switcher">
            <form method="get" action=""><div>';
	$qsa = array();
	parse_str($_SERVER['QUERY_STRING'],$qsa);
	foreach ($qsa as $name => $argument) {
	    if ($name != 'month' && $name != 'yr') {
			$my_calendar_body .= '<input type="hidden" name="'.$name.'" value="'.$argument.'" />';
	    }
	  }
	// We build the months in the switcher
	$my_calendar_body .= '
            <label for="my-calendar-month">'.__('Month','my-calendar').':</label> <select id="my-calendar-month" name="month" style="width:100px;">
            <option value="jan"'.month_comparison('jan').'>'.__('January','my-calendar').'</option>
            <option value="feb"'.month_comparison('feb').'>'.__('February','my-calendar').'</option>
            <option value="mar"'.month_comparison('mar').'>'.__('March','my-calendar').'</option>
            <option value="apr"'.month_comparison('apr').'>'.__('April','my-calendar').'</option>
            <option value="may"'.month_comparison('may').'>'.__('May','my-calendar').'</option>
            <option value="jun"'.month_comparison('jun').'>'.__('June','my-calendar').'</option>
            <option value="jul"'.month_comparison('jul').'>'.__('July','my-calendar').'</option> 
            <option value="aug"'.month_comparison('aug').'>'.__('August','my-calendar').'</option> 
            <option value="sept"'.month_comparison('sept').'>'.__('September','my-calendar').'</option> 
            <option value="oct"'.month_comparison('oct').'>'.__('October','my-calendar').'</option> 
            <option value="nov"'.month_comparison('nov').'>'.__('November','my-calendar').'</option> 
            <option value="dec"'.month_comparison('dec').'>'.__('December','my-calendar').'</option> 
            </select>
            <label for="my-calendar-year">'.__('Year','my-calendar').':</label> <select id="my-calendar-year" name="yr">
';
	// The year builder is string mania. If you can make sense of this, you know your PHP!
	$past = 2;
	$future = 2;
	$fut = 1;
		while ($past > 0) {
		    $p .= '            <option value="';
		    $p .= date("Y",time())-$past;
		    $p .= '"'.year_comparison(date("Y",time())-$past).'>';
		    $p .= date("Y",time())-$past."</option>\n";
		    $past = $past - 1;
		}
		while ($fut < $future) {
		    $f .= '            <option value="';
		    $f .= date("Y",time())+$fut;
		    $f .= '"'.year_comparison(date("Y",time())+$fut).'>';
		    $f .= date("Y",time())+$fut."</option>\n";
		    $fut = $fut + 1;
		} 
	$my_calendar_body .= $p;
	$my_calendar_body .= '<option value="'.date("Y",time()).'"'.year_comparison(date("Y",time())).'>'.date("Y",time())."</option>\n";
	$my_calendar_body .= $f;
    $my_calendar_body .= '</select> <input type="submit" value="'.__('Go','my-calendar').'" /></div>
	</form></div>';
	return $my_calendar_body;
}

// Actually do the printing of the calendar
// Compared to searching for and displaying events
// this bit is really rather easy!
function my_calendar($name,$format,$category,$showkey) {
    global $wpdb;
	if ($category == "") {
	$category=null;
	}
    // First things first, make sure calendar is up to date
    check_calendar();

    // Deal with the week not starting on a monday
    if (get_option('start_of_week') == 0) {
		$name_days = array(1=>__('<abbr title="Sunday">Sun</abbr>','my-calendar'),__('<abbr title="Monday">Mon</abbr>','my-calendar'),__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),__('<abbr title="Thursday">Thur</abbr>','my-calendar'),__('<abbr title="Friday">Fri</abbr>','my-calendar'),__('<abbr title="Saturday">Sat</abbr>','my-calendar'));
    } else {
		// Choose Monday if anything other than Sunday is set
		$name_days = array(1=>__('<abbr title="Monday">Mon</abbr>','my-calendar'),__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),__('<abbr title="Thursday">Thur</abbr>','my-calendar'),__('<abbr title="Friday">Fri</abbr>','my-calendar'),__('<abbr title="Saturday">Sat</abbr>','my-calendar'),__('<abbr title="Sunday">Sun</abbr>','my-calendar'));
	}

    // Carry on with the script
    $name_months = array(1=>__('January','my-calendar'),__('February','my-calendar'),__('March','my-calendar'),__('April','my-calendar'),__('May','my-calendar'),__('June','my-calendar'),__('July','my-calendar'),__('August','my-calendar'),__('September','my-calendar'),__('October','my-calendar'),__('November','my-calendar'),__('December','my-calendar'));

    // If we don't pass arguments we want a calendar that is relevant to today
    if (empty($_GET['month']) || empty($_GET['yr'])) {
        $c_year = date("Y");
        $c_month = date("m");
        $c_day = date("d");
    }

    // Years get funny if we exceed 3000, so we use this check
    if ($_GET['yr'] <= 3000 && $_GET['yr'] >= 0) {
        // This is just plain nasty and all because of permalinks
        // which are no longer used, this will be cleaned up soon
        if ($_GET['month'] == 'jan' || $_GET['month'] == 'feb' || $_GET['month'] == 'mar' || $_GET['month'] == 'apr' || $_GET['month'] == 'may' || $_GET['month'] == 'jun' || $_GET['month'] == 'jul' || $_GET['month'] == 'aug' || $_GET['month'] == 'sept' || $_GET['month'] == 'oct' || $_GET['month'] == 'nov' || $_GET['month'] == 'dec') {
	       // Again nasty code to map permalinks into something
	       // databases can understand. This will be cleaned up
               $c_year = mysql_escape_string($_GET['yr']);
               if ($_GET['month'] == 'jan') { $t_month = 1; }
               else if ($_GET['month'] == 'feb') { $t_month = 2; }
               else if ($_GET['month'] == 'mar') { $t_month = 3; }
               else if ($_GET['month'] == 'apr') { $t_month = 4; }
               else if ($_GET['month'] == 'may') { $t_month = 5; }
               else if ($_GET['month'] == 'jun') { $t_month = 6; }
               else if ($_GET['month'] == 'jul') { $t_month = 7; }
               else if ($_GET['month'] == 'aug') { $t_month = 8; }
               else if ($_GET['month'] == 'sept') { $t_month = 9; }
               else if ($_GET['month'] == 'oct') { $t_month = 10; }
               else if ($_GET['month'] == 'nov') { $t_month = 11; }
               else if ($_GET['month'] == 'dec') { $t_month = 12; }
               $c_month = $t_month;
               $c_day = date("d");
        } else {
		// No valid month causes the calendar to default to today			
               $c_year = date("Y");
               $c_month = date("m");
               $c_day = date("d");
        }
    } else {
		// No valid year causes the calendar to default to today	
        $c_year = date("Y");
        $c_month = date("m");
        $c_day = date("d");
    }

    // Fix the days of the week if week start is not on a monday
	if (get_option('start_of_week') == 0) {
		$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
        $first_weekday = ($first_weekday==0?1:$first_weekday+1);
      } else {
		$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
		$first_weekday = ($first_weekday==0?7:$first_weekday);
      }

    $days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
	if ($category != "" && $category != "all") {
		$category_label = $category . ' ';
	} else {
		$category_label = "";
	}
    // Start the calendar and add header and navigation
		$my_calendar_body .= "<div id=\"jd-calendar\">";
		// Add the calendar table and heading
		if ($format == "calendar") {
		$my_calendar_body .= '<div class="my-calendar-header">';

	    // We want to know if we should display the date switcher
	    $date_switcher = get_option('display_jump');

	    if ($date_switcher == 'true') {
			$my_calendar_body .= build_date_switcher();
		}

	    // The header of the calendar table and the links. Note calls to link functions
	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev">' . prev_link($c_year,$c_month) . '</li>
	                    <li class="my-calendar-next">' . next_link($c_year,$c_month) . '</li>
						</ul>
	                    </div>
					</div>';		
			$my_calendar_body .= "\n<table class=\"my-calendar-table\" summary=\"$category_label".__('Calendar','my-calendar')."\">\n";
			$my_calendar_body .= '<caption class="my-calendar-month">'.$name_months[(int)$c_month].' '.$c_year."</caption>\n";
		} else {
			$my_calendar_body .= "\n<h2 class=\"my-calendar-header\">$category_label".__('Calendar','my-calendar')."</h2>\n";

			$num_months = get_option('my_calendar_show_months');
			if ($num_months <= 1) {			
			$my_calendar_body .= '<h3 class="my-calendar-month">'.__('Events in','my-calendar').' '.$name_months[(int)$c_month].' '.$c_year."</h3>\n";
			} else {
			$my_calendar_body .= '<h3 class="my-calendar-month">'.$name_months[(int)$c_month].'&thinsp;&ndash;&thinsp;'.$name_months[(int)$c_month+$num_months-1].' '.$c_year."</h3>\n";			
			}
		$my_calendar_body .= '<div class="my-calendar-header">';

	    // We want to know if we should display the date switcher
	    $date_switcher = get_option('display_jump');

	    if ($date_switcher == 'true') {
			$my_calendar_body .= build_date_switcher();
		}

	    // The header of the calendar table and the links. Note calls to link functions
	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev">' . prev_link($c_year,$c_month) . '</li>
	                    <li class="my-calendar-next">' . next_link($c_year,$c_month) . '</li>
						</ul>
	                    </div>
					</div>';	
	}
    // If in calendar format, print the headings of the days of the week
	//$my_calendar_body .= "$format, $category, $name";
if ($format == "calendar") {
    $my_calendar_body .= "<thead>\n<tr>\n";
    for ($i=1; $i<=7; $i++) {
	// Colors need to be different if the starting day of the week is different
	
		if (get_option('start_of_week') == 0) {
		    $my_calendar_body .= '<th scope="col" class="'.($i<7&&$i>1?'day-heading':'weekend-heading').'">'.$name_days[$i]."</th>\n";
		} else {
		    $my_calendar_body .= '<th scope="col" class="'.($i<6?'day-heading':'weekend-heading').'">'.$name_days[$i]."</th>\n";
		}
	}	
    $my_calendar_body .= "</tr>\n</thead>\n<tbody>";

    for ($i=1; $i<=$days_in_month;) {
        $my_calendar_body .= '<tr>';
        for ($ii=1; $ii<=7; $ii++) {
            if ($ii==$first_weekday && $i==1) {
				$go = TRUE;
			} elseif ($i > $days_in_month ) {
				$go = FALSE;
			}

            if ($go) {
		// Colors again, this time for the day numbers
				if (get_option('start_of_week') == 0) {
				    // This bit of code is for styles believe it or not.
				    $grabbed_events = grab_events($c_year,$c_month,$i,$category);
				    $no_events_class = '';
					    if (!count($grabbed_events)) {
							$no_events_class = ' no-events';
					    }
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd")?'current-day':'day-with-date').$no_events_class.'">'."\n	".'<span'.($ii<7&&$ii>1?'':' class="weekend"').'>'.$i++.'</span>'."\n		". draw_events($grabbed_events, $format) . "\n</td>\n";
				} else {
				    $grabbed_events = grab_events($c_year,$c_month,$i,$category);
				    $no_events_class = '';
			            if (!count($grabbed_events))
				      {
					$no_events_class = ' no-events';
				      }
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd")?'current-day':'day-with-date').$no_events_class.'">'."\n	".'<span'.($ii<6?'':' class="weekend"').'>'.$i++.'</span>'."\n		". draw_events($grabbed_events, $format) . "\n</td>\n";
				}
	      } else {
			$my_calendar_body .= "<td class='day-without-date'>&nbsp;</td>\n";
	      }
        }
        $my_calendar_body .= "</tr>";
    }
	$my_calendar_body .= "\n</tbody>\n</table>";
} else if ($format == "list") {
	$my_calendar_body .= "<ul id=\"calendar-list\">";
// show calendar as list
$date_format = get_option('my_calendar_date_format');
if ($date_format == "") {
	$date_format = "l, F j, Y";
}
	$num_months = get_option('my_calendar_show_months');
	$num_events = 0;
	for ($m=0;$m<$num_months;$m++) {
		if ($m == 0) {
			$add_month = 0;
		} else {
			$add_month = 1;
		}
		$c_month = (int) $c_month + $add_month;
	    for ($i=1; $i<=$days_in_month; $i++) {
			$grabbed_events = grab_events($c_year,$c_month,$i,$category);
			if (count($grabbed_events)) {
				if ( get_option('list_javascript') != 1) {
					$is_anchor = "<a href='#'>";
					$is_close_anchor = "</a>";
				} else {
					$is_anchor = $is_close_anchor = "";
				}
				$my_calendar_body .= "<li$class><strong class=\"event-date\">$is_anchor".date($date_format,mktime(0,0,0,$c_month,$i,$c_year))."$is_close_anchor</strong>".draw_events($grabbed_events, $format)."</li>";
				$num_events++;
			} 	
			if (is_odd($num_events)) {
				$class = " class='odd'";
			} else {
				$class = "";
			}		
		}		
	}
	if ($num_events == 0) {
		$my_calendar_body .= "<li class='no-events'>".__('There are no events scheduled during this period.','my-calendar') . "</li>";
	}
	$my_calendar_body .= "</ul>";
} else {
	$my_calendar_body .= "Unrecognized calendar format.";
}	
    if ($showkey != 'no') {
		$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_name ASC";
		$cat_details = $wpdb->get_results($sql);
        $my_calendar_body .= '<div class="category-key">
		<h3>'.__('Category Key','my-calendar')."</h3>\n<ul>\n";
        foreach($cat_details as $cat_detail) {
			if ($cat_detail->category_icon != "") {
			$my_calendar_body .= '<li><span class="category-color-sample"><img src="'.WP_PLUGIN_URL.'/my-calendar/icons/'.$cat_detail->category_icon.'" alt="" style="background:'.$cat_detail->category_color.';" /></span>'.$cat_detail->category_name."</li>\n";
			} else {
			$my_calendar_body .= '<li><span class="category-color-sample" style="background:'.$cat_detail->category_color.';"> &nbsp; </span>'.$cat_detail->category_name."</li>\n";			
			}
		}
        $my_calendar_body .= "</ul>\n</div>";
      }
	$my_calendar_body .= "\n</div>";
    // The actual printing is done by the shortcode function.
    return $my_calendar_body;
}

function is_odd( $int ) {
  return( $int & 1 );
}

?>