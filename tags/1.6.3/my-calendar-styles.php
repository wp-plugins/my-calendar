<?php
// Display the admin configuration page


function edit_my_calendar_styles() {
	global $wpdb, $initial_style;
	
	// We can't use this page unless My Calendar is installed/upgraded
	check_my_calendar();
	$my_calendar_style = stripcslashes( get_option('my_calendar_style') );
	$my_calendar_use_styles = get_option('my_calendar_use_styles');
	$my_calendar_show_css = stripcslashes(get_option('my_calendar_show_css'));
	
	if ( isset($_POST['style'] ) ) {

	$my_calendar_style = $_POST['style'];
	$use_styles = ($_POST['use_styles']=='on')?'true':'false';
	
	update_option('my_calendar_style',$my_calendar_style);
	update_option('my_calendar_use_styles',$use_styles);

	$my_calendar_show_css = ($_POST['my_calendar_show_css']=='')?'':$_POST['my_calendar_show_css'];
	update_option('my_calendar_show_css',$my_calendar_show_css);

		if ( $_POST['reset_styles'] == 'on') {
			update_option('my_calendar_style',$initial_style);
		}
	echo "<div class=\"updated\"><p><strong>".__('Style Settings saved','my-calendar').".</strong></p></div>";
	}
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
	<label for="my_calendar_show_css"><?php _e('Apply CSS only on these pages (comma separated page IDs)','my-calendar'); ?></label> <input type="text" id="my_calendar_show_css" name="my_calendar_show_css" value="<?php echo $my_calendar_show_css; ?>" />
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
  </form>
  </div>

 </div>
 </div>
 </div>
  <?php


}

?>