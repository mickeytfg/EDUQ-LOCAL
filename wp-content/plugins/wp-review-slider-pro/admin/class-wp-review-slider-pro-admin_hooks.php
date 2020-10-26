<?php
/**
 * The admin-specific hooks functionality of the plugin. Specialty hooks.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/admin
 */

/**
 * The admin-specific hooks functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/admin
 * @author     Your Name <email@example.com>
 */
class WP_Review_Pro_Admin_Hooks {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugintoken    The ID of this plugin.
	 */
	private $plugintoken;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugintoken       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugintoken, $version ) {

		$this->_token = $plugintoken;
		$this->version = $version;
		$this->dbversion = $version;
		$this->_default_api_token = "AIzaSyD8b2Kbs86gaCmDJNQpj8wE28xgegSYNd8";
		//for testing==============
		$this->version = time();
		//===================
				

	}

	//--======================= GOOGLE =======================--//
	public function wpfbr_ajax_google_reviews()
	{
		global $wpdb, $current_user;
		//get_currentuserinfo();

		if(!is_user_logged_in())  
		{
			$out = __('User not logged in','wp-review-slider-pro');
			//header( "Content-Type: application/json" );
			echo $out;
			die();
		}
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		if(!defined('DOING_AJAX')) define('DOING_AJAX', 1);
		
		if (strpos(@ini_get('disable_functions'), 'set_time_limit') === false) {
			@set_time_limit(3600);
		}

		$options = get_option('wpfbr_google_options');

		//if( empty( $options['google_api_key'] )){
		//	_e('Google Places API Key not found.');
		//	die();
		//}

		if( empty( $options['google_location_set']['place_id'] )){
			_e('There is no location set. Please search and select location to get reviews.','wp-review-slider-pro');
			die();
		}
		
		echo $this->get_google_reviews( $options );
		
		die();
	}

	
	
	public function get_google_reviews( $options = array(),$iscron = false )
	{
		global $wpdb;

		if( empty( $options ) )
			$options = get_option('wpfbr_google_options');
		
		if( empty( $options['google_api_key'] ) ){
			$google_api_key = $this->_default_api_token;
		} else {
			$google_api_key = $options['google_api_key'];
		}
		if(isset($options['select_google_api'])){
			if($options['select_google_api']=='default'){
				$google_api_key = $this->_default_api_token;
			} else if ($options['select_google_api']=='mine'){
				$google_api_key = $options['google_api_key'];
			}
		}

		//loop here for each place set
		$placeidarray = [];
		if($options['google_location_set']['place_id']){
			$placeidarray[]=$options['google_location_set']['place_id'];
		}
		
		$tempoptions = get_option('wpfbr_google_options');
		if(!isset($tempoptions['google_business_location_total'])){
			//first time seeing this find total of locations currently being used and set to that. if only using one then set to zero
			$numinuse = 1;
			for ($x = 2; $x <= 4; $x++) {
				if($tempoptions["google_location_set$x"]["place_id"]!=''){
					$numinuse = $x;
				}
			}
			$tempoptions['google_business_location_total']=$numinuse;
			update_option('wpfbr_google_options',$tempoptions);
		}

		$loopnum = $options['google_business_location_total'];
		
		if($loopnum < 2){
			$loopnum = 15;
		}

		
		
		for ($x = 2; $x <= $loopnum; $x++) {
			if(isset($options["google_location_set$x"]) && $options["google_location_set$x"]["place_id"]) {
				$placeidarray[]=$options["google_location_set$x"]["place_id"];
			} 
		}

		$urlnum = 1;
		
		
		foreach ($placeidarray as &$placeidvalue) {
			$haserror= false;
			if($placeidvalue!=''){
				
						
				$google_places_url = add_query_arg(
					array(
						'placeid' => trim($placeidvalue),
						'key'     => trim($google_api_key),
						'language' => trim($options['google_language_option']),
					),
					'https://maps.googleapis.com/maps/api/place/details/json'
				);
				
				$response = $this->get_reviews( $google_places_url );

				//Error message from google;
				if ( ! empty( $response->error_message ) ) 
				{
					//return '<strong>'.$response->status.'</strong>: '.$response->error_message;
					echo sprintf( __('Place %d :Google Error: %d, %s, <a href="%s" target="_blank">more info</a> </br>', 'wp-review-slider-pro' ), $urlnum, $response->status, $response->error_message, $google_places_url );
					$haserror = true;
				} 
				//Error message from wordpress//
				elseif ( isset( $response['error_message'] ) && ! empty( $response['error_message'] ) ) 
				{
					echo sprintf( __('Place %d :WP Error: %d, %s  <a href="%s" target="_blank">Google Result</a> </br>', 'wp-review-slider-pro' ), $urlnum ,$response['status'],$response['error_message'], $google_places_url  );
					$haserror = true;
					//return '<strong>' . $response['status'] . '</strong>: ' . $response['error_message'];
				}
				//-----------------------------------------------
				
				//save business url in options
				$options['google_url'] =$response['result']['url'];
				$options['google_name'] =$response['result']['name'];
				$options['google_address'] =$response['result']['formatted_address'];
				if($urlnum>1){
					$options["google_location_set$urlnum"]["avg_reviews"] =$response['result']['rating'];
				} else {
					$options["google_location_set"]["avg_reviews"] =$response['result']['rating'];
				}

				update_option( 'wpfbr_google_options', $options );

				$stats = array();
				$table_name = $wpdb->prefix . 'wpfb_reviews';

				//foreach element in $arr
				foreach( $response['result']['reviews'] as $item)
				{
					//only enter reviews with ratings more than X;
					if( ! empty( $options['google_location_minrating'] )&& ! empty($item['rating'])&& (int)$options['google_location_minrating'] > (int)$item['rating'])
						continue;

					//check to see if row is in db already
					$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$item['time']."' " );

					
					//if( empty( $checkrow ) )
					//{
						$review_text = $item['text'];
						//$review_length = str_word_count($item['text']);
						//if($review_length <2 && $review_text !=""){		//fix for other language error
							$review_length = substr_count($review_text, ' ');
						//}
						if (extension_loaded('mbstring')) {
							$review_length_char = mb_strlen($review_text);
						} else {
							$review_length_char = strlen($review_text);
						}
						//get reviewer id from author url so we can display on front end
						$intreviewer_id = filter_var($item['author_url'], FILTER_SANITIZE_NUMBER_INT);
						
						$temppagename = $response['result']['name']. ' '.$urlnum;
						
						//last name option to not save in db
						$user_name = $item['author_name'];
						if(isset($options['google_last_name_option'])){
							if($options['google_last_name_option']!='full'){
								$lastnamesaveoption = $options['google_last_name_option'];
								$user_name =$this->changelastname($user_name, $lastnamesaveoption);
							}
						}
						
						$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$user_name."' AND type = 'Google' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$item['time']."')" );
						
						if( empty( $checkrow ) )
						{						
						$stats[] =array(
							'pageid' 			=> $response['result']['place_id'], 
							'pagename' 			=> $temppagename, 
							'created_time' 		=> date( "Y-m-d H:i:s", $item['time'] ),
							'created_time_stamp' 	=> $item['time'],
							'reviewer_name' 		=> $user_name,
							'reviewer_id' 		=> $intreviewer_id,
							'rating' 			=> $item['rating'],
							'review_text' 		=> $item['text'],
							'hide' 			=> '',
							'review_length' 		=> $review_length,
							'review_length_char' => $review_length_char,
							'type' 			=> 'Google',
							'userpic'			=> $item['profile_photo_url'],
							'from_url' =>$response['result']['url'],
							'language_code' =>$item['language']
						);
						}
					//}
					$review_length='';
					$review_length_char='';
				}
				$i = 0;
				$insertnum = 0;
				foreach ( $stats as $stat ){
					$insertnum = $wpdb->insert( $table_name, $stat );
					$i=$i + 1;
				}
				
				//update totals and averages
				if(isset($response['result']['place_id'])){
				$this->updatetotalavgreviews('google', $response['result']['place_id'], $response['result']['rating'], $response['result']['user_ratings_total'] );
				}
				
				//send $reviews array to function to send email if turned on.
				if(count($stats)>0 && $insertnum>0){
						$this->sendnotificationemail($stats, "google");
				}
				//--------------------------------
				if($haserror==false){
					if($i==0){
						echo sprintf( __('Place %d : No new reviews found.</br>', 'wp-review-slider-pro' ), $urlnum );
					} else {
						echo sprintf( __('Place %d : %d Reviews inserted.</br>', 'wp-review-slider-pro' ), $urlnum ,$i );
					}
				}
				ob_flush();
				flush();

				//sleep(1);
				
				$urlnum++;
			}
		}	//end for loop of placeid array
		
		if($iscron==false){
			die();
		}
	}


	/**
	 * cURL (wp_remote_get) the Google Places API
	 *
	 * @description: CURLs the Google Places API with our url parameters and returns JSON response
	 *
	 * @param $url
	 *
	 * @return array|mixed
	 */
	function get_reviews( $url ) 
	{
		//Sanitize the URL
		$url = esc_url_raw( $url );

		//echo $url;
		// Send API Call using WP's HTTP API
		//$data = wp_remote_get( $url );
		
		$data = wp_remote_get($url, array(
		  'timeout' => 20,
		  'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0'
		));

		if ( is_wp_error( $data ) ) 
		{
			$response['error_message'] 	= $data->get_error_message();
			$reponse['status'] 		= $data->get_error_code();
			return $response;
		}
		$response = json_decode( $data['body'], true );

		if( ! ( isset( $response['result']['reviews'] ) && ! empty( $response['result']['reviews'] ) ) )
		{
			$response['error_message'] 	= __('No Google Reviews Found.','wp-review-slider-pro');
			$reponse['status'] 		= __LINE__;
			$response['result']['reviews'] = [];
			return $response;
		}

		//Get Reviewers Avatars
		$response = $this->get_reviewers_avatars( $response );

		//Google response data
		return $response;
	}

	/**
	 * Get Reviewers Avatars
	 *
	 * Get avatar from Places API response or provide placeholder.
	 *
	 * @return array
	 */
	function get_reviewers_avatars( $response ) 
	{
		// Includes Avatar image from user.
		if ( isset( $response['result']['reviews'] ) && ! empty( $response['result']['reviews'] ) ) 
		{
			// Loop Google Places reviews.
			foreach( $response['result']['reviews'] as $i => $review ) {
				// Check to see if image is empty (no broken images).
				if ( ! empty( $review['profile_photo_url'] ) ) {
					$avatar_img = $review['profile_photo_url'] . '?sz=100';
				} else {
					$avatar_img = WPREV_PLUGIN_URL . '/public/css/imgs/mystery-man.png';
				}
				$response['result']['reviews'][$i]['profile_photo_url'] = $avatar_img;
			}
		}
		return $response;
	}	
	
	
		//ajax for testing the api key
	public function wpfbr_ajax_testing_api(){
		//echo "here";
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$apikey = $_POST['apikey'];
		
		$goodkey = false;
		
		//remote get the autocomplete first
		//https://maps.googleapis.com/maps/api/place/autocomplete/json?input=1600+Amphitheatre&key=<API_KEY>		
		$url = "https://maps.googleapis.com/maps/api/place/autocomplete/json?input=1600+Amphitheatre&key=".$apikey;
		
		//echo $url;
		
		$data = wp_remote_get( $url );

		if ( is_wp_error( $data ) ) 
		{
			$response['error_message'] 	= $data->get_error_message();
			$reponse['status'] 		= $data->get_error_code();
			print_r($response);
		}
		$response = json_decode( $data['body'], true );
		
		if(isset($response['predictions'][0]['id'])){
			//autocomplete is working
			echo "- Autocomplete is working.<br>";
			$goodkey = true;
		} else {
			//key not good
			echo "- Something is wrong with this Google API Key. Error from Google...<br><br>";
			print_r($response);
		}
		
		if($goodkey){
				//remote get place if passed outcomplete
				$url = "https://maps.googleapis.com/maps/api/place/details/json?placeid=ChIJC8DB3J5sYogRV8b_lTk20U4&key=".$apikey;
				$data = wp_remote_get( $url );

				if ( is_wp_error( $data ) ) 
				{
					$response['error_message'] 	= $data->get_error_message();
					$reponse['status'] 		= $data->get_error_code();
					print_r($response);
				}
				$response = json_decode( $data['body'], true );
				
				if(isset($response['result']['name'])){
					//place lookup is working
					echo "- Place Look-up is working.<br><br>";
					echo "- This key should be good to go. Make sure to click Save Settings at the bottom.<br><br>";
				} else {
					echo "- Something is wrong with this Google API Key. Error from Google...<br><br>";
					print_r($response);
				}
		}
		die();
				
	}
	
	//--======================= end GOOGLE =======================--//
	
	
	//=========================Facebook==============================//
	
	/**
	 * Store reviews in table, called from javascript file admin.js for fb reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_process_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$postreviewarray = $_POST['postreviewarray'];
		
		$getresponse = $this->wpfb_process_ajax_go( $postreviewarray );
		
		echo $getresponse;
		
		die();
	}
	
	//for last name options of FB page, called from below
	public function formatlastname($tempreviewername){
		//what to do with last name
		$option = get_option('wprevpro_options');
		if(isset($option['fb_last_name_option'])){
			$lastnameoption = $option['fb_last_name_option'];
			if(isset($lastnameoption)){
				//make sure php mb extension is loaded
				if (extension_loaded('mbstring')) {
					$words = mb_split("\s", $tempreviewername);
				} else {
					$words = explode(" ", $tempreviewername);
				}
				if($lastnameoption=="nothing"){
					$tempreviewername=$words[0];
				} else if($lastnameoption=="initial"){
					$tempfirst = $words[0];
					if(isset($words[1])){
						$templast = $words[1];
						if (extension_loaded('mbstring')) {
						$templast =mb_substr($templast,0,1);
						} else {
							$templast =substr($templast,0,1);
						}
						$tempreviewername = $tempfirst.' '.$templast.'.';
					} else {
						$tempreviewername = $tempfirst;
					}
				}
			}
		}
		return $tempreviewername;
	}
	
	//for encoding emojis if needed
	public function wprev_maybe_encode_emoji( $string ) {
		global $wpdb;
		$db_charset = $wpdb->charset;
		if ( 'utf8mb4' != $db_charset ) {
			if ( function_exists('wp_encode_emoji') && function_exists( 'mb_convert_encoding' ) ) {
				$string = wp_encode_emoji( $string );
			}
		}
		return $string;
	 }

 
	public function wpfb_process_ajax_go($postreviewarray){
		//loop through each one and insert in to db  
		global $wpdb;
		$db_charset = $wpdb->charset;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$foundone = 0;
		$stats = array();

		foreach($postreviewarray as $item) { //foreach element in $arr
			$pageid = $item['pageid'];
			$pagename = $item['pagename'];
			//$pagename = str_replace("%27","'",$pagename);
			$created_time = $item['created_time'];
			$created_time_stamp = strtotime($created_time);
			//fix for admin timezone offset in hours
			$timezoneoffset = get_option('gmt_offset')*60*60;
			$created_time_stamp = $created_time_stamp + $timezoneoffset;
			$created_time = date ("Y-m-d H:i:s", $created_time_stamp);
			$reviewer_name = $item['reviewer_name'];
			$reviewer_name = $this->formatlastname( $reviewer_name );
			$reviewer_id = $item['reviewer_id'];
			$reviewer_imgurl = $item['reviewer_imgurl'];
			if(array_key_exists('rating', $item) && $item['rating']){
				$rating = $item['rating'];
			} else {
				$rating ="";
			}
			if(array_key_exists('recommendation_type', $item) && $item['recommendation_type']){
				$recommendation_type = $item['recommendation_type'];
			} else {
				$recommendation_type ="";
			}
			$review_text = $item['review_text'];
			//check if we need to encode emojis
			if ( 'utf8mb4' != $db_charset ) {
			$review_text = $this->wprev_maybe_encode_emoji( $review_text );
			}

			$review_length = substr_count($review_text, ' ');
			if (extension_loaded('mbstring')) {
				$review_length_char = mb_strlen($review_text);
			} else {
				$review_length_char = strlen($review_text);
			}

			$rtype = $item['type'];
			
			//option for saving positive recommendation_type as 5 start
			$option = get_option('wprevpro_options');
			if(isset($option['fb_recommendation_to_star'])){
				if($option['fb_recommendation_to_star'] =='1'){
					if($rating=='' && $recommendation_type=="positive"){
						$rating=5;
					}
					if($rating=='' && $recommendation_type=="negative"){
						$rating=2;
					}
				}
			}
			
			//check to see if row is in db already
			$checkrow = $wpdb->get_row( "SELECT id FROM ".$table_name." WHERE reviewer_id = '$reviewer_id'" );
			if ( null === $checkrow ) {
				$unixtimestamp = strtotime($created_time);
				$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$reviewer_name."' AND type = '".$rtype."' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
			}
			
			if ( null === $checkrow ) {
				if($reviewer_id!=''){
				$stats[] =array(
						'pageid' => $pageid, 
						'pagename' => $pagename, 
						'created_time' => $created_time,
						'created_time_stamp' => strtotime($created_time),
						'reviewer_name' => $reviewer_name,
						'reviewer_id' => $reviewer_id,
						'rating' => $rating,
						'recommendation_type' => $recommendation_type,
						'review_text' => $review_text,
						'hide' => '',
						'review_length' => $review_length,
						'review_length_char' => $review_length_char,
						'type' => $rtype,
						'userpic' => $reviewer_imgurl
					);
				}
			} else {
				//$foundone = 1;
			}
		}
		$i = 0;
		$insertnum = 0;
		
		//print_r($stats);
		
		foreach ( $stats as $stat ){
			$insertnum = $wpdb->insert( $table_name, $stat );
			$i=$i + 1;
		}
			//send $reviews array to function to send email if turned on.
			if(count($stats)>0){
				$this->sendnotificationemail($stats, "facebook");

			}
			//--------------------------------
	
		$insertid = $wpdb->insert_id;
		
		//call function to update total reviews and avg based on what we have in db for this.
		//only going to call this once since we have to pull all reviews and get from db
		$updatetotalavgreviews = $this->updatetotalavgreviews('facebook', $pageid, '', '' );
		
		return $insertnum."-".$insertid."-".$i."-".$foundone;
	}
	
/**
	 * Store user options for fb cron job admin.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_process_ajax_cron_page(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$postpageid = $_POST['pageid'];
		$postaddtocron = $_POST['addtocron'];
		//$postauthtoken = $_POST['authtoken'];
		
		//first save authtoken, this is so user doesn't have to hit save button again
		//$wprevpro_options_new = get_option('wprevpro_options' );
		//$wprevpro_options_new['fb_user_token_field_display']=$postauthtoken;
		//update_option( 'wprevpro_options', $wprevpro_options_new);
		
		$option = 'wpfb_cron_pages';
		
		//get existing option array of fb cron pages
		$fbcronpagesarray = get_option( $option );
		if(isset($fbcronpagesarray)){
			$fbcronpagesarray = json_decode($fbcronpagesarray, true);
		} else {
			$fbcronpagesarray = array();
		}
		
		if($postaddtocron=='yes'){
			//add this pageid to option
			$fbcronpagesarray[] = $postpageid;
		} else {
			//remove this page id from option
			$fbcronpagesarray = array_diff($fbcronpagesarray, array($postpageid));
		}
		
		//reset array index
		$fbcronpagesarray = array_values($fbcronpagesarray);
		
		//update option in db
		$new_value = json_encode($fbcronpagesarray, JSON_FORCE_OBJECT);
		update_option( $option, $new_value);
		
		echo $postpageid;
		echo $postaddtocron;
		echo $new_value;

		die();
	}

    /**
	 * check for new reviews of fb pages with cron job checked. 
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_get_fb_reviews_cron() {
      global $pagenow;
	  //$foreverpagetokens = get_option('wprevpro_forever_pagetokens');
	  //$foreverpagetokensarray = json_decode($foreverpagetokens, true);

	  $pagestocron = get_option('wpfb_cron_pages');
	  $fbcronpagesarray = json_decode($pagestocron, true);
	  
	  $option = get_option('wprevpro_options');
	  $accesscode = $option['fb_app_code'];
	  //must have long lasting page tokens
	  //print_r($fbcronpagesarray);
	  
	  //loop through each page
	  $n=1;
		foreach($fbcronpagesarray as $x => $pageid) {

				//made it this far now try to grab reviews
				$tempurl = "https://fbapp.ljapps.com/ajaxgetpagerevs.php?rlimit=10&q=getrevs&acode=".$accesscode."&afterc=&callback=cron&pid=".$pageid;
				//echo $tempurl."<br>";
				
				//first try wp_remote_get
				//$data = wp_remote_get( $tempurl );
				//if( is_wp_error( $data ) ) {
					if (ini_get('allow_url_fopen') == true) {
						$data=file_get_contents($tempurl);
					} else if (function_exists('curl_init')) {
						$data=$this->file_get_contents_curl($tempurl);
					}
				//}
				// If the response is an array, it's coming from wp_remote_get,
				// so we just want to capture to the body index for json_decode.
				//if( is_array( $data ) ) {
				//	$data = $data['body'];
				//}
				
				//$data = file_get_contents($tempurl);
				//echo($data)."<br>";;
				$data = json_decode($data, true);
				//print_r($data);
				$reviewdata = $data['data'];
				//print_r($reviewdata);
				
					if (is_array($reviewdata)){
					//put data in to another array and pass to function
					$arrlength = count($reviewdata);
					//echo "<br>length:".$arrlength;
					for($x = 0; $x < $arrlength; $x++) {
						$reviewarray[$x]['pageid']=$pageid;
						$reviewarray[$x]['pagename']=$reviewdata[$x]['pagename'];
						$reviewarray[$x]['created_time']=$reviewdata[$x]['created_time'];
						$reviewarray[$x]['reviewer_name']=$reviewdata[$x]['reviewer']['name'];
						$reviewarray[$x]['reviewer_id']=$reviewdata[$x]['reviewer']['id'];
						if(isset($reviewdata[$x]['rating'])){
							$reviewarray[$x]['rating']=$reviewdata[$x]['rating'];
						} else {
							$reviewarray[$x]['rating']='';
						}
						if(isset($reviewdata[$x]['recommendation_type'])){
							$reviewarray[$x]['recommendation_type']=$reviewdata[$x]['recommendation_type'];
						} else {
							$reviewarray[$x]['recommendation_type']='';
						}
						if(isset($reviewdata[$x]['review_text'])){
							$reviewarray[$x]['review_text']=$reviewdata[$x]['review_text'];
						} else {
							$reviewarray[$x]['review_text']='';
						}
						if(isset($reviewdata[$x]['reviewer']['imgurl'])){
							$reviewarray[$x]['reviewer_imgurl']=$reviewdata[$x]['reviewer']['imgurl'];
						} else {
							$reviewarray[$x]['reviewer_imgurl']='';
						}
						
						$reviewarray[$x]['type']="Facebook";
					}
					//save them to db
					//print_r($reviewarray);
					if (is_array($reviewarray)){
						$savereviews = $this->wpfb_process_ajax_go( $reviewarray );
						//unset array
						foreach ($reviewarray as $key => $value) {
							unset($reviewarray[$key]);
						}
					}
				}
				
			
			$n++;
		}
	  
	
	}
		
	
	//======================End Facebook========================//
	/**
	 * Hides or deletes reviews in table, called from javascript file wprevpro_review_list_page.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_hidereview_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$rid = intval($_POST['reviewid']);
		$myaction = $_POST['myaction'];
		$newsw = $_POST['sortweight'];

		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		//grab review and see if it is hidden or not
		$myreview = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $rid" );
		
		//check to see if we are deleting or just hiding or showing
		if($myaction=="hideshow"){
			
			//pull array from options table of yelp hidden
			$yelphidden = get_option( 'wprevpro_hidden_reviews' );
			if(!$yelphidden){
				$yelphiddenarray = array('');
			} else {
				$yelphiddenarray = json_decode($yelphidden,true);
			}
			if(!is_array($yelphiddenarray)){
				$yelphiddenarray = array('');
			}
			$this_yelp_val = $myreview->reviewer_name."-".$myreview->created_time_stamp."-".$myreview->review_length."-".$myreview->type."-".$myreview->rating;

			if($myreview->hide=="yes"){
				//already hidden need to show
				$newvalue = "";
				
				//remove from $yelphidden
				if(($key = array_search($this_yelp_val, $yelphiddenarray)) !== false) {
					unset($yelphiddenarray[$key]);
				}
				
			} else {
				//shown, need to hide
				$newvalue = "yes";
				
				//need to update Yelp hidden ids in options table here array of name,time,count,type
				 array_push($yelphiddenarray,$this_yelp_val);
			}
			//update hidden yelp reviews option, use this when downloading yelp reviews so we can re-hide them each download
			$yelphiddenjson=json_encode($yelphiddenarray);
			update_option( 'wprevpro_hidden_reviews', $yelphiddenjson );
			
			//update database review table to hide this one
			$data = array( 
				'hide' => "$newvalue"
				);
			$format = array( 
					'%s'
				); 
			$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $rid ), $format, array( '%d' ));
			if($updatetempquery>0){
				echo $rid."-".$myaction."-".$newvalue;
			} else {
				echo $rid."-".$myaction."-fail";
			}
			
			//update the total and average review here.
			$this->updatetotalavgreviews('submitted', trim($myreview->pageid), '', '' );

		}
		if($myaction=="deleterev"){
			$deletereview = $wpdb->delete( $table_name, array( 'id' => $rid ), array( '%d' ) );
			if($deletereview>0){
				echo $rid."-".$myaction."-success";
				//delete this local avatar and cache
				$filename = $myreview->created_time_stamp.'_'.$myreview->id.'.jpg';
				//$localfile = plugin_dir_path(dirname(__FILE__)).'public/partials/avatars/'.$filename;
				$img_locations_option = json_decode(get_option( 'wprev_img_locations' ),true);
				$avatar_dir = $img_locations_option['upload_dir_wprev_avatars'];
				$localfile = $avatar_dir.$filename;
				@unlink($localfile);
				
			} else {
				echo $rid."-".$myaction."-fail";
			}
		}
		if($myaction=="updatesw"){
			//update the sortweight
			$data = array( 
				'sort_weight' => "$newsw"
				);
			$format = array( 
					'%d'
				); 
			$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $rid ), $format, array( '%d' ));
			if($updatetempquery>0){
				echo $rid."-".$myaction."-success";
			} else {
				echo $rid."-".$myaction."-fail";
			}
		}

		die();
	}
	
	/**
	 * Ajax, retrieves reviews from table, called from javascript file wprevpro_templates_posts_page.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_getreviews_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
	$hidepagination = false;
	$hidesearch = false;
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$filtertext = htmlentities($_POST['filtertext']);
		$filterrating = htmlentities($_POST['filterrating']);
		$filterrating = intval($filterrating);
		$filtertype = htmlentities($_POST['filtertype']);
		$filterlang = htmlentities($_POST['filterlang']);
		$filterpage = htmlentities($_POST['filterpageid']);
		$curselrevs = $_POST['curselrevs'];
		
		//perform db search and return results
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$rowsperpage = 20;
		
		//pagenumber
		if(isset($_POST['pnum'])){
		$temppagenum = $_POST['pnum'];
		} else {
		$temppagenum ="";
		}
		if ( $temppagenum=="") {
			$pagenum = 1;
		} else if(is_numeric($temppagenum)){
			$pagenum = intval($temppagenum);
		}
		
		//sort direction
		if($_POST['sortdir']=="ASC" || $_POST['sortdir']=="DESC"){
			$sortdir = $_POST['sortdir'];
		} else {
			$sortdir = "DESC";
		}

		//make sure sortby is valid
		if(!isset($_POST['sortby'])){
			$_POST['sortby'] = "";
		}
		$allowed_keys = ['created_time_stamp', 'reviewer_name', 'rating', 'recommendation_type', 'review_length', 'pagename', 'type' , 'hide', 'company_name'];
		$checkorderby = sanitize_key($_POST['sortby']);
	
		if(in_array($checkorderby, $allowed_keys, true) && $_POST['sortby']!=""){
			$sorttable = $_POST['sortby']. " ";
		} else {
			$sorttable = "created_time_stamp ";
		}
		if($_POST['sortdir']=="ASC" || $_POST['sortdir']=="DESC"){
			$sortdir = $_POST['sortdir'];
		} else {
			$sortdir = "DESC";
		}
		
		//get reviews from db
		$lowlimit = ($pagenum - 1) * $rowsperpage;
		$tablelimit = $lowlimit.",".$rowsperpage;
		
		if($filterrating>0){
			$filterratingtext = "rating = ".$filterrating;
		} else {
			$filterratingtext = "rating > -1";
		}
		
		//filter by type
		if($filtertype!='all'){
			$filtertypetext = " AND type = '".$filtertype."' ";
		} else {
			$filtertypetext = "";
		}
		
		//filter by language_code
		if($filterlang!='all'){
			if($filterlang=='unset'){
				$filterlangtext = " AND language_code = '' ";
			} else if($filterlang==''){
				$filterlangtext = "";
			} else {
				$filterlangtext = " AND language_code = '".$filterlang."' ";
			}
		} else {
			$filterlangtext = "";
		}
		
		//filter by pageId
		$filterpagetext = "";
		if($filterpage!='all'){
			if($filterpage==''){
				$filterpagetext = "";
			} else {
				$filterpagetext = " AND pageid = '".$filterpage."' ";
			}
		}
		
			
		//check to see if looking for previously selected only
		if (is_array($curselrevs)){
			$query = "SELECT * FROM ".$table_name." WHERE id IN (";
			//loop array and add to query
			$n=1;
			foreach ($curselrevs as $value) {
				if($value!=""){
					if(count($curselrevs)==$n){
						$query = $query." $value";
					} else {
						$query = $query." $value,";
					}
				}
				$n++;
			}
			$query = $query.")";
			//echo $query ;

			$reviewsrows = $wpdb->get_results($query);
			$hidepagination = true;
			$hidesearch = true;
		} else {
		

			//if filtertext set then use different query
			if($filtertext!=""){
				$reviewsrows = $wpdb->get_results("SELECT * FROM ".$table_name."
					WHERE (reviewer_name LIKE '%".$filtertext."%' or review_text LIKE '%".$filtertext."%') AND ".$filterratingtext.$filtertypetext.$filterlangtext.$filterpagetext."
					ORDER BY ".$sorttable." ".$sortdir." 
					LIMIT ".$tablelimit." "
				, ARRAY_A);
				$hidepagination = true;
			} else {
				$reviewsrows = $wpdb->get_results(
					$wpdb->prepare("SELECT * FROM ".$table_name."
					WHERE id>%d AND ".$filterratingtext.$filtertypetext.$filterlangtext.$filterpagetext."
					ORDER BY ".$sorttable." ".$sortdir." 
					LIMIT ".$tablelimit." ", "0")
				, ARRAY_A);
			}
		}
		//print_r($reviewsrows);
		//die();
		// Print last SQL query string
//echo $wpdb->last_query;
// Print last SQL query result
//$wpdb->last_result;
// Print last SQL query Error
//$wpdb->last_error;
		//total number of rows
		$reviewtotalcount = $wpdb->get_var( "SELECT COUNT(*) FROM ".$table_name." WHERE id>1 AND ".$filterratingtext.$filtertypetext.$filterlangtext.$filterpagetext);
		//total pages
		$totalpages = ceil($reviewtotalcount/$rowsperpage);
		
		$reviewsrows['reviewtotalcount']=$reviewtotalcount;
		$reviewsrows['totalpages']=$totalpages;
		$reviewsrows['pagenum']=$pagenum;
		
		if($hidepagination){
			$reviewsrows['reviewtotalcount']=0;
			//$reviewsrows['totalpages']=0;
			//$reviewsrows['pagenum']=0;
		}
		if($hidesearch){
			//$reviewsrows['reviewtotalcount']=0;
			$reviewsrows['totalpages']=0;
			//$reviewsrows['pagenum']=0;
		}
		
		$results = json_encode($reviewsrows);
		echo $results;

		die();
	}

	/**
	 * Check if an item exists out there in the "ether".
	 *
	 * @param string $url - preferably a fully qualified URL
	 * @return boolean - true if it is out there somewhere
	 */
	public function webItemExists($url) {
		if (($url == '') || ($url == null)) { return false; }
		$response = wp_remote_head( $url, array( 'timeout' => 5 ) );
		$accepted_status_codes = array( 404);
		echo 'code'.wp_remote_retrieve_response_code( $response );
		if ( in_array( wp_remote_retrieve_response_code( $response ), $accepted_status_codes ) ) {
			return false;
		}
		return true;
	}	
	
	
	/**
	 * replaces insert into post text on media uploader when uploading reviewer avatar
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_media_text() {
		global $pagenow;
		if ( 'media-upload.php' == $pagenow || 'async-upload.php' == $pagenow ) {
			// Now we'll replace the 'Insert into Post Button' inside Thickbox
			add_filter( 'gettext', array($this,'replace_thickbox_text') , 1, 3 );
		}
	}
	 
	public function replace_thickbox_text($translated_text, $text, $domain) {
		if ('Insert into Post' == $text) {
			$referer = strpos( wp_get_referer(), 'wp_pro-reviews' );
			if ( $referer != '' ) {
				return __('Use as Reviewer Avatar or Logo', 'wp-review-slider-pro' );
			}
		}
		return $translated_text;
	}
	
	
	//--======================= yelp =======================--//
	
	/**
	 * download yelp reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	 
	//for ajax call to yelp master
	public function wprevpro_ajax_download_yelp_master() {
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$thisurlnum = $_POST['urlnum'];
		$getresponse = $this->wprevpro_download_yelp_master($thisurlnum);
		//echo $getresponse;
		//echo "here";
		die();
	}
	 
	 
	public function wprevpro_download_yelp_master($downloadurlnum = 'all') {
			$options = get_option('wprevpro_yelp_settings');
			
			//check to see if only downloading one here, if not that skip and continue
			if($downloadurlnum!='all'){
				if($downloadurlnum==1){
					$numurl='';
				} else {
					$numurl=$downloadurlnum;
				}
				if (filter_var($options['yelp_business_url'.$numurl], FILTER_VALIDATE_URL)) {
					$currenturlmore = $options['yelp_business_url'.$numurl];
					$this->wprevpro_download_yelp_master_perurl($currenturlmore,$numurl);
				} else {
					//$errormsg = 'Please enter a valid URL. If the URL contains international non-ASCII characters (ü) then use the encoded version. You can get it by copying the URL from the address bar and pasting in the URL field.';
					$errormsg = esc_html__('Please enter a valid URL. If the URL contains international non-ASCII characters (ü) then use the encoded version. You can get it by copying the URL from the address bar and pasting in the URL field.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
			} else {
				//make sure you have valid url, if not display message
				if (filter_var($options['yelp_business_url'], FILTER_VALIDATE_URL)) {
					//call for this url, multiple times
					$currenturl = $options['yelp_business_url'];
					$urlnum = '';
					$this->wprevpro_download_yelp_master_perurl($currenturl,$urlnum);
					
				} else {
					//$errormsg = 'Please enter a valid URL. If the URL contains international non-ASCII characters (ü) then use the encoded version. You can get it by copying the URL from the address bar and pasting in the URL field.';
					$errormsg = esc_html__('Please enter a valid URL. If the URL contains international non-ASCII characters (ü) then use the encoded version. You can get it by copying the URL from the address bar and pasting in the URL field.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
				
				$totalmorepages = $options['yelp_business_url_more'];
				for ($x = 2; $x <= $totalmorepages; $x++) {
					sleep(2);
					$numurl = $x;
					if (filter_var($options['yelp_business_url'.$numurl], FILTER_VALIDATE_URL)) {
						$currenturlmore = $options['yelp_business_url'.$numurl];
						if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
						$this->wprevpro_download_yelp_master_perurl($currenturlmore,$numurl);
						}
					}
				} 
			}

	}
	
	//for using curl instead of fopen
	private function file_get_contents_curl($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
		$data = curl_exec($ch);
		curl_close($ch);
		return $data;
	}
	

	public function wprevpro_download_yelp_master_perurl($currenturl,$urlnum) {
		//ini_set('memory_limit','256M');
		
		global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$options = get_option('wprevpro_yelp_settings');

				  
				//echo "passed both tests";
				$stripvariableurl = strtok($currenturl, '?');
				$yelpurl[1] = $stripvariableurl.'?sort_by=date_desc';
				$yelpurl[2] = $stripvariableurl.'?start=20&sort_by=date_desc';
				//$yelpurl[3] = $stripvariableurl.'?start=40&sort_by=date_desc';
				//$yelpurl[4] = $stripvariableurl.'?start=60&sort_by=date_desc';
				
				//include_once('simple_html_dom.php');
				//loop to grab pages
				$reviews = [];
				$n=1;
				

				foreach ($yelpurl as $urlvalue) {
					
					// Create DOM from URL or file
					if (ini_get('allow_url_fopen') == true) {
						$fileurlcontents=file_get_contents($urlvalue);
					} else if (function_exists('curl_init')) {
						$fileurlcontents=$this->file_get_contents_curl($urlvalue);
					} else {
						$fileurlcontents='<html><body>'.esc_html__('fopen is not allowed on this host.', 'wp-review-slider-pro').'</body></html>';
						$errormsg = $errormsg . '<p style="color: #A00;">'.esc_html__('fopen is not allowed on this host and cURL did not work either. Ask your web host to turn fopen on or fix cURL.', 'wp-review-slider-pro').'</p>';
						$this->errormsg = $errormsg;
						echo $errormsg;
						break;
					}
					
					//echo $html;
					//echo($fileurlcontents);
					//die();
					
					//$response = wp_remote_get( $urlvalue );
					//if ( is_array( $response ) ) {
					//  $header = $response['headers']; // array of http header lines
					//  $fileurlcontents = $response['body']; // use the content
					//}

					$startpos = strpos($fileurlcontents, 'reviewFeedQueryProps');
					$firstsubstring = substr($fileurlcontents,$startpos+22);
					
					$endpos = strpos($firstsubstring, ',"businessId"');
					$finalstring = substr($firstsubstring,0,$endpos);
					
					$finalstringjson = json_decode($finalstring,TRUE);
					
					//print_r($finalstringjson);
										
					//return;
					$html = wppro_str_get_html($fileurlcontents);
					
					//echo $html;
					//print_r($finalstringjson);
					//die();
					
					$pagename ='';
					//find yelp business name and add to db under pagename
					if($html->find('.biz-page-title', 0)){
						$pagename = $html->find('.biz-page-title', 0)->plaintext;
						$pagetype = 'old';
					}
					//add check
					if($pagename==''){
						if($html->find('.biz-page-title', 1)){
							$pagename = $html->find('.biz-page-title', 1)->plaintext;
							$pagetype = 'old';
						}
					}
					if($pagename==''){
						if($html->find('.heading--no-spacing__373c0__1PzQP', 0)){
						$pagename = $html->find('.heading--no-spacing__373c0__1PzQP', 0)->plaintext;
						$pagetype = 'new';
						}
					}
					
					if($pagename=='' || $pagename==' '){
						echo esc_html__('Error: Can not find page name for this URL. Please contact us or use a Review Funnel to download the reviews.', 'wp-review-slider-pro').'<br>';
						die();
					}
					
					$pagename = trim($pagename).' '.$urlnum;
					//create pageid for db
					$pageid = str_replace(" ","",$pagename);
					$pageid = str_replace("'","",$pageid);
					$pageid = str_replace('"',"",$pageid);
					
				
					//this is different depending on which yelp page type
					//===========================
					if($pagetype=='old'){
						$reviewsarray = $this->wpyelp_download_yelp_master_typeold($html,$pagename,$yelpurl,$pageid);
						$reviewstemp = $reviewsarray['reviews'];
						$reviews = array_merge($reviews, $reviewstemp);
					} else if($pagetype=='new'){
						$reviewsarray = $this->wpyelp_download_yelp_master_typenew($finalstringjson,$pagename,$yelpurl,$pageid);
						$reviewstemp = $reviewsarray['reviews'];
						$reviews = array_merge($reviews, $reviewstemp);
					} else {
						echo "Error: Page title now found. Please contact support".
						die();
					}
					//================================


					//find total and average number here and end break loop early if total number less than 50. review-count
					$avgrating ='';
					$totalreviews ='';
					//echo "here9";
					//die();
					
					if($html->find('div.biz-main-info', 0)){
						//find total number here and end break loop early if total number less than 50. review-count
						$totalreviews = $html->find('div.biz-main-info', 0)->find('span.review-count', 0)->plaintext;
						$totalreviews = intval($totalreviews);
						if (($n*20) > $totalreviews) {
										break;
								}
					} else if($html->find('p[class=lemon--p__373c0__3Qnnj text__373c0__2pB8f text-color--mid__373c0__3G312 text-align--left__373c0__2pnx_ text-size--large__373c0__1568g]', 0)){
						//find total number here and end break loop early if total number less than 50. review-count
						$totalreviews = $html->find('p[class=lemon--p__373c0__3Qnnj text__373c0__2pB8f text-color--mid__373c0__3G312 text-align--left__373c0__2pnx_ text-size--large__373c0__1568g]', 0)->plaintext;
						$totalreviews = intval($totalreviews);
						if (($n*20) > $totalreviews) {
										break;
								}
					}
					
					if($html->find('div.rating-very-large', 0)){
						$avgrating = $html->find('div.rating-very-large', 0)->title;
						$avgrating = (float)$avgrating;
					}
							
					//break here if found one already in db
					if($reviewindb == 'yes') {
						break;
					}		
							
					//sleep for random 2 seconds
					sleep(rand(0,1));
					$n++;
					
					//var_dump($reviews);
					// clean up memory
					if (!empty($html)) {
						$html->clear();
						unset($html);
					}

				}
				 
				//var_dump($reviews);
				// clean up memory
				if (!empty($html)) {
					$html->clear();
					unset($html);
				}
				
					
				//go ahead and delete first, only if we have new ones and turned on.
				if(count($reviews)>0){
					
					if($options['yelp_radio_rule']!='no'){
						$temppagename = trim($pagename);
						$wpdb->delete( $table_name, array( 'type' => 'Yelp', 'pagename' => $temppagename ) );
						$temppagename='';
					}
					//add all new yelp reviews to db
					foreach ( $reviews as $stat ){
						$insertnum = $wpdb->insert( $table_name, $stat );
					}
					
					//send $reviews array to function to send email if turned on.
					$this->sendnotificationemail($reviews,"yelp");
					
					//reviews added to db
					if(isset($insertnum)){
						$errormsg = ' '.count($reviews).' Yelp reviews downloaded.';
						$this->errormsg = $errormsg;
						
						//update avatars
						$this->wprevpro_download_img_tolocal();
						
					}
				} else {
					$errormsg = esc_html__('No new reviews found. Please note the Plugin can only return Recommended Reviews.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
				}
				echo $errormsg;
				
				//update total and average
				//echo $pageid."-".$avgrating."-".$totalreviews;
				$this->updatetotalavgreviews('yelp', trim($pageid), $avgrating, $totalreviews );

	}
	
	public function wpyelp_download_yelp_master_typeold($html,$pagename,$yelpurl,$pageid){
					
						// Find 20 reviews
					global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$options = get_option('wprevpro_yelp_settings');
					$i = 1;
			
					foreach ($html->find('div.review--with-sidebar') as $review) {
						
							if ($i > 21) {
									break;
							}
							$user_name='';
							$userimage='';
							$rating='';
							$datesubmitted='';
							$rtext='';
							$user_id='';
							// Find user_name
							if($review->find('a.user-display-name', 0)){
								$user_name = $review->find('a.user-display-name', 0)->plaintext;
								$user_id = $review->find('a.user-display-name', 0)->href;
								$user_id = substr($user_id, strpos($user_id, "userid=") + 7);
							}
							if($user_name==''){
								//try again for some international sites
								if($review->find('li.user-name', 0)){
									$user_name = $review->find('li.user-name', 0)->plaintext;
								}
							}
							if($user_id==''){
								$user_id = str_replace(" ","",$user_name);
							}
														
							// Find userimage
							if($review->find('img.photo-box-img', 0)){
								$userimage = $review->find('img.photo-box-img', 0)->src;
							}
							
							// find rating
							if($review->find('div.rating-large', 0)){
								$rating = $review->find('div.rating-large', 0)->title;
								$rating = intval($rating);
							}
							
							// find date
							if($review->find('span.rating-qualifier', 0)){
								$datesubmitted = $review->find('span.rating-qualifier', 0)->plaintext;
								$datesubmitted = str_replace(array("Updated", "review"), "", $datesubmitted);
							}
							
							// find text
							$rtext ='';
							if($review->find('div.review-content', 0)){
								$rtext = $review->find('div.review-content', 0)->find('p', 0)->plaintext;
							}
							//fix for read more tag js-content-toggleable hidden
							if(strlen($rtext)<1){
								if($review->find('div.js-expandable-comment', 0)){
								$rtext = $review->find('div.review-content', 0)->find('div.js-expandable-comment', 0)->find('span.js-content-toggleable', 1)->plaintext;
								}
							}
							
							if($rating>0){
								//$review_length = str_word_count($rtext);
								//if($review_length <2 && $rtext !=""){		//fix for other language error
									$review_length = substr_count($rtext, ' ');
								//}
								$pos = strpos($userimage, 'default_avatars');
								if ($pos === false) {
									$userimage = str_replace("60s.jpg","120s.jpg",$userimage);
								}
								$timestamp = strtotime($datesubmitted);
								$timestamp = date("Y-m-d H:i:s", $timestamp);
								//check option to see if this one has been hidden
								//pull array from options table of yelp hidden
								$yelphidden = get_option( 'wprevpro_hidden_reviews' );
								if(!$yelphidden){
									$yelphiddenarray = array('');
								} else {
									$yelphiddenarray = json_decode($yelphidden,true);
								}
								$this_yelp_val = trim($user_name)."-".strtotime($datesubmitted)."-".$review_length."-Yelp-".$rating;
								if (in_array($this_yelp_val, $yelphiddenarray)){
									$hideme = 'yes';
								} else {
									$hideme = 'no';
								}
								
								//check to see if in database already
											//check to see if row is in db already
								$reviewindb = 'no';
								if($options['yelp_radio_rule']!='no'){
									$reviewindb = 'no';
								} else {
									$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".strtotime($datesubmitted)."' AND reviewer_name = '".trim($user_name)."' " );
									if( empty( $checkrow ) )
									{
										$reviewindb = 'no';
									} else {
										$reviewindb = 'yes';
										break;
									}
								}
								$furlrev = "https://www.yelp.com/user_details?userid=".$user_id;
								//find character length
								if (extension_loaded('mbstring')) {
									$review_length_char = mb_strlen($rtext);
								} else {
									$review_length_char = strlen($rtext);
								}

								if( $reviewindb == 'no' )
								{
									$reviews[] = [
											'reviewer_name' => trim($user_name),
											'reviewer_id' => $user_id,
											'pageid' => trim($pageid),
											'pagename' => trim($pagename),
											'userpic' => $userimage,
											'rating' => $rating,
											'created_time' => $timestamp,
											'created_time_stamp' => strtotime($datesubmitted),
											'review_text' => trim($rtext),
											'hide' => $hideme,
											'review_length' => $review_length,
											'review_length_char' => $review_length_char,
											'type' => 'Yelp',
											'from_url' => $yelpurl[1],
											'from_url_review' => $furlrev
									];
								}
								$review_length ='';
								$review_length_char='';
							}
					 
							$i++;
					}
				
				$results['reviews'] = $reviews;

					return $results;
		
	}
	
	public function wpyelp_download_yelp_master_typenew($finalstringjson,$pagename,$yelpurl,$pageid){
					// Find 20 reviews
					global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$options = get_option('wprevpro_yelp_settings');
					$i = 1;
					
						foreach ($finalstringjson['reviews'] as $review) {
							
								if ($i > 21) {
										break;
								}
								$user_name='';
								$userimage='';
								$rating='';
								$datesubmitted='';
								$rtext='';
								$user_id='';
								// Find user_name
								$user_name = $review['user']['altText'];
								if($user_id==''){
									$user_id = str_replace(" ","",$user_name);
								}
															
								// Find userimage
								$userimage = $review['user']['src'];
								
								// find rating
								$rating = $review['rating'];
								
								// find date
								$datesubmitted = $review['localizedDate'];
								
								// find text
								$rtext ='';
								$rtext = html_entity_decode($review['comment']['text']);
								$lang = $review['comment']['language'];
								
								if($rating>0){
									//$review_length = str_word_count($rtext);
									//if($review_length <2 && $rtext !=""){		//fix for other language error
										$review_length = substr_count($rtext, ' ');
									//}
									$pos = strpos($userimage, 'default_avatars');
									if ($pos === false) {
										$userimage = str_replace("60s.jpg","120s.jpg",$userimage);
									}
									$timestamp = strtotime($datesubmitted);
									$timestamp = date("Y-m-d H:i:s", $timestamp);
									//check option to see if this one has been hidden
									//pull array from options table of yelp hidden
									$yelphidden = get_option( 'wprevpro_hidden_reviews' );
									if(!$yelphidden){
										$yelphiddenarray = array('');
									} else {
										$yelphiddenarray = json_decode($yelphidden,true);
									}
									$this_yelp_val = trim($user_name)."-".strtotime($datesubmitted)."-".$review_length."-Yelp-".$rating;
									if (in_array($this_yelp_val, $yelphiddenarray)){
										$hideme = 'yes';
									} else {
										$hideme = 'no';
									}
									
									//check to see if in database already
									//check to see if row is in db already
									$reviewindb = 'no';
									if($options['yelp_radio_rule']!='no'){
										$reviewindb = 'no';
									} else {
										$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".strtotime($datesubmitted)."' AND reviewer_name = '".trim($user_name)."' " );
										if( empty( $checkrow ) )
										{
											$reviewindb = 'no';
										} else {
											$reviewindb = 'yes';
											break;
										}
									}

									$furlrev = "https://www.yelp.com/".$review['user']['url'];
									//find character length
									if (extension_loaded('mbstring')) {
										$review_length_char = mb_strlen($rtext);
									} else {
										$review_length_char = strlen($rtext);
									}
									if( $reviewindb == 'no' )
									{
										$reviews[] = [
												'reviewer_name' => trim($user_name),
												'reviewer_id' => $user_id,
												'pageid' => trim($pageid),
												'pagename' => trim($pagename),
												'userpic' => $userimage,
												'rating' => $rating,
												'created_time' => $timestamp,
												'created_time_stamp' => strtotime($datesubmitted),
												'review_text' => trim($rtext),
												'hide' => $hideme,
												'review_length' => $review_length,
												'review_length_char' => $review_length_char,
												'type' => 'Yelp',
												'from_url' => $yelpurl[1],
												'from_url_review' => $furlrev,
												'language_code' => $lang,
										];
									}
									$review_length ='';
									$review_length_char='';
								}
						 
								$i++;
						}
					
					$results['reviews'] = $reviews;

					return $results;
	}
	
//--======================= end yelp =======================--//	
	
	
	/**
	 * Ajax, retrieves reviews from table, called from javascript file wprevpro_templates_posts_page.js
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wpfb_getavatars_ajax(){
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$this->wprevpro_download_img_tolocal();
		die();
	}
	
	 /**
	 * download a copy of the avatars to local server if checked in template and saved
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	 private function compressimage($source, $destination, $quality) {
		$info = getimagesize($source);
		if ($info['mime'] == 'image/jpeg') 
			$image = imagecreatefromjpeg($source);
		elseif ($info['mime'] == 'image/gif') 
			$image = imagecreatefromgif($source);
		elseif ($info['mime'] == 'image/png') 
			$image = imagecreatefrompng($source);
		imagejpeg($image, $destination, $quality);
	
		return $destination;
	}
	
	private function wppro_resizeimage($source,$size){
		//add fix for cron job
		if(!function_exists('wp_get_current_user')) {
			include(ABSPATH . "wp-includes/pluggable.php"); 
		}
		$image = wp_get_image_editor( $source );
		if ( ! is_wp_error( $image ) ) {
			$imagesize = $image->get_size();
			if($imagesize['width']>$size){
				$image->resize( $size, $size, true );
				$image->save( $source );
			}
		} else {
			$error_string = $image->get_error_message();
			echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
		}
	}

	//used for cache all avatars
	public function wprevpro_download_img_tolocal() {

		//$imagecachedir = plugin_dir_path( __DIR__ ).'/public/partials/cache/';
		$img_locations_option = json_decode(get_option( 'wprev_img_locations' ),true);
		$imagecachedir = $img_locations_option['upload_dir_wprev_cache'];
		
		//get array of all reviews, check to see if there is an image 
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$currentreviews = $wpdb->get_results("SELECT id, reviewer_id, created_time_stamp, reviewer_name, type, userpic FROM $table_name WHERE type !='Manual'");
		$copyfuncworks = 'no';
		foreach ( $currentreviews as $review ) 
		{
			//$review->id
			$blob = $review->reviewer_name;
			$blob = preg_replace("/[^a-zA-Z]+/", "", $blob);
			$newfilename = $review->created_time_stamp.'_'.strtolower($blob);
			$newfile = $imagecachedir . $newfilename.'.jpg';

			$userpic = htmlspecialchars_decode($review->userpic);
			//check for avatar
			
			if(@filesize($newfile)<200){
				if($userpic!=''){
					if ( @copy($userpic, $newfile) ) {
						//echo "Copy success!";
						$copyfuncworks = 'yes';
					} else if (function_exists('curl_init')) {
						$curl = curl_init();
						$fh = fopen($newfile, 'w');
						curl_setopt($curl, CURLOPT_URL, $userpic);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						$result = curl_exec($curl);
						fwrite($fh, $result);
						fclose($fh);
						curl_close($curl);
											
						if ( is_file($newfile) ) {
							$copyfuncworks = 'yes';
						}
					}
					if($copyfuncworks == 'yes'){
						$this->wppro_resizeimage($newfile,135);
						$d =$this->compressimage($newfile, $newfile, 85);
					}
				}
			}
			//now create low quality version
			$newfilelow = $imagecachedir . $newfilename.'_60.jpg';
			if($userpic!=''){
				if(@filesize($newfilelow)<200 && @filesize($newfile)>200){
						if ( @copy($newfile, $newfilelow) ) {
							//echo "Copy success!";
							$this->wppro_resizeimage($newfilelow,60);
						}
				}
			}
			//--------------------------
			//now try to save a local copy
			//copy this file to avatar directory and update db, currenlty only doing this for FB google,and funnels if turned on
			if($userpic!=''){
				//echo "prepare to copy:".$newfile;
				if(@filesize($newfile)>200){
					if($review->type=="Facebook" || $review->type=="Google"){
						$this->wprevpro_download_avatar_tolocal($newfile,$review);
					}
				}
			}
		}
		//set option value 
		update_option( $this->_token . '_copysuccess', $copyfuncworks );

		//die();
	}
	
	//Used to create local copy of avatar to serve 
	public function wprevpro_download_avatar_tolocal($filetocopy,$review) {
		//$imagecachedir = plugin_dir_path( __DIR__ ).'public/partials/avatars/';
		$img_locations_option = json_decode(get_option( 'wprev_img_locations' ),true);
		$imageuploadedir =$img_locations_option['upload_dir_wprev_avatars'];
		$filename = $review->created_time_stamp.'_'.$review->id;
		//get array of all reviews, check to see if the image exists
		$newfile = $imageuploadedir . $filename.'.jpg';
		
		$newfileurl = esc_url( $img_locations_option['upload_url_wprev_avatars']). $filename.'.jpg';
			//check for avatar
			if($filetocopy!=''){
				$id= $review->id;
				$revid = $review->reviewer_id;
				global $wpdb;
				$table_name = $wpdb->prefix . 'wpfb_reviews';
				$imgcopyfail = true;
				if(@filesize($newfile)<200){
					if ( @copy($filetocopy, $newfile) ) {
						//update db with new image location, userpiclocal
						//echo "copy success".$id."-".$revid;
						$imgcopyfail = false;
					}
					if($imgcopyfail) {
						//echo "copy failed";
						//unable to copy
						$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
					} else {
						//image was copied
						$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '$newfileurl' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
						//try to resize if too large
						$this->wppro_resizeimage($newfile,135);
					}
				} else {
					//echo "image exists:".$newfile;
					//image does exist, just update db with this filename
					$wpdb->query( $wpdb->prepare("UPDATE $table_name SET userpiclocal = '$newfileurl' WHERE id = %d AND reviewer_id = %s",$id, $revid) );
				}
			}
	}
	
	//for exporting CSV file of templates
	public function print_csv()
	{
		if ( ! current_user_can( 'manage_options' ) )
			return;

			      header('Content-Type: text/csv; charset=utf-8');  
				  header('Content-Disposition: attachment; filename=templatedata.csv');  
				  $output = fopen("php://output", "w");  
				  //fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
				  //Get list of all current forms--------------------------
				  global $wpdb;
				  $table_name = $wpdb->prefix . 'wpfb_post_templates';
					$currentformsarray = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
					//print_r($currentformsarray);
					
					//get the column keys and insert them on the first row of the excel file
					$arraykeys = array_keys($currentformsarray[0]);
					//print_r($arraykeys);
					
					fputcsv($output, $arraykeys); 
					
				  //while($row = mysqli_fetch_assoc($result)) 
				foreach ( $currentformsarray as $currentform ) 
				  {  
					   fputcsv($output, $currentform);  
				  }  
				  fclose($output);  

		// output the CSV data
		die();
	}
	public function printreviews_csv()
	{
		if ( ! current_user_can( 'manage_options' ) )
			return;

			      header('Content-Type: text/csv; charset=utf-8');  
				  header('Content-Disposition: attachment; filename=reviewdata.csv');  
				  $output = fopen("php://output", "w");  
				  //fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
				  //Get list of all current forms--------------------------
				  global $wpdb;
				  $table_name = $wpdb->prefix . 'wpfb_reviews';
					$currentformsarray = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
					//print_r($currentformsarray);
					
					//get the column keys and insert them on the first row of the excel file
					$arraykeys = array_keys($currentformsarray[0]);
					//print_r($arraykeys);
					
					fputcsv($output, $arraykeys); 
					
				  //while($row = mysqli_fetch_assoc($result)) 
				foreach ( $currentformsarray as $currentform ) 
				  {  
					   fputcsv($output, $currentform);  
				  }  
				  fclose($output);  

		// output the CSV data
		die();
	}
		//for exporting CSV file of templates
	public function print_csv_badges()
	{
		if ( ! current_user_can( 'manage_options' ) )
			return;

			      header('Content-Type: text/csv; charset=utf-8');  
				  header('Content-Disposition: attachment; filename=badgedata.csv');  
				  $output = fopen("php://output", "w");  
				  //fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
				  //Get list of all current forms--------------------------
				  global $wpdb;
				  $table_name = $wpdb->prefix . 'wpfb_badges';
					$currentformsarray = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
					//print_r($currentformsarray);
					
					//get the column keys and insert them on the first row of the excel file
					$arraykeys = array_keys($currentformsarray[0]);
					//print_r($arraykeys);
					
					fputcsv($output, $arraykeys); 
					
				  //while($row = mysqli_fetch_assoc($result)) 
				foreach ( $currentformsarray as $currentform ) 
				  {  
					   fputcsv($output, $currentform);  
				  }  
				  fclose($output);  

		// output the CSV data
		die();
	}
			//for exporting CSV file of templates
	public function print_csv_forms()
	{
		if ( ! current_user_can( 'manage_options' ) )
			return;

			      header('Content-Type: text/csv; charset=utf-8');  
				  header('Content-Disposition: attachment; filename=formdata.csv');  
				  $output = fopen("php://output", "w");  
				  //fputcsv($output, array('ID', 'First Name', 'Last Name', 'Email', 'Joining Date'));  
				  //Get list of all current forms--------------------------
				  global $wpdb;
				  $table_name = $wpdb->prefix . 'wpfb_forms';
					$currentformsarray = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
					//print_r($currentformsarray);
					
					//get the column keys and insert them on the first row of the excel file
					$arraykeys = array_keys($currentformsarray[0]);
					//print_r($arraykeys);
					
					fputcsv($output, $arraykeys); 
					
				  //while($row = mysqli_fetch_assoc($result)) 
				foreach ( $currentformsarray as $currentform ) 
				  {  
					   fputcsv($output, $currentform);  
				  }  
				  fclose($output);  

		// output the CSV data
		die();
	}
	
	
	//--======================= starttripadvisor =======================--//
	
	/**
	 * download tripadvisor reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	 
	//for ajax call to tripadvisor master
	public function wprevpro_ajax_download_tripadvisor_master() {
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$thisurlnum = $_POST['urlnum'];
		
		$getresponse = $this->wprevpro_download_tripadvisor_master($thisurlnum);
		
		//echo $getresponse;
		
		die();

	}
	 
	 
	public function wprevpro_download_tripadvisor_master($downloadurlnum = 'all') {
			$options = get_option('wprevpro_tripadvisor_settings');
			
			if(!empty($options['tripadvisor_scrape_method'])){
				$scrapemethod = $options['tripadvisor_scrape_method'];
			} else {
				$scrapemethod = "default";
			}
			//check to see if only downloading one here, if not that skip and continue
			if($downloadurlnum!='all'){
				if($downloadurlnum==1){
					$numurl='';
				} else {
					$numurl=$downloadurlnum;
				}
				if (filter_var($options['tripadvisor_business_url'.$numurl], FILTER_VALIDATE_URL)) {
					$currenturlmore = $options['tripadvisor_business_url'.$numurl];
					if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
					$this->wprevpro_download_tripadvisor_master_perurl($currenturlmore,$numurl,$scrapemethod);
					}
				} else {
					$errormsg = esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
			} else {
			//for cron get everything
				//make sure you have valid url, if not display message
				if (filter_var($options['tripadvisor_business_url'], FILTER_VALIDATE_URL)) {
					$currenturl = $options['tripadvisor_business_url'];
					$this->wprevpro_download_tripadvisor_master_perurl($currenturl,1,$scrapemethod);
				} else {
					$errormsg = esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
				
				$totalmorepages = $options['tripadvisor_business_url_more'];
				for ($x = 2; $x <= $totalmorepages; $x++) {
					sleep(2);
					$numurl = $x;
					if (filter_var($options['tripadvisor_business_url'.$numurl], FILTER_VALIDATE_URL)) {
						$currenturlmore = $options['tripadvisor_business_url'.$numurl];
						if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
						$this->wprevpro_download_tripadvisor_master_perurl($currenturlmore,$numurl,$scrapemethod);
						}
					}
				} 
			}

	}

	public function wprevpro_download_tripadvisor_showuserreviews_url($currenturl) {
		ini_set('memory_limit','300M');
		if (strpos($currenturl, 'VacationRentalReview') !== false) {
			//this is a vactionrental
			$vactionrental = true;
		} else {
			$vactionrental = false;
		}
					
		//scan page and try to get href and build URL.
		// Create DOM from URL or file
			if (ini_get('allow_url_fopen') == true) {
				$fileurlcontents=file_get_contents($currenturl);
			} else if (function_exists('curl_init')) {
				$fileurlcontents=$this->file_get_contents_curl($currenturl);
			} else {
						$fileurlcontents='<html><body>'.esc_html__('fopen is not allowed on this host.', 'wp-review-slider-pro').'</body></html>';
						$errormsg = $errormsg . '<p style="color: #A00;">'.esc_html__('fopen is not allowed on this host and cURL did not work either. Ask your web host to turn fopen on or fix cURL.', 'wp-review-slider-pro').'</p>';
						$this->errormsg = $errormsg;
						echo $errormsg;
				die();
			}
			//fix for lazy load base64 ""
			$fileurlcontents = str_replace('=="', '', $fileurlcontents);
					
			$html = wppro_str_get_html($fileurlcontents);
					
			//find total and average number here
			$avgrating ='';
			$totalreviews = '';
			
			//works for hotels
			if($html->find('div.ratingContainer', 0)){
				if($html->find('div.ratingContainer', 0)->find('span.ui_bubble_rating', 0)){
				$avgrating = $html->find('div.ratingContainer', 0)->find('span.ui_bubble_rating', 0)->alt;
				$avgrating = str_replace(" of 5 bubbles","",$avgrating);
				$avgrating = str_replace(",",".",$avgrating);
				$avgrating = (float)$avgrating;
				}
				if($html->find('div.ratingContainer', 0)->find('span.reviewCount', 0)){
				$totalreviews = $html->find('div.ratingContainer', 0)->find('span.reviewCount', 0)->plaintext;
				$totalreviews = str_replace(",","",$totalreviews);
				$totalreviews = intval($totalreviews);
				}
			}
			//backup method for hotels
			if($avgrating==''){
					if($html->find('span.hotels-hotel-review-about-with-photos-Reviews__overallRating--vElGA', 0)){
					$avgrating = $html->find('span.hotels-hotel-review-about-with-photos-Reviews__overallRating--vElGA', 0)->plaintext;
					$avgrating = str_replace(",",".",$avgrating);
					$avgrating = preg_replace('/[^0-9.]+/', '', $avgrating);
					//$avgrating = $avgrating/10;
					}
					if($html->find('span.hotels-hotel-review-about-with-photos-Reviews__seeAllReviews--3PpLR', 0)){
					$totalreviews = $html->find('span.hotels-hotel-review-about-with-photos-Reviews__seeAllReviews--3PpLR', 0)->plaintext;
					$totalreviews = str_replace(",","",$totalreviews);
					$totalreviews = intval($totalreviews);
					}
			}
			
			//if not found try backup method, currently used for resturants
			if($avgrating==''){
				if($html->find('div.rating_and_popularity', 0)){
					if($html->find('div.rating_and_popularity', 0)->find('span.ui_bubble_rating', 0)){
					$avgrating = $html->find('div.rating_and_popularity', 0)->find('span.ui_bubble_rating', 0)->alt;
					$avgrating = str_replace(" of 5 bubbles","",$avgrating);
					//fix for comma
					$avgrating = str_replace(",",".",$avgrating);
					$avgrating = (float)$avgrating;
					}
					if($html->find('div.rating_and_popularity', 0)->find('div.rating', 0)){
					$totalreviews = $html->find('div.rating_and_popularity', 0)->find('div.rating', 0)->plaintext;
					$totalreviews = str_replace(",","",$totalreviews);
					$totalreviews = intval($totalreviews);
					}
				}
			}
			//finally one more try for vacationrental
			if($avgrating==''){
				if($html->find('div.ratingSystem', 0)){
					if($html->find('div.ratingSystem', 0)->find('span.ui_bubble_rating', 0)){
					$avgrating = $html->find('div.ratingSystem', 0)->find('span.ui_bubble_rating', 0)->class;
					$avgrating = str_replace(",",".",$avgrating);
					$avgrating = preg_replace('/[^0-9.]+/', '', $avgrating);
					$avgrating = $avgrating/10;
					}
					if($html->find('div.ratingSystem', 0)->find('span.based-on-n-reviews', 0)){
					$totalreviews = $html->find('div.ratingSystem', 0)->find('span.based-on-n-reviews', 0)->plaintext;
					$totalreviews = str_replace(",","",$totalreviews);
					$totalreviews = str_replace("-","",$totalreviews);
					$totalreviews = str_replace("based on ","",$totalreviews);
					$totalreviews = preg_replace('/[^0-9.]+/', '', $totalreviews);
					}
				}
			}
			//finally one more try for attraction
			if($avgrating=='' || $totalreviews==''){
				if($html->find('div.ui_poi_review_rating ', 0)){
					if($html->find('div.ui_poi_review_rating ', 0)->find('span.ui_bubble_rating', 0)){
					$avgrating = $html->find('div.ui_poi_review_rating', 0)->find('span.ui_bubble_rating', 0)->class;
					$avgrating = str_replace(",",".",$avgrating);
					$avgrating = preg_replace('/[^0-9.]+/', '', $avgrating);
					$avgrating = $avgrating/10;
					}
					if($html->find('div.ui_poi_review_rating', 0)->find('span.reviewCount', 0)){
					$totalreviews = $html->find('div.ui_poi_review_rating', 0)->find('span.reviewCount', 0)->plaintext;
					$totalreviews = str_replace(",","",$totalreviews);
					$totalreviews = str_replace("-","",$totalreviews);
					$totalreviews = str_replace("based on ","",$totalreviews);
					$totalreviews = preg_replace('/[^0-9.]+/', '', $totalreviews);
					}
				}
			}
			
			$reviewobject5 ="";
			$page2url ="";
			$rtitlelink ="";
			$nextbtnlink="";
			//echo $html;
			//die();
			//check to see if on vacation rental or regular page
			if($vactionrental==true){
				if($html->find('div.reviewSelector')){
					$reviewobject = $html->find('div.reviewSelector',0);
				} else {
					echo esc_html__('Error 102a: Unable to read Vacation Rental TripAdvisor page.<br> Please use a Review Funnel to grab these reviews.', 'wp-review-slider-pro');
					die();	
				}
			} else {
				if($html->find('div.review-container')){
						$reviewobject = $html->find('div.review-container',0);
						$reviewobject5 = $html->find('div.review-container',5);
				} else {
					//fix for hotel review, hotels-hotel-review-community-content-Card__card-
					if($html->find('div.ui_card')){
						$reviewobject = $html->find('div.ui_card',0);
						$reviewobject5 = $html->find('div.ui_card',4);
						if($reviewobject->find("div[class*=ReviewTitle]", 0)){
							$rtitlelink = $reviewobject->find("div[class*=ReviewTitle]", 0)->find('a',0)->href;
						}
						if($reviewobject5->find("div[class*=ReviewTitle]", 0)){
							$nextbtnlink = $reviewobject5->find("div[class*=ReviewTitle]", 0)->find('a',0)->href;
						}
			
					} else if($html->find("div[class*=ReviewTitle]", 0)){
						$rtitlelink = $html->find("div[class*=ReviewTitle]", 0)->find('a',0)->href;

						if($html->find("div[class*=ReviewTitle]", 4)){
							$nextbtnlink = $html->find("div[class*=ReviewTitle]", 4)->find('a',0)->href;
						}
						//echo $rtitlelink;
					
					} else {
						echo esc_html__('Error 103a: Unable to read TripAdvisor page.<br> Please use a Review Funnel to grab these reviews.', 'wp-review-slider-pro');
						//echo $html;
						die();	
					}
				}
			}
			
			//find page name here
					//find tripadvisor business name and add to db under pagename
					$pagename ='';
					
					if($vactionrental==false){
						if($html->find('.altHeadInline', 0)){
							if($html->find('.altHeadInline', 0)->find('a',0)){
								$pagename = $html->find('.altHeadInline', 0)->find('a',0)->plaintext;
							}
						}
					} else {
						if($html->find('.vrPgHdr', 0)){
							if($html->find('.vrPgHdr', 0)->find('a',0)){
								$pagename = $html->find('.vrPgHdr', 0)->find('a',0)->plaintext;
							}
						}
					}

					if($pagename==''){
						if($html->find('h1[id=HEADING]',0)){
						$pagename = $html->find('h1[id=HEADING]',0)->plaintext;
						}
					}
					if($pagename==''){
						if($html->find('h1[class=ui_header h1]',0)){
						$pagename = $html->find('h1[class=ui_header h1]',0)->plaintext;
						}
					}
					if($pagename==''){
						if($html->find('h1[class=propertyHeading]',0)){
						$pagename = $html->find('h1[class=propertyHeading]',0)->plaintext;
						}
					}
					if($pagename==''){
						if($html->find('span[class=ui_header h1]',0)){
						$pagename = $html->find('span[class=ui_header h1]',0)->plaintext;
						}
					}
					//=====================
					
			
			if($reviewobject!="" && $rtitlelink==''){
				$rtitlelink = $reviewobject->find('div.quote', 0)->find('a',0)->href;
			}
			
			if($reviewobject5!="" && $nextbtnlink==''){
				$nextbtnlink = $reviewobject5->find('div.quote', 0)->find('a',0)->href;
			}
			
			$parseurl = parse_url($currenturl);
			$newurl = $parseurl['scheme'].'://'.$parseurl['host'].$rtitlelink;
			$page2url = $parseurl['scheme'].'://'.$parseurl['host'].$nextbtnlink;
			
			//$response =array("page1"=>$newurl,"page2"=>$page2url);
			
			$response =array("page1"=>$newurl,"page2"=>$page2url,"totalreviews"=>$totalreviews,"avgrating"=>$avgrating,"pagename"=>$pagename);
			$html->clear(); 
			unset($html);
			
			//print_r($response);
			//die();

			//create new link based on $currenturl and $rtitlelink
		return $response;
	}
	
	public function wprevpro_download_tripadvisor_master_perurl($currenturl,$urlnum,$scrapemethod) {
		ini_set('memory_limit','300M');
		$listedurl = $currenturl;
		$usephantomsimple = false;
		$vactionrental = false;

		$tripadvisoroptions = get_option('wprevpro_tripadvisor_settings');
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
		
			//original came from Url
			if (strpos($currenturl, 'VacationRentalReview') !== false) {
				//this is a vactionrental
				$vactionrental = true;
				$showuserreviewpage=false;
				$orgregularpage = false;
			} else if (strpos($currenturl, 'ShowUserReviews') !== false) {
				$vactionrental = false;
				$showuserreviewpage=true;
				$orgregularpage = false;
			} else if (strpos($currenturl, 'Hotel_Review') !== false) {
				//$usephantomsimple = true;
				//$hotelreview = true;
				
			}  else if (strpos($currenturl, 'Attraction_Review') !== false) {
				//$usephantomsimple = true;
			} else {
				$vactionrental = false;
				$showuserreviewpage=false;
				$orgregularpage = true;
			}
			
			//now users can choose to use phantom simple_html_dom
			if($scrapemethod=="phantom"){
				$usephantomsimple = true;
			}
		
			//if $currenturl does not have ShowUserReviews in title then we need to try and get from page.
			if (strpos($currenturl, 'ShowUserReviews') === false) {
				$urlarray = $this->wprevpro_download_tripadvisor_showuserreviews_url($currenturl);
				$currenturl =$urlarray['page1'];
				$nexturl =$urlarray['page2'];
				$totalreviews = $urlarray['totalreviews'];
				$avgrating = $urlarray['avgrating'];
				$pagename =  $urlarray['pagename'];
				//add url number to pagename
				$pagename = $pagename.' '.$urlnum;

				//make sure you have valid url, if not display message
				if (filter_var($currenturl, FILTER_VALIDATE_URL)) {
					//good URL
				} else {
					$errormsg = esc_html__('Error 001: Review page not found from URL. Please contact support.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
			}
			//print_r($urlarray);
			//die();

				//echo "passed both tests";
				$tripadvisorurl[0] = $currenturl;
				if($nexturl!=""){
				$tripadvisorurl[1] = $nexturl;
				}

				//loop to grab pages
				$reviews = [];
				$n=1;
				foreach ($tripadvisorurl as $urlvalue) {
					

				//Don't use phantomcloud on ShowUserReviews pages	
				if (strpos($urlvalue, 'ShowUserReviews') !== false) {
					//showuserreviews page
					$tempurlvalue = $urlvalue;
				} 
				if($usephantomsimple==true) {
					
					//--------------phantomjscloud-------------------------
					$tempurlvalue = plugin_dir_path( __FILE__ ).'tripcapture'.$urlnum.'.html';
					$url = 'http://PhantomJScloud.com/api/browser/v2/ak-2cme5-eqftq-dv73x-nr41t-gkbvs/';
					//different script for different pages, need to check url for VacationRentalReview
					if (strpos($urlvalue, 'VacationRentalReview') !== false) {
						//this is a vactionrental
						$vactionrental = true;
						$scriptpayload ="var morespan,wprevcheckspantext,counter=8,morespanobjects=document.getElementsByClassName('ulBlueLinks'),morespancheck;_pjscMeta.manualWait=true;var wprevscrollpageby=setInterval(function(){if(counter--,window.scrollBy(0,500),0===counter){clearInterval(wprevscrollpageby);for(var e=0;e<morespanobjects.length;e++)(morespan=morespanobjects[e]).click(),'More'==(morespancheck=document.getElementsByClassName('ulBlueLinks')[e]).innerText&&morespan.click();_pjscMeta.manualWait=false}},600);";
					} else if($usephantomsimple==true){
						$vactionrental = false;
						$scriptpayload = "var counter=15;_pjscMeta.manualWait=true;var wprevscrollpageby=setInterval(function(){counter--;window.scrollBy(0,500);if(counter===0){_pjscMeta.manualWait=false;clearInterval(wprevscrollpageby)}},500)";
					} else {
						$vactionrental = false;
						$scriptpayload = "var counter=12;var morespan=document.getElementsByClassName('ulBlueLinks')[0];var wprevcheckspantext;var morespancheck='';_pjscMeta.manualWait=true;var wprevscrollpageby=setInterval(function(){counter--;window.scrollBy(0,400);if(counter===0){clearInterval(wprevscrollpageby);morespan.scrollIntoView();morespan.click();wprevcheckspantext=setInterval(function(){morespancheck=document.getElementsByClassName('ulBlueLinks')[0];if(morespancheck.innerHTML==='Show less'){clearInterval(wprevcheckspantext);_pjscMeta.manualWait =false;}else{morespancheck.scrollIntoView();morespancheck.click()}},500)}},600)";
					}
					//Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36
					$payload = '{
						"url":"'.$urlvalue.'",
						"renderType":"html",
						"outputAsJson":false,
						"requestSettings": {
							"ignoreImages": false,
							"disableJavascript": false, 
							"userAgent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36",
							"xssAuditingEnabled": false,
							"webSecurityEnabled": false,
							"resourceWait": 2000,
							"resourceTimeout": 2000,
							"maxWait": 30000,
							"waitInterval": 2000,
							"stopOnError": false,
							"resourceModifier": [],
							"customHeaders": {},
							"clearCache": true,
							"clearCookies": true,
							"cookies": [],
							"deleteCookies": []
						},
						scripts:{
							domReady:[
								"'.$scriptpayload.'",
								],
							"loadFinished": [
								"",
								]
								}
					}';
					$options = array(
						'http' => array(
							'header'  => "Content-type: application/json\r\n",
							'method'  => 'POST',
							'content' => $payload
						)
					);
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($payload)));
					
					$html = curl_exec($ch);
					if ($html === FALSE) { 
						$errormsg = 'Error: Unable to load this Url.'; 
						//echo $errormsg;
					} else {
						//echo 'saving file';
						$savefile = file_put_contents($tempurlvalue,$html );
						//echo $savefile;
					}
					//check for file and stop here if doesn't exists
					if(!file_exists($tempurlvalue)){
						echo esc_html__('Error 101: Unable to get TripAdvisor page. Please make sure that your Hosting provider has file_put_contents turned on.', 'wp-review-slider-pro');
						die();
					}
					
				//--------------phantomjscloud-------------------------	
				}

					// Create DOM from URL or file
					if (ini_get('allow_url_fopen') == true) {
						$fileurlcontents=file_get_contents($tempurlvalue);
					} else if (function_exists('curl_init')) {
						$fileurlcontents=$this->file_get_contents_curl($tempurlvalue);
					} else {
						$fileurlcontents='<html><body>'.esc_html__('fopen is not allowed on this host.', 'wp-review-slider-pro').'</body></html>';
						$errormsg = $errormsg . '<p style="color: #A00;">'.esc_html__('fopen is not allowed on this host and cURL did not work either. Ask your web host to turn fopen on or fix cURL.', 'wp-review-slider-pro').'</p>';
						$this->errormsg = $errormsg;
						echo $errormsg;
						die();
					}

					

					//fix for lazy load base64 ""
					$fileurlcontents = str_replace('=="', '', $fileurlcontents);
			
					$html = wppro_str_get_html($fileurlcontents);
					
					//echo $html;
					//die();
					//check to see if on vacation rental or regular page
					if($vactionrental==true){
						if($html->find('div.reviewSelector')){
							$reviewobject = $html->find('div.reviewSelector');
						} else {
							echo esc_html__('Error 102: Unable to read Vacation Rental TripAdvisor page. Please try again or contact support.', 'wp-review-slider-pro');
							die();	
						}
					} else {
						if($html->find('div.review-container')){
							$reviewobject = $html->find('div.review-container');
						} else {
							echo esc_html__('Error 103: Unable to read TripAdvisor page. Please try again or contact support.', 'wp-review-slider-pro');
							die();	
						}
					}
					
					//create pageid for db
					$pageid = str_replace(" ","",$pagename)."_trip";
					$pageid = str_replace("'","",$pageid);
					$pageid = str_replace('"',"",$pageid);
					$pageid = str_replace('&#x27;',"",$pageid);
					
					// Find 20 reviews
					$i = 1;
					
					//print_r($reviewobject );
					//die();
					
					foreach ($reviewobject as $review) {
						
							if ($i > 21) {
									break;
							}
							$user_name='';
							$userimage='';
							$rating='';
							$datesubmitted='';
							$rtext='';
							// Find user_name
							if($review->find('div.username', 0)){
								$user_name = $review->find('div.username', 0)->plaintext;
							}
							if($user_name==''){
								if($review->find('div.info_text', 0)){
									$user_name = $review->find('div.info_text', 0)->find('div', 0)->plaintext;
								}
							}
							
						if($vactionrental==false){
							//find lazyload image js variable and convert to array #\slazyImgs\s*=\s*(.*?);\s*$#s
							$startstringpos = stripos("$html","var lazyImgs = [") + 16;
							$choppedstr = substr("$html", $startstringpos);
							$endstringpos = stripos("$choppedstr","]");
							$finalstring = trim(substr("$html", $startstringpos, $endstringpos));
							$finalstring =str_replace(":true",':"true"',$finalstring);
							$finalstring ="[".str_replace(":false",':"false"',$finalstring)."]";
							$jsonlazyimg  = json_decode($finalstring, true);
						}

							// Find userimage ui_avatar, need to pull from lazy load varible
							$userimage='';
							if($vactionrental==false){
								if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
									$userimageid = $review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->id;
									//strip id from 
									$userimageid = strrchr ($userimageid , "_" );
									//loop through array and return url
									for ($x = 0; $x <= count( (array)$jsonlazyimg); $x++) {
										//get temp id
										$tempid = $jsonlazyimg[$x]['id'];
										$tempid = strrchr ($tempid , "_" );
										if($userimageid==$tempid){
											$userimage = $jsonlazyimg[$x]['data'];
											$x = count( (array)$jsonlazyimg) + 1;
										}
									} 
								}
							}
							
							//if the userimage wasn't found try to direct pull it data-lazyurl
							if($vactionrental==false){
								$checkstringpos =  strpos($userimage, 'base64');
								if($userimage =='' || $checkstringpos>0){
									if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
										if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'}){
											$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'};
										} else {
											$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->src;
										}
									}
								}
								//
							}
							//one more try for vacationrentals
							if($userimage ==''){
								if($review->find('div.avatar', 0)->find('img.avatar', 0)){
										$userimage =$review->find('div.avatar', 0)->find('img.avatar', 0)->src;
								}
							}
							//one more try for hotels
							if($userimage ==''){
								if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
										$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->src;
								}
							}
							
							//if userimage not found check
								if($userimage ==''){
									if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)){
										if($review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'}){
											$userimage =$review->find('div.ui_avatar', 0)->find('img.basicImg', 0)->{'data-lazyurl'};
										}
									}
									//echo "<br>userimage:".$userimage;
								}
								
								
					
							// find rating
							if($review->find('span.ui_bubble_rating', 0)){
								$temprating = $review->find('span.ui_bubble_rating', 0)->class;
								$int = filter_var($temprating, FILTER_SANITIZE_NUMBER_INT);
								//echo $int."<br>";
								$rating = str_replace(0,"",$int);
							}
							
							// find date
							if($review->find('span.ratingDate', 0)){
								if($vactionrental==false){
									$datesubmitted = $review->find('span.ratingDate', 0)->title;
									$datesubmitted = preg_replace("(<([a-z]+)>.*?</\\1>)is","",$datesubmitted);
								} else {
									if($review->find('span.ratingDate', 0)->title){
										$datesubmitted = $review->find('span.ratingDate', 0)->title;
										$datesubmitted = preg_replace("(<([a-z]+)>.*?</\\1>)is","",$datesubmitted);
									} else {
									$datesubmitted = $review->find('span.ratingDate', 0)->innertext;
									$datesubmitted = preg_replace("(<([a-z]+)>.*?</\\1>)is","",$datesubmitted);
									$datesubmitted = str_replace("Reviewed","",$datesubmitted);
									$datesubmitted = str_replace("Beoordeeld","",$datesubmitted);
									$datesubmitted = str_replace("op","",$datesubmitted);
									$datesubmitted = str_replace('Recensito il',"",$datesubmitted);
									
									//$datesubmitted = date('d-m-Y H:i:s', strtotime($datesubmitted, 1324189035));
									}
								}
							}
							//echo "<br>".$datesubmitted;
							//echo $user_name.":".$datesubmitted."</br>";
							 //1 de Janeiro de 2018
							
							// find text
							$rtext='';
							if($vactionrental==false){
								if($review->find('div.prw_reviews_text_summary_hsx', 0)){
									$rtext = $review->find('div.prw_reviews_text_summary_hsx', 0)->find('p', 0)->plaintext;
								}
								//if this is the first review then handle differently, it is at top of showuserreview page
								if($i==1){
									if($review->find('div.prw_reviews_resp_sur_review_text', 0)){
										$rtext = $review->find('div.prw_reviews_resp_sur_review_text', 0)->find('p', 0)->plaintext;
									} else if($review->find('div.prw_reviews_resp_sur_review_text_expanded', 0)){
										$rtext = $review->find('div.prw_reviews_resp_sur_review_text_expanded', 0)->find('p', 0)->plaintext;
									}
									
									
								}
							} else {
								if($review->find('div.entry', 0)){
									$rtext = $review->find('div.entry', 0)->find('p', 0)->plaintext;
								}
							}
							//if rtext is blank try one more time, used to get top review on hotels
							if($rtext==''){
								if($review->find('div.entry', 0)){
									$rtext = $review->find('div.entry', 0)->find('p', 0)->plaintext;
								}
							}
							$rtext =str_replace("...More","...",$rtext);

							//find title if set on options page
							//-may be used in future version--------
								$rtitle = '';
								if($vactionrental==false){
									if($review->find('span.noQuotes', 0)){
										$rtitle = $review->find('span.noQuotes', 0)->plaintext;
									}
								} else {
									if($review->find('div.quote', 0)){
										$rtitle = $review->find('div.quote', 0)->plaintext;
									}									
								}
								//if rtitle
								if($rtitle==''){
									if($review->find('div.quote', 0)){
										$rtitle = $review->find('div.quote', 0)->plaintext;
									}
								}
								
							//-----------------------------------
							$fromlink = '';
							if($review->find('div.quote', 0)->find('a',0)){
								$fromlinkgrab = $review->find('div.quote', 0)->find('a',0)->href;
								$parseurl = parse_url($urlvalue);
								$fromlink= $parseurl['scheme'].'://'.$parseurl['host'].$fromlinkgrab;
							}	else {
								$fromlink = $listedurl;
							}
							//echo "here".$fromlinkgrab;
							//echo "here2".$fromlink;
							//die();
							
							if($rating>0 && $rtext!=''){
								//test detect language
								//$langcode = $this->wprev_detectlang($rtext);
							
								$review_length = substr_count($rtext, ' ');

								$pos = strpos($userimage, 'default_avatars');
								if ($pos === false) {
									$userimage = str_replace("60s.jpg","120s.jpg",$userimage);
								}

								//echo "<br>".$datesubmitted;
								$timestamp = $this->myStrtotime($datesubmitted);
								//echo "<br>".$timestamp;
								$unixtimestamp = $timestamp;
								$timestamp = date("Y-m-d H:i:s", $timestamp);
								$hideme = 'no';
													
								//check to see if in database already
								$reviewindb = 'no';
								$user_name = trim($user_name);
								//$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$unixtimestamp."' AND reviewer_name = '".$user_name."' " );
								
								if (extension_loaded('mbstring')) {
									$review_length_char = mb_strlen($rtext);
								} else {
									$review_length_char = strlen($rtext);
								}
								
								$sitetype = 'TripAdvisor';
								
								$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$user_name."' AND type = '".$sitetype."' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
								
								if( empty( $checkrow ) ){
										$reviewindb = 'no';
								} else {
										$reviewindb = 'yes';
										break;
								}
								if( $reviewindb == 'no' )
								{
								
 
									if (!in_array($user_name, array_column($reviews, 'reviewer_name'))) {
										$reviews[] = [
												'reviewer_name' => $user_name,
												'pagename' => trim($pagename),
												'pageid' => trim($pageid),
												'userpic' => $userimage,
												'rating' => $rating,
												'created_time' => $timestamp,
												'created_time_stamp' => $unixtimestamp,
												'review_text' => trim($rtext),
												'review_title' => trim($rtitle),
												'hide' => $hideme,
												'review_length' => $review_length,
												'review_length_char' => $review_length_char,
												'type' => 'TripAdvisor',
												'from_url' => trim($listedurl),
												'from_url_review' => $fromlink,
										];
									}
								}
								$review_length ='';
								$review_length_char='';
							}
					 
							$i++;
					}
					
					//break here if found one already in db
					if($reviewindb == 'yes') {
						break;
						//die();
					}

					//sleep for random 2 seconds
					sleep(rand(1,2));
					$n++;
					
					//var_dump($reviews);
					// clean up memory
					if (!empty($html)) {
						$html->clear();
						unset($html);
					}
				}
				 

				$reviews = array_unique($reviews, SORT_REGULAR);
				//$tempArr = array_unique(array_column($reviews, 'value'));
				//$reviews = array_intersect_key($array, $tempArr);

				//print_r($reviews);
				
				//remove duplicates
				$reviewernames = [];
				$insertreviews = [];
				foreach ( $reviews as $stat ){
					if (!in_array($stat['reviewer_name'], $reviewernames)) {
						$insertreviews[] = $stat;
					}
					$reviewernames[] = $stat['reviewer_name'];
				}
				
				
				//go ahead and delete first, only if we have new ones and turned on.
				if(count($insertreviews)>0){

					//add all new tripadvisor reviews to db
					$insertnum=0;
					foreach ( $insertreviews as $stat ){
						$insertnum = $wpdb->insert( $table_name, $stat );
					}
					//reviews added to db
					if($insertnum>0){
						$errormsg = count($insertreviews) . " ".esc_html__('TripAdvisor reviews downloaded.', 'wp-review-slider-pro');
						$this->errormsg = $errormsg;
					} else {
						$errormsg = $errormsg . " ".esc_html__('Unable to find any new reviews.', 'wp-review-slider-pro');
						$this->errormsg = $errormsg;
					}
					
					
					//send $reviews array to function to send email if turned on.
					if(count($insertreviews)>0){
						$this->sendnotificationemail($insertreviews, "tripadvisor");
					}
				
				} else {
					$errormsg = esc_html__('No new reviews found.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
				}
				
				 //update total and average
				 //echo "total:";
				 //echo $totalreviews;
				 //echo "avg:";
				 //echo $avgrating;
				$this->updatetotalavgreviews('tripadvisor', trim($pageid), $avgrating, $totalreviews );
				
				echo $errormsg;

	}
//--======================= end tripadvisor =======================--//
	public function wprev_detectlang($text, $charlimit=20, $hint="") {
		
		$text = substr($text,0,$charlimit);
		$url = "https://translate.yandex.net/api/v1.5/tr.json/detect?key=trnsl.1.1.20190109T164624Z.fbb8ce48f42ad9d4.93571175069c1306e6db5cdd48baf849a3ad5cc5&text=".urlencode($text);
		
		//Sanitize the URL
		$url = esc_url_raw( $url );

		//echo $url;
		// Send API Call using WP's HTTP API
		$data = wp_remote_get( $url );

		if ( is_wp_error( $data ) ) 
		{
			$response['error_message'] 	= $data->get_error_message();
			$reponse['status'] 		= $data->get_error_code();
			return $response;
		}
		$response = json_decode( $data['body'], true );
		print_r($response);
		
	}

	
	//public function testing_function() {
     // global $pagenow;
    //  if ($_GET['page']=='wp_pro-notifications') {
	//	$this->sendnotificationemail(array(),'facebook','');
    //  }
   // }

	//---for sending notification email=================================================
	public function sendnotificationemail($reviewarray, $type) {
		//new method uses the notification table in the db
		global $wpdb;
		$table_name_notify = $wpdb->prefix . 'wpfb_nofitifcation_forms';
		//first check to see if there are any forms in the db, if we find some, loop and send emails
		$currentforms = $wpdb->get_results("SELECT * FROM $table_name_notify WHERE enable != 'no' ORDER BY id DESC",ARRAY_A);
		//print_r($currentforms);
		$pageid='';
		if(isset($reviewarray[0]['pageid'])){
			$pageid = trim($reviewarray[0]['pageid']);
		}
		
		//print_r($reviewarray);
		if (is_array($currentforms)){
			if (count($currentforms)>0){
				//passed checks we have array of notifications
				$totalforms = count($currentforms);
				for ($x = 0; $x < $totalforms; $x++) {
					//echo "id:".$currentforms[$x]['id']." <br>";
					//run through checks to see if we need to send
					$passedtypecheck = false;
					//use the type and $reviewarray to see if we need to send an email if we need too.
					$dbsitetypearray = json_decode($currentforms[$x]['site_type']);
					if($currentforms[$x]['site_type']==''){
						$passedtypecheck = true;
					}
					if(is_array($dbsitetypearray) && in_array($type, $dbsitetypearray)){
						$passedtypecheck = true;
					}
					$passedpagecheck = false;
					//use the page
					$dbpageidarray = json_decode($currentforms[$x]['source_page']);
					if($currentforms[$x]['source_page']==''){
						$passedpagecheck = true;
					}
					//echo "pageid:".$pageid;
					//print_r($dbpageidarray);
					if(is_array($dbpageidarray)){
						if(in_array($pageid, $dbpageidarray)){
							$passedpagecheck = true;
						}
						//add check for special characters
						$temppageidhtmlentities = htmlspecialchars_decode($pageid);
						if(in_array($temppageidhtmlentities, $dbpageidarray)){
							$passedpagecheck = true;
						}
						//add check for special characters
						$temppageidhtmlentities = htmlentities($pageid);
						if(in_array($temppageidhtmlentities, $dbpageidarray)){
							$passedpagecheck = true;
						}
					}
					
					
					//continue if made it this far and email is not blank
					if($passedpagecheck && $passedtypecheck && $currentforms[$x]['email']!=''){
						
						//loop review array and see if any match the rule in the form, is so then send the mail
						$foundone = false;
						$emailtable ='';
						if(is_array($reviewarray)){
							foreach ( $reviewarray as $review ){
								//is this greater, equal, or less
								$addthisreview = false;
								$ratingnum = $currentforms[$x]['rate_val'];
								//fb fix for recommendations---------
								if($review['rating']<1 && $review['recommendation_type']=='positive'){
									$review['rating']=5;
								} else if($review['rating']<1 && $review['recommendation_type']=='negative'){
									$review['rating']=2;
								}
								//---------------
								if($currentforms[$x]['rate_op']=='>' || $currentforms[$x]['rate_op']=='&gt;'){
									if($review['rating']>$ratingnum && $review['rating']>0){
										$addthisreview=true;
									}
								} else if($currentforms[$x]['rate_op']=='='|| $currentforms[$x]['rate_op']=='&equals;'){
									if($review['rating']==$ratingnum && $review['rating']>0){
										$addthisreview=true;
									}
								} else if($currentforms[$x]['rate_op']=='<' || $currentforms[$x]['rate_op']=='&lt;'){
									if($review['rating']<$ratingnum && $review['rating']>0){
										$addthisreview=true;
									}
								}
								if($addthisreview){
									$foundone = true;
									//add to email string
									$emailtable = $emailtable . '<tr><td style="vertical-align: top;padding: 5px;border: 1px solid #f2f2f2;"><b>'.$review['rating'].'</b></td><td style="vertical-align: top;padding: 5px;border: 1px solid #f2f2f2;">'.date("M j, Y",$review['created_time_stamp']).'</td><td style="vertical-align: top;padding: 5px;border: 1px solid #f2f2f2;">'.$review['reviewer_name'].'</td><td style="vertical-align: top;padding: 5px;border: 1px solid #f2f2f2;">'.$review['review_text'].'</td></tr>';
								}
							}
						}
						//if we found a review then we form email here...
						if($foundone){
							if($currentforms[$x]['email_first_line']==""){
								$currentforms[$x]['email_first_line']= __('WP Review Slider Pro found the following reviews that match your notification settings.', 'wp-review-slider-pro');
							}
							if($type=='facebook'){
								$originurl = "https://www.facebook.com/pg/".$reviewarray[0]['pageid']."/reviews/";
							} else {
								$originurl = $review['from_url'];
								$originurl = urldecode($originurl);
							}
							$tempnameaddress = '<p>'.esc_html__('Social Page Name:', 'wp-review-slider-pro').' <b>'.$reviewarray[0]['pagename'].'</b></p>';
							
							$emailstring = '<div>'.html_entity_decode($currentforms[$x]['email_first_line']).'</div><p><b>'.esc_html__('Review From:', 'wp-review-slider-pro').' <a href="'.$originurl.'" target="_blank" style="text-decoration: none;">'.ucfirst($type).'</a></b></p><p><b>'.esc_html__('Review URL:', 'wp-review-slider-pro').' <a href="'.$originurl.'" target="_blank" style="text-decoration: none;">'.$originurl.'</a></b></p>'.$tempnameaddress.'<br><table><tr><td  style="width: 50px;"><b>'.esc_html__('Rating', 'wp-review-slider-pro').'</b></td><td style="width: 100px;"><b>'.esc_html__('Date', 'wp-review-slider-pro').' </b></td><td><b>'.esc_html__('Name', 'wp-review-slider-pro').' </b></td><td><b>'.esc_html__('Text', 'wp-review-slider-pro').' </b></td></tr>';
							
							$emailstring = $emailstring . $emailtable . '</table><br><br>';
							
							//finally send the mail here...
							$headers = array('Content-Type: text/html; charset=UTF-8');
							if ( wrsp_fs()->can_use_premium_code() ) {
								//loop through emails and remove admin links if not an admin
								$sendtoemail = $currentforms[$x]['email'];
								$email_data = explode(",",$sendtoemail);
								$subject = $currentforms[$x]['email_subject'];
								if($subject==""){
									$subject=esc_html__('New Reviews Notification - WP Pro Review Slider', 'wp-review-slider-pro');
								}
								 if ( ! empty( $email_data) ) {
									foreach( $email_data as $email) {
										$adminlinks ='';
										$user = get_user_by( 'email', $email);
										if ( ! empty( $user ) ) {
											if($user->allcaps['administrator']){
												$siteurl = get_option( 'siteurl' );
												//user is admin, add links
												$adminlinks = '<p><a href="'.$siteurl.'/wp-admin/admin.php?page=wp_pro-reviews" target="_blank" style="text-decoration: none;">'.esc_html__('View in Plugin Admin', 'wp-review-slider-pro').'</a></p><p> '.esc_html__('To turn off or modify these notifications go to the notifications page in the plugin.', 'wp-review-slider-pro').'</p>';
											}
										}
										$emailstringfinal = $emailstring.$adminlinks;
										$adminlinks ='';
										//echo "sending email......";
										wp_mail( $email, $subject, $emailstringfinal, $headers );
									}
								}
							}
						
						}
					}
					
					//echo "<br>passedtypecheck:".$passedtypecheck;
					//echo "<br>passedpagecheck:".$passedpagecheck."<br>";
					
					
				}
			}
		}
		
		
		
	}
	


	//last name save options================================
	private function changelastname($fullname, $lastnamesaveoption = 'full'){
		//last name display options
		$tempreviewername = stripslashes(strip_tags($fullname));
		$words = explode(" ", $tempreviewername);
		if($lastnamesaveoption!='full'){
			if($lastnamesaveoption=="nothing"){
				$tempreviewername=$words[0];
			} else if($lastnamesaveoption=="initial"){
				$tempfirst = $words[0];
				if(isset($words[1])){
					$templast = $words[1];
					$templast =substr($templast,0,1);
					$tempreviewername = $tempfirst.' '.$templast.'.';
				} else {
					$tempreviewername = $tempfirst;
				}
				
			}
		}
		return $tempreviewername;
	}
	
	//fix stringtotime for other languages
	private function myStrtotime($date_string) { 
		$monthnamearray = array(
		'janvier'=>'jan',
		'février'=>'feb',
		'mars'=>'march',
		'avril'=>'apr',
		'mai'=>'may',
		'juin'=>'jun',
		'juillet'=>'jul',
		'août'=>'aug',
		'septembre'=>'sep',
		'octobre'=>'oct',
		'novembre'=>'nov',
		'décembre'=>'dec',
		'gennaio'=>'jan',
		'febbraio'=>'feb',
		'marzo'=>'march',
		'aprile'=>'apr',
		'maggio'=>'may',
		'giugno'=>'jun',
		'luglio'=>'jul',
		'agosto'=>'aug',
		'settembre'=>'sep',
		'ottobre'=>'oct',
		'novembre'=>'nov',
		'dicembre'=>'dec',
		'janeiro'=>'jan',
		'fevereiro'=>'feb',
		'março'=>'march',
		'abril'=>'apr',
		'maio'=>'may',
		'junho'=>'jun',
		'julho'=>'jul',
		'agosto'=>'aug',
		'setembro'=>'sep',
		'outubro'=>'oct',
		'novembro'=>'nov',
		'dezembro'=>'dec',
		'enero'=>'jan',
		'febrero'=>'feb',
		'marzo'=>'march',
		'abril'=>'apr',
		'mayo'=>'may',
		'junio'=>'jun',
		'julio'=>'jul',
		'agosto'=>'aug',
		'septiembre'=>'sep',
		'octubre'=>'oct',
		'noviembre'=>'nov',
		'diciembre'=>'dec',
		'januari'=>'jan',
		'februari'=>'feb',
		'maart'=>'march',
		'april'=>'apr',
		'mei'=>'may',
		'juni'=>'jun',
		'juli'=>'jul',
		'augustus'=>'aug',
		'september'=>'sep',
		'oktober'=>'oct',
		'november'=>'nov',
		'december'=>'dec',
		' de '=>'',
		'dezember'=>'dec',
		'januar '=>'jan ',
		'stycznia'=>'jan',
		'lutego'=>'feb',
		'februar'=>'feb',
		'marca'=>'march',
		'märz'=>'march',
		'kwietnia'=>'apr',
		'maja'=>'may',
		'czerwca'=>'jun',
		'lipca'=>'jul',
		'sierpnia'=>'aug',
		'września'=>'sep',
		'października'=>'oct',
		'listopada'=>'nov',
		'grudnia'=>'dec',
		'february'=>'feb',
		'января'=>'jan',
		'февраля'=>'feb',
		'марта'=>'march',
		'апреля'=>'apr',
		'мая'=>'may',
		'июня'=>'jun',
		'июля'=>'jul',
		'августа'=>'aug',
		'сентября'=>'sep',
		'октября'=>'oct',
		'ноября'=>'nov',
		'декабря'=>'dec',
		);
		//echo strtr(strtolower($date_string), $monthnamearray);
		return strtotime(strtr(strtolower($date_string), $monthnamearray)); 
	}
	
	//for adding total and averages to new wpfb_total_averages table in database added 10.9.3
	//use for 2 cases badge and review template filter
	/*
	public function updatetotalavgreviewstable($btp_id,$btp_type,$currentpostid=''){
		global $wpdb;
		//first case is regular source page id;
		if($btp_type=='badge' && $btp_id!=''){
			
		//end badge type	
		}
	}
	*/
	//used to actually insert the values from function above
	/*
	public function updatetotalavgreviewstableinsert($btp_type,$valuearray){
		global $wpdb;
		$table_name_totalavg = $wpdb->prefix . 'wpfb_total_averages';
		$key = $valuearray['btp_id'];
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
				'btp_type' => "$btp_type",
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
		$insertrow = $wpdb->replace( $table_name_totalavg, $data, $format );
	}
	*/
	
	//-----for updating options for total and avg based on pageid
	public function updatetotalavgreviews($type, $pageid, $avg, $total ){
		$ratingsarray= array();
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		//fix for comma in some languages
		$avg = str_replace(",",".",$avg);
		$option = 'wppro_total_avg_reviews';

		//get existing option array of fb cron pages
		$wppro_total_avg_reviews_array = get_option( $option );
		if(isset($wppro_total_avg_reviews_array)){
			$wppro_total_avg_reviews_array = json_decode($wppro_total_avg_reviews_array, true);
		} else {
			$wppro_total_avg_reviews_array = array();
		}
			$field_name = 'rating';
			$prepared_statement = $wpdb->prepare( "SELECT rating, recommendation_type FROM {$table_name} WHERE hide != %s AND pageid = %s", 'yes', $pageid);
			$fbreviews = $wpdb->get_results( $prepared_statement );
			
			foreach ( $fbreviews as $fbreview ) 
				{
					//echo $fbreview->post_title;
					if($fbreview->rating>0){
						$tempnum=$fbreview->rating;
					} else if($fbreview->recommendation_type=='positive'){
						$tempnum=5;
					} else if($fbreview->recommendation_type=='negative'){
						$tempnum=2;
					}
					$ratingsarray[]=$tempnum;
				}
			
			//print_r($ratingsarray);
			$ratingsarray = array_filter($ratingsarray);
			if(count($ratingsarray)>0){
				$avgdb = round(array_sum($ratingsarray) / count($ratingsarray), 3);
				$totaldb =  round(count($ratingsarray), 0);
				$wppro_total_avg_reviews_array[$pageid]['total_indb'] = $totaldb;
				if($avg>0){
					$wppro_total_avg_reviews_array[$pageid]['avg'] = round($avg,3);
				} else {
					//$wppro_total_avg_reviews_array[$pageid]['avg'] = $avgdb;
				}
				if($total>0){
					$wppro_total_avg_reviews_array[$pageid]['total'] = $total;
				} else {
					//$wppro_total_avg_reviews_array[$pageid]['total'] = $totaldb;
				}
				$wppro_total_avg_reviews_array[$pageid]['total_indb'] = $totaldb;
				$wppro_total_avg_reviews_array[$pageid]['avg_indb'] = $avgdb;
			}
		
		//ratings for badge 2
		$temprating = $this->wprp_get_temprating($ratingsarray);
		if(isset($temprating)){
			$wppro_total_avg_reviews_array[$pageid]['numr1'] = array_sum($temprating[1]);
			$wppro_total_avg_reviews_array[$pageid]['numr2'] = array_sum($temprating[2]);
			$wppro_total_avg_reviews_array[$pageid]['numr3'] = array_sum($temprating[3]);
			$wppro_total_avg_reviews_array[$pageid]['numr4'] = array_sum($temprating[4]);
			$wppro_total_avg_reviews_array[$pageid]['numr5'] = array_sum($temprating[5]);
		}

		$new_value = json_encode($wppro_total_avg_reviews_array, JSON_FORCE_OBJECT);
		update_option( $option, $new_value);
		
		//added in 10.9.3 now adding this to table wpfb_total_averages---------
		//will enventually replace the options save
		/*
			$valuearray['btp_id']=$pageid;
			$valuearray['total']='';
			if(isset($wppro_total_avg_reviews_array[$pageid]['total'])){
				$valuearray['total']=$wppro_total_avg_reviews_array[$pageid]['total'];
			}
			$valuearray['total_indb']=$wppro_total_avg_reviews_array[$pageid]['total_indb'];
			$valuearray['avg']='';
			if(isset($wppro_total_avg_reviews_array[$pageid]['avg'])){
				$valuearray['avg']=$wppro_total_avg_reviews_array[$pageid]['avg'];
			}
			$valuearray['avg_indb']=$wppro_total_avg_reviews_array[$pageid]['avg_indb'];
			$valuearray['numr1']=$wppro_total_avg_reviews_array[$pageid]['numr1'];
			$valuearray['numr2']=$wppro_total_avg_reviews_array[$pageid]['numr2'];
			$valuearray['numr3']=$wppro_total_avg_reviews_array[$pageid]['numr3'];
			$valuearray['numr4']=$wppro_total_avg_reviews_array[$pageid]['numr4'];
			$valuearray['numr5']=$wppro_total_avg_reviews_array[$pageid]['numr5'];
			
			$this->updatetotalavgreviewstableinsert('page',$valuearray);
			*/
		//---------------------------
	}
	
	//used to get back number of ratings for each value
	private function wprp_get_temprating($ratingsarray){
		//fist set to blank instead of null
		for ($x = 0; $x <= 5; $x++) {
			$temprating[$x][]=0;
		}
		foreach ( $ratingsarray as $tempnum ) 
		{
			//need to count number of each rating
			if($tempnum==1){
				$temprating[1][]=1;
			} else if($tempnum==2){
				$temprating[2][]=1;
			} else if($tempnum==3){
				$temprating[3][]=1;
			} else if($tempnum==4){
				$temprating[4][]=1;
			} else if($tempnum==5){
				$temprating[5][]=1;
			}
		}
		return $temprating;
	}
	

//-----form functions--------------
	/**
	 * Ajax, save form template to db
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_saveform_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$formtitle = sanitize_text_field($_POST['title']);
		$formid = sanitize_text_field($_POST['tid']);
		
		$femail =  sanitize_text_field($_POST['email']);
		$fcss = sanitize_textarea_field($_POST['css']);
		$fhtml = wp_kses_post($_POST['fhtml']);
		
		$createdtime = time();
		$formdata = sanitize_text_field(stripslashes($_POST['data']));
		$formarray = json_decode($formdata,true);
		
		//misc settings
		$fmiscdata = sanitize_text_field(stripslashes($_POST['misc']));
		$fmisc = json_decode($fmiscdata,true);	//keep as php object
		$fmiscencode = json_encode($fmisc);

		//================this can not handle 10 or 11
		foreach($formarray as $x_key => $x_value) {
			//get the field number from the string, then use it to setup array
			$indexnum = substr($x_key, 7, 1);
			$indexname = substr($x_key, 10, -1);
			//test for zero
			if( substr($x_key, 8, 1)=='0'){
				$indexnum = substr($x_key, 7, 2);
				$indexname = substr($x_key, 11, -1);
			}
			$fieldarray[$indexnum][$indexname]=$x_value;
		}
		//reindex here so we can drag and drop to reorder fields. Must do this to avoid json_encode adding 0 to json
		$fieldarray=array_values($fieldarray);

		$fieldarrayjson = json_encode($fieldarray);
		//die();
		//perform db search and return resultsform_fields
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_forms';
		//insert or update
			$data = array( 
				'title' => "$formtitle",
				'created_time_stamp' => "$createdtime",
				'form_fields' => "$fieldarrayjson",
				'notifyemail' => "$femail",
				'form_css' => "$fcss",
				'form_html' => "$fhtml",
				'form_misc' => "$fmiscencode",
				);
				//print_r($data);
			$format = array( 
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				); 		
			
		//insert or update if editing
		if($formid>0){
			$where = array( 'id' => "$formid" );
			//$formatwhere = array('%s');
			$insertrow = $wpdb->update( $table_name, $data, array( 'id' => $formid ), $format, array( '%d' ));
			$insertid =$formid;
		} else {
			$insertrow = $wpdb->insert( $table_name, $data, $format );
			$insertid = $wpdb->insert_id;
		}

		echo $insertid;
		die();
	}
	
	//
	/**
	 * Ajax, categories list html, used in Review List page and Templates page to select cat ids and post ids
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function wprp_getcategories_ajax(){
	//ini_set('display_errors',1);  
	//error_reporting(E_ALL);
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$idtype = sanitize_text_field($_POST['idtype']);
		$searchterm = sanitize_text_field($_POST['sterm']);
		$filterpagetype = sanitize_text_field($_POST['ttypeonly']);	//if yes then only searching for this type
		$orderby = sanitize_text_field($_POST['orderby']);
		$orderby = strtolower($orderby);
		$order = 'ASC';

		if (is_admin()) {
			//echo $idtype;
			if($idtype=='cat'){
				if($orderby==""){
					$orderby = 'name';
				} else if($orderby=="id"){
					$orderby = 'term_id';
				} else if($orderby=="description"){
					$orderby = 'description';
				} else if($orderby=="slug"){
					$orderby = 'slug';
				} else if($orderby=="count"){
					$orderby = 'count';
					$order='DESC';
				}
				
				//find all terms,then use to find all categories
				$defaulttaxes = ['category','post_tag'];
				$args = array(
				  'public'   => true,
				  '_builtin' => false
				   
				); 
				$customtaxonomies = get_taxonomies($args);
				$alltaxonomies = array_merge($defaulttaxes,array_values($customtaxonomies));
				
				$tablehtml = '<tr idtype="'.$idtype.'"><td>Oops, unable to retrieve list of post categories.</td></tr>';
				$catargs = array(
					'taxonomy'=> $alltaxonomies,
					'orderby' => $orderby,
					'order'   => $order
				);
				print_r($catargs);
				$categories = get_categories($catargs);
				print_r($categories);
				
				if(count($categories)>0){
					$tablehtml = '<thead><tr class="classidtype" idtype="'.$idtype.'">
					<td id="cb" class="manage-column column-cb check-column"></td><th scope="col" id="idnum" class="manage-column column-idnum column-primary sortable desc"><span class="sortspan">'.esc_html__('ID', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><span class="sortspan">'.esc_html__('Name', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="description" class="manage-column column-description sortable desc"><span  class="sortspan">'.esc_html__('Description', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="slug" class="manage-column column-slug sortable desc"><span class="sortspan">'.esc_html__('Slug', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="posts" class="manage-column column-posts num sortable desc"><span  class="sortspan">'.esc_html__('Taxonomy', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="posts" class="manage-column column-posts num sortable desc"><span  class="sortspan">'.esc_html__('Count', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th></tr></thead>
					<tbody id="the-list" data-wp-lists="list:tag">';
					foreach( $categories as $category ) {
						$category_link = sprintf( 
							'<a href="%1$s" alt="%2$s" target="_blank">%3$s</a>',
							esc_url( get_category_link( $category->term_id ) ),
							esc_attr( sprintf( __( 'View all posts in %s', 'wp-review-slider-pro' ), $category->name ) ),
							esc_html( $category->name )
						);
						 
						$tablehtml = $tablehtml . '<tr idtype="'.$idtype.'" id="tag-'.sprintf( esc_html__($category->term_id )).'"><td scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-'.sprintf( esc_html__($category->term_id )).'">Select another cat</label><input type="checkbox" name="catids[]" value="'.sprintf( esc_html__($category->term_id )).'" id="cb-select-'.sprintf( esc_html__($category->term_id )).'"></td><td class="idnum column-name has-row-actions column-primary" data-colname="ID"><strong>'.sprintf( esc_html__($category->term_id )).'</strong></td><td class="name column-name has-row-actions column-primary" data-colname="Name"><strong>'.sprintf($category_link ).'</strong></td><td class="description column-description" data-colname="Description">'. sprintf( esc_html__($category->description )) .'</td><td class="slug column-slug" data-colname="Slug">'.sprintf( esc_html__($category->slug )).'</td><td class="posts column-posts" data-colname="Taxonomy">' . sprintf( esc_html__($category->taxonomy )) . '</td><td class="posts column-posts" data-colname="Count">' . sprintf( esc_html__($category->count )) . '</td></tr>';
					} 
					$tablehtml = $tablehtml . '</tbody>';
				}
			} else if($idtype=='posts' || $idtype=='pages'){
				if($orderby=="" || $orderby=="name"){
					$orderby = 'post_name';
				} else if($orderby=="id"){
					$orderby = 'ID';
				} else if($orderby=="title"){
					$orderby = 'post_title';
				} else if($orderby=="modified"){
					$orderby = 'post_modified';
				} else if($orderby=="type"){
					$orderby = 'post_type';
				}
				
				$tablehtml = '<tr idtype="'.$idtype.'"><td>'.esc_html__('Oops, unable to retrieve list of post IDs.', 'wp-review-slider-pro').'</td></tr>';
				//get all post types here in an array
				$defaultposttypearray = ['page','post'];
				//print_r($defaultposttypearray);
					$args = array(
					   'public'   => true,
					   '_builtin' => false
					);
				$customposttypearray =get_post_types($args);
				//print_r($customposttypearray);
				$posttypearray = array_merge($defaultposttypearray,array_values($customposttypearray));
				
				if($idtype=='pages'){
					$posttypearray = ['page'];
				}
				if($filterpagetype=="yes"){
					//do not search for page type
					if (($key = array_search('page', $posttypearray)) !== false) {
						unset($posttypearray[$key]);
					}
				}
				$args = array(
					'orderby'    => 'menu_order',
					'numberposts'    => '-1',
					'post_type'   => $posttypearray,	//may need more here, product is for woocommerce
					's' => $searchterm,			//search parameter
					'sort_order' => 'asc'
				);
				//echo $filterpagetype;
				//print_r($args);
				$post_list = get_posts($args);
				if(count($post_list)>0){
					$tablehtml = '<thead><tr class="classidtype" idtype="'.$idtype.'">
					<td id="cb" class="manage-column column-cb check-column"></td><th scope="col" id="idnum" class="manage-column column-idnum column-primary sortable desc"><span class="sortspan">'.esc_html__('ID', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="name" class="manage-column column-name column-primary sortable desc"><span class="sortspan">'.esc_html__('Name', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="post_title" class="manage-column column-title sortable desc"><span  class="sortspan">'.esc_html__('Title', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="post_modified" class="manage-column column-post_modified sortable desc"><span class="sortspan">'.esc_html__('Modified', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th><th scope="col" id="post_type" class="manage-column column-post_type sortable desc"><span class="sortspan">'.esc_html__('Type', 'wp-review-slider-pro').'</span><span class="sorting-indicator"></span></th></tr></thead>
					<tbody id="the-list" data-wp-lists="list:tag">';
					foreach( $post_list as $post ) {
						$category_link = sprintf( 
							'<a href="%1$s" alt="%2$s" target="_blank">%3$s</a>',
							esc_url( get_category_link( $post->ID ) ),
							esc_attr( sprintf( __( 'View all posts in %s', 'wp-review-slider-pro' ), $post->post_name ) ),
							esc_html( $post->post_name )
						);
						 
						$tablehtml = $tablehtml . '<tr idtype="'.$idtype.'" id="tag-'.sprintf( esc_html__($post->ID )).'"><td scope="row" class="check-column"><label class="screen-reader-text" for="cb-select-'.sprintf( esc_html__($post->ID )).'">Select another post</label><input type="checkbox" name="catids[]" value="'.sprintf( esc_html__($post->ID )).'" id="cb-select-'.sprintf( esc_html__($post->ID )).'"></td><td class="idnum column-name has-row-actions column-primary" data-colname="ID"><strong>'.sprintf( esc_html__($post->ID )).'</strong></td><td class="name column-name has-row-actions column-primary" data-colname="Name"><strong>'.sprintf($category_link ).'</strong></td><td class="title column-title" data-colname="Title">'. sprintf( esc_html__($post->post_title )) .'</td><td class="post_modified column-post_modified" data-colname="post_modified">'.sprintf( esc_html__($post->post_modified )).'</td><td class="post_modified column-post_type" data-colname="post_type">'.sprintf( esc_html__($post->post_type )).'</td></tr>';
					} 
					$tablehtml = $tablehtml . '</tbody>';
				}
			}
		}
		
		echo $tablehtml;
		
		die();
	}
	
	
	/**
	 * download airbnb reviews when clicking the save button on Airbnb page
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
		//for ajax call to airbnb master
	public function wprevpro_ajax_download_airbnb_master() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$thisurlnum = $_POST['urlnum'];
		$getresponse = $this->wprevpro_download_airbnb_master($thisurlnum);
		//echo $getresponse;
		die();
	}
	
	private function getreviewurlfrommain_airbnb($urlvalue, $listing_id, $listtype){
		$response = wp_remote_get( $urlvalue );
		if ( is_array( $response ) ) {
		  $header = $response['headers']; // array of http header lines
		  $fileurlcontents = $response['body']; // use the content
		} else {
			echo esc_html__('Error finding reviews. Please contact plugin support.', 'wp-review-slider-pro');
			die();
		}
		
		//echo $fileurlcontents;

		//going to try to pull the API key
		$dom  = new DOMDocument();
		libxml_use_internal_errors( 1 );
		$dom->loadHTML( $fileurlcontents );

		$xpath = new DOMXpath( $dom );

		$totalrevessearch = $xpath->query('//div[contains(@class,"_vy3ibx")]');
		$temptotalreviews = intval($totalrevessearch->item(0)->nodeValue);
		//update the badge total and average here
		$reviewurl['totalreviews'] = $temptotalreviews;

		
		$titleNode = $xpath->query('//title');
		$temptitle = $titleNode->item(0)->nodeValue;
		$pieces = explode("-", $temptitle);
		
		$reviewurl['pagetitle']=$pieces[0];

		$items = $xpath->query( '//meta/@content' );
		//$items = $dom->getElementsByTagName("meta");
		$key='';
		$findme='"api_config":{';
		if( $items->length < 1 )
		{
			die( __('Error 1: No key found.', 'wp-review-slider-pro') );
		} else {
			//print_r($items);
			foreach ($items as $item) {
				if(strpos($item->nodeValue,$findme)){
					$nodearray = json_decode( $item->nodeValue, true );
					$key = $nodearray['api_config']['key'];
					$locale = $nodearray['locale'];
					//echo $key;
					//end the loop early
					break;
				}
			}
		}
		
		if($key==""){
			//first shorten the stringtotime
			$findme = '","api_config":';
			$pos = strpos($fileurlcontents, $findme);
			//echo "<br>".$pos;
			$shortstring = substr($fileurlcontents,$pos-20,200);
			//echo "<br>".$shortstring;
			//no key found using dom method, try getting with string method
			$findme = 'api","key":"';
			$pos = strpos($shortstring, $findme);
			//echo "<br>".$pos;
			$tempendstring = substr($shortstring,$pos,100);
			//echo "<br>".$tempendstring;
			$end = strpos($tempendstring, '"},');
			//echo "<br>".$end;
			$key = substr($shortstring,$pos+12,$end-12);
			//echo "<br>".$key;
			//now fine locale
			$findme = '"locale":"';
			$firstpos = strpos($shortstring, $findme);
			//echo "<br>".$firstpos;
			$locale = substr($shortstring,$firstpos+10,2);
			//echo "<br>".$locale;
			//die();
		}

		if($key==""){
			die( __('Error 2: No key found. This could be a temporary error, please try a few more times.', 'wp-review-slider-pro') );
		}
		//print_r($nodearray);
		//die();
		
		//use the key and the listing id to find review data					
		$rurl = "https://www.airbnb.com/api/v2/reviews?key=".$key."&locale=".$locale."&listing_id=".$listing_id."&role=guest&_format=for_p3&_order=language_country";
		
		if($listtype=='experience'){
			$rurl = "https://www.airbnb.com/api/v2/reviews?key=".$key."&locale=".$locale."&reviewable_id=".$listing_id."&reviewable_type=MtTemplate&role=guest&_format=for_experiences_guest_flow&_limit=".$limit."&_offset=".$offset."&_order=language_country";
						
		}

		$reviewurl['url'] = esc_url_raw($rurl);
		
		return $reviewurl;
		
	}
		
	/**
	 * download airbnb reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_download_airbnb_master($downloadurlnum = 'all') {
		
		
		$options = get_option('wprevpro_airbnb_settings');
		
			//check to see if only downloading one here, if not that skip and continue
			if($downloadurlnum!='all'){
				if($downloadurlnum==1){
					$numurl='';
				} else {
					$numurl=$downloadurlnum;
				}
				$currenturlmore = $options['airbnb_business_url'.$numurl];
				$currenturlmore = trim($currenturlmore);
				if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
					if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
						if (strpos($currenturlmore, '/users/show/') !== false) {
							$this->wpairbnb_download_airbnb_master_users($currenturlmore,$numurl);
						} else {
							$this->wprevpro_download_airbnb_master_perurl($currenturlmore,$numurl);
						}
					}
				} else {
					$errormsg = esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
			} else {
			//for cron get everything
				//make sure you have valid url, if not display message
				if (filter_var($options['airbnb_business_url'], FILTER_VALIDATE_URL)) {
					$currenturl = $options['airbnb_business_url'];
					$currenturl = trim($currenturl);
					if (strpos($currenturl, '/users/show/') !== false) {
							$this->wpairbnb_download_airbnb_master_users($currenturl,1);
					} else {
							$this->wprevpro_download_airbnb_master_perurl($currenturl,1);
					}
				} else {
					$errormsg = esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
				
				$totalmorepages = $options['airbnb_business_url_more'];
				for ($x = 2; $x <= $totalmorepages; $x++) {
					sleep(2);
					$numurl = $x;
					$currenturlmore = $options['airbnb_business_url'.$numurl];
					$currenturlmore = trim($currenturlmore);
						
					if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
						if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
							if (strpos($currenturl, '/users/show/') !== false) {
								$this->wpairbnb_download_airbnb_master_users($currenturlmore,$numurl);
							} else {
								$this->wprevpro_download_airbnb_master_perurl($currenturlmore,$numurl);
							}
						}
					}
				} 
			}
	}

    /**
	 * download airbnb users reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wpairbnb_download_airbnb_master_users($currenturlmore,$numurl) {
		//make sure file get contents is turned on for this host
		$errormsg ='';
		
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$options = get_option('wprevpro_airbnb_settings');
			
			//make sure you have valid url, if not display message
			if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
			  // you're good
			  //echo "valid url";
			  //if($options['airbnb_radio']=='yes'){
				  
				$airbnburl[1] = $currenturlmore;
				
				//loop to grab pages
				$reviews = [];
				$n=1;
				foreach ($airbnburl as $urlvalue) {

				
					//grab the page and save it locally
					$response = wp_remote_get($urlvalue);
							if ( is_array( $response ) ) {
							  $header = $response['headers']; // array of http header lines
							  $fileurlcontentsremote = $response['body']; // use the content
							} else {
								echo esc_html__('Error finding key. Please contact plugin support.', 'wp-review-slider-pro');
								die();
							}
					$savedurlfile = plugin_dir_path( __FILE__ ).'airbnbusercapture.html';
					$savefile = file_put_contents($savedurlfile,$fileurlcontentsremote );
					if(!file_exists($savedurlfile)){
						echo esc_html__('Error 102: Unable to get Airbnb page. Please make sure that your Hosting provider has file_put_contents turned on.', 'wp-review-slider-pro');
								die();
					}
					//================================
					
					if (ini_get('allow_url_fopen') == true) {
						$fileurlcontents=file_get_contents($savedurlfile);
					} else if (function_exists('curl_init')) {
						$fileurlcontents=$this->file_get_contents_curl($savedurlfile);
					} else {
						$fileurlcontents='<html><body>'.esc_html__('fopen is not allowed on this host.', 'wp-review-slider-pro').'</body></html>';
						$errormsg = $errormsg . '<p style="color: #A00;">'.esc_html__('fopen is not allowed on this host and cURL did not work either. Ask your web host to turn fopen on or fix cURL.', 'wp-review-slider-pro').'</p>';
						$this->errormsg = $errormsg;
						echo $errormsg;
						die();
					}

					// Find 20 reviews
					$i = 1;
					
					//find the $pagename
					//"title":"
					$titlepos = strpos($fileurlcontents, '"title":"');
					if(!$titlepos){
						$titlepos = strpos($fileurlcontents, '<title>');
						$titlehalfstring = substr($fileurlcontents,$titlepos+7);
						$titleendpos = strpos($titlehalfstring, '</title>');
						$pagename = substr($titlehalfstring,0,$titleendpos);
					} else {
						$titlehalfstring = substr($fileurlcontents,$titlepos+9);
						$titleendpos = strpos($titlehalfstring, '","');
						$pagename = substr($titlehalfstring,0,$titleendpos);
					}
					
					if($numurl>1){
						if($options['airbnb_business_url'.$numurl.'_name']!=''){
							//use saved value if set
							$pagename =$options['airbnb_business_url'.$numurl.'_name'];
						}
					} else {
						if($options['airbnb_business_url_name']!=''){
							$pagename =$options['airbnb_business_url_name'];
						}
					}
					
					
					$pageid = str_replace(" ","",$pagename)."_airbnb";
					//$pageid = preg_replace("/[^a-zA-Z0-9]/", "", $pagename)."_airbnb";
					$pageid = str_replace("'","",$pageid);
					$pageid = str_replace('"',"",$pageid);
					$pageid = str_replace("&#x27;","",$pageid);
					
					//echo $pagename;
					//die();
					
					//need to pull out json of reviews and make an array here
					//"recent_reviews_from_guest":
					$findme = '"recent_reviews_from_guest":';
						$pos = strpos($fileurlcontents, $findme);
						//echo "<br>".$pos;
						
						$temphalfstring = substr($fileurlcontents,$pos+28);
						//echo "<br>".$temphalfstring;
						$endpos = strpos($temphalfstring, "]");
						
						$finalstring = substr($temphalfstring,0,$endpos+1);
						//echo "<br>".$finalstring;
						
						$reviewArray = json_decode($finalstring, true);
						
						//print_r($reviewArray);
						//die();

					foreach ($reviewArray as $review) {

							if ($i > 21) {
									break;
							}
							$user_name='';
							$userimage='';
							$rating='';
							$datesubmitted='';
							$rtext='';
							// Find user_name
							if(isset($review['reviewer']['first_name'])){
								$user_name = addslashes($review['reviewer']['first_name']);
							}
							//echo $user_name;
							//die();
							
							// Find userimage ui_avatar, need to pull from lazy load varible
							//print_r($review->find('div.avatar-wrapper', 0)->find('img.lazy', 0));
							$userimage ='';
							if(isset($review['reviewer']['picture_url'])){
								$userimage = $review['reviewer']['picture_url'];
							}
							
							//echo $userimage ;
							
							//die();
							
							// find rating
							$rating ='';
							
							// find date
							if(isset($review['created_at'])){
								$datesubmitted = $review['created_at'];
							}
							
							// find text
							if(isset($review['comments'])){
								$rtext = $review['comments'];
							}

							if($user_name!=''){
								$review_length = str_word_count($rtext);
								if (extension_loaded('mbstring')) {
									$review_length_char = mb_strlen($rtext);
								} else {
									$review_length_char = strlen($rtext);
								}
								
								$pos = strpos($userimage, 'default_avatars');
								if ($pos === false) {
									$userimage = str_replace("60s.jpg","120s.jpg",$userimage);
								}
								//$timestamp = strtotime($datesubmitted);
								if($datesubmitted!=''){
								$timestamp = $this->myStrtotime($datesubmitted);
								//echo "<br>".$datesubmitted." - ".$timestamp;
								$unixtimestamp = $timestamp;
								$timestamp = date("Y-m-d H:i:s", $timestamp);
								}

								$hideme = '';
								//add check to see if already in db, skip if it is and end loop
								$reviewindb = 'no';
								//$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$unixtimestamp."' AND reviewer_name = '".trim($user_name)."' " );
								$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".trim($user_name)."' AND type = 'Airbnb' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
								
								if( empty( $checkrow ) ){
										$reviewindb = 'no';
								} else {
										$reviewindb = 'yes';
								}
								if( $reviewindb == 'no' )
								{
								
								$reviews[] = [
										'reviewer_name' => trim($user_name),
										'pagename' => trim($pagename),
										'pageid' => trim($pageid),
										'userpic' => $userimage,
										'rating' => $rating,
										'created_time' => $timestamp,
										'created_time_stamp' => $unixtimestamp,
										'review_text' => trim($rtext),
										'hide' => $hideme,
										'review_length' => $review_length,
										'review_length_char' => $review_length_char,
										'type' => 'Airbnb',
										'from_url' => $urlvalue,
										'from_url_review' => ''
								];
								}
								$review_length ='';
								$review_length_char='';
							}
							$i++;
					
					}

					//sleep for random 2 seconds
					sleep(rand(0,2));
					$n++;
					
					// clean up memory
					if (!empty($html)) {
						$html->clear();
						unset($html);
					}
				}
					// clean up memory
					if (!empty($html)) {
						$html->clear();
						unset($html);
					}
					 
				//print_r($reviews);
				//die();

				//add all new airbnb reviews to db
				$insertnum=0;
				foreach ( $reviews as $stat ){
					$insertnum = $wpdb->insert( $table_name, $stat );
				}
				//reviews added to db
				if($insertnum>0){
					$errormsg = $errormsg . count($stat).' Airbnb reviews downloaded.';
					$this->errormsg = $errormsg;
					//check to send notifications
					$this->sendnotificationemail($reviews, 'airbnb');
				} else {
					$errormsg = $errormsg . ' '.esc_html__('Unable to find any new reviews.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
				}
				
			 // }
			} else {
				$errormsg = $errormsg . ' '.esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
				$this->errormsg = $errormsg;
			}
			
			if($options['airbnb_radio']=='no'){
				$wpdb->delete( $table_name, array( 'type' => 'Airbnb' ) );
				//cancel wp cron job
			}
			

		if($errormsg !=''){
			echo $errormsg;
		}
	}	
		
	public function wprevpro_download_airbnb_master_perurl($currenturl,$urlnum) {
		
		//make sure file get contents is turned on for this host
		$errormsg ='';
		$ShowUserReviews = false;
		$listedurl = $currenturl;
				
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$options = get_option('wprevpro_airbnb_settings');
		
		$stripvariableurl = strtok($currenturl, '?');
		//find the listing_id
		$listing_id = (int) filter_var($stripvariableurl, FILTER_SANITIZE_NUMBER_INT);
		
		//find the reviewurl for this URL
				if(strpos($listedurl, '/experiences/') !== false){		
					//experiences, get different api key stuff
					$isexperience = true;
					$urldetails = $this->getreviewurlfrommain_airbnb($stripvariableurl, $listing_id, 'experience');
				} else {
					$isexperience = false;
					$urldetails = $this->getreviewurlfrommain_airbnb($stripvariableurl, $listing_id, 'room');
				}
				
		//$urldetails = $this->getreviewurlfrommain_airbnb($stripvariableurl, $listing_id);
		
		$limit=100;
		$offset=0;
		$airbnburl[1] =$urldetails['url']."&_limit=".$limit."&_offset=".$offset."";

		//loop to grab pages
		$reviews = [];
		$n=1;
		foreach ($airbnburl as $urlvalue) {
			
			$data = wp_remote_get( $urlvalue );
			if ( is_wp_error( $data ) ) 
			{
				$response['error_message'] 	= $data->get_error_message();
				$reponse['status'] 		= $data->get_error_code();
				print_r($response);
				die();
			}
			$pagedata = json_decode( $data['body'], true );

			//find airbnb business name and add to db under pagename
			$pagename ='';
			$pageid ='';
			if($urldetails['pagetitle']!=""){
				if($urlnum>1){
					if($options['airbnb_business_url'.$urlnum.'_name']!=''){
						//use saved value if set
						$pagename =$options['airbnb_business_url'.$urlnum.'_name'];
					} else {
						$pagename =$urldetails['pagetitle'].$urlnum;
					}
				} else {
					if($options['airbnb_business_url_name']!=''){
						$pagename =$options['airbnb_business_url_name'];
					} else {
						$pagename =$urldetails['pagetitle'];
					}
				}
				$pageid = str_replace(" ","",$pagename)."_airbnb";
				//$pageid = preg_replace("/[^a-zA-Z0-9]/", "", $pagename)."_airbnb";
				$pageid = str_replace("'","",$pageid);
				$pageid = str_replace('"',"",$pageid);
				
			}

			// Find 20 reviews
			$reviewsarray = $pagedata['reviews'];
//print_r($pagedata);

			foreach ($reviewsarray as $review) {

					$user_name='';
					$userimage='';
					$rating='';
					$datesubmitted='';
					$rtext='';
					
					//find reviewer_id
					if($review['reviewer']['id']){
						$reviewer_id = $review['reviewer']['id'];
					}
					if($isexperience){
						// Find user_name
						if($review['author']['first_name']){
							$user_name = $review['author']['first_name'];
						}
						
						// Find userimage ui_avatar
						if($review['author']['picture_url']){
							$userimage = $review['author']['picture_url'];
						}

					} else {
								
						// Find user_name
						if($review['reviewer']['first_name']){
							$user_name = $review['reviewer']['first_name'];
						}
						
						// Find userimage ui_avatar
						if($review['reviewer']['picture_url']){
							$userimage = $review['reviewer']['picture_url'];
						}
					}

					// find rating
					if($review['rating']){
						$rating = $review['rating'];
					}

					// find date created_at
					if($review['created_at']){
						$datesubmitted = $review['created_at'];
					}
					
					// find text
					if($review['comments']){
						$rtext = $review['comments'];
					}
					
					if($rating>0){
						$review_length = str_word_count($rtext);
						if (extension_loaded('mbstring')) {
							$review_length_char = mb_strlen($rtext);
						} else {
							$review_length_char = strlen($rtext);
						}
						
						$timestamp = $this->myStrtotime($datesubmitted);
						$unixtimestamp = $timestamp;
						$timestamp = date("Y-m-d H:i:s", $timestamp);
						$hideme = '';
						$user_name = addslashes($user_name);
						//add check to see if already in db, skip if it is and end loop
						$reviewindb = 'no';
						//$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$unixtimestamp."' AND reviewer_name = '".trim($user_name)."' " );
						
						$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".trim($user_name)."' AND type = 'Airbnb' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
						if( empty( $checkrow ) ){
								$reviewindb = 'no';
						} else {
								$reviewindb = 'yes';
						}
						if( $reviewindb == 'no' )
						{
							$furlrev = 'https://www.airbnb.com/users/show/'.trim($reviewer_id);
						$reviews[] = [
								'reviewer_name' => trim($user_name),
								'reviewer_id' => trim($reviewer_id),
								'pagename' => trim($pagename),
								'pageid' => trim($pageid),
								'userpic' => $userimage,
								'rating' => $rating,
								'created_time' => $timestamp,
								'created_time_stamp' => $unixtimestamp,
								'review_text' => trim($rtext),
								'hide' => $hideme,
								'review_length' => $review_length,
								'review_length_char' => $review_length_char,
								'type' => 'Airbnb',
								'from_url' => trim($listedurl),
								'from_url_review' => $furlrev,
						];
						}
						$review_length ='';
						$review_length_char='';
					}
			}

			//sleep for random 2 seconds
			sleep(rand(0,2));
			$n++;
			
			// clean up memory
			if (!empty($html)) {
				$html->clear();
				unset($html);
			}
		}
		 

		// clean up memory
		if (!empty($html)) {
			$html->clear();
			unset($html);
		}
		
		
		//add all new airbnb reviews to db
		$insertnum=0;
		if(count($reviews)>0){
					
					//send $reviews array to function to send email if turned on.
					$notifyoption = get_option('wprevpro_notifications_settings');
					if(isset($notifyoption['notifications_type_airbnb'])){
						if($notifyoption['notifications_type_airbnb']>0 || $notifyoption['notifications_type_airbnb_high']>0){
							$this->sendnotificationemail($reviews, "airbnb");
						}
					}
					//--------------------------------
			foreach ( $reviews as $stat ){
				$insertnum = $wpdb->insert( $table_name, $stat );
			}
		}
		
		if(trim($pageid)!=''){
			$this->updatetotalavgreviews('airbnb', trim($pageid), '', $urldetails['totalreviews']);
		}
		//reviews added to db
		if($insertnum>0){
			$errormsg = count($reviews) . ' '.esc_html__('Airbnb reviews downloaded.', 'wp-review-slider-pro');
			$this->errormsg = $errormsg;
		} else {
			$errormsg = $errormsg . ' '.esc_html__('Unable to find any new reviews.', 'wp-review-slider-pro');
			$this->errormsg = $errormsg;
		}


		if($errormsg !=''){
			echo $errormsg;
		}
	}
	
	
		
	/**
	 * download vrbo reviews when clicking the save button on vrbo page
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
		//for ajax call to vrbo master
	public function wprevpro_ajax_download_vrbo_master() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$thisurlnum = $_POST['urlnum'];
		$getresponse = $this->wprevpro_download_vrbo_master($thisurlnum);
		//echo $getresponse;
		die();
	}
	
	private function getreviewurlfrommain_vrbo($urlvalue){
		
		//echo $urlvalue;
		$response = wp_remote_get( $urlvalue );
		if ( is_array( $response ) ) {
		  $header = $response['headers']; // array of http header lines
		  $fileurlcontents = $response['body']; // use the content
		} else {
			echo esc_html__('Error finding reviews. Please contact plugin support.', 'wp-review-slider-pro');
			die();
		}
		
		//echo $fileurlcontents;
		//die();

		//going to try to pull the API key
		$dom  = new DOMDocument();
		libxml_use_internal_errors( 1 );
		$dom->loadHTML( $fileurlcontents );

		$xpath = new DOMXpath( $dom );

		$totalrevessearch = $xpath->query('//strong[contains(@class,"reviews-summary__num-reviews")]');
		//print_r($totalrevessearch);
		$temptotalreviews = intval($totalrevessearch->item(0)->nodeValue);
		//update the badge total and average here
		$reviewurl['totalreviews'] = $temptotalreviews;

		
		$titleNode = $xpath->query('//h1[contains(@class,"property-headline__headline")]');
		$temptitle = $titleNode->item(0)->nodeValue;
		$reviewurl['pagetitle']=$temptitle;

		if($listingId==""){
			//first shorten the stringtotime
			$findme = ',"listingId":"';
			$pos = strpos($fileurlcontents, $findme);
			//echo "<br>".$pos;
			$shortstring = substr($fileurlcontents,$pos-20,200);
			//echo "<br>".$shortstring;
			//no key found using dom method, try getting with string method
			$findme = ',"listingId":"';
			$pos = strpos($shortstring, $findme);
			//echo "<br>".$pos;
			$tempendstring = substr($shortstring,$pos,100);
			//echo "<br>tempendstring:".$tempendstring;
			$end = strpos($tempendstring, 'listingNumber');
			//echo "<br>end:".$end;
			$listingId = substr($shortstring,$pos+14,$end-17);
			//echo "<br>listingid:".$listingId."<br>";
		}

		if($listingId==""){
			die( esc_html__('Error 2: No listingId found. This could be a temporary error, please try a few more times.', 'wp-review-slider-pro')); 
		}
		//print_r($nodearray);
		//die();
		
		//https://www.vrbo.com/pdp/reviews?listingId=321.995508.1543464&site=vrbo&page=2&pageSize=6&variant=DESKTOP
		
		//use the key and the listing id to find review data					
		$rurl = "https://www.vrbo.com/pdp/reviews?listingId=".$listingId."&site=vrbo&variant=DESKTOP";
		$reviewurl['url'] = esc_url_raw($rurl);
		
		return $reviewurl;
		
	}
		
	/**
	 * download vrbo reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_download_vrbo_master($downloadurlnum = 'all') {

		$options = get_option('wprevpro_vrbo_settings');
			
			//check to see if only downloading one here, if not that skip and continue
			if($downloadurlnum!='all'){
				if($downloadurlnum==1){
					$numurl='';
				} else {
					$numurl=$downloadurlnum;
				}
				if (filter_var($options['vrbo_business_url'.$numurl], FILTER_VALIDATE_URL)) {
					$currenturlmore = $options['vrbo_business_url'.$numurl];
					$currenturlmore = trim($currenturlmore);
					if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {
							$this->wprevpro_download_vrbo_master_perurl($currenturlmore,$numurl);

					}
				} else {
					$errormsg = esc_html__('Please enter a valid URL.', 'wp-review-slider-pro');
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
			} else {
			//for cron get everything
				//make sure you have valid url, if not display message
				if (filter_var($options['vrbo_business_url'], FILTER_VALIDATE_URL)) {
					$currenturl = $options['vrbo_business_url'];
					$currenturl = trim($currenturl);
							$this->wprevpro_download_vrbo_master_perurl($currenturl,1);
				} else {
					$errormsg = 'Please enter a valid URL.';
					$this->errormsg = $errormsg;
					echo $errormsg;
				}
				$totalmorepages = $options['vrbo_business_url_more'];
				for ($x = 2; $x <= $totalmorepages; $x++) {
					sleep(2);
					$numurl = $x;
					if (filter_var($options['vrbo_business_url'.$numurl], FILTER_VALIDATE_URL)) {
						$currenturlmore = $options['vrbo_business_url'.$numurl];
						$currenturlmore = trim($currenturlmore);
						if (filter_var($currenturlmore, FILTER_VALIDATE_URL)) {

								$this->wprevpro_download_vrbo_master_perurl($currenturlmore,$numurl);
							
						}
					}
				} 
			}
	}

		
	public function wprevpro_download_vrbo_master_perurl($currenturl,$urlnum) {
		
		//make sure file get contents is turned on for this host
		$errormsg ='';
		$ShowUserReviews = false;
		$listedurl = $currenturl;
				
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$options = get_option('wprevpro_vrbo_settings');
		
		$stripvariableurl = strtok($currenturl, '?');
		
		//find the reviewurl for this URL
		$urldetails = $this->getreviewurlfrommain_vrbo($stripvariableurl);
		
		print_r($options);
		
		$limit=100;
		$offset=1;
		$vrbourl[1] =$urldetails['url']."&page=$offset&pageSize=$limit";
		
		//print_r($urldetails);
		//die();
		
		//loop to grab pages
		$reviews = [];
		$n=1;
		foreach ($vrbourl as $urlvalue) {
			
			$data = wp_remote_get( $urlvalue );
			if ( is_wp_error( $data ) ) 
			{
				$response['error_message'] 	= $data->get_error_message();
				$reponse['status'] 		= $data->get_error_code();
				print_r($response);
				die();
			}
			$pagedata = json_decode( $data['body'], true );

			//find vrbo business name and add to db under pagename
			$pagename ='';
			$pageid ='';
			if($urldetails['pagetitle']!=""){
				if($urlnum>1){
					if($options['vrbo_business_url'.$urlnum.'_name']!=''){
						//use saved value if set
						$pagename =$options['vrbo_business_url'.$urlnum.'_name'];
					} else {
						$pagename =$urldetails['pagetitle'].$urlnum;
					}
				} else {
					if($options['vrbo_business_url_name']!=''){
						$pagename =$options['vrbo_business_url_name'];
					} else {
						$pagename =$urldetails['pagetitle'];
					}
				}
				$pageid = str_replace(" ","",$pagename)."_vrbo";
				$pageid = str_replace("'","",$pageid);
				$pageid = str_replace('"',"",$pageid);
				//$pageid = preg_replace("/[^a-zA-Z0-9]/", "", $pagename)."_vrbo";
			}

			// Find 20 reviews
			$reviewsarray = $pagedata['reviews'];
//print_r($pagedata);

			foreach ($reviewsarray as $review) {

					$user_name='';
					$userimage='';
					$rating='';
					$datesubmitted='';
					$rtext='';
					$reviewer_id='';
					
					// Find user_name
					if($review['reviewer']['nickname']){
						$user_name = $review['reviewer']['nickname'];
					}
					
					// Find userimage ui_avatar
					if($review['reviewer']['profileImage']){
						$userimage = $review['reviewer']['profileImage'];
					}


					// find rating
					if($review['rating']){
						$rating = $review['rating'];
					}

					// find date created_at
					if($review['datePublished']){
						$datesubmitted = $review['datePublished'];
					}
					
					// find headline
					
					if($review['headline']){
						$review_title = $review['headline'];
					}
					if($review['body']){
						$rtext = $review['body'];
					}
					
					//owner response
					//{ "id":12630808, "name":"Response from the owner", "date":"2018-08-24", "comment":"Raul - this is a very bad example of how the trip went. Sorry you feel that way, and hope you have better luck with another charter..." }
					//{"id":16369073,"name":"Response from the owner","date":"2014-05-29","comment":"Thank You - Jennifer.  Your family is always a pleasure to have aboard.  Fish On!!"}
					
					if($review['response']['body']){
						$ownerresponsearray = [];
						$responsebody = $review['response']['body'];
						$ownerresponsearray['id']='';
						$ownerresponsearray['name']='Owner';
						$ownerresponsearray['date']='';
						$ownerresponsearray['comment']=$responsebody;
						$ownerresponsearray = json_encode($ownerresponsearray);
					} else {
						$ownerresponsearray ='';
					}
					
					
					if($rating>0){
						$review_length = str_word_count($rtext);
						if (extension_loaded('mbstring')) {
							$review_length_char = mb_strlen($rtext);
						} else {
							$review_length_char = strlen($rtext);
						}
						
						$timestamp = $this->myStrtotime($datesubmitted);
						$unixtimestamp = $timestamp;
						$timestamp = date("Y-m-d H:i:s", $timestamp);
						$hideme ='';
						
						//add check to see if already in db, skip if it is and end loop
						$reviewindb = 'no';
						
						$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".trim($user_name)."' AND type = 'VRBO' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
						
						
						if( empty( $checkrow ) ){
								$reviewindb = 'no';
						} else {
								$reviewindb = 'yes';
						}
						if( $reviewindb == 'no' )
						{
							$furlrev = 'https://www.vrbo.com/users/show/'.trim($reviewer_id);
						$reviews[] = [
								'reviewer_name' => trim($user_name),
								'reviewer_id' => trim($reviewer_id),
								'pagename' => trim($pagename),
								'pageid' => trim($pageid),
								'userpic' => $userimage,
								'rating' => $rating,
								'created_time' => $timestamp,
								'created_time_stamp' => $unixtimestamp,
								'review_text' => trim($rtext),
								'review_title' => trim($review_title),
								'hide' => $hideme,
								'review_length' => $review_length,
								'review_length_char' => $review_length_char,
								'type' => 'VRBO',
								'from_url' => trim($listedurl),
								'owner_response' => $ownerresponsearray,
						];
						}
						$review_length ='';
						$review_length_char='';
					}
			}

			//sleep for random 2 seconds
			sleep(rand(0,2));
			$n++;
			
			// clean up memory
			if (!empty($html)) {
				$html->clear();
				unset($html);
			}
		}
		 

		// clean up memory
		if (!empty($html)) {
			$html->clear();
			unset($html);
		}
		
		
		//add all new vrbo reviews to db
		$insertnum=0;
		if(count($reviews)>0){
			foreach ( $reviews as $stat ){
				$insertnum = $wpdb->insert( $table_name, $stat );
			}
			
			//send $reviews array to function to send email if turned on.
			$this->sendnotificationemail($reviews, "vrbo");
		}
		
		if(trim($pageid)!=''){
			$this->updatetotalavgreviews('vrbo', trim($pageid), '', $urldetails['totalreviews']);
		}
		//reviews added to db
		if($insertnum>0){
			$errormsg = count($reviews) . ' '.esc_html__('VRBO reviews downloaded.', 'wp-review-slider-pro');
			$this->errormsg = $errormsg;
		} else {
			$errormsg = $errormsg . ' '.esc_html__('Unable to find any new reviews.', 'wp-review-slider-pro');
			$this->errormsg = $errormsg;
		}


		if($errormsg !=''){
			echo $errormsg;
		}
	}
	
	/**
	 * sync woocommerce reviews when clicking the save button on WooCommerce page
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_download_woo() {
      global $pagenow;
      if (isset($_GET['settings-updated']) && $pagenow=='admin.php' && current_user_can('export') && $_GET['page']=='wp_pro-get_woo') {
		$this->wprevpro_download_woo_master();
      }
    }

	/**
	 * download woocommerce reviews
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_download_woo_master() {
		$options = get_option('wprevpro_woo_settings');
		//print_r($options);
		//Array([woo_radio_sync] => yes,[woo_sync_all] => all)

		if($options['woo_radio_sync']=='yes'){
			//grab all woocommerce reviews depending on settings
			global $wpdb;
			
			
			if($options['woo_sync_all']=='all'){
				$args = array(
					'type__in'  => 'review',
					 'parent'      => 0,	//don't get responses yet
					 'status' => 'all',
				);
			} else if($options['woo_sync_all']=='approved'){
				$args = array(
					'type__in'  => 'review',
					 'parent'      => 0,	//don't get responses yet
					 'status' => 'approve',
				);
			}
			$comments = get_comments( $args );
			
			//print_r($comments);
			
			//echo get_avatar( 'jgwhite33@hotmail.com', 32 );
			//echo get_avatar_url( 'jgwhite33@hotmail.com', 32 );
			
			//loop through the comments, find the avatar, and the rating, date, product image, product title, cat id, prod id, text, etc...
			foreach ($comments as $comment) {
					// Output comments etc here
									
					$table_name = $wpdb->prefix . 'wpfb_reviews';
					//add check to see if already in db, skip if it is and end loop
					$reviewindb = 'no';
					$unixtimestamp = $this->myStrtotime($comment->comment_date);
					$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE created_time_stamp = '".$unixtimestamp."' " );
					
					$tempreviewarray = $this->wprevpro_returncommentinfoarray($comment,$options['woo_name_options']);
						
					if( empty( $checkrow ) ){
						$reviewindb = 'no';
						$reviews['add'][] = $tempreviewarray;
					} else {
						$reviewindb = 'yes';
						$reviews['update'][] = $tempreviewarray;
					}
					unset($tempreviewarray);
					
			}
			
			//insert or update array in to reviews table.
			if(isset($reviews['add']) && count($reviews['add'])>0){
				foreach ( $reviews['add'] as $stat ){
					$insertnum = $wpdb->insert( $table_name, $stat );
					//update badge totals
					$this->updatetotalavgreviews('woocommerce', $stat['pageid'], '','');
				}
				$this->errormsg = count($reviews['add']).' '.esc_html__('added to database.', 'wp-review-slider-pro');
				
				//send $reviews array to function to send email if turned on.
				$this->sendnotificationemail($reviews['add'], "woocommerce");
			}
			if(isset($reviews['update']) && count($reviews['update'])>0){
				foreach ( $reviews['update'] as $stat ){
					$tempreviewid = $stat['reviewer_id'];
					$insertnum = $wpdb->update( $table_name, $stat,array( 'reviewer_id' => $tempreviewid ) );
					//update badge totals
					$this->updatetotalavgreviews('woocommerce', $stat['pageid'], '','');
				}
				$this->errormsg = count($reviews['update']).' '.esc_html__('updated in database.', 'wp-review-slider-pro');
			}

			//we also need to hook in to when a new comment is added, deleted, approved, unapproved
			
		}
		
	}
	
	/**
	 * ran when a new comment is inserted, deleted (or spam), or updated (approved, unapproved)
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprevpro_returncommentinfoarray($comment,$nameoption = 'author'){
		//print_r($comment);
		$user_name = $comment->comment_author;
		$results['reviewer_name'] = $user_name;
		$results['reviewer_id'] = 'woo_'.str_replace(' ','',$user_name)."_".$comment->user_id."_".$comment->comment_ID;
			//if we need first or last name find it here
			if($nameoption!='author'){
				$user = get_user_by( 'email', $comment->comment_author_email );
				if ( ! empty( $user ) ) {
				//echo ‘User is ‘ . $user->first_name . ‘ ‘ . $user->last_name;
					if($nameoption=='first' && isset($user->first_name) && $user->first_name!=''){
						$results['reviewer_name'] = $user->first_name;
					} else if($nameoption=='last' && isset($user->last_name) && $user->last_name!=''){
						$results['reviewer_name'] = $user->last_name;
					} else if($nameoption=='firstlast'){
						if((isset($user->last_name) && $user->last_name!='') || (isset($user->first_name) && $user->first_name!='')){
						$results['reviewer_name'] = $user->first_name.' '.$user->last_name;
						}
					}
				}
			}
		
		$pageid = $comment->comment_post_ID;
		$results['pageid'] = $pageid;
		$post = get_post( $pageid ); 
		//print_r($post);
		$results['pagename'] = $post->post_title;	//use for the product title
		$results['from_url'] = get_permalink($pageid);
		$results['userpic'] = get_avatar_url( $comment->comment_author_email, 80 );
		$results['rating'] = get_comment_meta( $comment->comment_ID, 'rating', true );
		$unixtimestamp = $this->myStrtotime($comment->comment_date);
		$results['created_time_stamp'] = $unixtimestamp;
		$results['created_time'] = date("Y-m-d H:i:s", $unixtimestamp);
		$results['review_text'] = $comment->comment_content;
		$results['review_length'] = str_word_count($results['review_text']);
		if (extension_loaded('mbstring')) {
			$results['review_length_char'] = mb_strlen($results['review_text']);
		} else {
			$results['review_length_char'] = strlen($results['review_text']);
		}
					
		$hideme = $comment->comment_approved;
		if($hideme==0){
			$results['hide'] = 'yes';
		} else {
			$results['hide'] = 'no';
		}
		//["-107-"],["-18-","-25-"]
		$posts = array();
		$posts[] = "-".intval($comment->comment_post_ID)."-";	//encoding here so we can add more later
		$results['posts'] = json_encode($posts);
		//find cats
		$catidarray = array();
		//woocommerce check 
		$categories = get_the_terms( $pageid, 'product_cat');
		if(is_array($categories)){
			$arrlength = count($categories);
			if($arrlength>0 && $categories){
				for($x = 0; $x < $arrlength; $x++) {
					$catidarray[] = "-".$categories[$x]->term_id."-";	//array containing just the cat_IDs that this post belongs to, dashes added so we can use like search
				}
			}
		}
		$results['categories'] = json_encode($catidarray);
		$results['type'] = 'WooCommerce';
		
		//product image
		$productimage = wp_get_attachment_image_src( get_post_thumbnail_id( $pageid ), 'thumbnail' );
		if(!$productimage){
			$productimage[0] = '';
		}
		$results['miscpic'] = $productimage[0];
					
		//print_r($results);
		return $results;
	}	
	 
	public function wprevpro_woo_deletecomment($comment){
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$commentinfoarray = $this->wprevpro_returncommentinfoarray($comment);
		$wpdb->delete( $table_name, array( 'reviewer_id' => $commentinfoarray['reviewer_id'] ) );
		//update badge totals
		$this->updatetotalavgreviews('woocommerce', trim($pageid), '','');
	}
	
	public function wprevpro_woo_changestatus($new_status,$old_status,$comment){
		if($new_status=='spam' || $new_status=='trash'){
			$this->wprevpro_woo_deletecomment($comment);
		} else{
			//comment approved or unapproved via ajax
			$comment_id = $comment->comment_ID;
			$this->wprevpro_woo_iud_comment($comment_id);
		}
	}
	
	
	public function wprevpro_woo_iud_comment($comment_ID,$info=''){

		//echo "comment inserted or updated, get info and insert or update reviews table";
		$comment = get_comment( $comment_ID );
			if(is_object($comment)){
				if($comment->comment_type=="review"){
					global $wpdb;
					$table_name = $wpdb->prefix . 'wpfb_reviews';
					
					//get comment data and insert or update below.
					$commentinfoarray = $this->wprevpro_returncommentinfoarray($comment);

					//if marked as spam them remove from wpprorev db
					if($comment->comment_approved=='spam' || $comment->comment_approved=='trash'){
						$this->wprevpro_woo_deletecomment($comment);
					}
					
					//if radio option is set to Approved only and this comment is unapproved then do nothing, $options['woo_sync_all']=='approved'
					$options = get_option('wprevpro_woo_settings');
					if($options['woo_sync_all']=='approved' && $comment->comment_approved!=1){
						//don't do anything since not syncing unapproved comments
					} else {
						//find out if we need to update or insert
						$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_id = '".$commentinfoarray['reviewer_id']."' " );
						if( empty( $checkrow ) ){
							//not in db, insert it
							$insertnum = $wpdb->insert( $table_name, $commentinfoarray );
						} else {
							//is in db, update it.
							$insertnum = $wpdb->update( $table_name, $commentinfoarray,array( 'reviewer_id' => $commentinfoarray['reviewer_id'] ) );
						}
						//update badge totals
						$this->updatetotalavgreviews('woocommerce', trim($pageid), '','');
					}
				}
			}
	}
	public function wprevpro_woo_iud_comment_delete($comment_ID,$comment){
		//comment being deleted, delete from our db as well
		$this->wprevpro_woo_deletecomment($comment);
	}
	//============end woocommerce=========================
	
	//================for review funnels=======================================
	
	/**
	 * used to call review funnel server to download reviews. server will check one more time for valid license
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	 //list the funnel jobs
	public function wprp_revfunnel_listjobs_ajax(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$fid = sanitize_text_field($_POST['fid']);
		$fid =intval($fid );
		$frlicenseid = get_option( 'wprev_fr_siteid' );
		$options = get_option('wprevpro_funnel_options');
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviewfunnel';
		$reviewfunneldetails = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $fid" );
		$dbsiteinfo_id = $options['dbsiteinfo_id'];	//this is the id of the site in the db on remote server
		
		//continue here
		$serverurl = 'https://funnel.ljapps.com/listaddprofilejobs?sid='.intval($dbsiteinfo_id).'&frlicenseid='.intval($frlicenseid).'&scrapeurl='.urlencode($reviewfunneldetails->url).'&scrapequery='.urlencode($reviewfunneldetails->query);
		
		$resultarray['serverurl']=$serverurl;
		
		$response = wp_remote_get( $serverurl );
			
		//print_r($response);
			$resultarray['result']='';
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
				$listjobarray = json_decode($body,true);
				//print_r($listjobarray);
				//if we have an error adding job then we display a warning.
				if($listjobarray['ack']=='querydb'){
					$resultarray['ack']=$listjobarray['ack'];
					$addjobresultarray = $listjobarray['result'];
					//catch error from reviewscrape
					if(!is_array($addjobresultarray)){
						$resultarray['ack']='error';
						$resultarray['msg']= esc_html__('Error! DB error.', 'wp-review-slider-pro').' '.$addjobresultarray['message'].' '.esc_html__('Contact Support.', 'wp-review-slider-pro');
					} else {
						//list jobs
						$resultarray['result']=$listjobarray['result'];
					}
				} else if($listjobarray['ack']=='error'){
					$resultarray['ack']='error';
					$resultarray['msg']=esc_html__('Error!', 'wp-review-slider-pro').' '.$listjobarray['ackmessage'].' '.esc_html__('Contact Support.', 'wp-review-slider-pro');
				} else {
					$resultarray['ack']='error';
					$resultarray['msg']=esc_html__('Error! Trouble communicating with server.', 'wp-review-slider-pro').' '.$serverurl;
				}
			} else {
				$resultarray['ack']='error';
				$resultarray['msg']=esc_html__('Error! Trouble communicating with server.', 'wp-review-slider-pro').' '.$serverurl;
			}
			
		echo json_encode($resultarray);
		die();
		
	}

	/**
	 * used to call review funnel server to add scrape job. called via ajax on review funnel page. server will check one more time for valid license
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	//function to update total credits used and left__373c0__2pnx_
	public function updatecreditsoptions(){
		$frlicenseid = get_option( 'wprev_fr_siteid' );
		$frsiteurl = get_option( 'wprev_fr_url' );
		$wpsiteurl = get_site_url();
		$response = wp_remote_get( 'https://funnel.ljapps.com/frstats?frlicenseid='.$frlicenseid.'&frsiteurl='.$frsiteurl.'&wpsiteurl='.$wpsiteurl );
		 
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$headers = $response['headers']; // array of http header lines
			$body    = $response['body']; // use the content
		}
		$licensecheckarray = json_decode($body,true);

		//error check
		if($licensecheckarray['ack']!="success"){
			echo '<div class="w3-panel w3-red"><p>'.__( 'Error: Unable to check your review credit balance. Please try again.', 'wp-review-slider-pro' ).$licensecheckarray['ackmessage'].'</p></div> ';
			die();
		}

		//print_r($licensecheckarray);
		$statsarray=$licensecheckarray['stats'];

		//update options in db, so we can check before we make call to server, also do this when using cron job
		$tempoptions['ack']=$licensecheckarray['ack'];
		$tempoptions['totalreviewbank']=$statsarray['totalreviewbank'];
		$tempoptions['totalreviewcreditsused']=$statsarray['totalreviewcreditsused'];
		$tempoptions['dbsiteinfo_id']=$statsarray['id'];
		update_option('wprevpro_funnel_options',$tempoptions);
	}
	
	public function wprp_revfunnel_addprofile_ajax(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$fid = sanitize_text_field($_POST['fid']);
		$diffparam = sanitize_text_field($_POST['rv']);	//only get new reviews or not usediff or nodiff, usediff is new only
		$fid =intval($fid );
		$resultarray = $this->wprp_revfunnel_addprofile_ajax_go($fid, $diffparam);
		echo json_encode($resultarray);
		die();
	}
	//calling from above and also calling this from cron job
	public function wprp_revfunnel_addprofile_ajax_go($fid, $diffparam){
		$frlicenseid = get_option( 'wprev_fr_siteid' );
		$frsiteurl = urlencode(get_option( 'wprev_fr_url' ));
		$resultarray['job_id']='';
		$resultarray['scrapeurl']='';
		
		//run check to update total credits used
		$this->updatecreditsoptions();
		
		//make a call to server, only if we are not out of calls and this site has passed check.
		//$options['ack'], $options['totalreviewbank'], $options['totalreviewcreditsused']
		$options = get_option('wprevpro_funnel_options'); 
		if($options['ack']!="success"){
			//return error message here
			$resultarray['ack']='error';
			$resultarray['msg']=esc_html__('Oops, it looks like this site does not have a valid license.', 'wp-review-slider-pro').' ';

		}else if($options['totalreviewbank']<$options['totalreviewcreditsused']){
			//return error message here
			$resultarray['ack']='error';
			$resultarray['msg']=esc_html__('Oops, it appears that you have used up your review quota.', 'wp-review-slider-pro').' ';
			
		} else {
			$resultarray['ack']='success';
			$resultarray['msg']='passed checks';
			//now find info for this review funnel from WP db
			global $wpdb;
			$table_name = $wpdb->prefix . 'wpfb_reviewfunnel';
			$reviewfunneldetails = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $fid" );
			$scrapeurl = $reviewfunneldetails->url;
			$scrapequery =  $reviewfunneldetails->query;
			$scrapefromdate = $reviewfunneldetails->from_date;
			$scrapeblocks = $reviewfunneldetails->blocks;
			//check for google and use query if set
			if($reviewfunneldetails->site_type=="google" || $reviewfunneldetails->site_type=="Google"){
				$scrapefromdate='';
				$scrapeurl='';
			} else {
				//$scrapeblocks='';
				$scrapequery='';
			}
			
			//$resultarray['job_id']='35';
			$resultarray['dbsiteinfo_id']=$options['dbsiteinfo_id'];
			$dbsiteinfo_id = $options['dbsiteinfo_id'];	//this is the id of the site in the db on remote server
			
			//echo  'https://funnel.ljapps.com/addprofile?sid='.intval($dbsiteinfo_id).'&frlicenseid='.intval($frlicenseid).'&frsiteurl='.$frsiteurl.'&scrapeurl='.urlencode($scrapeurl).'&scrapequery='.urlencode($scrapequery).'&scrapefromdate='.$scrapefromdate.'&scrapeblocks='.$scrapeblocks.'&diffparam='.$diffparam;
			
			//continue here
			$wpsiteurl = urlencode(get_site_url());
			
			$resultarray['scrapejoburl'] = 'https://funnel.ljapps.com/addprofile?sid='.intval($dbsiteinfo_id).'&frlicenseid='.intval($frlicenseid).'&frsiteurl='.$frsiteurl.'&scrapeurl='.urlencode($scrapeurl).'&scrapequery='.urlencode($scrapequery).'&scrapefromdate='.$scrapefromdate.'&scrapeblocks='.$scrapeblocks.'&diffparam='.$diffparam.'&wpsiteurl='.$wpsiteurl;
			
			//echo $resultarray['scrapejoburl'];
			
			$response = wp_remote_get( $resultarray['scrapejoburl']);
			
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
				$addscrapeprofilearray = json_decode($body,true);
				//if we have an error adding job then we display a warning.
				if($addscrapeprofilearray['ack']=='curl'){
					$resultarray['ack']=$addscrapeprofilearray['ack'];
					$addjobresultarray = json_decode($addscrapeprofilearray['result'],true);
					//print_r($addjobresultarray);
					//catch error from reviewscrape
					if(!$addjobresultarray['success']){
						$resultarray['ack']='error';
						$resultarray['msg']='Error! '.$addjobresultarray['message'].'. Contact Support.';
					} else {
						//grab the job_id and save to db with this funnel
						$job_id = $addjobresultarray['job_id'];
						$resultarray['job_id']=$job_id;
						//====job_ids are saved on server calls table. we can ping it for updates.
						//if $resultarray['job_id'] is not blank then we have success.
					}
				} else if($addscrapeprofilearray['ack']=='error'){
					$resultarray['ack']='error';
					$resultarray['msg']='Error! '.$addscrapeprofilearray['ackmessage'].' '.esc_html__('Contact Support.', 'wp-review-slider-pro');
				}
			} else {
				$resultarray['ack']='error';
				$resultarray['msg']=esc_html__('Error! Can not wp_remote_get Contact Support.', 'wp-review-slider-pro');
			}
		}
		return $resultarray;
	}
	
	/**
	 * used to call review funnel server to download reviews. server will check one more time for valid license
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprp_revfunnel_getrevs_ajax(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$job_id = sanitize_text_field($_POST['jid']);
		$fid = sanitize_text_field($_POST['fid']);
		$pagenum = sanitize_text_field($_POST['pnum']);
		$perpage = sanitize_text_field($_POST['perp']);
		
		$resultarray = $this->wprp_revfunnel_getrevs_ajax_go($job_id,$fid,$pagenum,$perpage);
		//use resultarray to communicate back to javascript
		echo json_encode($resultarray);
		die();
	}
	public function wprp_revfunnel_getrevs_ajax_go($job_id,$fid,$pagenum=1,$perpage=100){
		
		$frlicenseid = get_option( 'wprev_fr_siteid' );
		$fid =intval($fid );
		$pagenum =intval($pagenum );
		$perpage =intval($perpage );
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviewfunnel';
		$reviewfunneldetails = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $fid" );
		$pagename = $reviewfunneldetails->title;
		$pageid = str_replace(" ","",$pagename)."_".$reviewfunneldetails->id;
		$pageid = str_replace("'","",$pageid);
		$pageid = str_replace('"',"",$pageid);

		$sitetype = $reviewfunneldetails->site_type;
		if($sitetype=='Google'){
			$listedurl= 'https://www.google.com/search?q='.urlencode($reviewfunneldetails->query);
		} else {
			$listedurl= urldecode($reviewfunneldetails->url);
		}
		$tempcats='';
		if(isset($reviewfunneldetails->categories)){
		$tempcats=$reviewfunneldetails->categories;
		}
		$tempposts='';
		if(isset($reviewfunneldetails->posts)){
		$tempposts=$reviewfunneldetails->posts;
		}

		//print_r($reviewfunneldetails);
			
		//make a call to server, only if we are not out of calls and this site has passed check.
		//$options['ack'], $options['totalreviewbank'], $options['totalreviewcreditsused']
		$resultarray['ack']='';
		$resultarray['msg']='';
		$resultarray['dbsiteinfo_id']='';
		$resultarray['numinserted']='';
		$resultarray['numreturned']='';
		$resultarray['scraperesult']='';
		
		$options = get_option('wprevpro_funnel_options'); 
		if($options['ack']!="success"){
			//return error message here
			$resultarray['ack']='error';
			$resultarray['msg']=esc_html__('Oops, it looks like this site does not have a valid license.', 'wp-review-slider-pro').' ';

		} else {
			$resultarray['ack']='success';
			$resultarray['msg']='passed checks';
			$resultarray['dbsiteinfo_id']=$options['dbsiteinfo_id'];
			$dbsiteinfo_id = $options['dbsiteinfo_id'];	//this is the id of the site in the db on remote server
			//continue here
			$callurl = 'https://funnel.ljapps.com/getrevs?jid='.intval($job_id).'&sid='.intval($dbsiteinfo_id).'&frlicenseid='.intval($frlicenseid).'&pnum='.$pagenum.'&perp='.$perpage;
			//echo $callurl;
			$resultarray['callurl']=$callurl;
			$response = wp_remote_get( $callurl );
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
			}
			$getrevsarray = json_decode($body,true);
			$resultarray['scraperesult']=$getrevsarray;
			$scraperesultreviewsarray = json_decode($getrevsarray['result'],true);
			//print_r($scraperesultreviewsarray);
			$reviewsarray = $scraperesultreviewsarray['reviews'];
			//print_r($reviewsarray);
			
			//insert this in to the review database, return success message and count or error
			//add check to see if already in db, skip if it is and end loop
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			if(is_array($reviewsarray) && count($reviewsarray)>0){
				$resultarray['numreturned'] = count($reviewsarray);
				foreach($reviewsarray as $item) {
					$reviewindb = 'no';

					$reviewer_name = trim($item['name']);
					$reviewer_name =$this->changelastname($reviewer_name, $reviewfunneldetails->last_name);
					$review_text = trim($item['review_text']);
					$review_length = str_word_count($review_text);
					if (extension_loaded('mbstring')) {
						$review_length_char = mb_strlen($review_text);
					} else {
						$review_length_char = strlen($review_text);
					}
					
					$searchname = addslashes($reviewer_name);
					$pagename = trim($pagename);
					$timestamp = $this->myStrtotime($item['date']);
					$unixtimestamp = $timestamp;
					$timestamp = date("Y-m-d H:i:s", $timestamp);
						
					$unique_id = trim($item['unique_id']);
						
					$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$searchname."' AND type = '".$sitetype."' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR unique_id = '".$unique_id."' OR created_time_stamp = '".$unixtimestamp."')" );
					
					//another check in case the name as been changed, check the unique_id
					$checkrow2 = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE unique_id = '".$unique_id."' AND type = '".$sitetype."' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );

					$owner_response = '';
					if(isset($item['response']) && is_array($item['response'])){
						$owner_response = json_encode($item['response']);
					}
					
					if( empty( $checkrow ) ||  empty(checkrow2) ){
						
						$reviews[] = [
							'reviewer_name' => $reviewer_name,
							'reviewer_id' => trim($item['id']),
							'pagename' => trim($pagename),
							'pageid' => trim($pageid),
							'userpic' => trim($item['profile_picture']),
							'rating' => $item['rating_value'],
							'created_time' => $timestamp,
							'created_time_stamp' => $unixtimestamp,
							'review_text' => $review_text,
							'hide' => '',
							'review_length' => $review_length,
							'review_length_char' => $review_length_char,
							'type' => $sitetype,
							'review_title' => trim($item['review_title']),
							'from_url' => trim($listedurl),
							'from_url_review' => trim($item['url']),
							'company_title' => trim($item['reviewer_title']),
							'location' => trim($item['location']),
							'verified_order' => trim($item['verified_order']),
							'language_code' => trim($item['language_code']),
							'unique_id' => trim($item['unique_id']),
							'meta_data' => trim($item['meta_data']),
							'categories' => trim($tempcats),
							'posts' => trim($tempposts),
							'owner_response' => trim($owner_response),
						];
					}
				}
				
				//insert or update array in to reviews table.
				$totalreviewsinserted=0;
				if(isset($reviews) && count($reviews)>0){
					foreach ( $reviews as $stat ){
						$statobj ='';
						$pictocopy='';
						//print_r($stat);
						$insertnum = $wpdb->insert( $table_name, $stat );
						$stat['id']=$wpdb->insert_id;
						$this->my_print_db_error();
						$totalreviewsinserted = $totalreviewsinserted + $insertnum;
						//if inserted and save avatar local turned on, then try to copy here
						if($stat['id']>0 && $reviewfunneldetails->profile_img=="yes" && $stat['userpic']!=''){
							$pictocopy=$stat['userpic'];
							$statobj = (object) $stat;
							$this->wprevpro_download_avatar_tolocal($pictocopy,$statobj);
						}
					}
					
					//send $reviews array to function to send email if turned on.
					$sitetypelower = strtolower($sitetype);
					$this->sendnotificationemail($reviews,$sitetypelower);
				}
				$resultarray['numinserted']=$totalreviewsinserted;
				unset($reviews);
				
				//update total and avg for badges.
				if(trim($pageid)!=''){
					$temptype = strtolower($sitetype);
					$this->updatetotalavgreviews($temptype, trim($pageid), $scraperesultreviewsarray['average_rating'], $scraperesultreviewsarray['review_count']);
				}
			}
					
		}
		
		return $resultarray;

	}
	
	private function my_print_db_error(){
		global $wpdb;
		if($wpdb->last_error !== '') :
			$str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
			$query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
			print "<div id='error'>
			<p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
			<code>$query</code></p>
			</div>";
		endif;
	}
	

	/**
	 * used to run language detector from Yandex api, ran on Settings/Notification page via ajax
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	//called from settings js page 
	public function wprevpro_run_language_detect_ajax(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$apikey = sanitize_text_field($_POST['apikey']);
		$page = sanitize_text_field($_POST['dbpage']);
		$page = intval($page);

		$resultarray['key'] = $apikey;

		$resultarray = $this->wprevpro_run_language_detect_ajax_go($apikey, $page);
		
		//use resultarray to communicate back to javascript
		echo json_encode($resultarray);
		die();
	}
	public function wprevpro_run_language_detect_ajax_go($apikey, $page = '0', $limit=10){
		
		//search db and find total reviews that do not have the language set.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		$totalunsetreviews = $wpdb->get_results( "SELECT * FROM $table_name WHERE language_code = '' AND (review_text != '' OR review_title != '')" ,ARRAY_A);

		$query = "SELECT * FROM $table_name WHERE language_code = '' AND (review_text != '' OR review_title != '') LIMIT $limit";
		$reviews = $wpdb->get_results($query,ARRAY_A );
		//$wpdb->last_query();
		//$wpdb->show_errors();
		//$wpdb->print_error();
		//die();

		$resultarray['totalcount']=count($totalunsetreviews);
		$resultarray['apikey']=$apikey;
		$resultarray['reviews']=$reviews;
		//$resultarray['query']=$query;

		//loop through first 20 or less
		$loopnum=$limit;
		if($resultarray['totalcount']<=$loopnum){
			$loopnum = $resultarray['totalcount'];
		}
		
		for ($x = 0; $x < $loopnum; $x++) {
			$stringtodetect = '';
			//first try to grab part of review_text
			if(strlen($reviews[$x]['review_text']) > 40){
				$stringtodetect = substr($reviews[$x]['review_text'],0,40);
			} else {
				//description not long enough, grab title if set
				if($reviews[$x]['review_title']!=''){
					$stringtodetect =$reviews[$x]['review_title'];
				} else {
					//use short description as last resort
					$stringtodetect =$reviews[$x]['review_text'];
				}
			}
			$resultarray['detect'][$x]['strdetect']=$stringtodetect;
			
			if($stringtodetect!=""){
				//now call yandex api
				//https://translate.yandex.net/api/v1.5/tr.json/detect?key=<API key>&text=<text>&[hint=<list of probable text languages>]
				$url = "https://translate.yandex.net/api/v1.5/tr.json/detect?key=".$apikey."&text=".urlencode($stringtodetect)."";  
				$data = wp_remote_get( $url );
				if ( is_wp_error( $data ) ) 
				{
					$resultarray['detect'][$x]['error_message'] 	= $data->get_error_message();
					$resultarray['detect'][$x]['status'] 		= $data->get_error_code();
				}
				$resultarray['detect'][$x]['decoderresult']	= json_decode( $data['body'], true );
				
				//update the db with the language if we have success code, if not then make a note of it and display to user
				$templang='';
				$rid='';
				if($resultarray['detect'][$x]['decoderresult']['code']==200){
					$templang=$resultarray['detect'][$x]['decoderresult']['lang'];
					$rid = $reviews[$x]['id'];
					$data = array( 
						'language_code' => "$templang"
						);
					$format = array( 
							'%s'
						); 
					$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $rid ), $format, array( '%d' ));
					if($updatetempquery>0){
						//success
						$resultarray['detect'][$x]['decoderresult']['dbupdated']='yes';
					} else {
						$resultarray['detect'][$x]['decoderresult']['dbupdated']='error';
					}
				}
				
				// wait for .5 seconds
				//usleep(500000);
			}
		}
				
		return $resultarray;

	}
	
	/**
	 * used to get overall chart data via ajax on analytics page 
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	//called from settings js page 
	public function wppro_get_overall_chart_data(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$startdate = sanitize_text_field($_POST['startd']);
		$enddate = sanitize_text_field($_POST['endd']);
		
		$rtypearray = sanitize_text_field(stripslashes($_POST['stypes']));
		$rtypearray = json_decode($rtypearray,true);

		$rpagearray = sanitize_text_field(stripslashes($_POST['slocations']));
		$rpagearray = json_decode($rpagearray,true);
		
		$utstartdate=strtotime($startdate);
		$utenddate=strtotime($enddate);
		
		$filtertext = sanitize_text_field($_POST['filtertext']);
		
		
		//add location pageid search if set
		if(is_array($rpagearray)){
			$rpagearray = array_filter($rpagearray);
			$rpagearray = array_values($rpagearray);
			if(count($rpagearray)>0){
				for ($x = 0; $x < count($rpagearray); $x++) {
					if($x==0){
						$rpagefilter = "AND (pageid = '".$rpagearray[$x]."'";
					} else {
						$rpagefilter = $rpagefilter." OR pageid = '".$rpagearray[$x]."'";
					}
				}
				//add shortcode pageid
				for ($k = 0; $k < count($shortcodepageidarray); $k++) {
					if($shortcodepageidarray[$k]!=''){
						$rpagefilter = $rpagefilter." OR pageid = '".trim($shortcodepageidarray[$k])."'";
					}
				}
				$rpagefilter = $rpagefilter.")";
			}
		}
		
		//add type search if set
		if(is_array($rtypearray)){
			$rtypearray = array_filter($rtypearray);
			$rtypearray = array_values($rtypearray);
			if(count($rtypearray)>0){
				for ($x = 0; $x < count($rtypearray); $x++) {
					if($x==0){
						$rtypefilter = "AND (type = '".$rtypearray[$x]."'";
					} else {
						$rtypefilter = $rtypefilter." OR type = '".$rtypearray[$x]."'";
					}
				}
				$rtypefilter = $rtypefilter.")";
			}
		}
			
		
		//query db and return reviews
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		
		//if filtertext set then use different query
		if($filtertext!=""){
			$querystring = "SELECT * FROM ".$table_name." WHERE (reviewer_name LIKE '%".$filtertext."%' or review_text LIKE '%".$filtertext."%') AND (rating > 0 OR recommendation_type != '') AND (created_time_stamp >= $utstartdate AND created_time_stamp <= $utenddate) ".$rtypefilter." ".$rpagefilter." ORDER BY created_time_stamp ASC";
		} else {
			$querystring = "SELECT * FROM $table_name WHERE (rating > 0 OR recommendation_type != '') AND (created_time_stamp >= $utstartdate AND created_time_stamp <= $utenddate) ".$rtypefilter." ".$rpagefilter." ORDER BY created_time_stamp ASC";
		}

		$totalreviews = $wpdb->get_results($querystring ,ARRAY_A);
		
		$resultarray['querystring']=$querystring;
		
		//$totalreviews = $wpdb->get_results( "SELECT * FROM $table_name WHERE rating > 0 OR recommendation_type != '' ORDER BY created_time_stamp ASC" ,ARRAY_A);
		
		//print_r($totalreviews);
		
		//loop array of all reviews and build results arrays
		$resultarray['ratingvals']=Array();
		$positivewords = '';
		$negativewords = '';
		for ($x = 0; $x < count($totalreviews); $x++) {
			$temptext=$totalreviews[$x]['review_text'];
			if (extension_loaded('mbstring')) {
				$review_length_char = mb_strlen($temptext);
			} else {
				$review_length_char = strlen($temptext);
			}
			if($review_length_char>70){
				if (extension_loaded('mbstring')) {
					$temptext=mb_substr($temptext,0,70).'...';
				} else {
					$temptext=substr($temptext,0,70).'...';
				}
			} else {
				if (extension_loaded('mbstring')) {
					$temptext=mb_substr($temptext,0,70);
				} else {
					$temptext=substr($temptext,0,70);
				}
			}
			$temptext=strip_tags($temptext);
			$temptime = $totalreviews[$x]['created_time_stamp'];
			$tempname = $totalreviews[$x]['reviewer_name'].' - '.date('M j, Y',$temptime);
			if($totalreviews[$x]['pagename']!=''){
				$temppagename =  $totalreviews[$x]['type']." - ".$totalreviews[$x]['pagename'];
			} else {
				$temppagename =  $totalreviews[$x]['type']." - ".$totalreviews[$x]['pageid'];
			}
			
			if($totalreviews[$x]['review_title']!=''){
				$temptitle = $totalreviews[$x]['review_title'];
				if($temptext!=''){
					$temptextarray=[$temppagename,$tempname,$temptitle,$temptext];
				} else {
					$temptextarray=[$temppagename,$tempname,$temptitle];
				}
			} else {
				if($temptext!=''){
					$temptextarray=[$temppagename,$tempname,$temptext];
				} else {
					$temptextarray=[$temppagename,$tempname];
				}
			}
//print_r($temptext);			
			$resultarray['labelvals'][]=$temptextarray;
			//fix for FB
			if($totalreviews[$x]['recommendation_type']=='positive'){
				$totalreviews[$x]['rating']=5;
			} else if($totalreviews[$x]['recommendation_type']=='negative') {
				$totalreviews[$x]['rating']=2;
			}

			//$resultarray['ratingvals'][]= array("x"=>$x, "y"=>intval($totalreviews[$x]['rating']));
			$resultarray['ratingvals'][]=(int)$totalreviews[$x]['rating'];
			//pass review id so we can pull info from db
			
			$resultarray['reviewdata'][]=$totalreviews[$x];
			
			if($totalreviews[$x]['rating']>0){
				$tempnum=(int)$totalreviews[$x]['rating'];
			} else if($totalreviews[$x]['recommendation_type']=='positive'){
				$tempnum=5;
			} else if($totalreviews[$x]['recommendation_type']=='negative'){
				$tempnum=2;
			}
			$temptype = $totalreviews[$x]['type'];
			$typeratingsarray[$temptype][]=$tempnum;
			$ratingsarray[]=$tempnum;
			
			//create positive and negative word string so we can find most common
			if($tempnum>3){
				$positivewords = $positivewords." ".$totalreviews[$x]['review_text'];
			} else if($tempnum<=3){
				$negativewords = $negativewords." ".$totalreviews[$x]['review_text'];
			}
			
		}

		$typeratingsarray = array_filter($typeratingsarray);
		$resultarray['ratingtypenumvals'] = $typeratingsarray;
		
		//now we need to find number of each rating
		$temprating = $this->wprp_get_temprating($ratingsarray);
		if(isset($temprating)){
			$tempratingarray['numr1'] = array_sum($temprating[1]);
			$tempratingarray['numr2'] = array_sum($temprating[2]);
			$tempratingarray['numr3'] = array_sum($temprating[3]);
			$tempratingarray['numr4'] = array_sum($temprating[4]);
			$tempratingarray['numr5'] = array_sum($temprating[5]);
		} else {
			$tempratingarray['numr1'] = 0;
			$tempratingarray['numr2'] = 0;
			$tempratingarray['numr3'] = 0;
			$tempratingarray['numr4'] = 0;
			$tempratingarray['numr5'] = 0;
		}
		$resultarray['ratingnumvals'] = $tempratingarray;
		
		//return avg rating
		$resultarray['avgrating'] = '';
		if(count($ratingsarray)) {
			$ratingsarray = array_filter($ratingsarray);
			$resultarray['avgrating'] = round(array_sum($ratingsarray)/count($ratingsarray),1);
		}
		
		//find arrays of positive and negative words
		$stopwordsarray=["here","very","great","good","their","there","would","which","what","were","when","that", "with", "have", "this", "will", "your", "from", "they", "know", "want", "been", "because", "once"];
		$resultarray['poswordarray'] = $this->mostFrequentWords($positivewords,$stopwordsarray);
		$resultarray['negwordarray'] = $this->mostFrequentWords($negativewords,$stopwordsarray);
		
		//$resultarray['ratingvals'] = [12, 19, 3, 5, 2, 3];
		//$resultarray['labelvals'] = ['January', 'February', 'March', 'April', 'May', 'June'];
		echo json_encode($resultarray);
		die();
	
	}
	
	//function to find most frequent words in a string
	public function mostFrequentWords($string, $stopWords = [], $limit = 15) {
		$string = preg_replace('/\b[A-Za-z0-9]{1,3}\b\s?/i', '', $string);	//remove short words
		$words = array_count_values(array_diff(str_word_count(strtolower($string), 1), $stopWords));
		arsort($words); // Sort based on frequency
		return array_slice($words, 0, $limit);
	}
	
	
	
	
		
	/**
	 * used to download reviews from the wp_pro-get_apps pages.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */	
	public function wprp_getapps_getrevs_ajax(){
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$fid = sanitize_text_field($_POST['fid']);
		$pagenum = sanitize_text_field($_POST['pnum']);
		$perpage = sanitize_text_field($_POST['perp']);
		$revsinsertedsofar = sanitize_text_field($_POST['totalrevsin']);
		$resultarray = $this->wprp_getapps_getrevs_ajax_go($fid,$pagenum,$perpage,$revsinsertedsofar);
		//use resultarray to communicate back to javascript
		echo json_encode($resultarray);
		die();
	}
	public function wprp_getapps_getrevs_ajax_go($fid,$pagenum=1,$perpage=100,$revsinsertedsofar=0){
		$frlicenseid = get_option( 'wprev_fr_siteid' );
		$fid =intval($fid );
		$pagenum =intval($pagenum );
		$perpage =intval($perpage );	//currently not being used
		$revsinsertedsofar =intval($revsinsertedsofar );
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_getapps_forms';
		$reviewformdetails = $wpdb->get_row( "SELECT * FROM $table_name WHERE id = $fid" );
		$pagename = $reviewformdetails->title;
		$pageid = str_replace(" ","",$pagename)."_".$reviewformdetails->id;
		$pageid = str_replace("'","",$pageid);
		$pageid = str_replace('"',"",$pageid);
		$sitetype = $reviewformdetails->site_type;
		$listedurl= $reviewformdetails->url;
		$tempcats='';
		if(isset($reviewformdetails->categories)){
		$tempcats=$reviewformdetails->categories;
		}
		$tempposts='';
		if(isset($reviewformdetails->posts)){
		$tempposts=$reviewformdetails->posts;
		}
		$blockstoinsert = intval($reviewformdetails->blocks);

		//print_r($reviewformdetails);
		
		$nextdoorpageid = $reviewformdetails->page_id;
			
			
		//make a call to server, only if we are not out of calls and this site has passed check.
		//$options['ack'], $options['totalreviewbank'], $options['totalreviewcreditsused']
		$resultarray['ack']='';
		$resultarray['msg']='';
		$resultarray['numinserted']='';
		$resultarray['numreturned']='';
		$resultarray['scraperesult']='';
		$resultarray['pagenum']=$pagenum;

		$getreviewsarray= $this->wprp_getapps_getrevs_page($sitetype,$listedurl,$pagenum,$perpage,$nextdoorpageid);
		$resultarray['callurl']=$getreviewsarray['callurl'];
		
		if($getreviewsarray['ack']!='success'){
			$resultarray['ack']=$getreviewsarray['ack'];
		}
		
		$reviewsarray = $getreviewsarray['reviews'];
			
		if(count($reviewsarray)>0){
			
			//slice the array if it is bigger than the blocks, number to download
			if(count($reviewsarray)>$blockstoinsert){
				$reviewsarray= array_slice($reviewsarray,0,$blockstoinsert);
			}

			//echo count($reviewsarray);
			//print_r($reviewsarray);
			//die();
			//insert this in to the review database, return success message and count or error
			//add check to see if already in db, skip if it is and end loop
			$table_name = $wpdb->prefix . 'wpfb_reviews';
			$reviews= array();
			if(is_array($reviewsarray) && count($reviewsarray)>0){
				
				foreach($reviewsarray as $item) {
					$reviewindb = 'no';
					
					$reviewer_name = trim($item['reviewer_name']);
					$reviewer_name =$this->changelastname($reviewer_name, $reviewformdetails->last_name);
					$review_text = trim($item['review_text']);
					$review_length = str_word_count($review_text);
					if (extension_loaded('mbstring')) {
						$review_length_char = mb_strlen($review_text);
					} else {
						$review_length_char = strlen($review_text);
					}
					$searchname = addslashes($reviewer_name);
					$timestamp = $this->myStrtotime($item['updated']);
					$unixtimestamp = $timestamp;
					$timestamp = date("Y-m-d H:i:s", $timestamp);
						
					//$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$searchname."' AND type = '".$sitetype."' AND created_time_stamp = '".$unixtimestamp."' " );
					
					$checkrow = $wpdb->get_var( "SELECT id FROM ".$table_name." WHERE reviewer_name = '".$searchname."' AND type = '".$sitetype."' AND (review_length_char = '".$review_length_char."' OR review_length = '".$review_length."' OR created_time_stamp = '".$unixtimestamp."')" );
					
					
					//owner response data
					$owner_response = '';
					//if(isset($item['response']) && is_array($item['response'])){
					//	$owner_response = json_encode($item['response']);
					//}
					
					if( empty( $checkrow ) ){
						
							$reviews[] = [
								'reviewer_name' => $reviewer_name,
								'reviewer_id' => trim($item['review_id']),
								'pagename' => trim($pagename),
								'pageid' => trim($pageid),
								'userpic' => $item['userpic'],
								'rating' => $item['rating'],
								'recommendation_type' => $item['recommendation_type'],
								'created_time' => $timestamp,
								'created_time_stamp' => $unixtimestamp,
								'review_text' => $review_text,
								'hide' => '',
								'review_length' => $review_length,
								'review_length_char' => $review_length_char,
								'type' => $sitetype,
								'review_title' => trim($item['review_title']),
								'from_url' => trim($listedurl),
								'from_url_review' => trim($item['from_url_review']),
								'reviewer_email' => trim($item['reviewer_email']),
								'company_title' => $item['company_title'],
								'company_url' => $item['company_url'],
								'company_name' => $item['company_name'],
								'location' => $item['location'],
								'verified_order' => '',
								'language_code' => $item['language_code'],
								'unique_id' => '',
								'meta_data' => '',
								'categories' => trim($tempcats),
								'posts' => trim($tempposts),
								'owner_response' => trim($owner_response),
							];
					} else {
						//end loop since we already have these
						break;
					}
				}
				$resultarray['numreturned'] = count($reviews);
				
				//insert or update array in to reviews table.
				$totalreviewsinserted=0;
				//echo "here";
				//print_r($reviews);
				$resultarray['revsinsertedsofar']=$revsinsertedsofar;
				if(isset($reviews) && count($reviews)>0){
					foreach ( $reviews as $stat ){
						//make sure we don't go over number to insert
						if($blockstoinsert > $revsinsertedsofar){
							$statobj ='';
							$pictocopy='';
							//print_r($stat);
							$insertnum = $wpdb->insert( $table_name, $stat );
							$stat['id']=$wpdb->insert_id;
							$this->my_print_db_error();
							$totalreviewsinserted = $totalreviewsinserted + $insertnum;
							$revsinsertedsofar = $revsinsertedsofar + $insertnum;
							//if inserted and save avatar local turned on, then try to copy here
							if($stat['id']>0 && $reviewformdetails->profile_img=="yes" && $stat['userpic']!=''){
								$pictocopy=$stat['userpic'];
								$statobj = (object) $stat;
								$this->wprevpro_download_avatar_tolocal($pictocopy,$statobj);
							}
							
						}
					}
				}
				$resultarray['numinserted']=$totalreviewsinserted;
				unset($reviews);
				
				//update total and avg for badges.
				if(trim($pageid)!=''){
					$temptype = strtolower($sitetype);
					$this->updatetotalavgreviews($temptype, trim($pageid), '', '');
				}
				//send $reviews array to function to send email if turned on.
				if(count($reviews)>0){
					$sitetypelower = strtolower($sitetype);
					$this->sendnotificationemail($reviews, $sitetypelower);
				}
				
				//update last ran on
				$table_name_form = $wpdb->prefix . 'wpfb_getapps_forms';
				$clr = time();
				$cfid = $reviewformdetails->id;
				$data = array('last_ran' => "{$clr}");
				$format = array( '%s' );
				$updatetempquery = $wpdb->update($table_name_form,$data,array('id' => $cfid),$format,array( '%d' ));
			
			}
		
		sleep(1);
		}	
		
		
		return $resultarray;

	}
	
	//for calling remote get and returning array of reviews to insert, used for itunes, Zillow, Nextdoor, Get Your Guide, Freemius, etc...
	public function wprp_getapps_getrevs_page($type,$listedurl,$pagenum,$perpage,$nextdoorpageid){
		$result['ack']='success';
		
		if($type=='iTunes'){
			//call itunes page here. This can be moved to another function if we add another source.
			//grab the id out of the Url $listedurl, https://podcasts.apple.com/us/podcast/id1462192400
			$storeitemid = '';
			if (($pos = strpos($listedurl, "/id")) !== FALSE) { 
				$idonword = substr($listedurl, $pos+3);
				$storeitemid = (int) filter_var($idonword, FILTER_SANITIZE_NUMBER_INT);	//filtering out everything but number
			} else {
				//can't find the ID
				$result['ack']=esc_html__('Error: Can not find the ID in the URL you entered.', 'wp-review-slider-pro');
			}
			$urlarray = parse_url($listedurl);
			$pathstr = $urlarray['path'];
			$patharray = (explode("/",$pathstr));
			$patharray = array_filter($patharray);
			$patharray = array_values($patharray);
			$countrycode = $patharray[0];
			if(!isset($countrycode) || $countrycode==''){
				$countrycode = 'us';
			}

			//https://itunes.apple.com/us/rss/customerreviews/page=1/id=1462192400/sortby=mostrecent/xml
			//json https://itunes.apple.com/us/rss/customerreviews/page=1/id=1462192400/sortby=mostrecent/json
			$callurl = "https://itunes.apple.com/".$countrycode."/rss/customerreviews/page=".$pagenum."/id=".$storeitemid."/sortby=mostrecent/xml";

			//echo $callurl;
			$response = wp_remote_get( $callurl );
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
			}
			//$getrevsarray = json_decode($body, TRUE);
			// do a string replace on im:rating so we can parse it correctly
			$body = str_replace("im:rating","rating_value",$body);
			$xml=simplexml_load_string($body);
			$getrevsarray = json_decode(json_encode((array)$xml), TRUE);

			$reviewsarraytemp = $getrevsarray['entry'];
			
			//loop reviews and build new array of just what we name
			foreach ($reviewsarraytemp as $item) {
				 $reviewsarray[] = [
				 'reviewer_name' => $item['author']['name'],
				 'reviewer_id' => $item['id'],
				 'reviewer_email' => '',
				 'userpic' => '',
				 'rating' => $item['rating_value'],
				 'updated' => $item['updated'],
				 'review_text' => $item['content'][0],
				 'review_title' => $item['title'],
				 'from_url_review' => $item['author']['uri'],
				 'language_code' => '',
				 'location' => '',
				 'recommendation_type' => '',
				 'company_title' =>  '',
				 'company_url' => '',
				 'company_name' => '',
				 ];
			}

			$result['reviews'] = $reviewsarray;

		} else if($type=='HousecallPro'){
			//$listedurl = https://client.housecallpro.com/reviews/Wellmann-Plumbing/de5f6b5d-23a0-4467-89fe-f793c431470d/
			//start from page 0

			//https://api.housecallpro.com/alpha/organizations/de5f6b5d-23a0-4467-89fe-f793c431470d/reviews?page=1&count=10
			$tempuniquecode = '';
			$temppieces = array_filter(explode("/", $listedurl));
			$tempuniquecode = end($temppieces);

			$callurl = "https://api.housecallpro.com/alpha/organizations/".$tempuniquecode."/reviews?&page=".$pagenum."&count=20";

			//echo $callurl;
			$result['callurl'] =$callurl;
			$response = wp_remote_get( $callurl );
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
			} else {
				$result['ack']=esc_html__('Error: Can not use remote get on this url:', 'wp-review-slider-pro').' '.$callurl;
			}
			
			$getrevsarray = json_decode($body, TRUE);	//convert to array
			//print_r($getrevsarray);
			
			$reviewsarraytemp = $getrevsarray['reviews_list']['data'];
			
			//loop reviews and build new array of just what we name
			foreach ($reviewsarraytemp as $item) {
				$tempuserpic = '';
				 $reviewsarray[] = [
				 'reviewer_name' => trim($item['customer_name']),
				 'reviewer_id' => '',
				 'reviewer_email' => '',
				 'userpic' => $tempuserpic,
				 'rating' => $item['rating'],
				 'updated' => $item['created_at'],
				 'review_text' => $item['comments'],
				 'review_title' => '',
				 'from_url_review' => '',
				 'language_code' => '',
				 'location' => '',
				 'recommendation_type' => '',
				 'company_title' =>  '',
				 'company_url' => '',
				 'company_name' => '',
				 ];
			}
			
			
			$result['reviews'] = $reviewsarray;
			
			
		} else if($type=='GetYourGuide'){
			//https://www.getyourguide.com/london-l57/magical-london-harry-potter-guided-walking-tour-t174648/
			//start from page 0
			if($pagenum > 0){
				$pagenum= $pagenum - 1;
			}
			
			//https://travelers-api.getyourguide.com/activities/174648/reviews?limit=3&offset=2
			$tempuniquecode = '';
			$temppieces = array_filter(explode("/", $listedurl));
			$tempuniquecode = end($temppieces);
			$explodedagain = array_filter(explode("-", $tempuniquecode));
			$tempuniquecode = end($explodedagain);
			$finalid = str_replace("t","",$tempuniquecode);
			
			$offset = $pagenum * 50;
			$callurl = "https://travelers-api.getyourguide.com/activities/".$finalid."/reviews?limit=50&offset=".$offset;

			//echo $callurl;
			$result['callurl'] =$callurl;
			$response = wp_remote_get( $callurl );
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$body    = $response['body']; // use the content
			} else {
				$result['ack']=esc_html__('Error: Can not use remote get on this url:', 'wp-review-slider-pro').' '.$callurl;
			}
			
			$getrevsarray = json_decode($body, TRUE);	//convert to array
			$reviewsarraytemp = $getrevsarray['reviews'];
			
			//print_r($reviewsarraytemp);
			
			//loop reviews and build new array of just what we name
			foreach ($reviewsarraytemp as $item) {
				$tempuserpic = '';
				if($item['author']['photo']){
					$tempuserpic = $item['author']['photo'];
				}
				$templocation='';
				if($item['author']['country']){
					$templocation=$item['author']['country'];
				}
				 $reviewsarray[] = [
				 'reviewer_name' => trim($item['author']['fullName']),
				 'reviewer_id' => $item['id'],
				 'reviewer_email' => '',
				 'userpic' => $tempuserpic,
				 'rating' => $item['rating'],
				 'updated' => $item['created'],
				 'review_text' => $item['message'],
				 'review_title' => $item['title'],
				 'from_url_review' => '',
				 'language_code' => $item['language'],
				 'location' => $templocation,
				 'recommendation_type' => '',
				 'company_title' =>  '',
				 'company_url' => '',
				 'company_name' => '',
				 ];
			}
			
			
			$result['reviews'] = $reviewsarray;
			
			
			//print_r($reviewsarray);
			//die();
			
			
		} else if($type=='Nextdoor'){
			//first we need to find the pageid by loading the html and searching in javascript code
			/*
			$response = wp_remote_get( $listedurl );
 			if ( is_array( $response ) && ! is_wp_error( $response ) ) {
				$headers = $response['headers']; // array of http header lines
				$fileurlcontents = $response['body']; // use the content
			} else {
				$result['ack']=esc_html__('Error: Can not use remote get on this url:', 'wp-review-slider-pro').' '.$callurl;
			}
			//search $fileurlcontents for the page id.
			//first shorten the stringtotime
			$findme = 'pageId:';
			$pos = strpos($fileurlcontents, $findme);
			$shortstring = substr($fileurlcontents,$pos,50);
			//echo $shortstring;
			$end = strpos($shortstring, ',');
			//echo "end-".$end;
			$pageId = substr($shortstring,8,$end-8);
*/

			//now we create new URL for the feed
			$callurl = "https://nextdoor.com/web/feeds/recommendations/?page_id=".$nextdoorpageid;
			$result['callurl'] =$callurl;
			
			//echo $callurl;
			
			//we have to emulate browser, most important is the ndbr_at, it may change.
			$cookieval = get_option('wprevpro_cookieval');
			$tempbody=$this->file_get_contents_curl_browser($callurl,$cookieval);
			$getrevsarray = json_decode($tempbody, TRUE);	//convert to array

			$reviewsarraytemp = $getrevsarray['content_list'];
			//print_r($reviewsarraytemp);
			
			//loop reviews and build new array of just what we need
			foreach ($reviewsarraytemp as $item) {
				$tempuserpic = '';
				if($item['story_new']['author']['avatar_url']){
					$tempuserpic = $item['story_new']['author']['avatar_url'];
				}
				$updatedtimestring = substr($item['story_new']['creation_time_ms'],0,-3);
				
				$updatedtimestring = date("Y-m-d H:i:s", $updatedtimestring);
				
				 $reviewsarray[] = [
				 'reviewer_name' => trim($item['story_new']['author']['name']),
				 'reviewer_id' => $item['story_new']['author']['id'],
				 'reviewer_email' => '',
				 'userpic' => $tempuserpic,
				 'rating' => '',
				 'updated' => $updatedtimestring,
				 'review_text' => $item['story_new']['body'],
				 'review_title' => '',
				 'from_url_review' => '',
				 'language_code' => '',
				 'location' => $item['story_new']['author']['from']['name'],
				 'recommendation_type' => 'positive',
				 'company_title' =>  '',
				 'company_url' => '',
				 'company_name' => '',
				 ];
			}

			$result['reviews'] = $reviewsarray;
			
		}  else if($type=='Freemius'){
			//first include the SDK so we can use to grab the reviews
			//echo WPREV_PLUGIN_DIR;
			//explode the $listedurl to find id and tokens
			$tokenarray = explode(",",$listedurl);
			$plugin_id = trim($tokenarray[0]);
			$pkey = trim($tokenarray[1]);
			$skey = trim($tokenarray[2]);
			
			//print_r($tokenarray);
			
			require_once WPREV_PLUGIN_DIR.'admin/freemius/FreemiusBase.php';
			require_once WPREV_PLUGIN_DIR.'admin/freemius/Freemius.php';
			
			// Init SDK.
			$api = new Freemius_Api('plugin', $plugin_id, $pkey, $skey);
			
			// Get all products.
			$reviewsarraytemp = $api->Api('reviews.json?enriched=true&count=50');
			//loop reviews and build new array of just what we need
			//print_r($reviewsarraytemp->reviews);
			
			foreach ($reviewsarraytemp->reviews as $item) {
				$tempuserpic = get_avatar_url($item->email);
				//echo $item->name;
				//try to pull from gravatar
				//rating based on 100 percent, 60 equals 3 stars
				$rating =  (substr($item->rate,0,-1))/2;
				$company_title = '';
				if($item->job_title){
					$company_title = $item->job_title;
				}
				$company_url = '';
				if($item->company_url){
					$company_url = $item->company_url;
				}
				$company_name = '';
				if($item->company){
					$company_name = $item->company;
				}
				
				$reviewsarray[] = [
				 'reviewer_name' => $item->name,
				 'reviewer_id' => $item->user_id,
				 'reviewer_email' => $item->email,
				 'userpic' => $tempuserpic,
				 'rating' => $rating,
				 'updated' => $item->created,
				 'review_text' => $item->text,
				 'review_title' => $item->title,
				 'from_url_review' => $item->profile_url,
				 'language_code' => '',
				 'location' => '',
				 'recommendation_type' => '',
				 'company_title' =>  $company_title,
				 'company_url' => $company_url,
				 'company_name' => $company_name,
				 ];
			}
			
			//print_r($reviewsarray);

			$result['reviews'] = $reviewsarray;
			
		} else if($type=='Zillow'){

			$getreviews = false;
			$errormsg='';
			$callurl = $listedurl;
			if (filter_var($callurl, FILTER_VALIDATE_URL)) {
				$stripvariableurl = strtok($callurl, '?');
				$stripvariableurl = stripslashes($stripvariableurl);
				//check url to find out what kind of review page this is
				if (strpos($stripvariableurl, '/profile/') !== false) {
					$urldetails = $this->getreviewurlfrommain($stripvariableurl, $limit=50, $pagenum);
					$urlvalue = $urldetails['url'];
					//if this is a realtor page
					if(isset($urlvalue) && $urlvalue!=''){
						$getreviews = true;
					} else {
						$errormsg = $errormsg . __(' Unable to find the Zillow reviews URL. Contact support.','wp-review-slider-pro');
						$this->errormsg = $errormsg;
					}
				} else if(strpos($stripvariableurl, '/lender-profile/') !== false){
					//for lender profile
					$errormsg = $errormsg . __(' Sorry, this does not currently work for a lender profile url. Please try the Review Funnel feature.','wp-review-slider-pro');
					$this->errormsg = $errormsg;
				}
				
				if($getreviews){
					
					//now actually get the reviews
					$data = wp_remote_get( $urlvalue );
					if ( is_wp_error( $data ) ) 
					{
						$response['error_message'] 	= $data->get_error_message();
						$reponse['status'] 		= $data->get_error_code();
						print_r($response);
						die();
					}
					if ( is_array( $data ) ) {
					  $header = $data['headers']; // array of http header lines
					  $body = $data['body']; // use the content
					}
						
					$pagedata = json_decode( $body, true );
					
					// Find 20 reviews
					$reviewsarray = $pagedata['reviews'];

					foreach ($reviewsarray as $review) {
							$user_name='';
							$userimage='';
							$rating='';
							$datesubmitted='';
							$rtext='';
							//find what is reviewed
							if($review['revieweeDisplayName']){
								$pagename = $review['revieweeDisplayName'];
							}
							
							// Find user_name
							if($review['reviewerDisplayName']){
								$user_name = $review['reviewerDisplayName'];
							}
							
							// Find userimage ui_avatar
							$userimage = '';

							// find rating
							if($review['overallRating']['amount']){
								$rating = $review['overallRating']['amount'];
								$rating = str_replace(0,"",$rating);
							}

							// find date created_at
							if($review['reviewYear']){
								//11/14/2018
								$datesubmitted = $review['reviewMonth']."/".$review['reviewDay']."/".$review['reviewYear'];
							}
							
							// find text
							if($review['reviewBodyMain']){
								$rtext = $review['reviewBodyMain'];
								//check for extra text and add ... if we find it
								if($review['reviewBodyExtra']!='' && $review['reviewBodyExtra']!='null' ){
									$rtext = $rtext.'...';
								}
							}
							
							if($rating>0){
								$reviewsarraytemp[] = [
										'reviewer_name' => trim($user_name),
										'pagename' => trim($pagename),
										'rating' => $rating,
										'date' => $datesubmitted,
										'review_text' => trim($rtext),
										'type' => 'Zillow'
								];
							}
					}
				}
				
				//loop reviews and build new array of just what we need
				foreach ($reviewsarraytemp as $item) {
					 $reviewsarrayfinal[] = [
					 'reviewer_name' => trim($item['reviewer_name']),
					 'reviewer_id' => '',
					 'reviewer_email' => '',
					 'userpic' => '',
					 'rating' => $item['rating'],
					 'updated' => $item['date'],
					 'review_text' => $item['review_text'],
					 'review_title' => '',
					 'from_url_review' => '',
					 'language_code' => '',
					 'location' => '',
					 'recommendation_type' => '',
					 'company_title' =>  '',
					 'company_url' => '',
					 'company_name' => '',
					 ];
				}
				
			
				$result['reviews'] = $reviewsarrayfinal;
			
			} else {
				$errormsg='Please enter a valid URL.';
			}
			
			$result['ack'] =$errormsg;
			
			//print_r($getrevsarray);
			//die();
			
			
		}

		return $result;
	}
	
		private function getreviewurlfrommain($urlvalue, $limit=100, $page=1){
					$response = wp_remote_get( $urlvalue );
					if ( is_array( $response ) ) {
					  $header = $response['headers']; // array of http header lines
					  $fileurlcontents = $response['body']; // use the content
					} else {
						echo "Error finding reviews. Please contact plugin support.";
						die();
					}
					$html = wppro_str_get_html($fileurlcontents);
					//find zillow business name and add to db under pagename
					$id ='';
					if($html->find('div.write-a-review', 0)){
						if($html->find('div.write-a-review', 0)->find('a',0)){
							$id = $html->find('div.write-a-review', 0)->find('a',0)->href;
							$id = substr($id, strpos($id, "s=") + 2);
						}
					}
					//use the key and the listing id to find review data					
					$rurl ="https://www.zillow.com/ajax/review/ReviewDisplayJSONGetPage.htm?id=".$id."&size=".$limit."&page=".$page."&page_type=received&moderator_actions=0&reviewee_actions=0&reviewer_actions=0&proximal_buttons=1&hasImpersonationPermission=0&service=&sort=1";
					$reviewurl['url'] = esc_url_raw($rurl);
					return $reviewurl;
	}
	
		//for using curl instead of fopen
	private function file_get_contents_curl_browser($url,$cookieval) {
		$agent= 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
							'authority: nextdoor.com',
							'pragma: no-cache',
							'cache-control: no-cache',
							'upgrade-insecure-requests: 1',
							'user-agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
							'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3', 'accept-encoding: gzip, deflate, br',
							'accept-language: en-US,en;q=0.9',
							'cookie: '.$cookieval.''
					));
		curl_setopt($ch, CURLOPT_URL,$url);
		$result=curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	//====================twitter======================
	//for checking twitter keys
	public function wprp_twitter_gettweets_ajax() {
		
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$searchquery = sanitize_text_field($_POST['query']);
		$searchendpoint = sanitize_text_field($_POST['endpoint']);
		$formid = sanitize_text_field($_POST['fid']);
		$resultarray['searchquery'] = $searchquery;
		$resultarray['searchendpoint'] = $searchendpoint;
		
		//update the searchquery for the form id, this is becuase of the input on the pop-up.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_gettwitter_forms';
		$timenow = time();
		$data = array('query' => "$searchquery",'last_ran' =>"$timenow");
		$format = array('%s','%d');
		$updatetempquery = $wpdb->update($table_name, $data, array( 'id' => $formid ), $format, array( '%d' ));
		
		$wprevpro_twitter_api_key = get_option('wprevpro_twitterapi_key');
		$wprevpro_twitter_api_key_secret = get_option('wprevpro_twitterapi_key_secret');
		$wprevpro_twitter_api_token = get_option('wprevpro_twitterapi_token');
		$wprevpro_twitter_api_token_secret = get_option('wprevpro_twitterapi_token_secret');

		
		
		//------if we are using default keys then force to the standard search, also force in javascript
		if($searchendpoint=="7" || $wprevpro_twitter_api_key=='' || $wprevpro_twitter_api_key_secret=='' || $wprevpro_twitter_api_token=='' || $wprevpro_twitter_api_token_secret==''){
			//====default twitter keys used for standard search/
			$wprevpro_twitter_api_default['key']='O30jlOfBnZdV5Eh8iWO37jsEw';
			$wprevpro_twitter_api_default['secret']='GL4LFyXwfOZTORVmkQjXrhorUzEIy7ycamYXC8icpDWrluKXi2';
			$wprevpro_twitter_api_default['token']='919980007707037697-B8oPwME9yBWt0NQc3L9pdEBvWqzFfzE';
			$wprevpro_twitter_api_default['token_secret']='Gvk3Op3oNyhzzOd1oONPp414yNO6XnFqN5AxSJnMVxkoI';
		
			//use standard search
			$connection = new Abraham\TwitterOAuth\TwitterOAuth($wprevpro_twitter_api_default['key'], $wprevpro_twitter_api_default['secret'], $wprevpro_twitter_api_default['token'], $wprevpro_twitter_api_default['token_secret']);

			
			$resultstemp = (array)$connection->get("search/tweets", ["q" => $searchquery,"count" => '100']);

			//print_r($resultstemp);
			
			$statuses['results']=$resultstemp['statuses'];
			
			//$resultsarr = json_decode($resultstemp,true);
			//print_r($resultsarr);
			//$statusesarr = $resultsarr['statuses'];
			//$statuses = json_encode($statusesarr['statuses']);
			//$statuses need to match what we get from premium search
		} else {

			$connection = new Abraham\TwitterOAuth\TwitterOAuth($wprevpro_twitter_api_key, $wprevpro_twitter_api_key_secret, $wprevpro_twitter_api_token, $wprevpro_twitter_api_token_secret);
			if($searchendpoint=='all'){
				$endhtml = 'fullarchive';
			} else {
				$endhtml = '30day';
			}
			$statuses = $connection->get("tweets/search/".$endhtml."/wprevdev", ["query" => $searchquery,"maxResults" => '100']);
			
		}
		
		//get an array of all tweets in db and pass back so we can know what we already have.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		$resultarray['savedreviews'] = $wpdb->get_col( "SELECT unique_id FROM ".$table_name." WHERE type = 'Twitter'" );
		
		
		if ($connection->getLastHttpCode() == 200) {
			$resultarray['ack'] = 'success';
			$resultarray['msg'] ='';
			$resultarray['statuses'] =$statuses;
		} else {
			// Handle error case
			$resultarray['ack'] = 'error';
			$temperrormessage = (array)$connection->getLastBody();
			$temperrormessage = json_encode($temperrormessage);
			$resultarray['msg'] = $temperrormessage;
			$resultarray['statuses'] =$statuses;
		}
		
		echo json_encode($resultarray);
		die();
	}
	//for saving or deleting tweets in db
	public function wprp_twitter_savetweet_ajax() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		
		$saveordel =  sanitize_text_field($_POST['saveordel']);
		
		$review_text = sanitize_text_field($_POST['tw_text']);
		$tw_rtc = sanitize_text_field($_POST['tw_rtc']);
		$tw_rc = sanitize_text_field($_POST['tw_rc']);
		$tw_fc = sanitize_text_field($_POST['tw_fc']);
		$tw_time = sanitize_text_field($_POST['tw_time']);
		$tw_id = sanitize_text_field($_POST['tw_id']);
		$tw_sname = sanitize_text_field($_POST['tw_sname']);
		$tw_name = sanitize_text_field($_POST['tw_name']);
		$tw_img = sanitize_text_field($_POST['tw_img']);
		$tw_lang = sanitize_text_field($_POST['tw_lang']);
		
		$fid = sanitize_text_field($_POST['fid']);
		$limage = sanitize_text_field($_POST['limage']);

		$pagename = sanitize_text_field($_POST['title']);
		$pageid = str_replace(" ","",$pagename)."_".$fid;
		$pageid = str_replace("'","",$pageid);
		$pageid = str_replace('"',"",$pageid);
		
		$timestamp = $this->myStrtotime($tw_time);
		$unixtimestamp = $timestamp;
		$timestamp = date("Y-m-d H:i:s", $timestamp);
		
		if (extension_loaded('mbstring')) {
			$review_length = mb_substr_count($review_text, ' ');
			$review_length_char = mb_strlen($review_text);
		} else {
			$review_length = substr_count($review_text, ' ');
			$review_length_char = strlen($review_text);
		}
		
		$from_url = "https://twitter.com/".$tw_sname."/status/".$tw_id;
		
		$cats = sanitize_text_field($_POST['cats']);
		$cats = str_replace("'",'"',$cats);
		$posts = sanitize_text_field($_POST['posts']);
		$posts = str_replace("'",'"',$posts);
		//save likes, retweets, and replies in meta_data
		//===============================================
		$meta_data['user_url'] = "https://twitter.com/".$tw_sname;
		$meta_data['favorite_count'] = $tw_fc;
		$meta_data['retweet_count'] = $tw_rtc;
		$meta_data['reply_count'] = $tw_rc;
		$meta_data['screenname'] = $tw_sname;
		$meta_json = json_encode($meta_data);
		//{"user_url":"https://www.tripadvisor.com/Profile/rhohensee","location":"Houston, Texas","contributions":2,"helpful_votes":3,"date_of_visit":"2019-07-31"}
		//===============================================
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';
		//if saving in db
		if($saveordel=='save'){
			
			$stat = [
						'reviewer_name' => $tw_name,
						'reviewer_id' => trim($tw_sname),
						'pagename' => trim($pagename),
						'pageid' => trim($pageid),
						'userpic' => $tw_img,
						'recommendation_type' => 'positive',
						'created_time' => $timestamp,
						'created_time_stamp' => $unixtimestamp,
						'review_text' => $review_text,
						'hide' => '',
						'review_length' => $review_length,
						'review_length_char' => $review_length_char,
						'type' => 'Twitter',
						'from_url' => trim($from_url),
						'from_url_review' => trim($from_url),
						'language_code' => $tw_lang,
						'unique_id' => $tw_id,
						'meta_data' => $meta_json,
						'categories' => trim($cats),
						'posts' => trim($posts),
					];
			
			$insertnum = $wpdb->insert( $table_name, $stat );
			$resultarray['insertnum']=$insertnum;
			
			//try to save local image if turned on
				if($insertnum>0 && $limage=="yes" && $tw_img!=''){
					$resultarray['imgdownload']='yes';
					$stat['id']=$wpdb->insert_id;
					$resultarray['id']=$stat['id'];
					$statobj = (object) $stat;
					$this->wprevpro_download_avatar_tolocal($tw_img,$statobj);
				}
			
		}
		
		echo json_encode($resultarray);
		die();
		
	}
	//to delete tweet via ajax
	public function wprp_twitter_deltweet_ajax() {
		check_ajax_referer('randomnoncestring', 'wpfb_nonce');
		$tw_id = sanitize_text_field($_POST['tw_id']);
		
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpfb_reviews';

		//remove this tweets
		$deletereview = $wpdb->delete( $table_name, array( 'unique_id' => $tw_id ), array( '%s' ) );
		$resultarray['deletenum']=$deletereview;
		
		echo json_encode($resultarray);
		die();
		
	}

}
?>