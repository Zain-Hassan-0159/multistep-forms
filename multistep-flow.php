<?php 
/**
 * Plugin Name: Multistep Flow
 * Text Domain: multistep-flow
 * Description: This plugin is used to create a questions bank with multistep form.
 * Version: 1.1
 * Author: Zain Hassan
 * Author URI: https://hassanzain.com/
 * License: GPL2
 */

 if ( ! defined( 'ABSPATH' ) ) {
	die( 'Try try again' );
}
require_once('inc/MSFloader.php');
define( 'MSF_path', plugin_dir_url( __FILE__ ) );
 
function assign_custom_template($template) {
	global $wp_query;
	$curr_slug = $wp_query->query_vars['name'];
	$curr_slug = strtolower($curr_slug);
	$slugs = getAllSlugFromCustomPost();

	foreach ($slugs as $slug) {
		if ($curr_slug == strtolower($slug)) {
			$template = plugin_dir_path(__FILE__) . 'template-custom.php';
			break;
		}
	}
	return $template;
}
add_filter('template_include', 'assign_custom_template');

function getAllSlugFromCustomPost(){
	$args = array(
		'post_type' => 'msf_lead_froms',
		'posts_per_page' => -1, // Set to -1 to retrieve all posts
	);
	$form_slugs = [];
	
	$query = new WP_Query( $args );
	
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$form_id = get_the_ID();

			$msf_ls_slug = get_post_meta($form_id, 'msf_ls_slug', true);
			$form_slugs[] = $msf_ls_slug;

			
		}
		wp_reset_postdata();
	}

	return $form_slugs;
	
}

