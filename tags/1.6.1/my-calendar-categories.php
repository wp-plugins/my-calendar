<?php
// Function to handle the management of categories

function my_dirlist($directory) {
    // create an array to hold directory list
    $results = array();
    // create a handler for the directory
    $handler = opendir($directory);
    // keep going until all files in directory have been read
    while ($file = readdir($handler)) {
        // if $file isn't this directory or its parent, 
        // add it to the results array
        if ($file != '.' && $file != '..')
            $results[] = $file;
    }
    // tidy up: close the handler
    closedir($handler);
    // done!
	sort($results,SORT_STRING);
    return $results;
}


function my_calendar_manage_categories() {
  global $wpdb;

  // My Calendar must be installed and upgraded before this will work
  check_my_calendar();

?>
<div class="wrap">
<?php 
echo my_calendar_check_db();
?>
<?php
  // We do some checking to see what we're doing
  if (isset($_POST['mode']) && $_POST['mode'] == 'add') {
      $sql = "INSERT INTO " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_name='".mysql_real_escape_string($_POST['category_name'])."', category_color='".mysql_real_escape_string($_POST['category_color'])."', category_icon='".mysql_real_escape_string($_POST['category_icon'])."'";
      $results = $wpdb->query($sql);
	  if ( $results ) {
      echo "<div class=\"updated\"><p><strong>".__('Category added successfully','my-calendar')."</strong></p></div>";
	  } else {
      echo "<div class=\"error\"><p><strong>".__('Category addition failed.','my-calendar')."</strong></p></div>";	  
	  }
    } else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'delete') {
      $sql = "DELETE FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".mysql_real_escape_string($_GET['category_id']);
      $results = $wpdb->query($sql);
	  if ($results) {
	      $sql = "UPDATE " . MY_CALENDAR_TABLE . " SET event_category=1 WHERE event_category=".mysql_real_escape_string($_GET['category_id']);
	      $cal_results = $wpdb->query($sql);
	  }
	  if ($results && $cal_results) {
      echo "<div class=\"updated\"><p><strong>".__('Category deleted successfully. Categories in calendar updated.','my-calendar')."</strong></p></div>";
	  } else if ( $results && !$cal_results ) {
      echo "<div class=\"updated\"><p><strong>".__('Category deleted successfully. Categories in calendar not updated.','my-calendar')."</strong></p></div>";	  
	  } else if ( !$results && $cal_results ) {
      echo "<div class=\"updated\"><p><strong>".__('Category not deleted. Categories in calendar updated.','my-calendar')."</strong></p></div>";	  
	  }
    } else if (isset($_GET['mode']) && isset($_GET['category_id']) && $_GET['mode'] == 'edit' && !isset($_POST['mode'])) {
      $sql = "SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " WHERE category_id=".mysql_real_escape_string($_GET['category_id']);
      $cur_cat = $wpdb->get_row($sql);
		mc_edit_category_form('edit',$cur_cat);
      } else if (isset($_POST['mode']) && isset($_POST['category_id']) && isset($_POST['category_name']) && isset($_POST['category_color']) && $_POST['mode'] == 'edit') {
      $sql = "UPDATE " . MY_CALENDAR_CATEGORIES_TABLE . " SET category_name='".mysql_real_escape_string($_POST['category_name'])."', category_color='".mysql_real_escape_string($_POST['category_color'])."', category_icon='".mysql_real_escape_string($_POST['category_icon'])."' WHERE category_id=".mysql_real_escape_string($_POST['category_id']);
      $wpdb->get_results($sql);
      echo "<div class=\"updated\"><p><strong>".__('Category edited successfully','my-calendar')."</strong></p></div>";
    }

  if ($_GET['mode'] != 'edit' || $_POST['mode'] == 'edit') {
	mc_edit_category_form('add');
    } 
?>
</div>
<?php
}

function mc_edit_category_form($view='edit',$cur_cat='') {
global $path;
if ( file_exists( WP_PLUGIN_DIR . '/my-calendar-custom/' ) ) {
		$directory = WP_PLUGIN_DIR . '/my-calendar-custom/';
		$path = '/my-calendar-custom';
	} else {
		$directory = dirname(__FILE__).'/icons/';
		$path = '/my-calendar/icons';
    }
?>
<?php if ($view == 'add') { ?>
    <h2><?php _e('Add Category','my-calendar'); ?></h2>
<?php } else { ?>
  <h2><?php _e('Edit Category','my-calendar'); ?></h2>
<?php } ?>

<?php jd_show_support_box(); ?>   
<div id="poststuff" class="jd-my-calendar">
<div class="postbox">

<h3><?php _e('Category Editor','my-calendar'); ?></h3>
	<div class="inside">	   
    <form name="my-calendar"  id="my-calendar" method="post" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-categories">
		<?php if ($view == 'add') { ?>	
			<div>
			<input type="hidden" name="mode" value="add" />
            <input type="hidden" name="category_id" value="" />
			</div>
		<?php } else { ?>
			<div>
			<input type="hidden" name="mode" value="edit" />
            <input type="hidden" name="category_id" value="<?php echo $cur_cat->category_id ?>" />
			</div>		
		<?php } ?>
			<fieldset>
			<legend><?php if ($view == 'add') { _e('Add Category','my-calendar'); } else { _e('Edit Category','my-calendar'); } ?></legend>
				<label for="category_name"><?php _e('Category Name','my-calendar'); ?>:</label> <input type="text" id="category_name" name="category_name" class="input" size="30" value="<?php echo $cur_cat->category_name ?>" /><br />
				<label for="category_color"><?php _e('Category Color (Hex format)','my-calendar'); ?>:</label> <input type="text" id="category_color" name="category_color" class="input" size="10" maxlength="7" value="<?php echo $cur_cat->category_color ?>" /><br />
				<label for="category_icon"><?php _e('Category Icon','my-calendar'); ?>:</label> <select name="category_icon" id="category_icon">
<?php
$files = my_dirlist($directory);
foreach ($files as $value) {
if ($cur_cat->category_icon == $value) {
	$selected = " selected='selected'";
} else {
	$selected = "";
}
	echo "<option value='$value'$selected style='background: url(".WP_PLUGIN_URL."$path/$value) left 50% no-repeat;'>$value</option>";
}
?>			
				</select>					
			</fieldset>
			<p>
                <input type="submit" name="save" class="button-primary" value="<?php if ($view == 'add') {  _e('Add Category','my-calendar'); } else { _e('Save Changes','my-calendar'); } ?> &raquo;" />
			</p>
    </form>
</div>
</div>
<?php mc_manage_categories(); ?>
</div>
<?php
}

function mc_manage_categories() {
	global $wpdb, $path;
?>
 <h2><?php _e('Manage Categories','my-calendar'); ?></h2>
<?php
    
    // We pull the categories from the database	
    $categories = $wpdb->get_results("SELECT * FROM " . MY_CALENDAR_CATEGORIES_TABLE . " ORDER BY category_id ASC");

 if ( !empty($categories) )
   {
     ?>
     <table class="widefat page fixed" id="my-calendar-category-listing" summary="Manage Categories Listing">
       <thead> 
       <tr>
         <th class="manage-column" scope="col"><?php _e('ID','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Category Name','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Category Color','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Category Icon','my-calendar'); ?></th>
	 <th class="manage-column" scope="col"><?php _e('Edit','my-calendar') ?></th>
	 <th class="manage-column" scope="col"><?php _e('Delete','my-calendar') ?></th>
       </tr>
       </thead>
       <?php
       $class = '';
       foreach ( $categories as $category ) {
	   $class = ($class == 'alternate') ? '' : 'alternate';
           ?>
           <tr class="<?php echo $class; ?>">
	     <th scope="row"><?php echo $category->category_id; ?></th>
	     <td><?php echo $category->category_name; ?></td>
	     <td style="background-color:<?php echo $category->category_color; ?>;">&nbsp;</td>
	     <td style="background-color:<?php echo $category->category_color; ?>;"><img src="<?php echo WP_PLUGIN_URL . $path; ?>/<?php echo $category->category_icon; ?>" alt="" /></td>		 
	     <td><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-categories&amp;mode=edit&amp;category_id=<?php echo $category->category_id;?>" class='edit'><?php echo __('Edit','my-calendar'); ?></a></td>
	     <?php
		       if ($category->category_id == 1) {
					echo '<td>'.__('N/A','my-calendar').'</td>';
		       } else {
	               ?>
	               <td><a href="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=my-calendar-categories&amp;mode=delete&amp;category_id=<?php echo $category->category_id;?>" class="delete" onclick="return confirm('<?php echo __('Are you sure you want to delete this category?','my-calendar'); ?>')"><?php echo __('Delete','my-calendar'); ?></a></td>
	               <?php
		       }
                ?>
              </tr>
                <?php
          }
      ?>
      </table>
      <?php
   } else {
     echo '<p>'.__('There are no categories in the database - something has gone wrong!','my-calendar').'</p>';
   }
?>
<?php
}