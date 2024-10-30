<?php
/**
 * Plugin Name: Lazy Blogger's AI Image Generator
 * Description: AI Generates an image based on the text provided in the post contents, and set it as featured image automatically when the post is published
 * Version: 1.3
 * Author: tubm
 * Author URI: https://wordpress.org/support/users/tubm/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LAZY_BLOGGERS_AI_IMAGE_GENERATOR_API_URL', 'https://api.openai.com/v1/images/generations' );
define( 'LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/admin-settings.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/image-generation.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/log.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/set-featured-image.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/function-calling.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'includes/tiktoken.php';
require_once LAZY_BLOGGERS_AI_IMAGE_GENERATOR_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Function executed when post status changes
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Old post status.
 * @param WP_Post $post       Post object.
 */
function lazy_bloggers_ai_image_generator_on_transition_post_status( $new_status, $old_status, $post ) {
	$post_id = $post->ID;

	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	if ( 'publish' !== $new_status || 'publish' === $old_status ) {
		return;
	}

	wp_schedule_single_event( time() + 1, 'lazy_bloggers_ai_image_generator_delayed_image_generation', array( $post_id ) );
}
add_action( 'transition_post_status', 'lazy_bloggers_ai_image_generator_on_transition_post_status', 10, 3 );

/**
 * Handles delayed image generation
 *
 * @param int $post_id The post ID.
 */
function lazy_bloggers_ai_image_generator_delayed_image_generation( $post_id ) {
	$use_post_content = get_option( 'lazy_bloggers_ai_image_generator_use_post_content' );

	if ( $use_post_content ) {
		$post         = get_post( $post_id );
		$post_content = wp_strip_all_tags( $post->post_content );

		$initial_token_count = lazy_bloggers_ai_image_generator_count_tokens( $post_content );
		if ( $initial_token_count > 100000 ) {
			$post_content        = lazy_bloggers_ai_image_generator_limit_tokens( $post_content );
			$limited_token_count = lazy_bloggers_ai_image_generator_count_tokens( $post_content );
		}

		lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - Initial token count: ' . $initial_token_count );
		lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - Limited token count: ' . $limited_token_count );

		$image_prompt = lazy_bloggers_ai_image_generator_create_image_prompt( $post_content );

		if ( $image_prompt ) {
			$image_url = lazy_bloggers_ai_image_generator_generate_image( $post_id, true, false, false, false, $image_prompt );
		} else {
			lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - Failed to create image prompt from post content' );
			return;
		}
	} else {
		$use_title    = get_option( 'lazy_bloggers_ai_image_generator_include_title' );
		$use_category = get_option( 'lazy_bloggers_ai_image_generator_include_category' );
		$use_tag      = get_option( 'lazy_bloggers_ai_image_generator_include_tag' );

		$image_url = lazy_bloggers_ai_image_generator_generate_image( $post_id, false, $use_title, $use_category, $use_tag );
	}

	if ( $image_url ) {
		lazy_bloggers_ai_image_generator_set_featured_image_from_url( $post_id, $image_url );
	}

	if ( ! $use_post_content ) {
		$post_categories = get_the_category( $post_id );
		$category_names  = array();
		if ( $post_categories ) {
			foreach ( $post_categories as $category ) {
				if ( 'Uncategorized' !== $category->name ) {
					$category_names[] = $category->name;
				}
			}
		} else {
			lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - No categories found' );
		}

		$post_tags = get_the_terms( $post_id, 'post_tag' );
		$tag_names = array();
		if ( $post_tags ) {
			foreach ( $post_tags as $tag ) {
				$tag_names[] = $tag->name;
			}
		} else {
			lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - No tags found' );
		}
	}
}
add_action( 'lazy_bloggers_ai_image_generator_delayed_image_generation', 'lazy_bloggers_ai_image_generator_delayed_image_generation', 10, 1 );
