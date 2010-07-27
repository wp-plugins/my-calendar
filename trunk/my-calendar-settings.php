<?php
// Display the admin configuration page

if ($_POST['import'] == 'true') {
	my_calendar_import();
}
	
function my_calendar_import() {
if ( get_option('ko_calendar_imported') != 'true' ) {
global $wpdb;
$wpdb->hide_errors();
		define('KO_CALENDAR_TABLE', $wpdb->prefix . 'calendar');
		define('KO_CALENDAR_CATS', $wpdb->prefix . 'calendar_categories');
		$events = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_TABLE, 'ARRAY_A');
		$wpdb->print_error();
		$sql = "";
		foreach ($events as $key) {
			$title = mysql_real_escape_string($key['event_title']);
			$desc = mysql_real_escape_string($key['event_desc']);
			$begin = mysql_real_escape_string($key['event_begin']);
			$end = mysql_real_escape_string($key['event_end']);
			$time = mysql_real_escape_string($key['event_time']);
			$recur = mysql_real_escape_string($key['event_recur']);
			$repeats = mysql_real_escape_string($key['event_repeats']);
			$author = mysql_real_escape_string($key['event_author']);
			$category = mysql_real_escape_string($key['event_category']);
			$linky = mysql_real_escape_string($key['event_link']);
			
		    $sql = "INSERT INTO " . MY_CALENDAR_TABLE . " SET 
			event_title='" . ($title) . "', 
			event_desc='" . ($desc) . "', 
			event_begin='" . ($begin) . "', 
			event_end='" . ($end) . "', 
			event_time='" . ($time) . "', 
			event_recur='" . ($recur) . "', 
			event_repeats='" . ($repeats) . "', 
			event_author=".($author).", 
			event_category=".($category).", 
			event_link='".($linky)."';
			";
		$events_results = $wpdb->query($sql);		
		$wpdb->print_error(); 				
		}	
		$cats = $wpdb->get_results("SELECT * FROM " . KO_CALENDAR_CATS, 'ARRAY_A');
		$wpdb->print_error(); 
		
		$catsql = "";
		foreach ($cats as $key) {
			$name = mysql_real_escape_string($key['category_name']);
			$color = mysql_real_escape_string($key['category_colour']);
			$id = mysql_real_escape_string($key['category_id']);
			
			$catsql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET 
				category_id='".$id."',
				category_name='".$name."', 
				category_color='".$color."' 
				ON DUPLICATE KEY UPDATE 
				category_name='".$name."', 
				category_color='".$color."';
				";	
			$cats_results = $wpdb->query($catsql);
			$wpdb->print_error(); 			
		}
	 			

		if ($cats_results !== false) {
			$message = __('Categories imported successfully.','my-calendar');
		} else {
			$message = __('Categories not imported.','my-calendar');
		}
		if ($events_results !== false) {
			$e_message = __('Events imported successfully.','my-calendar');
		} else {
			$e_message = __('Events not imported.','my-calendar');
		}
		$return_value = "<div id='message' class='updated fade'><ul><li>$message</li><li>$e_message</li></ul></div>";
		echo $return_value;
		if ( $cats_results !== false && $events_results !== false ) {
			update_option( 'ko_calendar_imported','true' );
		}
	} 
}

function edit_my_calendar_config() {
  global $wpdb;

  // We can't use this page unless My Calendar is installed/upgraded
  check_my_calendar();

  if (isset($_POST['permissions'])) {
  
	if ($_POST['permissions'] == 'subscriber') { $new_perms = 'read'; }
	else if ($_POST['permissions'] == 'contributor') { $new_perms = 'edit_posts'; }
	else if ($_POST['permissions'] == 'author') { $new_perms = 'publish_posts'; }
	else if ($_POST['permissions'] == 'editor') { $new_perms = 'moderate_comments'; }
	else if ($_POST['permissions'] == 'admin') { $new_perms = 'manage_options'; }
	else { $new_perms = 'manage_options'; }

	$my_calendar_show_months = (int) $_POST['my_calendar_show_months'];
	$my_calendar_date_format = $_POST['my_calendar_date_format'];

	
	$disp_author = ($_POST['display_author']=='on')?'true':'false';
	$disp_jump = ($_POST['display_jump']=='on')?'true':'false';
	$my_calendar_show_map = ($_POST['my_calendar_show_map']=='on')?'true':'false';
	$my_calendar_show_address = ($_POST['my_calendar_show_address']=='on')?'true':'false';
	$my_calendar_show_heading = ($_POST['my_calendar_show_heading']=='on')?'true':'false';
	$my_calendar_notime_text = $_POST['my_calendar_notime_text'];
	$mc_previous_events = $_POST['mc_previous_events'];
	$mc_next_events = $_POST['mc_next_events'];
	
	$my_calendar_hide_icons = ($_POST['my_calendar_hide_icons']=='on')?'true':'false';
	$mc_apply_color = $_POST['mc_apply_color'];
	
	$my_calendar_caption = $_POST['my_calendar_caption'];
	$my_calendar_event_link_expires = ($_POST['mc_event_link_expires']=='on')?'true':'false';
	
	  update_option('can_manage_events',$new_perms);
	  update_option('display_author',$disp_author);
	  update_option('display_jump',$disp_jump);
	  update_option('my_calendar_show_months',$my_calendar_show_months);
	  update_option('my_calendar_date_format',$my_calendar_date_format);
	  update_option('my_calendar_show_map',$my_calendar_show_map);
	  update_option('my_calendar_show_address',$my_calendar_show_address); 
	  update_option('my_calendar_show_heading',$my_calendar_show_heading);
	  update_option('my_calendar_notime_text',$my_calendar_notime_text);
	  update_option('mc_next_events',$mc_next_events);
	  update_option('mc_previous_events',$mc_previous_events);	  
	  update_option('my_calendar_hide_icons',$my_calendar_hide_icons);
	  update_option('my_calendar_caption',$my_calendar_caption);
	  update_option('mc_event_link_expires',$my_calendar_event_link_expires);
	  update_option('mc_apply_color',$mc_apply_color);
	  
	  
      echo "<div class=\"updated\"><p><strong>".__('Settings saved','my-calendar').".</strong></p></div>";
    }

  // Pull the values out of the database that we need for the form
  $allowed_group = get_option('can_manage_events');
  $my_calendar_show_months = get_option('my_calendar_show_months');
  $my_calendar_show_map = get_option('my_calendar_show_map');
  $my_calendar_show_address = get_option('my_calendar_show_address');
  $disp_author = get_option('display_author');
  $mc_event_link_expires = get_option('mc_event_link_expires');
  // checkbox
  $disp_jump = get_option('display_jump');
  //checkbox

  if ($allowed_group == 'read') { $subscriber_selected='selected="selected"';}
  else if ($allowed_group == 'edit_posts') { $contributor_selected='selected="selected"';}
  else if ($allowed_group == 'publish_posts') { $author_selected='selected="selected"';}
  else if ($allowed_group == 'moderate_comments') { $editor_selected='selected="selected"';}
  else if ($allowed_group == 'manage_options') { $admin_selected='selected="selected"';}

  // Now we render the form
  ?>
    <div class="wrap">
<?php 
echo my_calendar_check_db();
?>	
    <h2><?php _e('My Calendar Options','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">



<div class="postbox">
	<h3><?php _e('Calendar Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
    <fieldset>
    <legend><?php _e('Calendar Options: Management','my-calendar'); ?></legend>
    <p>
    <label for="permissions"><?php _e('Choose the lowest user group that may manage events','my-calendar'); ?></label> <select id="permissions" name="permissions">
		<option value="subscriber"<?php echo $subscriber_selected ?>><?php _e('Subscriber','my-calendar')?></option>
		<option value="contributor" <?php echo $contributor_selected ?>><?php _e('Contributor','my-calendar')?></option>
		<option value="author" <?php echo $author_selected ?>><?php _e('Author','my-calendar')?></option>
		<option value="editor" <?php echo $editor_selected ?>><?php _e('Editor','my-calendar')?></option>
		<option value="admin" <?php echo $admin_selected ?>><?php _e('Administrator','my-calendar')?></option>
	</select>
	</p>
	</fieldset>
	<fieldset>
	<legend><?php _e('Calendar Options: Output','my-calendar'); ?></legend>
	<p>
    <label for="display_author"><?php _e('Do you want to display the author name on events?','my-calendar'); ?></label> <select id="display_author" name="display_author">
		<option value="on" <?php jd_cal_checkSelect('display_author','true'); ?>><?php _e('Yes','my-calendar') ?></option>
		<option value="off" <?php jd_cal_checkSelect('display_author','false'); ?>><?php _e('No','my-calendar') ?></option>
	</select>
	</p>

	<p>
	<label for="display_jump"><?php _e('Display a jumpbox for changing month and year quickly?','my-calendar'); ?></label> <select id="display_jump" name="display_jump">
		 <option value="on" <?php jd_cal_checkSelect('display_jump','true'); ?>><?php _e('Yes','my-calendar') ?></option>
		 <option value="off" <?php jd_cal_checkSelect('display_jump','false'); ?>><?php _e('No','my-calendar') ?></option>
	</select>
	</p>
	<p>
	<label for="my_calendar_show_months"><?php _e('In list mode, show how many months of events at a time:','my-calendar'); ?></label> <input type="text" size="3" id="my_calendar_show_months" name="my_calendar_show_months" value="<?php echo $my_calendar_show_months; ?>" />
	</p>
	<p>
	<label for="my_calendar_date_format"><?php _e('Date format in list mode','my-calendar'); ?></label> <input type="text" id="my_calendar_date_format" name="my_calendar_date_format" value="<?php if ( get_option('my_calendar_date_format')  == "") { echo get_option('date_format'); } else { echo get_option( 'my_calendar_date_format'); } ?>" /> Current: <?php if ( get_option('my_calendar_date_format') == '') { echo date_i18n(get_option('date_format')); } else { echo date_i18n(get_option('my_calendar_date_format')); } ?><br />
	<small><?php _e('Date format uses the same syntax as the <a href="http://php.net/date">PHP <code>date()</code> function</a>. Save option to update sample output.','my-calendar'); ?></small>
	</p>
	<p>
	<input type="checkbox" id="my_calendar_show_heading" name="my_calendar_show_heading" <?php jd_cal_checkCheckbox('my_calendar_show_heading','true'); ?> /> <label for="my_calendar_show_heading"><?php _e('Show Heading for Calendar','my-calendar'); ?></label>
    </p>	
	<p>
	<label for="my_calendar_notime_text"><?php _e('Label for events without a specific time'); ?></label> <input type="text" id="my_calendar_notime_text" name="my_calendar_notime_text" value="<?php if ( get_option('my_calendar_notime_text') == "") { _e('N/A','my-calendar'); } else { echo stripslashes( get_option('my_calendar_notime_text') ); } ?>" />
	</p>
	<p>
	<label for="mc_previous_events"><?php _e('Previous events link text'); ?></label> <input type="text" id="mc_previous_events" name="mc_previous_events" value="<?php if ( get_option('mc_previous_events') == "") { _e('Previous Events','my-calendar'); } else { echo stripslashes( get_option('mc_previous_events') ); } ?>" />
	</p>
	<p>
	<label for="mc_next_events"><?php _e('Next events link text'); ?></label> <input type="text" id="mc_next_events" name="mc_next_events" value="<?php if ( get_option('mc_next_events') == "") { _e('Next Events','my-calendar'); } else { echo stripslashes( get_option('mc_next_events') ); } ?>" />
	</p>	
	<p>
	<label for="my_calendar_caption"><?php _e('Additional caption text','my-calendar'); ?></label> <input type="text" id="my_calendar_caption" name="my_calendar_caption" value="<?php echo stripslashes( get_option('my_calendar_caption') ); ?>" /><br /><small><?php _e('The calendar caption is the text containing the displayed month and year in either list or calendar format. This text will be displayed following that existing text.'); ?></small>
	</p>
	<p>
	<input type="checkbox" id="my_calendar_hide_icons" name="my_calendar_hide_icons" <?php jd_cal_checkCheckbox('my_calendar_hide_icons','true'); ?> /> <label for="my_calendar_hide_icons"><?php _e('Hide category icons in output','my-calendar'); ?></label>
	</p>
	<p>
    <input type="checkbox" id="my_calendar_show_map" name="my_calendar_show_map" <?php jd_cal_checkCheckbox('my_calendar_show_map','true'); ?> /> <label for="my_calendar_show_map"><?php _e('Show Link to Google Map (when sufficient address information is available.)','my-calendar'); ?></label>
	</p>
	<p>
    <input type="checkbox" id="my_calendar_show_address" name="my_calendar_show_address" <?php jd_cal_checkCheckbox('my_calendar_show_address','true'); ?> /> <label for="my_calendar_show_address"><?php _e('Show Event Address in Details','my-calendar'); ?></label>
	</p>
	<p>
	<input type="checkbox" id="mc_event_link_expires" name="mc_event_link_expires" <?php jd_cal_checkCheckbox('mc_event_link_expires','true'); ?> /> <label for="mc_event_link_expires"><?php _e('Links associated with events will automatically expire after the event has passed.','my-calendar'); ?></label>
	</p>
	<p>
    <input type="radio" id="mc_apply_color_default" name="mc_apply_color" value="default" <?php jd_cal_checkCheckbox('mc_apply_color','default'); ?> /> <label for="mc_apply_color_default"><?php _e('Default usage of category colors.','my-calendar'); ?></label><br />
    <input type="radio" id="mc_apply_color_to_titles" name="mc_apply_color" value="font"  <?php jd_cal_checkCheckbox('mc_apply_color','font'); ?> /> <label for="mc_apply_color_to_titles"><?php _e('Apply category colors to event titles as a font color.','my-calendar'); ?></label><br />
	<input type="radio" id="mc_apply_bgcolor_to_titles" name="mc_apply_color" value="background"  <?php jd_cal_checkCheckbox('mc_apply_color','background'); ?> /> <label for="mc_apply_bgcolor_to_titles"><?php _e('Apply category colors to event titles as a background color.','my-calendar'); ?></label>	
	</p>
	</fieldset>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save Settings','my-calendar'); ?> &raquo;" />
	</p>
  </form>
  <?php
//update_option( 'ko_calendar_imported','false' );
    
if ( get_option( 'ko_calendar_imported' ) != 'true' ) {
  	if (function_exists('check_calendar')) {
	echo "<div class='import'>";
	echo "<p>";
	_e('My Calendar has identified that you have the Calendar plugin by Kieran O\'Shea installed. You can import those events and categories into the My Calendar database. Would you like to import these events?','my-calendar');
	echo "</p>";
	?>
		<form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
		<div>
		<input type="hidden" name="import" value="true" />
		<input type="submit" value="<?php _e('Import from Calendar','my-calendar'); ?>" name="import-calendar" class="button-primary" />
		</div>
		</form>
	<?php
	echo "</div>";
	}
}
	?>
  </div>

 </div>
 </div>
 </div>
  <?php
}
?>