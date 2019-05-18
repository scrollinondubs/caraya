<?php
/**
 * Template Name: Sponsor RY
 * This should be used in conjunction with the Fundify plugin.
 * Modified by Liesbeth Smit / RY Darien
 * @package Fundify
 * @since Fundify 1.0
 */

get_header(); ?>

	<div class="title pattern-<?php echo rand(1,4); ?>">
		<div class="container">
			<?php while ( have_posts() ) : the_post(); ?>
			<h1><?php the_title() ;?></h1>
			<?php endwhile; ?>
		</div>
		<!-- / container -->
	</div>
	<div id="content">
					<div class="container">
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', 'single' ); ?>


			<?php endwhile; ?>
		</div>
		
		

		


			<div id="projects">
				<section>
					<?php 
						if ( idcf_is_crowdfunding()  ) :
							$wp_query = new WP_Query( array(
								'post_type' => 'ignition_product',
								'paged' => ( get_query_var( 'page' ) ? get_query_var( 'page' ) : 1 )
							) );
						else :
							$wp_query = new WP_Query( array(
								'posts_per_page' => get_option( 'posts_per_page' ),
								'paged'          => ( get_query_var('page') ? get_query_var('page') : 1 )
							) );
						endif;

						if ( $wp_query->have_posts() ) :
					?>

						<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
							<?php get_template_part( 'content', idcf_is_crowdfunding() ? 'project' : 'post' ); ?>
						<?php endwhile; ?>

					<?php else : ?>

						<?php get_template_part( 'no-results', 'index' ); ?>

					<?php endif; ?>
					<?php wp_reset_query(); ?>
				</section>
			
			
				<?php do_action( 'fundify_loop_after' ); ?>
			</div>
		<!-- / container -->
	</div>
	<!-- / content -->

<?php get_footer(); ?>