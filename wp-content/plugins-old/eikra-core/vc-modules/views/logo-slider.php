<?php
$logos = vc_param_group_parse_atts( $logos );
?>
<div class="rt-vc-logo-slider">  
	<div class="owl-theme owl-carousel rt-owl-carousel" data-carousel-options="<?php echo esc_attr( $owl_data );?>">
		<?php foreach ( $logos as $logo ): ?>
			<?php if ( empty( $logo['image'] ) ) continue; ?>
			<div class="rtin-item">
				<?php if ( !empty( $logo['url'] ) ): ?>
					<a href="<?php echo esc_url( $logo['url'] );?>" target="_blank"><?php echo wp_get_attachment_image( $logo['image'], 'full' )?></a>
				<?php else: ?>
					<?php echo wp_get_attachment_image( $logo['image'], array('270', '130') )?>
				<?php endif; ?>
			</div>			
		<?php endforeach; ?>                      
	</div>  
</div>  