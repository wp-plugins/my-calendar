<?php
if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
} else {
delete_option('mc_can_manage_events');
delete_option('mc_style');
delete_option('mc_display_author');
delete_option('mc_display_jump');
delete_option('mc_version');
delete_option('mc_use_styles');
delete_option('mc_show_months');
delete_option('mc_show_map');
delete_option('mc_show_address');
delete_option('mc_today_template');
delete_option('mc_upcoming_template');
delete_option('mc_today_title');
delete_option('ko_calendar_imported');
delete_option('mc_show_heading');
delete_option('mc_listjs');
delete_option('mc_caljs');
delete_option('mc_calendar_javascript');
delete_option('mc_list_javascript');
delete_option('mc_minijs');
delete_option('mc_mini_javascript');
delete_option('mc_notime_text');
delete_option('mc_hide_icons');
delete_option('mc_caption');
delete_option('mc_event_link_expires');
delete_option('mc_apply_color');
delete_option('mc_date_format');
delete_option('mc_no_events_text');
delete_option('mc_show_css');
delete_option('mc_apply_color');
delete_option('mc_next_events');
delete_option('mc_previous_events');
delete_option('mc_input_options');
delete_option('mc_input_options_administrators');
delete_option('mc_event_mail','false');
delete_option('mc_event_mail_subject');
delete_option('mc_event_mail_to');
delete_option('mc_event_mail_message');
delete_option('mc_event_approve');		
delete_option('mc_event_approve_perms');
delete_option('mc_no_fifth_week');		
delete_option('mc_user_settings');
delete_option('mc_ajaxjs' );
delete_option('mc_ajax_javascript' );
delete_option('mc_templates');
delete_option('mc_user_settings_enabled');
delete_option('mc_user_location_type'); 
delete_option('mc_show_js');
delete_option('mc_event_open');
delete_option('mc_event_closed');
delete_option('mc_event_registration');
delete_option('mc_short');
delete_option('mc_desc');
delete_option('mc_location_type');
delete_option('mc_skip_holidays_category');
delete_option('mc_skip_holidays');
delete_option('mc_event_edit_perms');
delete_option('mc_css_file');
delete_option('mc_db_version');
delete_option('mc_stored_styles');
delete_option('mc_show_rss');
delete_option('mc_show_ical');
delete_option('mc_show_weekends' );
delete_option('mc_uri' );
delete_option('mc_location_control' );
delete_option('mc_use_mini_template' );
delete_option('mc_use_list_template' );
delete_option('mc_calendar_location' );
delete_option('mc_use_grid_template' );
delete_option('mc_week_format' );
delete_option('mc_time_format' );
delete_option('mc_use_details_template' );
delete_option( 'mc_details' );
delete_option( 'mc_widget_defaults' );
delete_option( 'mc_default_sort' );
add_option( 'mc_uninstalled','true' );
}
?>