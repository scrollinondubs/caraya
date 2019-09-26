<?php

Class IDE_WC {

	var $platform;

	function __construct() {
		if (!function_exists('idf_platform')) {
			return;
		}
		if (!is_id_pro()) {
			return;
		}
		$this->$platform = idf_platform();
		if ($this->$platform == 'wc') {
			$this->init();
		}
	}

	function init() {
		$this->set_filters();
	}

	function set_filters() {
		add_action('ide_fes_create', array($this, 'wc_fes_associations'), 5, 6);
		add_action('ide_fes_update', array($this, 'wc_fes_associations'), 5, 6);
	}

	private function wc_fes_associations($user_id, $project_id, $post_id, $proj_args, $levels, $auth) {
		if (empty($levels)) {
			return;
		}
		foreach ($levels as $level) {
			$project = new ID_Project($project_id);
			$the_project = $project->the_project

			$post = get_post_by('id', $post_id);
			if (empty($the_project) || empty($post)) {
				return;
			}

			$post_meta = $project->get_project_meta();
			$product = new WC_Product();
			$product->set_name($post->post_title . ': ' . $level['title']); 
			$product->set_status($post->post_status);
			
			$product->set_catalog_visibility('hidden');
			$product->set_description($post_meta['ign_project_long_description']);
			$product->set_short_description($project->short_description());
			//$product->set_sku('U-123');
			$product->set_price(5.00);
			$product->set_regular_price(5.00); 
			$product->set_date_on_sale_from(); 

			$product->set_category_ids($term_ids); //Set the product categories.                   | array $term_ids List of terms IDs.
			$product->set_tag_ids($term_ids); //Set the product tags.                              | array $term_ids List of terms IDs.
			$product->set_image_id(); //Set main image ID.                                         | int|string $image_id Product image id.
			$product->set_gallery_image_ids(); //Set gallery attachment ids.                       | array $image_ids List of image ids.
			$new_product_id = $product->save(); //Saving the data to create new product, it will return product ID.
		}
	}

}
$ide_wc = new IDE_WC();
?>