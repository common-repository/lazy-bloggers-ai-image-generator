<?php
/**
 * Logging for this plugin.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log an error message
 *
 * @param string $message The message to be logged.
 */
function lazy_bloggers_ai_image_generator_error_log( $message ) {
	if ( get_option( 'lazy_bloggers_ai_image_generator_enable_logging' ) ) {
		$timestamp   = gmdate( '[Y-m-d H:i:s]' );
		$log_message = $timestamp . ' ' . $message;

		$logs   = get_option( 'lazy_bloggers_ai_image_generator_logs', array() );
		$logs[] = $log_message;

		$logs = array_slice( $logs, -100 );

		update_option( 'lazy_bloggers_ai_image_generator_logs', $logs );
	}
}
