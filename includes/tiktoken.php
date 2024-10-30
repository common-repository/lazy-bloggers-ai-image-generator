<?php
/**
 * Tiktoken to count tokens and limit tokens.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use TikToken\Encoder;

/**
 * Count tokens in the post content
 *
 * @param string $post_content The post content.
 * @return int Number of tokens.
 */
function lazy_bloggers_ai_image_generator_count_tokens( $post_content ) {
	$encoder = new Encoder();
	$tokens  = $encoder->encode( $post_content );
	return count( $tokens );
}

/**
 * Limit the number of tokens in the post content
 *
 * @param string $post_content The post content.
 * @param int    $max_tokens   Maximum number of tokens allowed (fixed to 100000).
 * @return string Limited post content.
 */
function lazy_bloggers_ai_image_generator_limit_tokens( $post_content, $max_tokens = 100000 ) {
	$encoder = new Encoder();
	$tokens  = $encoder->encode( $post_content );

	if ( count( $tokens ) <= $max_tokens ) {
		return $post_content;
	}

	$limited_tokens = array_slice( $tokens, 0, $max_tokens );

	$limited_post_content = $encoder->decode( $limited_tokens );

	return $limited_post_content;
}
