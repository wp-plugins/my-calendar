<?php
// The widget to show todays events in the sidebar
function init_my_calendar_today() {
  // Check for required functions
  if (!function_exists('register_sidebar_widget')) {
    return;
  }
  function my_calendar_today($args) {
  extract($args);
    $the_title = get_option('my_calendar_today_title');
    $widget_title = empty($the_title) ? __('Today\'s Events','my-calendar') : $the_title;
    $the_events = my_calendar_todays_events();
    if ($the_events != '') {
      echo $before_widget;
      echo $before_title . stripslashes($widget_title) . $after_title;
      echo $the_events;
      echo $after_widget;
    }
  }

  function my_calendar_today_control() {
    $widget_title = get_option('my_calendar_today_title');
	$widget_template = get_option('my_calendar_today_template');
	$widget_text = get_option('my_calendar_no_events_text');
	
    if (isset($_POST['my_calendar_today_title'])) {
      update_option('my_calendar_today_title',strip_tags($_POST['my_calendar_today_title']));
    }
    if (isset($_POST['my_calendar_today_template'])) {
      update_option('my_calendar_today_template',$_POST['my_calendar_today_template']);
    }
	if (isset($_POST['my_calendar_no_events_text'])) {
      update_option('my_calendar_no_events_text',$_POST['my_calendar_no_events_text']);	
	}
    ?>
<p>
   <label for="my_calendar_today_title"><?php _e('Title','my-calendar'); ?>:</label><br />
   <input class="widefat" type="text" id="my_calendar_today_title" name="my_calendar_today_title" value="<?php echo stripslashes($widget_title); ?>"/>
</p>
<p>
	<label for="my_calendar_today_template"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="8" cols="20" id="my_calendar_today_template" name="my_calendar_today_template"><?php echo stripcslashes($widget_template); ?></textarea>
</p>
<p>
	<label for="my_calendar_no_events_text"><?php _e('Show this text if there are no events today:','my-calendar'); ?></label><br />
	<input class="widefat" type="text" id="my_calendar_no_events_text" name="my_calendar_no_events_text" value="<?php echo stripcslashes($widget_text); ?>" /></textarea>
</p>	
    <?php
  }

  register_sidebar_widget(__('Today\'s Events','my-calendar'),'my_calendar_today');
  register_widget_control(__('Today\'s Events','my-calendar'),'my_calendar_today_control');
  }

// replace upcoming_events_widget with my_calendar
// The widget to show todays events in the sidebar                                              
function init_my_calendar_upcoming() {
  // Check for required functions                                                               
  if (!function_exists('register_sidebar_widget'))
    return;

  function my_calendar_upcoming($args) {
    extract($args);
    $the_title = get_option('my_calendar_upcoming_title');
    $widget_title = empty($the_title) ? __('Upcoming Events','my-calendar') : $the_title;
	$the_events = my_calendar_upcoming_events();
    if ($the_events != '') {
      echo $before_widget;
      echo $before_title . stripslashes($widget_title) . $after_title;
      echo $the_events;
      echo $after_widget;
    }
  }

  function my_calendar_upcoming_control() {
    $widget_title = get_option('my_calendar_upcoming_title');
    $widget_template = get_option('my_calendar_upcoming_template');	
	$upcoming_days = get_option('display_upcoming_days');
	$past_days = get_option('display_past_days');
	$upcoming_events = get_option('display_upcoming_events');
	$past_events = get_option('display_past_events');	
    $display_in_category = stripcslashes( get_option('display_in_category') );
	
    
	if (isset($_POST['my_calendar_upcoming_title'])) {
      update_option('my_calendar_upcoming_title',strip_tags($_POST['my_calendar_upcoming_title']));
    }
	if (isset($_POST['display_in_category'])) {
      update_option('display_in_category',$_POST['display_in_category']);
    }	
    if (isset($_POST['my_calendar_upcoming_template'])) {
      update_option('my_calendar_upcoming_template',$_POST['my_calendar_upcoming_template']);
    }	
	
    if (isset($_POST['display_upcoming_type'])) {	
		$display_upcoming_type = $_POST['display_upcoming_type'];	 
		update_option('display_upcoming_type',$display_upcoming_type);
    }

	if (isset($_POST['display_upcoming_days'])) {
		$display_upcoming_days = (int) $_POST['display_upcoming_days'];
		update_option('display_upcoming_days',$display_upcoming_days);
    }

    if (isset($_POST['display_upcoming_events'])) {
		$display_upcoming_events = (int) $_POST['display_upcoming_events'];
		update_option('display_upcoming_events',$display_upcoming_events);
    }

    if (isset($_POST['display_past_events'])) {
		$display_past_events = (int) $_POST['display_past_events'];
		update_option('display_past_events',$display_past_events);
    }

    if (isset($_POST['display_past_days'])) {
		$display_past_days = (int) $_POST['display_past_days'];	
		update_option('display_past_days',$display_past_days);
    }
	// add options for days/events
    ?>
<p>
   <label for="my_calendar_upcoming_title"><?php _e('Title','my-calendar'); ?>:</label><br />
   <input class="widefat" type="text" id="my_calendar_upcoming_title" name="my_calendar_upcoming_title" value="<?php if(isset($_POST['my_calendar_upcoming_title'])){echo strip_tags($_POST['my_calendar_upcoming_title']); } else { echo $widget_title; } ?>"/>
</p>
<p>
	<label for="my_calendar_upcoming_template"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="8" cols="20" id="my_calendar_upcoming_template" name="my_calendar_upcoming_template"><?php  if(isset($_POST['my_calendar_upcoming_template'])){echo stripcslashes($_POST['my_calendar_upcoming_template']); } else { echo stripcslashes($widget_template); } ?></textarea>
</p>
	<fieldset>
	<legend><?php _e('Widget Options','my-calendar'); ?></legend>
	<p>
	<label for="display_upcoming_type"><?php _e('Display upcoming events by:','my-calendar'); ?></label> <select id="display_upcoming_type" name="display_upcoming_type">
	<option value="events" <?php jd_cal_checkSelect('display_upcoming_type','events'); ?>><?php _e('Events (e.g. 2 past, 3 future)','my-calendar') ?></option>
	<option value="days" <?php jd_cal_checkSelect('display_upcoming_type','days'); ?>><?php _e('Dates (e.g. 4 days past, 5 forward)','my-calendar') ?></option>
	</select>
	</p>
	<p>
	<input type="text" id="display_upcoming_events" name="display_upcoming_events" value="<?php  if(isset($_POST['display_upcoming_events'])){echo $_POST['display_upcoming_events']; } else { echo $upcoming_events; } ?>" size="1" maxlength="3" /> <label for="display_upcoming_events"><?php _e('events into the future;','my-calendar'); ?></label><br />
	<input type="text" id="display_past_events" name="display_past_events" value="<?php if(isset($_POST['display_past_events'])){echo $_POST['display_past_events']; } else { echo $past_events; } ?>" size="1" maxlength="3" /> <label for="display_past_events"><?php _e('events from the past','my-calendar'); ?></label>
	</p>
	<p>
	<input type="text" id="display_upcoming_days" name="display_upcoming_days" value="<?php if(isset($_POST['display_upcoming_days'])){echo $_POST['display_upcoming_days']; } else { echo $upcoming_days; } ?>" size="1" maxlength="3" /> <label for="display_upcoming_days"><?php _e('days into the future;','my-calendar'); ?></label><br />
	<input type="text" id="display_past_days" name="display_past_days" value="<?php if(isset($_POST['display_past_days'])){echo $_POST['display_past_days']; } else { echo $past_days; } ?>" size="1" maxlength="3" /> <label for="display_past_days"><?php _e('days from the past','my-calendar'); ?></label>
	</p>
	<p>
	<label for="display_in_category"><?php _e('Show only this category:','my-calendar'); ?></label><br />
	<input type="text" id="display_in_category" name="display_in_category" value="<?php if(isset($_POST['display_in_category'])){echo $_POST['display_in_category']; } else { echo $display_in_category; } ?>" class="widefat" />
	</fieldset>
    <?php
  }

  register_sidebar_widget(__('Upcoming Events','my-calendar'),'my_calendar_upcoming');
  register_widget_control(__('Upcoming Events','my-calendar'),'my_calendar_upcoming_control');
}


// Widget upcoming events
function my_calendar_upcoming_events($before='default',$after='default',$type='default',$category='default',$template='default') {
  global $wpdb;
	$offset = (60*60*get_option('gmt_offset'));
  // This function cannot be called unless calendar is up to date
	check_my_calendar();
	$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
  
	if ($type == 'default') {
		$display_upcoming_type = get_option('display_upcoming_type');
    } else {
		$display_upcoming_type = $type;
	}
  
      // Get number of days we should go into the future
	if ($after == 'default') {
		$future_days = get_option('display_upcoming_days');
		$future_events = get_option('display_upcoming_events');	  
	} else {
		$future_days = $after;
		$future_events = $after;
	}
	// Get number of days we should go into the past
	if ($before == 'default') {	  
		$past_days = get_option('display_past_days');
		$past_events = get_option('display_past_events');
	} else {
		$past_days = $before;
		$past_events = $before;
	}
	 
	if ($category == 'default') {
		$category = get_option('display_in_category');
	} else {
		$category = $category;
	}
    
	if ($template == 'default') {
		$template = get_option('my_calendar_upcoming_template');
	} else {
		$template = $template;
	}
  
      $day_count = -($past_days);
	  $output = "<ul>";
	  
	if ($display_upcoming_type == "days") {
      while ($day_count < $future_days+1) {
          list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m"),date("d"),date("Y"))));
          $events = my_calendar_grab_events( $y,$m,$d,$category );
			$current_date = "$y-$m-$d";
          @usort($events, "my_calendar_time_cmp");
          foreach($events as $event) {
		    $event_details = event_as_array($event);
			$date_diff = jd_date_diff($event_details['date'],$event_details['date_end']);
			
			if (get_option('my_calendar_date_format') != '') {
				$date = date_i18n(get_option('my_calendar_date_format'),strtotime($current_date));
				$date_end = date_i18n(get_option('my_calendar_date_format'),strtotime(my_calendar_add_date($current_date,$datediff)));
			} else {
				$date = date_i18n(get_option('date_format'),strtotime($current_date));
				$date_end = date_i18n(get_option('date_format'),strtotime(my_calendar_add_date($current_date,$datediff)));
			}
			
			$event_details['date'] = $date;
			$event_details['date_end'] = $date_end;

			// by Roland
			if ( get_option( 'mc_event_approve' ) == 'true' ) {
				if ( $event->event_approved != 0 ) {
					$output .= "<li>".jd_draw_template($event_details,$template)."</li>";
				}
			} else {
				$output .= "<li>".jd_draw_template($event_details,$template)."</li>";
			}
// by Roland end
          }
          $day_count = $day_count+1;
        }
	} else {
         $events = mc_get_all_events($category);		 // grab all events WITHIN reasonable proximity		 	 
		 $past = 1;
		 $future = 1;
         @usort( $events, "my_calendar_timediff_cmp" );// sort all events by proximity to current date
	     $count = count($events);
			for ( $i=0;$i<=$count;$i++ ) {
				if ($events[$i]) {
					if ( ( $past<=$past_events && $future<=$future_events ) ) {
						$near_events[] = $events[$i]; // if neither limit is reached, split off freely
					} else if ( $past <= $past_events && ( my_calendar_date_comp( $events[$i]->event_begin,$today ) ) ) {
						$near_events[] = $events[$i]; // split off another past event
					} else if ( $future <= $future_events && ( !my_calendar_date_comp( $events[$i]->event_begin,$today ) ) ) {
						$near_events[] = $events[$i]; // split off another future event
					}				
					if ( my_calendar_date_comp( $events[$i]->event_begin,$today ) ) {
						$past++;
					} elseif  ( my_calendar_date_equal( $events[$i]->event_begin,$today ) ) {
						$present = 1;
					} else {
						$future++;
					} 
				}
			}
		  
		  $events = $near_events;
		  @usort( $events, "my_calendar_datetime_cmp" ); // sort split events by date	  
		  if ( is_array( $events ) ) {
          foreach( $events as $event ) {
		    $event_details = event_as_array( $event );
				$date = date('Y-m-d',strtotime($event_details['date']));
				if (my_calendar_date_comp( $date,$today )===true) {
					$class = "past-event";
				} else {
					$class = "future-event";
				}
				if ( my_calendar_date_equal( $date,$today ) ) {
					$class = "today";
				}	
			if ( get_option( 'mc_event_approve' ) == 'true' ) {
				if ( $event->event_approved != 0 ) {				
					$output .= "<li class=\"$class\">".jd_draw_template($event_details,$template)."</li>\n";
				}
			} else {
				$output .= "<li class=\"$class\">".jd_draw_template($event_details,$template)."</li>\n";
			}
          }
          $day_count = $day_count+1;
		  } else {
			$output .= "<li class=\"no-events\">".__('There are no events currently scheduled.','my-calendar')."</li>\n";
		  }
	}

      if ($output != '') {
		$output .= "</ul>";
          return $output;
        }
		
}

// Widget todays events
function my_calendar_todays_events($category='default',$template='default') {
  global $wpdb;
	$offset = (60*60*get_option('gmt_offset'));  
  // This function cannot be called unless calendar is up to date
  check_my_calendar();

  
	if ($template == 'default') {
		$template = get_option('my_calendar_today_template');
	} else {
		$template = $template;
	}  
	if ($category == 'default') {
		$category = null;
	} else {
		$category = $category;
	}  
    $events = my_calendar_grab_events(date("Y",time()+$offset),date("m",time()+$offset),date("d",time()+$offset),$category);
	if (count($events) != 0) {
		$output = "<ul>";
	}
    @usort($events, "my_calendar_time_cmp");
        foreach($events as $event) {
		    $event_details = event_as_array($event);

				if (get_option('my_calendar_date_format') != '') {
				$date = date_i18n(get_option('my_calendar_date_format'),time()+$offset);
				} else {
				$date = date_i18n(get_option('date_format'),time()+$offset);
				}	
			// correct displayed time to today
			$event_details['date'] = $date;
			if ( get_option( 'mc_event_approve' ) == 'true' ) {
				if ( $event->event_approved != 0 ) {			
					$output .= "<li>".jd_draw_template($event_details,$template)."</li>";
				}
			} else {
				$output .= "<li>".jd_draw_template($event_details,$template)."</li>";
			}
        }
    if (count($events) != 0) {
		$output .= "</ul>";
        return $output;
    } else {
		return stripslashes( get_option('my_calendar_no_events_text') );
	}
}

?>