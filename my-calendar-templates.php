<?php

function jd_draw_template($array,$template) {
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

$offset = (60*60*get_option('gmt_offset'));  

$category_name = $event->category_name;
$category_color = $event->category_color;
$category_icon = $event->category_icon;

		if ( file_exists( WP_PLUGIN_DIR . '/my-calendar-custom/' ) ) {
				$path = '/my-calendar-custom';
			} else {
				$path = '/my-calendar/icons';
		    }
		$category_icon = WP_PLUGIN_URL . $path . '/' . $category_icon;

$e = get_userdata($event->event_author);
$host = get_userdata($event->event_host);

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
if ( strlen( trim( $map_string ) ) > 0 ) {
	$map_string = str_replace(" ","+",$map_string);
	if ($event->event_label != "") {
		$map_label = $event->event_label;
	} else {
		$map_label = $event->event_title;
	}
	$zoom = ($event->event_zoom != 0)?$event->event_zoom:'15';
	
	if ($event->event_longitude != '0.000000' && $event->event_latitude != '0.000000') {
		$map_string = "$event->event_latitude,$event->event_longitude";
	}
	
	$map = "<a href=\"http://maps.google.com/maps?f=q&z=$zoom&q=$map_string\">Map<span> to $map_label</span></a>";
} else {
	$map = "";
}

if (get_option('my_calendar_date_format') != '') {
$date = date_i18n(get_option('my_calendar_date_format'),strtotime($event->event_begin));
$date_end = date_i18n(get_option('my_calendar_date_format'),strtotime($event->event_end));
} else {
$date = date_i18n(get_option('date_format'),strtotime($event->event_begin));
$date_end = date_i18n(get_option('date_format'),strtotime($event->event_end));
}


    $details = array();
	$details['category'] = stripslashes($event->category_name);
	$details['title'] = stripslashes($event->event_title);
	if ($event->event_time == '00:00:00' ) {
	$details['time'] = get_option( 'my_calendar_notime_text' );
	} else {
	$details['time'] = date(get_option('time_format'),strtotime($event->event_time));
	}
	
	$tz = mc_user_timezone();
			
	if ($tz != '') {
		$local_begin = date_i18n( get_option('time_format'), strtotime($event->event_time ."+$tz hours") );
		$details['usertime'] = "$local_begin";
	} else {
		$details['usertime'] = '';
	}
	
	if ($event->event_endtime == '00:00:00' ) {
	$details['endtime'] = '';
	} else {
	$details['endtime'] = date( get_option('time_format'),strtotime($event->event_endtime));
	}
	$details['author'] = $e->display_name;
	$details['host'] = $host->display_name;	
	if ( $event->event_link_expires == 0 ) {
	$details['link'] = $event->event_link;
	} else {
		if ( my_calendar_date_comp( $event->event_end, date('Y-m-d',time()+$offset ) ) ) {
			$details['link'] = '';
		} else {
			$details['link'] = $event->event_link;
		}
	}
	if ( $event->event_open == '1' ) {
		$event_open = get_option( 'mc_event_open' );
	} else if ( $event->event_open == '0' ) {
		$event_open = get_option( 'mc_event_closed' );
	} else {
		$event_open = '';
	}
	$details['description'] = stripslashes($event->event_desc);
	if ($details['link'] != '') {
	$details['link_title'] = "<a href='".$event->event_link."'>".stripslashes($event->event_title)."</a>";
	} else {
	$details['link_title'] = stripslashes($event->event_title);	
	}
	$details['date'] = $date;
	$details['enddate'] = $date_end;
	$details['location'] = stripslashes($event->event_label);
	$details['street'] = stripslashes($event->event_street);
	$details['street2'] = stripslashes($event->event_street2);
	$details['city'] = stripslashes($event->event_city);
	$details['state'] = stripslashes($event->event_state);
	$details['postcode'] = stripslashes($event->event_postcode);
	$details['country'] = stripslashes($event->event_country);
	$details['hcard'] = stripslashes($hcard);
	$details['link_map'] = $map;
	$details['shortdesc'] = stripslashes($event->event_short);
	$details['event_open'] = $event_open;
	$details['icon'] = $category_icon;
	$details['color'] = $category_color;
	if ($event->event_approve == 1 ) {
		$details['event_status'] = __('Published','my-calendar');
	} else {
		$details['event_status'] = __('Reserved','my-calendar');
	}
	
  return $details;
}
?>