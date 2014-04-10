<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Export single event as iCal file
function mc_export_vcal() {
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
		$mc_id = (int) str_replace( 'mc_','',$_GET['vcal']);
		$event = mc_get_event( $mc_id,'object' );
		// need to modify date values to match real values using date above
	$array = mc_create_tags( $event );

$template = "BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:-//Accessible Web Design//My Calendar//http://www.joedolson.com//v$mc_version//EN';
BEGIN:VEVENT
UID:{dateid}-{id}
LOCATION:{ical_location}
SUMMARY:{title}
DTSTAMP:{ical_start}
ORGANIZER;CN={host}:MAILTO:{host_email}
DTSTART:{ical_start}
DTEND:{ical_end}
CATEGORIES:{category}
URL;VALUE=URI:{link}
DESCRIPTION;ENCODING=QUOTED-PRINTABLE:{ical_desc}
END:VEVENT
END:VCALENDAR";
	$template = apply_filters( 'mc_single_ical_template', $template, $array );
	$output = jd_draw_template($array, $template);
	return $output;
}

function my_calendar_rss( $events=array() ) {
	// establish template
	if ( isset($_GET['mcat']) ) { $cat_id = (int) $_GET['mcat']; } else { $cat_id = false; }
		$template = "\n<item>
		<title>{rss_title}</title>
		<link>{details_link}</link>
		<pubDate>{rssdate}</pubDate>
		<dc:creator>{author}</dc:creator>  	
		<description><![CDATA[{rss_description}]]></description>
		<content:encoded><![CDATA[<div class='vevent'>
		<h1 class='summary'>{rss_title}</h1>
		<div class='description'>{rss_description}</div>
		<p class='dtstart' title='{ical_start}'>Begins: {time} on {date}</p>
		<p class='dtend' title='{ical_end}'>Ends: {endtime} on {enddate}</p>	
		<p>Recurrance: {recurs}</p>
		<p>Repetition: {repeats} times</p>
		<div class='location'>{rss_hcard}</div>
		{rss_link_title}
		</div>]]></content:encoded>
		<dc:format xmlns:dc='http://purl.org/dc/elements/1.1/'>text/html</dc:format>
		<dc:source xmlns:dc='http://purl.org/dc/elements/1.1/'>".home_url()."</dc:source>	
		{guid}
	  </item>\n";
	if ( get_option( 'mc_use_rss_template' ) == 1 ) { $templates = get_option('mc_templates'); $template = $templates['rss']; }
	// add RSS headers
	$charset = get_bloginfo('charset');
	$output = '<?xml version="1.0" encoding="'.$charset.'"?>
	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:dc="http://purl.org/dc/elements/1.1/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
		>
	<channel>
	  <title>'. get_bloginfo('name') .' Calendar</title>
	  <link>'. home_url() .'</link>
	  <description>'. get_bloginfo('description') . ': My Calendar Events</description>
	  <language>'. get_bloginfo('language') .'</language>
	  <managingEditor>'. get_bloginfo('admin_email') .' (' . get_bloginfo('name') . ' Admin)</managingEditor>
	  <generator>My Calendar WordPress Plugin http://www.joedolson.com/articles/my-calendar/</generator>
	  <lastBuildDate>'. mysql2date('D, d M Y H:i:s +0000', current_time( 'timestamp' ) ) .'</lastBuildDate>
	  <atom:link href="'. mc_get_current_url() .'" rel="self" type="application/rss+xml" />';

		if ( empty( $events ) ) {
			$events = mc_get_rss_events( $cat_id );
		} 
		foreach ( $events as $date ) {
			foreach ( array_keys( $date ) as $key ) {
				$event =& $date[$key];
				$array = mc_create_tags( $event );
				$output .= jd_draw_template( $array, $template, 'rss' );
			}
		}
	$output .= '</channel>
	</rss>';
		header('Content-type: application/rss+xml');
		header("Pragma: no-cache");
		header("Expires: 0");
	echo mc_strip_to_xml($output);
}

// just a double check to try to ensure that the XML feed can be rendered.
function mc_strip_to_xml($value) {
    $ret = "";
    $current;
    if ( empty( $value ) ) {
        return $ret;
    }
    $length = strlen($value);
    for ($i=0; $i < $length; $i++) {
        $current = ord($value{$i});
        if (($current == 0x9) ||
            ($current == 0xA) ||
            ($current == 0xD) ||
            (($current >= 0x20) && ($current <= 0xD7FF)) ||
            (($current >= 0xE000) && ($current <= 0xFFFD)) ||
            (($current >= 0x10000) && ($current <= 0x10FFFF))) {
            $ret .= chr($current);
        } else {
            $ret .= " ";
        }
    }
    return $ret;
}