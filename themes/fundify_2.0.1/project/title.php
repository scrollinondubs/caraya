<?php
/**
 * Campaign title.
 *
 * @package Fundify
 * @since Fundify 1.5
 */

global $post;
$company_name = get_post_meta($post->ID, 'ign_company_name', true);
?>

<div class="title <?php echo (empty($company_name) ? '' : 'title-two'); ?> pattern-<?php echo rand(1,4); ?>">
	<div class="container">
		<h1><?php the_title() ;?></h1>
		<h2 class="project-tag-single">
			<?php
                  $terms = wp_get_post_terms( $post->ID, 'project_category');
                  if(!empty($terms)) {
                  $site_url = home_url();
                  $cat_name = "";
                   foreach($terms as $term){
                      if($term->count > 0){
                           $cat_name .= "<a href='".esc_url( $site_url )."/project-category/".$term->slug."'>".$term->name."</a>";
                          break;
                      }
                   }
                  if($term->count > 0){ echo $cat_name; }
                  }
               ?>
        </h2>
	</div>
</div>