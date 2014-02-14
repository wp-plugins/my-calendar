<?php
// Load WordPress core files
	$iswin = preg_match('/:\\\/', dirname(__file__));
	$slash = ($iswin) ? "\\" : "/";
	$wp_path = preg_split('/(?=((\\\|\/)wp-content)).*/', dirname(__file__));
	$wp_path = ( isset($wp_path[0]) && $wp_path[0] != "" && $wp_path[0] != dirname(__FILE__) ) ? $wp_path[0] : $_SERVER["DOCUMENT_ROOT"];
require_once($wp_path . $slash . 'wp-load.php');
require_once($wp_path . $slash . 'wp-admin' . $slash . 'admin.php'); 

// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') ) 
	wp_die(__( "You don't have access to this function.", 'my-calendar' ));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php bloginfo('name') ?> &rsaquo; <?php _e("My Calendar Shortcode Generator",'my-calendar'); ?> &#8212; WordPress</title>
<?php
// WordPress styles
wp_admin_css( 'css/global' );
wp_admin_css( 'css/colors' );
wp_admin_css( 'css/ie' );
$hook_suffix = '';
if ( isset($page_hook) ) {
	$hook_suffix = "$page_hook";
} else if ( isset($plugin_page) ) {
	$hook_suffix = "$plugin_page";
} else if ( isset($pagenow) ) {
	$hook_suffix = "$pagenow";
}
do_action("admin_print_styles-$hook_suffix");
do_action('admin_print_styles');
do_action("admin_print_scripts-$hook_suffix");
do_action('admin_print_scripts');
do_action("admin_head-$hook_suffix");
do_action('admin_head');
?>
<link rel="stylesheet" href="<?php echo plugins_url('button/generator.css',dirname(__FILE__)); ?>?ver=<?php echo mc_tiny_mce_version(); ?>" type="text/css" media="screen" charset="utf-8" />
<script src="<?php echo plugins_url('button/mcb.js',dirname(__FILE__)); ?>" type="text/javascript" charset="utf-8"></script>
</head>
<body class="<?php echo apply_filters( 'admin_body_class', '' ); ?>">
	<div class="wrap">
		<h2><?php _e("My Calendar Shortcode Generator",'my-calendar'); ?></h2> 
		<p>
			<?php _e("For navigational fields above and below the calendar: the defaults specified in your settings will be used if the attribute is left blank. Use <code>none</code> to hide all navigation elements.",'my-calendar'); ?>
		</p>
		<form action="#" mode="POST">
		<fieldset> 
			<legend><?php _e('Shortcode Attributes', 'my-calendar'); ?></legend>
					<p>
					<?php echo my_calendar_categories_list('select','admin'); ?>
					</p>
					<p>
						<label for="ltype"><?php _e('Location filter type:','my-calendar'); ?></label>
						<select name="ltype" id="ltype">
							<option value="" selected="selected"><?php _e('All locations','my-calendar'); ?></option>
							<option value='event_label'><?php _e('Location Name','my-calendar'); ?></option>
							<option value='event_city'><?php _e('City','my-calendar'); ?></option>
							<option value='event_state'><?php _e('State','my-calendar'); ?></option>
							<option value='event_postcode'><?php _e('Postal Code','my-calendar'); ?></option>
							<option value='event_country'><?php _e('Country','my-calendar'); ?></option>
							<option value='event_region'><?php _e('Region','my-calendar'); ?></option>
						</select>
					</p>
					<p>
					<label for="lvalue"><?php _e('Location filter value:','my-calendar'); ?></label>
					<input type="text" name="lvalue" id="lvalue" />
					</p>
					<p>
					<label for="format"><?php _e('Format', 'my-calendar'); ?></label>
                    <select name="format" id="format">
                        <option value="calendar" selected="selected"><?php _e('Grid','my-calendar'); ?></option> 
						<option value="list"><?php _e('List','my-calendar'); ?></option>
                    </select>
					</p>
					<p>
					<label for="above" id='labove'><?php _e('Navigation above calendar','my-calendar'); ?></label>
					<input type="text" name="above" id="above" value="nav,toggle,jump,print,timeframe" aria-labelledby='labove aboveLabel' /><br />
					<em id="aboveLabel"><?php _e('Use "none" for no navigation.','my-calendar'); ?></em>
					</p>
					<p>
					<label for="below" id='lbelow'><?php _e('Navigation below calendar','my-calendar'); ?></label>
					<input type="text" name="below" id="below" value="key,feeds" aria-labelledby='lbelow belowLabel' /><br />
					<em id="belowLabel"><?php _e('Use "none" for no navigation.','my-calendar'); ?></em>
					</p>					
					<p>
					<label for="time"><?php _e('Time Segment', 'my-calendar'); ?></label>
                    <select name="time" id="time">
                        <option value="month" selected="selected"><?php _e('Month', 'my-calendar'); ?></option>
                        <option value="week"><?php _e('Week', 'my-calendar'); ?></option> 
						<option value="day"><?php _e('Day', 'my-calendar'); ?></option>
                    </select>
					</p>
		<?php 
			 // Grab all the categories and list them
			$users = my_calendar_getUsers();
			$options = '';
			foreach($users as $u) {
				$options = '<option value="'.$u->ID.'">'.$u->display_name."</option>\n";
			}
		?>			<p>
					<label for="author"><?php _e('Limit by Author','my-calendar'); ?></label>
					<select name="author" id="author" multiple="multiple">
						<option value="all"><?php _e('All authors','my-calendar'); ?></option>
						<?php echo $options; ?>
					</select>
					</p>
					<p>
					<label for="host"><?php _e('Limit by Host','my-calendar'); ?></label>
					<select name="host" id="host" multiple="multiple">
						<option value="all"><?php _e('All hosts','my-calendar'); ?></option>
						<?php echo $options; ?>
					</select>
					</p>
		</fieldset>
		<p>
		<input type="button" class="button" id="mycalendar" name="generate" value="<?php _e('Generate Shortcode', 'my-calendar'); ?>" />
		</p>
		<p><?php _e('<strong>Note:</strong> If you provide a location filter value, it must be an exact match for that information as saved with your events. (e.g. "Saint Paul" is not equivalent to "saint paul" or "St. Paul")','my-calendar'); ?></p>
	</form>
	</div>
	<script type="text/javascript" charset="utf-8">
		// <![CDATA[
		jQuery(document).ready(function(){
			try {
				myCalQT.Tag.Generator.initialize();
			} catch (e) {
				throw "<?php _e("My Calendar: this generator isn't going to put the shortcode in your page. Sorry!", 'my-calendar'); ?>";
			}
		});
		// ]]>
	</script>
</body>
</html>