<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.0
 */

$args = array(
	'role'   => LP_TEACHER_ROLE,
	'number' => $number
);

switch ( $orderby ) {
	case 'id_asc':
	$args['orderby'] = 'ID';
	$args['order']   = 'ASC';
	break;
	case 'id_dsc':
	$args['orderby'] = 'ID';
	$args['order']   = 'DESC';
	break;
	case 'display_name':
	$args['orderby'] = 'display_name';
	$args['order']   = 'ASC';
	break;
	default:
	$args['orderby'] = 'ID';
	$args['order']   = 'DESC';
	break;
}

$query = new WP_User_Query( $args );
?>
<div class="rt-vc-instructor-2 owl-wrap rt-owl-nav-1">
	<div class="section-title clearfix">
		<h2 class="owl-custom-nav-title"><?php echo esc_html( $title );?></h2>
		<div class="owl-custom-nav">
			<div class="owl-prev"><i class="fa fa-angle-left"></i></div><div class="owl-next"><i class="fa fa-angle-right"></i></div>
		</div>
	</div>
	<div class="owl-theme owl-carousel rt-owl-carousel" data-carousel-options="<?php echo esc_attr( $owl_data );?>">
		<?php if ( ! empty( $query->results ) ): ?>
			<?php foreach ( $query->results as $instructor ): ?>
				<?php
				$id = $instructor->ID;
				$description = get_the_author_meta( 'description', $id );
				$description = wp_trim_words( $description, 15 );
				$user_meta = get_the_author_meta( 'rt_lp_instructor_info', $id );
				$socials = isset( $user_meta['socials'] ) ? $user_meta['socials'] : array();
				$socials = array_filter( $socials );
				$socials_fields = RDTheme_Helper::instructor_socials();
				?>
				<div class="rtin-item">
					<div class="rtin-img">
						<a href="<?php echo esc_url( learn_press_user_profile_link( $id ) ); ?>"><?php echo get_avatar( $id , 360 ); ?></a>
					</div>
					<div class="rtin-content">
						<div class="rtin-title-wrap">
							<h3 class="rtin-title"><a href="<?php echo esc_url( learn_press_user_profile_link( $id ) ); ?>"><?php echo esc_html( $instructor->display_name ); ?></a></h3>						
							<?php if ( !empty( $user_meta['designation'] ) ) : ?>
								<div class="rtin-designation"><?php echo esc_html( $user_meta['designation'] ); ?></div>
							<?php endif; ?>
						</div>
						<?php if ( !empty( $description ) ) : ?>
							<div class="rtin-description"><?php echo wp_kses_post( $description ); ?></div>
						<?php endif; ?>
						<?php if ( !empty( $socials ) ) : ?>						
							<ul class="rtin-social">
								<?php foreach ( $socials as $key => $value ): ?>
									<li><a href="<?php echo esc_url(  $value ); ?>" target="_blank"><i class="fa <?php echo esc_attr( $socials_fields[$key]['icon'] ); ?>"></i></a></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>					
					</div>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<?php esc_html_e( 'No Instructors Found' , 'eikra-core' ); ?>
		<?php endif; ?>
		<?php wp_reset_query(); ?>
	</div>
</div> 