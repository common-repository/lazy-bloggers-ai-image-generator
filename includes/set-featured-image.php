<?php
/**
 * Set featured image for the published post.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set featured image for the post from a URL
 *
 * @param int   $post_id    Post ID.
 * @param array $image_data Image data (URL at [0], title at [1]).
 */
function lazy_bloggers_ai_image_generator_set_featured_image_from_url( $post_id, $image_data ) {
	$image_url  = $image_data[0];
	$post_title = $image_data[1];

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$media = media_sideload_image( $image_url, $post_id, $post_title, 'src' );
	if ( ! is_wp_error( $media ) ) {
		$args        = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'post_parent'    => $post_id,
		);
		$attachments = get_posts( $args );
		if ( $attachments ) {
			$attachment = $attachments[0];

			$new_file_name = sanitize_file_name( $post_title . '_' . time() . '.png' );
			$file_path     = get_attached_file( $attachment->ID );
			$new_file_path = dirname( $file_path ) . '/' . $new_file_name;

			global $wp_filesystem;
			if ( empty( $wp_filesystem ) ) {
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}

			if ( $wp_filesystem->move( $file_path, $new_file_path ) ) {
				update_attached_file( $attachment->ID, $new_file_path );
			}

			update_post_meta( $attachment->ID, '_wp_attachment_image_alt', $post_title );

			if ( set_post_thumbnail( $post_id, $attachment->ID ) ) {
				lazy_bloggers_ai_image_generator_error_log( 'Featured Image Set - Post ID: ' . $post_id );

			} else {
				lazy_bloggers_ai_image_generator_error_log( 'Mission Failed - Post ID: ' . $post_id . ' - Failed to set featured image' );
			}
		} else {
			lazy_bloggers_ai_image_generator_error_log( 'Mission Failed - Post ID: ' . $post_id . ' - No attachment found' );
		}
	} else {
		lazy_bloggers_ai_image_generator_error_log( 'Mission Failed - Post ID: ' . $post_id . ' - Error in media_sideload_image' );
	}
}
