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

 	//db function variables
	global $wpdb;
	$table_name = $wpdb->prefix . 'wpfb_post_templates';
	
 //use the template id to find template in db, echo error if we can't find it or just don't display anything
 	//Get the form--------------------------
	$currentform = $wpdb->get_results("SELECT * FROM $table_name WHERE id = ".$a['tid']);
	
	$totalreviewsnum ='';
	$reviewratingsarray = Array();
	$reviewratingsarrayavg ='';
	
	//check to make sure template found
	if(isset($currentform[0])){
		
		//get all the reviews based on template filters
		$shortcodepageid ='';
		if(isset($a['pageid'])){
			$shortcodepageid = $a['pageid'];
		}
		$shortcodelang='';
		if(isset($a['langcode'])){
			$shortcodelang = $a['langcode'];
		}
		require_once("getreviews_class.php");
		$reviewsclass = new GetReviews_Functions();
		$totalreviewsarray = $reviewsclass->wppro_queryreviews($currentform,$startoffset=0,$totaltoget=0,$notinstring='',$shortcodepageid,$shortcodelang);
		$totalreviews = $totalreviewsarray['reviews'];
		//$totalreviewsarray['totalcount']
		//$totalreviewsarray['totalavg']
	
		$reviewsperpage= $currentform[0]->display_num*$currentform[0]->display_num_rows;
		
		//template misc stuff
		$template_misc_array = json_decode($currentform[0]->template_misc, true);
			
			
	//only continue if some reviews found
	$makingslideshow=false;
	$ismakingslideshow = "no";
	if(count($totalreviews)>0){

	//------------add style
		$iswidget = '';
		$gettemplatestylecode = $reviewsclass->wppro_gettemplatestylecode($currentform,$iswidget,$template_misc_array);
		echo $gettemplatestylecode;
		//---------end add style-----------------------
		
		//add header text if set
		if(isset($template_misc_array['header_text']) && $template_misc_array['header_text']!=''){
			$arrallowedtags = array('em' => array(), 'i' => array(), 'strong' => array(), 'b' => array());
			$tempheadertext= '<div class="wprev_header_txt"><'.wp_kses($template_misc_array['header_text_tag'],$arrallowedtags).'>'.wp_kses($template_misc_array['header_text'],$arrallowedtags).'</'.wp_kses($template_misc_array['header_text_tag'],$arrallowedtags).'></div>';
			
			//grab values from badge if set and badge exists.
			$badgeavg='';
			$badgetotal='';
			if(isset($template_misc_array['header_filter_opt']) && $template_misc_array['header_filter_opt']>0){
				$badgeid = intval($template_misc_array['header_filter_opt']);
				//try to find values
				require_once('badge_class.php');	
				$badgetools = new badgetools($badgeid);
				$totalavgarray = $badgetools->gettotalsaverages();
				$badgetotal = $totalavgarray['finaltotal'];
				$badgeavg = $totalavgarray['finalavg'];
			}
			if($badgeavg!='' && $badgetotal!=''){
				$tempheadertext=str_replace("{avgrating}",$badgeavg,$tempheadertext);
				$tempheadertext=str_replace("{totalratings}",$badgetotal,$tempheadertext);
			} else {
				$tempheadertext=str_replace("{avgrating}",$totalreviewsarray['totalavg'],$tempheadertext);
				$tempheadertext=str_replace("{totalratings}",$totalreviewsarray['totalcount'],$tempheadertext);
			}
			echo $tempheadertext;
		}
		
		//add search bar if turned on and not slider
		if(!isset($template_misc_array['header_search'])){
			$template_misc_array['header_search']='';
		}
		if(!isset($template_misc_array['header_sort'])){
			$template_misc_array['header_sort']='';
		}
		//add quick search tags if turned on
		if(!isset($template_misc_array['header_tag'])){
			$template_misc_array['header_tag']='';
		}
		if(!isset($template_misc_array['header_tags'])){
			$template_misc_array['header_tags']='';
		}
		if(!isset($template_misc_array['header_rating'])){
			$template_misc_array['header_rating']='';
			$template_misc_array['header_langcodes']='';
		}
		if($currentform[0]->createslider!='yes' && $currentform[0]->load_more=='yes' && ($template_misc_array['header_search']=='yes' || $template_misc_array['header_sort']=='yes' || $template_misc_array['header_tag']=='yes' || $template_misc_array['header_rating']=='yes' || $template_misc_array['header_langcodes']=='yes')){

			$loading_img_url = esc_url( plugins_url( 'imgs/', __FILE__ ) ).'loading_ripple.gif';
			
			echo "<div class='wprev_search_sort_bar'>";
			
			if($template_misc_array['header_search']=='yes'){
				echo '<input class="wprev_searchsort wprev_search" id="wprevpro_header_search_input" type="text" name="wprevpro_header_search_input" placeholder="'.esc_html($template_misc_array['header_search_place']).'" value="">';
			}
			if($template_misc_array['header_sort']=='yes'){
				echo '<select class="wprev_searchsort wprev_sort" name="wprevpro_header_sort" id="wprevpro_header_sort">
						<option value="" disabled selected>'.esc_html($template_misc_array['header_sort_place']).'</option>
						<option value="newest">'.__('Newest', 'wp-review-slider-pro').'</option>
						<option value="oldest">'.__('Oldest', 'wp-review-slider-pro').'</option>
						<option value="highest">'.__('Highest', 'wp-review-slider-pro').'</option>
						<option value="lowest">'.__('Lowest', 'wp-review-slider-pro').'</option>
						<option value="longest">'.__('Longest', 'wp-review-slider-pro').'</option>
						<option value="shortest">'.__('Shortest', 'wp-review-slider-pro').'</option>
						<option value="random">'.__('Random', 'wp-review-slider-pro').'</option>
				</select>';
			}
			if($template_misc_array['header_rating']=='yes'){
				echo '<select class="wprev_searchsort wprev_sort" name="wprevpro_header_rating" id="wprevpro_header_rating">
						<option value="unset" selected>'.esc_html($template_misc_array['header_rating_place']).'</option>
						<option value="1">'.__('1', 'wp-review-slider-pro').'</option>
						<option value="2">'.__('2', 'wp-review-slider-pro').'</option>
						<option value="3">'.__('3', 'wp-review-slider-pro').'</option>
						<option value="4">'.__('4', 'wp-review-slider-pro').'</option>
						<option value="5">'.__('5', 'wp-review-slider-pro').'</option>
				</select>';
			}
			if($template_misc_array['header_langcodes']=='yes'){
				$langcodestring = $template_misc_array['header_langcodes_list'];
				$langcodestring = preg_replace('/\s/', '', $langcodestring);	//remove whitespaces
				$langcodearray = array_filter(explode(",",$langcodestring));
				echo '<select class="wprev_searchsort wprev_sort" name="wprevpro_header_langcodes" id="wprevpro_header_langcodes">
						<option value="unset" selected>'.esc_html($template_misc_array['header_langcodes_place']).'</option>';
				foreach ($langcodearray as $value){ 
					echo '<option value="'.$value.'">'.$value.'</option>';
				} 
				echo '</select>';
			}
			//add loading image if needed
			if($template_misc_array['header_sort']=='yes' || $template_misc_array['header_search']=='yes' || $template_misc_array['header_rating']=='yes' || $template_misc_array['header_langcodes']=='yes'){
				echo '<img src="'.$loading_img_url.'" class="wprppagination_loading_image_search" style="display:none;">';
			}
			
			if($currentform[0]->createslider!='yes' && ($template_misc_array['header_tag']=='yes' & $template_misc_array['header_tags']!='')){
				//get array of header tags
				$str = esc_html( $template_misc_array['header_tags']);
				$tagarray = explode(",",$str);
				if(is_array($tagarray) && count($tagarray)>0){
					echo '<div class="wprevpro_searchtags_div">';
					$arrlength = count($tagarray);
					for($x = 0; $x < $arrlength; $x++) {
						echo '<span class="wprevpro_stag">'.trim($tagarray[$x]).'</span>';
					}
					echo '<img src="'.$loading_img_url.'" class="wprppagination_loading_image_tag" style="display:none;">';
					echo '</div>';
				}
				//print_r($tagarray);
			}
			
			echo '</div>';
			
		}



//find out if making same height and addclass so we can ues in js
		if($currentform[0]->review_same_height=='yes' || $currentform[0]->review_same_height=='cur'){
			$notsameheight="";
		} else {
			//only using for non-slider
			if($currentform[0]->createslider != "yes"){
				$notsameheight="revnotsameheight";
			} else {
				$notsameheight='';
			}
		}
		
		//if making slide show then add it here
		if($currentform[0]->createslider == "yes"){
			//make sure we have enough to create a show here
			if($totalreviews>$reviewsperpage){
				$makingslideshow = true;
				$ismakingslideshow = "yes";
				$rtltag = '';
				if ( is_rtl() ) {
					$rtltag = 'dir="rtl"';
				}
				$mobileoneperslide = "";
				if(isset($currentform[0]->slidermobileview) && $currentform[0]->slidermobileview == "one"){
					$mobileoneperslide = 'onemobil="yes"';
				}
				echo '<div class="wprevpro wprev-slider '.$notsameheight.'" '.$mobileoneperslide.' '.$rtltag.' id="wprev-slider-'.$currentform[0]->id.'">';
			}
		} else {
			//not making slideshow
			echo '<div class="wprevpro wprev-no-slider '.$notsameheight.'" id="wprev-slider-'.$currentform[0]->id.'">';

		}

		//echo $reviewsperpage;
		
		$totalreviewsnum = count($totalreviews);
		if($makingslideshow){
					echo '<ul>';
			$totalreviewschunked = array_chunk($totalreviews, $reviewsperpage);
		} else {
			$totalreviewschunked = array_chunk($totalreviews, $totalreviewsnum);
		}
		//echo "<br>";
		//print_r($totalreviewschunked);

		//loop through each chunk
		foreach ( $totalreviewschunked as $reviewschunked ){
			//echo "loop1";
			$totalreviewstemp = $reviewschunked;
			//print_r($totalreviewstemp);
			//need to break $totalreviewstemp up based on how many rows, create an multi array containing them
			if($currentform[0]->display_num_rows>1 && count($totalreviewstemp)>$currentform[0]->display_num){
				//count of reviews total is greater than display per row then we need to break in to multiple rows
				for ($row = 0; $row < $currentform[0]->display_num_rows; $row++) {
					$n=1;
					foreach ( $totalreviewstemp as $tempreview ){
						//echo "<br>".$tempreview->reviewer_name;
						if($n>($row*$currentform[0]->display_num) && $n<=(($row+1)*$currentform[0]->display_num)){
							//echo $row."-".$n."-".$tempreview->reviewer_name."<br>";
							$rowarray[$row][$n]=$tempreview;
						}
						$n++;
					}
				}
			} else {
				//everything on one row so just put in multi array
				$rowarray[0]=$totalreviewstemp;
			}
			
			//if making slide show
			if($makingslideshow){
					echo '<li>';
			}
		 
				//include the correct tid here
			if($currentform[0]->style=="1" || $currentform[0]->style=="2" || $currentform[0]->style=="3" || $currentform[0]->style=="4" || $currentform[0]->style=="5" || $currentform[0]->style=="6" || $currentform[0]->style=="7" || $currentform[0]->style=="8" || $currentform[0]->style=="9" || $currentform[0]->style=="10" ){
				$iswidget=false;
				//display_masonry-------------
				if(	$currentform[0]->display_masonry=="yes"){
					echo '<div class="wprs_masonry">';
				}	//display_masonry-------------

				$iswidget=false;
				if($currentform[0]->style=='1'){
					include(plugin_dir_path( __FILE__ ) . 'template_style_'.$currentform[0]->style.'.php');
				} else {
					if ( wrsp_fs()->can_use_premium_code() ) {
						include(plugin_dir_path( __FILE__ ) . 'template_style_'.$currentform[0]->style.'.php');
					}
				}
				
				//display_masonry------------
				if(	$currentform[0]->display_masonry=="yes"){
					echo '</div>';
				}
				//display_masonry------------
			}
			
			//if making slide show then end loop here
			if($makingslideshow){
					echo '</li>';
			}
			
			unset($rowarray);
		
		}	//end loop chunks

		//---add load more button if turned on
		$jslastslide ='';
		if(isset($currentform[0]->load_more) && $currentform[0]->load_more=="yes"){
			//if sort is random or picking reviews then we need to add the ids to button so we can test and do a NOT IN call 
			$notinstring='';
			//if($currentform[0]->display_order=="random" && $currentform[0]->showreviewsbyid!=""){
				$alreadygrabbedreviews=Array();
				for ($x = 0; $x < count($totalreviews); $x++) {
					if(isset($totalreviews[$x]->id)){
						$alreadygrabbedreviews[] = $totalreviews[$x]->id;
					}
				}
				$notinstring= implode(",",$alreadygrabbedreviews);
			//}
			//($currentform,$iswidget,$makingslideshow, $notinstring='',$shortcodepageid='',$shortcodelang='',$cpostid='' )
			$cpostid = get_the_ID();
			$getloadmorebtnhtml = $reviewsclass->wppro_getloadmorebtnhtml($currentform,$iswidget,$makingslideshow,$notinstring,$shortcodepageid,$shortcodelang,$cpostid,$totalreviewsarray['totalcount']);
			
			//only add if we need it.
			if($totalreviewsarray['totalcount']>$reviewsperpage){
				$jslastslide = $getloadmorebtnhtml['jslastslide'];
				echo $getloadmorebtnhtml['echothis'];
			}
		}
		
		//create all js to add to slider and nonslider-------------
		//for forcing same height, on both slider and nonslider
			$forceheight ='';
			if($currentform[0]->review_same_height!=""){
				if($currentform[0]->review_same_height=='yes' || $currentform[0]->review_same_height=='cur'){
					$forceheight='var maxheights = $("#wprev-slider-'.$currentform[0]->id.'").find(".w3_wprs-col").find("p").parent().map(function (){return $(this).outerHeight();}).get();var maxHeightofslide = Math.max.apply(null, maxheights);if(maxHeightofslide>0){$("#wprev-slider-'.$currentform[0]->id.'").find(".w3_wprs-col").find("p").parent().css( "height", maxHeightofslide );}';
				}
			}
		
		
		$avatarimgexists ='';
		//javascript that checks if avatar images exists, hides if not found
		//$avatarimgexists = "$('.wprevpro_avatarimg').each(function() {var tempsrc = $(this).attr('src');var newimage = new Image();newimage.src = tempsrc;if(newimage.width==0){jQuery(this).remove();}});";
			
		//if making slide show add this stuff. 
		if($makingslideshow){
			//grab db values
			if($currentform[0]->sliderautoplay!="" && $currentform[0]->sliderautoplay=='yes'){
				$autoplay = 'true';
			} else {
				$autoplay = 'false';
			}
			if($currentform[0]->sliderdirection=='vertical' || $currentform[0]->sliderdirection=='horizontal' || $currentform[0]->sliderdirection=='fade'){
				$animation = $currentform[0]->sliderdirection;
			} else {
				$animation = 'horizontal';
			}
			if($currentform[0]->sliderarrows!="" && $currentform[0]->sliderarrows=='no'){
				$arrows = 'false';
			} else {
				$arrows = 'true';
			}
			$slidedots ='';
			if($currentform[0]->sliderdots!="" && $currentform[0]->sliderdots=='no'){
				$slidedots = '$("#wprev-slider-'.$currentform[0]->id.'").siblings(".wprs_unslider-nav").hide();';
			} else {
				$slidedots = '$("#wprev-slider-'.$currentform[0]->id.'").siblings(".wprs_unslider-nav").show();';
			}

			if($currentform[0]->sliderdelay!="" && intval($currentform[0]->sliderdelay)>0){
				$delay = intval($currentform[0]->sliderdelay)*1000;
			} else {
				$delay = "3000";
			}
			if($currentform[0]->sliderspeed!="" && intval($currentform[0]->sliderspeed)>0){
				$sliderspeed = intval($currentform[0]->sliderspeed);
			} else {
				$sliderspeed = "750";
			}
			
			$fixheight = '';
			if($currentform[0]->sliderheight!="" && $currentform[0]->sliderheight=='yes' && $forceheight==''){
				$animateHeight = 'true';
					$fixheight='slider.data("wprs_unslider").animate("last");setTimeout(function(){slider.data("wprs_unslider").animate(0);}, 100);';
			} else {
				$animateHeight = 'false';
				//add fix for fade transition
				if($animation=='fade'){
					$fixheight='var heights = $("#wprev-slider-'.$currentform[0]->id.'").find( "li" ).map(function (){return $(this).outerHeight();}).get(); var maxHeight = Math.max.apply(null, heights);$("#wprev-slider-'.$currentform[0]->id.'").height(maxHeight);';
				}
			}
		
			//for making the arrows not move
			$sliderarrowheight='';
			if($arrows == 'true' && $inslideout!="yes"){
				$sliderarrowheight="var temparrow=$('#wprev-slider-".$currentform[0]->id."').siblings('a.next,a.prev');var saoffset=temparrow.offset();if(saoffset.top>0){temparrow.offset({ top: saoffset.top});}";
			}

			$mousepause ='';
			if($autoplay=='true'){
			$mousepause = "slider.on('mouseover', function() {slider.data('wprs_unslider').stop();}).on('mouseout', function() {slider.data('wprs_unslider').start();});";
			}
			
			//need to check if this is in a float and if the float has a delay, if so then we need to stop and then start the slider delaying it
			$checkfloatdelay='';
			if(isset($insidefloat) && $animatedelay>0){
				//go back to the first slide .5 seconds before
				$slideanimatedelay = $animatedelay*1000-50;
				$checkfloatdelay='setTimeout(function(){slider.data("wprs_unslider").animate(0);}, '.$slideanimatedelay.');';
			}

				echo '</ul></div>';
				echo "<script type='text/javascript'>
						function wprs_defer(method) {
							document.getElementById('wprev-slider-".$currentform[0]->id."').style.display='none';
							if (window.jQuery) {
								if(jQuery.fn.wprs_unslider){
									method();
								} else {
									setTimeout(function() { wprs_defer(method) }, 500);
									console.log('waiting for wppro_rev_slider js...');									
								}
							} else {
								setTimeout(function() { wprs_defer(method) }, 100);
								console.log('waiting for jquery..');
							}
						}
						wprs_defer(function () {
							jQuery(document).ready(function($) {
								document.getElementById('wprev-slider-".$currentform[0]->id."').style.display='block';
								var slider = $('#wprev-slider-".$currentform[0]->id."').wprs_unslider(
									{
									autoplay:".$autoplay.",
									delay: '".$delay."',
									animation: '".$animation."',
									speed: ".$sliderspeed.",
									arrows: ".$arrows.",
									animateHeight: ".$animateHeight.",
									activeClass: 'wprs_unslider-active',
									infinite: false,
									}
								);
								slider.on('wprs_unslider.change', function(event, index, slide) {
									$('#wprev-slider-".$currentform[0]->id."').find('.wprs_rd_less:visible').trigger('click');
								});
								$('#wprev-slider-".$currentform[0]->id."').siblings('.wprs_unslider-nav').attr( 'id','wprs_nav_".$currentform[0]->id."');
								$('#wprev-slider-".$currentform[0]->id."').siblings('.wprs_unslider-arrow').addClass('wprs_nav_arrow_".$currentform[0]->id."');
								".$slidedots.$mousepause.$forceheight.$fixheight.$sliderarrowheight.$jslastslide.$avatarimgexists.$checkfloatdelay."});
						});
						</script>";
		} else {
		echo '</div>';
				echo "<script type='text/javascript'>
						function wprs_defer(method) {
							if (window.jQuery) {
								method();
							} else {
								setTimeout(function() { wprs_defer(method) }, 50);
							}
						}
						wprs_defer(function () {
							jQuery(document).ready(function($) {
								".$forceheight."".$avatarimgexists."
							});
						});
						</script>";
		}
		
		
		
		//google snippet html
		$tempsnippethtml = $reviewsclass->wppro_getgooglesnippet($currentform,$totalreviewsarray['totalcount'],$totalreviewsarray['totalavg'],$totalreviews);
		echo $tempsnippethtml;
	 
	}
}
?>

