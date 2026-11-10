<?php
/**
 * Title: Footer
 * Slug: naturaly/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package Naturaly
 */

?>
<!-- wp:group {"className":"naturaly-footer","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"1120px"}} -->
<div class="wp-block-group naturaly-footer" style="padding-top:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50)"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:site-title {"level":0} /-->

<!-- wp:paragraph {"className":"naturaly-meta"} -->
<p class="naturaly-meta">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> &middot; <?php esc_html_e( 'Proudly powered by WordPress', 'naturaly' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
