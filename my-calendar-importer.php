<?php

/*  use SplFileObject to parse CSV 
	$file = 'data.csv';
	try {
		$csv  = new SplFileObject($file, 'r');
	} catch ( RuntimeException $e ) {
		printf( "Error opening csv: %s\n", $e->getMessage() );
	}
	 
	while( !$csv->eof() && ( $row = $csv->fgetcsv() ) && $row[0] !== null ) {
		// $row is a numerical keyed array  with
		// a string per field (zero based).
	}
*/

// Display the admin configuration page
function my_calendar_import( $importer='ko_calendar' ) {
	global $wpdb;
	$mcdb = $wpdb;
	$ko_format = array(
		'has_cats'=>1,
		'event'=>array(
		'title'=>'event_title',
		'desc'=>'event_desc',
		'begin'=>'event_begin',
		'end'=>'event_end',
		'time'=>'event_time',
		'recur'=>'event_recur',
		'repeats'=>'event_repeats',
		'author'=>'event_author',
		'category'=>'event_category',
		'link'=>'event_link',
		'table'=>'calendar'),
		'cats'=>array( 'table'=>'calendar_categories','name'=>'category_name','color'=>'category_colour','id'=>'category_id' )
	);
	$ev_format = array(
		'has_cats'=>0,
		'event'=>array(
		'title'=>'eventTitle',
		'desc'=>'eventDesc',
		'begin'=>'eventStartDate',
		'end'=>'eventEndDate',
		'time'=>'eventStartTime',
		'endtime'=>'eventEndTime',
		'author'=>'postID',
		'link'=>'eventLinkout',
		'table'=>'eventscalendar_main')
	);
	switch ($importer) {
		case 'ko_calendar':$format = $ko_format;break;
		case 'events_calendar':$format = $ev_format;break;
	}
	if ( get_option( $importer.'_imported' ) != 'true' ) {
		define('IMPORT_CALENDAR_TABLE', $mcdb->prefix . $format['table'] );
		if ( $format['has_cats'] == 1 ) {
			define('IMPORT_CALENDAR_CATS', $mcdb->prefix . $format['cats']['table'] );
		}
		$events = $mcdb->get_results("SELECT * FROM " . IMPORT_CALENDAR_TABLE, 'ARRAY_A');
		$sql = "";
		foreach ($events as $key) {
			foreach( $format as $k=>$v ) {
				${$k} = mysql_real_escape_string( $key[$v[$k]] );
			}
			// figure this out later
			$title = mysql_real_escape_string($key[$format['title']]);
			$desc = mysql_real_escape_string($key[$format['desc']]);
			$begin = mysql_real_escape_string($key[$format['begin']]);
			$end = mysql_real_escape_string($key[$format['end']]);
			$time = mysql_real_escape_string($key[$format['time']]);
			$recur = mysql_real_escape_string($key[$format['recur']]);
			$repeats = mysql_real_escape_string($key[$format['repeats']]);
			$author = mysql_real_escape_string($key[$format['author']]);
			$category = mysql_real_escape_string($key[$format['category']]);
			$link = mysql_real_escape_string($key[$format['link']]);
		    $sql = "INSERT INTO " . my_calendar_table() . " SET 
			event_title='" . ($title) . "', 
			event_desc='" . ($desc) . "', 
			event_begin='" . ($begin) . "', 
			event_end='" . ($end) . "', 
			event_time='" . ($time) . "', 
			event_recur='" . ($recur) . "', 
			event_repeats='" . ($repeats) . "', 
			event_author=".($author).", 
			event_category=".($category).", 
			event_link='".($link)."';
			";
			$events_results = $mcdb->query($sql);		
		}	
		if ( $format['has_cats'] == 1 ) {
			$cats = $mcdb->get_results("SELECT * FROM " . KO_CALENDAR_CATS, 'ARRAY_A');	
			$catsql = "";
			foreach ($cats as $key) {
				$name = mysql_real_escape_string($key['category_name']);
				$color = mysql_real_escape_string($key['category_colour']);
				$id = mysql_real_escape_string($key['category_id']);
				$catsql = "INSERT INTO " . my_calendar_categories_table() . " SET 
					category_id='".$id."',
					category_name='".$name."', 
					category_color='".$color."' 
					ON DUPLICATE KEY UPDATE 
					category_name='".$name."', 
					category_color='".$color."';
					";	
				$cats_results = $mcdb->query($catsql);
				//$mcdb->print_error(); 			
			}			
			$message = ( $cats_results !== false )?__('Categories imported successfully.','my-calendar'):__('Categories not imported.','my-calendar');
		}
		$e_message = ( $events_results !== false )?__('Events imported successfully.','my-calendar'):__('Events not imported.','my-calendar');
		$return = "<div id='message' class='updated fade'><ul><li>$message</li><li>$e_message</li></ul></div>";
		echo $return;
		if ( $cats_results !== false && $events_results !== false ) {
			update_option( $importer.'_imported','true' );
		}
	} 
}