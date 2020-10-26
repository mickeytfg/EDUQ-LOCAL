<?php

/**
 * Provide a public-facing view for the badge style
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

//get the large logo url
$badgeorgin = $currentform[0]->badge_orgin;
$logourl = esc_url($template_misc_array['liconurl']);
$logourllink = esc_url($template_misc_array['liconurllink']);
$logoalt = $badgeorgin." logo".
$businessname = esc_html($currentform[0]->badge_bname);
$finaltotalhtml ='';


//get badge totals and averages
	require_once('badge_class.php');	
	$badgetools = new badgetools($a['tid']);
	$totalavgarray = $badgetools->gettotalsaverages($template_misc_array,$currentform);
	$finaltotal = $totalavgarray['finaltotal'];
	$finalavg = $totalavgarray['finalavg'];



//get the large logo html
$logohtml = '';
$show_licon = esc_html($template_misc_array['show_licon']);
$logourllinktargethtml = 'target="_blank"';
if(isset($template_misc_array['liconurllink_target'])){
	$logourllinktarget = esc_html($template_misc_array['liconurllink_target']);
	if($logourllinktarget=='same'){
		$logourllinktargethtml = 'target="_self"';
	}
}
//follow or no follow, default to nofollow
$followorno = 'rel="nofollow"';
if(isset($template_misc_array['liconurllink_attr'])){
	if(esc_html($template_misc_array['liconurllink_attr'])=='follow'){
		$followorno = '';
	}
}

if($show_licon=="yes"){
	if($logourllink!=''){
		$logohtml = '<a href="'.$logourllink.'" '.$logourllinktargethtml.' '.$followorno.'><img src='.$logourl.' alt="'.$logoalt.'" class="wppro_badge1_IMG_3 b3i"></a>';
	} else {
		$logohtml = '<img src='.$logourl.' alt="'.$logoalt.'" class="wppro_badge1_IMG_3 b3i">';
	}
}
//-------------

//div12 text-----
$ctext = '';
if(isset($template_misc_array['c_text'])){
	if($template_misc_array['c_text']!=''){
		$ctext = esc_html($template_misc_array['c_text']);
	}
}
$ctext2 = '';
if(isset($template_misc_array['c_text2'])){
	if($template_misc_array['c_text2']!=''){
		$ctext2 = esc_html($template_misc_array['c_text2']);
	}
}

$ctext_url = '';
if(isset($template_misc_array['c_text_url'])){
	if($template_misc_array['c_text_url']!=''){
		$ctext_url = esc_html($template_misc_array['c_text_url']);
	}
}

$ctext_url_title = '';
if(isset($template_misc_array['c_text_url_title'])){
	if($template_misc_array['c_text_url_title']!=''){
		$ctext_url_title = esc_html($template_misc_array['c_text_url_title']);
	}
}


$ctext_url_start='';
$ctext_url_end='';
if($ctext_url!=''){
	$ctext_url_start='<a href="'.$ctext_url.'" title="'.$ctext_url_title.'" '.$followorno.' class="wppro_badge1_A_14" '.$logourllinktargethtml.'>';
	$ctext_url_end='</a>';
}
				
if($ctext=='' && $ctext2==''){
	$div12html ='';
} else if($ctext=='' && $ctext2!=''){
	$div12html = '<div class="wppro_badge1_DIV_12 b4s12">'.$ctext_url_start.'<span class="wppro_badge1_SPAN_15">'.$finaltotal.'</span>&nbsp;'.$ctext2.$ctext_url_end.'</div>';
} else if($ctext!='' && $ctext2==''){
	$div12html = '<span class="wppro_badge1_DIV_12 b4s12"><span class="wppro_badge1_SPAN_15">'.$ctext_url_start.$finalavg.'</span>&nbsp;'.$ctext.$ctext_url_end.'</span>';
} else if($ctext!='' && $ctext2!=''){
	$div12html = '<span class="wppro_badge1_DIV_12 b4s12"><span class="wppro_badge1_SPAN_15">'.$finalavg.'</span>&nbsp;'.$ctext.'&nbsp;'.$ctext_url_start.'<span class="wppro_badge1_SPAN_15">'.$finaltotal.'</span>&nbsp;'.$ctext2.$ctext_url_end.'</span>';
}


//small icons setup-------------
$smalliconshtml='';
$tempsiconarray =[];
if(isset($template_misc_array['sicon'])){
	if(is_array($template_misc_array['sicon'])){
		$tempsiconarray = $template_misc_array['sicon'];
	}
}
//print_r($tempsiconarray);
if(count($tempsiconarray)>0){
	$smalliconshtml= $smalliconshtml . '<div class="wppro_badge1_DIV_13">';
	foreach ($tempsiconarray as $keysi => $valuesi) {
		$temptype = $valuesi;
		if (in_array($temptype, $tempsiconarray)){
			$tempsiiconurl = esc_url($template_misc_array['si_'.$temptype.'_linkurl']);
			if($temptype!='custom'){
				if($tempsiiconurl!=''){
					$smalliconshtml= $smalliconshtml . '<a href="'.$tempsiiconurl.'" target="_blank" '.$followorno.'><img src="'.$imgs_url.$temptype.'_small_icon.png" alt="$temptype logo" class="wppro_badge1_IMG_4"></a>';
				} else {
					$smalliconshtml= $smalliconshtml . '<img src="'.$imgs_url.$temptype.'_small_icon.png" alt="'.$temptype.' logo" class="wppro_badge1_IMG_4">';
				}
			} else {
			$customimgurl = esc_url($template_misc_array['si_custom_imgurl']);
				if($tempsiiconurl!=''){
					$smalliconshtml= $smalliconshtml . '<a href="'.$tempsiiconurl.'" target="_blank" '.$followorno.'><img src="'.$customimgurl.'" alt="logo" class="wppro_badge1_IMG_4"></a>';
				} else {
					$smalliconshtml= $smalliconshtml . '<img src="'.$customimgurl.'" alt="logo" class="wppro_badge1_IMG_4">';
				}
			}
		}
	}
	$smalliconshtml= $smalliconshtml . '</div>';
}

//starhtml setup-------------
$starhtml ='';
$roundtohalf ='';
if($finalavg>0){
$roundtohalf = round($finalavg * 2) / 2;
}
if($badgeorgin=='yelp' || $badgeorgin=='tripadvisor'){
		$starhtml = $starhtml.'<img src="'.$imgs_url.$badgeorgin.'_stars_'.$roundtohalf.'.png" alt="'.$finalavg.' '.$badgeorgin.' stars" class="wppro_badge_stars">';
} else {

	for ($x = 1; $x <= $roundtohalf; $x++) {
			$starhtml = $starhtml.'<span class="wprsp-star-full"></span>';
	}
	if($roundtohalf==1.5||$roundtohalf==2.5||$roundtohalf==3.5||$roundtohalf==4.5){
		//add another half
		$starhtml = $starhtml.'<span class="wprsp-star-half"></span>';
		$x++;
	}
	//if x is less than 5 need another star or half
	$starleft = 5 - $x;
	for ($x = 0; $x <= $starleft; $x++) {
		$starhtml = $starhtml.'<span class="wprsp-star-empty"></span>';
	}
}
//---------------------

?>

<div class="wprevpro_badge wppro_badge1_DIV_1 b3s1" id="wprev-badge-<?php echo $currentform[0]->id; ?>">
	<div class="wppro_badge1_DIV_2 b3s2">
	<?php echo $logohtml; ?><span class="wppro_badge1_SPAN_4 b3s4"><?php echo $businessname; ?></span>
		<div class="wppro_badge1_DIV_5 b3s5">
			<div class="wppro_badge1_DIV_stars b3s6">
			<?php echo $starhtml; ?>
			</div>
			<?php echo $div12html; ?>
			<?php echo $smalliconshtml; ?>
		</div>
	</div>
</div>

