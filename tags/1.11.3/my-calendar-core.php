<?php
/*
Note:
            $qst = get_permalink($post->ID);
            $qst = parse_url($qst);
            if ($qst['query'])
                $qst = '&format=pdf';
            else
                $qst = '?format=pdf';
*/
function my_calendar_add_feed() {
	global $wp_rewrite, $wpdb;
	$mcdb = $wpdb;
	if ( get_option('mc_show_rss') == 'true' ) {
		add_feed( 'my-calendar-rss', 'my_calendar_rss' );
	}
	if ( get_option('mc_show_ical') == 'true' ) {
		add_feed( 'my-calendar-ics', 'my_calendar_ical' );
	}
	if ( get_option('mc_show_print') == 'true' ) {
		add_feed( 'my-calendar-print', 'my_calendar_print' );
	}	
	/* removed 2/11/2012.
	if ( get_option('mc_show_rss') == 'true' || get_option('mc_show_ical') == 'true' || get_option('mc_show_print') == true ) {
		add_action('generate_rewrite_rules', 'mc_rewrite_rules');
		$wp_rewrite->flush_rules();	
	}
	*/
}
/* I believe that this is obsolete, at least as far back as 2.9.2
function mc_rewrite_rules( $wp_rewrite ) {
  $new_rules = array(
    'feed/(.+)' => 'index.php?feed='.$wp_rewrite->preg_index(1)
  );
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
*/

if ( ! function_exists( 'is_ssl' ) ) {
	function is_ssl() {
		if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
		 return true;
		if ( '1' == $_SERVER['HTTPS'] )
		 return true;
		} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
		}
	return false;
	}
}

// mod from Mike T
function my_calendar_getUsers() {
	global $blog_id, $wpdb;
	$mcdb = $wpdb;
	if ( version_compare( get_bloginfo( 'version' ), '3.1','<' ) ) {
			$authors = $mcdb->get_results( "SELECT ID, user_nicename, display_name from $mcdb->users ORDER BY display_name" );
			return $authors;
	} else {
		$users = new WP_User_Query( array(
		'blog_id' => $blog_id,
		'orderby' => 'display_name',
		'fields' => array('ID', 'user_nicename','display_name')
		) );
	}
	return $users->get_results();
}

function jd_calendar_plugin_action($links, $file) {
	if ($file == plugin_basename(dirname(__FILE__).'/my-calendar.php')) {
		$links[] = "<a href='admin.php?page=my-calendar-config'>" . __('Settings', 'my-calendar') . "</a>";
		$links[] = "<a href='admin.php?page=my-calendar-help'>" . __('Help', 'my-calendar') . "</a>";
	}
	return $links;
}

// Function to add the calendar style into the header
function my_calendar_wp_head() {
  global $wpdb, $wp_query, $wp_plugin_url;
	$mcdb = $wpdb;
  // If the calendar isn't installed or upgraded this won't work
  check_my_calendar();
  $styles = mc_get_style_path( get_option( 'mc_css_file' ),'url' );
	if ( get_option('mc_use_styles') != 'true' ) {
	
		$this_post = $wp_query->get_queried_object();
		if (is_object($this_post)) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'mc_show_css' ) != '' ) {
		$array = explode( ",",get_option( 'mc_show_css' ) );
			if (!is_array($array)) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || get_option( 'mc_show_css' ) == '' ) {
	// generate category colors
	$category_styles = '';
	$categories = $mcdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");
	foreach ( $categories as $category ) {
			$class = "mc_".sanitize_title($category->category_name);
			$hex = (strpos($category->category_color,'#') !== 0)?'#':'';
			$color = $hex.$category->category_color;
		if ( get_option( 'mc_apply_color' ) == 'font' ) {
			$type = 'color';
		} else if ( get_option( 'mc_apply_color' ) == 'background' ) {
			$type = 'background';
		}
		if ( get_option( 'mc_apply_color' )  == 'font' || get_option( 'mc_apply_color' ) == 'background' ) {
		// always an anchor as of 1.11.0
		$category_styles .= "\n.mc-main .$class .event-title a { $type: $color; }";
		}
	}
	$add = '';
	if ( is_user_logged_in() ) {
		$stylesheet_url = plugins_url( 'mc-admin.css', __FILE__ );
		$add = "<link rel=\"stylesheet\" href=\"$stylesheet_url\" type=\"text/css\" media=\"all\" />";
	}
$all_styles = "
<link rel=\"stylesheet\" href=\"$styles\" type=\"text/css\" media=\"all\" />
$add
<style type=\"text/css\">
<!--
.mcjs .mc-main .details, .mcjs .mc-main .calendar-events { display: none; }
/* Styles by My Calendar - Joseph C Dolson http://www.joedolson.com/ */
$category_styles
.mc-event-visible {
display: block!important;
}
-->
</style>";
if ( mc_is_tablet() && file_exists( get_stylesheet_directory() . '/mc-tablet.css' ) ) {
	$all_styles .=  get_stylesheet_directory_uri() . '/mc-tablet.css';
}
if ( mc_is_mobile() && file_exists( get_stylesheet_directory() . '/mc-mobile.css' ) ) {
	$all_styles .=  get_stylesheet_directory_uri() . '/mc-mobile.css';
} 
$all_styles = apply_filters( 'mc_filter_styles',$all_styles,$styles );
echo $all_styles;
		}
	}
}

// Function to deal with events posted by a user when that user is deleted
function mc_deal_with_deleted_user( $id ) {
  global $wpdb;
	$mcdb = $wpdb;
  check_my_calendar();
  // Do the queries
  // This may not work quite right in multi-site. Need to explore further when I have time.
  $mcdb->get_results( "UPDATE ".my_calendar_table()." SET event_author=".$mcdb->get_var("SELECT MIN(ID) FROM ".$mcdb->prefix."users",0,0)." WHERE event_author=".$id );
  $mcdb->get_results( "UPDATE ".my_calendar_table()." SET event_host=".$mcdb->get_var("SELECT MIN(ID) FROM ".$mcdb->prefix."users",0,0)." WHERE event_host=".$id );
}

// Function to add the javascript to the admin header
function my_calendar_add_javascript() { 
global $wp_plugin_url;
	if ( isset($_GET['page']) && $_GET['page'] == 'my-calendar' ) {
		wp_enqueue_script('jquery.calendrical',plugins_url( 'js/jquery.calendrical.js', __FILE__ ), array('jquery') );
		wp_enqueue_script('jquery.addfields',plugins_url( 'js/jquery.addfields.js', __FILE__ ), array('jquery') );
		if ( version_compare( get_bloginfo( 'version' ) , '3.3' , '<' ) ) {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
		}
		$mc_input = get_option( 'mc_input_options' );
		// If the editor is enabled, then don't modify the upload script. 
		if ( !isset($mc_input['event_image']) ) { $mc_input['event_image'] = 'off'; }
		if ( $mc_input['event_use_editor'] != 'on' && $mc_input['event_image'] == 'on' ) {
			wp_register_script('mc-upload', plugins_url( 'js/upload.js', __FILE__ ), array('jquery','media-upload','thickbox'));
			wp_enqueue_script('mc-upload');	
		}
	}
	if ( isset($_GET['page']) && $_GET['page'] == 'my-calendar-groups' ) {
		wp_enqueue_script('jquery.checkall',plugins_url( 'js/jquery.checkall.js', __FILE__ ), array('jquery') );
	}
}

function my_calendar_write_js() {
	if ( isset($_GET['page']) && $_GET['page']=='my-calendar' ) {
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(document).ready(function($) {
	    $('#event_begin, #event_time,' + '#event_end, #event_endtime').calendricalDateTimeRange();
	});
	//]]>
	</script>
	<?php
	}
	if ( isset($_GET['page']) && $_GET['page']=='my-calendar-help') {
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('dd:even').css('background','#f6f6f6');
	});
	</script>
	<?php
	}
}
add_action( 'in_plugin_update_message-my-calendar/my-calendar.php', 'mc_plugin_update_message' );
function mc_plugin_update_message() {
	global $mc_version;
	define('MC_PLUGIN_README_URL',  'http://svn.wp-plugins.org/my-calendar/trunk/readme.txt');
	$response = wp_remote_get( MC_PLUGIN_README_URL, array ('user-agent' => 'WordPress/My Calendar' . $mc_version . '; ' . get_bloginfo( 'url' ) ) );
	if ( ! is_wp_error( $response ) || is_array( $response ) ) {
		$data = $response['body'];
		$bits=explode('== Upgrade Notice ==',$data);
		echo '<div id="mc-upgrade"><p><strong style="color:#c22;">Upgrade Notes:</strong> '.nl2br(trim($bits[1])).'</p></div>';
	} else {
		printf(__('<br /><strong>Note:</strong> Please review the <a class="thickbox" href="%1$s">changelog</a> before upgrading.','my-calendar'),'plugin-install.php?tab=plugin-information&amp;plugin=my-calendar&amp;TB_iframe=true&amp;width=640&amp;height=594');
	}
}

function mc_header_js() {
global $wp_query;
	wp_enqueue_script('jquery');
	if ( get_option('mc_draggable') == '1' && !is_admin() ) { 
		$this_post = $wp_query->get_queried_object();
		if (is_object($this_post)) {
			$id = $this_post->ID;
		} 
		if ( get_option( 'mc_show_js' ) != '' ) {
		$array = explode( ",",get_option( 'mc_show_js' ) );
			if (!is_array($array)) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || get_option( 'mc_show_js' ) == '' ) {
			wp_enqueue_script('jquery.easydrag',plugins_url( 'js/jquery.easydrag.js', __FILE__ ), array('jquery') );
		}
	}
}

function mc_footer_js() {
	if ( mc_is_mobile() && get_option('mc_mobile') == 'true' ) {
		return;
	} else {
		$scripting = '';
		global $wpdb, $wp_query;
		$mcdb = $wpdb;
		if ( get_option('mc_calendar_javascript') != 1 || get_option('mc_list_javascript') != 1 || get_option('mc_mini_javascript') != 1 || get_option('mc_ajax_javascript') != 1 ) {
		  
		$list_js = stripcslashes( get_option( 'mc_listjs' ) );
		$cal_js = stripcslashes( get_option( 'mc_caljs' ) );
		if ( get_option('mc_open_uri') == 'true') { $cal_js = str_replace('e.preventDefault();','',$cal_js); }
		$mini_js = stripcslashes( get_option( 'mc_minijs' ) );
		if ( get_option('mc_open_day_uri') == 'true' || get_option('mc_open_day_uri') == 'listanchor'  || get_option('mc_open_day_uri') == 'calendaranchor') { $mini_js = str_replace('e.preventDefault();','',$mini_js); }
		$ajax_js = stripcslashes( get_option( 'mc_ajaxjs' ) );

			if (is_object($wp_query)) {
				$id = $wp_query->post->ID;
			} 
			if ( get_option( 'mc_show_js' ) != '' ) {
			$array = explode( ",",get_option( 'mc_show_js' ) );
				if (!is_array($array)) {
					$array = array();
				}
			}
			if ( @in_array( $id, $array ) || get_option( 'mc_show_js' ) == '' ) {
				$scripting = "<script type='text/javascript'>\n";
				if ( get_option('mc_calendar_javascript') != 1 ) {	$scripting .= "\n".$cal_js; }
				if ( get_option('mc_list_javascript') != 1 ) {	$scripting .= "\n".$list_js; }
				if ( get_option('mc_mini_javascript') != 1 ) {	$scripting .= "\n".$mini_js; }
				if ( get_option('mc_ajax_javascript') != 1 ) { $scripting .= "\n".$ajax_js; }
				$scripting .= "</script>";
			}
		}
		$scripting = apply_filters( 'mc_filter_javascript_footer',$scripting );
		echo $scripting;
	}
}

function my_calendar_add_styles() {
	if ( !empty($_GET['page']) ) {
	if (  isset($_GET['page']) && ($_GET['page'] == 'my-calendar' || $_GET['page'] == 'my-calendar-groups' || $_GET['page'] == 'my-calendar-categories' || $_GET['page'] == 'my-calendar-locations' || $_GET['page'] == 'my-calendar-config' || $_GET['page'] == 'my-calendar-styles' || $_GET['page'] == 'my-calendar-help' || $_GET['page'] == 'my-calendar-behaviors' ) || $_GET['page'] == 'my-calendar-templates' ) {
		echo '<link type="text/css" rel="stylesheet" href="'.plugins_url( 'mc-styles.css', __FILE__ ).'" />';
	}
	if ( isset($_GET['page']) && $_GET['page'] == 'my-calendar') {
		echo '<link type="text/css" rel="stylesheet" href="'.plugins_url( 'js/calendrical.css', __FILE__ ).'" />';
		$mc_input = get_option('mc_input_options');
		if ( !isset($mc_input['event_image']) ) { $mc_input['event_image'] = 'off'; }		
		if ( $mc_input['event_image'] == 'on' || $mc_input['event_use_editor'] != 'on' || version_compare( get_bloginfo( 'version' ), '3.0','<' ) ) {
			echo '<link type="text/css" rel="stylesheet" href="'.includes_url( 'js/thickbox/thickbox.css' ).'" />';
		}
		if ( version_compare( get_bloginfo( 'version' ) , '3.3' , '<' ) ) {		
			wp_enqueue_style('thickbox');
		}
	}
	}
}

function mc_get_current_url() {
	$pageURL = 'http';
	if ( !empty($_SERVER['HTTPS']) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ( !empty( $_SERVER['SERVER_PORT']) && $_SERVER["SERVER_PORT"] != "80") {
			if ( strpos( $_SERVER["HTTP_HOST"], $_SERVER["SERVER_PORT"] ) === FALSE ) { 
				$pageURL .= $_SERVER["HTTP_HOST"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else { 
				$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]; 
			}	
		} else {
			$pageURL .= $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		}
	return esc_url($pageURL);
}

function csv_to_array($csv, $delimiter = ',', $enclosure = '"', $escape = '\\', $terminator = "\n") {
    $r = array();
    $rows = explode($terminator,trim($csv));
    foreach ($rows as $row) {
        if (trim($row)) {
            $values = explode($delimiter,$row);
			$r[$values[0]] = $values[1];
        }
    }
    return $r;
}

function mc_if_needs_permissions() {
	// this prevents administrators from losing privileges to edit my calendar
	$role = get_role( 'administrator' );
	$caps = $role->capabilities;
	if ( isset($caps['mc_add_events']) ) {
		return; 
	} else {
		$role->add_cap( 'mc_add_events' );
		$role->add_cap( 'mc_approve_events' );
		$role->add_cap( 'mc_manage_events' );
		$role->add_cap( 'mc_edit_cats' );
		$role->add_cap( 'mc_edit_styles' );
		$role->add_cap( 'mc_edit_behaviors' );
		$role->add_cap( 'mc_edit_templates' );
		$role->add_cap( 'mc_edit_settings' );
		$role->add_cap( 'mc_edit_locations' );
		$role->add_cap( 'mc_view_help' );	
	}
}

function mc_add_roles( $add=false, $manage=false, $approve=false ) {
	// grant administrator role all event permissions
	$role = get_role( 'administrator' );
	$role->add_cap( 'mc_add_events' );
	$role->add_cap( 'mc_approve_events' );
	$role->add_cap( 'mc_manage_events' );
	$role->add_cap( 'mc_edit_cats' );
	$role->add_cap( 'mc_edit_styles' );
	$role->add_cap( 'mc_edit_behaviors' );
	$role->add_cap( 'mc_edit_templates' );
	$role->add_cap( 'mc_edit_settings' );
	$role->add_cap( 'mc_edit_locations' );
	$role->add_cap( 'mc_view_help' );
	
	// depending on permissions settings, grant other permissions
	
	if ( $add && $manage && $approve ) {
	// this is an upgrade;
		// Get Roles
		$subscriber = get_role('subscriber');
		$contributor = get_role('contributor');
		$author = get_role('author');
		$editor = get_role('editor');
		$subscriber->add_cap( 'mc_view_help' );
		$contributor->add_cap( 'mc_view_help' );
		$author->add_cap( 'mc_view_help' );
		$editor->add_cap( 'mc_view_help' );

		switch( $add ) {
			case 'read':
				$subscriber->add_cap( 'mc_add_events' );
				$contributor->add_cap( 'mc_add_events' );
				$author->add_cap( 'mc_add_events' );
				$editor->add_cap( 'mc_add_events' );				
			break;
			case 'edit_posts':
				$contributor->add_cap( 'mc_add_events' );
				$author->add_cap( 'mc_add_events' );
				$editor->add_cap( 'mc_add_events' );		
			break;
			case 'publish_posts':
				$author->add_cap( 'mc_add_events' );
				$editor->add_cap( 'mc_add_events' );		
			break;
			case 'moderate_comments':
				$editor->add_cap( 'mc_add_events' );		
			break;
		}
		switch( $approve ) {
			case 'read':
				$subscriber->add_cap( 'mc_approve_events' );
				$contributor->add_cap( 'mc_approve_events' );
				$author->add_cap( 'mc_approve_events' );
				$editor->add_cap( 'mc_approve_events' );
			break;
			case 'edit_posts':
				$contributor->add_cap( 'mc_approve_events' );
				$author->add_cap( 'mc_approve_events' );
				$editor->add_cap( 'mc_approve_events' );		
			break;
			case 'publish_posts':
				$author->add_cap( 'mc_approve_events' );
				$editor->add_cap( 'mc_approve_events' );		
			break;
			case 'moderate_comments':
				$editor->add_cap( 'mc_approve_events' );		
			break;
		}	
		switch( $manage ) {
			case 'read':
				$subscriber->add_cap( 'mc_manage_events' );
				$contributor->add_cap( 'mc_manage_events' );
				$author->add_cap( 'mc_manage_events' );
				$editor->add_cap( 'mc_manage_events' );
			break;
			case 'edit_posts':
				$contributor->add_cap( 'mc_manage_events' );
				$author->add_cap( 'mc_manage_events' );
				$editor->add_cap( 'mc_manage_events' );		
			break;
			case 'publish_posts':
				$author->add_cap( 'mc_manage_events' );
				$editor->add_cap( 'mc_manage_events' );		
			break;
			case 'moderate_comments':
				$editor->add_cap( 'mc_manage_events' );		
			break;
		}
	}
}

// Function to check what version of My Calendar is installed and install or upgrade if needed
function check_my_calendar() {
	global $wpdb, $initial_listjs, $initial_caljs, $initial_minijs, $initial_ajaxjs,$mc_version,$grid_template,$list_template,$mini_template,$single_template, $defaults;
	$mcdb = $wpdb;
	mc_if_needs_permissions();
	$current_version = ( get_option('mc_version') == '') ? get_option('my_calendar_version') : get_option('mc_version');
	// If current version matches, don't bother running this.
	if ($current_version == $mc_version) {
		return true;
	}

  // Lets see if this is first run and create a table if it is!
  // Assume this is not a new install until we prove otherwise
  $new_install = false;
  $my_calendar_exists = false;
  $upgrade_path = array();
  
  // Determine the calendar version
  $tables = $mcdb->get_results("show tables;");
	foreach ( $tables as $table ) {
      foreach ( $table as $value )  {
		  if ( $value == MY_CALENDAR_TABLE ) {
		      $my_calendar_exists = true;
			  // check whether installed version matches most recent version, establish upgrade process.
		    } 
       }
    }
	if ( $my_calendar_exists == true && $current_version == '' ) {
		// If the table exists, but I don't know what version it is, I have to run the full cycle of upgrades. 
		$current_version = '1.3.9';
	}
	
	if ( $my_calendar_exists == false ) {
      $new_install = true;
	} else {	
		// for each release requiring an upgrade path, add a version compare. 
		// Loop will run every relevant upgrade cycle.
		if ( version_compare( $current_version, "1.4.0", "<" ) ) {	$upgrade_path[] = "1.4.0"; } 
		if ( version_compare( $current_version, "1.4.7", "<" ) ) {	$upgrade_path[] = "1.4.7"; } 
		if ( version_compare( $current_version, "1.4.8", "<" ) ) {	$upgrade_path[] = "1.4.8"; } 
		if ( version_compare( $current_version, "1.5.0", "<" ) ) {	$upgrade_path[] = "1.5.0"; } 
		if ( version_compare( $current_version, "1.6.0", "<" ) ) {	$upgrade_path[] = "1.6.0"; } 
		if ( version_compare( $current_version, "1.6.2", "<" ) ) {	$upgrade_path[] = "1.6.2"; } 
		if ( version_compare( $current_version, "1.6.3", "<" ) ) {	$upgrade_path[] = "1.6.3"; } 
		if ( version_compare( $current_version, "1.7.0", "<" ) ) { 	$upgrade_path[] = "1.7.0"; } 
		if ( version_compare( $current_version, "1.7.1", "<" ) ) { 	$upgrade_path[] = "1.7.1"; } 
		if ( version_compare( $current_version, "1.8.0", "<" ) ) {	$upgrade_path[] = "1.8.0"; } 
		if ( version_compare( $current_version, "1.9.0", "<" ) ) {	$upgrade_path[] = "1.9.0"; }
		if ( version_compare( $current_version, "1.9.1", "<" ) ) {	$upgrade_path[] = "1.9.1"; }
		if ( version_compare( $current_version, "1.9.3", "<" ) ) {  $upgrade_path[] = "1.9.3"; }
		if ( version_compare( $current_version, "1.10.0", "<" ) ) { $upgrade_path[] = "1.10.0"; }
		if ( version_compare( $current_version, "1.10.7", "<" ) ) { $upgrade_path[] = "1.10.7"; }	
		if ( version_compare( $current_version, "1.11.0", "<" ) ) { $upgrade_path[] = "1.11.0"; }
		if ( version_compare( $current_version, "1.11.1", "<" ) ) { $upgrade_path[] = "1.11.1"; }
	}
	// having determined upgrade path, assign new version number
	update_option( 'mc_version' , $mc_version );
	// Now we've determined what the current install is or isn't 
	if ( $new_install == true ) {
		 //add default settings
		mc_default_settings();
		$sql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_id=1, category_name='General', category_color='#ffffff', category_icon='event.png'";
		$mcdb->query($sql);
    } else {
		// clear cache so updates are immediately available
		mc_delete_cache();
	}	
	// switch for different upgrade paths
	foreach ($upgrade_path as $upgrade) {
		switch ($upgrade) {
		// only upgrade db on most recent version
			case '1.11.1':
				add_option( 'mc_event_link', 'true' );
				break;
			case '1.11.0':
				add_option( 'mc_convert','true');
				add_option('mc_process_shortcodes','false');
				$add = get_option('mc_can_manage_events'); // yes, this is correct.
				$manage = get_option('mc_event_edit_perms');
				$approve = get_option('mc_event_approve_perms');
				mc_add_roles( $add, $manage, $approve );		
				delete_option( 'mc_can_manage_events' );
				delete_option( 'mc_event_edit_perms' );
				delete_option( 'mc_event_approve_perms' );			
				break;
			case '1.10.7':
				upgrade_db();
				update_option( 'mc_multisite_show', 0 );
				break;
			case '1.10.0':
				upgrade_db();
				update_option( 'mc_caching_enabled','true' );
				update_option( 'mc_week_caption',"The week's events" );
				update_option( 'mc_show_print','false' );
				update_option( 'mc_db_version','1.10.0' );			
				break;
			case '1.9.3':
				update_option( 'mc_draggable', 1 );
				break;
			case '1.9.1':
				update_option( 'mc_widget_defaults', $defaults);
				break;
			case '1.9.0':
				delete_option( 'mc_show_heading' );
				add_option( 'mc_time_format', get_option( 'time_format' ) );
				add_option( 'mc_display_jump', get_option( 'display_jump' ) );
				add_option( 'mc_display_author', get_option( 'display_author' ) );
				if ( get_option( 'can_manage_events' ) != '' ) {
					add_option( 'mc_can_manage_events', get_option( 'can_manage_events' ) );
				} else {
					add_option( 'mc_can_manage_events', 'manage_options' );				
				}
				add_option( 'mc_ajaxjs', get_option( 'my_calendar_ajaxjs' ) );
				add_option( 'mc_caljs', get_option( 'my_calendar_caljs' ) );
				add_option( 'mc_css_file', get_option( 'my_calendar_css_file' ) );
				add_option( 'mc_date_format', get_option( 'my_calendar_date_format' ) );
				add_option( 'mc_hide_icons', get_option( 'my_calendar_hide_icons' ) );
				add_option( 'mc_listjs', get_option( 'my_calendar_listjs' ) );
				add_option( 'mc_minijs', get_option( 'my_calendar_minijs' ) );
				add_option( 'mc_notime_text', get_option( 'my_calendar_notime_text' ) );
				add_option( 'mc_show_address', get_option( 'my_calendar_show_address' ) );
				add_option( 'mc_show_css', get_option( 'my_caledar_show_css' ) );
				add_option( 'mc_show_heading', get_option( 'my_calendar_show_heading' ) );
				add_option( 'mc_show_js', get_option( 'my_calendar_show_js' ) );
				add_option( 'mc_show_map', get_option( 'my_calendar_show_map' ) );
				add_option( 'mc_show_months', get_option( 'my_calendar_show_months' ) );
				add_option( 'mc_templates', get_option( 'my_calendar_templates' ) );
				add_option( 'mc_use_styles', get_option( 'my_calendar_use_styles' ) );
				add_option( 'mc_version', get_option( 'my_calendar_version' ) );
				add_option( 'mc_widget_defaults', get_option( 'my_calendar_widget_defaults' ) );
				add_option( 'mc_week_format', "M j, 'y" );
				add_option( 'mc_calendar_javascript', get_option( 'calendar_javascript' ) );
				add_option( 'mc_list_javascript', get_option( 'list_javascript' ) );
				add_option( 'mc_mini_javascript', get_option( 'mini_javascript' ) );
				add_option( 'mc_ajax_javascript', get_option( 'ajax_javascript' ) );
				
				delete_option( 'ajax_javascript' );
				delete_option( 'mini_javascript' );
				delete_option( 'calendar_javascript' );
				delete_option( 'list_javascript' );
				delete_option( 'display_jump' );
				delete_option( 'display_author' );
				delete_option( 'can_manage_events' );
				delete_option( 'my_calendar_week_format' );
				delete_option( 'my_calendar_ajaxjs' );
				delete_option( 'my_calendar_caljs' );
				delete_option( 'my_calendar_css_file' );
				delete_option( 'my_calendar_date_format' );
				delete_option( 'my_calendar_hide_icons' );
				delete_option( 'my_calendar_listjs' );
				delete_option( 'my_calendar_minijs' );
				delete_option( 'my_calendar_notime_text' );
				delete_option( 'my_calendar_show_address' );
				delete_option( 'my_calendar_show_css' );
				delete_option( 'my_calendar_show_heading' );
				delete_option( 'my_calendar_show_js' );
				delete_option( 'my_calendar_show_map' );
				delete_option( 'my_calendar_show_months' );
				delete_option( 'my_calendar_templates' );
				delete_option( 'my_calendar_use_styles' );
				delete_option( 'my_calendar_version' );
				delete_option( 'my_calendar_widget_defaults' );
				add_option( 'mc_location_control','' );
				add_site_option('mc_multisite','0' );
				add_option( 'mc_templates', array(
					'title'=>'{title}',
					'link'=>'{title}',
					'label'=>'{title}',
					'mini'=>$mini_template,
					'grid'=>$grid_template,
					'list'=>$list_template,
					'details'=>$single_template
				));	
				$mc_input = get_option( 'mc_input_options' );
				$mc_input['event_image'] = 'on';
				update_option( 'mc_input_options',$mc_input );				
				upgrade_db();
				update_option('mc_db_version','1.9.0');			
			case '1.8.0':
				$mc_input = get_option( 'mc_input_options' );
				if ( !isset( $mc_input['event_use_editor'] ) ) {
					$mc_input['event_use_editor'] = 'off';
					update_option( 'mc_input_options',$mc_input );
				}
				add_option( 'mc_show_weekends','true' );
				add_option( 'mc_uri','' );
				delete_option( 'my_calendar_stored_styles');
			break;
			case '1.7.1':
				if ( get_option('mc_location_type') == '' ) {
					update_option('mc_location_type','event_state');
				}
			break;				
			case '1.7.0': 
				add_option('mc_show_rss','false');
				add_option('mc_show_ical','false');					
				add_option('mc_skip_holidays','false');	
				add_option('mc_event_edit_perms','manage_options');
				$original_styles = get_option('mc_style');
				if ($original_styles != '') {
				$stylefile = mc_get_style_path('refresh.css');
					if ( mc_write_styles( $stylefile, $original_styles ) ) {
						delete_option('mc_style');
					} else {
						add_option('mc_file_permissions','false');
					}
				}
				if ( get_option( 'mc_css_file' ) == '' ) {
					update_option('mc_css_file','my-calendar.css');				
				}
				// convert old widget settings into new defaults
				$type = get_option('display_upcoming_type');
				if ($type == 'events') {
					$before = get_option('display_upcoming_events');
					$after = get_option('display_past_events');
				} else {
					$before = get_option('display_upcoming_days');
					$after = get_option('display_past_days');
				}
				$category = get_option('display_in_category');
				$today_template = get_option('mc_today_template'); 
				$upcoming_template = get_option('mc_upcoming_template');
				$today_title = get_option('mc_today_title');
				$today_text = get_option('mc_no_events_text');
				$upcoming_title = get_option('mc_upcoming_title');

				$defaults = array(
					'upcoming'=>array(	
						'type'=>$type,
						'before'=>$before,
						'after'=>$after,
						'template'=>$upcoming_template,
						'category'=>$category,
						'text'=>'',
						'title'=>$upcoming_title
					),
					'today'=>array(
						'template'=>$today_template,
						'category'=>'',
						'title'=>$today_title,
						'text'=>$today_text
					)
				);
				add_option('mc_widget_defaults',$defaults);
				delete_option('display_upcoming_type');
				delete_option('display_upcoming_events');
				delete_option('display_past_events');
				delete_option('display_upcoming_days');
				delete_option('display_todays','true');
				delete_option('display_upcoming','true');
				delete_option('display_upcoming_days',7);				
				delete_option('display_past_days');
				delete_option('display_in_category');
				delete_option('mc_today_template'); 
				delete_option('mc_upcoming_template');
				delete_option('mc_today_title');
				delete_option('my_calendar_no_events_text');
				delete_option('mc_upcoming_title');			
			break;		
			case '1.6.3':
				add_option( 'mc_ajaxjs',$initial_ajaxjs );
				add_option( 'mc_ajax_javascript', 1 );
			break;
			case '1.6.2':
				$mc_user_settings = array(
				'my_calendar_tz_default'=>array(
					'enabled'=>'off',
					'label'=>'My Calendar Default Timezone',
					'values'=>array(
							"-12" => "(GMT -12:00) Eniwetok, Kwajalein",
							"-11" => "(GMT -11:00) Midway Island, Samoa",
							"-10" => "(GMT -10:00) Hawaii",
							"-9.5" => "(GMT -9:30) Marquesas Islands",
							"-9" => "(GMT -9:00) Alaska",
							"-8" => "(GMT -8:00) Pacific Time (US &amp; Canada)",
							"-7" => "(GMT -7:00) Mountain Time (US &amp; Canada)",
							"-6" => "(GMT -6:00) Central Time (US &amp; Canada), Mexico City",
							"-5" => "(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima",
							"-4.5" => "(GMT -4:30) Venezuela",
							"-4" => "(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz",
							"-3.5" => "(GMT -3:30) Newfoundland",
							"-3" => "(GMT -3:00) Brazil, Buenos Aires, Georgetown",
							"-2" => "(GMT -2:00) Mid-Atlantic",
							"-1" => "(GMT -1:00 hour) Azores, Cape Verde Islands",
							"0" => "(GMT) Western Europe Time, London, Lisbon, Casablanca",
							"1" => "(GMT +1:00 hour) Brussels, Copenhagen, Madrid, Paris",
							"2" => "(GMT +2:00) Kaliningrad, South Africa",
							"3" => "(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg",
							"3.5" => "(GMT +3:30) Tehran",
							"4" => "(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi",
							"4.5" => "(GMT +4:30) Afghanistan",
							"5" => "(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
							"5.5" => "(GMT +5:30) Bombay, Calcutta, Madras, New Delhi",
							"5.75" => "(GMT +5:45) Nepal",
							"6" => "(GMT +6:00) Almaty, Dhaka, Colombo",
							"6.5" => "(GMT +6:30) Myanmar, Cocos Islands",
							"7" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
							"8" => "(GMT +8:00) Beijing, Perth, Singapore, Hong Kong",
							"9" => "(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
							"9.5" => "(GMT +9:30) Adelaide, Darwin",
							"10" => "(GMT +10:00) Eastern Australia, Guam, Vladivostok",
							"10.5" => "(GMT +10:30) Lord Howe Island",
							"11" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
							"11.5" => "(GMT +11:30) Norfolk Island",
							"12" => "(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka",
							"12.75" => "(GMT +12:45) Chatham Islands",
							"13" => "(GMT +13:00) Tonga",
							"14" => "(GMT +14:00) Line Islands"
							),
					),
				'my_calendar_location_default'=>array(
					'enabled'=>'off',
					'label'=>'My Calendar Default Location',
					'values'=>array(
								'AL'=>"Alabama",
								'AK'=>"Alaska", 
								'AZ'=>"Arizona", 
								'AR'=>"Arkansas", 
								'CA'=>"California", 
								'CO'=>"Colorado", 
								'CT'=>"Connecticut", 
								'DE'=>"Delaware", 
								'DC'=>"District Of Columbia", 
								'FL'=>"Florida", 
								'GA'=>"Georgia", 
								'HI'=>"Hawaii", 
								'ID'=>"Idaho", 
								'IL'=>"Illinois", 
								'IN'=>"Indiana", 
								'IA'=>"Iowa", 
								'KS'=>"Kansas", 
								'KY'=>"Kentucky", 
								'LA'=>"Louisiana", 
								'ME'=>"Maine", 
								'MD'=>"Maryland", 
								'MA'=>"Massachusetts", 
								'MI'=>"Michigan", 
								'MN'=>"Minnesota", 
								'MS'=>"Mississippi", 
								'MO'=>"Missouri", 
								'MT'=>"Montana",
								'NE'=>"Nebraska",
								'NV'=>"Nevada",
								'NH'=>"New Hampshire",
								'NJ'=>"New Jersey",
								'NM'=>"New Mexico",
								'NY'=>"New York",
								'NC'=>"North Carolina",
								'ND'=>"North Dakota",
								'OH'=>"Ohio", 
								'OK'=>"Oklahoma", 
								'OR'=>"Oregon", 
								'PA'=>"Pennsylvania", 
								'RI'=>"Rhode Island", 
								'SC'=>"South Carolina", 
								'SD'=>"South Dakota",
								'TN'=>"Tennessee", 
								'TX'=>"Texas", 
								'UT'=>"Utah", 
								'VT'=>"Vermont", 
								'VA'=>"Virginia", 
								'WA'=>"Washington", 
								'WV'=>"West Virginia", 
								'WI'=>"Wisconsin", 
								'WY'=>"Wyoming"),
					)
				);
				$check = get_option('mc_user_settings');
				if ( !is_array( $check['my_calendar_location_default'] ) ) {
					update_option('mc_user_settings',$mc_user_settings);
				}				
			break;
			case '1.6.0':
				add_option('mc_user_settings_enabled',false);
				add_option('mc_user_location_type','state');
				add_option('mc_show_js',get_option('mc_show_css') );   
			break;
			case '1.5.0':
				add_option('mc_event_mail','false');
				add_option('mc_event_mail_subject','');
				add_option('mc_event_mail_to','');
				add_option('mc_event_mail_message','');
				add_option('mc_event_approve','false');		
				add_option('mc_event_approve_perms','manage_options');
				add_option('mc_no_fifth_week','true');				
			break;
			case '1.4.8':
				add_option('mc_input_options',array('event_short'=>'on','event_desc'=>'on','event_category'=>'on','event_link'=>'on','event_recurs'=>'on','event_open'=>'on','event_location'=>'on','event_location_dropdown'=>'on') );	
				add_option('mc_input_options_administrators','false');
			break;
			case '1.4.7':
				add_option( 'mc_event_open', 'Registration is open' );
				add_option( 'mc_event_closed', 'Registration is closed' );
				add_option( 'mc_event_registration', 'false' );
				add_option( 'mc_short', 'false' );
				add_option( 'mc_desc', 'true' );
			break;
			case '1.4.0':
			// change tables					
				add_option( 'mc_event_link_expires','false' );
				add_option( 'mc_apply_color','default' );
				add_option( 'mc_minijs', $initial_minijs);
				add_option( 'mc_mini_javascript', 0);
			break;
			default:
			break;
		}
	}
	/* 
	if the user has fully uninstalled the plugin but kept the database of events, this will restore default 
	settings and upgrade db if needed.
	*/
	if ( get_option( 'mc_uninstalled' ) == 'true' ) {
		mc_default_settings();	
		update_option( 'mc_db_version', $mc_version );
		delete_option( 'mc_uninstalled' );
	}
}



function jd_cal_checkCheckbox( $theFieldname,$theValue,$theArray='' ){
	if (!is_array( get_option( $theFieldname ) ) ) {
	if( get_option( $theFieldname ) == $theValue ){
		echo 'checked="checked"';
	}
	} else {
		$theSetting = get_option( $theFieldname );
		if ( !empty($theSetting[$theArray]['enabled']) && $theSetting[$theArray]['enabled'] == $theValue ) {
			echo 'checked="checked"';
		}
	}
}
function jd_cal_checkSelect( $theFieldname,$theValue,$theArray='' ){
	if (!is_array( get_option( $theFieldname ) ) ) {
	if( get_option( $theFieldname ) == $theValue ){
			echo 'selected="selected"';
	}
	} else {
		$theSetting = get_option( $theFieldname );
		if ( $theSetting[$theArray]['enabled'] == $theValue ) {
			echo 'selected="selected"';
		}
	}
}

function my_calendar_fouc() {
global $wp_query;
	if ( get_option('mc_calendar_javascript') != 1 || get_option('mc_list_javascript') != 1 || get_option('mc_mini_javascript') != 1 ) {
		$scripting = "\n<script type='text/javascript'>\n";
		$scripting .= "jQuery('html').addClass('mcjs');\n";
		$scripting .= "jQuery(document).ready(function($) { \$('html').removeClass('mcjs') });\n";
		$scripting .= "jQuery.noConflict();\n";
		$scripting .= "</script>\n";

		if (is_object($wp_query)) {
			$id = $wp_query->post->ID;
		} 
		if ( get_option( 'mc_show_js' ) != '' ) {
		$array = explode( ",",get_option( 'mc_show_js' ) );
			if ( !is_array( $array ) ) {
				$array = array();
			}
		}
		if ( @in_array( $id, $array ) || trim ( get_option( 'mc_show_js' ) ) == '' ) {	
			echo $scripting;
		}
	}
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

function mc_event_repeats_forever( $recur, $repeats ) {
	if ( $recur != 'S' && $repeats == 0 ) {
		return true;
	}
	switch ($recur) {
		case "S": // single
			return false;
		break;
		case "D": // daily
			return ($repeats == 999)?true:false;
		break;
		case "W": // weekly
			return ($repeats == 500)?true:false;		
		break;
		case "B": // biweekly
			return ($repeats == 250)?true:false;		
		break;
		case "M": // monthly
		case "U":
			return ($repeats == 120)?true:false;		
		break;
		case "Y":
			return ($repeats == 10)?true:false;		
		break;
		default: false;		
	}
}

function my_calendar_is_odd( $int ) {
  return( $int & 1 );
}

function mc_can_edit_event($author_id) {
	global $user_ID;
	get_currentuserinfo();
	$user = get_userdata($user_ID);	
	
	if ( current_user_can( 'mc_manage_events' ) ) {
			return true;
		} elseif ( $user_ID == $author_id ) {
			return true;
		} else {
			return false;
		}
}

function jd_option_selected($field,$value,$type='checkbox') {
	switch ($type) {
		case 'radio':		
		case 'checkbox':
		$result = ' checked="checked"';
		break;
		case 'option':
		$result = ' selected="selected"';
		break;
	}	
	if ($field == $value) {
		$output = $result;
	} else {
		$output = '';
	}
	return $output;
}

// compatibility of clone keyword between PHP 5 and 4
if (version_compare(phpversion(), '5.0') < 0) {
	eval('
	function clone($object) {
	  return $object;
	}
	');
}

add_action( 'admin_bar_menu','my_calendar_admin_bar', 200 );
function my_calendar_admin_bar() {
	global $wp_admin_bar;
	if ( current_user_can( 'mc_add_events' ) ) {
		$url = admin_url('admin.php?page=my-calendar');
		$args = array( 'id'=>'my-calendar','title'=>__('Add Event','my-calendar'),'href'=>$url );
		$wp_admin_bar->add_menu($args);
	}
}

// functions to route db queries

function my_calendar_table() {
	$option = (int) get_site_option('mc_multisite');
	$choice = (int) get_option('mc_current_table');
	switch ($option) {
		case 0:return MY_CALENDAR_TABLE;break;
		case 1:return MY_CALENDAR_GLOBAL_TABLE;break;
		case 2:return ($choice==1)?MY_CALENDAR_GLOBAL_TABLE:MY_CALENDAR_TABLE;break;
		default:return MY_CALENDAR_TABLE;
	}
}
function my_calendar_categories_table() {
	$option = (int) get_site_option('mc_multisite');
	$choice = (int) get_option('mc_current_table');	
	switch ($option) {
		case 0:return MY_CALENDAR_CATEGORIES_TABLE;break;
		case 1:return MY_CALENDAR_GLOBAL_CATEGORIES_TABLE;break;
		case 2:return ($choice==1)?MY_CALENDAR_GLOBAL_CATEGORIES_TABLE:MY_CALENDAR_CATEGORIES_TABLE;break;
		default:return MY_CALENDAR_CATEGORIES_TABLE;
	}
}
function my_calendar_locations_table() {
	$option = (int) get_site_option('mc_multisite');
	$choice = (int) get_option('mc_current_table');	
	switch ($option) {
		case 0:return MY_CALENDAR_LOCATIONS_TABLE;break;
		case 1:return MY_CALENDAR_GLOBAL_LOCATIONS_TABLE;break;
		case 2:return ($choice==1)?MY_CALENDAR_GLOBAL_LOCATIONS_TABLE:MY_CALENDAR_LOCATIONS_TABLE;break;
		default:return MY_CALENDAR_LOCATIONS_TABLE;
	}
}

// Mail functions by Roland
function my_calendar_send_email( $details ) {
$event = event_as_array($details);

	if ( get_option('mc_event_mail') == 'true' ) {	
		$to = get_option('mc_event_mail_to');
		$subject = get_option('mc_event_mail_subject');
		$message = jd_draw_template( $event, get_option('mc_event_mail_message') );
		$mail = wp_mail($to, $subject, $message);
	}
}
// checks submitted events against akismet, if available, otherwise just returns false 
function mc_akismet( $event_url='', $description='' ) {
	global $akismet_api_host, $akismet_api_port, $user;
	if ( current_user_can( 'mc_manage_events' ) ) { // is a privileged user
		return 0;
	} 
	$c = array();
	if ( ! function_exists( 'akismet_http_post' ) || ! ( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) ) {
		return 0;
	}

	$c['blog'] = get_option( 'home' );
	$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$c['referrer'] = $_SERVER['HTTP_REFERER'];
	$c['comment_type'] = 'my_calendar_event';
	if ( $permalink = get_permalink() )
		$c['permalink'] = $permalink;
		
	if ( '' != $event_url )
		$c['comment_author_url'] = $event_url;
	if ( '' != $description )
		$c['comment_content'] = $description;

	$ignore = array( 'HTTP_COOKIE' );

	foreach ( $_SERVER as $key => $value )
		if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;

	$query_string = '';
	foreach ( $c as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

	$response = akismet_http_post( $query_string, $akismet_api_host,
		'/1.1/comment-check', $akismet_api_port );
	if ( 'true' == $response[1] )
		return 1;
	else
		return 0;
}

function mc_external_link( $link, $type='event' ) {
	$url = parse_url($link);
	$host = $url['host'];
	$site = parse_url( get_option( 'siteurl' ) );
	$known = $site['host'];
	if ( strpos( $host, $known ) === false ) {
		return "class='$type-link external'";
	} else {
		return "class='$type-link'";
	}
	return;
}

// Adding button to the MCE toolbar (Visual Mode) 
add_action('init', 'mc_addbuttons');

// Add button hooks to the Tiny MCE 
function mc_addbuttons() {
	global $mc_version;
	if (!current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
		return;
	}
	if ( get_user_option('rich_editing') == 'true') {
		add_filter( 'tiny_mce_version', 'mc_tiny_mce_version', 0 );
		add_filter( 'mce_external_plugins', 'mc_plugin', 0 );
		add_filter( 'mce_buttons', 'mc_button', 0 );
	}
	// Register Hooks
	if (is_admin()) {	
		// Add Quicktag
		add_action( 'edit_form_advanced', 'mc_add_quicktags' );
		add_action( 'edit_page_form', 'mc_add_quicktags' );

		// Queue Embed JS
		add_action( 'admin_head', 'mc_admin_js_vars');
		wp_enqueue_script( 'mcqt', plugins_url('button/mcb.js',__FILE__), array(), $mc_version );
	} 
}

// Break the browser cache of TinyMCE
function mc_tiny_mce_version( ) {
	global $mc_version;
	return 'mcb-' . $mc_version;
}

// Load the custom TinyMCE plugin
function mc_plugin( $plugins ) {
global $wp_plugin_url;

	$plugins['mcqt'] = plugins_url('button/tinymce3/editor_plugin.js', __FILE__ );
	return $plugins;
}

// Add the buttons: separator, custom
function mc_button( $buttons ) {
	array_push( $buttons, 'separator', 'myCalendar' );
	return $buttons;
}


add_action('admin_print_scripts', 'mc_quicktags');
function mc_quicktags() {
	wp_enqueue_script(
		'mc_quicktags',
		plugin_dir_url(__FILE__) . 'js/mc-quicktags.js',
		array('quicktags')
	);
}

// Add a button to the quicktag view (HTML Mode) >>>
function mc_add_quicktags(){
?>
<script type="text/javascript">
// <![CDATA[
(function(){
	if (typeof jQuery === 'undefined') {
		return;
	}
	jQuery(document).ready(function($){
		// Add the buttons to the HTML view
		$("#ed_toolbar").append('<input type="button" class="ed_button" onclick="myCalQT.Tag.embed.apply(myCalQT.Tag); return false;" title="Insert My Calendar" value="My Calendar" />');
	});
}());
// ]]>
</script>
<?php	
}

function mc_newline_replace($string) {
  return (string)str_replace(array("\r", "\r\n", "\n"), '', $string);
}

// Set URL for the generator page
function mc_admin_js_vars(){
global $wp_plugin_url;
?>
<script type="text/javascript">
// <![CDATA[
	if (typeof myCalQT !== 'undefined' && typeof myCalQT.Tag !== 'undefined') {
		myCalQT.Tag.configUrl = "<?php echo plugins_url( 'button/generator.php',__FILE__ ); ?>";
	}
// ]]>	
</script>
<?php
}

function reverse_array($array, $boolean, $order) {
	if ( $order == 'desc' ) {
		return array_reverse($array, $boolean);
	} else {
		return $array;
	}
}

function mc_is_mobile() {
	$uagent = new uagent_info();
	if ( $uagent->DetectMobileQuick() == $uagent->true ) {
		return true;
	} else {
		return false;
	}
}

function mc_is_tablet() {
	$uagent = new uagent_info();
	if ( $uagent->DetectTierTablet() == $uagent->true ) {
		return true;
	} else {
		return false;
	}
}

function mc_guess_calendar() {
	global $wpdb;
	$mcdb = $wpdb;
	/* If you're looking at this, and have suggestions for other slugs I could be looking at, feel free to let me know. I didn't feel a need to be overly thorough. */
	$my_guesses = array( 'calendar','events','activities','classes','courses','rehearsals','schedule','calendario','actividades','eventos','kalender','veranstaltungen','unterrichten','eventi','classi' );
	foreach( $my_guesses as $guess ) {
		$value = $mcdb->get_var("SELECT id FROM $mcdb->posts WHERE post_name LIKE '%$guess%'" );
		if ( $value ) {
			_e('Is this your calendar page?','my-calendar'); echo ' <code>'.get_permalink( $value ).'</code>';
			return;
		} else {
			_e('I tried to guess, but don\'t have a suggestion for you.','my-calendar');;
			return;
		}
	}
}

function jcd_get_support_form() {
global $current_user;
get_currentuserinfo();
	// send fields for My Calendar
	$version = get_option('mc_version');
	$mc_db_version = get_option('mc_db_version');
	$mc_uri = get_option('mc_uri');
	$mc_css = get_option('mc_css_file');
	// send fields for all plugins
	$wp_version = get_bloginfo('version');
	$home_url = home_url();
	$wp_url = get_bloginfo('wpurl');
	$language = get_bloginfo('language');
	$charset = get_bloginfo('charset');
	// server
	$php_version = phpversion();

	// theme data
	if ( function_exists( 'wp_get_theme' ) ) {
	$theme = wp_get_theme();
		$theme_name = $theme->Name;
		$theme_uri = $theme->ThemeURI;
		$theme_parent = $theme->Template;
		$theme_version = $theme->Version;	
	} else {
	$theme_path = get_stylesheet_directory().'/style.css';	
	$theme = get_theme_data($theme_path);
		$theme_name = $theme['Name'];
		$theme_uri = $theme['ThemeURI'];
		$theme_parent = $theme['Template'];
		$theme_version = $theme['Version'];
	}
	// plugin data

	$plugins = get_plugins();
	$plugins_string = '';
	
		foreach( array_keys($plugins) as $key ) {
			if ( is_plugin_active( $key ) ) {
				$plugin =& $plugins[$key];
				$plugin_name = $plugin['Name'];
				$plugin_uri = $plugin['PluginURI'];
				$plugin_version = $plugin['Version'];
				$plugins_string .= "$plugin_name: $plugin_version; $plugin_uri\n";
			}
		}
	$data = "
================ Installation Data ====================
==My Calendar:==
Version: $version
DB Version: $mc_db_version
URI: $mc_uri
CSS: $mc_css

==WordPress:==
Version: $wp_version
URL: $home_url
Install: $wp_url
Language: $language
Charset: $charset

==Extra info:==
PHP Version: $php_version
Server Software: $_SERVER[SERVER_SOFTWARE]
User Agent: $_SERVER[HTTP_USER_AGENT]

==Theme:==
Name: $theme_name
URI: $theme_uri
Parent: $theme_parent
Version: $theme_version

==Active Plugins:==
$plugins_string
";
	if ( isset($_POST['mc_support']) ) {
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce,'my-calendar-nonce') ) die("Security check failed");	
		$request = ( !empty($_POST['support_request']) )?stripslashes($_POST['support_request']):false;
		$has_donated = ( $_POST['has_donated'] == 'on')?"Donor":"No donation";
		$has_purchased = ( $_POST['has_purchased'] == 'on')?"Purchaser":"No purchase";
		$has_read_faq = ( $_POST['has_read_faq'] == 'on')?"Read FAQ":false;
		$subject = "My Calendar support request. $has_donated; $has_purchased";
		$message = $request ."\n\n". $data;
		$from = "From: \"$current_user->display_name\" <$current_user->user_email>\r\n";

		if ( !$has_read_faq ) {
			echo "<div class='message error'><p>".__('Please read the FAQ and other Help documents before making a support request.','my-calendar')."</p></div>";
		} else if ( !$request ) {
			echo "<div class='message error'><p>".__('Please describe your problem in detail. I\'m not psychic.','my-calendar')."</p></div>";
		} else {
			wp_mail( "plugins@joedolson.com",$subject,$message,$from );
		
			if ( $has_donated == 'Donor' || $has_purchased == 'Purchaser' ) {
				echo "<div class='message updated'><p>".__('Thank you for supporting the continuing development of this plug-in! I\'ll get back to you as soon as I can.','my-calendar')."</p></div>";		
			} else {
				echo "<div class='message updated'><p>".__('I\'ll get back to you as soon as I can, after dealing with any support requests from plug-in supporters.','my-calendar')."</p></div>";				
			}
		}
	}
	
	echo "
	<form method='post' action='".admin_url('admin.php?page=my-calendar-help')."'>
		<div><input type='hidden' name='_wpnonce' value='".wp_create_nonce('my-calendar-nonce')."' /></div>
		<div>
		<p>".
		__('Please note: I do keep records of those who have donated, <strong>but if your donation came from somebody other than your account at this web site, please note this in your message.</strong>','my-calendar')
		."<p>
		<code>".__('From:','my-calendar')." \"$current_user->display_name\" &lt;$current_user->user_email&gt;</code>
		</p>
		<p>
		<input type='checkbox' name='has_read_faq' id='has_read_faq' value='on' /> <label for='has_read_faq'>".__('I have read <a href="http://www.joedolson.com/articles/my-calendar/faq/">the FAQ for this plug-in</a>.','my-calendar')." <span>(required)</span></label>
		</p>
		<p>
		<input type='checkbox' name='has_donated' id='has_donated' value='on' /> <label for='has_donated'>".__('I have <a href="http://www.joedolson.com/donate.php">made a donation to help support this plug-in</a>.','my-calendar')."</label>
		</p>
		<p>
		<input type='checkbox' name='has_purchased' id='has_purchased' value='on' /> <label for='has_purchased'>".__('I have <a href="http://www.joedolson.com/articles/my-calendar/users-guide/">purchased the User\'s Guide</a>, but could not find an answer to this question.','my-calendar')."</label>
		</p>
		<p>
		<label for='support_request'>Support Request:</label><br /><textarea name='support_request' id='support_request' cols='80' rows='10'>".stripslashes($request)."</textarea>
		</p>
		<p>
		<input type='submit' value='".__('Send Support Request','my-calendar')."' name='mc_support' class='button-primary' />
		</p>
		<p>".
		__('The following additional information will be sent with your support request:','my-calendar')
		."</p>
		<div class='mc_support'>
		".wpautop($data)."
		</div>
		</div>
	</form>";
}

// Actions -- these are action hooks attached to My Calendar events, usable to add additional actions during those events.
// Actions are only performed after their respective My Calendar events have been successfully completed.
// If there are errors in the My Calendar event, the action hook will not fire.
/*
mc_save_event
Performed when an event is added, updated, or copied. Arguments are the action taken ('edit','copy','add') and 
and an array of the processed event data

mc_delete_event
Performed when an event is deleted. Argument is the event_id.

mc_mass_delete_events
Performed when events are deleted en masse. Argument is an array of event_ids deleted.

*/

// Filters -- these are filters applied on My Calendar elements, which you can use to modify output. 
// Base values are empty unless otherwise specified.
// The actual filters are in the places they belong, but these are here for documentation.
/*
mc_before_calendar
	- inserts information before the calendar is output to the page. 
	- received arguments: calendar setup variables
	
mc_after_calendar
	- inserts information after the calendar is output to the page.
	- received arguments: calendar setup variables
	
mc_before_event_title
	- insert information at beginning of event title.
	- received arguments: event object
	
mc_after_event_title
	- insert information after event title.
	- received arguments: event object
	
mc_before_event
	- insert information before event details
	- received arguments: event object
	
mc_after_event
	- insert information after event details
	- received arguments: event object
	
mc_event_content
	- base value: event content output.
	- received arguments: event details as string, event object
	- runs for all event output formats.
	
	mc_event_content_mini
		- same as above, only runs in mini output
	mc_event_content_list
		- same as above, only runs in list output
	mc_event_content_single
		- same as above, only runs in single output
	mc_event_content_grid
		- same as above, only runs in grid output

mc_event_upcoming
	- base value: upcoming event output
	- received arguments: event object
	
mc_event_today
	- base value: today's event output
	- received arguments: event object

mc_category_selector
	- base value: category selector output
	- received arguments: categories object

mc_location_selector
	- base value: location selector output
	- received arguments: locations object

mc_location_list
	-base value: location list output
	-received arguments: locations object
	
mc_category_key
	- base value: category key output
	- received arguments: categories object

mc_previous_link
	- base value: previous link output
	- received arguments: array of previous link parameters

mc_next_link
	- base value: next link output
	- received arguments: array of previous link parameters

mc_jumpbox
	- base value: jumpbox output
	- received arguments: none
	
mc_filter_styles
	- base value: styles head block (string)
	- received arguments: URL for your selected My Calendar stylesheet
	
mc_filter_javascript_footer
	- base value: javascript footer block
	- received arguments: none
	
mc_filter_shortcodes
	- base value: array of shortcodes and values
	- received arguments: event object
*/