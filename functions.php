<?php
/**
 * Naturaly functions and definitions.
 *
 * @package Naturaly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'naturaly_setup' ) ) :
	/**
	 * Theme setup: translations and editor styles.
	 */
	function naturaly_setup() {
		load_theme_textdomain( 'naturaly', get_template_directory() . '/languages' );
		add_editor_style( 'assets/css/theme.css' );
	}
endif;
add_action( 'after_setup_theme', 'naturaly_setup' );

/**
 * Enqueue front-end styles.
 */
function naturaly_enqueue_styles() {
	$version = wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'naturaly-style', get_template_directory_uri() . '/assets/css/theme.css', array(), $version );
	// Small file: let WordPress inline it to save a render-blocking request.
	wp_style_add_data( 'naturaly-style', 'path', get_template_directory() . '/assets/css/theme.css' );
}
add_action( 'wp_enqueue_scripts', 'naturaly_enqueue_styles' );

/**
 * Register the pattern category used by the theme.
 */
function naturaly_register_pattern_category() {
	register_block_pattern_category(
		'naturaly',
		array( 'label' => __( 'Naturaly', 'naturaly' ) )
	);
}
add_action( 'init', 'naturaly_register_pattern_category' );

/**
 * Remove the emoji detection script and styles (one script and one style
 * less on every page; modern browsers render emoji natively).
 */
function naturaly_disable_emojis() {
	if ( ! apply_filters( 'naturaly_disable_emojis', true ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'admin_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'naturaly_disable_emojis' );

/**
 * Remove head links that only add weight (RSD, Windows Live Writer,
 * shortlink, generator tag).
 */
function naturaly_clean_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'after_setup_theme', 'naturaly_clean_head' );

/**
 * Find the first image inside a post's content.
 *
 * @param int $post_id Post ID.
 * @return array|null Array with 'id' (attachment ID, 0 if unknown), 'src' and 'alt', or null.
 */
function naturaly_first_content_image( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( ! is_string( $content ) || ! preg_match( '/<img\b[^>]*>/i', $content, $match ) ) {
		return null;
	}
	$tag = $match[0];

	$id = 0;
	if ( preg_match( '/\bwp-image-(\d+)\b/', $tag, $id_match ) && wp_attachment_is_image( (int) $id_match[1] ) ) {
		$id = (int) $id_match[1];
	}

	$src = '';
	if ( preg_match( '/\bsrc\s*=\s*(["\'])(.*?)\1/i', $tag, $src_match ) ) {
		$src = html_entity_decode( $src_match[2], ENT_QUOTES );
	}
	if ( ! $id && ( '' === $src || 0 === strpos( $src, 'data:' ) ) ) {
		return null;
	}

	$alt = '';
	if ( preg_match( '/\balt\s*=\s*(["\'])(.*?)\1/i', $tag, $alt_match ) ) {
		$alt = html_entity_decode( $alt_match[2], ENT_QUOTES );
	}

	return array(
		'id'  => $id,
		'src' => $src,
		'alt' => $alt,
	);
}

/**
 * Post lists: when a post has no featured image, show the first image
 * from its content instead.
 *
 * @param string   $block_content Rendered block.
 * @param array    $block         Parsed block.
 * @param WP_Block $instance      Block instance.
 * @return string
 */
function naturaly_post_thumb_fallback( $block_content, $block, $instance ) {
	$class = isset( $block['attrs']['className'] ) ? $block['attrs']['className'] : '';
	if ( '' !== trim( $block_content ) || false === strpos( $class, 'naturaly-post-thumb' ) ) {
		return $block_content;
	}
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : 0;
	if ( ! $post_id || post_password_required( $post_id ) ) {
		return $block_content;
	}
	$image = naturaly_first_content_image( $post_id );
	if ( ! $image ) {
		return $block_content;
	}

	if ( $image['id'] ) {
		$img = wp_get_attachment_image( $image['id'], 'large', false, array( 'loading' => 'lazy' ) );
	} else {
		$img = sprintf(
			'<img src="%s" alt="%s" loading="lazy" decoding="async" />',
			esc_url( $image['src'] ),
			esc_attr( $image['alt'] )
		);
	}
	if ( ! $img ) {
		return $block_content;
	}

	return sprintf(
		'<figure class="wp-block-post-featured-image naturaly-post-thumb"><a href="%s">%s</a></figure>',
		esc_url( get_permalink( $post_id ) ),
		$img
	);
}
add_filter( 'render_block_core/post-featured-image', 'naturaly_post_thumb_fallback', 10, 3 );
