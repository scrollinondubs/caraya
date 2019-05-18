<?php
/**
 * Template Name: Recently Added
 *
 * This should be used in conjunction with the Fundify plugin.
 *
 * @package Fundify
 * @since Fundify 1.3
 */

if (idcf_is_crowdfunding()) {
	$post_type = 'ignition_product';
}

else {
	$post_type = array('download');
}

$thing_args = array(
	'post_type' => $post_type
);

if ( is_page_template( 'page-templates/new-this-week.php' ) ) {
	$thing_args[ 'date_query' ] = array( array( 'week' => date( 'W' ), 'year' => date( 'Y' ) ) );
}
else {
	if (idcf_is_crowdfunding()) {
		$thing_args['tax_query'] = array(
			array(
				'taxonomy' => 'project_type',
				'field' => 'slug',
				'terms' => 'featured'
			)
		);
	}
	else {
		$thing_args[ 'meta_key' ]   = '_campaign_featured';
		$thing_args[ 'meta_value' ] = 1;
	}
} 

$things = new WP_Query( $thing_args );

get_header();
?>

	<div class="title pattern-<?php echo rand(1,4); ?>">
		<div class="container">
			<h1><?php the_title(); ?></h1>
		</div>
		<!-- / container -->
	</div>

	<div id="content">
		<div class="container">

			<div id="projects">
				<section>
					<?php if ( $things->have_posts() ) : ?>

						<?php while ( $things->have_posts() ) : $things->the_post(); ?>
							<?php 
							if (idcf_is_crowdfunding()) {
								get_template_part('content', 'project');
							}
							else {
								get_template_part( 'content', 'campaign' ); 
							}
							?>
						<?php endwhile; ?>

						<?php do_action( 'fundify_loop_after' ); ?>

					<?php else : ?>

						<?php get_template_part( 'no-results', 'index' ); ?>

					<?php endif; ?>
				</section>
			</div>
		</div>
		<!-- / container -->
	</div>
	<!-- / content -->

<?php get_footer(); ?>