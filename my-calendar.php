<?php
/*
Plugin Name: My Calendar
Plugin URI: http://www.joedolson.com/articles/my-calendar/
Description: Accessible WordPress event calendar plugin. Show events from multiple calendars on pages, in posts, or in widgets.
Author: Joseph C Dolson
Author URI: http://www.joedolson.com
Version: 1.4.4
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
global $wpdb;
// Define the tables used in My Calendar
define('MY_CALENDAR_TABLE', $wpdb->prefix . 'my_calendar');
define('MY_CALENDAR_CATEGORIES_TABLE', $wpdb->prefix . 'my_calendar_categories');
define('MY_CALENDAR_LOCATIONS_TABLE', $wpdb->prefix . 'my_calendar_locations');

// Define other plugin constants
$my_calendar_directory = get_bloginfo( 'wpurl' ) . '/' . PLUGINDIR . '/' . dirname( plugin_basename(__FILE__) );
define( 'MY_CALENDAR_DIRECTORY', $my_calendar_directory );

// Create a master category for My Calendar and its sub-pages
add_action('admin_menu', 'my_calendar_menu');
// Add the function that puts style information in the header
add_action('wp_head', 'my_calendar_wp_head');
// Add the function that deals with deleted users
add_action('delete_user', 'mc_deal_with_deleted_user');
// Add the widgets if we are using version 2.8
add_action('widgets_init', 'init_my_calendar_today');
add_action('widgets_init', 'init_my_calendar_upcoming');

register_activation_hook( __FILE__, 'check_my_calendar' );
// add filters to text widgets which will process shortcodes
add_filter( 'widget_text', 'do_shortcode', 9 );

function jd_calendar_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/my-calendar.php'))
		$links[] = "<a href='admin.php?page=my-calendar-config'>" . __('Settings', 'my-calendar') . "</a>";
		$links[] = "<a href='admin.php?page=my-calendar-help'>" . __('Help', 'my-calendar') . "</a>";
	return $links;
}
add_filter('plugin_action_links', 'jd_calendar_plugin_action', -10, 2);

include(dirname(__FILE__).'/my-calendar-settings.php' );
include(dirname(__FILE__).'/my-calendar-categories.php' );
include(dirname(__FILE__).'/my-calendar-locations.php' );
include(dirname(__FILE__).'/my-calendar-help.php' );
include(dirname(__FILE__).'/my-calendar-event-manager.php' );
include(dirname(__FILE__).'/my-calendar-styles.php' );
include(dirname(__FILE__).'/my-calendar-widgets.php' );
include(dirname(__FILE__).'/date-utilities.php' );
include(dirname(__FILE__).'/my-calendar-install.php' );
include(dirname(__FILE__).'/my-calendar-upgrade-db.php' );


// Before we get on with the functions, we need to define the initial style used for My Calendar

function jd_show_support_box() {
?>
<div class="resources">
<ul>
<li><a href="http://mywpworks.com/wp-plugin-guides/my-calendar-plugin-beginners-guide/">Buy the Beginner's Guide</a></li>
<li><a href="http://www.joedolson.com/articles/my-calendar/"><?php _e("Get Support",'my-calendar'); ?></a></li>
<li><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-help"><?php _e("My Calendar Help",'my-calendar'); ?></a></li>
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
function mc_deal_with_deleted_user($id) {
  global $wpdb;
  // This wouldn't work unless the database was up to date. Lets check.
  check_my_calendar();
  // Do the query
  $wpdb->get_results( "UPDATE ".MY_CALENDAR_TABLE." SET event_author=".$wpdb->get_var("SELECT MIN(ID) FROM ".$wpdb->prefix."users",0,0)." WHERE event_author=".$id );
}

// Function to add the calendar style into the header
function my_calendar_wp_head() {
  global $wpdb, $wp_query;
  // If the calendar isn't installed or upgraded this won't work
  check_my_calendar();
  $styles = stripcslashes(get_option('my_calendar_style'));
	if ( get_option('my_calendar_use_styles') != 'true' ) {
	
		$this_post = $wp_query->get_queried_object();
		if (is_object($this_post)) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'my_calendar_show_css' ) != '' ) {
		$array = explode( ",",get_option( 'my_calendar_show_css' ) );
			if (!is_array($array)) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || get_option( 'my_calendar_show_css' ) == '' ) {
	
// generate category colors
$categories = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");
	foreach ( $categories as $category ) {
			$class = sanitize_title($category->category_name);
			$color = $category->category_color;
		if ( get_option( 'mc_apply_color' ) == 'font' ) {
			$type = 'color';
		} else if ( get_option( 'mc_apply_color' ) == 'background' ) {
			$type = 'background';
		}
		$category_styles .= "\n#jd-calendar .$class { $type: $color; }";
	}	
	
echo "
<style type=\"text/css\">
<!--
.js #jd-calendar .details { display: none; }
// Styles from My Calendar - Joseph C Dolson http://www.joedolson.com/
$styles

$category_styles
-->
</style>";

		}
	}
}

// Function to deal with adding the calendar menus
function my_calendar_menu() {
  global $wpdb;
  // We make use of the My Calendar tables so we must have installed My Calendar
  check_my_calendar();
  // Set admin as the only one who can use My Calendar for security
  $allowed_group = 'manage_options';
  // Use the database to *potentially* override the above if allowed
  $allowed_group = get_option('can_manage_events');


  // Add the admin panel pages for My Calendar. Use permissions pulled from above
	if (function_exists('add_menu_page')) {
		add_menu_page(__('My Calendar','my-calendar'), __('My Calendar','my-calendar'), $allowed_group, 'my-calendar', 'edit_my_calendar');
	}
	if (function_exists('add_submenu_page')) {
		add_submenu_page('my-calendar', __('Add/Edit Events','my-calendar'), __('Add/Edit Events','my-calendar'), $allowed_group, 'my-calendar', 'edit_my_calendar');
		add_action( "admin_head", 'my_calendar_write_js' );		
		add_action( "admin_head", 'my_calendar_add_styles' );
		// Note only admin can change calendar options
		add_submenu_page('my-calendar', __('Manage Categories','my-calendar'), __('Manage Categories','my-calendar'), 'manage_options', 'my-calendar-categories', 'my_calendar_manage_categories');
		add_submenu_page('my-calendar', __('Manage Locations','my-calendar'), __('Manage Locations','my-calendar'), 'manage_options', 'my-calendar-locations', 'my_calendar_manage_locations');		
		add_submenu_page('my-calendar', __('Settings','my-calendar'), __('Settings','my-calendar'), 'manage_options', 'my-calendar-config', 'edit_my_calendar_config');
		add_submenu_page('my-calendar', __('Style Editor','my-calendar'), __('Style Editor','my-calendar'), 'manage_options', 'my-calendar-styles', 'edit_my_calendar_styles');
		add_submenu_page('my-calendar', __('My Calendar Help','my-calendar'), __('Help','my-calendar'), 'manage_options', 'my-calendar-help', 'my_calendar_help');		
	}
}
add_action( "admin_menu", 'my_calendar_add_javascript' );

// Function to add the javascript to the admin header
function my_calendar_add_javascript() { 
	if ($_GET['page'] == 'my-calendar') {
		wp_enqueue_script('jquery-ui-datepicker',WP_PLUGIN_URL . '/my-calendar/js/ui.datepicker.js', array('jquery','jquery-ui-core') );
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
}
function my_calendar_add_display_javascript() {
	wp_enqueue_script('jquery');
}
add_action('init','my_calendar_add_display_javascript');

function my_calendar_fouc() {
	if ( get_option('calendar_javascript') != 1 || get_option('list_javascript') != 1 || get_option('mini_javascript') != 1 ) {
		$scripting = "\n<script type='text/javascript'>\n";
		$scripting .= "var \$mc = jQuery.noConflict();\n";
		$scripting .= "\$mc('html').addClass('js');\n";
		$scripting .= "\$mc(document).ready(function() { \$mc('html').removeClass('js') });\n";
		$scripting .= "</script>\n";
	}
	echo $scripting;
}

function my_calendar_calendar_javascript() {
  global $wpdb, $wp_query;

	if ( get_option('calendar_javascript') != 1 || get_option('list_javascript') != 1 || get_option('mini_javascript') != 1 ) {
	  
	$list_js = stripcslashes( get_option( 'my_calendar_listjs' ) );
	$cal_js = stripcslashes( get_option( 'my_calendar_caljs' ) );
	$mini_js = stripcslashes( get_option( 'my_calendar_minijs' ) );

		$this_post = $wp_query->get_queried_object();
		if (is_object($this_post)) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'my_calendar_show_css' ) != '' ) {
		$array = explode( ",",get_option( 'my_calendar_show_css' ) );
			if (!is_array($array)) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || get_option( 'my_calendar_show_css' ) == '' ) {
			$scripting = "<script type='text/javascript'>\n";
			if ( get_option('calendar_javascript') != 1 ) {	$scripting .= "\n".$cal_js; }
			if ( get_option('list_javascript') != 1 ) {	$scripting .= "\n".$list_js; }
			if ( get_option('mini_javascript') != 1 ) {	$scripting .= "\n".$mini_js; }
			$scripting .= "</script>";
			echo $scripting;
		}
	}	
}
add_action('wp_footer','my_calendar_calendar_javascript');
add_action('wp_head','my_calendar_fouc');

function my_calendar_add_styles() {

	echo '<link type="text/css" rel="stylesheet" href="'.WP_PLUGIN_URL.'/my-calendar/js/ui.datepicker.css" />';

	echo '  
<style type="text/css">
<!--
.jd-my-calendar {
margin-right: 190px!important;
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
.import {
background: #ffa;
padding: 5px 10px;
border: 1px solid #aaa;
-moz-border-radius: 5px;
-webkit-border-radius: 5px;
border-radius: 5px;
margin: 15px 0;
}
.n4 {width: 32px;}
.n5 {width: 32px;}
.n6 {width: 64px;}
.n7 {width: 128px;}
.n8 {width: 256px;}
.category-color {
width: 1.2em;
height: 1.2em;
display: inline-block;
-moz-border-radius: 3px;
-webkit-border-radius: 3px;
border-radius: 3px;
border: 1px solid #000;
}
//-->
</style>';
}

function my_calendar_insert($atts) {
	extract(shortcode_atts(array(
				'name' => 'all',
				'format' => 'calendar',
				'category' => 'all',
				'showkey' => 'yes'
			), $atts));
	if ( isset($_GET['format']) ) {
		$format = mysql_real_escape_string($_GET['format']);
	}
	return my_calendar($name,$format,$category,$showkey);
}

function my_calendar_insert_upcoming($atts) {
	extract(shortcode_atts(array(
				'before' => 'default',
				'after' => 'default',
				'type' => 'default',
				'category' => 'default',
				'template' => 'default'
			), $atts));
	return my_calendar_upcoming_events($before, $after, $type, $category, $template);
}

function my_calendar_insert_today($atts) {
	extract(shortcode_atts(array(
				'category' => 'default',
				'template' => 'default'
			), $atts));
	return my_calendar_todays_events($category, $template);
}


// add shortcode interpreter
add_shortcode('my_calendar','my_calendar_insert');
add_shortcode('my_calendar_upcoming','my_calendar_insert_upcoming');
add_shortcode('my_calendar_today','my_calendar_insert_today');

// Function to check what version of My Calendar is installed and install if needed
function check_my_calendar() {
	global $wpdb, $initial_style, $initial_listjs, $initial_caljs, $initial_minijs, $mini_styles;
	$current_version = get_option('my_calendar_version');
	// If current version matches, don't bother running this.
	if ($current_version == '1.4.4') {
		return true;
	}

  // Lets see if this is first run and create a table if it is!
  // Assume this is not a new install until we prove otherwise
  $new_install = false;
  $my_calendar_exists = false;
  $upgrade_path = array();
  
  // Determine the calendar version
  $tables = $wpdb->get_results("show tables;");
	foreach ( $tables as $table ) {
      foreach ( $table as $value )  {
		  if ( $value == MY_CALENDAR_TABLE ) {
		      $my_calendar_exists = true;
			  // check whether installed version matches most recent version, establish upgrade process.
		    } 
       }
    }
	
	if ( $my_calendar_exists == false ) {
      $new_install = true;
	// for each release requiring an upgrade path, add a version compare. Loop will run every relevant upgrade cycle.
    } else if ( version_compare( $current_version,"1.3.0","<" ) ) {
		$upgrade_path[] = "1.3.0";
	} else if ( version_compare( $current_version,"1.3.8","<" ) ) {
		$upgrade_path[] = "1.3.8";
	} else if ( version_compare( $current_version, "1.4.0", "<" ) ) {
		$upgrade_path[] = "1.4.0";
	} 
	
	// having determined upgrade path, assign new version number
	update_option( 'my_calendar_version' , '1.4.4' );

	// Now we've determined what the current install is or isn't 
	if ( $new_install == true ) {
		  //add default settings
		mc_default_settings();
		$sql = "UPDATE " . MY_CALENDAR_TABLE . " SET event_category=1";
		$wpdb->get_results($sql);
		$sql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_color='#ffffff', category_icon='event.png'";
		$wpdb->get_results($sql);
    } 
			
// switch for different upgrade paths
	foreach ($upgrade_path as $upgrade) {
		switch ($upgrade) {
			case '1.3.0':
				add_option('my_calendar_listjs',$initial_listjs);
				add_option('my_calendar_caljs',$initial_caljs);
				add_option('my_calendar_show_heading','true');  
			break;
			case '1.3.8':
				update_option('my_calendar_show_css','');
			break;
			case '1.4.0':
			// change tables					
				add_option( 'mc_db_version', '1.4.0' );
				add_option( 'mc_event_link_expires','false' );
				add_option( 'mc_apply_color','default' );
				add_option( 'my_calendar_minijs', $initial_minijs);
				add_option( 'mini_javascript', 1);
				upgrade_db();
			break;
			default:
			break;
		}
}
	/* 
	if the user has fully uninstalled the plugin but kept the database of events, this will restore default 
	settings and upgrade db if needed.
	*/
	if ( get_option( 'my_calendar_uninstalled' ) == 'true' ) {
		mc_default_settings();	
		update_option( 'mc_db_version', '1.4.0' );
	}
	// check whether mini version styles exist in current styles, if not, add them
	if (strpos(get_option('my_calendar_style'),"mini-event") === false) {
		$cur_styles = get_option('my_calendar_style')."\n".$mini_styles;
		update_option('my_calendar_style',$cur_styles);
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
function my_calendar_permalink_prefix() {
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
function my_calendar_next_link($cur_year,$cur_month,$format) {
  $next_year = $cur_year + 1;
  $next_events = ( get_option( 'mc_next_events') == '' )?"Next events":stripcslashes( get_option( 'mc_next_events') );
  $num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1 || $format=="calendar") {  
	  if ($cur_month == 12) {
	      return '<a href="' . my_calendar_permalink_prefix() . 'month=1&amp;yr=' . $next_year . '#jd-calendar" rel="nofollow">'.$next_events.' &raquo;</a>';
	    } else {
	      $next_month = $cur_month + 1;
	      return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $cur_year . '#jd-calendar" rel="nofollow">'.$next_events.' &raquo;</a>';
	    }
	} else {
		$next_month = (($cur_month + $num_months) > 12)?(($cur_month + $num_months) - 12):($cur_month + $num_months);
		if ($cur_month >= (12-$num_months)) {	  
		  return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $next_year . '#jd-calendar" rel="nofollow">'.$next_events.' &raquo;</a>';
		} else {
		  return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $cur_year . '#jd-calendar" rel="nofollow">'.$next_events.' &raquo;</a>';
		}	
	}
}

// Configure the "Previous" link in the calendar
function my_calendar_prev_link($cur_year,$cur_month,$format) {
  $last_year = $cur_year - 1;
  $previous_events = ( get_option( 'mc_previous_events') == '' )?"Previous events":stripcslashes( get_option( 'mc_previous_events') );
  $num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1 || $format=="calendar") {  
		if ($cur_month == 1) {
	      return '<a href="' . my_calendar_permalink_prefix() . 'month=12&amp;yr='. $last_year .'#jd-calendar" rel="nofollow">&laquo; '.$previous_events.'</a>';
	    } else {
	      $next_month = $cur_month - 1;
	      return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $cur_year . '#jd-calendar" rel="nofollow">&laquo; '.$previous_events.'</a>';
	    }
	} else {
		$next_month = ($cur_month > $num_months)?($cur_month - $num_months):(($cur_month - $num_months) + 12);
		if ($cur_month <= $num_months) {	  
		  return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $last_year . '#jd-calendar" rel="nofollow">&laquo; '.$previous_events.'</a>';
		} else {
		  return '<a href="' . my_calendar_permalink_prefix() . 'month='.$next_month.'&amp;yr=' . $cur_year . '#jd-calendar" rel="nofollow">&laquo; '.$previous_events.'</a>';
		}	
	}	
}

// Used to draw multiple events
function my_calendar_draw_events($events, $type) {
  // We need to sort arrays of objects by time
  usort($events, "my_calendar_time_cmp");
	if ($type == "mini" && count($events)>0) {
		$output .= "<div class='calendar-events'>";
	}
	foreach($events as $event) {
		$output .= my_calendar_draw_event($event, $type);
	}
	if ($type == "mini" && count($events)>0) {
		$output .= "</div>";
	}	
  return $output;
}
// Used to draw an event to the screen
function my_calendar_draw_event($event, $type="calendar") {
  global $wpdb, $categories;
  // My Calendar must be updated to run this function
  check_my_calendar();
                                     
  $display_author = get_option('display_author');
  $display_map = get_option('my_calendar_show_map');
  $display_address = get_option('my_calendar_show_address');
	$this_category = $event->event_category; 
	foreach ($categories as $key=>$value) {
		if ($value->category_id == $this_category) {
			$cat_details = $categories[$key];
		} 
	}  
	$category = sanitize_title( $cat_details->category_name );
	if ( get_option('my_calendar_hide_icons')=='true' ) {
		$image = "";
	} else {
	    if ($cat_details->category_icon != "") {
			$path = ( file_exists( WP_PLUGIN_DIR . '/my-calendar-custom/' ) )?'/my-calendar-custom' : '/my-calendar/icons';
			$image = '<img src="'.WP_PLUGIN_URL.$path.'/'.$cat_details->category_icon.'" alt="" class="category-icon" style="background:'.$cat_details->category_color.';" />';
		} else {
			$image = "";
		}
	}
    $location_string = $event->event_street.$event->event_street2.$event->event_city.$event->event_state.$event->event_postcode.$event->event_country;
	// put together address information as vcard
	if (($display_address == 'true' || $display_map == 'true') && strlen($location_string) > 0 ) {
		$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;	
		$address .= '<div class="address vcard">';
			if ($display_address == 'true') {
				$address .= "<div class=\"adr\">";
					if ($event->event_label != "") {
						$address .= "<strong class=\"org\">".stripslashes($event->event_label)."</strong><br />";
					}					
					if ($event->event_street != "") {
						$address .= "<div class=\"street-address\">".stripslashes($event->event_street)."</div>";
					}
					if ($event->event_street2 != "") {
						$address .= "<div class=\"street-address\">".stripslashes($event->event_street2)."</div>";
					}
					if ($event->event_city != "") {
						$address .= "<span class=\"locality\">".stripslashes($event->event_city).",</span>";
					}						
					if ($event->event_state != "") {
						$address .= " <span class=\"region\">".stripslashes($event->event_state)."</span> ";
					}
					if ($event->event_postcode != "") {
						$address .= " <span class=\"postal-code\">".stripslashes($event->event_postcode)."</span>";
					}	
					if ($event->event_country != "") {
						$address .= "<div class=\"country-name\">".stripslashes($event->event_country)."</div>";
					}	
				$address .= "</div>";			
			}
			if ($display_map == 'true' && strlen($location_string) > 0 ) {
					$map_string = str_replace(" ","+",$map_string);
					if ($event->event_label != "") {
						$map_label = stripslashes($event->event_label);
					} else {
						$map_label = stripslashes($event->event_title);
					}
					$zoom = ($event->event_zoom != 0)?$event->event_zoom:'15';
					
					if ($event->event_longitude != '0.000000' && $event->event_latitude != '0.000000') {
						$map_string = "$event->event_longitude,$event->event_latitude";
					}
					
					$map = "<a href=\"http://maps.google.com/maps?f=q&amp;z=$zoom&amp;q=$map_string\">Map<span> to $map_label</span></a>";
					$address .= "<div class=\"url map\">$map</div>";
			}
		$address .= "</div>";
	}

    $header_details .=  "\n<div class='$type-event'>\n";
		if ( get_option('mc_show_link_on_title') == 'true' ) { // this doesn't exist yet.
			if ( $event->event_link_expires == 0 ) {
				$event_link = $event->event_link;
			} else {
				if ( my_calendar_date_comp( $event->event_begin,date_i18n('Y-m-d',time() ) ) ) {
					$event_link = '';
				} else {
					$event_link = $event->event_link;		
				}
			}  
			if ($event_link != '') {
				$mytitle = '<a href="'.$event_link.'" class="my-link">'.stripslashes($event->event_title).' &raquo; </a>';
			} else {
				$mytitle = stripslashes($event->event_title);	
			}	
		} else {
			$mytitle = stripslashes($event->event_title);
		}
	if ($type == 'calendar') { 
		$toggle = " <a href='#' class='mc-toggle mc-expand'><img src='".MY_CALENDAR_DIRECTORY."/images/event-details.png' alt='".__('Event Details','my-calendar')."' /></a>";
	} else {
		$toggle = "";
	}
	if ($type != 'list') {
	$header_details .= "<h3 class='event-title $category'>$image".$mytitle."$toggle</h3>\n";
	}

	$header_details .= "<div class='details'>"; 
	if ( $type == "calendar" ) { $header_details .= "<h3 class='close'><a href='#' class='mc-toggle mc-close'><img src='".MY_CALENDAR_DIRECTORY."/images/event-close.png' alt='".__('Close','my-calendar')."' /></a></h3>"; }
		if ( $event->event_time != "00:00:00" && $event->event_time != '' ) {
			$header_details .= "<span class='event-time'>".date_i18n(get_option('time_format'), strtotime($event->event_time));
			if ($event->event_endtime != "00:00:00" && $event->event_endtime != '' ) {
				$header_details .= "<span class='time-separator'>&thinsp;&ndash;&thinsp;</span><span class='end-time'>".date_i18n(get_option('time_format'), strtotime($event->event_endtime))."</span>";
			}
			$header_details .= "</span>\n";
		} else {
			$header_details .= "<span class='event-time'>";
				if ( get_option('my_calendar_notime_text') == '' || get_option('my_calendar_notime_text') == "N/A" ) { 
				$header_details .= "<abbr title='".__('Not Applicable','my-calendar')."'>".__('N/A','my-calendar')."</abbr>\n"; 
				} else {
				$header_details .= get_option('my_calendar_notime_text');
				}
			$header_details .= "</span>";
		}
		$header_details .= "<div class='sub-details'>";
		if ($type == "list") {
			$header_details .= "<h3 class='event-title'>$image".$mytitle."</h3>\n";
		}
		if ($display_author == 'true') {
			$e = get_userdata($event->event_author);
			$header_details .= '<span class="event-author">'.__('Posted by', 'my-calendar').': <span class="author-name">'.$e->display_name."</span></span><br />\n		";
		}	
	if (($display_address == 'true' || $display_map == 'true') && strlen($location_string) > 0 ) {
		$header_details .= $address;
	}
  // handle link expiration
	if ( $event->event_link_expires == 0 ) {
		$event_link = $event->event_link;
	} else {
		if ( my_calendar_date_comp( $event->event_begin,date_i18n('Y-m-d',time() ) ) ) {
			$event_link = '';
		} else {
			$event_link = $event->event_link;		
		}
	}
  
	if ($event_link != '') {
		$details = "\n". $header_details . '' . wpautop(stripcslashes($event->event_desc),1) . '<p><a href="'.$event_link.'" class="event-link">' . stripslashes($event->event_title) . '&raquo; </a></p>'."</div></div></div>\n";
	} else {
		$details = "\n". $header_details . '' . wpautop(stripcslashes($event->event_desc),1) . "</div></div></div>\n";	
	}
  return $details;
}
function mc_select_category($category, $type='event') {
global $wpdb;
	if ( strpos( $category, "|" ) ) {
		$categories = explode( "|", $category );
		$numcat = count($categories);
		$i = 1;
		foreach ($categories as $key) {
			if ( is_numeric($key) ) {
				if ($i == 1) {
					$select_category .= ($type=='all')?" WHERE (":' (';
				}				
				$select_category .= " event_category = $key";
				if ($i < $numcat) {
					$select_category .= " OR ";
				} else if ($i == $numcat) {
					$select_category .= ($type=='all')?") ":' ) AND';
				}
			$i++;
			} else {
				$cat = $wpdb->get_row("SELECT category_id FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_name = '$key'");
				$category_id = $cat->category_id;
				if ($i == 1) {
					$select_category .= ($type=='all')?" WHERE (":' (';
				}
				$select_category .= " event_category = $category_id";
				if ($i < $numcat) {
					$select_category .= " OR ";
				} else if ($i == $numcat) {
					$select_category .= ($type=='all')?") ":' ) AND';
				}
				$i++;						
			}
		}
	} else {	 
		if (is_numeric($category)) {
		$select_category = ($type=='all')?" WHERE event_category = $category":" event_category = $category AND";
		} else {
		$cat = $wpdb->get_row("SELECT category_id FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_name = '$category'");
		$category_id = $cat->category_id;
			if (!$category_id) {
				//if the requested category doesn't exist, fail silently
				$select_category = "";
			} else {
				$select_category = ($type=='all')?" WHERE event_category = $category_id":" event_category = $category_id AND";
			}
		}
	}
	return $select_category;
}
// used to generate upcoming events lists
function mc_get_all_events($category) {
global $wpdb;
	 if ( $category!='default' ) {
		$select_category = mc_select_category($category,'all');
	 } else {
		$select_category = "";
	 }
    $events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . "$select_category");
	$offset = (60*60*get_option('gmt_offset'));
	$date = date('Y', time()+($offset)).'-'.date('m', time()+($offset)).'-'.date('d', time()+($offset));
    if (!empty($events)) {
        foreach($events as $event) {
			if ($event->event_recur != "S") {
				$orig_begin = $event->event_begin;
				$orig_end = $event->event_end;
				$numback = 0;
				$numforward = $event->event_repeats;
				$event_repetition = (int) $event->event_repeats;
				if ($event_repetition !== 0) {				
					switch ($event->event_recur) {
						case "D":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = my_calendar_add_date($orig_begin,$i,0,0);
								$end = my_calendar_add_date($orig_end,$i,0,0);		
								${$i} = clone($event);
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "W":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = my_calendar_add_date($orig_begin,($i*7),0,0);
								$end = my_calendar_add_date($orig_end,($i*7),0,0);
								${$i} = clone($event);
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "B":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = my_calendar_add_date($orig_begin,($i*14),0,0);
								$end = my_calendar_add_date($orig_end,($i*14),0,0);
								${$i} = clone($event);
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;							
						case "M":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = my_calendar_add_date($orig_begin,0,$i,0);
								$end = my_calendar_add_date($orig_end,0,$i,0);
								${$i} = clone($event);
								${$i}->event_begin = $begin;
								${$i}->event_end = $end;							
								$arr_events[]=${$i};
							}
							break;
						case "Y":
							for ($i=$numback;$i<=$numforward;$i++) {
								$begin = my_calendar_add_date($orig_begin,0,0,$i);
								$end = my_calendar_add_date($orig_end,0,0,$i);
								${$i} = clone($event);
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
							$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							if ( my_calendar_date_comp($event_begin, $today) ) { // compare first date against today's date 	
								if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = my_calendar_add_date($event_begin,($realStart),0,0);
										if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone($event);
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
							$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = my_calendar_add_date($event_begin,$realDays,0,0);
									if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone($event);
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
							$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							
							if ( my_calendar_date_comp($event_begin, $today) ) { // compare first date against today's date 
								if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays*7),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_weeks = $diff/(86400*7);
									$weeks = explode(".",$diff_weeks);
									$realStart = $weeks[0] - $nDays;
									$realFinish = $weeks[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = my_calendar_add_date($event_begin,($realStart*7),0,0);
										if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone($event);
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}
								
								} else {
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = my_calendar_add_date($event_begin,($realDays*7),0,0);
									if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
										${$realDays} = clone($event);
										${$realDays}->event_begin = $this_date;
										$arr_events[] = ${$realDays};
									}
								}
								}
							} else {
								break;
							}						
						break;
						
						case "B":
							$event_begin = $event->event_begin;
							$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							
							if ( my_calendar_date_comp($event_begin, $today) ) { // compare first date against today's date 
								if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays*14),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_weeks = $diff/(86400*14);
									$weeks = explode(".",$diff_weeks);
									$realStart = $weeks[0] - $nDays;
									$realFinish = $weeks[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = my_calendar_add_date($event_begin,($realStart*14),0,0);
										if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone($event);
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}
								
								} else {
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = my_calendar_add_date($event_begin,($realDays*14),0,0);
									if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
										${$realDays} = clone($event);
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
							$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
							
							if ( my_calendar_date_comp($event_begin, $today) ) { // compare first date against today's date 	
								if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400*30);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = my_calendar_add_date($event_begin,0,$realStart,0);
										if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone($event);
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = my_calendar_add_date($event_begin,0,$realDays,0);
									if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone($event);
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
							$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
							$nDays = get_option('display_past_events');
							$fDays = get_option('display_upcoming_events');
								
							if ( my_calendar_date_comp($event_begin, $today) ) { // compare first date against today's date 		
								if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
									$diff = jd_date_diff_precise(strtotime($event_begin));
									$diff_days = $diff/(86400*365);
									$days = explode(".",$diff_days);
									$realStart = $days[0] - $nDays;
									$realFinish = $days[0] + $fDays;

									for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
									$this_date = my_calendar_add_date($event_begin,0,0,$realStart);
										if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
											${$realStart} = clone($event);
											${$realStart}->event_begin = $this_date;
											$arr_events[] = ${$realStart};
										}
									}								
								
								} else {							
								$realDays = -($nDays);
								for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$this_date = my_calendar_add_date($event_begin,0,0,$realDays);
									if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
										${$realDays} = clone($event);
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
function my_calendar_grab_events($y,$m,$d,$category=null) {

	if (!checkdate($m,$d,$y)) {
		return;
	}

     global $wpdb;
	 if ( $category != null ) {
		$select_category = mc_select_category($category);
	 } else {
		$select_category = "";
	 }
     $arr_events = array();

     // set the date format
     $date = $y . '-' . $m . '-' . $d;
     
     // First we check for conventional events. These will form the first instance of a recurring event
     // or the only instance of a one-off event
     $events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_begin <= '$date' AND event_end >= '$date' AND event_recur = 'S' ORDER BY event_id");
     if (!empty($events)) {
         foreach($events as $event) {
			$arr_events[]=$event;
         }
     }

// Fetch Annual Events
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin)
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date'),EXTRACT(YEAR_MONTH FROM event_begin))) <= event_repeats
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'B' AND '$date' >= event_begin AND event_repeats = 0
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'B' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*14) >= (TO_DAYS('$date') - TO_DAYS(event_end))
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'W' AND '$date' >= event_begin AND event_repeats = 0
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'W' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*7) >= (TO_DAYS('$date') - TO_DAYS(event_end))	
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'D' AND '$date' >= event_begin AND event_repeats = 0
	UNION ALL
	SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE $select_category event_recur = 'D' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats) >= (TO_DAYS('$date') - TO_DAYS(event_end))	
	ORDER BY event_id");
	
	if (!empty($events)) {
			foreach($events as $event) {
				switch ($event->event_recur) {
					case 'Y':
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
					break;
					case 'M':
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
					break;
					case 'B':
				    // Now we are going to check to see what day the original event
				    // fell on and see if the current date is both after it and on 
				    // the correct day. If it is, display the event!
				    $day_start_event = date('D',strtotime($event->event_begin));
				    $day_end_event = date('D',strtotime($event->event_end));
				    $current_day = date('D',strtotime($date));
					$current_date = date('Y-m-d',strtotime($date));
					$start_date = $event->event_begin;
					
					$plan = array("Mon"=>1,"Tue"=>2,"Wed"=>3,"Thu"=>4,"Fri"=>5,"Sat"=>6,"Sun"=>7);

						for ($n=0;$n<=$event->event_repeats;$n++) {
							if ( $current_date == my_calendar_add_date($start_date,(14*$n)) ) {
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
					break;
					case 'W':
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
					break;
					case 'D':
						$arr_events[]=$event;					
					break;
					
				}
			}
     	}
    return $arr_events;
}

function mc_month_comparison($month) {
	$offset = (60*60*get_option('gmt_offset'));
	$current_month = date("n", time()+($offset));
	if (isset($_GET['yr']) && isset($_GET['month'])) {
		if ($month == $_GET['month']) {
			return ' selected="selected"';
		  }
	} elseif ($month == $current_month) { 
		return ' selected="selected"'; 
	}
}

function mc_year_comparison($year) {
	$offset = (60*60*get_option('gmt_offset'));
		$current_year = date("Y", time()+($offset));
		if (isset($_GET['yr']) && isset($_GET['month'])) {
			if ($year == $_GET['yr']) {
				return ' selected="selected"';
			}
		} else if ($year == $current_year) {
			return ' selected="selected"';
		}
}
function mc_build_date_switcher() {
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
            <label for="my-calendar-month">'.__('Month','my-calendar').':</label> <select id="my-calendar-month" name="month">
            <option value="1"'.mc_month_comparison('1').'>'.__('January','my-calendar').'</option>
            <option value="2"'.mc_month_comparison('2').'>'.__('February','my-calendar').'</option>
            <option value="3"'.mc_month_comparison('3').'>'.__('March','my-calendar').'</option>
            <option value="4"'.mc_month_comparison('4').'>'.__('April','my-calendar').'</option>
            <option value="5"'.mc_month_comparison('5').'>'.__('May','my-calendar').'</option>
            <option value="6"'.mc_month_comparison('6').'>'.__('June','my-calendar').'</option>
            <option value="7"'.mc_month_comparison('7').'>'.__('July','my-calendar').'</option> 
            <option value="8"'.mc_month_comparison('8').'>'.__('August','my-calendar').'</option> 
            <option value="9"'.mc_month_comparison('9').'>'.__('September','my-calendar').'</option> 
            <option value="10"'.mc_month_comparison('10').'>'.__('October','my-calendar').'</option> 
            <option value="11"'.mc_month_comparison('11').'>'.__('November','my-calendar').'</option> 
            <option value="12"'.mc_month_comparison('12').'>'.__('December','my-calendar').'</option> 
            </select>
            <label for="my-calendar-year">'.__('Year','my-calendar').':</label> <select id="my-calendar-year" name="yr">
';
	// The year builder is string mania. If you can make sense of this, you know your PHP!
	$past = 5;
	$future = 5;
	$fut = 1;
	$offset = (60*60*get_option('gmt_offset'));
	
		while ($past > 0) {
		    $p .= '            <option value="';
		    $p .= date("Y",time()+($offset))-$past;
		    $p .= '"'.mc_year_comparison(date("Y",time()+($offset))-$past).'>';
		    $p .= date("Y",time()+($offset))-$past."</option>\n";
		    $past = $past - 1;
		}
		while ($fut < $future) {
		    $f .= '            <option value="';
		    $f .= date("Y",time()+($offset))+$fut;
		    $f .= '"'.mc_year_comparison(date("Y",time()+($offset))+$fut).'>';
		    $f .= date("Y",time()+($offset))+$fut."</option>\n";
		    $fut = $fut + 1;
		} 
	$my_calendar_body .= $p;
	$my_calendar_body .= '<option value="'.date("Y",time()+($offset)).'"'.mc_year_comparison(date("Y",time()+($offset))).'>'.date("Y",time()+($offset))."</option>\n";
	$my_calendar_body .= $f;
    $my_calendar_body .= '</select> <input type="submit" value="'.__('Go','my-calendar').'" /></div>
	</form></div>';
	return $my_calendar_body;
}

// Actually do the printing of the calendar
// Compared to searching for and displaying events
// this bit is really rather easy!
function my_calendar($name,$format,$category,$showkey) {
    global $wpdb,$categories;

	$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE ;
    $categories = $wpdb->get_results($sql);
	
	if ($category == "") {
	$category=null;
	}
    // First things first, make sure calendar is up to date
    check_my_calendar();

    // Deal with the week not starting on a monday
	$name_days = array(
		__('<abbr title="Sunday">Sun</abbr>','my-calendar'),
		__('<abbr title="Monday">Mon</abbr>','my-calendar'),
		__('<abbr title="Tuesday">Tues</abbr>','my-calendar'),
		__('<abbr title="Wednesday">Wed</abbr>','my-calendar'),
		__('<abbr title="Thursday">Thur</abbr>','my-calendar'),
		__('<abbr title="Friday">Fri</abbr>','my-calendar'),
		__('<abbr title="Saturday">Sat</abbr>','my-calendar')
		);
	
	if ($format == "mini") {
		$name_days = array(
		__('<abbr title="Sunday">S</abbr>','my-calendar'),
		__('<abbr title="Monday">M</abbr>','my-calendar'),
		__('<abbr title="Tuesday">T</abbr>','my-calendar'),
		__('<abbr title="Wednesday">W</abbr>','my-calendar'),
		__('<abbr title="Thursday">T</abbr>','my-calendar'),
		__('<abbr title="Friday">F</abbr>','my-calendar'),
		__('<abbr title="Saturday">S</abbr>','my-calendar')
		);
	}
	
	if ( get_option('start_of_week') == '1' ) {
   			$first = array_shift($name_days);
			$name_days[] = $first;	
	}
     // Carry on with the script
    $name_months = array(1=>__('January','my-calendar'),__('February','my-calendar'),__('March','my-calendar'),__('April','my-calendar'),__('May','my-calendar'),__('June','my-calendar'),__('July','my-calendar'),__('August','my-calendar'),__('September','my-calendar'),__('October','my-calendar'),__('November','my-calendar'),__('December','my-calendar'));
	$offset = (60*60*get_option('gmt_offset'));
    // If we don't pass arguments we want a calendar that is relevant to today
    if (empty($_GET['month']) || empty($_GET['yr'])) {
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
        $c_day = date("d",time()+($offset));
    }
    // Years get funny if we exceed 3000, so we use this check
    if ($_GET['yr'] <= 3000 && $_GET['yr'] >= 0) {
 
        if ( isset($_GET['month']) ) {
               $c_year = (int) $_GET['yr'];
               $c_month = (int) $_GET['month'];
               $c_day = date("d",time()+($offset));
        } else {
		// No valid month causes the calendar to default to today			
               $c_year = date("Y",time()+($offset));
               $c_month = date("m",time()+($offset));
               $c_day = date("d",time()+($offset));
        }
    } else {
		// No valid year causes the calendar to default to today	
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
        $c_day = date("d",time()+($offset));
    }

    // Fix the days of the week if week start is not on a monday
	if (get_option('start_of_week') == 0) {
		$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
    } else {
		$first_weekday = date("w",mktime(0,0,0,$c_month,1,$c_year));
        $first_weekday = ($first_weekday==0?6:$first_weekday-1);
    }

    $days_in_month = date("t", mktime (0,0,0,$c_month,1,$c_year));
	$and = __("and",'my-calendar');
	if ($category != "" && $category != "all") {
		$category_label = str_replace("|"," $and ",$category) . ' ';
	} else {
		$category_label = "";
	}
    // Start the calendar and add header and navigation
		$my_calendar_body .= "<div id=\"jd-calendar\" class=\"$format\">";
		// Add the calendar table and heading
		$caption_text = stripslashes( get_option('my_calendar_caption') );
		
		if ($format == "calendar" || $format == "mini" ) {
		$my_calendar_body .= '<div class="my-calendar-header">';

	    // We want to know if we should display the date switcher
	    $date_switcher = get_option('display_jump');

	    if ($date_switcher == 'true') {
			$my_calendar_body .= mc_build_date_switcher();
		}
	    // The header of the calendar table and the links. Note calls to link functions
	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev">' . my_calendar_prev_link($c_year,$c_month,$format) . '</li>
	                    <li class="my-calendar-next">' . my_calendar_next_link($c_year,$c_month,$format) . '</li>
						</ul>
	                    </div>
					</div>';		
			$my_calendar_body .= "\n<table class=\"my-calendar-table\" summary=\"$category_label".__('Calendar','my-calendar')."\">\n";
			$my_calendar_body .= '<caption class="my-calendar-month">'.$name_months[(int)$c_month].' '.$c_year.$caption_text."</caption>\n";
		} else {
			if ( get_option('my_calendar_show_heading') == 'true' ) {
				$my_calendar_body .= "\n<h2 class=\"my-calendar-heading\">$category_label".__('Calendar','my-calendar')."</h2>\n";
			}
		// determine which header text to show depending on number of months displayed;
		$my_calendar_body .= (get_option('my_calendar_show_months') <= 1)?'<h3 class="my-calendar-month">'.__('Events in','my-calendar').' '.$name_months[(int)$c_month].' '.$c_year.$caption_text."</h3>\n":'<h3 class="my-calendar-month">'.$name_months[(int)$c_month].'&thinsp;&ndash;&thinsp;'.$name_months[(int)$c_month+$num_months-1].' '.$c_year.$caption_text."</h3>\n";
		$my_calendar_body .= '<div class="my-calendar-header">';
	    // We want to know if we should display the date switcher
		$my_calendar_body .= ( get_option('display_jump') == 'true' )?mc_build_date_switcher():'';

	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev">' . my_calendar_prev_link($c_year,$c_month,$format) . '</li>
	                    <li class="my-calendar-next">' . my_calendar_next_link($c_year,$c_month,$format) . '</li>
						</ul>
	                    </div>
					</div>';	
	}
    // If in calendar format, print the headings of the days of the week
if ( $format == "calendar" || $format == "mini" ) {
    $my_calendar_body .= "<thead>\n<tr>\n";
    for ($i=0; $i<=6; $i++) {
	// Colors need to be different if the starting day of the week is different
		if (get_option('start_of_week') == 0) {
		    $my_calendar_body .= '<th scope="col" class="'.($i<6&&$i>0?'day-heading':'weekend-heading').'">'.$name_days[$i]."</th>\n";
		} else {
		    $my_calendar_body .= '<th scope="col" class="'.($i<5?'day-heading':'weekend-heading').'">'.$name_days[$i]."</th>\n";
		}
	}	
    $my_calendar_body .= "</tr>\n</thead>\n<tbody>";

    for ($i=1; $i<=$days_in_month;) {
        $my_calendar_body .= '<tr>';
        for ($ii=0; $ii<=6; $ii++) {
            if ($ii==$first_weekday && $i==1) {
				$go = TRUE;
			} elseif ($i > $days_in_month ) {
				$go = FALSE;
			}

            if ($go) {
		// Colors again, this time for the day numbers
				$grabbed_events = my_calendar_grab_events($c_year,$c_month,$i,$category);
				$events_class = '';
					if (!count($grabbed_events)) {
						$events_class = ' no-events';
						$element = 'span';
						$trigger = '';
					} else {
						$events_class = ' has-events';
						if ($format == 'mini') {
							$element = 'a href="#"';
							$trigger = ' trigger';
						} else {
							$element = 'span';
							$trigger = '';
						}
					}
				if (get_option('start_of_week') == 0) {
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date_i18n("Ymd",time())?'current-day':'day-with-date').$events_class.'">'."\n	<$element class='mc-date ".($ii<6&&$ii>0?"$trigger":"weekend$trigger")."'>".$i++."</$element>\n		". my_calendar_draw_events($grabbed_events, $format) . "\n</td>\n";
				} else {
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date_i18n("Ymd",time())?'current-day':'day-with-date').$events_class.'">'."\n	<$element class='mc-date ".($ii<5?"$trigger":"weekend$trigger'")."'>".$i++."</$element>\n		". my_calendar_draw_events($grabbed_events, $format) . "\n</td>\n";
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
	$date_format = ( get_option('my_calendar_date_format') != '' ) ? ( get_option('my_calendar_date_format') ) : ( get_option( 'date_format' ) );
	$num_months = get_option('my_calendar_show_months');
	$num_events = 0;
	for ($m=0;$m<$num_months;$m++) {
		if ($m == 0) {
			$add_month = 0;
		} else {
			$add_month = 1;
		}
		$c_month = (int) $c_month + $add_month;
	    for ($i=1; $i<=31; $i++) {
			$grabbed_events = my_calendar_grab_events($c_year,$c_month,$i,$category);
			if (count($grabbed_events)) {
				if ( get_option('list_javascript') != 1) {
					$is_anchor = "<a href='#'>";
					$is_close_anchor = "</a>";
				} else {
					$is_anchor = $is_close_anchor = "";
				}
				$my_calendar_body .= "<li class='$class".(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd",time()+($offset))?' current-day':'')."'><strong class=\"event-date\">$is_anchor".date_i18n($date_format,mktime(0,0,0,$c_month,$i,$c_year))."$is_close_anchor</strong>".my_calendar_draw_events($grabbed_events, $format)."</li>";
				$num_events++;
			} 	
			$class = (my_calendar_is_odd($num_events))?"odd":"even";
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

		if ( file_exists( WP_PLUGIN_DIR . '/my-calendar-custom/' ) ) {
				$path = '/my-calendar-custom';
			} else {
				$path = '/my-calendar/icons';
		    }
        foreach($cat_details as $cat_detail) {
		
			if ($cat_detail->category_icon != "" && get_option('my_calendar_hide_icons')!='true') {
			$my_calendar_body .= '<li><span class="category-color-sample"><img src="'.WP_PLUGIN_URL.$path.'/'.$cat_detail->category_icon.'" alt="" style="background:'.$cat_detail->category_color.';" /></span>'.$cat_detail->category_name."</li>\n";
			} else {
			$my_calendar_body .= '<li><span class="category-color-sample no-icon" style="background:'.$cat_detail->category_color.';"> &nbsp; </span>'.$cat_detail->category_name."</li>\n";			
			}
		}
        $my_calendar_body .= "</ul>\n</div>";
      }
	//$my_calendar_body .= $wpdb->num_queries; // total number of queries	  
	  
	$my_calendar_body .= "\n</div>";
    // The actual printing is done by the shortcode function.
    return $my_calendar_body;
}

function my_calendar_is_odd( $int ) {
  return( $int & 1 );
}


function mc_can_edit_event($author_id) {
	global $user_ID;
	get_currentuserinfo();
	$user = get_userdata($user_ID);	
	
	if ( current_user_can('create_users') ) {
			return true;
		} elseif ( $user_ID == $author_id ) {
			return true;
		} else {
			return false;
		}
}

// compatibility of clone keyword between PHP 5 and 4
if (version_compare(phpversion(), '5.0') < 0) {
	eval('
	function clone($object) {
	  return $object;
	}
	');
}
?>