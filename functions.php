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
