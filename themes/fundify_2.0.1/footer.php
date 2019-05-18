<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package Fundify
 * @since Fundify 1.0
 */
?>

	<footer id="footer">
		<div class="container">
			<?php
				$widgetizeme = array(
					2 => 'WP_Widget_Pages',
					3 => 'WP_Widget_Archives',
					4 => 'WP_Widget_Meta'
				);

				foreach ( $widgetizeme as $key => $widget ) :
			?>
				<div class="footer-widget">
				<?php
					if ( ! dynamic_sidebar($key) )
						the_widget( $widget, array(), array(
							'before_widget' => '<div>',
							'after_widget'  => '</div>',
							'before_title'  => '<h3 class="widget-title">',
							'after_title'   => '</h3>'
						) );
				?>
				</div>
			<?php endforeach; ?>
			
			<div class="last-widget">
				<?php if ( fundify_is_crowdfunding() ) : ?>
				<h3><?php _e( 'Get the Stats', 'fundify' ); ?></h3>
				<ul>
					<li></li>
				</ul>
				<?php endif; ?>

				<div class="copy">
					<a href="<?php echo home_url(); ?>">
						<img src="<?php echo esc_url( fundify_theme_mod( 'footer_logo_image' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="footer-logo">
					</a>
					<p><?php printf( __( '&copy; Copyright %s %s', 'fundify' ), get_bloginfo( 'name' ), date( 'Y' ) ); ?></p>
				</div>
			</div>
		</div>
		<!-- / container -->
	</footer>
	<!-- / footer -->

	<?php wp_footer(); ?>
</body>
</html>