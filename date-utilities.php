<?php 

function my_calendar_add_date($givendate,$day=0,$mth=0,$yr=0) {
	$cd = strtotime($givendate);
	$newdate = date('Y-m-d', mktime(date('h',$cd),date('i',$cd), date('s',$cd), date('m',$cd)+$mth,date('d',$cd)+$day, date('Y',$cd)+$yr));
	return $newdate;
}
function my_calendar_date_comp($early,$late) {
	$firstdate = strtotime($early);
	$lastdate = strtotime($late);
	if ($firstdate <= $lastdate) {
		return true;
	} else {
		return false;
	}
}

function my_calendar_date_equal($early,$late) {
	$firstdate = strtotime($early);
	$lastdate = strtotime($late);
	if ($early == $late) {
		return true;
	} else {
		return false;
	}	
}

// Function to compare time in event objects
function my_calendar_time_cmp($a, $b) {
  if ($a->event_time == $b->event_time) {
    return 0;
  }
  return ($a->event_time < $b->event_time) ? -1 : 1;
}

// Function to compare datetime in event objects
function my_calendar_datetime_cmp($a, $b) {
	$event_dt_a = strtotime($a->event_begin .' '. $a->event_time);
	$event_dt_b = strtotime($b->event_begin .' '. $b->event_time);
  if ($event_dt_a == $event_dt_b ) {
    return 0;
  }
  return ( $event_dt_a < $event_dt_b ) ? -1 : 1;
}

// reverse Function to compare datetime in event objects
function my_calendar_reverse_datetime_cmp($b, $a) {
	$event_dt_a = strtotime($a->event_begin .' '. $a->event_time);
	$event_dt_b = strtotime($b->event_begin .' '. $b->event_time);
  if ($event_dt_a == $event_dt_b ) {
    return 0;
  }
  return ( $event_dt_a < $event_dt_b ) ? -1 : 1;
}

function my_calendar_timediff_cmp($a, $b) {
	$event_dt_a = strtotime($a->event_begin .' '. $a->event_time);
	$event_dt_b = strtotime($b->event_begin .' '. $b->event_time);
	$diff_a = jd_date_diff_precise($event_dt_a);
	$diff_b = jd_date_diff_precise($event_dt_b);
	
	if ( $diff_a == $diff_b ) {
		return 0;
	}
	return ( $diff_a < $diff_b ) ? -1 : 1;
}

function jd_date_diff_precise($start,$end="NOW") {
        if ($end == "NOW") {
			$end = strtotime("NOW");
		}
		$sdate = $start;
        $edate = $end;

        $time = $edate - $sdate;
		
		return abs($time);
}

function jd_date_diff($start, $end="NOW") {
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $time = $edate - $sdate;		
		if ($time < 86400 && $time > -86400) {
			return false;
		} else {
            $pday = ($edate - $sdate) / 86400;
            $preday = explode('.',$pday);		
			return $preday[0];
		}
}

function week_of_month($date_of_event) {
					switch ($date_of_event) {
						case ($date_of_event>=1 && $date_of_event <8):
						$week_of_event = 0;
						break;
						case ($date_of_event>=8 && $date_of_event <15):
						$week_of_event = 1;
						break;
						case ($date_of_event>=15 && $date_of_event <22):
						$week_of_event = 2;
						break;
						case ($date_of_event>=22 && $date_of_event <29):
						$week_of_event = 3;
						break;		
						case ($date_of_event>=29):
						$week_of_event = 4;
						break;
					}
					return $week_of_event;
}
?>