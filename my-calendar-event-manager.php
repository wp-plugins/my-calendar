<?php
// The actual function called to render the manage events page and 
// to deal with posts
function edit_my_calendar() {
    global $current_user, $wpdb, $users_entries;

	if ( get_option('ko_calendar_imported') != 'true' ) {  
		if (function_exists('check_calendar')) {
		echo "<div id='message' class='updated'>";
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
		echo "<p>";
		_e('Although it is possible that this import could fail to import your events correctly, it should not have any impact on your existing Calendar database. If you encounter any problems, <a href="http://www.joedolson.com/contact.php">please contact me</a>!','my-calendar');
		echo "</p>";
		echo "</div>";
		}
	}

// First some quick cleaning up 
$edit = $create = $save = $delete = false;

$action = !empty($_POST['action']) ? $_POST['action'] : '';
$event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : '';

if ($_GET['mode'] == 'edit') {
	$action = "edit";
	$event_id = (int) $_GET['event_id'];
}

// Lets see if this is first run and create us a table if it is!
check_my_calendar();

if ($_GET['mode'] == 'delete') {
	    $sql = "SELECT event_title, event_author FROM " . MY_CALENDAR_TABLE . " WHERE event_id=" . (int) $_GET['event_id'];
	   $result = $wpdb->get_results( $sql, ARRAY_A );
	if ( mc_can_edit_event( $result[0]['event_author'] ) ) {
	?>
		<div class="error">
		<p><strong><?php _e('Delete Event','my-calendar'); ?>:</strong> <?php _e('Are you sure you want to delete this event?','my-calendar'); ?></p>
		<form action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar" method="post">
		<div>
		<input type="hidden" value="delete" name="action" />
		<input type="hidden" value="<?php echo (int) $_GET['event_id']; ?>" name="event_id" />
		<input type="submit" name="submit" class="button-primary" value="<?php _e('Delete','my-calendar'); echo " &quot;".$result[0]['event_title']."&quot;"; ?>" />
		</div>
		</form>
		</div>
	<?php
	} else {
	?>
		<div class="error">
		<p><strong><?php _e('You do not have permission to delete that event.','my-calendar'); ?></strong></p>
		</div>
	<?php
	}
}

if ( isset( $_POST['action'] ) ) {
$proceed = false;
// Deal with adding an event to the database
$event_author = (int) ($action == 'add')?($current_user->ID):($_POST['event_author']);

$output = mc_check_data($action,$_POST);
$proceed = $output[0];
 // end data checking and gathering
	if ( $action == 'add' && $proceed == true ) {
		$add = $output[2];
		$formats = array('%s','%s','%s','%s','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d');
		$result = $wpdb->insert( 
				MY_CALENDAR_TABLE, 
				$add, 
				$formats 
				);
			if ( !$result ) {
                ?>
			<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('I\'m sorry! I couldn\'d add that event to the database.','my-calendar'); ?></p></div>
			<?php
	      } else {
		?>
		<div class="updated"><p><?php _e('Event added. It will now show in your calendar.','my-calendar'); ?></p></div>
		<?php
	      }
	}
	if ( $action == 'edit' && $proceed == true ) {
		if ( mc_can_edit_event( $event_author ) ) {	
			$update = $output[2];
			$formats = array('%s','%s','%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%f','%f','%d');
			$result = $wpdb->update( 
					MY_CALENDAR_TABLE, 
					$update, 
					array( 'event_id'=>$event_id ),
					$formats, 
					'%d' );
				if ( $result === false ) {
					?>
					<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php echo _e('Your event was not updated.','my-calendar'); ?></p></div>
					<?php
				} else if ( $result === 0 ) {
					?>
					<div class="updated"><p><?php _e('Nothing was changed in that update.','my-calendar'); ?></p></div>
					<?php
				} else {
					?>
					<div class="updated"><p><?php _e('Event updated successfully','my-calendar'); ?></p></div>
					<?php
				}
		} else {
		?>
		<div class="error">
		<p><strong><?php _e('You do not have sufficient permissions to edit that event.','my-calendar'); ?></strong></p>
		</div>
		<?php
		}			
	}

	if ( $action == 'delete' ) {
// Deal with deleting an event from the database
		if ( empty($event_id) )	{
			?>
			<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e("You can't delete an event if you haven't submitted an event id",'my-calendar'); ?></p></div>
			<?php			
		} else {
			$sql = "DELETE FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
			$wpdb->get_results($sql);
			
			$sql = "SELECT event_id FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "'";
			$result = $wpdb->get_results($sql);
			
			if ( empty($result) || empty($result[0]->event_id) ) {
				?>
				<div class="updated"><p><?php _e('Event deleted successfully','my-calendar'); ?></p></div>
				<?php
			} else {
				?>
				<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Despite issuing a request to delete, the event still remains in the database. Please investigate.','my-calendar'); ?></p></div>
				<?php

			}		
		}
	}
}

// Now follows a little bit of code that pulls in the main 
// components of this page; the edit form and the list of events
?>

<div class="wrap">
<?php 
my_calendar_check_db();
?>
	<?php
	if ( $action == 'edit' || ($action == 'edit' && $error_with_saving == 1)) {
		?>
		<h2><?php _e('Edit Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('edit', $event_id);
		}	
	} else {
		?>
		<h2><?php _e('Add Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		
		<?php jd_events_edit_form(); ?>
	
		<h2><?php _e('Manage Events','my-calendar'); ?></h2>
		
		<?php 
		
		if ( isset( $_GET['sort'] ) ) {
			$sortby = (int) $_GET['sort'];
		} else {
			$sortby = 'default';
		}
		
		if ( isset( $_GET['order'] ) ) {
			if ( $_GET['order'] == 'ASC' ) {
				$sortdir = 'ASC';
			} else {
				$sortdir = 'default';
			}
		} else {
			$sortdir = 'default';
		}
		jd_events_display_list($sortby,$sortdir);
	}
	?>
</div>

<?php
} 

// The event edit form for the manage events admin page
function jd_events_edit_form($mode='add', $event_id=false) {
	global $wpdb,$users_entries;
	$data = false;
	
	if ( $event_id !== false ) {
		if ( intval($event_id) != $event_id ) {
			echo "<div class=\"error\"><p>".__('Sorry! That\'s an invalid event key.','my-calendar')."</p></div>";
			return;
		} else {
			$data = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_real_escape_string($event_id) . "' LIMIT 1");
			if ( empty($data) ) {
				echo "<div class=\"error\"><p>".__("Sorry! We couldn't find an event with that ID.",'my-calendar')."</p></div>";
				return;
			}
			$data = $data[0];
		}
		// Recover users entries if they exist; in other words if editing an event went wrong
		if (!empty($users_entries)) {
		    $data = $users_entries;
		  }
	} else {
	  // Deal with possibility that form was submitted but not saved due to error - recover user's entries here
	  $data = $users_entries;
	}
	global $user_ID;
	get_currentuserinfo();
	?>
	
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php if ($mode == "add") { _e('Add an Event','my-calendar'); } else { _e('Edit Event'); } ?></h3>
	<div class="inside">	
	<form name="my-calendar" id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar">
		<div>
		<input type="hidden" name="action" value="<?php echo $mode; ?>" />
		<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
		<input type="hidden" name="event_author" value="<?php echo $user_ID; ?>" />
		</div>
        <fieldset>
		<legend><?php _e('Enter your Event Information','my-calendar'); ?></legend>
		<p>
		<label for="event_title"><?php _e('Event Title','my-calendar'); ?></label> <input type="text" id="event_title" name="event_title" class="input" size="60" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_title)); ?>" />
		</p>
		<p>
		<label for="event_desc"><?php _e('Event Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><textarea id="event_desc" name="event_desc" class="input" rows="5" cols="50"><?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_desc)); ?></textarea>
		</p>
        <p>
		<label for="event_category"><?php _e('Event Category','my-calendar'); ?></label>
		<select id="event_category" name="event_category">
			<?php
				 // Grab all the categories and list them
			 $sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE;
								 $cats = $wpdb->get_results($sql);
				foreach($cats as $cat) {
				 echo '<option value="'.$cat->category_id.'"';
					if (!empty($data)) {
						if ($data->event_category == $cat->category_id){
						 echo 'selected="selected"';
						}
					}
					echo '>'.$cat->category_name.'</option>';
				}
			?>
            </select>
            </p>
			<p>
			<label for="event_link"><?php _e('Event Link (Optional)','my-calendar'); ?></label> <input type="text" id="event_link" name="event_link" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_link); ?>" /> <input type="checkbox" value="1" id="event_link_expires" name="event_link_expires"<?php if ( !empty($data) && $data->event_link_expires == '1' ) { echo " checked=\"checked\""; } else if ( !empty($data) && $data->event_link_expires == '0' ) { echo ""; } else if ( get_option( 'mc_event_link_expires' ) == 'true' ) { echo " checked=\"checked\""; } ?> /> <label for="event_link_expires"><?php _e('This link will expire when the event passes.','my-calendar'); ?></label>
			</p>
			<p>
			<label for="event_begin"><?php _e('Start Date (YYYY-MM-DD)','my-calendar'); ?></label> <input type="text" id="event_begin" name="event_begin" class="calendar_input" size="12" value="<?php if ( !empty($data) ) { echo htmlspecialchars($data->event_begin);} else {echo date_i18n("Y-m-d");} ?>" />
			</p>			
			<p>
			<label for="event_end"><?php _e('End Date (YYYY-MM-DD) (Optional)','my-calendar'); ?></label> <input type="text" name="event_end" id="event_end" class="calendar_input" size="12" value="<?php if ( !empty($data) ) {echo htmlspecialchars($data->event_end);} ?>" />
			</p>
			<p>
			<label for="event_time"><?php _e('Time (hh:mm)','my-calendar'); ?></label> <input type="text" id="event_time" name="event_time" class="input" size="12"
					value="<?php 
					$offset = (60*60*get_option('gmt_offset'));					
					if ( !empty($data) ) {
						if ($data->event_time == "00:00:00") {
						echo '';
						} else {
							echo date("H:i",strtotime($data->event_time));
						}
					} else {
						echo date_i18n("H:i",time()+$offset);
					}
					?>" /> <?php _e('Optional, set blank if your event is an all-day event or does not happen at a specific time.','my-calendar'); ?> <?php _e('Current time difference from GMT is ','my-calendar'); echo get_option('gmt_offset'); _e(' hour(s)', 'my-calendar'); ?>
			</p>
			<p>
			<label for="event_endtime"><?php _e('End Time (hh:mm)','my-calendar'); ?></label> <input type="text" id="event_endtime" name="event_endtime" class="input" size="12"
					value="<?php 
					if ( !empty($data) ) {
						if ($data->event_endtime == "00:00:00") {
						echo '';
						} else {
							echo date("H:i",strtotime($data->event_endtime));
						}
					} else {
						echo '';
					}
					?>" /> <?php _e('Optional. End times will not be displayed on events where this is not set.','my-calendar'); ?>
			</p>			
			</fieldset>
			<fieldset>
			<legend><?php _e('Recurring Events','my-calendar'); ?></legend> <?php
					if ($data->event_repeats != NULL) {
						$repeats = $data->event_repeats;
					} else {
						$repeats = 0;
					}
					if ($data->event_recur == "S") {
						$selected_s = 'selected="selected"';
					} else if ($data->event_recur == "D") {
						$selected_d = 'selected="selected"';						
					} else if ($data->event_recur == "W") {
						$selected_w = 'selected="selected"';
					} else if ($data->event_recur == "B") {
						$selected_b = 'selected="selected"';						
					} else if ($data->event_recur == "M")	{
						$selected_m = 'selected="selected"';
					} else if ($data->event_recur == "Y")	{
						$selected_y = 'selected="selected"';
					}
					?>
			<p>
			<label for="event_repeats"><?php _e('Repeats for','my-calendar'); ?></label> <input type="text" name="event_repeats" id="event_repeats" class="input" size="1" value="<?php echo $repeats; ?>" /> 
			<label for="event_recur"><?php _e('Units','my-calendar'); ?></label> <select name="event_recur" class="input" id="event_recur">
						<option class="input" <?php echo $selected_s; ?> value="S"><?php _e('Does not recur','my-calendar'); ?></option>
						<option class="input" <?php echo $selected_d; ?> value="D"><?php _e('Daily','my-calendar'); ?></option>						
						<option class="input" <?php echo $selected_w; ?> value="W"><?php _e('Weekly','my-calendar'); ?></option>
						<option class="input" <?php echo $selected_b; ?> value="B"><?php _e('Bi-weekly','my-calendar'); ?></option>						
						<option class="input" <?php echo $selected_m; ?> value="M"><?php _e('Monthly','my-calendar'); ?></option>
						<option class="input" <?php echo $selected_y; ?> value="Y"><?php _e('Annually','my-calendar'); ?></option>
			</select><br />
					<?php _e('Entering 0 means forever, if a unit is selected. If the recurrance unit is left at "Does not recur," the event will not reoccur.','my-calendar'); ?>
			</p>
			</fieldset>			
			<?php if ( get_option( 'my_calendar_show_address' ) == 'true' || get_option( 'my_calendar_show_map' ) == 'true' ) { ?>
			<fieldset>
			<legend><?php _e('Event Location','my-calendar'); ?></legend>
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<?php $locations = $wpdb->get_results("SELECT location_id,location_label FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY location_id ASC");
				if ( !empty($locations) ) {
			?>				
			<p>
			<label for="location_preset"><?php _e('Choose a preset location:','my-calendar'); ?></label> <select name="location_preset" id="location_preset">
				<option value="none"> -- </option>
				<?php
				foreach ( $locations as $location ) {
					echo "<option value=\"".$location->location_id."\">".stripslashes($location->location_label)."</option>";
				}
				?>
			
			</select>
			</p>
			<?php
				} else {
				?>
				<input type="hidden" name="location_preset" value="none" />
				<p><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations"><?php _e('Add recurring locations for later use.','my-calendar'); ?></a></p>
				<?php
				}
			?>			
			<p>
			<label for="event_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> <input type="text" id="event_label" name="event_label" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_label)); ?>" />
			</p>
			<p>
			<label for="event_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="event_street" name="event_street" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_street)); ?>" />
			</p>			
			<p>
			<label for="event_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="event_street2" name="event_street2" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_street2)); ?>" />
			</p>
			<p>
			<label for="event_city"><?php _e('City','my-calendar'); ?></label> <input type="text" id="event_city" name="event_city" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_city)); ?>" /> <label for="event_state"><?php _e('State/Province','my-calendar'); ?></label> <input type="text" id="event_state" name="event_state" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_state)); ?>" /> <label for="event_postcode"><?php _e('Postal Code','my-calendar'); ?></label> <input type="text" id="event_postcode" name="event_postcode" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_postcode)); ?>" />
			</p>			
			<p>
			<label for="event_country"><?php _e('Country','my-calendar'); ?></label> <input type="text" id="event_country" name="event_country" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars(stripslashes($data->event_country)); ?>" />
			</p>
			<p>
			<label for="event_zoom"><?php _e('Initial Zoom','my-calendar'); ?></label> 
				<select name="event_zoom" id="event_zoom">
				<option value="16"<?php if ( !empty( $data ) && ( $data->event_zoom == 16 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Neighborhood','my-calendar'); ?></option>
				<option value="14"<?php if ( !empty( $data ) && ( $data->event_zoom == 14 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Small City','my-calendar'); ?></option>
				<option value="12"<?php if ( !empty( $data ) && ( $data->event_zoom == 12 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Large City','my-calendar'); ?></option>
				<option value="10"<?php if ( !empty( $data ) && ( $data->event_zoom == 10 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Greater Metro Area','my-calendar'); ?></option>
				<option value="8"<?php if ( !empty( $data ) && ( $data->event_zoom == 8 ) ) { echo " selected=\"selected\""; } ?>><?php _e('State','my-calendar'); ?></option>
				<option value="6"<?php if ( !empty( $data ) && ( $data->event_zoom == 6 ) ) { echo " selected=\"selected\""; } ?>><?php _e('Region','my-calendar'); ?></option>
				</select>
			</p>
			<fieldset>
			<legend><?php _e('GPS Coordinates (optional)','my-calendar'); ?></legend>
			<p>
			<small><?php _e('If you supply GPS coordinates for your location, they will be used in place of any other address information to pinpoint your location.','my-calendar'); ?></small>
			</p>
			<p>
			<label for="event_longitude"><?php _e('Longitude','my-calendar'); ?></label> <input type="text" id="event_longitude" name="event_longitude" class="input" size="10" value="<?php if ( !empty( $data ) ) echo htmlspecialchars(stripslashes($data->event_longitude)); ?>" /> <label for="event_latitude"><?php _e('Latitude','my-calendar'); ?></label> <input type="text" id="event_latitude" name="event_latitude" class="input" size="10" value="<?php if ( !empty( $data ) ) echo htmlspecialchars(stripslashes($data->event_latitude)); ?>" />
			</p>			
			</fieldset>			
			</fieldset>
			<?php } ?>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Save Event','my-calendar'); ?> &raquo;" />
			</p>

	</form>
	</div>
	</div>
</div>
	<?php
}
// Used on the manage events admin page to display a list of events
function jd_events_display_list($sortby='default',$sortdir='default') {
	global $wpdb;
	if ($sortby == 'default') {
		$sortbyvalue = 'event_begin';
	} else {
		switch ($sortby) {
		    case 1: 
			$sortbyvalue = 'event_ID';
			break;
			case 2:
			$sortbyvalue = 'event_title';
			break;
			case 3:
			$sortbyvalue = 'event_desc';
			break;
			case 4:
			$sortbyvalue = 'event_begin';
			break;
			case 5 :
			$sortbyvalue = 'event_author';
			break;
			case 6:
			$sortbyvalue = 'event_category';
			break;
			case 7:
			$sortbyvalue = 'event_label';
			break;
			default:
			$sortbyvalue = 'event_begin';
		}
	}
	if ($sortdir == 'default') {
		$sortbydirection = 'DESC';
	} else {
		$sortbydirection = $sortdir;
	}
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " ORDER BY $sortbyvalue $sortbydirection");

	if ($sortbydirection == 'DESC') {
		$sorting = "&amp;order=ASC";
	} else {
		$sorting = '';
	}
	
	if ( !empty($events) ) {
		?>
		<table class="widefat page fixed" id="my-calendar-admin-table" summary="Table of Calendar Events">
		        <thead>
			    <tr>
				<th class="manage-column n4" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=1<?php echo $sorting; ?>"><?php _e('ID','my-calendar') ?></a></th>
				<th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=2<?php echo $sorting; ?>"><?php _e('Title','my-calendar') ?></a></th>
				<th class="manage-column" scope="col"><?php _e('Link','my-calendar') ?></th>
				<th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=7<?php echo $sorting; ?>"><?php _e('Location','my-calendar') ?></a></th>
				<th class="manage-column n8" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=3<?php echo $sorting; ?>"><?php _e('Description','my-calendar') ?></a></th>
				<th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=4<?php echo $sorting; ?>"><?php _e('Start Date','my-calendar') ?></a></th>
				<th class="manage-column n6" scope="col"><?php _e('Recurs','my-calendar') ?></th>
		        <th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=5<?php echo $sorting; ?>"><?php _e('Author','my-calendar') ?></a></th>
		        <th class="manage-column" scope="col"><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;sort=6<?php echo $sorting; ?>"><?php _e('Category','my-calendar') ?></a></th>
				<th class="manage-column n7" scope="col"><?php _e('Edit / Delete','my-calendar') ?></th>
			    </tr>
		        </thead>
		<?php
		$class = '';
		$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE ;
        $categories = $wpdb->get_results($sql);
			
		foreach ( $events as $event ) {
			$class = ($class == 'alternate') ? '' : 'alternate';
			$author = get_userdata($event->event_author); 
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $event->event_id; ?></th>
				<td><?php echo stripslashes($event->event_title); ?></td>
				<td><?php echo stripslashes($event->event_link); ?></td>
				<td><?php echo stripslashes($event->event_label); ?></td>
				<td><?php echo substr(strip_tags(stripslashes($event->event_desc)),0,60); ?>&hellip;</td>
				<?php if ($event->event_time != "00:00:00") { $eventTime = date_i18n(get_option('time_format'), strtotime($event->event_time)); } else { $eventTime = get_option('my_calendar_notime_text'); } ?>
				<td><?php echo "$event->event_begin ($eventTime)"; ?></td>
				<?php /* <td><?php echo $event->event_end; ?></td> */ ?>
				<td>
				<?php 
					// Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { _e('Never','my-calendar'); } 
					else if ($event->event_recur == 'D') { _e('Daily','my-calendar'); }
					else if ($event->event_recur == 'W') { _e('Weekly','my-calendar'); }
					else if ($event->event_recur == 'B') { _e('Bi-Weekly','my-calendar'); }
					else if ($event->event_recur == 'M') { _e('Monthly','my-calendar'); }
					else if ($event->event_recur == 'Y') { _e('Yearly','my-calendar'); }
				?>&thinsp;&ndash;&thinsp;<?php
				        // Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { echo __('N/A','my-calendar'); }
					else if ($event->event_repeats == 0) { echo __('Forever','my-calendar'); }
					else if ($event->event_repeats > 0) { echo $event->event_repeats.' '.__('Times','my-calendar'); }					
				?>				
				</td>
				<td><?php echo $author->display_name; ?></td>
                                <?php
								$this_category = $event->event_category;
								foreach ($categories as $key=>$value) {
									if ($value->category_id == $this_category) {
										$this_cat = $categories[$key];
									} 
								}
                                ?>
				<td><div class="category-color" style="background-color:<?php echo $this_cat->category_color;?>;"> </div> <?php echo stripslashes($this_cat->category_name); ?></td>
				<?php unset($this_cat); ?>
				<td>
				<?php if ( mc_can_edit_event( $event->event_author ) ) { ?>
				<a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=edit&amp;event_id=<?php echo $event->event_id;?>" class='edit'><?php echo __('Edit','my-calendar'); ?></a> &middot; <a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar&amp;mode=delete&amp;event_id=<?php echo $event->event_id;?>" class="delete"><?php echo __('Delete','my-calendar'); ?></a></td>
				<?php } else { echo "Not editable."; } ?>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	} else {
		?>
		<p><?php _e("There are no events in the database!",'my-calendar')	?></p>
		<?php	
	}
}

function mc_check_data($action,$_POST) {
global $wpdb, $current_user;
if ( $action == 'add' || $action == 'edit' ) {
	$title = !empty($_POST['event_title']) ? $_POST['event_title'] : '';
	$desc = !empty($_POST['event_desc']) ? $_POST['event_desc'] : '';
	$begin = !empty($_POST['event_begin']) ? $_POST['event_begin'] : '';
	$end = !empty($_POST['event_end']) ? $_POST['event_end'] : $begin;
	$time = !empty($_POST['event_time']) ? $_POST['event_time'] : '';
	$endtime = !empty($_POST['event_endtime']) ? $_POST['event_endtime'] : '';
	$recur = !empty($_POST['event_recur']) ? $_POST['event_recur'] : '';
	$repeats = !empty($_POST['event_repeats']) ? $_POST['event_repeats'] : 0;
	$category = !empty($_POST['event_category']) ? $_POST['event_category'] : '';
    $linky = !empty($_POST['event_link']) ? $_POST['event_link'] : '';
    $expires = !empty($_POST['event_link_expires']) ? $_POST['event_link_expires'] : '0';	
	$location_preset = !empty($_POST['location_preset']) ? $_POST['location_preset'] : '';
    $event_author = !empty($_POST['event_author']) ? $_POST['event_author'] : '';
	// set location
		if ($location_preset != 'none') {
			$sql = "SELECT * FROM " . MY_CALENDAR_LOCATIONS_TABLE . " WHERE location_id = $location_preset";
			$location = $wpdb->get_row($sql);
			$event_label = $location->location_label;
			$event_street = $location->location_street;
			$event_street2 = $location->location_street2;
			$event_city = $location->location_city;
			$event_state = $location->location_state;
			$event_postcode = $location->location_postcode;
			$event_country = $location->location_country;
			$event_longitude = $location->location_longitude;
			$event_latitude = $location->location_latitude;
			$event_zoom = $location->location_zoom;
		} else {
	    $event_label = !empty($_POST['event_label']) ? $_POST['event_label'] : '';
	    $event_street = !empty($_POST['event_street']) ? $_POST['event_street'] : '';
	    $event_street2 = !empty($_POST['event_street2']) ? $_POST['event_street2'] : '';
	    $event_city = !empty($_POST['event_city']) ? $_POST['event_city'] : '';
	    $event_state = !empty($_POST['event_state']) ? $_POST['event_state'] : '';
	    $event_postcode = !empty($_POST['event_postcode']) ? $_POST['event_postcode'] : '';
	    $event_country = !empty($_POST['event_country']) ? $_POST['event_country'] : '';
		$event_longitude = !empty($_POST['event_longitude']) ? $_POST['event_longitude'] : '';	
	    $event_latitude = !empty($_POST['event_latitude']) ? $_POST['event_latitude'] : '';	
	    $event_zoom = !empty($_POST['event_zoom']) ? $_POST['event_zoom'] : '';	
	    }
	// Deal with those who have magic quotes turned on
	if ( ini_get('magic_quotes_gpc') ) {
		$title = stripslashes($title);
		$desc = stripslashes($desc);
		$begin = stripslashes($begin);
		$end = stripslashes($end);
		$time = stripslashes($time);
		$endtime = stripslashes($endtime);
		$recur = stripslashes($recur);
		$repeats = stripslashes($repeats);
		$category = stripslashes($category);
		$linky = stripslashes($linky);	
		$expires = stripslashes($expires);
		$event_label = stripslashes($event_label);
		$event_street = stripslashes($event_street);
		$event_street2 = stripslashes($event_street2);
		$event_city = stripslashes($event_city);
		$event_state = stripslashes($event_state);
		$event_postcode = stripslashes($event_postcode);
		$event_country = stripslashes($event_country);	
		$event_longitude = stripslashes($event_longitude);	
		$event_latitude = stripslashes($event_latitude);	
		$event_zoom = stripslashes($event_zoom);	
	}	

	// Perform some validation on the submitted dates - this checks for valid years and months
	$date_format_one = '/^([0-9]{4})-([0][1-9])-([0-3][0-9])$/';
    $date_format_two = '/^([0-9]{4})-([1][0-2])-([0-3][0-9])$/';
	if ((preg_match($date_format_one,$begin) || preg_match($date_format_two,$begin)) && (preg_match($date_format_one,$end) || preg_match($date_format_two,$end))) {
            // We know we have a valid year and month and valid integers for days so now we do a final check on the date
        $begin_split = split('-',$begin);
	    $begin_y = $begin_split[0]; 
	    $begin_m = $begin_split[1];
	    $begin_d = $begin_split[2];
        $end_split = split('-',$end);
	    $end_y = $end_split[0];
	    $end_m = $end_split[1];
	    $end_d = $end_split[2];
            if (checkdate($begin_m,$begin_d,$begin_y) && checkdate($end_m,$end_d,$end_y)) {
		       // Ok, now we know we have valid dates, we want to make sure that they are either equal or that the end date is later than the start date
			       if (strtotime($end) >= strtotime($begin)) {
				   $start_date_ok = 1;
				   $end_date_ok = 1;
					} else {
				   ?>
				   <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Your event end date must be either after or the same as your event begin date','my-calendar'); ?></p></div>
				   <?php
					}
		    } else {
			?>
	                <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Your date formatting is correct but one or more of your dates is invalid. Check for number of days in month and leap year related errors.','my-calendar'); ?></p></div>
	                <?php
		    }
	  }	else {
	    ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Both start and end dates must be in the format YYYY-MM-DD','my-calendar'); ?></p></div>
            <?php
	  }
        // We check for a valid time, or an empty one
        $time_format_one = '/^([0-1][0-9]):([0-5][0-9])$/';
		$time_format_two = '/^([2][0-3]):([0-5][0-9])$/';
        if (preg_match($time_format_one,$time) || preg_match($time_format_two,$time) || $time == '') {
            $time_ok = 1;
			if ( strlen($time) == 5 ) { $time = $time . ":00";	}
			if ( strlen($time) == 0 ) { $time = "00:00:00"; }
        } else {
            ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The time field must either be blank or be entered in the format hh:mm','my-calendar'); ?></p></div>
            <?php
	    }
        // We check for a valid end time, or an empty one
        if (preg_match($time_format_one,$endtime) || preg_match($time_format_two,$endtime) || $endtime == '') {
            $endtime_ok = 1;
			if ( strlen($endtime) == 5 ) { $endtime = $endtime . ":00";	}
			if ( strlen($endtime) == 0 ) { $endtime = "00:00:00"; }
        } else {
            ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The end time field must either be blank or be entered in the format hh:mm','my-calendar'); ?></p></div>
            <?php
	    }		
	// We check to make sure the URL is alright                                                        
	if (preg_match('/^(http)(s?)(:)\/\//',$linky) || $linky == '') {
	    $url_ok = 1;
	  }	else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The URL entered must either be prefixed with http:// or be completely blank','my-calendar'); ?></p></div>
              <?php
	  }
	// The title must be at least one character in length and no more than 255 - only basic punctuation is allowed
	$title_length = strlen($title);
	if ( $title_length > 1 && $title_length <= 255 ) {
	    $title_ok =1;
	  }	else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The event title must be between 1 and 255 characters in length.','my-calendar'); ?></p></div>
              <?php
	  }
	// We run some checks on recurrance                                                                        
	if (($repeats == 0 && $recur == 'S') || (($repeats >= 0) && ($recur == 'W' || $recur == 'B' || $recur == 'M' || $recur == 'Y' || $recur == 'D'))) {
	    $recurring_ok = 1;
	  }	else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The repetition value must be 0 unless a type of recurrance is selected.','my-calendar'); ?></p></div>
              <?php
	  }
	if ($start_date_ok == 1 && $end_date_ok == 1 && $time_ok == 1 && $endtime_ok == 1 && $url_ok == 1 && $title_ok == 1 && $recurring_ok == 1) {
		$proceed = true;
		if ($action == 'add' ) {
			$submit = array(
				'event_begin'=>$begin, 
				'event_end'=>$end, 
				'event_title'=>$title, 
				'event_desc'=>$desc, 			
				'event_time'=>$time, 
				'event_recur'=>$recur, 
				'event_repeats'=>$repeats, 
				'event_author'=>$current_user->ID,
				'event_category'=>$category, 
				'event_link'=>$linky,
				'event_label'=>$event_label, 
				'event_street'=>$event_street, 
				'event_street2'=>$event_street2, 
				'event_city'=>$event_city, 
				'event_state'=>$event_state, 
				'event_postcode'=>$event_postcode, 
				'event_country'=>$event_country,
				'event_endtime'=>$endtime, 								
				'event_link_expires'=>$expires, 				
				'event_longitude'=>$event_longitude,
				'event_latitude'=>$event_latitude,
				'event_zoom'=>$event_zoom);
			
		} else if ($action == 'edit') {
			$submit = array(
				'event_begin'=>$begin, 
				'event_end'=>$end, 
				'event_title'=>$title, 
				'event_desc'=>$desc, 			
				'event_time'=>$time, 
				'event_recur'=>$recur, 
				'event_repeats'=>$repeats, 
				'event_category'=>$category, 
				'event_link'=>$linky,
				'event_label'=>$event_label, 
				'event_street'=>$event_street, 
				'event_street2'=>$event_street2, 
				'event_city'=>$event_city, 
				'event_state'=>$event_state, 
				'event_postcode'=>$event_postcode, 
				'event_country'=>$event_country,
				'event_endtime'=>$endtime, 				
				'event_link_expires'=>$expires, 				
				'event_longitude'=>$event_longitude,
				'event_latitude'=>$event_latitude,
				'event_zoom'=>$event_zoom);			
		}
		
		
	} else {
	    // The form is going to be rejected due to field validation issues, so we preserve the users entries here
			$users_entries->event_title = $title;
			$users_entries->event_desc = $desc;
			$users_entries->event_begin = $begin;
			$users_entries->event_end = $end;
			$users_entries->event_time = $time;
			$users_entries->event_endtime = $endtime;
			$users_entries->event_recur = $recur;
			$users_entries->event_repeats = $repeats;
			$users_entries->event_category = $category;
			$users_entries->event_link = $linky;
			$users_entries->event_link_expires = $expires;
			$users_entries->event_label = $event_label;
			$users_entries->event_street = $event_street;
			$users_entries->event_street2 = $event_street2;
			$users_entries->event_city = $event_city;
			$users_entries->event_state = $event_state;
			$users_entries->event_postcode = $event_postcode;
			$users_entries->event_country = $event_country;	
			$users_entries->event_longitude = $event_longitude;		
			$users_entries->event_latitude = $event_latitude;		
			$users_entries->event_zoom = $event_zoom;
			$users_entries->event_author = $event_author;
			$proceed = false;
	  }	
	  
	  
	}
	$data = array($proceed, $users_entries, $submit);
	return $data;
}

?>