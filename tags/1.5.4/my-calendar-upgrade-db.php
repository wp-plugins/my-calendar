<?php
function my_calendar_check_db() {
global $wpdb;
$row = $wpdb->get_row( 'SELECT * FROM '.MY_CALENDAR_TABLE );

if ( $_POST['upgrade'] == 'true' ) {
	my_calendar_upgrade_db();
}

	if ( !isset( $row->event_approved ) && isset( $row->event_id ) ) {
	?>

	<div class='upgrade-db error'>
		<form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
		<div>
			<input type="hidden" name="upgrade" value="true" />
		</div>
		<p>
		<?php _e('The My Calendar database needs to be updated.','my-calendar'); ?>
		<input type="submit" value="<?php _e('Update now','my-calendar'); ?>" name="update-calendar" class="button-primary" />
		</p>
		</form>
	</div>
<?php
	} elseif ( !isset ( $row->event_id ) ) {
?>
	<div class='upgrade-db error'>
		<form method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-config">
		<div>
			<input type="hidden" name="upgrade" value="true" />
		</div>
		<p>
		<?php _e('You haven\'t entered any events, so My Calendar can\'t tell whether your database is up to date. If you can\'t add events, upgrade your database!','my-calendar'); ?>
		<input type="submit" value="<?php _e('Update now','my-calendar'); ?>" name="update-calendar" class="button-primary" />
		</p>
		</form>
	</div>
<?php
	} else {
		if ( isset($_POST) && $_POST['upgrade'] == 'true' ) {
		?>
		<div class='upgrade-db updated'>
		<p>
		<?php _e('My Calendar Database is updated.','my-calendar'); ?>
		</p>
		</div>
<?php
		}
	}
}



function my_calendar_upgrade_db() {

$initial_db = "CREATE TABLE " . MY_CALENDAR_TABLE . " ( 
 event_id INT(11) NOT NULL AUTO_INCREMENT,
 event_begin DATE NOT NULL,
 event_end DATE NOT NULL,
 event_title VARCHAR(255) NOT NULL,
 event_desc TEXT NOT NULL,
 event_time TIME,
 event_endtime TIME,
 event_recur CHAR(1),
 event_repeats INT(3),
 event_status INT(1) NOT NULL DEFAULT '1',
 event_group INT(1) NOT NULL DEFAULT '0',
 event_author BIGINT(20) UNSIGNED,
 event_category BIGINT(20) UNSIGNED,
 event_link TEXT,
 event_link_expires TINYINT(1) NOT NULL,
 event_label VARCHAR(60) NOT NULL,
 event_street VARCHAR(60) NOT NULL,
 event_street2 VARCHAR(60) NOT NULL,
 event_city VARCHAR(60) NOT NULL,
 event_state VARCHAR(60) NOT NULL,
 event_postcode VARCHAR(10) NOT NULL,
 event_country VARCHAR(60) NOT NULL,
 event_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 event_zoom INT(2) NOT NULL DEFAULT '14',
 event_group INT(1) NOT NULL DEFAULT '0',
 event_approved INT(1) NOT NULL DEFAULT '1',
 PRIMARY KEY  (event_id),
 KEY event_recur (event_recur)
 );";

$initial_cat_db = "CREATE TABLE " . MY_CALENDAR_CATEGORIES_TABLE . " ( 
 category_id INT(11) NOT NULL AUTO_INCREMENT, 
 category_name VARCHAR(255) NOT NULL, 
 category_color VARCHAR(7) NOT NULL, 
 category_icon VARCHAR(128) NOT NULL,
 PRIMARY KEY  (category_id) 
 );";
 
$initial_loc_db = "CREATE TABLE " . MY_CALENDAR_LOCATIONS_TABLE . " ( 
 location_id INT(11) NOT NULL AUTO_INCREMENT, 
 location_label VARCHAR(60) NOT NULL,
 location_street VARCHAR(60) NOT NULL,
 location_street2 VARCHAR(60) NOT NULL,
 location_city VARCHAR(60) NOT NULL,
 location_state VARCHAR(60) NOT NULL,
 location_postcode VARCHAR(10) NOT NULL,
 location_country VARCHAR(60) NOT NULL,
 location_longitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_latitude FLOAT(10,6) NOT NULL DEFAULT '0',
 location_zoom INT(2) NOT NULL DEFAULT '14',
 PRIMARY KEY  (location_id) 
 );";

 	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	$q_db = dbDelta($initial_db);
	$cat_db = dbDelta($initial_cat_db);
	$loc_db = dbDelta($initial_loc_db);	

} ?>