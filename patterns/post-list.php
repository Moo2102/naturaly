<?php
/**
 * Title: Post list
 * Slug: naturaly/post-list
 * Categories: naturaly, query
 * Block Types: core/query
 * Description: A single-column list of posts with image, date, category, title and excerpt.
 *
 * @package Naturaly
 */

?>
<!-- wp:query {"queryId":0,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"layout":{"type":"default"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:group {"className":"naturaly-post-item","layout":{"type":"default"}} -->
<div class="wp-block-group naturaly-post-item"><!-- wp:post-featured-image {"isLink":true,"className":"naturaly-post-thumb"} /-->

<!-- wp:group {"className":"naturaly-meta","style":{"spacing":{"blockGap":"0.5rem"}},"layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group naturaly-meta"><!-- wp:post-date /-->

<!-- wp:post-terms {"term":"category","prefix":"· "} /--></div>
<!-- /wp:group -->

<!-- wp:post-title {"isLink":true,"fontSize":"large","style":{"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} /-->

<!-- wp:post-excerpt {"excerptLength":32,"style":{"spacing":{"margin":{"top":"var:preset|spacing|20"}}}} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"style":{"spacing":{"margin":{"top":"var:preset|spacing|50"}}},"layout":{"type":"flex","justifyContent":"space-between"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p><?php esc_html_e( 'Nothing found. Try a different search.', 'naturaly' ); ?></p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->
