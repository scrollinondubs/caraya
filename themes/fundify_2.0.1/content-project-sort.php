<div class="sort-tabs" id="filter">
				
	<h3><?php _e( 'Show', 'fundify' ); ?></h3>
	<div class="dropdown">
		<div class="current">
			<?php if ( is_tax( 'project_category' ) ) : ?>
				<?php single_term_title(); ?>
			<?php else : ?>
				<?php _e( 'All', 'fundify' ); ?>
			<?php endif; ?>
		</div>
		
		<ul class="option-set">
			<li><a href="<?php echo get_post_type_archive_link( 'ignition_product' ); ?>"><?php _e('All', 'fundify'); ?></a></li>
			<?php
				$categories = get_terms( 'project_category', array( 'hide_empty' => 1 ) );
				foreach ( $categories as $category ) :
			?>
			<li><a href="<?php echo esc_url( get_term_link( $category, 'project_category' ) ); ?>"><?php echo $category->name; ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php //if ( fundify_is_crowdfunding()  ) : ?>
	<ul class="option-set home">
		<?php if ( fundify_page_template_link( 'new-this-week.php' ) ) : ?>
		<li><a href="<?php echo fundify_page_template_link( 'new-this-week.php' ); ?>" data-filter=".new-this-week"><?php _e( 'Recently Added', 'fundify' ); ?></a></li>
		<?php endif; ?>

		<?php if ( fundify_page_template_link( 'staff-picks.php' ) ) : ?>
		<li><a href="<?php echo fundify_page_template_link( 'staff-picks.php' ); ?>" data-filter=".staff-pick"><?php _e( 'Staff Picks', 'fundify' ); ?></a></li>
		<?php endif; ?>
	</ul>
	<?php //endif; ?>
</div>