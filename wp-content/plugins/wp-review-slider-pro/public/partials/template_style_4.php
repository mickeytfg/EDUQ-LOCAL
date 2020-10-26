<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    WP_Review_Pro
 * @subpackage WP_Review_Pro/public/partials
 */
 //html code for the template style
$plugin_dir = WP_PLUGIN_DIR;
$imgs_url = esc_url( plugins_url( 'imgs/', __FILE__ ) );

require_once("template_class.php");
$templateclass = new Template_Functions();

//loop if more than one row
for ($x = 0; $x < count($rowarray); $x++) {
	if(	$currentform[0]->display_masonry!="yes"){
	if(	$currentform[0]->template_type=="widget"){
			$iswidget=true;
		?>
		<div class="wprevpro_t4_outer_div_widget w3_wprs-row-padding-small wprevprodiv">
		<?php
		} else {
			$iswidget=false;
		?>
		<div class="wprevpro_t4_outer_div w3_wprs-row wprevprodiv">
		<?php
	}
	}
	//loop 
	foreach ( $rowarray[$x] as $review ) 
	{
		//add to reviews number array
		$reviewratingsarray[] = $review->rating;
		
		//get userpic, functions in the class-wp-review-slider-pro-public.php file
		//$imagecachedir = plugin_dir_path( __FILE__ ).'cache/';
		$userpic = $templateclass->wprevpro_get_user_pic($review,'142','142',$currentform[0]);

		//star number --------------------------------------
		$starfile_burl_logo_array = $templateclass->wprevpro_get_star_logo_burl($review,$imgs_url,$currentform[0],"t4");
		$starfile = $starfile_burl_logo_array['starfile'];
		$logo =  "<div>".$starfile_burl_logo_array['logo']."</div>";
		$burl = $starfile_burl_logo_array['burl'];		

		
		$reviewtext = "";
		//review text --------------------------
		if(!isset($template_misc_array['length_type'])){
			$template_misc_array['length_type']='words';
		}
		$reviewtext = $templateclass->wprevpro_get_reviewtext($review,$currentform[0],$template_misc_array['length_type']);
		
		//date format MM/DD/YYYY, DD/MM/YY, DD/MM/YYYY, YYYY-MM-DD
		$datestring = $templateclass->wprevpro_get_datestring($review,$template_misc_array);
		
		//========company format   Owner, Dental Practice Website
		$companyhtml = $templateclass->wprevpro_get_companyhtml($review,$template_misc_array,"t4");
		
		//last name display options
		$tempreviewername = $templateclass->wprevpro_get_reviewername($review,$template_misc_array);
		
		//link to author url if turned on in template, use reviewer_id from db and create url for different types
		$profilelink = $templateclass->wprevpro_get_profilelink($review,$currentform,$userpic,$tempreviewername,$template_misc_array,$burl);
		
		//userpic html, this could change to nothing if userpic turned off.
		//$userpichtml = $profilelink['userpichtml'];
		//userpic html
		if(!isset($template_misc_array['default_avatar'])){
			$template_misc_array['default_avatar']='none';
		}
		//if the review does not have a image we hide it or show mystery depending on settin
		if($userpic==""){
			//use setting to determine default mystery man if set
			if($template_misc_array['default_avatar']=='fb'){
				$userpic = $imgs_url.'fb_mystery_man_big.png';
			} else if($template_misc_array['default_avatar']=='trip'){
				$userpic = $imgs_url.'trip_myster_man_big.png';
			} else if($template_misc_array['default_avatar']=='google'){
				$userpic = $imgs_url.'google_mystery_man_big.png';
			} else if($template_misc_array['default_avatar']=='yelp'){
				$userpic = $imgs_url.'yelp_mystery_man_big.png';
			} else if($template_misc_array['default_avatar']=='airbnb'){
				$userpic = $imgs_url.'airbnb_mystery_man_small.png';
			}
		}
		if($userpic==""){
			$userpichtml = '<div class="wpproslider_t4_avatar_div"></div>';
		} else {
			$userpichtml = '<div class="wpproslider_t4_avatar_div">'.$profilelink['start'].'<img src="'.$userpic.'" alt="'.$tempreviewername.' Avatar" class="wpproslider_t4_IMG_2 wprevpro_avatarimg" />'.$profilelink['end'].'</div>';
			if(isset($template_misc_array['avataropt'])){
				if($template_misc_array['avataropt']=='hide'){
					$userpichtml = '';
				} else if($template_misc_array['avataropt']=='mystery'){
					if($review->type=="Yelp"){
						$tempuserpic = $imgs_url.'yelp_mystery_man_big.png';
					} else if ($review->type=="TripAdvisor"){
						$tempuserpic = $imgs_url.'trip_myster_man_big.png';
					} else if ($review->type=="Google"){
						$tempuserpic = $imgs_url.'google_mystery_man_big.png';
					} else if ($review->type=="Airbnb"){
						$tempuserpic = $imgs_url.'airbnb_mystery_man_small.png';
					} else {
						$tempuserpic = $imgs_url.'fb_mystery_man_big.png';
						//use setting to determine default mystery man if set
						if($template_misc_array['default_avatar']=='trip'){
							$tempuserpic = $imgs_url.'trip_myster_man_big.png';
						} else if($template_misc_array['default_avatar']=='google'){
							$tempuserpic = $imgs_url.'google_mystery_man_big.png';
						} else if($template_misc_array['default_avatar']=='yelp'){
							$tempuserpic = $imgs_url.'yelp_mystery_man_big.png';
						} else if($template_misc_array['default_avatar']=='airbnb'){
							$tempuserpic = $imgs_url.'airbnb_mystery_man_small.png';
						}
					}
					$userpichtml = '<div class="wpproslider_t4_avatar_div">'.$profilelink['start'].'<img src="'.$tempuserpic.'" alt="'.$tempreviewername.' Avatar" class="wpproslider_t4_IMG_2 wprevpro_avatarimg"/>'.$profilelink['end'].'</div>';
				}
			}
		}
		
		//title option for tripadvisor, manual, and submitted
		$title = '';
		if(isset($template_misc_array['showtitle'])){
			if(isset($review->review_title)){
				if($template_misc_array['showtitle']=="hide"){
					$title = '';
				} else if ($review->review_title!='') {
					$title = '<span class="wprevpro_t4_DIV_title">'.stripslashes(strip_tags($review->review_title)).' </span>';
				}
			}
		}
		
		//masonry style
		//per a row
		if($currentform[0]->display_num>0){
			$perrow = 12/$currentform[0]->display_num;
		} else {
			$perrow = 4;
		}
		$tempmasonrydiv ='wpproslider_t4_DIV_1 w3_wprs-col l'.$perrow;
		if(	$currentform[0]->display_masonry=="yes"){
			$tempmasonrydiv ='wprs_masonry_item wprevpro_t4_DIV_1';
		}
		
		//starhtlm
		$starhtmlarray = $templateclass->wprevpro_get_starhtml($review,$template_misc_array,$currentform,$starfile);
		$starhtml = $starhtmlarray[0];
		$starhtml2 = $starhtmlarray[1];
		
		//miscpichtml, for woocommerce
		$miscpichtml = $templateclass->wprevpro_get_miscpichtml($review,$currentform[0]);
		
		//load template from child theme if found
		$custometheme= get_stylesheet_directory()."/wprevpro/template".$currentform[0]->style.".php";
		if (file_exists($custometheme)) {
			include($custometheme);
		} else {

	?>
	<div class="<?php echo $tempmasonrydiv; ?>">
		<div class="indrevdiv wpproslider_t4_DIV_1a<?php if($iswidget){echo "_widget";} ?>  wprev_preview_bg1_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?> wprev_preview_bradius_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>">
			<?php echo $userpichtml; ?>
			<div class="wpproslider_t4_H3_3 wprev_preview_tcolor1_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>">
				<?php echo $tempreviewername; ?><?php echo $companyhtml;?>
			</div>
			<span class="wpproslider_t4_SPAN_4">
			<span class="wprevpro_star_imgs_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>"><?php echo $starhtml; ?></span>
			</span>
			<p class="wpproslider_t4_P_5 wprev_preview_tcolor2_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>">
				<?php echo $title; ?><?php echo stripslashes($reviewtext); ?>
			</p>
			<span class="wpproslider_t4_date wprev_preview_tcolor3_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>"> <span class="wprev_showdate_T<?php echo $currentform[0]->style; ?><?php if($iswidget){echo "_widget";} ?>"><?php echo $datestring; ?></span></span>
			<?php echo $logo; ?>
			<?php echo $miscpichtml; ?>
		</div>
	</div>
	<?php
		}
	}
	//end loop
	if(	$currentform[0]->display_masonry!="yes"){
	?>
	</div>
<?php
	}
}
?>
