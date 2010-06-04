<?php
// Function to handle the management of locations

function my_calendar_manage_locations() {
  global $wpdb;

  // My Calendar must be installed and upgraded before this will work
  check_my_calendar();
  
  
?>
<div class="wrap">
<?php
  // We do some checking to see what we're doing
  if (isset($_POST['mode']) && $_POST['mode'] == 'add') {
      $sql = "INSERT INTO " . MY_CALENDAR_LOCATIONS_TABLE . " SET location_label='".mysql_real_escape_string($_POST['location_label'])."', location_street='".mysql_real_escape_string($_POST['location_street'])."', location_street2='".mysql_real_escape_string($_POST['location_street2'])."', location_city='".mysql_real_escape_string($_POST['location_city'])."', location_state='".mysql_real_escape_string($_POST['location_state'])."', location_postcode='".mysql_real_escape_string($_POST['location_postcode'])."', location_country='".mysql_real_escape_string($_POST['location_country'])."'";
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>".__('Location added successfully','my-calendar')."</strong></p></div>";
    } else if (isset($_GET['mode']) && isset($_GET['location_id']) && $_GET['mode'] == 'delete') {
      $sql = "DELETE FROM " . MY_CALENDAR_LOCATIONS_TABLE . " WHERE location_id=".mysql_real_escape_string($_GET['location_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>".__('Location deleted successfully','my-calendar')."</strong></p></div>";
    } else if (isset($_GET['mode']) && isset($_GET['location_id']) && $_GET['mode'] == 'edit' && !isset($_POST['mode'])) {
      $sql = "SELECT * FROM " . MY_CALENDAR_LOCATIONS_TABLE . " WHERE location_id=".mysql_real_escape_string($_GET['location_id']);
      $cur_loc = $wpdb->get_row($sql);
      ?>
   <h2><?php _e('Edit Location','my-calendar'); ?></h2>
<?php jd_show_support_box(); ?>   
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
<h3><?php _e('Location Editor','my-calendar'); ?></h3>
	<div class="inside">	   
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations">
			<div>
			<input type="hidden" name="mode" value="edit" />
            <input type="hidden" name="location_id" value="<?php echo $cur_loc->location_id ?>" />
			</div>
			<fieldset>
			<legend>Event Location</legend>
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<p>
			<label for="location_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> <input type="text" id="location_label" name="location_label" class="input" size="40" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_label); ?>" />
			</p>
			<p>
			<label for="location_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="location_street" name="location_street" class="input" size="40" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_street); ?>" />
			</p>			
			<p>
			<label for="location_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="location_street2" name="location_street2" class="input" size="40" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_street2); ?>" />
			</p>
			<p>
			<label for="location_city"><?php _e('City','my-calendar'); ?></label> <input type="text" id="location_city" name="location_city" class="input" size="40" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_city); ?>" /> <label for="location_state"><?php _e('State/Province','my-calendar'); ?></label> <input type="text" id="location_state" name="location_state" class="input" size="10" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_state); ?>" /> <label for="location_postcode"><?php _e('Postal Code','my-calendar'); ?></label> <input type="text" id="location_postcode" name="location_postcode" class="input" size="10" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_postcode); ?>" />
			</p>			
			<p>
			<label for="location_country"><?php _e('Country','my-calendar'); ?></label> <input type="text" id="location_country" name="location_country" class="input" size="10" value="<?php if ( !empty($cur_loc) ) echo htmlspecialchars($cur_loc->location_country); ?>" />
			</p>
			</fieldset>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Save Changes','my-calendar'); ?> &raquo;" />
			</p>
    </form>
</div>
</div>
</div>
      <?php
    } else if (isset($_POST['mode']) && isset($_POST['location_id']) && isset($_POST['location_label']) && isset($_POST['location_street']) && $_POST['mode'] == 'edit') {
      $sql = "UPDATE " . MY_CALENDAR_LOCATIONS_TABLE . " SET location_label='".mysql_real_escape_string($_POST['location_label'])."', location_street='".mysql_real_escape_string($_POST['location_street'])."', location_street2='".mysql_real_escape_string($_POST['location_street2'])."', location_city='".mysql_real_escape_string($_POST['location_city'])."', location_state='".mysql_real_escape_string($_POST['location_state'])."', location_postcode='".mysql_real_escape_string($_POST['location_postcode'])."', location_country='".mysql_real_escape_string($_POST['location_country'])."' WHERE location_id=".mysql_real_escape_string($_POST['location_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>".__('Location edited successfully','my-calendar')."</strong></p></div>";
    }

  if ($_GET['mode'] != 'edit' || $_POST['mode'] == 'edit') {
?>

    <h2><?php _e('Add Location','my-calendar'); ?></h2>
	<?php jd_show_support_box(); ?>   
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
<h3><?php _e('Add New Location','my-calendar'); ?></h3>
	<div class="inside">		
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations">
			<div>
			<input type="hidden" name="mode" value="add" />
            <input type="hidden" name="location_id" value="" />
			</div>
			<fieldset>
			<legend>Event Location</legend>
			<p>
			<?php _e('All location fields are optional: <em>insufficient information may result in an inaccurate map</em>.','my-calendar'); ?>
			</p>
			<p>
			<label for="location_label"><?php _e('Name of Location (e.g. <em>Joe\'s Bar and Grill</em>)','my-calendar'); ?></label> <input type="text" id="location_label" name="location_label" class="input" size="40" value="" />
			</p>
			<p>
			<label for="location_street"><?php _e('Street Address','my-calendar'); ?></label> <input type="text" id="location_street" name="location_street" class="input" size="40" value="" />
			</p>			
			<p>
			<label for="location_street2"><?php _e('Street Address (2)','my-calendar'); ?></label> <input type="text" id="location_street2" name="location_street2" class="input" size="40" value="" />
			</p>
			<p>
			<label for="location_city"><?php _e('City','my-calendar'); ?></label> <input type="text" id="location_city" name="location_city" class="input" size="40" value="" /> <label for="location_state"><?php _e('State/Province','my-calendar'); ?></label> <input type="text" id="location_state" name="location_state" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->location_state); ?>" /> <label for="location_postcode"><?php _e('Postal Code','my-calendar'); ?></label> <input type="text" id="location_postcode" name="location_postcode" class="input" size="10" value="<?php if ( !empty($data) ) echo htmlspecialchars($data->location_postcode); ?>" />
			</p>			
			<p>
			<label for="location_country"><?php _e('Country','my-calendar'); ?></label> <input type="text" id="location_country" name="location_country" class="input" size="10" value="" />
			</p>
			</fieldset>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php _e('Add Location','my-calendar'); ?> &raquo;" />
			</p>
    </form>
</div>
</div>
</div>
    <h2><?php _e('Manage Locations','my-calendar'); ?></h2>
<?php
    
    // We pull the locations from the database	
    $locations = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_LOCATIONS_TABLE . " ORDER BY location_id ASC");

 if ( !empty($locations) )
   {
     ?>
     <table class="widefat page fixed" id="my-calendar-location-listing" summary="Manage Locations Listing">
       <thead> 
       <tr>
         <th class="manage-column" scope="col"><?php _e('ID','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Location','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Edit','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Delete','my-calendar') ?></th>
       </tr>
       </thead>
       <?php
       $class = '';
       foreach ( $locations as $location ) {
	   $class = ($class == 'alternate') ? '' : 'alternate';
           ?>
           <tr class="<?php echo $class; ?>">
	     <th scope="row"><?php echo $location->location_id; ?></th>
	     <td><?php echo $location->location_label . "<br />" . $location->location_street . "<br />" . $location->location_street2 . "<br />" . $location->location_city . ", " . $location->location_state . " " . $location->location_postcode; ?></td>
	     <td><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations&amp;mode=edit&amp;location_id=<?php echo $location->location_id;?>" class='edit'><?php echo __('Edit','my-calendar'); ?></a></td>
         <td><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-locations&amp;mode=delete&amp;location_id=<?php echo $category->location_id;?>" class="delete" onclick="return confirm('<?php echo __('Are you sure you want to delete this category?','my-calendar'); ?>')"><?php echo __('Delete','my-calendar'); ?></a></td>
         </tr>
                <?php
          }
      ?>
      </table>
      <?php
   } else {
     echo '<p>'.__('There are no locations in the database yet!','my-calendar').'</p>';
   }
?>
<p>
<em><?php _e('Please note: editing or deleting locations stored for re-use will have no effect on any event previously scheduled at that location. The location database exists purely as a shorthand method to enter frequently used locations into event records.','my-calendar'); ?>
</p>
  </div>

<?php
      } 
?>
</div>
<?php
}
?>