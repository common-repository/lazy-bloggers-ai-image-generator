<?php
/**
 * Image generation for this plugin.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generate an image from a post.
 *
 * @param int         $post_id The ID of the post to generate an image from.
 * @param bool        $use_post_content Whether to use the post content as part of the prompt.
 * @param bool        $use_title Whether to use the post title as part of the prompt.
 * @param bool        $use_category Whether to use the post categories as part of the prompt.
 * @param bool        $use_tag Whether to use the post tags as part of the prompt.
 * @param string|null $custom_prompt A custom prompt to use if not using the post content, title, categories, or tags.
 * @return array|null An array containing the generated image URL and post title, or null if the image generation failed.
 */
function lazy_bloggers_ai_image_generator_generate_image( $post_id, $use_post_content, $use_title, $use_category, $use_tag, $custom_prompt = null ) {
	$api_key      = get_option( 'lazy_bloggers_ai_image_generator_api_key' );
	$size         = get_option( 'lazy_bloggers_ai_image_generator_size' );
	$style_prompt = get_option( 'lazy_bloggers_ai_image_generator_style_prompt' );
	$final_prompt = '';

	$post       = get_post( $post_id );
	$post_title = wp_strip_all_tags( $post->post_title );

	$prompt_parts = array();

	if ( $use_post_content && $custom_prompt ) {
		$prompt_parts[] = $custom_prompt;
	} else {
		if ( $use_title ) {
			$prompt_parts[] = $post_title;
		}
		if ( $use_category ) {
			$post_categories = get_the_terms( $post_id, 'category' );
			$category_names  = array();
			if ( $post_categories && ! is_wp_error( $post_categories ) ) {
				foreach ( $post_categories as $category ) {
					if ( 'Uncategorized' !== $category->name ) {
						$category_names[] = wp_strip_all_tags( $category->name );
					}
				}
			}
			if ( ! empty( $category_names ) ) {
				$prompt_parts[] = implode( ', ', $category_names );
			}
		}
		if ( $use_tag ) {
			$post_tags = get_the_terms( $post_id, 'post_tag' );
			$tag_names = array();
			if ( $post_tags && ! is_wp_error( $post_tags ) ) {
				foreach ( $post_tags as $tag ) {
					$tag_names[] = wp_strip_all_tags( $tag->name );
				}
			}
			if ( ! empty( $tag_names ) ) {
				$prompt_parts[] = implode( ', ', $tag_names );
			}
		}
	}

	$final_prompt = implode( ', ', $prompt_parts );
	$style_prompt = get_option( 'lazy_bloggers_ai_image_generator_style_prompt' );

	if ( ! empty( $style_prompt ) ) {
		$final_prompt .= '. The image must be in the style of ' . $style_prompt;
	}

	lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - Final Prompt: ' . $final_prompt );

	$headers = array(
		'Content-Type'  => 'application/json',
		'Authorization' => 'Bearer ' . $api_key,
	);

	$request_args = array(
		'headers' => $headers,
		'body'    => wp_json_encode(
			array(
				'prompt'          => $final_prompt,
				'n'               => 1,
				'size'            => $size,
				'response_format' => 'url',
			)
		),
		'timeout' => 30,
		'method'  => 'POST',
	);

	$response = wp_remote_post( LAZY_BLOGGERS_AI_IMAGE_GENERATOR_API_URL, $request_args );
	$json     = json_decode( wp_remote_retrieve_body( $response ), true );

	lazy_bloggers_ai_image_generator_error_log( 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - API Response: ' . wp_remote_retrieve_body( $response ) );

	$log_message = 'Lazy Bloggers AI Image Generator - Post ID: ' . $post_id . ' - ';
	if ( $json && isset( $json['data'][0]['url'] ) ) {
		$log_message .= 'Image generation succeeded';
	} else {
		$log_message .= 'Image generation failed';
	}
	lazy_bloggers_ai_image_generator_error_log( $log_message );

	if ( $json && isset( $json['data'][0]['url'] ) ) {
		return array( $json['data'][0]['url'], $post_title );
	} else {
		return null;
	}
}
