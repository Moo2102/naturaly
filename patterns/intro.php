<?php
/**
 * Title: Blog intro
 * Slug: naturaly/intro
 * Categories: naturaly, text
 * Description: Main heading (H1) of the blog home, filled with the site tagline or, if empty, the site title.
 *
 * @package Naturaly
 */

$naturaly_intro = get_bloginfo( 'description' );
if ( '' === trim( $naturaly_intro ) ) {
	$naturaly_intro = get_bloginfo( 'name' );
}
?>
<!-- wp:group {"style":{"spacing":{"padding":{"bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":1,"fontSize":"x-large"} -->
<h1 class="wp-block-heading has-x-large-font-size"><?php echo esc_html( $naturaly_intro ); ?></h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->
