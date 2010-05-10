<?php
if ( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
exit();
} else {
delete_option('can_manage_events');
delete_option('my_calendar_style');
delete_option('display_author');
delete_option('display_jump');
delete_option('display_todays');
delete_option('display_upcoming');
delete_option('display_upcoming_days');
delete_option('my_calendar_version');
delete_option('display_upcoming_type');
delete_option('display_upcoming_events');
delete_option('display_past_days');
delete_option('display_past_events');
delete_option('my_calendar_use_styles');
delete_option('my_calendar_show_months');
delete_option('my_calendar_show_map');
delete_option('my_calendar_show_address');
delete_option('my_calendar_today_template');
delete_option('my_calendar_upcoming_template');
delete_option('my_calendar_today_title');
delete_option('my_calendar_upcoming_title');
// Widget options
delete_option('my_calendar_today_title');
delete_option('my_calendar_today_template');
delete_option('my_calendar_upcoming_title');
delete_option('my_calendar_upcoming_template');
delete_option('display_upcoming_type');
delete_option('display_upcoming_days');
delete_option('display_upcoming_events');
delete_option('display_past_events');
delete_option('display_past_days');
delete_option('ko_calendar_imported');

}
?>