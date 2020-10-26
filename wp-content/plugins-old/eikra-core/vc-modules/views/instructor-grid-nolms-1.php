<?php
/**
 * @author  RadiusTheme
 * @since   1.0
 * @version 1.2
 */

$thumb_size = 'rdtheme-size9';

$args = array(
	'post_type'      => 'lp_instructor',
	'posts_per_page' => $number,
	'orderby'        => $orderby,
);

switch ( $orderby ) {
	case 'title':
	case 'menu_order':
	$args['order'] = 'ASC';
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

$query = new WP_Query( $args );

// Pagination fix
global $wp_query;
$wp_query = NULL;
$wp_query = $query;
?>
<div class="rt-vc-instructor-1 rtin-grid">
	<?php if ( have_posts() ) :?>
		<div class="row auto-clear">
			<?php while ( have_posts() ) : the_post();?>
				<?php
				$id = get_the_id();
				$designation = get_post_meta( $id, 'ac_instructor_designation', true );
				$socials = get_post_meta( $id, 'ac_instructor_socials', true );
				$socials = array_filter( $socials );
				$socials_fields = RDTheme_Helper::instructor_socials();
				?>
				<div class="col-lg-3 col-md-3 col-sm-4 col-xs-12">
					<div class="rtin-item mb30">
						<div class="rtin-img">
							<a href="<?php the_permalink();?>"><?php the_post_thumbnail( $thumb_size );?></a>
						</div>
						<div class="rtin-content">
							<h3 class="rtin-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h3>
							<?php if ( $designation ) : ?>
								<div class="rtin-designation"><?php echo esc_html( $designation ); ?></div>
							<?php endif; ?>	
							<?php if ( !empty( $socials ) ) : ?>
								<ul class="rtin-social">
									<?php foreach ( $socials as $key => $value ): ?>
										<li><a href="<?php echo esc_url( $value ); ?>" target="_blank"><i class="fa <?php echo esc_attr( $socials_fields[$key]['icon'] ); ?>"></i></a></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endwhile;?>
		</div>
		<div class="mt40"><?php RDTheme_Helper::pagination();?></div>
	<?php else: ?>
		<?php esc_html_e( 'No Instructors Found' , 'eikra-core' ); ?>
	<?php endif; ?>
	<?php wp_reset_query();?>
</div> 