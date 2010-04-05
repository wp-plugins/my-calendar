<?php
// Display the admin configuration page
function edit_my_calendar_config() {
  global $wpdb, $initial_style;

  // We can't use this page unless My Calendar is installed/upgraded
  check_calendar();

  if (isset($_POST['permissions']) && isset($_POST['style'])) {
      if ($_POST['permissions'] == 'subscriber') { $new_perms = 'read'; }
      else if ($_POST['permissions'] == 'contributor') { $new_perms = 'edit_posts'; }
      else if ($_POST['permissions'] == 'author') { $new_perms = 'publish_posts'; }
      else if ($_POST['permissions'] == 'editor') { $new_perms = 'moderate_comments'; }
	else if ($_POST['permissions'] == 'admin') { $new_perms = 'manage_options'; }
	else { $new_perms = 'manage_options'; }

	$my_calendar_style = $_POST['style'];
	$my_calendar_show_months = (int) $_POST['my_calendar_show_months'];
	$my_calendar_date_format = $_POST['my_calendar_date_format'];

	if (mysql_escape_string($_POST['display_author']) == 'on') { 
	$disp_author = 'true';
	} else {
	$disp_author = 'false';
	}

	if (mysql_escape_string($_POST['display_jump']) == 'on') {
	$disp_jump = 'true';
	} else {
	$disp_jump = 'false';
	}
	
	if (mysql_escape_string($_POST['use_styles']) == 'on') {
	$use_styles = 'true';			
	} else {
	$use_styles = 'false';
	}
	
	if (mysql_escape_string($_POST['my_calendar_show_map']) == 'on') {
	$my_calendar_show_map = 'true';			
	} else {
	$my_calendar_show_map = 'false';
	}
	
	if (mysql_escape_string($_POST['my_calendar_show_address']) == 'on') {
	$my_calendar_show_address = 'true';			
	} else {
	$my_calendar_show_address = 'false';
	}
	
	  update_option('can_manage_events',$new_perms);
	  update_option('my_calendar_style',$my_calendar_style);
	  update_option('display_author',$disp_author);
	  update_option('display_jump',$disp_jump);
	  update_option('my_calendar_use_styles',$use_styles);
	  update_option('my_calendar_show_months',$my_calendar_show_months);
	  update_option('my_calendar_date_format',$my_calendar_date_format);
	  update_option('my_calendar_show_map',$my_calendar_show_map);
	  update_option('my_calendar_show_address',$my_calendar_show_address); 
	  update_option('calendar_javascript', (int) $_POST['calendar_javascript']);
	  update_option('list_javascript', (int) $_POST['list_javascript']);
	  // Check to see if we are replacing the original style
	  
      if (mysql_escape_string($_POST['reset_styles']) == 'on') {
          update_option('my_calendar_style',$initial_style);
        }
      echo "<div class=\"updated\"><p><strong>".__('Settings saved','my-calendar').".</strong></p></div>";
    }

  // Pull the values out of the database that we need for the form
  $allowed_group = get_option('can_manage_events');
  $my_calendar_style = stripcslashes(get_option('my_calendar_style'));
  $my_calendar_use_styles = get_option('my_calendar_use_styles');
  $my_calendar_show_months = get_option('my_calendar_show_months');
  $my_calendar_show_map = get_option('my_calendar_show_map');
  $my_calendar_show_address = get_option('my_calendar_show_address');
  $disp_author = get_option('display_author');
  $calendar_javascript = get_option('calendar_javascript');
  $list_javascript = get_option('list_javascript');
  // checkbox
  $disp_jump = get_option('display_jump');
  //checkbox

  if ($allowed_group == 'read') { $subscriber_selected='selected="selected"';}
  else if ($allowed_group == 'edit_posts') { $contributor_selected='selected="selected"';}
  else if ($allowed_group == 'publish_posts') { $author_selected='selected="selected"';}
  else if ($allowed_group == 'moderate_comments') { $editor_selected='selected="selected"';}
  else if ($allowed_group == 'manage_options') { $admin_selected='selected="selected"';}

  // Now we render the form
  ?>
    <div class="wrap">
	
    <h2><?php _e('My Calendar Options','my-calendar'); ?></h2>
    <?php show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Calendar Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=my-calendar-config">
    <fieldset>
    <legend><?php _e('Primary Calendar Options','my-calendar'); ?></legend>
    <p>
    <label for="permissions"><?php _e('Choose the lowest user group that may manage events','my-calendar'); ?></label> <select id="permissions" name="permissions">
				            <option value="subscriber"<?php echo $subscriber_selected ?>><?php _e('Subscriber','my-calendar')?></option>
				            <option value="contributor" <?php echo $contributor_selected ?>><?php _e('Contributor','my-calendar')?></option>
				            <option value="author" <?php echo $author_selected ?>><?php _e('Author','my-calendar')?></option>
				            <option value="editor" <?php echo $editor_selected ?>><?php _e('Editor','my-calendar')?></option>
				            <option value="admin" <?php echo $admin_selected ?>><?php _e('Administrator','my-calendar')?></option>
				        </select>
	</p>
	<p>
    <label for="display_author"><?php _e('Do you want to display the author name on events?','my-calendar'); ?></label> <select id="display_author" name="display_author">
                                        <option value="on" <?php jd_cal_checkSelect('display_author','true'); ?>><?php _e('Yes','my-calendar') ?></option>
                                        <option value="off" <?php jd_cal_checkSelect('display_author','false'); ?>><?php _e('No','my-calendar') ?></option>
                                    </select>
	</p>
	<p>
	<label for="display_jump"><?php _e('Display a jumpbox for changing month and year quickly?','my-calendar'); ?></label> <select id="display_jump" name="display_jump">
                                         <option value="on" <?php jd_cal_checkSelect('display_jump','true'); ?>><?php _e('Yes','my-calendar') ?></option>
                                         <option value="off" <?php jd_cal_checkSelect('display_jump','false'); ?>><?php _e('No','my-calendar') ?></option>
                                    </select>
	</p>
	<p>
	<label for="my_calendar_show_months"><?php _e('In list mode, show how many months of events at a time:','my-calendar'); ?></label> <input type="text" size="3" id="my_calendar_show_months" name="my_calendar_show_months" value="<?php echo $my_calendar_show_months; ?>" />
	</p>
	<p>
	<label for="my_calendar_date_format"><?php _e('Date format in list mode','my-calendar'); ?></label> <input type="text" id="my_calendar_date_format" name="my_calendar_date_format" value="<?php if ( get_option('my_calendar_date_format')  == "") { echo get_option('date_format'); } else { echo get_option( 'my_calendar_date_format'); } ?>" /> Current: <?php if ( get_option('my_calendar_date_format') == '') { echo date(get_option('date_format')); } else { echo date(get_option('my_calendar_date_format')); } ?><br />
	<small><?php _e('Date format uses the same syntax as the <a href="http://php.net/date">PHP <code>date()</code> function</a>. Save option to update sample output.','my-calendar'); ?></small>
	</p>
	<p>
    <input type="checkbox" id="my_calendar_show_map" name="my_calendar_show_map" <?php jd_cal_checkCheckbox('my_calendar_show_map','true'); ?> /> <label for="my_calendar_show_map"><?php _e('Show Link to Google Map (when sufficient address information is available.)','my-calendar'); ?></label><br />
    <input type="checkbox" id="my_calendar_show_address" name="my_calendar_show_address" <?php jd_cal_checkCheckbox('my_calendar_show_address','true'); ?> /> <label for="my_calendar_show_address"><?php _e('Show Event Address in Details','my-calendar'); ?></label>
	</p>
	</fieldset>
	<fieldset>
	<legend><?php _e('Calendar Styles','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="reset_styles" name="reset_styles" /> <label for="reset_styles"><?php _e('Reset the My Calendar style to default','my-calendar'); ?></label><br />
    <input type="checkbox" id="use_styles" name="use_styles" <?php jd_cal_checkCheckbox('my_calendar_use_styles','true'); ?> /> <label for="use_styles"><?php _e('Disable My Calendar Stylesheet','my-calendar'); ?></label>
	</p>	
	<p>
	<label for="style"><?php _e('Edit the stylesheet for My Calendar','my-calendar'); ?></label><br /><textarea id="style" name="style" rows="10" cols="60" tabindex="2"><?php echo $my_calendar_style; ?></textarea>
	</p>	
	</fieldset>
    <fieldset>
	<legend><?php _e('Calendar Behaviors','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="list_javascript" name="list_javascript" value="1" <?php jd_cal_checkCheckbox('list_javascript',1); ?> /> <label for="list_javascript"><?php _e('Disable List Javascript Effects','my-calendar'); ?></label><br />
	<input type="checkbox" id="calendar_javascript" name="calendar_javascript" value="1"  <?php jd_cal_checkCheckbox('calendar_javascript',1); ?>/> <label for="calendar_javascript"><?php _e('Disable Calendar Javascript Effects','my-calendar'); ?></label>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>
  </form>
  </div>
 </div>
 </div>
 </div>
  <?php


}
?>