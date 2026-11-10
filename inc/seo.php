<?php
/**
 * Technical SEO: meta description, canonical URLs, Open Graph / Twitter
 * cards, JSON-LD structured data and robots rules.
 *
 * Everything here switches itself off when a dedicated SEO plugin
 * (Yoast, Rank Math, AIOSEO, SEOPress, The SEO Framework, Slim SEO) is
 * active, so the output is never duplicated. It can also be disabled with:
 *
 *     add_filter( 'naturaly_seo_enabled', '__return_false' );
 *
 * @package Naturaly
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the theme should print its own SEO tags.
 *
 * @return bool
 */
function naturaly_seo_enabled() {
	$plugin_active = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'THE_SEO_FRAMEWORK_VERSION' )
		|| defined( 'SLIM_SEO_VER' );

	return (bool) apply_filters( 'naturaly_seo_enabled', ! $plugin_active );
}

/**
 * Turn arbitrary HTML into a clean, single-line text of at most $length characters.
 *
 * @param string $text   Raw text or HTML.
 * @param int    $length Maximum length.
 * @return string
 */
function naturaly_seo_clean_text( $text, $length = 160 ) {
	$text = wp_strip_all_tags( strip_shortcodes( (string) $text ), true );
	$text = html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );

	if ( mb_strlen( $text ) > $length ) {
		$cut     = mb_substr( $text, 0, $length - 1 );
		$trimmed = preg_replace( '/\s+\S*$/u', '', $cut );
		if ( is_string( $trimmed ) && mb_strlen( $trimmed ) > $length * 0.6 ) {
			$cut = $trimmed;
		}
		$text = rtrim( $cut, " ,.;:-–" ) . '…';
	}

	return $text;
}

/**
 * Meta description for the current request.
 *
 * @return string
 */
function naturaly_seo_description() {
	$description = '';

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			if ( has_excerpt( $post ) ) {
				$description = $post->post_excerpt;
			} else {
				$content     = excerpt_remove_blocks( $post->post_content );
				$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $content ) ), 40, '' );
			}
		}
	} elseif ( is_front_page() || is_home() ) {
		$description = get_bloginfo( 'description' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$description = term_description();
		if ( '' === trim( wp_strip_all_tags( $description ) ) ) {
			/* translators: %s: term name. */
			$description = sprintf( __( 'All posts about %s.', 'naturaly' ), single_term_title( '', false ) );
		}
	} elseif ( is_author() ) {
		$author      = get_queried_object();
		$description = $author instanceof WP_User ? get_the_author_meta( 'description', $author->ID ) : '';
	} elseif ( is_post_type_archive() ) {
		$description = get_the_post_type_description();
	}

	if ( '' === trim( wp_strip_all_tags( (string) $description ) ) ) {
		$description = get_bloginfo( 'description' );
	}

	return (string) apply_filters( 'naturaly_seo_description', naturaly_seo_clean_text( $description ) );
}

/**
 * Canonical URL for non-singular views (core already handles singular ones).
 *
 * @return string Empty string when no canonical should be printed.
 */
function naturaly_seo_archive_canonical() {
	$base = '';

	if ( is_front_page() && is_home() ) {
		$base = home_url( '/' );
	} elseif ( is_home() ) {
		$base = get_permalink( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		$base = is_wp_error( $link ) ? '' : $link;
	} elseif ( is_author() ) {
		$base = get_author_posts_url( (int) get_queried_object_id() );
	} elseif ( is_post_type_archive() ) {
		$link = get_post_type_archive_link( get_query_var( 'post_type' ) );
		$base = $link ? $link : '';
	}

	if ( ! $base ) {
		return '';
	}

	$paged = (int) get_query_var( 'paged' );
	if ( $paged > 1 ) {
		global $wp_rewrite;
		if ( $wp_rewrite instanceof WP_Rewrite && $wp_rewrite->using_permalinks() ) {
			$base = user_trailingslashit( trailingslashit( $base ) . $wp_rewrite->pagination_base . '/' . $paged, 'paged' );
		} else {
			$base = add_query_arg( 'paged', $paged, $base );
		}
	}

	return $base;
}

/**
 * Current URL used for og:url.
 *
 * @return string
 */
function naturaly_seo_current_url() {
	if ( is_singular() ) {
		$url = wp_get_canonical_url();
		return $url ? $url : get_permalink();
	}
	return naturaly_seo_archive_canonical();
}

/**
 * Main image for the current request: featured image, otherwise site icon.
 *
 * @return array{url:string,width:int,height:int,alt:string}|null
 */
function naturaly_seo_image() {
	$attachment_id = 0;

	if ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$attachment_id = (int) get_post_thumbnail_id( get_queried_object_id() );
	}

	if ( $attachment_id ) {
		$src = wp_get_attachment_image_src( $attachment_id, 'full' );
		if ( $src ) {
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	$icon = get_site_icon_url( 512 );
	if ( $icon ) {
		return array(
			'url'    => $icon,
			'width'  => 512,
			'height' => 512,
			'alt'    => get_bloginfo( 'name' ),
		);
	}

	return null;
}

/**
 * Print meta description, canonical, Open Graph and Twitter tags.
 */
function naturaly_seo_head() {
	if ( ! naturaly_seo_enabled() || is_404() ) {
		return;
	}

	$description = naturaly_seo_description();
	$url         = naturaly_seo_current_url();
	$image       = naturaly_seo_image();
	$is_article  = is_singular( 'post' );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	if ( ! is_singular() && ! is_search() ) {
		$canonical = naturaly_seo_archive_canonical();
		if ( $canonical ) {
			printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );
		}
	}

	$tags = array(
		'og:locale'      => get_locale(),
		'og:type'        => $is_article ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => wp_get_document_title(),
		'og:description' => $description,
		'og:url'         => $url,
	);

	if ( $image ) {
		$tags['og:image']        = $image['url'];
		$tags['og:image:width']  = (string) $image['width'];
		$tags['og:image:height'] = (string) $image['height'];
		$tags['og:image:alt']    = $image['alt'];
	}

	if ( $is_article ) {
		$post                           = get_queried_object();
		$tags['article:published_time'] = get_post_time( 'c', true, $post );
		$tags['article:modified_time']  = get_post_modified_time( 'c', true, $post );
	}

	foreach ( $tags as $property => $content ) {
		if ( '' !== (string) $content ) {
			printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}

	$large = $image && $image['width'] >= 600;
	printf( '<meta name="twitter:card" content="%s" />' . "\n", $large ? 'summary_large_image' : 'summary' );
}
add_action( 'wp_head', 'naturaly_seo_head', 1 );

/**
 * Build the JSON-LD graph for the current request.
 *
 * @return array
 */
function naturaly_seo_schema_graph() {
	$home      = home_url( '/' );
	$site_name = get_bloginfo( 'name' );
	$language  = str_replace( '_', '-', get_locale() );

	$publisher = array(
		'@type' => 'Organization',
		'@id'   => $home . '#publisher',
		'name'  => $site_name,
		'url'   => $home,
	);

	$logo_id  = (int) get_theme_mod( 'custom_logo' );
	$logo_url = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : get_site_icon_url( 512 );
	if ( $logo_url ) {
		$publisher['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $logo_url,
		);
	}

	$graph = array(
		array(
			'@type'           => 'WebSite',
			'@id'             => $home . '#website',
			'url'             => $home,
			'name'            => $site_name,
			'description'     => get_bloginfo( 'description' ),
			'inLanguage'      => $language,
			'publisher'       => array( '@id' => $home . '#publisher' ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => add_query_arg( 's', '{search_term_string}', $home ),
				),
				'query-input' => 'required name=search_term_string',
			),
		),
		apply_filters( 'naturaly_schema_publisher', $publisher ),
	);

	$breadcrumbs = array(
		array(
			'name' => $site_name,
			'url'  => $home,
		),
	);

	if ( is_singular() && get_queried_object() instanceof WP_Post ) {
		$post  = get_queried_object();
		$url   = get_permalink( $post );
		$image = naturaly_seo_image();

		$page = array(
			'@type'         => 'WebPage',
			'@id'           => $url . '#webpage',
			'url'           => $url,
			'name'          => wp_get_document_title(),
			'description'   => naturaly_seo_description(),
			'inLanguage'    => $language,
			'isPartOf'      => array( '@id' => $home . '#website' ),
			'datePublished' => get_post_time( 'c', true, $post ),
			'dateModified'  => get_post_modified_time( 'c', true, $post ),
		);
		if ( $image ) {
			$page['primaryImageOfPage'] = array(
				'@type' => 'ImageObject',
				'url'   => $image['url'],
			);
		}
		$graph[] = $page;

		if ( 'post' === $post->post_type ) {
			$author     = get_userdata( (int) $post->post_author );
			$categories = wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) );
			$tags       = wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) );

			$article = array(
				'@type'            => 'BlogPosting',
				'@id'              => $url . '#article',
				'headline'         => naturaly_seo_clean_text( get_the_title( $post ), 110 ),
				'description'      => naturaly_seo_description(),
				'datePublished'    => get_post_time( 'c', true, $post ),
				'dateModified'     => get_post_modified_time( 'c', true, $post ),
				'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
				'isPartOf'         => array( '@id' => $home . '#website' ),
				'publisher'        => array( '@id' => $home . '#publisher' ),
				'inLanguage'       => $language,
				'wordCount'        => count( preg_split( '/\s+/u', trim( wp_strip_all_tags( $post->post_content ) ), -1, PREG_SPLIT_NO_EMPTY ) ),
			);
			if ( $author ) {
				$article['author'] = array(
					'@type' => 'Person',
					'name'  => $author->display_name,
					'url'   => get_author_posts_url( $author->ID ),
				);
			}
			if ( $image ) {
				$article['image'] = array(
					'@type'  => 'ImageObject',
					'url'    => $image['url'],
					'width'  => $image['width'],
					'height' => $image['height'],
				);
			}
			if ( ! is_wp_error( $categories ) && $categories ) {
				$article['articleSection'] = $categories;
			}
			if ( ! is_wp_error( $tags ) && $tags ) {
				$article['keywords'] = implode( ', ', $tags );
			}
			$graph[] = $article;

			$primary = get_the_category( $post->ID );
			if ( $primary ) {
				$link = get_category_link( $primary[0] );
				if ( $link ) {
					$breadcrumbs[] = array(
						'name' => $primary[0]->name,
						'url'  => $link,
					);
				}
			}
		} else {
			foreach ( array_reverse( get_post_ancestors( $post ) ) as $ancestor_id ) {
				$breadcrumbs[] = array(
					'name' => get_the_title( $ancestor_id ),
					'url'  => get_permalink( $ancestor_id ),
				);
			}
		}

		$breadcrumbs[] = array(
			'name' => get_the_title( $post ),
			'url'  => $url,
		);
		$breadcrumb_id = is_front_page() ? '' : $url . '#breadcrumb';
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$breadcrumbs[] = array(
				'name' => $term->name,
				'url'  => $link,
			);
			$breadcrumb_id = $link . '#breadcrumb';
		}
	}

	if ( ! empty( $breadcrumb_id ) && count( $breadcrumbs ) > 1 ) {
		$items = array();
		foreach ( $breadcrumbs as $index => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => naturaly_seo_clean_text( $crumb['name'], 110 ),
				'item'     => $crumb['url'],
			);
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $breadcrumb_id,
			'itemListElement' => $items,
		);
	}

	return (array) apply_filters( 'naturaly_schema_graph', $graph );
}

/**
 * Print the JSON-LD block.
 */
function naturaly_seo_schema() {
	if ( ! naturaly_seo_enabled() || is_404() || is_search() ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array_values( array_filter( naturaly_seo_schema_graph() ) ),
	);

	// JSON_HEX_TAG keeps "</script>" in titles from closing the tag early.
	$json = wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG );
	if ( $json ) {
		echo '<script type="application/ld+json">' . $json . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
add_action( 'wp_head', 'naturaly_seo_schema', 20 );

/**
 * Keep 404 pages out of the index (core already does this for search results).
 *
 * @param array $robots Robots directives.
 * @return array
 */
function naturaly_seo_robots( $robots ) {
	if ( naturaly_seo_enabled() && is_404() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'naturaly_seo_robots' );
