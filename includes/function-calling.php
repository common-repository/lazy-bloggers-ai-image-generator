<?php
/**
 * Function calling to generate image prompts from blog post content.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates an image generation prompt based on blog post content.
 *
 * @param string $post_content The content of the blog post.
 * @return string|null Generated image prompt, or null on error.
 */
function lazy_bloggers_ai_image_generator_create_image_prompt( $post_content ) {
	$api_key = get_option( 'lazy_bloggers_ai_image_generator_api_key' );
	$api_url = 'https://api.openai.com/v1/chat/completions';

	$headers = array(
		'Content-Type'  => 'application/json',
		'Authorization' => 'Bearer ' . $api_key,
	);

	$data = array(
		'model'         => 'gpt-4o-mini',
		'messages'      => array(
			array(
				'role'    => 'system',
				'content' => 'You are creative prompt engineer that summarizes blog posts and creates image generation prompts based on the content.',
			),
			array(
				'role'    => 'user',
				'content' => "Create an image generation prompt based on its content:\n\n" . $post_content,
			),
		),
		'functions'     => array(
			array(
				'name'        => 'create_image_prompt',
				'description' => 'Creates an image generation prompt based on the blog post content',
				'parameters'  => array(
					'type'       => 'object',
					'properties' => array(
						'image_prompt' => array(
							'type'        => 'string',
							'description' => 'A brief, focused prompt for DALL-E3 image generation (between 20 to 50 words) capturing the essence of the blog post content',
						),
					),
					'required'   => array( 'image_prompt' ),
				),
			),
		),
		'function_call' => array( 'name' => 'create_image_prompt' ),
	);

	$response = wp_remote_post(
		$api_url,
		array(
			'headers' => $headers,
			'body'    => wp_json_encode( $data ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		lazy_bloggers_ai_image_generator_error_log( 'WP Error in API call: ' . $response->get_error_message() );
		return null;
	}

	$body = wp_remote_retrieve_body( $response );
	lazy_bloggers_ai_image_generator_error_log( 'API Response: ' . $body );

	$result = json_decode( $body, true );

	if ( isset( $result['choices'][0]['message']['function_call']['arguments'] ) ) {
		$arguments = json_decode( $result['choices'][0]['message']['function_call']['arguments'], true );
		if ( isset( $arguments['image_prompt'] ) ) {
			return $arguments['image_prompt'];
		} else {
			lazy_bloggers_ai_image_generator_error_log( 'Image prompt not found in API response' );
			return null;
		}
	} else {
		lazy_bloggers_ai_image_generator_error_log( 'Unexpected API response structure' );
		return null;
	}
}
