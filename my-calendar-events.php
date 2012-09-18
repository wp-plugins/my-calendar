<?php
// used to generate upcoming events lists
function mc_get_all_events( $category ) {
global $wpdb;
	$mcdb = $wpdb;
if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	$select_category = ( $category!='default' )?mc_select_category($category,'all'):'';
	$limit_string = mc_limit_string('all');
	
	if ($select_category != '' && $limit_string != '') {
		$join = ' AND ';
	} else if ($select_category == '' && $limit_string != '' ) {
		$join = ' WHERE ';
	} else {
		$join = '';
	}
	$limits = $select_category . $join . $limit_string;
    $events = $mcdb->get_results("SELECT *,event_begin as event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) $limits AND event_flagged <> 1");
	$offset = (60*60*get_option('gmt_offset'));
	$date = date('Y', time()+($offset)).'-'.date('m', time()+($offset)).'-'.date('d', time()+($offset));
	$arr_events = array();
    if (!empty($events)) {
		$groups = array();
        foreach( array_keys($events) as $key) {
			$event =& $events[$key];
			if ( !in_array( $event->event_group_id, $groups ) ) {
				$event_occurrences = mc_increment_event( $event );
				$arr_events = array_merge( $arr_events, $event_occurrences );
				if ( $event->event_span == 1 ) {
					//$groups[] = $event->event_group_id;
				}
			}			
		}
	} 
	return $arr_events;
}

function mc_get_rss_events( $cat_id=false) {
	global $wpdb;
	$mcdb = $wpdb;
	if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	if ( $cat_id ) { $cat = "WHERE event_category = $cat_id"; } else { $cat = ''; }
	$events = $mcdb->get_results("SELECT *,event_begin as event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) $cat ORDER BY event_added DESC LIMIT 0,15" );
	foreach ( array_keys($events) as $key ) {
		$event =& $events[$key];	
		$output[] = $event;
	}
	return $output;
}

function my_calendar_get_event($date,$id,$type='html') {
	global $wpdb;
	$mcdb = $wpdb;
	  if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	$date = explode("-",$date);
	$m = (int) $date[1];
	$d = (int) $date[2];
	$y = (int) $date[0];
	if (!checkdate($m,$d,$y)) {
		return;
	}
    $event = $mcdb->get_row("SELECT * FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE event_id=$id");
	$event->event_start_ts = strtotime( $event->event_begin . ' ' . $event->event_time );
	if ( $type == 'object' ) { return $event; }
	if ($event) {
		$value = "	<div id='mc_event'>
			".my_calendar_draw_event( $event,'single',"$y-$m-$d" )."
		</div>\n";
	}
	return $value;
}
// Grab all events for the requested date from calendar
function my_calendar_grab_events($y,$m,$d,$category=null,$ltype='',$lvalue='',$source='calendar') {
			if ( isset($_GET['mcat']) ) { $ccategory = $_GET['mcat']; } else { $ccategory = $category; }
			if ( isset($_GET['ltype']) ) { $cltype = $_GET['ltype']; } else { $cltype = $ltype; }
			if ( isset($_GET['loc']) ) { $clvalue = $_GET['loc']; } else { $clvalue = $lvalue; }
			if ( $ccategory == '' ) { $ccategory = 'all'; }
			if ( $clvalue == '' ) { $clvalue = 'all';  }			
			if ( $cltype == '' ) { $cltype = 'all'; }
			if ( $clvalue == 'all' ) { $cltype = 'all'; }
	if (!checkdate($m,$d,$y)) {	return;	} // not a valid date
	$caching = ( get_option('mc_caching_enabled') == 'true' )?true:false;
	if ( $source != 'upcoming' ) { // no caching on upcoming events by days widgets or lists
		if ( $caching ) {
			$output = mc_check_cache( $y, $m, $d, $ccategory, $cltype, $clvalue );
			if ( $output && $output != 'empty' ) { return $output; }
			if ( $output == 'empty' ) { return; }
		}
	}
    global $wpdb;
	$mcdb = $wpdb;
	if ( get_option( 'mc_remote' ) == 'true' && function_exists('mc_remote_db') ) { $mcdb = mc_remote_db(); }
	$select_category = ( $category != null )?mc_select_category($category):'';
	$select_location = mc_limit_string( 'grab', $ltype, $lvalue );

	if ( $caching && $source != 'upcoming' ) { $select_category = ''; $select_location = ''; } 
	// if caching, then need all categories/locations in cache. UNLESS this is an upcoming events list
	
    $arr_events = array();
    // set the date format
    $date = $y . '-' . $m . '-' . $d;
	$limit_string = "event_flagged <> 1";
	if ( date( 'w',strtotime( $date ) ) != 0 && date( 'w',strtotime( $date ) ) != 6 ) {
		$weekday_string = "
		SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'E' AND event_begin <= '$date' AND event_repeats = 0 UNION
		SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) 
		WHERE $select_category $select_location $limit_string AND event_recur = 'E' AND '$date' >= event_begin AND event_repeats != 0 
		AND (event_repeats+1) >= ( (DATEDIFF('$date',event_end)+1) - ((WEEK('$date') - WEEK(event_end))*2) ) UNION ";		
	} else {
		$weekday_string = ''; 
	}
	$events = $mcdb->get_results($weekday_string . "
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_begin <= '$date' AND event_end >= '$date' AND event_recur = 'S'
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'Y' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin)
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'M' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date'),EXTRACT(YEAR_MONTH FROM event_begin))) <= event_repeats
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'U' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats = 0
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'U' AND EXTRACT(YEAR FROM '$date') >= EXTRACT(YEAR FROM event_begin) AND event_repeats != 0 AND (PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM '$date'),EXTRACT(YEAR_MONTH FROM event_begin))) <= event_repeats
	UNION	
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'B' AND '$date' >= event_begin AND event_repeats = 0
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'B' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*14) >= (TO_DAYS('$date') - TO_DAYS(event_end))
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'W' AND '$date' >= event_begin AND event_repeats = 0
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'W' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats*7) >= (TO_DAYS('$date') - TO_DAYS(event_end))	
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'D' AND '$date' >= event_begin AND event_repeats = 0
	UNION
	SELECT *,event_begin AS event_original_begin FROM " . MY_CALENDAR_TABLE . " JOIN " . MY_CALENDAR_CATEGORIES_TABLE . " ON (event_category=category_id) WHERE $select_category $select_location $limit_string AND event_recur = 'D' AND '$date' >= event_begin AND event_repeats != 0 AND (event_repeats) >= (TO_DAYS('$date') - TO_DAYS(event_end))	
	ORDER BY event_id");

	if (!empty($events)) {
			foreach( array_keys($events) as $key) {
			$event =& $events[$key];
			// add timestamps for start and end
				$diff = strtotime($event->event_end) - strtotime($event->event_begin);
				$event_end = date( 'Y-m-d',( strtotime( $date )+$diff ) );
				$fifth_week = $event->event_fifth_week;
				switch ($event->event_recur) {
					case 'S':
					case 'D':
					case 'E':
						$event->event_begin = $date;
						$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );
						$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
						$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
						$arr_events[]=$event;
					break;
					case 'Y':
					// Technically we don't care about the years, but we need to find out if the 
					// event spans the turn of a year so we can deal with it appropriately.
					$year_begin = date('Y',strtotime($event->event_begin));
					$year_end = date('Y',strtotime($event->event_end));
					if ($year_begin == $year_end) {
						if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) && 
							date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
								$event->event_begin = $date;
								$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );
								$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
								$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
								$arr_events[]=$event;
						}
					} else if ($year_begin < $year_end) {
						if (date('m-d',strtotime($event->event_begin)) <= date('m-d',strtotime($date)) || 
							date('m-d',strtotime($event->event_end)) >= date('m-d',strtotime($date))) {
								$event->event_begin = $date;
								$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
								$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
								$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
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
									$event->event_begin = $date;
									$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
									$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
									$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
						      		$arr_events[]=$event;
							}
					    } else if ($month_begin < $month_end) {
							if ( ($event->event_begin <= date('Y-m-d',strtotime($date))) && (date('d',strtotime($event->event_begin)) <= date('d',strtotime($date)) || 
								date('d',strtotime($event->event_end)) >= date('d',strtotime($date))) )	{
									$event->event_begin = $date;
									$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
									$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
									$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
									$arr_events[]=$event;
							}
					    }					
					break;
					case 'U':
				    // Technically we don't care about the years or months, but we need to find out if the 
				    // event spans the turn of a year or month so we can deal with it appropriately.
				    $month_begin = date( 'm',strtotime($event->event_begin) );
				    $month_end = date( 'm',strtotime($event->event_end) );
					$day_of_event = date( 'D',strtotime($event->event_begin) );
					$date_of_event = date( 'd',strtotime($event->event_begin) );
					$current_day = date( 'D',strtotime($date) );
					$current_date = date( 'd',strtotime($date) );
					$week_of_event = week_of_month($date_of_event);
					$current_week = week_of_month($current_date);
					$day_diff = jd_date_diff($event->event_begin,$event->event_end);
					$first_of_month = '01-'.date('M',strtotime($date)).'-'.date('Y',strtotime($date));
					if ( date( 'D',strtotime($event->event_begin ) ) == date( 'D',strtotime( "$first_of_month - 1 day" ) ) ) {
						$start = ($week_of_event)*7+1;
						$finish = ($start + 7)+1;					
					} else {
						$start = ($week_of_event)*7;
						$finish = ($start + 7);
					}
					$t = date('t',strtotime($date));
					if ($start < 1) { $start = 1; }
					$month = date( 'm', strtotime($date));					
					for ($i=$start;$i<=$finish;$i++) {
						if ( $i > $t ) {
							$day = $i-$t;
							$month = (date('m',strtotime($date)) == 12)?1:date('m',strtotime($date))+1;
						} else {
							$day = $i;
							$month = date( 'm', strtotime($date));
						}
						
						$string = date( 'Y', strtotime($date) ).'-'.$month.'-'.$day;
						if ( date('D',strtotime($string)) == $day_of_event ) {
							$date_of_event_this_month = $i;
							break;
						} 
					}
					if ( $fifth_week == 1 && $date_of_event_this_month > $t ) {		
						$finish = $start;
						$start = $start - 7;
						for ($i=$start;$i<=$finish;$i++) {
							$string = date( 'Y',strtotime($date) ).'-'.date('m',strtotime($date)).'-'.$i;
							if ( date('D',strtotime($string)) == $day_of_event ) {
								$date_of_event_this_month = $i;
								break;
							} 
						}
					}		
					if ( my_calendar_date_comp($event->event_begin,$date) ) {
						if ( ( $current_day == $day_of_event && $current_week == $week_of_event ) || ( $current_date >= $date_of_event_this_month && $current_date <= $date_of_event_this_month+$day_diff && $date_of_event_this_month != '' ) ) {	
							$event->event_begin = $date;
							$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
							$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
							$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
							$arr_events[]=$event;							
						} else {
							break;
						}
					}
					break;
					case 'B':
				    // Now we are going to check to see what day the original event
				    // fell on and see if the current date is both after it and on 
				    // the correct day. If it is, display the event!
				    $day_start_event = date('w',strtotime($event->event_begin));
				    $day_end_event = date('w',strtotime($event->event_end));
				    $current_day = date('w',strtotime($date));
					$current_date = date('Y-m-d',strtotime($date));
					$start_date = $event->event_begin;
					
					if ($event->event_repeats != 0) {
						for ($n=0;$n<=$event->event_repeats;$n++) {
							if ( $current_date == my_calendar_add_date($start_date,(14*$n)) ) {
							    if ($day_start_event > $day_end_event) {
									if (($day_start_event <= $current_day) || ($current_day <= $day_end_event))	{
									$event->event_begin = $date;
									$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
									$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
									$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
									$arr_events[]=$event;
							    	}
							    } else if (($day_start_event < $day_end_event) || ($day_start_event == $day_end_event)) {
									if (($day_start_event <= $current_day) && ($current_day <= $day_end_event))	{
									$event->event_begin = $date;
									$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
									$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
									$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
									$arr_events[]=$event;
							    	}		
							    }
							}
						}	
					} else {
						// get difference between today and event start date in biweekly periods; grab enough events to fill max poss.
						$diffdays = jd_date_diff($start_date,$current_date);
						$diffper = floor($diffdays/14) - 2;
						$advanceper = get_option('mc_show_months') * 3;
						$diffend = $diffper + $advanceper;
						for ($n=$diffper;$n<=$diffend;$n++) {
							if ( $current_date == my_calendar_add_date($start_date,(14*$n)) ) {
								$event->event_begin = $date;
								$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
								$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
								$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");
								$arr_events[]=$event;
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
							$event->event_begin = $date;
							$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
							$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
							$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");						
							$arr_events[]=$event;
				    	}
				    } else if (($plan[$day_start_event] < $plan[$day_end_event]) || ($plan[$day_start_event]== $plan[$day_end_event])) {
						if (($plan[$day_start_event] <= $plan[$current_day]) && ($plan[$current_day] <= $plan[$day_end_event]))	{
							$event->event_begin = $date;
							$event->event_end = date( 'Y-n-j', strtotime( $date )+$diff );				
							$event->event_start_ts = strtotime("$event->event_begin $event->event_time");
							$event->event_end_ts = strtotime("$event->event_end $event->event_endtime");						
							$arr_events[]=$event;
				    	}		
				    }
					break;
				}
			}
     	}
	if ( $source != 'upcoming' && $caching ) { 
		$new_cache = mc_create_cache( $arr_events, $y, $m, $d );
		if ( $new_cache ) {
			$output = mc_check_cache( $y, $m, $d, $ccategory, $cltype, $clvalue );
			return $output; 
		} else { 
			// need to clean cache if the cache is maxed.
			return mc_clean_cache( $arr_events, $ccategory, $cltype, $clvalue ); 
		}		
	} else {
		return $arr_events;
	}
}

function mc_check_cache($y, $m, $d, $category, $ltype, $lvalue) {
	$caching = ( get_option('mc_caching_enabled') == 'true' )?true:false;
	if ( $caching == true ) {
		$cache = get_transient("mc_cache");
		if ( isset( $cache[$y][$m][$d] ) ) {
			$value = $cache[$y][$m][$d];
		} else {
			return false;
		}
		if ( $value ) { return mc_clean_cache($value, $category,$ltype,$lvalue); } else { return false; }
	} else {
		return false;
	}
}

function mc_clean_cache( $cache, $category, $ltype, $lvalue ) {
global $wpdb;
	$mcdb = $wpdb;
	// process cache to strip events which do not meet current restrictions
	if ( $cache == 'empty' ) return false;
	$type = ($ltype != 'all')?"event_$ltype":"event_state";
	$return = false;
	if ( is_array($cache) ) {
			if ( strpos( $category, ',' ) !== false ) {
				$cats = explode(',',$category);
			} else if ( strpos( $category, '|' ) !== false ) {
				$cats = explode('|',$category);
			} else {
				$cats = array( $category );
			}
		foreach ( $cache as $key=>$value ) {
			foreach ( $cats as $cat ) {
				if ( is_numeric($cat) ) { $cat = (int) $cat; } 
				if ( ( $value->event_category == $cat || $category == 'all' || $value->category_name == $cat ) && ( $value->$type == $lvalue || ( $ltype == 'all' && $lvalue == 'all' ) ) ) {				
					$return[$key]=$value;
				} 
			}
		}
		return $return;
	}
}

function mc_create_cache($arr_events, $y, $m, $d ) {
	$caching = ( get_option('mc_caching_enabled') == 'true' )?true:false;
	if ( $arr_events == false ) { $arr_events = 'empty'; }
	if ( $caching == true ) {
		$before = memory_get_usage();
		$ret = get_transient("mc_cache");
		$after = memory_get_usage();
		$mem_limit = mc_allocated_memory( $before, $after );
		if ( $mem_limit ) { return false; } // if cache is maxed, don't add additional references. Cache expires every two days.
		$cache = get_transient("mc_cache");		
		$cache[$y][$m][$d] = $arr_events;
		set_transient( "mc_cache",$cache, 60*60*48 );
		return true;
	}
	return false;
}

function mc_allocated_memory($before, $after) {
    $size = ($after - $before);
	$total_allocation = str_replace('M','',ini_get('memory_limit'))*1048576; // CONVERT TO BYTES
	$limit =  $total_allocation/64;
	// limits each cache to occupying 1/64 of allowed PHP memory (usually will be between 125K and 1MB). 
	if ( $size > $limit ) { return true; } else { return false; }
}

function mc_delete_cache() {
	delete_transient( 'mc_cache' );
	delete_transient( 'mc_todays_cache' );
	delete_transient( 'mc_cache_upcoming' );
}

function _mc_increment_values( $recur ) {
	switch ($recur) {
		case "S": // single
			return 0;
		break;
		case "D": // daily
			return 999;
		break;
		case "E": // weekdays
			return 700;
		break;
		case "W": // weekly
			return 500;
		break;
		case "B": // biweekly
			return 250;
		break;
		case "M": // monthly
		case "U":
			return 120;
		break;
		case "Y":
			return 10;
		break;
		default: false;
	}
}

/* 
@param: an event object
@return: array: an integer number of past occurrences and the next date occurring OR a new event object
*/
function mc_increment_event( $event, $instance='', $object=true ) {
	$this_event_start = strtotime("$event->event_begin $event->event_time");
	$this_event_end = strtotime("$event->event_end $event->event_endtime");
	$event->event_start_ts = $this_event_start;
	$event->event_end_ts = $this_event_end;
	$fifth_week = $event->event_fifth_week;
	$holiday = $event->event_holiday;
	$arr_events = array();
	if ($event->event_recur != "S") {
		$orig_begin = $event->event_begin;
		$orig_end = $event->event_end;
		$numback = 0;
		// if we're splitting events, I'll take the performance hit to save coding; for the public site, keep it fast.
		if ( $object == true ) {
			$event_repetition = (int) $event->event_repeats;
		} else {
			$event_repetition = ( $event->event_repeats != 0)?$event->event_repeats:_mc_increment_values( $event->event_recur );
		}
		$numforward = $event_repetition;
		if ($event_repetition !== 0) {
			switch ($event->event_recur) {
				case "D":
				case "E":
					for ($i=$numback;$i<=$numforward;$i++) {
						$begin = my_calendar_add_date($orig_begin,$i,0,0);
						$end = my_calendar_add_date($orig_end,$i,0,0);		
						${$i} = clone($event);
						${$i}->event_begin = $begin;
						${$i}->event_end = $end;
						$this_event_start = strtotime("$begin $event->event_time");
						$this_event_end = strtotime("$end $event->event_endtime");
						${$i}->event_start_ts = $this_event_start;
						${$i}->event_end_ts = $this_event_end;
						if ( $event->event_recur == 'E' && ( date('w',$this_event_start ) != 0 && date('w',$this_event_start ) != 6 ) || $event->event_recur == 'D' ) {
							$arr_events[]=${$i};
						}
						if ( strtotime( $begin ) > $instance && $object == false ) {
							$data = array( $i-2, my_calendar_add_date( $orig_begin,$i,0,0 ) );
							return $data;
						} else {
							$data = array( $i-1, false );
						}
					}
					break;
				case "W":
					for ($i=$numback;$i<=$numforward;$i++) {
						$begin = my_calendar_add_date($orig_begin,($i*7),0,0);
						$end = my_calendar_add_date($orig_end,($i*7),0,0);
						${$i} = clone($event);
						${$i}->event_begin = $begin;
						${$i}->event_end = $end;
						$this_event_start = strtotime("$begin $event->event_time");
						$this_event_end = strtotime("$end $event->event_endtime");
						${$i}->event_start_ts = $this_event_start;
						${$i}->event_end_ts = $this_event_end;	
						$arr_events[]=${$i};
						if ( strtotime( $begin ) > $instance && $object == false ) {
							$data = array( $i-2, my_calendar_add_date( $orig_begin,($i*7),0,0 ) );
							return $data;
						} else {
							$data = array( $i-1, false );
						}					
					}
					break;
				case "B":
					for ($i=$numback;$i<=$numforward;$i++) {
						$begin = my_calendar_add_date($orig_begin,($i*14),0,0);
						$end = my_calendar_add_date($orig_end,($i*14),0,0);
						${$i} = clone($event);
						${$i}->event_begin = $begin;
						${$i}->event_end = $end;
						$this_event_start = strtotime("$begin $event->event_time");
						$this_event_end = strtotime("$end $event->event_endtime");
						${$i}->event_start_ts = $this_event_start;
						${$i}->event_end_ts = $this_event_end;								
						$arr_events[]=${$i};
						if ( strtotime( $begin ) > $instance && $object == false ) {
							$data = array( $i-2, my_calendar_add_date( $orig_begin,($i*14),0,0 ) );
							return $data;
						} else {
							$data = array( $i-1, false );
						}					
					}
					
					break;							
				case "M":
					for ($i=$numback;$i<=$numforward;$i++) {
						$begin = my_calendar_add_date($orig_begin,0,$i,0);
						$end = my_calendar_add_date($orig_end,0,$i,0);
						${$i} = clone($event);
						${$i}->event_begin = $begin;
						${$i}->event_end = $end;
						$this_event_start = strtotime("$begin $event->event_time");
						$this_event_end = strtotime("$end $event->event_endtime");
						${$i}->event_start_ts = $this_event_start;
						${$i}->event_end_ts = $this_event_end;	
						$arr_events[]=${$i};
						if ( strtotime( $begin ) > $instance && $object == false ) {
							$data = array( $i-2, my_calendar_add_date( $orig_begin,0,$i,0 ) );
							return $data;
						} else {
							$data = array( $i-1, false );
						}					
					}
					break;
				case "U":
					for ($i=$numback;$i<=$numforward;$i++) {
						$approxbegin = my_calendar_add_date($orig_begin,0,$i,0);
						$approxend = my_calendar_add_date($orig_end,0,$i,0);
						$day_diff = jd_date_diff($approxbegin, $approxend);						
						$day_of_event = date('D',strtotime($event->event_begin) );
						$week_of_event = week_of_month( date('d',strtotime($event->event_begin) ) );
						for ($n=-6;$n<=6;$n++) {								
							$timestamp = strtotime(my_calendar_add_date($approxbegin,$n,0,0));
							$current_day = date('D',$timestamp);
							if ($current_day == $day_of_event) {
							$current_week = week_of_month( date( 'd',$timestamp));
							$current_date = date( 'd',$timestamp);
								if ($current_day == $day_of_event && $current_week == $week_of_event) {
									$date_of_event_this_month = $current_date;
								} else {
									$first = $week_of_event*7;
									$last = $first+7;
									for ($s=$first;$s<=$last;$s++) {
										if ( $s > date('t',$timestamp) ) {
											$day = $s-date('t',$timestamp);
											$month = (date('m',$timestamp) == 12)?1:date('m',$timestamp)+1;
										} else {
											$day = $s;
											$month = date( 'm', $timestamp);
										}
										$string = date( 'Y', $timestamp ).'-'.$month.'-'.$day;
										$week = week_of_month($s);
											if ( date('D',strtotime($string)) == $day_of_event && $week == $week_of_event ) {
												$date_of_event_this_month = $s;	
												break;
											} 
									}
									if ( $fifth_week == 1 && $date_of_event_this_month > date('t',$timestamp) ) {
										$first = $first;
										$last = $first-7;
										for ($s=$last;$s<=$first;$s++) {
											$string = date( 'Y', $timestamp ).'-'.date('m', $timestamp).'-'.$s;
											if ( date('D',strtotime($string)) == $day_of_event ) {
												$date_of_event_this_month = $s;
												break;
											}
										}
									}
								}
								if ( ($current_day == $day_of_event && $current_week == $week_of_event) || ($current_date >= $date_of_event_this_month && $current_date <= $date_of_event_this_month+$day_diff && $date_of_event_this_month != '' ) ) {
									$begin = my_calendar_add_date($approxbegin,$n,0,0);
									$end = my_calendar_add_date($approxend,$n,0,0);
									${$i} = clone($event);
									${$i}->event_begin = $begin;
									${$i}->event_end = $end;
									$this_event_start = strtotime("$begin $event->event_time");
									$this_event_end = strtotime("$end $event->event_endtime");
									${$i}->event_start_ts = $this_event_start;
									${$i}->event_end_ts = $this_event_end;												
									$arr_events[]=${$i};
									if ( strtotime( $begin ) > $instance && $object == false ) {
										$data = array( $i-2, my_calendar_add_date( $approxbegin,$n,0,0 ) );
										return $data;
									} else {
										$data = array( $i-1, false );
									}								
								}
							}
						} 
					}
				break;
				case "Y":
					for ($i=$numback;$i<=$numforward;$i++) {
						$begin = my_calendar_add_date($orig_begin,0,0,$i);
						$end = my_calendar_add_date($orig_end,0,0,$i);
						${$i} = clone($event);
						${$i}->event_begin = $begin;
						${$i}->event_end = $end;
						$this_event_start = strtotime("$begin $event->event_time");
						$this_event_end = strtotime("$end $event->event_endtime");
						${$i}->event_start_ts = $this_event_start;
						${$i}->event_end_ts = $this_event_end;									
						$arr_events[]=${$i};
						if ( strtotime( $begin ) > $instance && $object == false ) {
							$data = array( $i-2, my_calendar_add_date( $orig_begin,0,0,$i ) );
							return $data;
						} else {
							$data = array( $i-1, false );
						}
					}
				break;
			}
		} else { // I really need to decide about getting rid of infinite events.
			$event_begin = $event->event_begin;
			$event_end = $event->event_end;
			$offset = (60*60*get_option('gmt_offset'));			
			$today = date('Y',time()+($offset)).'-'.date('m',time()+($offset)).'-'.date('d',time()+($offset));
			
			switch ($event->event_recur) {
				case "D":
				case "E":
					$nDays = 30;
					$fDays = 30;
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_days = $diff/(86400);
							$days = explode(".",$diff_days);
							$realStart = $days[0] - $nDays;
							$realFinish = $days[0] + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
							$this_date = my_calendar_add_date($event_begin,($realStart),0,0);
							$this_end = my_calendar_add_date($event_end,($realStart),0,0);									
								if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realStart} = clone($event);
									${$realStart}->event_begin = $this_date;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_start_ts = $this_event_start;
									${$realStart}->event_end_ts = $this_event_end;
									if ( $event->event_recur == 'E' && ( date('w',$this_event_start ) != 0 && date('w',$this_event_start ) != 6 ) || $event->event_recur == 'D' ) {
										$arr_events[] = ${$realStart};
									}							
								}
							}
						} else {
					$realDays = -($nDays);
						for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
						$this_date = my_calendar_add_date($event_begin,$realDays,0,0);
						$this_end = my_calendar_add_date($event_end,$realDays,0,0);
							if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
								${$realDays} = clone($event);
								${$realDays}->event_begin = $this_date;
								${$realDays}->event_end = $this_end;
								$this_event_start = strtotime("$this_date $event->event_time");
								$this_event_end = strtotime("$this_end $event->event_endtime");
								${$realDays}->event_start_ts = $this_event_start;
								${$realDays}->event_end_ts = $this_event_end;											
								$arr_events[] = ${$realDays};
							}
						}
					}
				break;
				case "W":
					$nDays = 6;
					$fDays = 6;
		
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays*7),0,0) )	) {							
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_weeks = $diff/(86400*7);
							$weeks = explode(".",$diff_weeks);
							$realStart = $weeks[0] - $nDays;
							$realFinish = $weeks[0] + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
							$this_date = my_calendar_add_date($event_begin,($realStart*7),0,0);
							$this_end = my_calendar_add_date($event_end,($realStart*7),0,0);
							if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realStart} = clone($event);
									${$realStart}->event_begin = $this_date;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_end = date('Y-m-d',$this_event_end);
									${$realStart}->event_start_ts = $this_event_start;
									${$realStart}->event_end_ts = $this_event_end;											
									$arr_events[] = ${$realStart};
								}
							}
						
						} else {
						$realDays = -($nDays);
						for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
						$this_date = my_calendar_add_date($event_begin,($realDays*7),0,0);
						$this_end = my_calendar_add_date($event_end,($realDays*7),0,0);								
							if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
								${$realDays} = clone($event);
								${$realDays}->event_begin = $this_date;
								${$realDays}->event_end = $this_end;
								$this_event_start = strtotime("$this_date $event->event_time");
								$this_event_end = strtotime("$this_end $event->event_endtime");
								${$realStart}->event_end = date('Y-m-d',$this_event_end);								
								${$realDays}->event_start_ts = $this_event_start;
								${$realDays}->event_end_ts = $this_event_end;											
								$arr_events[] = ${$realDays};
							}
						}
						}
				break;
				case "B":
					$nDays = 6;
					$fDays = 6;
					
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays*14),0,0) )) {
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_weeks = $diff/(86400*14);
							$weeks = explode(".",$diff_weeks);
							$realStart = $weeks[0] - $nDays;
							$realFinish = $weeks[0] + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
							$this_date = my_calendar_add_date($event_begin,($realStart*14),0,0);
							$this_end = my_calendar_add_date($event_end, ($realStart*14),0,0);
								if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realStart} = clone($event);
									${$realStart}->event_begin = $this_date;
									${$realStart}->event_end = $this_end;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_end = date('Y-m-d',$this_event_end);									
									${$realStart}->event_start_ts = $this_event_start;
									${$realStart}->event_end_ts = $this_event_end;												
									$arr_events[] = ${$realStart};
								}
							}
						
						} else {
						$realDays = -($nDays);
							for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
							$this_date = my_calendar_add_date($event_begin,($realDays*14),0,0);
							$this_end = my_calendar_add_date($event_end,($realDays*14),0,0);
								if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realDays} = clone($event);
									${$realDays}->event_begin = $this_date;
									${$realDays}->event_end = $this_end;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_end = date('Y-m-d',$this_event_end);									
									${$realDays}->event_start_ts = $this_event_start;
									${$realDays}->event_end_ts = $this_event_end;												
									$arr_events[] = ${$realDays};
								}
							}
						}
				break;
				
				case "M":
					$nDays = 5;
					$fDays = 5;
					
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_days = $diff/(86400*30);
							$days = explode(".",$diff_days);
							$realStart = $days[0] - $nDays;
							$realFinish = $days[0] + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
							$this_date = my_calendar_add_date($event_begin,0,$realStart,0);
							$this_end = my_calendar_add_date($event_end,0,$realStart,0);
							
								if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realStart} = clone($event);
									${$realStart}->event_begin = $this_date;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_end = date('Y-m-d',$this_event_end);									
									${$realStart}->event_start_ts = $this_event_start;
									${$realStart}->event_end_ts = $this_event_end;												
									$arr_events[] = ${$realStart};
								}
							}								
						
						} else {							
						$realDays = -($nDays);
						for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
						$this_date = my_calendar_add_date($event_begin,0,$realDays,0);
						$this_end = my_calendar_add_date($event_end,0,$realDays,0);								
							if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
								${$realDays} = clone($event);
								${$realDays}->event_begin = $this_date;
								${$realDays}->event_end = $this_end;
								$this_event_start = strtotime("$this_date $event->event_time");
								$this_event_end = strtotime("$this_end $event->event_endtime");
								${$realDays}->event_end = date('Y-m-d',$this_event_end);								
								${$realDays}->event_start_ts = $this_event_start;
								${$realDays}->event_end_ts = $this_event_end;											
								$arr_events[] = ${$realDays};
							}
						}
						}
				break;
				// "U" is month by day
				case "U":
					$nDays = 5;
					$fDays = 5;
					$day_of_event = date( 'D', strtotime($event->event_begin) );
					$week_of_event = week_of_month( date( 'j', strtotime($event->event_begin) ) );
					$day_diff = jd_date_diff($event_begin, $event_end);
					
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
							// this doesn't need to be precise; it only effects what dates will be checked.
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_days = floor($diff/(86400*30));
							$realStart = $diff_days - $nDays;
							$realFinish = $diff_days + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
								$approxbegin = my_calendar_add_date($event_begin,0,$realStart,0);
								$approxend = my_calendar_add_date($event_end,0,$realStart,0);
								for ($n=-6;$n<=6;$n++) {
									$timestamp = strtotime(my_calendar_add_date($approxbegin,$n,0,0));
									$current_day = date('D',$timestamp);
									if ($current_day == $day_of_event) {
										$current_week = week_of_month( date( 'j',$timestamp));
										$current_date = date( 'd',$timestamp);
										if ($current_day == $day_of_event && $current_week == $week_of_event) {
											$date_of_event_this_month = $current_date;
										} else {
											$first = ($week_of_event*7);
											$last = $first+7;
											for ($i=$first;$i<=$last;$i++) {
												if ( $i > date('t',$timestamp) ) {
													$day = $i-date('t',$timestamp);
													$month = (date('m',$timestamp) == 12)?1:date('m',$timestamp)+1;
												} else {
													$day = $i;
													$month = date( 'm', $timestamp);
												}
												$string = date( 'Y', $timestamp ).'-'.$month.'-'.$day;
												if ( date('D',strtotime($string)) == $day_of_event && $week_of_event == week_of_month( $i ) ) {
													$date_of_event_this_month = $i;										
													break;
												}											
											}
											if ( $fifth_week == 1 && $week_of_event == 4 ) {
												$last = $first;													
												$first = $first-7;
												for ($i=$first;$i<=$last;$i++) {
													$string = date( 'Y', $timestamp ).'-'.date('m', $timestamp).'-'.$i;
													if ( date('D',strtotime($string)) == $day_of_event ) {
														$date_of_event_this_month = $i;
														break;
													}
												}
											}
										}
										if ( ($current_day == $day_of_event && $current_week == $week_of_event) || ($current_date >= $date_of_event_this_month && $current_date <= $date_of_event_this_month+$day_diff && $date_of_event_this_month != '' ) ) {									
											$begin = my_calendar_add_date($approxbegin,$n,0,0);
											$end = my_calendar_add_date($approxend,$n,0,0);
											${$realStart} = clone($event);
											${$realStart}->event_begin = $begin;
											${$realStart}->event_end = $end;
											$this_event_start = strtotime("$begin $event->event_time");
											$this_event_end = strtotime("$end $event->event_endtime");
											${$realStart}->event_end = date('Y-m-d',$this_event_end);											
											${$realStart}->event_start_ts = $this_event_start;
											${$realStart}->event_end_ts = $this_event_end;
												$arr_events[]=${$realStart};	
											break;
										}
									}
								}
							}									
						
						} else {							
						$realDays = -($nDays);
						for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
								$approxbegin = my_calendar_add_date($event_begin,0,$realDays,0);
								$approxend = my_calendar_add_date($event_end,0,$realDays,0);
								if ( ! my_calendar_date_xcomp($approxbegin,$event_begin) ) { // if approx is before real start, skip
									for ($n=-6;$n<=6;$n++) {
										$timestamp = strtotime(my_calendar_add_date($approxbegin,$n,0,0));										
										$current_day = date('D',$timestamp);
										if ($current_day == $day_of_event) {
											$current_week = week_of_month( date( 'd',$timestamp));
											$current_date = date( 'd',$timestamp);
											$first = ($week_of_event*7);
											$last = $first+7;
											for ($i=$first;$i<=$last;$i++) {
												if ( $i > date('t',$timestamp) ) {
													$day = $i-date('t',$timestamp);
													$month = (date('m',$timestamp) == 12)?1:date('m',$timestamp)+1;
												} else {
													$day = $i;
													$month = date( 'm', $timestamp);
												}
												$string = date( 'Y', $timestamp ).'-'.$month.'-'.$day;
												if ( date('D',strtotime($string)) == $day_of_event ) {
													$date_of_event_this_month = $i;
													break;
												}											
											}
											if ( $fifth_week == 1 && $date_of_event_this_month > date('t',$timestamp) ) {
												$last = $first;
												$first = $first-7;
												for ($i=$first;$i<=$last;$i++) {
													$string = date( 'Y', $timestamp ).'-'.date('m', $timestamp).'-'.$i;
													if ( date('D',strtotime($string)) == $day_of_event ) {
														$date_of_event_this_month = $i;
														break;
													}
												}					
											}											
											if ( ($current_day == $day_of_event && $current_week == $week_of_event) || ($current_date >= $date_of_event_this_month && $current_date <= $date_of_event_this_month+$day_diff && $date_of_event_this_month != '' ) ) {											
												$begin = my_calendar_add_date($approxbegin,$n,0,0);
												$end = my_calendar_add_date($approxend,$n,0,0);
												${$realDays} = clone($event);
												${$realDays}->event_begin = $begin;
												${$realDays}->event_end = $end;	
												$this_event_start = strtotime("$begin $event->event_time");
												$this_event_end = strtotime("$end $event->event_endtime");
												${$realDays}->event_end = date('Y-m-d',$this_event_end);			
												${$realDays}->event_start_ts = $this_event_start;
												${$realDays}->event_end_ts = $this_event_end;												
												$arr_events[]=${$realDays};
												break;
											} 
										}
									}
								}
							}
						}
				break;
				case "Y":
					$nDays = 3;
					$fDays = 3;
						if (my_calendar_date_comp( $event_begin, my_calendar_add_date($today,-($nDays),0,0) )) {
							$diff = jd_date_diff_precise(strtotime($event_begin));
							$diff_days = $diff/(86400*365);
							$days = explode(".",$diff_days);
							$realStart = $days[0] - $nDays;
							$realFinish = $days[0] + $fDays;

							for ($realStart;$realStart<=$realFinish;$realStart++) { // jump forward to near present.
							$this_date = my_calendar_add_date($event_begin,0,0,$realStart);
							$this_end = my_calendar_add_date($event_end,0,0,$realStart);									
								if ( my_calendar_date_comp( $event->event_begin,$this_date ) ) {
									${$realStart} = clone($event);
									${$realStart}->event_begin = $this_date;
									$this_event_start = strtotime("$this_date $event->event_time");
									$this_event_end = strtotime("$this_end $event->event_endtime");
									${$realStart}->event_start_ts = $this_event_start;
									${$realStart}->event_end_ts = $this_event_end;												
									$arr_events[] = ${$realStart};
								}
							}								
						} else {							
						$realDays = -($nDays);
						for ($realDays;$realDays<=$fDays;$realDays++) { // for each event within plus or minus range, mod date and add to array.
						$this_date = my_calendar_add_date($event_begin,0,0,$realDays);
						$this_end = my_calendar_add_date($event_end,0,0,$realDays);
							if ( my_calendar_date_comp( $event->event_begin,$this_date ) == true ) {
								${$realDays} = clone($event);
								${$realDays}->event_begin = $this_date;
								${$realDays}->event_end = $this_end;
								$this_event_start = strtotime("$this_date $event->event_time");
								$this_event_end = strtotime("$this_end $event->event_endtime");
								${$realStart}->event_start_ts = $this_event_start;
								${$realStart}->event_end_ts = $this_event_end;											
								$arr_events[] = ${$realDays};
							}
						}
						}
				break;
			}
		}
	} else {
		$arr_events[]=$event;
	}
	if ( $object == false ) {
		return $data;
	} else {
		return $arr_events;
	}
}