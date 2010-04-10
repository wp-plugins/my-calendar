<?php
// The actual function called to render the manage events page and 
// to deal with posts
function edit_my_calendar() {
    global $current_user, $wpdb, $users_entries;
  ?>

<?php
// First some quick cleaning up 
$edit = $create = $save = $delete = false;

$action = !empty($_POST['action']) ? $_POST['action'] : '';
$event_id = !empty($_POST['event_id']) ? $_POST['event_id'] : '';

if ($_GET['action'] == 'edit') {
	$action = "edit";
	$event_id = (int) $_GET['event_id'];
}

// Lets see if this is first run and create us a table if it is!
check_my_calendar();

if ($_GET['action'] == 'delete') {
	    $sql = "SELECT event_title FROM " . MY_CALENDAR_TABLE . " WHERE event_id=" . (int) $_GET['event_id'];
	   $result = $wpdb->get_results($sql);
?>
	<div class="error">
	<p><strong><?php _e('Delete Event','my-calendar'); ?>:</strong> <?php _e('Are you sure you want to delete this event?','my-calendar'); ?></p>
	<form action="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=my-calendar" method="post">
	<div>
	<input type="hidden" value="delete" name="action" />
	<input type="hidden" value="<?php echo (int) $_GET['event_id']; ?>" name="event_id" />
	<input type="submit" name="submit" class="button-primary" value="<?php _e('Delete','my-calendar'); echo " &quot;".$result[0]->event_title."&quot;"; ?>" />
	</div>
	</form>
	</div>
<?php
}

// Deal with adding an event to the database
if ( $action == 'add' ) {
	$title = !empty($_POST['event_title']) ? $_POST['event_title'] : '';
	$desc = !empty($_POST['event_desc']) ? $_POST['event_desc'] : '';
	$begin = !empty($_POST['event_begin']) ? $_POST['event_begin'] : '';
	$end = !empty($_POST['event_end']) ? $_POST['event_end'] : $begin;
	$time = !empty($_POST['event_time']) ? $_POST['event_time'] : '';
	$recur = !empty($_POST['event_recur']) ? $_POST['event_recur'] : '';
	$repeats = !empty($_POST['event_repeats']) ? $_POST['event_repeats'] : '';
	$category = !empty($_POST['event_category']) ? $_POST['event_category'] : '';
    $linky = !empty($_POST['event_link']) ? $_POST['event_link'] : '';
    $event_label = !empty($_POST['event_label']) ? $_POST['event_label'] : '';
    $event_street = !empty($_POST['event_street']) ? $_POST['event_street'] : '';
    $event_street2 = !empty($_POST['event_street2']) ? $_POST['event_street2'] : '';
    $event_city = !empty($_POST['event_city']) ? $_POST['event_city'] : '';
    $event_state = !empty($_POST['event_state']) ? $_POST['event_state'] : '';
    $event_postcode = !empty($_POST['event_postcode']) ? $_POST['event_postcode'] : '';
    $event_country = !empty($_POST['event_country']) ? $_POST['event_country'] : '';	

	// Deal with the fools who have left magic quotes turned on
	if ( ini_get('magic_quotes_gpc') ) {
		$title = stripslashes($title);
		$desc = stripslashes($desc);
		$begin = stripslashes($begin);
		$end = stripslashes($end);
		$time = stripslashes($time);
		$recur = stripslashes($recur);
		$repeats = stripslashes($repeats);
		$category = stripslashes($category);
		$linky = stripslashes($linky);	
		$event_label = stripslashes($event_label);
		$event_street = stripslashes($event_street);
		$event_street2 = stripslashes($event_street2);
		$event_city = stripslashes($event_city);
		$event_state = stripslashes($event_state);
		$event_postcode = stripslashes($event_postcode);
		$event_country = stripslashes($event_country);		
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
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Both start and end dates must be entered and be in the format YYYY-MM-DD','my-calendar'); ?></p></div>
            <?php
	  }
        // We check for a valid time, or an empty one
        $time_format_one = '/^([0-1][0-9]):([0-5][0-9])$/';
		$time_format_two = '/^([2][0-3]):([0-5][0-9])$/';
        if (preg_match($time_format_one,$time) || preg_match($time_format_two,$time) || $time == '') {
            $time_ok = 1;
        } else {
            ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The time field must either be blank or be entered in the format hh:mm','my-calendar'); ?></p></div>
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
	// The title must be at least one character in length and no more than 60 - only basic punctuation is allowed
	if (preg_match('/^[a-zA-Z0-9\'\"]{1}[a-zA-Z0-9[:space:][.,;:()\'\"]{0,60}$/',$title)) {
	    $title_ok =1;
	  }	else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The event title must be between 1 and 60 characters in length. Some punctuation characters may not be allowed.','my-calendar'); ?></p></div>
              <?php
	  }
	// We run some checks on recurrance                                                                        
	if (($repeats == 0 && $recur == 'S') || (($repeats >= 0) && ($recur == 'W' || $recur == 'M' || $recur == 'Y' || $recur == 'D'))) {
	    $recurring_ok = 1;
	  }	else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The repetition value must be 0 unless a type of recurrance is selected in which case the repetition value must be 0 or higher','my-calendar'); ?></p></div>
              <?php
	  }
	if ($start_date_ok == 1 && $end_date_ok == 1 && $time_ok == 1 && $url_ok == 1 && $title_ok == 1 && $recurring_ok == 1) {
	    $sql = "INSERT INTO " . MY_CALENDAR_TABLE . " SET 
		event_title='" . mysql_escape_string($title) . "', 
		event_desc='" . mysql_escape_string($desc) . "', 
		event_begin='" . mysql_escape_string($begin) . "', 
		event_end='" . mysql_escape_string($end) . "', 
		event_time='" . mysql_escape_string($time) . "', 
		event_recur='" . mysql_escape_string($recur) . "', 
		event_repeats='" . mysql_escape_string($repeats) . "', 
		event_author=".$current_user->ID.", 
		event_category=".mysql_escape_string($category).", 
		event_link='".mysql_escape_string($linky)."',
		event_label='".mysql_escape_string($event_label)."', 
		event_street='".mysql_escape_string($event_street)."', 
		event_street2='".mysql_escape_string($event_street2)."', 
		event_city='".mysql_escape_string($event_city)."', 
		event_state='".mysql_escape_string($event_state)."', 
		event_postcode='".mysql_escape_string($event_postcode)."',
		event_country='".mysql_escape_string($event_country)."'";
	     
	    $wpdb->get_results($sql);
	
	    $sql = "SELECT event_id FROM " . MY_CALENDAR_TABLE . " WHERE event_title='" . mysql_escape_string($title) . "'"
		. " AND event_desc='" . mysql_escape_string($desc) . "' AND event_begin='" . mysql_escape_string($begin) . "' AND event_end='" . mysql_escape_string($end) . "' AND event_recur='" . mysql_escape_string($recur) . "' AND event_repeats='" . mysql_escape_string($repeats) . "' LIMIT 1";
	    $result = $wpdb->get_results($sql);
	
	    if ( empty($result) || empty($result[0]->event_id) ) {
                ?>
			<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('An event with the details you submitted could not be found in the database. This may indicate a problem with your database or the way in which it is configured.','my-calendar'); ?></p></div>
			<?php
	      } else {
		?>
		<div class="updated"><p><?php _e('Event added. It will now show in your calendar.','my-calendar'); ?></p></div>
		<?php
	      }
	  } else {
	    // The form is going to be rejected due to field validation issues, so we preserve the users entries here
			$users_entries->event_title = $title;
			$users_entries->event_desc = $desc;
			$users_entries->event_begin = $begin;
			$users_entries->event_end = $end;
			$users_entries->event_time = $time;
			$users_entries->event_recur = $recur;
			$users_entries->event_repeats = $repeats;
			$users_entries->event_category = $category;
			$users_entries->event_link = $linky;
			$users_entries->event_label = $event_label;
			$users_entries->event_street = $event_street;
			$users_entries->event_street2 = $event_street2;
			$users_entries->event_city = $event_city;
			$users_entries->event_state = $event_state;
			$users_entries->event_postcode = $event_postcode;
			$users_entries->event_country = $event_country;		
	  }
// Permit saving of events that have been edited	  
} elseif ( $action == 'edit_save' ) {
	$title = !empty($_POST['event_title']) ? $_POST['event_title'] : '';
	$desc = !empty($_POST['event_desc']) ? $_POST['event_desc'] : '';
	$begin = !empty($_POST['event_begin']) ? $_POST['event_begin'] : '';
	$end = !empty($_POST['event_end']) ? $_POST['event_end'] : $begin;
	$time = !empty($_POST['event_time']) ? $_POST['event_time'] : '';
	$recur = !empty($_POST['event_recur']) ? $_POST['event_recur'] : '';
	$repeats = !empty($_POST['event_repeats']) ? $_POST['event_repeats'] : '';
	$category = !empty($_POST['event_category']) ? $_POST['event_category'] : '';
    $linky = !empty($_POST['event_link']) ? $_POST['event_link'] : '';
    $event_label = !empty($_POST['event_label']) ? $_POST['event_label'] : '';
    $event_street = !empty($_POST['event_street']) ? $_POST['event_street'] : '';
    $event_street2 = !empty($_POST['event_street2']) ? $_POST['event_street2'] : '';
    $event_city = !empty($_POST['event_city']) ? $_POST['event_city'] : '';
    $event_state = !empty($_POST['event_state']) ? $_POST['event_state'] : '';
    $event_postcode = !empty($_POST['event_postcode']) ? $_POST['event_postcode'] : '';
    $event_country = !empty($_POST['event_country']) ? $_POST['event_country'] : '';
	

	// Deal with the fools who have left magic quotes turned on
	if ( ini_get('magic_quotes_gpc') ) {
		$title = stripslashes($title);
		$desc = stripslashes($desc);
		$begin = stripslashes($begin);
		$end = stripslashes($end);
		$time = stripslashes($time);
		$recur = stripslashes($recur);
		$repeats = stripslashes($repeats);
        $category = stripslashes($category);
        $linky = stripslashes($linky);
		$event_label = stripslashes($event_label);
		$event_street = stripslashes($event_street);
		$event_street2 = stripslashes($event_street2);
		$event_city = stripslashes($event_city);
		$event_state = stripslashes($event_state);
		$event_postcode = stripslashes($event_postcode);
		$event_country = stripslashes($event_country);
	}
	
	if ( empty($event_id) ) {
		?>
		<div class="error"><p><strong><?php _e('Failure','my-calendar'); ?>:</strong> <?php _e("You can't update an event if you haven't submitted an event id",'my-calendar'); ?></p></div>
		<?php		
	} else {
	  // Perform some validation on the submitted dates - this checks for valid years and months
      $date_format_one = '/^([0-9]{4})-([0][1-9])-([0-3][0-9])$/';
	  $date_format_two = '/^([0-9]{4})-([1][0-2])-([0-3][0-9])$/';
	  if ((preg_match($date_format_one,$begin) || preg_match($date_format_two,$begin)) && (preg_match($date_format_one,$end) || preg_match($date_format_two,$end)))	{
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
	    } else {
            ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('Both start and end dates must be entered and be in the format YYYY-MM-DD','my-calendar'); ?></p></div>
            <?php
	    }
	  // We check for a valid time, or an empty one
	  $time_format_one = '/^([0-1][0-9]):([0-5][0-9])$/';
	  $time_format_two = '/^([2][0-3]):([0-5][0-9])$/';
	    if (preg_match($time_format_one,$time) || preg_match($time_format_two,$time) || $time == '') {
	      $time_ok = 1;
	    } else {
            ?>
            <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The time field must either be blank or be entered in the format hh:mm','my-calendar'); ?></p></div>
            <?php
	    }
          // We check to make sure the URL is alright
	  if (preg_match('/^(http)(s?)(:)\/\//',$linky) || $linky == '') {
	      $url_ok = 1;
	    } else {
	      ?>
	      <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The URL entered must either be prefixed with http:// or be completely blank','my-calendar'); ?></p></div>
	      <?php
	    }
	  // The title must be at least one character in length and no more than 60 - no non-standard characters allowed
	if (preg_match('/^[a-zA-Z0-9\'\"]{1}[a-zA-Z0-9[:space:][.,;:()\'\"]{0,60}$/',$title)) {
	      $title_ok =1;
	    } else {
	      ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The event title must be between 1 and 60 characters in length. Some punctuation characters may not be allowed.','my-calendar'); ?></p></div>
              <?php
	    }
	  // We run some checks on recurrance              
          if (($repeats == 0 && $recur == 'S') || (($repeats >= 0) && ($recur == 'W' || $recur == 'M' || $recur == 'Y' || $recur == 'D' ))) {
              $recurring_ok = 1;
            } else {
              ?>
              <div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e('The repetition value must be 0 unless a type of recurrance is selected in which case the repetition value must be 0 or higher','my-calendar'); ?></p></div>
              <?php
	    }
	  if ($start_date_ok == 1 && $end_date_ok == 1 && $time_ok == 1 && $url_ok == 1 && $title_ok && $recurring_ok == 1) {
		$sql = "UPDATE " . MY_CALENDAR_TABLE . " SET 
				event_title='" . mysql_escape_string($title) . "', 
				event_desc='" . mysql_escape_string($desc) . "', 
				event_begin='" . mysql_escape_string($begin) . "', 
				event_end='" . mysql_escape_string($end) . "', 
				event_time='" . mysql_escape_string($time) . "', 
				event_recur='" . mysql_escape_string($recur) . "', 
				event_repeats='" . mysql_escape_string($repeats) . "', 
				event_author=".$current_user->ID . ", 
				event_category=".mysql_escape_string($category).", 
				event_link='".mysql_escape_string($linky)."', 
				event_label='".mysql_escape_string($event_label)."', 
				event_street='".mysql_escape_string($event_street)."', 
				event_street2='".mysql_escape_string($event_street2)."', 
				event_city='".mysql_escape_string($event_city)."', 
				event_state='".mysql_escape_string($event_state)."', 
				event_postcode='".mysql_escape_string($event_postcode)."', 
				event_country='".mysql_escape_string($event_country)."' 
				WHERE event_id='" . mysql_escape_string($event_id) . "'";
		     
		$wpdb->get_results($sql);
		
		$sql = "SELECT event_id FROM " . MY_CALENDAR_TABLE . " WHERE event_title='" . mysql_escape_string($title) . "'"
		     . " AND event_desc='" . mysql_escape_string($desc) . "' AND event_begin='" . mysql_escape_string($begin) . "' AND event_end='" . mysql_escape_string($end) . "' AND event_recur='" . mysql_escape_string($recur) . "' AND event_repeats='" . mysql_escape_string($repeats) . "' LIMIT 1";
		$result = $wpdb->get_results($sql);
		
			if ( empty($result) || empty($result[0]->event_id) ) {
				?>
				<div class="error"><p><strong><?php _e('Failure','my-calendar'); ?>:</strong> <?php _e('The database failed to return data to indicate the event has been updated sucessfully. This may indicate a problem with your database or the way in which it is configured.','my-calendar'); ?></p></div>
				<?php
			} else {
				?>
				<div class="updated"><p><?php _e('Event updated successfully','my-calendar'); ?></p></div>
				<?php
			}
	    } else {
	      // The form is going to be rejected due to field validation issues, so we preserve the users entries here
          $users_entries->event_title = $title;
	      $users_entries->event_desc = $desc;
	      $users_entries->event_begin = $begin;
	      $users_entries->event_end = $end;
	      $users_entries->event_time = $time;
	      $users_entries->event_recur = $recur;
	      $users_entries->event_repeats = $repeats;
	      $users_entries->event_category = $category;
	      $users_entries->event_link = $linky;
		  $users_entries->event_label = $event_label;
		  $users_entries->event_street = $event_street;
		  $users_entries->event_street2 = $event_street2;
		  $users_entries->event_city = $event_city;
		  $users_entries->event_state = $event_state;
		  $users_entries->event_postcode = $event_postcode;
		  $users_entries->event_country = $event_country;
	      $error_with_saving = 1;
	    }		
	}
} elseif ( $action == 'delete' ) {
// Deal with deleting an event from the database

	if ( empty($event_id) )	{
		?>
		<div class="error"><p><strong><?php _e('Error','my-calendar'); ?>:</strong> <?php _e("You can't delete an event if you haven't submitted an event id",'my-calendar'); ?></p></div>
		<?php			
	} else {
		$sql = "DELETE FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "'";
		$wpdb->get_results($sql);
		
		$sql = "SELECT event_id FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "'";
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

// Now follows a little bit of code that pulls in the main 
// components of this page; the edit form and the list of events
?>

<div class="wrap">
	<?php
	if ( $action == 'edit' || ($action == 'edit_save' && $error_with_saving == 1)) {
		?>
		<h2><?php _e('Edit Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		<?php
		if ( empty($event_id) ) {
			echo "<div class=\"error\"><p>".__("You must provide an event id in order to edit it",'my-calendar')."</p></div>";
		} else {
			jd_events_edit_form('edit_save', $event_id);
		}	
	} else {
		?>
		<h2><?php _e('Add Event','my-calendar'); ?></h2>
		<?php jd_show_support_box(); ?>
		
		<?php jd_events_edit_form(); ?>
	
		<h2><?php _e('Manage Events','my-calendar'); ?></h2>
		
		<?php jd_events_display_list();
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
			$data = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " WHERE event_id='" . mysql_escape_string($event_id) . "' LIMIT 1");
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
	
	?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php if ($mode == "add") { _e('Add an Event','my-calendar'); } else { _e('Edit Event'); } ?></h3>
	<div class="inside">	
	<form name="my-calendar" id="my-calendar" method="post" action="<?php bloginfo('url'); ?>/wp-admin/admin.php?page=my-calendar">
		<div>
		<input type="hidden" name="action" value="<?php echo $mode; ?>" />
		<input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
		</div>
        <fieldset>
		<legend><?php _e('Enter your Event Information','my-calendar'); ?></legend>
		<p>
		<label for="event_title"><?php _e('Event Title','my-calendar'); ?></label> <input type="text" id="event_title" name="event_title" class="input" size="40" maxlength="60" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_title); ?>" />
		</p>
		<p>
		<label for="event_desc"><?php _e('Event Description (<abbr title="hypertext markup language">HTML</abbr> allowed)','my-calendar'); ?></label><br /><textarea id="event_desc" name="event_desc" class="input" rows="5" cols="50"><?php if ( !empty($data) ) echo htmlspecialchars($data->event_desc); ?></textarea>
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
			<label for="event_link"><?php _e('Event Link (Optional)','my-calendar'); ?></label> <input type="text" id="event_link" name="event_link" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_link); ?>" />
			</p>
            <p>
			<label for="event_begin"><?php _e('Start Date (YYYY-MM-DD)','my-calendar'); ?></label> <input type="text" id="event_begin" name="event_begin" class="calendar_input" size="12" value="<?php if ( !empty($data) ) { echo htmlspecialchars($data->event_begin);} else {echo date("Y-m-d");} ?>" />
			</p>			
			<p>
			<label for="event_end"><?php _e('End Date (YYYY-MM-DD) (Optional)','calendar'); ?></label> <input type="text" name="event_end" id="event_end" class="calendar_input" size="12" value="<?php if ( !empty($data) ) {echo htmlspecialchars($data->event_end);} ?>" />
			</p>
			<p>
			<label for="event_time"><?php _e('Time (hh:mm)','calendar'); ?></label> <input type="text" id="event_time" name="event_time" class="input" size="12"
					value="<?php 
					if ( !empty($data) ) {
						if ($data->event_time == "00:00:00") {
						echo '';
						} else {
							echo date("H:i",strtotime(htmlspecialchars($data->event_time)));
						}
					} else {
						echo date("H:i",strtotime(current_time('mysql')));
					}
					?>" /> <?php _e('Optional, set blank if your event is an all-day event or does not happen at a specific time.','my-calendar'); ?> <?php _e('Current time difference from GMT is ','my-calendar'); echo get_option('gmt_offset'); _e(' hour(s)', 'my-calendar'); ?>
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
					} else if ($data->event_recur == "M")	{
						$selected_m = 'selected="selected"';
					} else if ($data->event_recur == "Y")	{
						$selected_y = 'selected="selected"';
					}
					?>
			<p>
			<label for="event_repeats"><?php _e('Repeats for','my-calendar'); ?></label> <input type="text" name="event_repeats" id="event_repeats" class="input" size="1" value="<?php echo $repeats; ?>" /> 
			<label for="event_recur"><?php _e('Units','my-calendar'); ?></label> <select name="event_recur" class="input" id="event_recur">
						<option class="input" <?php echo $selected_s; ?> value="S">Does not recur</option>
						<option class="input" <?php echo $selected_d; ?> value="D">Days</option>						
						<option class="input" <?php echo $selected_w; ?> value="W">Weeks</option>
						<option class="input" <?php echo $selected_m; ?> value="M">Months</option>
						<option class="input" <?php echo $selected_y; ?> value="Y">Years</option>
			</select><br />
					<?php _e('Entering 0 means forever, if a unit is selected. If the recurrance unit is left at "Does not recur," the event will not reoccur.','my-calendar'); ?>
			</p>
			</fieldset>			
			<?php if ( get_option( 'my_calendar_show_address' ) == 'true' || get_option( 'my_calendar_show_map' ) == 'true' ) { ?>
			<fieldset>
			<legend>Event Location</legend>
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<p>
			<label for="event_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> <input type="text" id="event_label" name="event_label" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_label); ?>" />
			</p>
			<p>
			<label for="event_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="event_street" name="event_street" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_street); ?>" />
			</p>			
			<p>
			<label for="event_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="event_street2" name="event_street2" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_street2); ?>" />
			</p>
			<p>
			<label for="event_city"><?php _e('City','my-calendar'); ?></label> <input type="text" id="event_city" name="event_city" class="input" size="40" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_city); ?>" /> <label for="event_state"><?php _e('State/Province','my-calendar'); ?></label> <input type="text" id="event_state" name="event_state" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_state); ?>" /> <label for="event_postcode"><?php _e('Postal Code','my-calendar'); ?></label> <input type="text" id="event_postcode" name="event_postcode" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_postcode); ?>" />
			</p>			
			<p>
			<label for="event_country"><?php _e('Country','my-calendar'); ?></label> <input type="text" id="event_country" name="event_country" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->event_country); ?>" />
			</p>
			</fieldset>
			<?php } ?>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Save Event','my-calendar'); ?> &raquo;" />
			</p>
			<div id="datepicker1">
			</div>
	</form>
	</div>
	</div>
</div>
	<?php
}
// Used on the manage events admin page to display a list of events
function jd_events_display_list() {
	global $wpdb;
	
	$events = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_TABLE . " ORDER BY event_begin DESC");
	
	if ( !empty($events) ) {
		?>
		<table class="widefat page fixed" id="my-calendar-admin-table" summary="Table of Calendar Events">
		        <thead>
			    <tr>
				<th class="manage-column n4" scope="col"><?php _e('ID','my-calendar') ?></th>
				<th class="manage-column" scope="col"><?php _e('Title','my-calendar') ?></th>
				<th class="manage-column n8" scope="col"><?php _e('Description','my-calendar') ?></th>
				<th class="manage-column" scope="col"><?php _e('Start Date','my-calendar') ?></th>
				<?php /* <th class="manage-column" scope="col"><?php _e('End Date','my-calendar') ?></th> */ ?>
				<th class="manage-column n6" scope="col"><?php _e('Recurs','my-calendar') ?></th>
				<th class="manage-column n6" scope="col"><?php _e('Repeats','my-calendar') ?></th>
		        <th class="manage-column" scope="col"><?php _e('Author','my-calendar') ?></th>
		        <th class="manage-column" scope="col"><?php _e('Category','my-calendar') ?></th>
				<th class="manage-column n7" scope="col"><?php _e('Edit / Delete','my-calendar') ?></th>
			    </tr>
		        </thead>
		<?php
		$class = '';
		foreach ( $events as $event ) {
			$class = ($class == 'alternate') ? '' : 'alternate';
			?>
			<tr class="<?php echo $class; ?>">
				<th scope="row"><?php echo $event->event_id; ?></th>
				<td><?php echo $event->event_title; ?></td>
				<td><?php echo $event->event_desc; ?></td>
				<td><?php echo $event->event_begin; ?></td>
				<?php /* <td><?php echo $event->event_end; ?></td> */ ?>
				<td>
				<?php 
					// Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { _e('Never','my-calendar'); } 
					else if ($event->event_recur == 'D') { _e('Daily','my-calendar'); }
					else if ($event->event_recur == 'W') { _e('Weekly','my-calendar'); }
					else if ($event->event_recur == 'M') { _e('Monthly','my-calendar'); }
					else if ($event->event_recur == 'Y') { _e('Yearly','my-calendar'); }
				?>
				</td>
				<td>
				<?php
				        // Interpret the DB values into something human readable
					if ($event->event_recur == 'S') { echo __('N/A','my-calendar'); }
					else if ($event->event_repeats == 0) { echo __('Forever','my-calendar'); }
					else if ($event->event_repeats > 0) { echo $event->event_repeats.' '.__('Times','my-calendar'); }					
				?>
				</td>
				<td><?php $e = get_userdata($event->event_author); echo $e->display_name; ?></td>
                                <?php
				$sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
                                $this_cat = $wpdb->get_row($sql);
                                ?>
				<td style="background-color:<?php echo $this_cat->category_color;?>;"><?php echo $this_cat->category_name; ?></td>
				<?php unset($this_cat); ?>
				<td><a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=my-calendar&amp;action=edit&amp;event_id=<?php echo $event->event_id;?>" class='edit'><?php echo __('Edit','my-calendar'); ?></a> &middot; <a href="<?php echo $_SERVER['PHP_SELF'] ?>?page=my-calendar&amp;action=delete&amp;event_id=<?php echo $event->event_id;?>" class="delete"><?php echo __('Delete','my-calendar'); ?></a></td>			</tr>
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

?>