<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/includes
 * @author     Your Name <email@example.com>
 */
class WP_Review_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WP_Review_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugintoken    The string used to uniquely identify this plugin.
	 */
	protected $plugintoken;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->_token = 'wp-review-slider-pro';
		$this->version = '10.9.9';
		//using this for development
		//$this->version = time();

		$this->load_dependencies();
		
		//currenlty not using translation
		$this->set_locale();	
		
		if (is_admin()) {	//checks to see if looking at an admin screen, this is why we can't do cron in this
			$this->define_admin_hooks();
		}
		$this->define_public_hooks();
		
		if (is_admin()) {
			//save version number to db
			$this->_log_version_number();
			
			//update db if not at latest
			$this->_updatedb_version_number();
			
			//final check to see if uploads directory was created and is writable
			//if not then set to avatars and cache folders in plugin.
			$this->_check_upload_folder_creation();
			
			//check avatar folder and db to sync
			$this->_sync_avatar_version_number();
		}
	}
	
	/**
	 * Update DB check to sync Avatars
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _sync_avatar_version_number () {
		$current_version = get_option($this->_token . '_current_sync_version', 0);
		if($current_version!=$this->version){
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			//check local avatars, remove from db if they don't exists
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$currentreviews = $wpdb->get_results("SELECT id, reviewer_id, created_time_stamp, reviewer_name, type, userpic FROM $table_name");
			foreach ( $currentreviews as $review ){
				$id= $review->id;
				$revid = $review->reviewer_id;
				//$imagecachedir = plugin_dir_path( __DIR__ ).'public/partials/avatars/';
				$img_locations_option = json_decode(get_option( 'wprev_img_locations' ),true);
				$imagecachedir = $img_locations_option['upload_dir_wprev_avatars'];
				$filename = $review->created_time_stamp.'_'.$review->id;
				$newfile = $imagecachedir . $filename.'.jpg';
				//check if avatar file exists, if not then remove the db value
				if(@filesize($newfile)<200){
					$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
				}
			}
			update_option( $this->_token . '_current_sync_version', $this->version );
		}
	}
	
	/**
	 * change avatar and cache directory if we weren't able to create it.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function _check_upload_folder_creation () {

		$upload = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_dir_wprev = $upload_dir . '/wprevslider';

		if (is_dir($upload_dir_wprev)) {
			$fcreated = true;
			$dir_writable = substr(sprintf('%o', fileperms($upload_dir_wprev)), -4) == "0775" ? true : false;
		} else {
			$fcreated = false;
			$dir_writable = false;
		}
		
		if($dir_writable==true && $fcreated == true){
			$upload_dir_wprev_avatars = $upload_dir . '/wprevslider/avatars/';
			$upload_dir_wprev_cache = $upload_dir . '/wprevslider/cache/';
			$upload_url = $upload['baseurl'];
			$upload_url_wprev_avatars = $upload_url . '/wprevslider/avatars/';
			$upload_url_wprev_cache = $upload_url . '/wprevslider/cache/';
			//check for ssl
			if(is_ssl()) {
				$upload_url_wprev_avatars = str_replace( 'http://', 'https://', $upload_url_wprev_avatars );
				$upload_url_wprev_cache = str_replace( 'http://', 'https://', $upload_url_wprev_cache );
			}
		} else {
			//set the constants to plugin local folders
			$upload_dir_wprev_avatars = plugin_dir_path( __DIR__ ).'public/partials/avatars/';
			$upload_dir_wprev_cache = plugin_dir_path( __DIR__ ).'public/partials/cache/';
			$upload_url_wprev_avatars = plugins_url( 'public/partials/avatars/',  dirname(__FILE__)  );
			$upload_url_wprev_cache = plugins_url( 'public/partials/cache/',  dirname(__FILE__)  );
		}
		$img_locations['upload_dir_wprev_avatars']=$upload_dir_wprev_avatars;
		$img_locations['upload_url_wprev_avatars']=$upload_url_wprev_avatars;
		$img_locations['upload_dir_wprev_cache']=$upload_dir_wprev_cache;
		$img_locations['upload_url_wprev_cache']=$upload_url_wprev_cache;
		$img_locations = json_encode($img_locations);
		update_option( 'wprev_img_locations', $img_locations );
	}
	
	/**
	 * Update DB if not at latest
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _updatedb_version_number () {
		$current_version = get_option($this->_token . '_current_db_version', 0);
		
		//=============================================================
		//========set this != when done testing
		//==========================================================
		if($current_version!=$this->version){
			
			//check cron
			if (! wp_next_scheduled ( 'wprevpro_daily_event' )) {
				wp_schedule_event(time(), 'daily', 'wprevpro_daily_event');  
			}
			//for checking language_code
			if (! wp_next_scheduled ( 'wprevpro_daily_event_lang' )) {
				$starttime = time()+500;
				wp_schedule_event($starttime, 'daily', 'wprevpro_daily_event_lang');  
			}
		
			//update db here
			
			//update table in database
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$charset_collate = $wpdb->get_charset_collate();
			
			$sql = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				pageid varchar(150) DEFAULT '' NOT NULL,
				pagename tinytext NOT NULL,
				created_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				created_time_stamp int(12) NOT NULL,
				reviewer_name tinytext NOT NULL,
				reviewer_email tinytext NOT NULL,
				company_name varchar(100) DEFAULT '' NOT NULL,
				company_title varchar(100) DEFAULT '' NOT NULL,
				company_url varchar(100) DEFAULT '' NOT NULL,
				reviewer_id varchar(50) DEFAULT '' NOT NULL,
				rating int(2) NOT NULL,
				recommendation_type varchar(12) DEFAULT '' NOT NULL,
				review_text text NOT NULL,
				hide varchar(3) DEFAULT '' NOT NULL,
				review_length int(5) NOT NULL,
				review_length_char int(5) NOT NULL,
				type varchar(20) DEFAULT '' NOT NULL,
				userpic varchar(500) DEFAULT '' NOT NULL,
				userpic_small varchar(500) DEFAULT '' NOT NULL,
				from_name varchar(20) DEFAULT '' NOT NULL,
				from_url varchar(500) DEFAULT '' NOT NULL,
				from_logo varchar(500) DEFAULT '' NOT NULL,
				from_url_review varchar(500) DEFAULT '' NOT NULL,
				review_title tinytext DEFAULT '' NOT NULL,
				categories text NOT NULL,
				posts text NOT NULL,
				consent varchar(3) DEFAULT '' NOT NULL,
				userpiclocal varchar(500) DEFAULT '' NOT NULL,
				hidestars varchar(3) DEFAULT '' NOT NULL,
				miscpic varchar(500) DEFAULT '' NOT NULL,
				location varchar(500) DEFAULT '' NOT NULL,
				verified_order varchar(10) DEFAULT '' NOT NULL,
				language_code varchar(3) DEFAULT '' NOT NULL,
				unique_id tinytext DEFAULT '' NOT NULL,
				meta_data text DEFAULT '' NOT NULL,
				owner_response text NOT NULL,
				sort_weight int(5) NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql );
			
			$table_name = $wpdb->prefix . 'wpfb_post_templates';
			$sql_template = "CREATE TABLE $table_name (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				template_type varchar(7) DEFAULT '' NOT NULL,
				style int(2) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				display_num int(2) NOT NULL,
				display_num_rows int(3) NOT NULL,
				load_more varchar(3) DEFAULT '' NOT NULL,
				load_more_text varchar(50) DEFAULT '' NOT NULL,
				display_order varchar(10) DEFAULT '' NOT NULL,
				display_order_second varchar(10) DEFAULT '' NOT NULL,
				hide_no_text varchar(3) DEFAULT '' NOT NULL,
				template_css text NOT NULL,
				min_rating int(2) NOT NULL,
				min_words int(4) NOT NULL,
				max_words int(4) NOT NULL,
				word_or_char varchar(5) DEFAULT '' NOT NULL,
				rtype varchar(200) DEFAULT '' NOT NULL,
				rpage varchar(1000) DEFAULT '' NOT NULL,
				createslider varchar(3) DEFAULT '' NOT NULL,
				numslides int(2) NOT NULL,
				sliderautoplay varchar(3) DEFAULT '' NOT NULL,
				sliderdirection varchar(12) DEFAULT '' NOT NULL,
				sliderarrows varchar(3) DEFAULT '' NOT NULL,
				sliderdots varchar(3) DEFAULT '' NOT NULL,
				sliderdelay int(2) NOT NULL,
				sliderspeed int(5) NOT NULL,
				sliderheight varchar(3) DEFAULT '' NOT NULL,
				slidermobileview varchar(5) DEFAULT '' NOT NULL,
				showreviewsbyid varchar(600) DEFAULT '' NOT NULL,
				template_misc text DEFAULT '' NOT NULL,
				read_more varchar(3) DEFAULT '' NOT NULL,
				read_more_num int(4) NOT NULL,
				read_more_text varchar(20) DEFAULT '' NOT NULL,
				facebook_icon varchar(3) DEFAULT '' NOT NULL,
				facebook_icon_link varchar(3) DEFAULT '' NOT NULL,
				google_snippet_add varchar(3) DEFAULT '' NOT NULL,
				google_snippet_type varchar(50) DEFAULT '' NOT NULL,
				google_snippet_name varchar(50) DEFAULT '' NOT NULL,
				google_snippet_desc varchar(300) DEFAULT '' NOT NULL,
				google_snippet_business_image varchar(400) DEFAULT '' NOT NULL,
				google_snippet_more text DEFAULT '' NOT NULL,
				cache_settings varchar(5) DEFAULT '' NOT NULL,
				review_same_height varchar(3) DEFAULT '' NOT NULL,
				add_profile_link varchar(3) DEFAULT '' NOT NULL,
				display_order_limit varchar(3) DEFAULT '' NOT NULL,
				display_masonry varchar(3) DEFAULT '' NOT NULL,
				read_less_text varchar(20) DEFAULT '' NOT NULL,
				string_sel varchar(3) DEFAULT '' NOT NULL,
				string_text varchar(200) DEFAULT '' NOT NULL,
				showreviewsbyid_sel varchar(9) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_template );
		
			$table_name_badge = $wpdb->prefix . 'wpfb_badges';
			$sql_badge = "CREATE TABLE $table_name_badge (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				badge_type varchar(7) DEFAULT '' NOT NULL,
				badge_bname varchar(100) DEFAULT '' NOT NULL,
				badge_orgin varchar(20) DEFAULT '' NOT NULL,
				style varchar(10) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				badge_css text NOT NULL,
				badge_misc text DEFAULT '' NOT NULL,
				rpage varchar(1200) DEFAULT '' NOT NULL,
				google_snippet_add varchar(3) DEFAULT '' NOT NULL,
				google_snippet_type varchar(50) DEFAULT '' NOT NULL,
				google_snippet_name varchar(50) DEFAULT '' NOT NULL,
				google_snippet_desc varchar(300) DEFAULT '' NOT NULL,
				google_snippet_business_image varchar(400) DEFAULT '' NOT NULL,
				google_snippet_more text DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_badge );
			
			$table_name_form = $wpdb->prefix . 'wpfb_forms';
			$sql_form = "CREATE TABLE $table_name_form (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				style varchar(10) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				form_css text NOT NULL,
				form_html text NOT NULL,
				form_fields text NOT NULL,
				form_misc text DEFAULT '' NOT NULL,
				notifyemail varchar(200) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_form );
			
			$table_name_form = $wpdb->prefix . 'wpfb_floats';
			$sql_form = "CREATE TABLE $table_name_form (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				float_type varchar(200) DEFAULT '' NOT NULL,
				content_id varchar(200) DEFAULT '' NOT NULL,
				style varchar(10) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				float_css text NOT NULL,
				float_misc text DEFAULT '' NOT NULL,
				enabled int(2) NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_form );
			
			$table_name_reviewfunnel = $wpdb->prefix . 'wpfb_reviewfunnel';
			$sql_reviewfunnel = "CREATE TABLE $table_name_reviewfunnel (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(140) DEFAULT '' NOT NULL,
				site_type varchar(20) DEFAULT '' NOT NULL,
				url varchar(700) DEFAULT '' NOT NULL,
				cron varchar(3) DEFAULT '' NOT NULL,
				cron_last_job_id int(12) NOT NULL,
				cron_last_ran int(12) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				from_date varchar(10) DEFAULT '' NOT NULL,
				query varchar(300) DEFAULT '' NOT NULL,
				blocks varchar(4) DEFAULT '' NOT NULL,
				job_ids text DEFAULT '' NOT NULL,
				last_name varchar(7) DEFAULT '' NOT NULL,
				profile_img varchar(7) DEFAULT '' NOT NULL,
				categories text NOT NULL,
				posts text NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_reviewfunnel );
			
			$table_name_getapps = $wpdb->prefix . 'wpfb_getapps_forms';
			$sql_reviewfunnel = "CREATE TABLE $table_name_getapps (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(140) DEFAULT '' NOT NULL,
				page_id varchar(25) DEFAULT '' NOT NULL,
				site_type varchar(20) DEFAULT '' NOT NULL,
				url varchar(700) DEFAULT '' NOT NULL,
				cron varchar(3) DEFAULT '' NOT NULL,
				last_ran int(12) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				blocks varchar(4) DEFAULT '' NOT NULL,
				last_name varchar(7) DEFAULT '' NOT NULL,
				profile_img varchar(7) DEFAULT '' NOT NULL,
				categories text NOT NULL,
				posts text NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_reviewfunnel );
			
			$table_name_notiform = $wpdb->prefix . 'wpfb_nofitifcation_forms';
			$sql_reviewfunnel = "CREATE TABLE $table_name_notiform (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(200) DEFAULT '' NOT NULL,
				source_page text NOT NULL,
				site_type text NOT NULL,
				created_time_stamp int(12) NOT NULL,
				rate_op varchar(10) DEFAULT '' NOT NULL,
				rate_val varchar(1) DEFAULT '' NOT NULL,
				email text NOT NULL,
				email_subject text NOT NULL,
				email_first_line text NOT NULL,
				enable varchar(3) DEFAULT '' NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_reviewfunnel );
			
			$table_name_getapps = $wpdb->prefix . 'wpfb_gettwitter_forms';
			$sql_reviewfunnel = "CREATE TABLE $table_name_getapps (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				title varchar(140) DEFAULT '' NOT NULL,
				site_type varchar(20) DEFAULT '' NOT NULL,
				query text DEFAULT '' NOT NULL,
				endpoint varchar(3) DEFAULT '' NOT NULL,
				last_ran int(12) NOT NULL,
				created_time_stamp int(12) NOT NULL,
				blocks varchar(4) DEFAULT '' NOT NULL,
				profile_img varchar(7) DEFAULT '' NOT NULL,
				categories text NOT NULL,
				posts text NOT NULL,
				UNIQUE KEY id (id)
			) $charset_collate;";
			dbDelta( $sql_reviewfunnel );
			
			//moving option wppro_total_avg_reviews to a table so we can access easier
			/*
			$table_name_totalavg = $wpdb->prefix . 'wpfb_total_averages';
			$sql_reviewfunnel = "CREATE TABLE $table_name_totalavg (
				btp_id varchar(150) DEFAULT '' NOT NULL,
				btp_type varchar(10) DEFAULT '' NOT NULL,
				total_indb varchar(10) DEFAULT '' NOT NULL,
				total varchar(10) DEFAULT '' NOT NULL,
				avg_indb varchar(10) DEFAULT '' NOT NULL,
				avg varchar(10) DEFAULT '' NOT NULL,
				numr1 varchar(10) DEFAULT '' NOT NULL,
				numr2 varchar(10) DEFAULT '' NOT NULL,
				numr3 varchar(10) DEFAULT '' NOT NULL,
				numr4 varchar(10) DEFAULT '' NOT NULL,
				numr5 varchar(10) DEFAULT '' NOT NULL,
				UNIQUE KEY id (btp_id)
			) $charset_collate;";
			dbDelta( $sql_reviewfunnel );
			*/
			
			
			//create directories in uploads folder for avatar and cache_settings
			$upload = wp_upload_dir();
			$upload_dir = $upload['basedir'];
			$upload_dir_wprev = $upload_dir . '/wprevslider';
			//check folder permissions, delete if false
			if (is_dir($upload_dir_wprev)) {
				$dir_writable = substr(sprintf('%o', fileperms($upload_dir_wprev)), -4) == "0775" ? true : false;
				if($dir_writable==false){
					//delete the directory and sub directories
					$this->wpprorev_rmrf($upload_dir_wprev);
				}
			}
			if (! is_dir($upload_dir_wprev)) {
			   mkdir( $upload_dir_wprev, 0775 );
			   chmod($upload_dir_wprev, 0775);
			}
			$upload_dir_wprev_avatars = $upload_dir . '/wprevslider/avatars';
			if (! is_dir($upload_dir_wprev_avatars)) {
			   mkdir( $upload_dir_wprev_avatars, 0775 );
			   chmod($upload_dir_wprev_avatars, 0775);
			}
			$upload_dir_wprev_cache = $upload_dir . '/wprevslider/cache';
			if (! is_dir($upload_dir_wprev_cache)) {
			   mkdir( $upload_dir_wprev_cache, 0775 );
			   chmod($upload_dir_wprev_cache, 0775);
			}
			
			//===============bring over old badge totals from wp-options table in to new custom table added in 10.9.3, can delete later=================
			/*
			$wppro_total_avg_reviews_array = get_option('wppro_total_avg_reviews');
			if(isset($wppro_total_avg_reviews_array)){
				$wppro_total_avg_reviews_array = json_decode($wppro_total_avg_reviews_array, true);
				
				//========set this != when done testing
				foreach ($wppro_total_avg_reviews_array as $key => $valuearray) {
					//echo $key."<br>";
					//$key is the pageid
					//$valuearray['total']
					
					//first check to see if this in in our WPFB_TOTAL_AVERAGE table
					$checkforrow = $wpdb->get_var( "SELECT btp_id FROM $table_name_totalavg WHERE btp_id = '$key'" );
					
					if($checkforrow){
						//already in db, do nothing we assume this as been done before
					} else {
						//insert in to db
						$temp_total_indb=$valuearray['total_indb'];
						$temp_total='';
						if(isset($valuearray['total'])){
							$temp_total=$valuearray['total'];
						}
						$temp_avg_indb=$valuearray['avg_indb'];
						$temp_avg='';
						if(isset($valuearray['avg'])){
							$temp_avg=$valuearray['avg'];
						}
						$temp_numr1=$valuearray['numr1'];
						$temp_numr2=$valuearray['numr2'];
						$temp_numr3=$valuearray['numr3'];
						$temp_numr4=$valuearray['numr4'];
						$temp_numr5=$valuearray['numr5'];
						$data = array( 
								'btp_id' => "$key",
								'btp_type' => "page",
								'total_indb' => "$temp_total_indb",
								'total' => "$temp_total",
								'avg_indb' => "$temp_avg_indb",
								'avg' => "$temp_avg",
								'numr1' => "$temp_numr1",
								'numr2' => "$temp_numr2",
								'numr3' => "$temp_numr3",
								'numr4' => "$temp_numr4",
								'numr5' => "$temp_numr5",
								);
								//print_r($data);
						$format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'); 
						$insertrow = $wpdb->insert( $table_name_totalavg, $data, $format );
						
					}
				}
			}
			//print_r($wppro_total_avg_reviews_array);
			
			//now calculate and add badge ids totals and averages
			*/

			//=====================================
			
			//----check to see if we need to update character counts of reviews
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			//update the char count of all reviews that have > 0 word count, but 0 char count
			$reviewsrows = $wpdb->get_results(
								$wpdb->prepare("SELECT id,review_text FROM ".$table_name." WHERE review_length>%d AND review_length_char=%d", "0","0"), ARRAY_A);
			foreach ($reviewsrows as $value) {
				if (extension_loaded('mbstring')) {
					$review_length_char = mb_strlen($value['review_text']);
				} else {
					$review_length_char = strlen($value['review_text']);
				}
				
				$tempid = $value['id'];
				$wpdb->update( 
					$table_name, 
					array( 
						'review_length_char' => $review_length_char,	// number
					), 
					array( 'id' => $tempid ), 
					array( 
						'%d'	// value2
					), 
					array( '%d' ) 
				);
			}
			//-----
			
			//=================== bring over old notification settings, added in 10.7.4, can delete later========
			$table_name_notify = $wpdb->prefix . 'wpfb_nofitifcation_forms';
			$wprevpro_notifications_settings = get_option('wprevpro_notifications_settings');
				//print_r($wprevpro_notifications_settings);
			$notificationforms = $wpdb->get_results("SELECT * FROM $table_name_notify ORDER BY id DESC");
			//check to make sure there are no forms in there yet. we are also going to delete the option values
			if(count($notificationforms)<1){
				$wprevpro_notifications_settings = get_option('wprevpro_notifications_settings');
				//print_r($wprevpro_notifications_settings);
				$created_time_stamp=time();
				$typearray = unserialize(WPREV_TYPE_ARRAY);
				for($x=0;$x<count($typearray);$x++){
				$typelowercase = strtolower($typearray[$x]);
					if($typelowercase!='manual' && $typelowercase!='submitted'){
						//notifications
						if(isset($wprevpro_notifications_settings['notifications_type_'.$typelowercase]) && $wprevpro_notifications_settings['notifications_type_'.$typelowercase]>0){
							//this is set, we need to convert it over
							$db_title=$typelowercase;
							$db_site_type='["'.$typelowercase.'"]';
							$db_rate_op='<';
							$db_rate_val=$wprevpro_notifications_settings['notifications_type_'.$typelowercase]+1;
							$db_email=$wprevpro_notifications_settings['notifications_email'];
							$db_email_subject=$wprevpro_notifications_settings['notifications_subject'];
							$db_email_first_line=$wprevpro_notifications_settings['notifications_first_line'];
							$db_enable='yes';
										$data = array( 
										'title' => "$db_title",
										'source_page' => "",
										'site_type' => "$db_site_type",
										'created_time_stamp' => "$created_time_stamp",
										'rate_op' => "$db_rate_op",
										'rate_val' => "$db_rate_val",
										'email' => "$db_email",
										'email_subject' => "$db_email_subject",
										'email_first_line' => "$db_email_first_line",
										'enable' => "$db_enable"
										);
										//print_r($data);
									$format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'); 
									$insertrow = $wpdb->insert( $table_name_notify, $data, $format );
						}
						//for high notifications
						if(isset($wprevpro_notifications_settings['notifications_type_'.$typelowercase.'_high']) && $wprevpro_notifications_settings['notifications_type_'.$typelowercase.'_high']>0){
							//this is set, we need to convert it over
							$db_title=$typelowercase;
							$db_site_type='["'.$typelowercase.'"]';
							$db_rate_op='>';
							$db_rate_val=$wprevpro_notifications_settings['notifications_type_'.$typelowercase.'_high']-1;
							$db_email=$wprevpro_notifications_settings['notifications_email'];
							$db_email_subject=$wprevpro_notifications_settings['notifications_subject'];
							$db_email_first_line=$wprevpro_notifications_settings['notifications_first_line'];
							$db_enable='yes';
										$data = array( 
										'title' => "$db_title",
										'source_page' => "",
										'site_type' => "$db_site_type",
										'created_time_stamp' => "$created_time_stamp",
										'rate_op' => "$db_rate_op",
										'rate_val' => "$db_rate_val",
										'email' => "$db_email",
										'email_subject' => "$db_email_subject",
										'email_first_line' => "$db_email_first_line",
										'enable' => "$db_enable"
										);
										//print_r($data);
									$format = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'); 
							$insertrow = $wpdb->insert( $table_name_notify, $data, $format );
						}
					}			
				}
				//clean up options array $wprevpro_notifications_settings = get_option('wprevpro_notifications_settings');
				if(isset($wprevpro_notifications_settings['api_key'])){
					$new_wprevpro_notifications_settings['api_key'] = $wprevpro_notifications_settings['api_key'];
					$new_wprevpro_notifications_settings['auto_lang_code'] = $wprevpro_notifications_settings['auto_lang_code'];
				} else {
					$new_wprevpro_notifications_settings['api_key'] = '';
					$new_wprevpro_notifications_settings['auto_lang_code'] = '';
				}
				update_option('wprevpro_notifications_settings',$new_wprevpro_notifications_settings);
				
			}
			//=========================================
			
			update_option( $this->_token . '_current_db_version', $this->version );
		}
		
	} // End _log_version_number ()
	
	
	//used to remove directories
	public function wpprorev_rmrf( $dir )
	{
		foreach ( glob( $dir ) as $file ) {
			if ( is_dir( $file ) ) {
				$this->wpprorev_rmrf( "{$file}/*" );
				rmdir( $file );
			} else {
				unlink( $file );
			}
		}
	}
	
	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->version );
	} // End _log_version_number ()

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Review_Pro_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Review_Pro_i18n. Defines internationalization functionality.
	 * - WP_Review_Pro_Admin. Defines all hooks for the admin area.
	 * - WP_Review_Pro_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-review-slider-pro-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-review-slider-pro-i18n.php';

		/**
		 * The classes responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-review-slider-pro-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-review-slider-pro-admin_hooks.php';
		
		/**
		 * The class responsible for parsing yelp and tripadvisor pages
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wppro_simple_html_dom.php';
		
		/**
		 * The class responsible for making tritter calls
		 */
		 //autoload correct classes
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/twitteroauth/autoload.php';


		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-review-slider-pro-public.php';
		
		/**
		 * The class responsible for the widget admin and public
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-review-slider-pro-widget.php';
		

		//register the loader
		$this->loader = new WP_Review_Pro_Loader();
		

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WP_Review_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Review_Pro_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WP_Review_Pro_Admin( $this->get_token(), $this->get_version() );
		$plugin_admin_hooks = new WP_Review_Pro_Admin_Hooks( $this->get_token(), $this->get_version() );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// register our wppro_airbnb_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wppro_airbnb_settings_init');
		
		// register our wppro_vrbo_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wppro_vrbo_settings_init');
		
		// register our wppro_woo_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wppro_woo_settings_init');
		
		// register our wprevpro_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wprevpro_settings_init');
		
		// register our wprevpro_yelp_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wprevpro_yelp_settings_init');
		
		// register our wprevpro_tripadvisor_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wprevpro_tripadvisor_settings_init');
		
		// register our wprevpro_google_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wprevpro_google_settings_init');
		
		// register our wprevpro_notifications_settings_init to the admin_init action hook, add setting inputs
		$this->loader->add_action('admin_init', $plugin_admin, 'wprevpro_notifications_settings_init');

		//add menu page
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_pages' );
		
		//the rest located in hooks file-------------

		//add ajax for adding FB feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_get_results', $plugin_admin_hooks, 'wpfb_process_ajax' );
		
		//add ajax for saving fb cron page user option
		$this->loader->add_action( 'wp_ajax_wpfbcron_update_useropt', $plugin_admin_hooks, 'wpfb_process_ajax_cron_page' );
		
		//add ajax for adding google reviews to table
		$this->loader->add_action( 'wp_ajax_wpfbr_google_reviews', $plugin_admin_hooks, 'wpfbr_ajax_google_reviews' ); 
		
		//add ajax for adding yelp feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_yelp_reviews', $plugin_admin_hooks, 'wprevpro_ajax_download_yelp_master' );
		
		//add ajax for adding tripadvisor feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_tripadvisor_reviews', $plugin_admin_hooks, 'wprevpro_ajax_download_tripadvisor_master' );
		
		//add ajax for adding airbnb feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_airbnb_reviews', $plugin_admin_hooks, 'wprevpro_ajax_download_airbnb_master' );
		
		//add ajax for adding vrbo feedback to table
		$this->loader->add_action( 'wp_ajax_wpfb_vrbo_reviews', $plugin_admin_hooks, 'wprevpro_ajax_download_vrbo_master' );

		//add ajax for hiding and deleting reviews in table
		$this->loader->add_action( 'wp_ajax_wpfb_hide_review', $plugin_admin_hooks, 'wpfb_hidereview_ajax' ); 

		//add ajax for hiding and deleting reviews in table
		$this->loader->add_action( 'wp_ajax_wpfb_find_reviews', $plugin_admin_hooks, 'wpfb_getreviews_ajax' ); 		
		
		//replaces insert into post text on media uploader when uploading reviewer avatar
		$this->loader->add_action( 'admin_init', $plugin_admin_hooks, 'wprevpro_media_text' );
		
		//add woo download when button pushed
		$this->loader->add_action( 'admin_init', $plugin_admin_hooks, 'wprevpro_download_woo' ); 
		
		//add ajax for updating all avatars on server
		$this->loader->add_action( 'wp_ajax_wpfb_update_avatars', $plugin_admin_hooks, 'wpfb_getavatars_ajax' ); 

		//for exporting csv file of all templates   add_action( 'admin_post_print.csv', 'print_csv' );
		$this->loader->add_action( 'admin_post_print.csv', $plugin_admin_hooks, 'print_csv' ); 
		
		//for exporting csv file of all reviews   add_action( 'admin_post_print_reviews.csv', 'printreviews_csv' );
		$this->loader->add_action( 'admin_post_printreviews.csv', $plugin_admin_hooks, 'printreviews_csv' );

		//for exporting csv file of all badges   add_action( 'admin_post_print.csv', 'print_csv_badges' );
		$this->loader->add_action( 'admin_post_print_badges.csv', $plugin_admin_hooks, 'print_csv_badges' ); 
		
		//for exporting csv file of all forms
		$this->loader->add_action( 'admin_post_print_forms.csv', $plugin_admin_hooks, 'print_csv_forms' ); 
		
		//add ajax for saving form
		$this->loader->add_action( 'wp_ajax_wprp_save_form', $plugin_admin_hooks, 'wprp_saveform_ajax' ); 
		
		//add ajax for returning pick categories html list, also works for post ids
		$this->loader->add_action( 'wp_ajax_wprp_get_cat_html', $plugin_admin_hooks, 'wprp_getcategories_ajax' );
		
		//action for updating, deleting, or inserting a woocommerce comment in to the review table
		$this->loader->add_action( 'comment_post', $plugin_admin_hooks, 'wprevpro_woo_iud_comment',10,2 );	//fired after comment inserted
		$this->loader->add_action( 'edit_comment', $plugin_admin_hooks, 'wprevpro_woo_iud_comment',10,2 );	//fired after comment edited
		$this->loader->add_action( 'deleted_comment', $plugin_admin_hooks, 'wprevpro_woo_iud_comment_delete',10,2 );	//fired right after comment deleted
		$this->loader->add_action( 'transition_comment_status', $plugin_admin_hooks, 'wprevpro_woo_changestatus',10,3 );	//fired right before comment deleted
		
		//add ajax for downloading review funnel
		$this->loader->add_action( 'wp_ajax_wprp_revfunnel_addprofile', $plugin_admin_hooks, 'wprp_revfunnel_addprofile_ajax' );
		//add ajax for adding job profile to review scrape
		$this->loader->add_action( 'wp_ajax_wprp_revfunnel_getrevs', $plugin_admin_hooks, 'wprp_revfunnel_getrevs_ajax' ); 
		//ajax for listing past scrape jobs
		$this->loader->add_action( 'wp_ajax_wprp_revfunnel_listjobs', $plugin_admin_hooks, 'wprp_revfunnel_listjobs_ajax' );
		
		//add ajax for running language detector
		$this->loader->add_action( 'wp_ajax_wppro_run_language_detect', $plugin_admin_hooks, 'wprevpro_run_language_detect_ajax' );
		
		//analytics - ajax for getting overall chart database
		$this->loader->add_action( 'wp_ajax_wppro_get_overall_chart_data', $plugin_admin_hooks, 'wppro_get_overall_chart_data' );
		
		//add ajax for retrieving reviews from app store (itunes)
		$this->loader->add_action( 'wp_ajax_wprp_getapps_getrevs', $plugin_admin_hooks, 'wprp_getapps_getrevs_ajax' ); 
		
		//add ajax for searching for tweets
		$this->loader->add_action( 'wp_ajax_wprp_twitter_gettweets', $plugin_admin_hooks, 'wprp_twitter_gettweets_ajax' ); 
		
		//add ajax for saving tweet
		$this->loader->add_action( 'wp_ajax_wprp_twitter_savetweet', $plugin_admin_hooks, 'wprp_twitter_savetweet_ajax' ); 
		//add ajax for deleting tweet
		$this->loader->add_action( 'wp_ajax_wprp_twitter_deltweet', $plugin_admin_hooks, 'wprp_twitter_deltweet_ajax' ); 
		
		//add ajax for adding google reviews to table
		$this->loader->add_action( 'wp_ajax_wpfbr_testing_api', $plugin_admin_hooks, 'wpfbr_ajax_testing_api' ); 
				
		//===for testing====comment out when not using
		//$this->loader->add_action( 'plugins_loaded', $plugin_admin_hooks, 'testing_function' ); 
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WP_Review_Pro_Public( $this->get_token(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles',999 );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts',999 );
		
		//for using template code instead of shortcode
		$this->loader->add_action( 'wprev_pro_plugin_action', $plugin_public, 'wprevpro_slider_action_print', 10, 1 );

		//add shortcode shortcode_wprevpro_usetemplate
		$plugin_public->shortcode_wprevpro_usetemplate();
		
		//add shortcode shortcode_wprevpro_usebadge for displaying a badge
		$plugin_public->shortcode_wprevpro_usebadge();
		
		//add ajax for getting form
		$this->loader->add_action( 'wp_ajax_wprp_get_form', $plugin_public, 'wppro_getform_ajax' );
		
		//add shortcode wprevpro_useform for displaying form
		$plugin_public->shortcode_wprevpro_useform();
		
		//add action for submit form posting
		$this->loader->add_action( 'admin_post_nopriv_wprev_submission_form', $plugin_public, 'wprev_submission_form_action');
		$this->loader->add_action( 'admin_post_wprev_submission_form', $plugin_public, 'wprev_submission_form_action');

		//still checking for admin https://codex.wordpress.org/AJAX_in_Plugins
		//we have to give public visitors admin access
		if ( is_admin() ) {
			//add ajax for submit form posting through ajax
			$this->loader->add_action( 'wp_ajax_wprp_save_review', $plugin_public, 'wprp_savereview_ajax' ); 
			$this->loader->add_action( 'wp_ajax_nopriv_wprp_save_review', $plugin_public, 'wprp_savereview_ajax' );
			
			//add ajax updating missing images, only doing on review list page now
			$this->loader->add_action( 'wp_ajax_wprp_update_profile_pic', $plugin_public, 'wppro_update_profile_pic_ajax' );
			//$this->loader->add_action( 'wp_ajax_nopriv_wprp_update_profile_pic', $plugin_public, 'wppro_update_profile_pic_ajax' );
			
			//add ajax for getting the load more reviews, called when someone clicks the load more button
			$this->loader->add_action( 'wp_ajax_wprp_load_more_revs', $plugin_public, 'wppro_loadmore_revs_ajax' );
			$this->loader->add_action( 'wp_ajax_nopriv_wprp_load_more_revs', $plugin_public, 'wppro_loadmore_revs_ajax' );
		}
		
		//add shortcode shortcode_wprevpro_usefloat for displaying a floating badge or template
		$plugin_public->shortcode_wprevpro_usefloat();
		
		//add ajax for getting the float html, currently only in admin float page
		$this->loader->add_action( 'wp_ajax_wprp_get_float', $plugin_public, 'wppro_getfloat_ajax' );
		
		//add ajax for getting the float slideout html, currently only in admin float page
		$this->loader->add_action( 'wp_ajax_wprp_get_slideout_revs', $plugin_public, 'wppro_getslideout_ajax' );
		
		//add action to print float html right after footer before closing body tag
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wprp_echofloatfooter' );
		
		//add action to print badge popup or slideout html right after footer before closing body tag
		$this->loader->add_action( 'wp_footer', $plugin_public, 'wprp_echobadgepopslide' );
		

	}
	

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
	
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_token() {
		return $this->_token;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WP_Review_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
