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

if ( get_query_var('paged') ) {
	$paged = get_query_var('paged');
}
elseif ( get_query_var('page') ) {
	$paged = get_query_var('page');
}
else {
	$paged = 1;
}

$args['paged'] = $paged;

$query = new WP_User_Query( $args );
$total = $query->get_total();
$max_num_pages = ceil( $total/$number );
?>
<div class="rt-vc-instructor-2">
	<?php if ( ! empty( $query->results ) ): ?>
		<div class="row auto-clear">
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
				<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
					<div class="rtin-item mb30">
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
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $max_num_pages > 1 ): ?>
			<div class="mt40"><?php RDTheme_Helper::pagination( $max_num_pages );?></div>
		<?php endif; ?>
	<?php else: ?>
		<?php esc_html_e( 'No Instructors Found' , 'eikra-core' ); ?>
	<?php endif; ?>
	<?php wp_reset_query(); ?>
</div> 