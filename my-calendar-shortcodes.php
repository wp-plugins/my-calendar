<?php


function my_calendar_insert($atts,$content=null) {
	extract(shortcode_atts(array(
				'name' => 'all',
				'format' => 'calendar',
				'category' => 'all',
				'showkey' => 'yes',
				'shownav' => 'yes',
				'showjump'=> '',
				'toggle' => 'no',
				'time' => 'month',
				'ltype' => '',
				'lvalue' => '',
				'id' => 'jd-calendar',
				'template' => ''
			), $atts));
	if ( $format != 'mini' ) {
		if ( isset($_GET['format']) ) {
			$format = mysql_real_escape_string($_GET['format']);
		}
	}
	//apply_filters( 'mc_filter_calendar_name',$all_styles,$styles );
	return my_calendar($name,$format,$category,$showkey,$shownav,$showjump,$toggle,$time, $ltype, $lvalue, $id, $template,$content );
}

function my_calendar_insert_upcoming($atts) {
	extract(shortcode_atts(array(
				'before' => 'default',
				'after' => 'default',
				'type' => 'default',
				'category' => 'default',
				'template' => 'default',
				'fallback' => '',
				'order' => 'asc',
				'skip' => '0',
			), $atts));
	return my_calendar_upcoming_events($before, $after, $type, $category, $template, $fallback, $order, $skip);
}

function my_calendar_insert_today($atts) {
	extract(shortcode_atts(array(
				'category' => 'default',
				'template' => 'default',
				'fallback' => ''
			), $atts));
	return my_calendar_todays_events($category, $template, $fallback);
}

function my_calendar_locations($atts) {
	extract(shortcode_atts(array(
				'show' => 'list',
				'type' => 'saved',
				'datatype' => 'name'
			), $atts));
	return my_calendar_locations_list($show,$type,$datatype);
}

function my_calendar_show_locations_list($atts) {
	extract(shortcode_atts(array(
				'show' => 'list',
				'datatype' => 'name'
			), $atts));
	return my_calendar_show_locations($show,$datatype);
}

function my_calendar_categories($atts) {
	extract(shortcode_atts(array(
				'show' => 'list'
			), $atts));
	return my_calendar_categories_list( $show );
}

?>