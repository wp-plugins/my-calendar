<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function my_calendar_api() {
	if ( isset( $_GET['my-calendar-api'] ) ) {
		if ( get_option( 'mc_api_enabled' ) == 'true' ) {
			$format = ( isset( $_GET['my-calendar-api'] ) ) ? $_GET['my-calendar-api'] : 'json';
			$from = ( isset( $_GET['from'] ) ) ? $_GET['from'] : date( 'Y-m-d', current_time( 'timestamp' ) );
			$to = ( isset( $_GET['to'] ) ) ? $_GET['to'] : date( 'Y-m-d', strtotime( current_time( 'timestamp' ).apply_filters('mc_api_auto_date',' + 7 days') ) );
			$category = ( isset( $_GET['mcat'] ) ) ? $_GET['mcat'] : '' ;
			$ltype = ( isset( $_GET['ltype'] ) ) ? $_GET['ltype'] : '' ;
			$lvalue = ( isset( $_GET['lvalue'] ) ) ? $_GET['lvalue'] : '' ;
			$author = ( isset( $_GET['author'] ) ) ? $_GET['author'] : '' ;
			$host = ( isset( $_GET['host'] ) ) ? $_GET['host'] : '' ;
			
			$data = my_calendar_events( $from, $to, $category, $ltype, $lvalue, 'api', $author, $host );
			$output = mc_format_api( $data, $format );
			// if json, encode as json
			// if xml, encode as xml
			echo $output;
			die;
		} else {
			_e( 'The My Calendar API is not enabled.','my-calendar' );
		}			
	}

}

function mc_format_api( $data, $format ) {
	$output = '';
	switch ( $format ) {
		case 'json' : $output = mc_format_json( $data ); break;
		case 'rss' : $output = mc_format_rss( $data ); break;
	}
	return $output;
}

function mc_format_json( $data ) {
	return json_encode( $data );
}

function mc_format_rss( $data ) {
	return my_calendar_rss( $data );
}