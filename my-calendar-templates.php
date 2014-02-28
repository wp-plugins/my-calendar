<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function jd_draw_template($array,$template,$type='list') {
	//1st argument: array of details
	//2nd argument: template to print details into
	$template = stripcslashes($template);	
	foreach ( $array as $key=>$value ) {
		if ( is_object($value) && !empty($value) ) {
			// null values return false...
		} else {
			if ( strpos( $template, "{".$key ) !== false ) {
				if ($type != 'list') {
					if ( $key == 'link' && $value == '') { $value = ( get_option('mc_uri') != '' && !is_numeric( get_option('mc_uri') ) )?get_option('mc_uri'):home_url(); }
					if ( $key != 'guid') { $value = htmlentities($value); }
				}
				if ( strpos( $template, "{".$key." " ) !== false ) { // only do preg_match if appropriate
					preg_match_all('/{'.$key.'\b(?>\s+(?:before="([^"]*)"|after="([^"]*)"|format="([^"]*)")|[^\s]+|\s+){0,2}}/', $template, $matches, PREG_PATTERN_ORDER );
					if ( $matches ) {
						$before = @$matches[1][0];
						$after = @$matches[2][0];
						$format = @$matches[3][0];
						if ( $format != '' ) { $value = date_i18n( stripslashes($format),strtotime(stripslashes($value)) ); }
						$value = ( $value == '' )?'':$before.$value.$after;
						$search = @$matches[0][0];
						$template = str_replace( $search, $value, $template );
					}
				} else { // don't do preg match (never required for RSS)
					$template = stripcslashes(str_replace( "{".$key."}", $value, $template ));					
				}			
			} // end {$key check
			// secondary search for RSS output
			$rss_search = "{rss_$key}";
			if ( strpos( $template, $rss_search ) !== false ) {
				$charset = get_option('blog_charset');
				if ( $charset == '' ) { $charset = 'UTF-8'; }
				//$value = htmlspecialchars( $value, ENT_QUOTES, $charset );
				//$value = htmlentities( $value, ENT_XML1, $charset );
				//	if ( $key == 'description' ) { echo $value; }
				$value = ent2ncr($value); // WP native function.
				$template = stripcslashes(str_replace($rss_search,$value,$template));
			}				
		} 
	}
//$new = microtime( true );
//$length = $new - $mtime;
//echo $length . ' seconds<br />'; //DEBUG		
	return stripslashes(trim($template));
}

function mc_map_string( $event, $source='event' ) {
	if ( $source == 'event' ) {
		$map_string = $event->event_street.' '.$event->event_street2.' '.$event->event_city.' '.$event->event_state.' '.$event->event_postcode.' '.$event->event_country;	
	} else {
		$map_string = $event->location_street.' '.$event->location_street2.' '.$event->location_city.' '.$event->location_state.' '.$event->location_postcode.' '.$event->location_country;	
	}
	return $map_string;
}

function mc_maplink( $event, $request='map', $source='event' ) {
	$map_string = mc_map_string( $event, $source );
	if ( $source == 'event' ) {
		if ( $request == 'gcal' ) { return $map_string; }
		$zoom = ( $event->event_zoom != 0 )?$event->event_zoom:'15';	
		$map_string = str_replace(" ","+",$map_string);
		if ( $event->event_longitude != '0.000000' && $event->event_latitude != '0.000000' ) {
			$map_string = "$event->event_latitude,$event->event_longitude";
			$connector = '';			
		}
	} else {
		$zoom = ( $event->location_zoom != 0 )?$event->location_zoom:'15';	
		$map_string = str_replace( " ","+",$map_string );
		if ( $event->location_longitude != '0.000000' && $event->location_latitude != '0.000000' ) {
			$map_string = "$event->location_latitude,$event->location_longitude";
			$connector = '';
		}	
	}
	if ( strlen( trim( $map_string ) ) > 6 ) {
		$map_url = "http://maps.google.com/maps?z=$zoom&amp;daddr=$map_string";
		if ( $request == 'url' || $source == 'location' ) { return $map_url; }
		$map_label = stripslashes( ( $event->event_label != "" )?$event->event_label:$event->event_title );
		$map = "<a href=\"$map_url\" class='map-link external'>".sprintf(__( 'Map<span> to %s</span>','my-calendar' ),$map_label )."</a>";
	} else {
		$map = "";
	}
	return $map;
}

function mc_google_cal( $dtstart, $dtend, $url, $title, $location, $description ) {
	$source = "https://www.google.com/calendar/render?action=TEMPLATE";
	$base = "&dates=$dtstart/$dtend";
	$base .= "&sprop=website:".$url;
	$base .= "&text=".urlencode($title);
	$base .= "&location=".urlencode($location);
	$base .= "&sprop=name:".urlencode(get_bloginfo('name'));
	$base .= "&details=".urlencode(stripcslashes($description));
    $base .= "&sf=true&output=xml";
	return $source . $base;
}

function mc_hcard( $event, $address='true', $map='true', $source='event', $context='event' ) {
	$the_map = mc_maplink( $event, 'url', $source );
	$event_url = ($source=='event')?$event->event_url:$event->location_url;
	$event_label = stripslashes( ($source=='event')?$event->event_label:$event->location_label );
	$event_street = stripslashes( ($source=='event')?$event->event_street:$event->location_street );
	$event_street2 = stripslashes( ($source=='event')?$event->event_street2:$event->location_street2 );
	$event_city = stripslashes( ($source=='event')?$event->event_city:$event->location_city );
	$event_state = stripslashes( ($source=='event')?$event->event_state:$event->location_state );
	$event_postcode = stripslashes( ($source=='event')?$event->event_postcode:$event->location_postcode );
	$event_country = stripslashes( ($source=='event')?$event->event_country:$event->location_country );
	$event_phone = stripslashes( ($source=='event')?$event->event_phone:$event->location_phone );
	
	if ( !$event_url && !$event_label && !$event_street && !$event_street2 && !$event_city && !$event_state && !$event_postcode && !$event_country && !$event_phone ) return;
	
	$sitelink_html = "<div class='url link'><a href='$event_url' class='location-link external'>".sprintf(__('Visit web site<span>: %s</span>','my-calendar'),$event_label)."</a></div>";
	$hcard = "<div class=\"address vcard\">";
	if ( $address == 'true' ) {
		$hcard .= "<div class=\"adr\">";
		if ($event_label != "") {$hcard .= "<strong class=\"org\">".$event_label."</strong><br />";	}
		$hcard .= ( $event_street.$event_street2.$event_city.$event_state.$event_postcode.$event_country.$event_phone == '' )?'':"<div class='sub-address'>";
		if ($event_street != "") {$hcard .= "<div class=\"street-address\">".$event_street."</div>";}
		if ($event_street2 != "") {	$hcard .= "<div class=\"street-address\">".$event_street2."</div>";	}
		if ($event_city != "") {$hcard .= "<div><span class=\"locality\">".$event_city."</span><span class='sep'>, </span>";}						
		if ($event_state != "") {$hcard .= "<span class=\"region\">".$event_state."</span> ";}
		if ($event_postcode != "") {$hcard .= " <span class=\"postal-code\">".$event_postcode."</span></div>";}	
		if ($event_country != "") {	$hcard .= "<div class=\"country-name\">".$event_country."</div>";}
		if ($event_phone != "") { $hcard .= "<div class=\"tel\">".$event_phone."</div>";}
		$hcard .= ( $event_street.$event_street2.$event_city.$event_state.$event_postcode.$event_country.$event_phone == '' )?'':"</div>";
		$hcard .= "</div>";
	}
	if ( $map == 'true' ) {
		$the_map = "<a href='$the_map' class='external'>$event_label</a>";
		$hcard .= ($the_map!='')?"<div class='url map'>$the_map</div>":'';
	}
	if ( $context != 'map' ) {
		$hcard .= ($event_url!='')?$sitelink_html:'';
	}
	$hcard .= "</div>";	
	return $hcard;
}

// Produces the array of event details used for drawing templates
function event_as_array( $event ) {
	$details = array();
	$details['post'] = $event->event_post;
	$date_format = ( get_option('mc_date_format') != '' )?get_option('mc_date_format'):get_option('date_format');	
	$details = apply_filters( 'mc_insert_author_data', $details, $event );
	$details = apply_filters( 'mc_filter_image_data', $details, $event );
	$map = mc_maplink( $event );
	$map_url = mc_maplink( $event, 'url' );
	$sitelink_html = "<div class='url link'><a href='$event->event_url' class='location-link external'>".sprintf(__('Visit web site<span>: %s</span>','my-calendar'),$event->event_label)."</a></div>";
	$details['sitelink_html'] = $sitelink_html;
	$details['sitelink'] = $event->event_url;
	$details['access'] = mc_expand( get_post_meta( $event->event_post, '_mc_event_access', true ), 'mc_event_access' );

	// date & time fields
	$dtstart = mc_format_timestamp( strtotime($event->occur_begin) );
	$dtend = mc_format_timestamp( strtotime($event->occur_end) );	
	$real_end_date = $event->occur_end;
	$details['date_utc'] = date_i18n( apply_filters( 'mc_date_format', $date_format, 'template_begin_ts' ) , $event->ts_occur_begin );
	$details['date_end_utc'] = date_i18n( apply_filters( 'mc_date_format', $date_format, 'template_end_ts' ) , $event->ts_occur_end );
	$details['time'] = ( date( 'H:i:s', strtotime( $event->occur_begin ) ) == '00:00:00' )?get_option( 'mc_notime_text' ):date(get_option('mc_time_format'), strtotime( $event->occur_begin ) );
	$endtime = ( date( 'H:i:s', strtotime($event->occur_end) ) == '00:00:00')?'23:59:00':date( 'H:i:s',strtotime($event->occur_end) );	
	$details['endtime'] = ( $event->occur_end == $event->occur_begin || $event->event_hide_end == 1 )?'':date_i18n( get_option('mc_time_format'),strtotime( $endtime ));
	$tz = mc_user_timezone();
	$details['runtime'] = mc_runtime( $event->ts_occur_begin, $event->ts_occur_end, $event );
	if ($tz != '') {
		$local_begin = date_i18n( get_option('mc_time_format'), strtotime($event->occur_begin ."+$tz hours") );
		$local_end = date_i18n( get_option('mc_time_format'), strtotime($event->occur_end ."+$tz hours") );
		$details['usertime'] = "$local_begin";
		$details['endusertime'] = ( $local_begin == $local_end )?'':"$local_end";
	} else {
		$details['usertime'] = $details['time'];
		$details['endusertime'] = ( $details['time'] == $details['endtime'] )?'':$details['endtime'];
	}
	$details['dtstart'] = date( 'Y-m-d\TH:i:s', strtotime( $event->occur_begin ) );// hcal formatted
	$details['dtend'] = date( 'Y-m-d\TH:i:s', strtotime($event->occur_end ) );	//hcal formatted end
	$details['rssdate'] = date( 'D, d M Y H:i:s +0000', strtotime( $event->event_added ) );	
		$date = date_i18n( apply_filters( 'mc_date_format', $date_format, 'template_begin' ) ,strtotime( $event->occur_begin ) );
		$date_end = date_i18n( apply_filters( 'mc_date_format', $date_format, 'template_end' ) ,strtotime($real_end_date) );
	$date_arr = array( 'occur_begin'=>$event->occur_begin,'occur_end'=>$event->occur_end );
	$date_obj = (object) $date_arr;
	if ( $event->event_span == 1 ) {
		$dates = mc_event_date_span( $event->event_group_id, $event->event_span, array( 0=>$date_obj ) );
	} else {
		$dates = array();
	}
	$details['date'] = ($event->event_span != 1)?$date:mc_format_date_span( $dates, 'simple', $date );
	$details['enddate'] = $date_end;
	$details['daterange'] = ($date == $date_end)?$date:"<span class='mc_db'>$date</span> <span>&ndash;</span> <span class='mc_de'>$date_end</span>";
	$details['timerange'] = ( ($details['time'] == $details['endtime'] ) || $event->event_hide_end == 1 )?$details['time']:"<span class='mc_tb'>".$details['time']."</span> <span>&ndash;</span> <span class='mc_te'>".$details['endtime']."</span>";
	$details['datespan'] = ($event->event_span == 1 || ($details['date'] != $details['enddate']) )?mc_format_date_span( $dates ):$date;
	$details['multidate'] = mc_format_date_span( $dates, 'complex', "<span class='fallback-date'>$date</span><span class='separator'>,</span> <span class='fallback-time'>$details[time]</span>&ndash;<span class='fallback-endtime'>$details[endtime]</span>" );
	$details['began'] = $event->event_begin; // returns date of first occurrence of an event.
	$details['recurs'] = mc_event_recur_string( $event );
	$details['repeats'] = $event->event_repeats;
	
	// category fields
	$details['cat_id'] = $event->event_category;
	$details['category'] = stripslashes($event->category_name);
	$details['icon'] = mc_category_icon( $event,'img' );
	$details['icon_html'] = "<img src='$details[icon]' class='mc-category-icon' alt='".__('Category','my-calendar').": ".esc_attr($event->category_name)."' />";
	$details['color'] = $event->category_color;
	
	// special
	$details['skip_holiday'] = ($event->event_holiday == 0)?'false':'true';
	$details['event_status'] = ( $event->event_approved == 1 )?__('Published','my-calendar'):__('Reserved','my-calendar');	
	
	// general text fields
	$strip_desc = mc_newline_replace( strip_tags( $event->event_desc ) );	
	$details['title'] = stripcslashes($event->event_title);
	$details['description'] = ( get_option('mc_process_shortcodes') == 'true' )?apply_filters('the_content',$event->event_desc):wpautop(stripslashes($event->event_desc));
	$details['description_raw'] = stripslashes($event->event_desc);
	$details['description_stripped'] = strip_tags(stripslashes($event->event_desc));
	$details['shortdesc'] = ( get_option('mc_process_shortcodes') == 'true' )?apply_filters('the_content',$event->event_short):wpautop(stripslashes($event->event_short));
	$details['shortdesc_raw'] = stripslashes($event->event_short);
	$details['shortdesc_stripped'] = strip_tags(stripslashes($event->event_short));

	// registration fields
	$details['event_open'] = mc_event_open( $event );
	$details['event_tickets'] = $event->event_tickets;
	$details['event_registration'] = stripslashes( wp_kses_data( $event->event_registration ) );

	// links
	$templates = get_option('mc_templates');
	$details_template = ( !empty($templates['label']) )? stripcslashes($templates['label']):__('Details about','my-calendar').' {title}';
	$tags = array( "{title}","{location}","{color}","{icon}","{date}","{time}" );
	$replacements = array( stripslashes($event->event_title), stripslashes($event->event_label), $event->category_color, $event->category_icon, $details['date'], $details['time'] );
	$details_label = str_replace($tags,$replacements,$details_template );
	//$details_label = mc_get_details_label( $event, $details ); // recursive...hmmmm.
	$details_link = mc_get_details_link( $event );
	$details['link'] = mc_event_link( $event );
	$details['link_title'] = ($details['link'] != '')?"<a href='".$event->event_link."'>".stripslashes($event->event_title)."</a>":stripslashes($event->event_title);	
	$details['details_link'] = ( get_option( 'mc_uri' ) != '' && !is_numeric( get_option('mc_uri') ) )?$details_link:'';
	$details['details'] = ( get_option( 'mc_uri' ) != '' && !is_numeric( get_option('mc_uri') ) )?"<a href='$details_link' class='mc-details'>$details_label</a>":'';
	$details['linking'] = ( $details['link'] != '' )?$event->event_url:$details_link;
		
	// location fields
	$details['location'] = stripslashes($event->event_label);
	$details['street'] = stripslashes($event->event_street);
	$details['street2'] = stripslashes($event->event_street2);
	$details['phone'] = apply_filters( 'mc_phone_format', stripslashes( $event->event_phone ) );
	$details['phone2'] = apply_filters( 'mc_phone_format', stripslashes( $event->event_phone2 ) );
	$details['city'] = stripslashes($event->event_city);
	$details['state'] = stripslashes($event->event_state);
	$details['postcode'] = stripslashes($event->event_postcode);
	$details['country'] = stripslashes($event->event_country);
	$details['hcard'] = stripslashes( mc_hcard( $event ) );
	$details['link_map'] = $map;
	$details['map_url'] = $map_url;
	$details['map'] = mc_generate_map( $event );
		$url = ( get_option( 'mc_uri' ) != '' && !is_numeric( get_option( 'mc_uri' ) ) )?$details_link:$event->event_url;
	$details['gcal'] = mc_google_cal( $dtstart, $dtend, $url, stripcslashes( $event->event_title ), mc_maplink( $event, 'gcal' ), $strip_desc );
	$details['gcal_link'] = "<a href='".mc_google_cal( $dtstart, $dtend, $url, stripcslashes( $event->event_title ) ,  mc_maplink( $event, 'gcal' ), $strip_desc )."'>".sprintf( __('<span class="screenreader">Send %1$s to </span>Google Calendar','my-calendar'), stripcslashes( $event->event_title ) )."</a>";
	$details['location_access'] = mc_expand( $event->event_access, 'mc_location_access' );
	$details['location_source'] = $event->event_location;	
	
	// IDs
	$details['dateid'] = $event->occur_id; // unique ID for this date of this event
	$details['id'] = $event->event_id;
	$details['group'] = $event->event_group_id;
	$details['event_span'] = $event->event_span;
	
	// RSS guid
	$details['region'] = $event->event_region;
	$details['guid'] =( get_option( 'mc_uri' ) != '' && !is_numeric( get_option('mc_uri') ) )?"<guid isPermaLink='true'>$details_link</guid>":"<guid isPermalink='false'>$details_link</guid>";

	// iCAL
	$details['ical_location'] = $event->event_label .' '. $event->event_street .' '. $event->event_street2 .' '. $event->event_city .' '. $event->event_state .' '. $event->event_postcode;
	$details['ical_description'] = str_replace( "\r", "=0D=0A=", $event->event_desc );
	$details['ical_desc'] = $strip_desc;
	$details['ical_start'] = $dtstart;
	$details['ical_end'] = $dtend;
	$ical_link = mc_build_url( array('vcal'=>$event->occur_id), array('month','dy','yr','ltype','loc','mcat','format'), get_option( 'mc_uri' ) );
	$details['ical'] = $ical_link;
	$details['ical_html'] = "<a class='ical' rel='nofollow' href='$ical_link'>".__('iCal','my-calendar')."</a>";
		
	// get URL, TITLE, LOCATION, DESCRIPTION strings
	$details = apply_filters( 'mc_filter_shortcodes',$details,$event );
	return $details;
}

function mc_get_details_label( $event, $details ) {
	$templates = get_option( 'mc_templates' );
	$details_template = ( !empty($templates['label']) )? stripcslashes($templates['label']): sprintf( __('Event Details %s','my-calendar'), '<span class="screen-reader-text">about {title}</span> &raquo;' );
	$details_label = wp_kses( jd_draw_template( $details, $details_template ), array( 'span'=>array( 'class'=>array('screen-reader-text') ), 'em', 'strong' ) );
	return $details_label;
}

function mc_format_timestamp( $os ) {
	$offset = (60*60*get_option('gmt_offset'));
	$time = ( get_option('mc_ical_utc')=='true')?date("Ymd\THi00", (mktime(date('H',$os),date('i',$os), date('s',$os), date('m',$os),date('d',$os), date('Y',$os) ) - ($offset) ) )."Z":date("Ymd\THi00", (mktime(date('H',$os),date('i',$os), date('s',$os), date('m',$os),date('d',$os), date('Y',$os) ) ) ); 
	return $time;
}

function mc_runtime( $start, $end, $event ) {
	if ( $event->event_hide_end || $start == $end ) {
		return;
	} else {
		return human_time_diff( $start, $end );
	}
}

function mc_event_link( $event ) {
	if ( $event->event_link_expires == 0 ) {
		$link = esc_url( $event->event_link );
	} else {
		if ( my_calendar_date_xcomp( $event->occur_end, date('Y-m-d',current_time('timestamp') ) ) ) {
			$link = '';
			do_action( 'mc_event_expired', $event );
		} else {
			$link = esc_url( $event->event_link );
		}
	}	
	return $link;
}

function mc_event_open( $event ) {
	if ( $event->event_open == '1' ) {
		$event_open = get_option( 'mc_event_open' );
	} else if ( $event->event_open == '0' ) {
		$event_open = get_option( 'mc_event_closed' ); 
	} else { 
		$event_open = '';	
	}
	return apply_filters( 'mc_event_open_text', $event_open, $event );
}

function mc_generate_map( $event, $source='event' ) {
	$id = rand();
	$maptype = 'roadmap';
	$zoom = ( $event->event_zoom != 0 )?$event->event_zoom:'15';
	$category_icon = mc_category_icon( $event,'img' );
	if ( !$category_icon ) { $category_icon = "//maps.google.com/mapfiles/marker_green.png"; }
	$address = addslashes( mc_map_string( $event, $source ) );
	$hcard = mc_hcard( $event, true, false, 'event','map' );
	$hcard = wp_kses( str_replace( array('</div>','<br />','<br><br>' ),'<br>', $hcard ), array( 'br'=>array() ) );	
	$html = addslashes( apply_filters( 'mc_map_html', $hcard, $event ) );
	$width= apply_filters( 'mc_map_height', '100%', $event );
	$height = apply_filters( 'mc_map_height', '300px', $event );
	$styles = " style='width: $width;height: $height'";
	$value = "
<script type='text/javascript'>
	(function ($) { 'use strict';
		$(function () {
			$('#mc_gmap_$id').gmap3(
				{
					marker:{ 
						values:[{
							address: '$address',
							options: { icon: new google.maps.MarkerImage( '$category_icon', new google.maps.Size(32,32,'px','px') ) }, 
							data:'$html'
							}], 
						events:{
						  click: function( marker, event, context ){
							var map = $(this).gmap3('get'),
							  infowindow = $(this).gmap3( { get:{name:'infowindow'} } );
							if ( infowindow ){
							  infowindow.open(map, marker);
							  infowindow.setContent(context.data);
							} else {
							  $(this).gmap3({
								infowindow:{
								  anchor:marker, 
								  options:{content: context.data}
								}
							  });
							}
						  }
						}
					},
					map:{
						options:{
						  zoom: $zoom,
						  mapTypeControl: true,
						  mapTypeControlOptions: {
							style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
						  },
						  navigationControl: true,
						  scrollwheel: true,
						  streetViewControl: false
						}
					}	
			});	
		}); 
	})(jQuery);
</script>
	<div id='mc_gmap_$id' class='mc-gmap-fupup'$styles></div>";
	return $value;
}

function mc_expand( $data, $option ) {
	//$option = get_option( $option );
	$output = '';
	if ( is_array( $data ) ) {
		if ( isset( $data['notes'] ) ) {
			unset( $data['notes'] );
		}
		foreach ( $data as $key => $value ) {
			$class = ( isset( $value ) ) ? sanitize_title( $value ) : '';
			$label = ( isset( $value ) ) ? $value : false;
			if ( !$label ) { continue; }
			$output .= "<li class='$class'><span>$label</span></li>\n";
		}
		return "<ul>".$output."</ul>";
	}
}

function mc_event_date_span( $group_id, $event_span, $dates=array() ) {
	global $wpdb;
	$mcdb = $wpdb;
	if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	$group_id = (int) $group_id;
	if ( $group_id == 0 && $event_span != 1 ) {
		return $dates;
	} else {
		$sql = "SELECT occur_begin, occur_end FROM ".my_calendar_event_table()." WHERE occur_group_id = $group_id ORDER BY occur_begin ASC";
		$dates = $mcdb->get_results( $sql );
		return $dates; 
	}
}
function mc_format_date_span( $dates, $display='simple',$default='' ) {
	if ( !$dates ) return $default;
	$count = count($dates);
	$last = $count - 1;
	if ( $display == 'simple' ) {
		$begin = $dates[0]->occur_begin;
		$end = $dates[$last]->occur_end;
		$begin = date_i18n( apply_filters( 'mc_date_format', get_option('mc_date_format'), 'date_span_begin' ),strtotime( $begin ));
		$end = date_i18n( apply_filters( 'mc_date_format', get_option('mc_date_format'), 'date_span_end' ),strtotime( $end ));
		$return = $begin . ' <span>&ndash;</span> ' . $end;	
	} else {
		$return = "<ul class='multidate'>";
		foreach ($dates as $date ) {
			$begin = $date->occur_begin;
			$end = $date->occur_end;
				$day_begin = date( 'Y-m-d', $date->occur_begin );
				$day_end = date( 'Y-m-d', $date->occur_end );
			$bformat = "<span class='multidate-date'>".date_i18n( get_option('mc_date_format'),strtotime( $begin ) ).'</span> <span class="multidate-time">'.date_i18n( get_option('mc_time_format'), strtotime( $begin ) )."</span>";
			$endtimeformat = ($date->occur_end == '00:00:00')?'':' '.get_option('mc_time_format');
			$eformat = ( $day_begin != $day_end )?get_option('mc_date_format').$endtimeformat:$endtimeformat;
			$span = ($eformat != '')?" <span>&ndash;</span> <span class='multidate-end'>":'';
			$endspan = ($eformat != '')?"</span>":'';
			$return .= "<li>$bformat".$span.date_i18n( $eformat,strtotime( $end ))."$endspan</li>";
		}
		$return .= "</ul>";
	}
	return $return;
}

add_filter( 'mc_insert_author_data', 'mc_author_data', 10, 2 );
function mc_author_data( $details, $event ) {
	if ( $event->event_author != 0 ) {
		$e = get_userdata($event->event_author);
		$host = get_userdata($event->event_host);
		$details['author'] = $e->display_name;
		$details['gravatar'] = get_avatar( $e->user_email );
		$details['author_email'] = $e->user_email;
		$details['author_id'] = $event->event_author;
		$details['host'] = (!$host || $host->display_name == '')?$e->display_name:$host->display_name; 
		$details['host_id'] = $event->event_host;
		$details['host_email'] = (!$host || $host->user_email == '')?$e->user_email:$host->user_email;
		$details['host_gravatar'] = ( !$host || $host->user_email == '' )?$details['gravatar']:get_avatar( $host->user_email );
	} else {
		$details['author'] = 'Public Submitter';
		$details['host'] = 'Public Submitter';
		$details['host_email'] = '';
		$details['author_email'] = '';
		$details['gravatar'] = '';
		$details['host_gravatar'] = '';
		$details['author_id'] = false;
		$details['host_id'] = false;
	}
	return $details;
}

add_filter( 'mc_filter_image_data', 'mc_image_data', 10, 2 );
function mc_image_data( $details, $event ) {
	$atts = apply_filters( 'mc_post_thumbnail_atts',  array( 'class'=>'mc-image' ) ); 
	$details['full'] = get_the_post_thumbnail( $event->event_post );	
	$sizes = get_intermediate_image_sizes();
	foreach ( $sizes as $size ) {
		$details[$size] = get_the_post_thumbnail( $event->event_post, $size, $atts );
	}
	if ( is_numeric( $event->event_post ) && isset( $details['medium'] ) ) {
		$details['image_url'] = strip_tags( $details['medium'] );
		$details['image'] = $details['medium'];
	} else {
		$details['image_url'] = ( $event->event_image != '' )?$event->event_image:'';
		$details['image'] = ( $event->event_image != '' )?"<img src='$event->event_image' alt='' class='mc-image' />":'';
	}
	return $details;
}

function mc_event_recur_string( $event ) {
	$recurs = str_split( $event->event_recur, 1 );
	$recur = $recurs[0];
	$every = ( isset($recurs[1]) )?$recurs[1]:1;
	$month_date = date( 'dS',strtotime( $event->occur_begin ) );
	$day_name = date_i18n( 'l',strtotime( $event->occur_begin ) );
	$week_number = mc_ordinal( week_of_month( date('j',strtotime($event->occur_begin) ) ) +1 );
	switch ( $recur ) {
		case 'S':$event_recur=__('Does not recur','my-calendar');break;
		case 'D':$event_recur=__('Daily','my-calendar');break;
		case 'E':$event_recur=__('Daily, weekdays only','my-calendar');break;
		case 'W':$event_recur=__('Weekly','my-calendar');break;
		case 'B':$event_recur=__('Bi-weekly','my-calendar');break;
		case 'M':$event_recur=sprintf(__('the %s of each month','my-calendar'), $month_date );break;
		case 'U':$event_recur=sprintf(__('the %s %s of each month','my-calendar'), $week_number, $day_name );break;
		case 'Y':$event_recur=__('Annually','my-calendar');break;
	}
	return apply_filters( 'mc_event_recur_string', $event_recur, $event );
}