<?php
function mc_user_profile() {
		global $user_ID;
		get_currentuserinfo();
		if ( isset($_GET['user_id']) ) { 
			$user_ID = (int) $_GET['user_id']; 
		} 
			$mc_us = get_usermeta( $user_ID, 'my_calendar_user_settings' );
			$options = get_option('mc_user_settings');
		
		foreach ($options as $key=>$value) {
			if ($value['enabled'] == 'on') {
				$name = $key;
				$label = $value['label'];
				$values = $value['values'];
				
				$form .= "
				<tr>
				<th scope='row'><label for='my_calendar_user_settings'>$label</label></th>
				<td><select name='my_calendar_user_settings[$name]' id='my_calendar_user_settings'>
				<option value='none'>No preference</option>\n";		
				foreach ($values as $optkey=>$optvalue) {
					if ($mc_us[$name]==$optkey) {
						$selected = " selected='selected'";
					} else {
						$selected = '';
					}
					$form .= "<option value='$optkey'$selected>$optvalue</option>\n";
				}
				
				$form .= "</select></td>
				</tr>";
			}
			
		}
		
		?>
		
		<h3><?php _e('My Calendar User Settings', 'my-calendar'); ?></h3>
		
		<table class="form-table">
		<?php echo $form; ?>	
		</table>
		
<?php
}

function mc_user_save_profile() {
	global $user_ID;
	get_currentuserinfo();
	if ( isset($_POST['user_id']) ) { 
		$user_ID = (int) $_POST['user_id']; 
	} 
	update_usermeta($user_ID ,'my_calendar_user_settings' , $_POST['my_calendar_user_settings'] );
}
?>