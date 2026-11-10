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

require get_template_directory() . '/inc/seo.php';
