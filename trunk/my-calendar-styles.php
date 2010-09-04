<?php
// Display the admin configuration page

function edit_my_calendar_styles() {
  global $wpdb, $initial_style, $initial_listjs, $initial_caljs, $initial_minijs;
  
  // We can't use this page unless My Calendar is installed/upgraded
  check_my_calendar();

  if ( isset($_POST['style'] ) ) {

	$my_calendar_style = $_POST['style'];
	$my_calendar_caljs = $_POST['my_calendar_caljs'];
	$my_calendar_listjs = $_POST['my_calendar_listjs'];
	$my_calendar_minijs = $_POST['my_calendar_minijs'];
	

	$use_styles = ($_POST['use_styles']=='on')?'true':'false';
	
	  update_option('my_calendar_style',$my_calendar_style);
	  update_option('my_calendar_use_styles',$use_styles);
	  // turn info off or on
	  update_option('calendar_javascript', (int) $_POST['calendar_javascript']);
	  update_option('list_javascript', (int) $_POST['list_javascript']);
	  update_option('mini_javascript', (int) $_POST['mini_javascript']);
	  // set js
	  update_option('my_calendar_listjs',$my_calendar_listjs);
	  update_option('my_calendar_minijs',$my_calendar_minijs);
	  update_option('my_calendar_caljs',$my_calendar_caljs);
	  $my_calendar_show_css = ($_POST['my_calendar_show_css']=='')?'':$_POST['my_calendar_show_css'];
	  update_option('my_calendar_show_css',$my_calendar_show_css);
	  // Check to see if we are replacing the original style
	  
		if ( $_POST['reset_styles'] == 'on') {
			update_option('my_calendar_style',$initial_style);
		}
		if ( $_POST['reset_caljs'] == 'on') {
			update_option('my_calendar_caljs',$initial_caljs);
		}
		if ( $_POST['reset_listjs'] == 'on') {
			update_option('my_calendar_listjs',$initial_listjs);
		}
		if ( $_POST['reset_minijs'] == 'on') {
			update_option('my_calendar_minijs',$initial_minijs);
		}		
		echo "<div class=\"updated\"><p><strong>".__('Style Settings saved','my-calendar').".</strong></p></div>";
    }

  $my_calendar_style = stripcslashes(get_option('my_calendar_style'));
  $my_calendar_use_styles = get_option('my_calendar_use_styles');

  $my_calendar_listjs = stripcslashes(get_option('my_calendar_listjs'));
  $list_javascript = get_option('list_javascript');
  
  $my_calendar_caljs = stripcslashes(get_option('my_calendar_caljs'));
  $calendar_javascript = get_option('calendar_javascript');

  $my_calendar_minijs = stripcslashes(get_option('my_calendar_minijs'));
  $mini_javascript = get_option('mini_javascript'); 
  
  $my_calendar_show_css = stripcslashes(get_option('my_calendar_show_css'));
  
  // Now we render the form
 
  ?>
    <div class="wrap">
	<?php 
echo my_calendar_check_db();
?>
    <h2><?php _e('My Calendar Styles','my-calendar'); ?></h2>
    <?php jd_show_support_box(); ?>
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">
	<h3><?php _e('Calendar Style Settings','my-calendar'); ?></h3>
	<div class="inside">	
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-styles">
	<p>
	<label for="my_calendar_show_css"><?php _e('Show CSS &amp; JavaScript only on these pages (comma separated page IDs)','my-calendar'); ?></label> <input type="text" id="my_calendar_show_css" name="my_calendar_show_css" value="<?php echo $my_calendar_show_css; ?>" />
	</p>    
	<fieldset>
    <legend><?php _e('CSS Style Options','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="reset_styles" name="reset_styles" /> <label for="reset_styles"><?php _e('Reset the My Calendar stylesheet to the default','my-calendar'); ?></label> <input type="checkbox" id="use_styles" name="use_styles" <?php jd_cal_checkCheckbox('my_calendar_use_styles','true'); ?> /> <label for="use_styles"><?php _e('Disable My Calendar Stylesheet','my-calendar'); ?></label>
	</p>	
	<p>
	<label for="style"><?php _e('Edit the stylesheet for My Calendar','my-calendar'); ?></label><br /><textarea id="style" name="style" rows="30" cols="80"><?php echo $my_calendar_style; ?></textarea>
	</p>	
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
    <fieldset>
	<legend><?php _e('Calendar Behaviors: Calendar View','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="reset_caljs" name="reset_caljs" /> <label for="reset_caljs"><?php _e('Reset the My Calendar Calendar Javascript','my-calendar'); ?></label> <input type="checkbox" id="calendar_javascript" name="calendar_javascript" value="1"  <?php jd_cal_checkCheckbox('calendar_javascript',1); ?>/> <label for="calendar_javascript"><?php _e('Disable Calendar Javascript Effects','my-calendar'); ?></label>
	</p>
	<p>
	<label for="calendar-javascript"><?php _e('Edit the jQuery scripts for My Calendar in Calendar format','my-calendar'); ?></label><br /><textarea id="calendar-javascript" name="my_calendar_caljs" rows="10" cols="80"><?php echo $my_calendar_caljs; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
    <fieldset>
	<legend><?php _e('Calendar Behaviors: List View','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="reset_listjs" name="reset_listjs" /> <label for="reset_listjs"><?php _e('Reset the My Calendar List Javascript','my-calendar'); ?></label> <input type="checkbox" id="list_javascript" name="list_javascript" value="1" <?php jd_cal_checkCheckbox('list_javascript',1); ?> /> <label for="list_javascript"><?php _e('Disable List Javascript Effects','my-calendar'); ?></label> 
	</p>
	<p>
	<label for="list-javascript"><?php _e('Edit the jQuery scripts for My Calendar in List format','my-calendar'); ?></label><br /><textarea id="list-javascript" name="my_calendar_listjs" rows="10" cols="80"><?php echo $my_calendar_listjs; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
   <fieldset>
	<legend><?php _e('Calendar Behaviors: Mini Calendar View','my-calendar'); ?></legend>
	<p>
	<input type="checkbox" id="reset_minijs" name="reset_minijs" /> <label for="reset_minijs"><?php _e('Reset the My Calendar Mini Format Javascript','my-calendar'); ?></label> <input type="checkbox" id="mini_javascript" name="mini_javascript" value="1" <?php jd_cal_checkCheckbox('mini_javascript',1); ?> /> <label for="mini_javascript"><?php _e('Disable Mini Javascript Effects','my-calendar'); ?></label> 
	</p>
	<p>
	<label for="mini-javascript"><?php _e('Edit the jQuery scripts for My Calendar in Mini Calendar format','my-calendar'); ?></label><br /><textarea id="mini-javascript" name="my_calendar_minijs" rows="10" cols="80"><?php echo $my_calendar_minijs; ?></textarea>
	</p>
	<p>
		<input type="submit" name="save" class="button-primary" value="<?php _e('Save','my-calendar'); ?> &raquo;" />
	</p>	
	</fieldset>
  </form>
  </div>

 </div>
 </div>
 </div>
  <?php


}

?>