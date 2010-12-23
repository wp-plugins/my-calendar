<?php
// Used to draw multiple events
function my_calendar_draw_events($events, $type, $process_date) {
  // We need to sort arrays of objects by time
  usort($events, "my_calendar_time_cmp");
	if ($type == "mini" && count($events) > 0) { $output .= "<div class='calendar-events'>"; }
	foreach($events as $event) { $output .= my_calendar_draw_event($event, $type, $process_date); }
	if ($type == "mini" && count($events) > 0) { $output .= "</div>"; }	
  return $output;
}
// Used to draw an event to the screen
function my_calendar_draw_event($event, $type="calendar", $process_date) {
	global $wpdb;
	// My Calendar must be updated to run this function
	check_my_calendar();						 
	$display_author = get_option('display_author');
	$display_map = get_option('my_calendar_show_map');
	$display_address = get_option('my_calendar_show_address');
	$this_category = $event->event_category; 
    // get user-specific data
	$tz = mc_user_timezone();
	$category = "mc_".sanitize_title( $event->category_name );
	if ( get_option('my_calendar_hide_icons')=='true' ) {
		$image = "";
	} else {
	    if ($event->category_icon != "") {
			$path = ( file_exists( WP_PLUGIN_DIR . '/my-calendar-custom/' ) )?'/my-calendar-custom' : '/my-calendar/icons';
			$image = '<img src="'.WP_PLUGIN_URL.$path.'/'.$event->category_icon.'" alt="" class="category-icon" style="background:'.$event->category_color.';" />';
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
			if ($display_map == 'true' && (strlen($location_string) > 0 || ( $event->event_longitude != '0.000000' && $event->event_latitude != '0.000000' ) ) ) {
					$map_string = str_replace(" ","+",$map_string);
					if ($event->event_label != "") {
						$map_label = stripslashes($event->event_label);
					} else {
						$map_label = stripslashes($event->event_title);
					}
					$zoom = ($event->event_zoom != 0)?$event->event_zoom:'15';
					$map_string_label = urlencode($map_label);
					
					if ($event->event_longitude != '0.000000' && $event->event_latitude != '0.000000') {
						$map_string = "$event->event_latitude,$event->event_longitude+($map_string_label)";
					}
					
					$map = "<a href=\"http://maps.google.com/maps?f=q&amp;z=$zoom&amp;q=$map_string\">Map<span> to $map_label</span></a>";
					$address .= "<div class=\"url map\">$map</div>";
			}
		$address .= "</div>";
	}

    $header_details .=  "\n<div class='$type-event'>\n";
	$array = event_as_array($event);
	$templates = get_option('my_calendar_templates');
	$title_template = ($templates['title'] == '' )?'{title}':$templates['title'];
	$mytitle = jd_draw_template($array,$title_template);
	//$mytitle = stripslashes($event->event_title); // turn this into a template
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
			
			if ($tz != '') {
				$local_begin = date_i18n( get_option('time_format'), strtotime($event->event_time ."+$tz hours") );
				$header_details .= "<span class='local-time'>$local_begin ". __('in your time zone','my-calendar')."</span>";
			}
			
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
		if ( my_calendar_date_comp( $event->event_end,date_i18n('Y-m-d',time()+$offset ) ) ) {
			$event_link = '';
		} else {
			$event_link = $event->event_link;		
		}
	}
	
	if ( get_option('mc_short') == 'true' ) {
		$short = "<div class='shortdesc'>".wpautop(stripcslashes($event->event_short),1)."</div>";	
	}
	if ( get_option('mc_desc') == 'true' ) {
		$description = "<div class='longdesc'>".wpautop(stripcslashes($event->event_desc),1)."</div>";
	}
	if ( get_option('mc_event_registration') == 'true' ) {
		switch ($event->event_open) {
			case '0':
				$status = get_option('mc_event_closed');
				break;
			case '1':
				$status = get_option('mc_event_open');
				break;
			case '2':
				$status = '';
				break;
			default:
				$status = '';
		}
	}
	// if the event is a member of a group of events, but not the first, note that.
	if ($event->event_group == 1 ) {
		$info = array();
		$info[] = $event->event_id;
		update_option( 'mc_event_groups' , $info );
	}
	if ( is_array( get_option( 'mc_event_groups' ) ) ) {
		if ( in_array ( $event->event_id , get_option( 'mc_event_groups') ) ) {
			if ( $process_date != $event->event_original_begin ) {
				$status = __("This class is part of a series. You must register for the first event in this series to attend.",'my-calendar');
			}
		}
	}
  
	if ($event_link != '') {
		$details = "\n". $header_details . '' . $description . $short . '<p>'.$status.'</p><p><a href="'.$event_link.'" class="event-link">' . stripslashes($event->event_title) . '&raquo; </a></p>'."</div></div></div>\n";
	} else {
		$details = "\n". $header_details . '' . $description . $short . '<p>'.$status."</p></div></div></div>\n";	
	}
	
	if ( get_option( 'mc_event_approve' ) == 'true' ) {
		if ( $event->event_approved == 1 ) {
		  return $details;
		}
	} else {
		return $details;
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
function my_calendar($name,$format,$category,$showkey,$shownav,$month='',$yr='') {
    global $wpdb;	
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
    $c_day = date("d",time()+($offset));	
    if (empty($_GET['month']) || empty($_GET['yr']) && ($month == '' || $yr == '')) {
        $c_year = date("Y",time()+($offset));
        $c_month = date("m",time()+($offset));
    } else {
		if ( isset($_GET['month']) && isset($_GET['yr']) ) {
		$c_year = $_GET['yr'];
		$c_month = $_GET['month'];
		} else {
		$c_year = $yr;
		$c_month = $month;
		}
	}
    // Years get funny if we exceed 3000, so we use this check
    if ( $year <= 3000 && $year >= 0) {
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
		$pLink = my_calendar_prev_link($c_year,$c_month,$format);
		$nLink = my_calendar_next_link($c_year,$c_month,$format);

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
		
		if ($shownav == 'yes') {
	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev"><a id="prevMonth" href="' . my_calendar_permalink_prefix() . 'month='.$pLink['month'].'&amp;yr=' . $pLink['yr'] . '#jd-calendar" rel="nofollow">'.$pLink['label'].' &raquo;</a></li>
	                    <li class="my-calendar-next"><a id="nextMonth" href="' . my_calendar_permalink_prefix() . 'month='.$nLink['month'].'&amp;yr=' . $nLink['yr'] . '#jd-calendar" rel="nofollow">'.$nLink['label'].' &raquo;</a></li>
						</ul>
	                    </div>';
		}
		$my_calendar_body .= '</div>';		
			$my_calendar_body .= "\n<table class=\"my-calendar-table\" summary=\"$category_label".__('Calendar','my-calendar')."\">\n";
			$my_calendar_body .= '<caption class="my-calendar-month">'.$name_months[(int)$c_month].' '.$c_year.$caption_text."</caption>\n";
	} else {
			if ( get_option('my_calendar_show_heading') == 'true' ) {
				$my_calendar_body .= "\n<h2 class=\"my-calendar-heading\">$category_label".__('Calendar','my-calendar')."</h2>\n";
			}
		// determine which header text to show depending on number of months displayed;
		$num_months = get_option('my_calendar_show_months');
		$my_calendar_body .= ($num_months <= 1)?'<h3 class="my-calendar-month">'.__('Events in','my-calendar').' '.$name_months[(int)$c_month].' '.$c_year.$caption_text."</h3>\n":
		'<h3 class="my-calendar-month">'.$name_months[(int)$c_month].'&thinsp;&ndash;&thinsp;'.$name_months[(int)($nLink['month']-1)].' '.$nLink['yr'].$caption_text."</h3>\n";
		$my_calendar_body .= '<div class="my-calendar-header">'; // this needs work
	    // We want to know if we should display the date switcher
		$my_calendar_body .= ( get_option('display_jump') == 'true' )?mc_build_date_switcher():'';

		if ($shownav == 'yes') {
	    $my_calendar_body .= '
						<div class="my-calendar-nav">
						<ul>
						<li class="my-calendar-prev"><a id="prevMonth" href="' . my_calendar_permalink_prefix() . 'month='.$pLink['month'].'&amp;yr=' . $pLink['yr'] . '#jd-calendar" rel="nofollow">'.$pLink['label'].' &raquo;</a></li>
	                    <li class="my-calendar-next"><a id="nextMonth" href="' . my_calendar_permalink_prefix() . 'month='.$nLink['month'].'&amp;yr=' . $nLink['yr'] . '#jd-calendar" rel="nofollow">'.$nLink['label'].' &raquo;</a></li>
						</ul>
	                    </div>';
		} 
		$my_calendar_body .= '</div>';	
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
	$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$i,$c_year));
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
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date_i18n("Ymd",time()+$offset)?'current-day':'day-with-date').$events_class.'">'."\n	<$element class='mc-date ".($ii<6&&$ii>0?"$trigger":"weekend$trigger")."'>".$i++."</$element>\n		". my_calendar_draw_events($grabbed_events, $format, $process_date) . "\n</td>\n";
				} else {
				    $my_calendar_body .= '<td class="'.(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date_i18n("Ymd",time()+$offset)?'current-day':'day-with-date').$events_class.'">'."\n	<$element class='mc-date ".($ii<5?"$trigger":"weekend$trigger'")."'>".$i++."</$element>\n		". my_calendar_draw_events($grabbed_events, $format, $process_date) . "\n</td>\n";
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
		if ($c_month > 12) {
			$c_month = $c_month - 12;
			$c_year = $c_year + 1;
		}
	    for ($i=1; $i<=31; $i++) {
		$process_date = date_i18n('Y-m-d',mktime(0,0,0,$c_month,$i,$c_year));
			$grabbed_events = my_calendar_grab_events($c_year,$c_month,$i,$category);
			if (count($grabbed_events)) {
				if ( get_option('list_javascript') != 1) {
					$is_anchor = "<a href='#'>";
					$is_close_anchor = "</a>";
				} else {
					$is_anchor = $is_close_anchor = "";
				}
				$my_calendar_body .= "<li class='$class".(date("Ymd", mktime (0,0,0,$c_month,$i,$c_year))==date("Ymd",time()+($offset))?' current-day':'')."'><strong class=\"event-date\">$is_anchor".date_i18n($date_format,mktime(0,0,0,$c_month,$i,$c_year))."$is_close_anchor</strong>".my_calendar_draw_events($grabbed_events, $format, $process_date)."</li>";
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


// Configure the "Next" link in the calendar
function my_calendar_next_link($cur_year,$cur_month,$format) {
  $next_year = $cur_year + 1;
  $next_events = ( get_option( 'mc_next_events') == '' )?"Next events":stripcslashes( get_option( 'mc_next_events') );
  $num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1 || $format=="calendar") {  
	  if ($cur_month == 12) {
			$nMonth = 1;
			$nYr = $next_year;
	    } else {
	      $next_month = $cur_month + 1;
		  $nMonth = $next_month;
		  $nYr = $cur_year;
	    }
	} else {
		$next_month = (($cur_month + $num_months) > 12)?(($cur_month + $num_months) - 12):($cur_month + $num_months);
		if ($cur_month >= (12-$num_months)) {	 
			$nMonth = $next_month;
			$nYr = $next_year;
		} else {
			$nMonth = $next_month;
			$nYr = $cur_year;		
		}	
	}
	$output = array('month'=>$nMonth,'yr'=>$nYr,'label'=>$next_events);
	return $output;
}

// Configure the "Previous" link in the calendar
function my_calendar_prev_link($cur_year,$cur_month,$format) {
  $last_year = $cur_year - 1;
  $previous_events = ( get_option( 'mc_previous_events') == '' )?"Previous events":stripcslashes( get_option( 'mc_previous_events') );
  $num_months = get_option('my_calendar_show_months');
  if ($num_months <= 1 || $format=="calendar") {  
		if ($cur_month == 1) {
			$pMonth = 12;
			$pYr = $last_year;
	    } else {
	      $next_month = $cur_month - 1;
		  $pMonth = $next_month;
		  $pYr = $cur_year;
	    }
	} else {
		$next_month = ($cur_month > $num_months)?($cur_month - $num_months):(($cur_month - $num_months) + 12);
		if ($cur_month <= $num_months) {
			$pMonth = $next_month;
			$pYr = $last_year;
		} else {
			$pMonth = $next_month;
			$pYr = $cur_year;		
		}	
	}
	$output = array('month'=>$pMonth,'yr'=>$pYr,'label'=>$previous_events);
	return $output;
}


function my_calendar_locations_list($show='list',$type='saved',$datatype='name') {
global $wpdb;
if ($type == 'saved') {
	switch ($datatype) {
		case "name":$data = "location_label";
		break;
		case "city":$data = "location_city";
		break;
		case "state":$data = "location_state";
		break;
		case "zip":$data = "location_postcode";
		break;
		case "country":$data = "location_country";
		break;
		default:$data = "location_label";
		break;
	}
} else {
	$data = $datatype;
}
$current_url = get_current_url();
$cv = urlencode($_GET['loc']);
$cd = urlencode($_GET['ltype']);
if (strpos($current_url,"?")===false) {
	$char = '?';
	$nonchar = '&';
} else {
	$char = '&';
	$nonchar = '?';
}
$needle = array("$nonchar"."loc=$cv&ltype=$cd","$char"."loc=$cv&ltype=$cd");
$current_url = str_replace( $needle,"",$current_url );

if (strpos($current_url,"/&")!==false || strpos($current_url,".php&")!==false) {
	$needle = array("/&",".php&");
	$replace = array("/?",".php?");
	$current_url = str_replace( $needle,$replace,$current_url );
}

	if ($type == 'saved') {
		$locations = $wpdb->get_results("SELECT DISTINCT $data FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY $data ASC", ARRAY_A );
	} else {
		$data = get_option( 'mc_user_settings' );
		$locations = $data['my_calendar_location_default']['values'];
		$datatype = str_replace('event_','',get_option( 'mc_location_type' ));
		$datatype = ($datatype=='label')?'name':$datatype;
		$datatype = ($datatype=='postcode')?'zip':$datatype;
	}
	if ($show == 'list') {
		$output .= "<ul id='mc-locations-list'>
		<li><a href='$current_url$char"."loc=none&amp;ltype=none'>Show all</a></li>\n";
	} else {
		$ltype = ($_GET['ltype']=='')?'none':$_GET['ltype'];
		$output .= "
		<form action='' method='GET'>
		<div>
		<input type='hidden' name='ltype' value='$ltype' />";
	$qsa = array();
	parse_str($_SERVER['QUERY_STRING'],$qsa);
		foreach ($qsa as $name => $argument) {
			if ($name != 'loc' && $name != 'ltype') {
				$output .= '<input type="hidden" name="'.$name.'" value="'.$argument.'" />'."\n";
			}
		}
		$output .= "<label for='mc-locations-list'>".__('Show events in:','my-calendar')."</label>
		<select name='loc' id='mc-locations-list'>
		<option value='none'>Show all</option>\n";
	}
	foreach ( $locations as $key=>$location ) {
		if ($type == 'saved') {
			foreach ( $location as $key=>$value ) {
				$vt = urlencode(trim($value));
				if ($show == 'list') {
					$output .= "<li><a rel='nofollow' href='$current_url".$char."loc=$vt&amp;ltype=$datatype'>$value</a></li>\n";
				} else {
					$output .= "<option value='$vt'>$value</option>\n";
				}
			}
		} else {
			$vk = urlencode(trim($key));
			$location = trim($location);
			if ($show == 'list') {
				$output .= "<li><a rel='nofollow' href='$current_url".$char."loc=$vk&amp;ltype=$datatype'>$location</a></li>\n";
			} else {
				$output .= "<option value='$vk'>$location</option>\n";	
			}			
		}
	}
	if ($show == 'list') {
		$output .= "</ul>";
	} else {
		$output .= "</select> <input type='submit' value=".__('Submit','my-calendar')." /></div></form>";
	}
	return $output;
}

?>