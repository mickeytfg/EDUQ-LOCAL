<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://ljapps.com
 * @since      1.0.0
 *
 * @package    WP_Google_Reviews
 * @subpackage WP_Google_Reviews/admin/partials
 */

    // check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }
 
    // add error/update messages
 
    // check if the user have submitted the settings
    // wordpress will add the "settings-updated" $_GET parameter to the url
    if (isset($_GET['settings-updated'])) {
        // add settings saved message with the class of "updated"
        add_settings_error('wpfbr_messages', 'wpfbr_message', __('Settings Saved', 'wp-review-slider-pro'), 'updated');
    }
    // show error/update messages
    settings_errors('wpfbr_messages');
?>
    
<div class="wrap wp_pro-settings" id="">
	<h1><img src="<?php echo plugin_dir_url( __FILE__ ) . 'logo.png'; ?>"></h1>

<?php 
include("tabmenu.php");
?>	
<div class="w3-row">
<div class="w3-col m2 sidemenucontainer">
<?php
include("getrevs_sidemenu.php");
?>	
</div>
<div class="w3-col m10">
<p><b>
<?php
printf( __( 'Use the %1$sReview Funnels%2$s method if you need to grab all your reviews instead of just your 5 Most Helpful.', 'wp-review-slider-pro' ) , '<a href='.$urlget['reviewfunnel'].'>','</a>' );
?></b>
</p>
	
	<form action="options.php" method="post">
		<?php
		//$options = get_option('wpfbr_google_options');
		//print_r($options);
		// get the value of the setting we've registered with register_setting()
		$options = get_option('wpfbr_google_options');

		// output security fields for the registered setting "wp_fb-google_settings"
		settings_fields('wp_fb-google_settings');
		
		// output setting sections and their fields
		// (sections are registered for "wp_fb-google_settings", each field is registered to a specific section)

		do_settings_sections('wp_fb-google_settings');

		// output save settings button
		//submit_button('Save Settings');
		
	?>
	<p class="submit">
		<input name="submit" id="submit" class="button button-primary" value="Save Settings" type="submit">&nbsp;&nbsp;

		<?php
		if( ! empty( $options['google_location_set']['place_id'] ) ) {
		?>
		<button onclick='getgooglereviewsfunction("<?php echo esc_attr( $options['google_location_set']['place_id'] ); ?>")' id="wpfbr_getgooglereviews" type="button" class="btn_green">Retrieve Reviews</button><br/>

		<?php } else {?>
		<button onclick='alert("Please enter the Location above and click Save Settings.");' title="Please enter the Location above and click Save Settings." id="wpfbr_getgooglereviews" type="button" class="button button-secondary btn_off">Retrieve Reviews</button><span><i> Please enter the Location above and click Save Settings.</i></span>
		<?php } ?>
	</p>
	</form>

	<div id="popup" class="popup-wrapper wprevpro_hide">
	  <div class="popup-content">
		<div class="popup-title">
		  <button type="button" class="popup-close">&times;</button>
		  <h3 id="popup_titletext"></h3>
		</div>
		<div id="popupbody" class="popup-body">
		  <div id="popup_bobytext1"></div>
		  <div id="popup_bobytext2"></div>
		</div>
	  </div>
	</div>
</div>
</div>