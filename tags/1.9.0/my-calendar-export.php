<?php

// Export single event as iCal file
function my_calendar_export_vcal() {
	if (isset($_GET['vcal'])) {
		$vcal = $_GET['vcal'];
		print my_calendar_send_vcal( $vcal );
		die;
	}
}

function my_calendar_send_vcal( $event_id ) {
	header("Content-Type: text/calendar");
	header("Cache-control: private");
	header('Pragma: private');
	header("Expires: Thu, 11 Nov 1977 05:40:00 GMT");
	header("Content-Disposition: inline; filename=my-calendar.ics");
	$output = preg_replace( "~(?<!\r)\n~","\r\n", my_calendar_generate_vcal( $event_id ) );
	return urldecode( stripcslashes( $output ) );
}

function my_calendar_generate_vcal( $event_id ) {
	global $mc_version;
	$mc_id = explode("_",$event_id);
	$id = (int) $mc_id[2];
	$date = $mc_id[1];
	$event = my_calendar_get_event( $date, $id, 'object' );
	$array = event_as_array($event, 'ical' );
$template = "BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
X-WR-CALNAME: ". get_bloginfo('name') ." Calendar
PRODID:-//Accessible Web Design//My Calendar//http://www.mywpcal.com//v$mc_version//EN';
BEGIN:VEVENT
UID:{dateid}-{id}
LOCATION:{ical_location}
SUMMARY:{title}
DTSTAMP:{ical_start}
ORGANIZER;CN={host}:MAILTO:{host_email}
DTSTART:{ical_start}
DTEND:{ical_end}
URL;VALUE=URI:{link}
DESCRIPTION;ENCODING=QUOTED-PRINTABLE:{ical_desc}
END:VEVENT
END:VCALENDAR";
	$output = jd_draw_template($array, $template);
	return $output;
}