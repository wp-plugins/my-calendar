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
      echo $before_title . $widget_title . $after_title;
      echo $the_events;
      echo $after_widget;
    }
  }

  function my_calendar_today_control() {
    $widget_title = get_option('my_calendar_today_title');
	$widget_template = get_option('my_calendar_today_template');
    if (isset($_POST['my_calendar_today_title'])) {
      update_option('my_calendar_today_title',strip_tags($_POST['my_calendar_today_title']));
    }
    if (isset($_POST['my_calendar_today_template'])) {
      update_option('my_calendar_today_template',$_POST['my_calendar_today_template']);
    }		
    ?>
<p>
   <label for="my_calendar_today_title"><?php _e('Title','my-calendar'); ?>:</label><br />
   <input class="widefat" type="text" id="my_calendar_today_title" name="my_calendar_today_title" value="<?php echo $widget_title; ?>"/>
</p>
<p>
	<label for="my_calendar_today_template"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="8" cols="20" id="my_calendar_today_template" name="my_calendar_today_template"><?php echo stripcslashes($widget_template); ?></textarea>
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
      echo $before_title . $widget_title . $after_title;
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
    
	if (isset($_POST['my_calendar_upcoming_title'])) {
      update_option('my_calendar_upcoming_title',strip_tags($_POST['my_calendar_upcoming_title']));
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
   <input class="widefat" type="text" id="my_calendar_upcoming_title" name="my_calendar_upcoming_title" value="<?php echo $widget_title; ?>"/>
</p>
<p>
	<label for="my_calendar_upcoming_template"><?php _e('Template','my-calendar'); ?></label><br />
	<textarea class="widefat" rows="8" cols="20" id="my_calendar_upcoming_template" name="my_calendar_upcoming_template"><?php echo stripcslashes($widget_template); ?></textarea>
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
	<input type="text" id="display_upcoming_events" name="display_upcoming_events" value="<?php echo $upcoming_events ?>" size="1" maxlength="2" /> <label for="display_upcoming_events"><?php _e('events into the future;','my-calendar'); ?></label><br />
	<input type="text" id="display_past_events" name="display_past_events" value="<?php echo $past_events ?>" size="1" maxlength="2" /> <label for="display_past_events"><?php _e('events from the past','my-calendar'); ?></label>
	</p>
	<p>
	<input type="text" id="display_upcoming_days" name="display_upcoming_days" value="<?php echo $upcoming_days ?>" size="1" maxlength="2" /> <label for="display_upcoming_days"><?php _e('days into the future;','my-calendar'); ?></label><br />
	<input type="text" id="display_past_days" name="display_past_days" value="<?php echo $past_days ?>" size="1" maxlength="2" /> <label for="display_past_days"><?php _e('days from the past','my-calendar'); ?></label>
	</p>
	</fieldset>
    <?php
  }

  register_sidebar_widget(__('Upcoming Events','my-calendar'),'my_calendar_upcoming');
  register_widget_control(__('Upcoming Events','my-calendar'),'my_calendar_upcoming_control');
}


// Widget upcoming events
function my_calendar_upcoming_events() {
  global $wpdb;

  // This function cannot be called unless calendar is up to date
  check_my_calendar();
  $template = get_option('my_calendar_upcoming_template');
  $display_upcoming_type = get_option('display_upcoming_type');
  
  
      // Get number of days we should go into the future
      $future_days = get_option('display_upcoming_days');
	  // Get number of days we should go into the past
	  $past_days = get_option('display_past_days');
	  $future_events = get_option('display_past_events');
	  $past_events = get_option('display_upcoming_events');
	  
      $day_count = -($past_days);
	  $output = "<ul>";

	if ($display_upcoming_type == "date") {
      while ($day_count < $future_days+1) {
          list($y,$m,$d) = split("-",date("Y-m-d",mktime($day_count*24,0,0,date("m"),date("d"),date("Y"))));
          $events = my_calendar_grab_events( $y,$m,$d );

          usort($events, "my_calendar_time_cmp");
          foreach($events as $event) {
		    $event_details = event_as_array($event);
			$output .= "<li>".jd_draw_widget_event($event_details,$template)."</li>";
          }
          $day_count = $day_count+1;
        }
	} else {
         $events = mc_get_all_events( ); // grab all events WITHIN reasonable proximity
          usort($events, "my_calendar_timediff_cmp");// sort all events by proximity to current date
			  for ($i=0;$i<=($past_events+$future_events);$i++) {
				if ($events[$i]) {
				$near_events[] = $events[$i]; // split off a number of events equal to the past + future settings
				}
			  }
		  
		  $events = $near_events;
		  usort($events, "my_calendar_datetime_cmp"); // sort split events by date
	  
          foreach($events as $event) {
		    $event_details = event_as_array($event);
				$today = date('Y').'-'.date('m').'-'.date('d');
				$date = date('Y-m-d',strtotime($event_details['date']));
				if (my_calendar_date_comp($date,$today)===true) {
					$class = "past-event";
				} else {
					$class = "future-event";
				}
				if (my_calendar_date_equal($date,$today)) {
					$class = "today";
				}				
			$output .= "<li class=\"$class\">".jd_draw_widget_event($event_details,$template)."</li>\n";
          }
          $day_count = $day_count+1;
	}

      if ($output != '') {
		$output .= "</ul>";
          return $output;
        }
}

// Widget todays events
function my_calendar_todays_events() {
  global $wpdb;

  // This function cannot be called unless calendar is up to date
  check_my_calendar();

  $template = get_option('my_calendar_today_template');
  
    $events = my_calendar_grab_events(date("Y"),date("m"),date("d"));
	if (count($events) != 0) {
		$output = "<ul>";
	}
    usort($events, "my_calendar_time_cmp");
        foreach($events as $event) {
		    $event_details = event_as_array($event);

				if (get_option('my_calendar_date_format') != '') {
				$date = date(get_option('my_calendar_date_format'),time());
				} else {
				$date = date(get_option('date_format'),time());
				}			
			// correct displayed time to today
			$event_details['date'] = $date;
			$output .= "<li>".jd_draw_widget_event($event_details,$template)."</li>";
        }
    if (count($events) != 0) {
		$output .= "</ul>";
        return $output;
    }
}

function jd_draw_widget_event($array,$template) {
	//1st argument: array of details
	//2nd argument: template to print details into
	foreach ($array as $key=>$value) {
	    $search = "{".$key."}";
		$template = stripcslashes(str_replace($search,$value,$template));
	}
	return $template;
}

// Draw an event but customise the HTML for use in the widget
function event_as_array($event) {
  global $wpdb;
  // My Calendar must be updated to run this function
  check_my_calendar();

$sql = "SELECT category_name FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".$event->event_category;
$category_name = $wpdb->get_row($sql);
$e = get_userdata($event->event_author);

$hcard = "<div class=\"address vcard\">";
$hcard .= "<div class=\"adr\">";
if ($event->event_label != "") {
	$hcard .= "<strong class=\"org\">".$event->event_label."</strong><br />";
}					
if ($event->event_street != "") {
	$hcard .= "<div class=\"street-address\">".$event->event_street."</div>";
}
if ($event->event_street2 != "") {
	$hcard .= "<div class=\"street-address\">".$event->event_street2."</div>";
}
if ($event->event_city != "") {
	$hcard .= "<span class=\"locality\">".$event->event_city.",</span>";
}						
if ($event->event_state != "") {
	$hcard .= "<span class=\"region\">".$event->event_state."</span> ";
}
if ($event->event_postcode != "") {
	$hcard .= " <span class=\"postal-code\">".$event->event_postcode."</span>";
}	
if ($event->event_country != "") {
	$hcard .= "<div class=\"country-name\">".$event->event_country."</div>";
}	
$hcard .= "</div>\n</div>";	

$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;	
if (strlen($map_string) > 10) {
	$map_string = str_replace(" ","+",$map_string);
	if ($event->event_label != "") {
		$map_label = $event->event_label;
	} else {
		$map_label = $event->event_title;
	}
	$map = "<a href=\"http://maps.google.com/maps?f=q&z=15&q=$map_string\">Map<span> to $map_label</span></a>";
} else {
	$map = "";
}

if (get_option('my_calendar_date_format') != '') {
$date = date(get_option('my_calendar_date_format'),strtotime($event->event_begin));
$date_end = date(get_option('my_calendar_date_format'),strtotime($event->event_end));
} else {
$date = date(get_option('date_format'),strtotime($event->event_begin));
$date_end = date(get_option('date_format'),strtotime($event->event_end));
}


    $details = array();
	$details['category'] = $category_name->category_name;
	$details['title'] = $event->event_title;
	$details['time'] = date(get_option('time_format'),strtotime($event->event_time));
	$details['author'] = $e->display_name;
	$details['link'] = $event->event_link;
	$details['description'] = $event->event_desc;
	if ($event->event_link != '') {
	$details['link_title'] = "<a href='".$event->event_link."'>".$event->event_title."</a>";
	} else {
	$details['link_title'] = $event->event_title;	
	}
	$details['date'] = $date;
	$details['enddate'] = $date_end;
	$details['location'] = $event->event_label;
	$details['street'] = $event->event_street;
	$details['street2'] = $event->event_street2;
	$details['city'] = $event->event_city;
	$details['state'] = $event->event_state;
	$details['postcode'] = $event->event_postcode;
	$details['country'] = $event->event_country;
	$details['hcard'] = $hcard;
	$details['link_map'] = $map;
  
  return $details;
}


?>