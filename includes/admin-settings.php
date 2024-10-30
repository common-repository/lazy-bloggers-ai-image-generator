<?php
/**
 * Admin settings for this plugin.
 *
 * @package Lazy_Bloggers_AI_Image_Generator
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the main menu for TUBM plugins
 */
function lazy_bloggers_ai_image_generator_add_main_menu() {
	global $menu;
	$menu_slug = 'tubm-plugins';

	$menu_exists = false;
	foreach ( $menu as $item ) {
		if ( $item[2] === $menu_slug ) {
			$menu_exists = true;
			break;
		}
	}

	if ( ! $menu_exists ) {
		add_menu_page(
			'TUBM Plugins',
			'TUBM Plugins',
			'manage_options',
			$menu_slug,
			'',
			'dashicons-admin-plugins',
			30
		);
	}
}
add_action( 'admin_menu', 'lazy_bloggers_ai_image_generator_add_main_menu', 9 );

/**
 * Add the submenu for this plugin
 */
function lazy_bloggers_ai_image_generator_add_submenu() {
	add_submenu_page(
		'tubm-plugins',
		'Lazy Blogger\'s AI Image Generator Settings',
		'Lazy Blogger\'s AI Image Generator',
		'manage_options',
		'lazy-bloggers-ai-image-generator',
		'lazy_bloggers_ai_image_generator_settings_page'
	);
}
add_action( 'admin_menu', 'lazy_bloggers_ai_image_generator_add_submenu' );

/**
 * Remove the default submenu of TUBM plugins.
 */
function lazy_bloggers_ai_image_generator_remove_default_submenu() {
	remove_submenu_page( 'tubm-plugins', 'tubm-plugins' );
}
add_action( 'admin_menu', 'lazy_bloggers_ai_image_generator_remove_default_submenu', 999 );

/**
 * Render the settings page for the plugin
 */
function lazy_bloggers_ai_image_generator_settings_page() {
	if ( isset( $_POST['clear_logs'] ) && check_admin_referer( 'lazy_bloggers_ai_image_generator_clear_logs_action', 'lazy_bloggers_ai_image_generator_clear_logs_nonce' ) ) {
		update_option( 'lazy_bloggers_ai_image_generator_logs', array() );
		echo '<div class="updated"><p>' . esc_html__( 'Logs have been cleared.', 'lazy-bloggers-ai-image-generator' ) . '</p></div>';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Lazy Blogger\'s AI Image Generator Settings Page', 'lazy-bloggers-ai-image-generator' ); ?></h1>
		<div class="plugin-description-small">
			<p style="font-size: 12px;"><?php esc_html_e( 'This plugin works in conjunction with the DALL-E3 API to generate large-sized image files in .png format.', 'lazy-bloggers-ai-image-generator' ); ?><br>
			<?php esc_html_e( 'Therefore, we recommend using', 'lazy-bloggers-ai-image-generator' ); ?> <a href="https://shortpixel.com/otp/af/QALRSBX1137437" target="_blank"><?php esc_html_e( 'ShortPixel Image Optimizer', 'lazy-bloggers-ai-image-generator' ); ?></a> <?php esc_html_e( '(we may receive a commission if you sign up and purchase credit).', 'lazy-bloggers-ai-image-generator' ); ?><br>
			<?php esc_html_e( 'It can automatically convert images to .jpg, .webp, .avif and so on... compress them, reducing file size by up to 90%.', 'lazy-bloggers-ai-image-generator' ); ?><br>
			<?php esc_html_e( 'You get 100 free credits every month!', 'lazy-bloggers-ai-image-generator' ); ?></p>
			<p style="font-size: 12px; font-weight: bold;"><?php esc_html_e( 'Note: This plugin requires an', 'lazy-bloggers-ai-image-generator' ); ?> <a href="https://platform.openai.com/account/api-keys" target="_blank"><?php esc_html_e( 'OpenAI API key', 'lazy-bloggers-ai-image-generator' ); ?></a> <?php esc_html_e( 'to function!', 'lazy-bloggers-ai-image-generator' ); ?></p>
		</div>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'lazy_bloggers_ai_image_generator_settings' );
			do_settings_sections( 'lazy_bloggers_ai_image_generator_settings' );
			?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'API Key', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="text" name="lazy_bloggers_ai_image_generator_api_key" value="<?php echo esc_attr( get_option( 'lazy_bloggers_ai_image_generator_api_key' ) ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Size', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td>
						<select name="lazy_bloggers_ai_image_generator_size">
							<option value="1024x1024" <?php selected( get_option( 'lazy_bloggers_ai_image_generator_size' ), '1024x1024' ); ?>><?php esc_html_e( '1024x1024', 'lazy-bloggers-ai-image-generator' ); ?></option>
							<option value="1024x1792" <?php selected( get_option( 'lazy_bloggers_ai_image_generator_size' ), '1024x1792' ); ?>><?php esc_html_e( '1024x1792', 'lazy-bloggers-ai-image-generator' ); ?></option>
							<option value="1792x1024" <?php selected( get_option( 'lazy_bloggers_ai_image_generator_size' ), '1792x1024' ); ?>><?php esc_html_e( '1792x1024', 'lazy-bloggers-ai-image-generator' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top" class="diable-when-function-calling">
					<th scope="row"><?php esc_html_e( 'Include Title', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="checkbox" name="lazy_bloggers_ai_image_generator_include_title" value="1" <?php checked( 1, get_option( 'lazy_bloggers_ai_image_generator_include_title' ), true ); ?> /></td>
				</tr>
				<tr valign="top" class="diable-when-function-calling">
					<th scope="row"><?php esc_html_e( 'Include Category', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="checkbox" name="lazy_bloggers_ai_image_generator_include_category" value="1" <?php checked( 1, get_option( 'lazy_bloggers_ai_image_generator_include_category' ), true ); ?> /></td>
				</tr>
				<tr valign="top" class="diable-when-function-calling">
					<th scope="row"><?php esc_html_e( 'Include Tags', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="checkbox" name="lazy_bloggers_ai_image_generator_include_tag" value="1" <?php checked( 1, get_option( 'lazy_bloggers_ai_image_generator_include_tag' ), true ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Create prompt from the post content', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="checkbox" id="lazy_bloggers_ai_image_generator_use_post_content" name="lazy_bloggers_ai_image_generator_use_post_content" value="1" <?php checked( 1, get_option( 'lazy_bloggers_ai_image_generator_use_post_content' ), true ); ?> /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Additional (Style) Prompt', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><textarea name="lazy_bloggers_ai_image_generator_style_prompt" maxlength="1000" rows="5" cols="50"><?php echo esc_textarea( get_option( 'lazy_bloggers_ai_image_generator_style_prompt' ) ); ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Enable Logging', 'lazy-bloggers-ai-image-generator' ); ?></th>
					<td><input type="checkbox" name="lazy_bloggers_ai_image_generator_enable_logging" value="1" <?php checked( 1, get_option( 'lazy_bloggers_ai_image_generator_enable_logging' ), true ); ?> /></td>
				</tr>  
			</table>
			<?php submit_button(); ?>
		</form>

		<!-- show logs -->
		<h2><?php esc_html_e( 'Logs', 'lazy-bloggers-ai-image-generator' ); ?></h2>
		<div id="lazy-bloggers-ai-image-generator-logs">
			<?php lazy_bloggers_ai_image_generator_display_logs(); ?>
		</div>

		<!-- Log Clear Form -->
		<form method="post">
			<?php wp_nonce_field( 'lazy_bloggers_ai_image_generator_clear_logs_action', 'lazy_bloggers_ai_image_generator_clear_logs_nonce' ); ?>
			<input type="hidden" name="clear_logs" value="1">
			<?php submit_button( esc_html__( 'Clear Logs', 'lazy-bloggers-ai-image-generator' ) ); ?>
		</form>

	</div>
	<?php
}

/**
 * Register plugin settings
 */
function lazy_bloggers_ai_image_generator_settings() {
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_api_key', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_size', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_style_prompt', 'sanitize_textarea_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_enable_logging', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_include_title', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_include_category', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_include_tag', 'sanitize_text_field' );
	register_setting( 'lazy_bloggers_ai_image_generator_settings', 'lazy_bloggers_ai_image_generator_use_post_content', 'sanitize_text_field' );
}
add_action( 'admin_init', 'lazy_bloggers_ai_image_generator_settings' );

/**
 * Enqueue JavaScript for the admin page.
 */
function lazy_bloggers_ai_image_generator_enqueue_admin_scripts() {

	wp_enqueue_script(
		'text2image-generator-admin',
		plugins_url( './js/admin-settings.js', __DIR__ ),
		array( 'jquery' ),
		'1.0.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'lazy_bloggers_ai_image_generator_enqueue_admin_scripts' );

/**
 * Function to display logs
 */
function lazy_bloggers_ai_image_generator_display_logs() {
	$logs = get_option( 'lazy_bloggers_ai_image_generator_logs', array() );

	if ( empty( $logs ) ) {
		echo '<p>' . esc_html__( 'No logs available.', 'lazy-bloggers-ai-image-generator' ) . '</p>';
	} else {
		echo '<ul>';
		foreach ( $logs as $log ) {
			echo '<li>' . esc_html( $log ) . '</li>';
		}
		echo '</ul>';
	}
}