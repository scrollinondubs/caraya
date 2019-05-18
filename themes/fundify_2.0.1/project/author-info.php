<?php
/**
 *
 */

global $post, $campaign;

$author = get_user_by( 'id', $post->post_author );
$profile = ide_creator_info($post->ID);
$permalink_structure = get_option('permalink_structure');
$prefix = (empty($permalink_structure) ? '&' : '?');

?>

<div class="widget widget-bio">
	<h3><?php _e( 'About the Author', 'fundify' ); ?></h3>

	<?php 
	echo '<img class="avatar avatar-40 photo" width="40" height="40" src="'.$profile['logo'].'"/>';
	//echo get_avatar( $author->user_email, 40 ); ?>

	<div class="author-bio">
		<strong><?php
				//echo esc_attr( $author->display_name );
				echo $profile['name'];
		?></a></strong><br />
		<small>
			<?php
				$args = array(
					'author' => $post->post_author,
					'post_type' => 'ignition_product',
					'status' => 'publish'
					);
				$posts = get_posts($args);
				$count = count($posts);
				?>
				<a href="<?php echo (function_exists('md_get_durl') ? md_get_durl().$prefix.'?creator_profile='.$post->post_author : ''); ?>">
				<?php printf( _nx( 'Created %1$d Project', 'Created %1$d Projects', $count, '1: Number of Projects Single 2: Number of Projects Plural', 'fundify' ), $count ); 
			?> 
				</a>
			<!--&bull; 
			<a href="<?php echo get_author_posts_url( $author->ID ); ?>"><?php _e( 'View Profile', 'fundify' ); ?></a></small>-->
	</div>

	<ul class="author-bio-links">
		<?php if ( !empty($profile['url']) ) : ?>
		<li class="contact-link"><i class="icon-link"></i> <?php echo make_clickable( $profile['url'] ); ?></li>
		<?php endif; ?>

		<?php
			foreach ( $profile as $key => $method ) :
				if ( !empty($method) && !in_array($key, array('url', 'name', 'logo', 'author'))) :
		?>
					<li class="contact-<?php echo $key; ?>"><i class="icon-<?php echo $key; ?>"></i><?php echo make_clickable( $method ); ?></li>
				<?php endif; ?>
		<?php endforeach; ?>
	</ul>

	<div class="author-bio-desc">
		<?php echo wpautop( $author->user_description ); ?>
	</div>

	<?php if ( !empty($author->user_email) ) : ?>
		<!--<div class="author-contact">
			<p><a href="mailto:<?php echo $author->user_email; ?>" class="button btn-green"><?php _e( 'Ask Question', 'fundify' ); ?></a></p>
		</div>-->
	<?php endif; ?>
</div>
