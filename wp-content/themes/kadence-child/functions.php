<?php
/**
 * Kadence Child Theme — functions.php
 * 
 * Adds: Custom Post Types (Forecast, Indicator, Expert Advisor),
 *       Tool Categories taxonomy for Pages
 * 
 * Menu management: Use Appearance → Menus in WordPress Dashboard
 */

// =============================================================================
// １. Parent theme styles
// =============================================================================
add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles' );

function kadence_child_enqueue_styles() {
	wp_enqueue_style(
		'kadence-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->parent()->get( 'Version' )
	);
	wp_enqueue_style(
		'kadence-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'kadence-style' ),
		wp_get_theme()->get( 'Version' )
	);
}


// =============================================================================
// ２. Inherit parent Kadence Customizer settings
// =============================================================================
add_filter( 'pre_option_theme_mods_kadence-child', 'forex_inherit_parent_mods', 5 );

function forex_inherit_parent_mods( $pre ) {
	$child_mods  = is_array( $pre ) ? $pre : array();
	$parent_mods = get_option( 'theme_mods_kadence', array() );
	return array_merge( $parent_mods, $child_mods );
}


// =============================================================================
// ３. Tool Categories taxonomy (for Pages)
// =============================================================================
add_action( 'init', 'forex_register_tool_taxonomy' );

function forex_register_tool_taxonomy() {
	register_taxonomy( 'tool_category', 'page', array(
		'hierarchical'      => true,
		'labels'            => array(
			'name'              => 'Tool Categories',
			'singular_name'     => 'Tool Category',
			'search_items'      => 'Search Tool Categories',
			'all_items'         => 'All Tool Categories',
			'edit_item'         => 'Edit Tool Category',
			'update_item'       => 'Update Tool Category',
			'add_new_item'      => 'Add New Tool Category',
			'new_item_name'     => 'New Tool Category Name',
			'menu_name'         => 'Tool Categories',
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'tool-category' ),
		'capabilities'      => array(
			'manage_terms' => 'edit_pages',
			'edit_terms'   => 'edit_pages',
			'delete_terms' => 'edit_pages',
			'assign_terms' => 'edit_pages',
		),
	) );
}


// =============================================================================
// ４. Custom Post Types — Forecast, Indicator, Expert Advisor
// =============================================================================
add_action( 'init', 'forex_register_custom_post_types' );

function forex_register_custom_post_types() {

	register_post_type( 'forecast', array(
		'labels'       => array(
			'name'          => 'Forecasts',
			'singular_name' => 'Forecast',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Forecast',
			'edit_item'     => 'Edit Forecast',
			'all_items'     => 'All Forecasts',
			'menu_name'     => 'Forecasts',
		),
		'public'          => true,
		'has_archive'     => true,
		'rewrite'         => array( 'slug' => 'forecast' ),
		'menu_icon'       => 'dashicons-chart-area',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'author' ),
		'show_in_rest'    => true,
		'publicly_queryable' => true,
	) );

	register_post_type( 'indicator', array(
		'labels'       => array(
			'name'          => 'Indicators',
			'singular_name' => 'Indicator',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Indicator',
			'edit_item'     => 'Edit Indicator',
			'all_items'     => 'All Indicators',
			'menu_name'     => 'Indicators',
		),
		'public'          => true,
		'has_archive'     => true,
		'rewrite'         => array( 'slug' => 'indicator' ),
		'menu_icon'       => 'dashicons-visibility',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'author' ),
		'show_in_rest'    => true,
		'publicly_queryable' => true,
	) );

	register_post_type( 'ea', array(
		'labels'       => array(
			'name'          => 'Expert Advisors',
			'singular_name' => 'Expert Advisor',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Expert Advisor',
			'edit_item'     => 'Edit Expert Advisor',
			'all_items'     => 'All Expert Advisors',
			'menu_name'     => 'Expert Advisors',
		),
		'public'          => true,
		'has_archive'     => true,
		'rewrite'         => array( 'slug' => 'expert-advisor' ),
		'menu_icon'       => 'dashicons-admin-generic',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields', 'comments', 'author' ),
		'show_in_rest'    => true,
		'publicly_queryable' => true,
	) );
}


// =============================================================================
// ５. Flush permalinks on theme switch
// =============================================================================
add_action( 'after_switch_theme', 'forex_flush_rewrite' );

function forex_flush_rewrite() {
	flush_rewrite_rules();
}


// =============================================================================
// ৬. CPT archive — custom styles
// =============================================================================
add_action( 'wp_enqueue_scripts', 'forex_cpt_archive_styles', 20 );

function forex_cpt_archive_styles() {
	$css = '
	/* CPT archive: reduce header spacing */
	.post-type-archive-forecast .entry-hero,
	.post-type-archive-indicator .entry-hero,
	.post-type-archive-ea .entry-hero {
		padding-top: 1.5rem !important;
		padding-bottom: 0.5rem !important;
		min-height: auto !important;
	}
	.post-type-archive-forecast .page-header-title,
	.post-type-archive-indicator .page-header-title,
	.post-type-archive-ea .page-header-title {
		font-size: 1.8rem !important;
		margin-bottom: 0 !important;
	}

	/* CPT archive: lighter text on dark bg */
	.post-type-archive-forecast .entry-title a,
	.post-type-archive-indicator .entry-title a,
	.post-type-archive-ea .entry-title a {
		color: #e8e8e8 !important;
	}
	.post-type-archive-forecast .entry-title a:hover,
	.post-type-archive-indicator .entry-title a:hover,
	.post-type-archive-ea .entry-title a:hover {
		color: #ffffff !important;
	}

	/* Single post/page headings — readable on dark bg */
	.single .entry-title,
	.single-forecast .entry-title,
	.single-indicator .entry-title,
	.single-ea .entry-title {
		color: #c5c5c5 !important;
	}
	.single .entry-title a,
	.single-forecast .entry-title a,
	.single-indicator .entry-title a,
	.single-ea .entry-title a {
		color: #c5c5c5 !important;
	}
	.single .entry-title a:hover,
	.single-forecast .entry-title a:hover,
	.single-indicator .entry-title a:hover,
	.single-ea .entry-title a:hover {
		color: #ffffff !important;
	}
	.post-type-archive-forecast .entry-summary,
	.post-type-archive-indicator .entry-summary,
	.post-type-archive-ea .entry-summary {
		color: #b8b8b8 !important;
	}
	.post-type-archive-forecast .entry-meta,
	.post-type-archive-indicator .entry-meta,
	.post-type-archive-ea .entry-meta {
		color: #999 !important;
	}
	.post-type-archive-forecast .entry-meta a,
	.post-type-archive-indicator .entry-meta a,
	.post-type-archive-ea .entry-meta a {
		color: #bbb !important;
	}
	/* CPT archive: card styling */
	.post-type-archive-forecast .content-bg,
	.post-type-archive-indicator .content-bg,
	.post-type-archive-ea .content-bg {
		background: rgba(255,255,255,0.05) !important;
		padding: 1.25rem !important;
	}
	/* Make sidebar area dark (ready for ads) */
	.post-type-archive-forecast #secondary,
	.post-type-archive-indicator #secondary,
	.post-type-archive-ea #secondary {
		background: #16162a !important;
		padding: 1.5rem 1rem !important;
		border-radius: 8px !important;
		border: 1px solid rgba(255,255,255,0.08) !important;
	}
	.post-type-archive-forecast #secondary .widget-title,
	.post-type-archive-indicator #secondary .widget-title,
	.post-type-archive-ea #secondary .widget-title {
		color: #e0e0e0 !important;
	}
	.post-type-archive-forecast #secondary .widget,
	.post-type-archive-indicator #secondary .widget,
	.post-type-archive-ea #secondary .widget {
		color: #b0b0b0 !important;
	}
	.post-type-archive-forecast #secondary a,
	.post-type-archive-indicator #secondary a,
	.post-type-archive-ea #secondary a {
		color: #8ab4f8 !important;
	}
	/* Make content area background transparent so dark bg shows through */
	.post-type-archive-forecast .content-container,
	.post-type-archive-indicator .content-container,
	.post-type-archive-ea .content-container {
		padding: 0 1rem !important;
	}
	';
	wp_add_inline_style( 'kadence-child-style', $css );
}


// =============================================================================
// ৭. Shortcode: [forex_posts type="indicator|ea|forecast" count="10"]
//     Blog-style listing with pagination. Use in any Gutenberg page.
// =============================================================================
add_shortcode( 'forex_posts', 'forex_posts_shortcode' );
add_action( 'wp_enqueue_scripts', 'forex_posts_styles', 25 );

function forex_posts_shortcode( $atts ) {
	$a = shortcode_atts( array(
		'type'  => 'forecast',
		'count' => 10,
	), $atts );

	$paged = max( 1, get_query_var( 'paged' ) ?: 1 );

	$q = new WP_Query( array(
		'post_type'      => sanitize_key( $a['type'] ),
		'posts_per_page' => max( 1, (int) $a['count'] ),
		'paged'          => $paged,
		'post_status'    => 'publish',
	) );

	if ( ! $q->have_posts() ) {
		return '<p>No posts found.</p>';
	}

	ob_start();
	echo '<div class="forex-shortcode-posts">';
	while ( $q->have_posts() ) {
		$q->the_post();
		?>
		<article class="forex-sc-item">
			<h3 class="forex-sc-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<div class="forex-sc-meta"><?php echo get_the_date(); ?></div>
			<div class="forex-sc-excerpt"><?php the_excerpt(); ?></div>
			<a class="forex-sc-readmore" href="<?php the_permalink(); ?>">Read More <span class="sc-arrow">→</span></a>
		</article>
		<?php
	}
	echo '</div>';

	// Pagination
	if ( $q->max_num_pages > 1 ) {
		echo '<div class="forex-sc-pagination">';
		echo paginate_links( array(
			'base'    => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
			'format'  => '?paged=%#%',
			'current' => $paged,
			'total'   => $q->max_num_pages,
			'prev_text' => '‹ Previous',
			'next_text' => 'Next ›',
		) );
		echo '</div>';
	}

	wp_reset_postdata();
	return ob_get_clean();
}

function forex_posts_styles() {
	$css = '
	/* CPT single: content headings — match header color */
	.single-forecast .entry-content h2,
	.single-indicator .entry-content h2,
	.single-ea .entry-content h2,
	.single-forecast .entry-content h3,
	.single-indicator .entry-content h3,
	.single-ea .entry-content h3 {
		color: #c5c5c5 !important;
	}
	.single-forecast .entry-content p,
	.single-indicator .entry-content p,
	.single-ea .entry-content p {
		color: #b8b8b8 !important;
	}

	/* Comment box: dark theme */
	#comments,
	#respond {
		background: rgba(255,255,255,0.03) !important;
		padding: 1.5rem !important;
		border-radius: 8px !important;
		border: 1px solid rgba(255,255,255,0.08) !important;
		margin-top: 1.5rem !important;
	}
	#comments .comment-body {
		padding: 1rem 0 !important;
		border-bottom: 1px solid rgba(255,255,255,0.06) !important;
	}
	#comments .comment-author .fn a,
	#comments .comment-metadata a {
		color: #b0b0b0 !important;
	}
	#comments .comment-author .fn a:hover,
	#comments .comment-metadata a:hover {
		color: #fff !important;
	}
	#comments .reply a {
		color: #8ab4f8 !important;
	}
	#respond label,
	#respond .comment-notes {
		color: #b0b0b0 !important;
	}
	#respond input[type="text"],
	#respond input[type="email"],
	#respond input[type="url"],
	#respond textarea {
		background: rgba(255,255,255,0.06) !important;
		border: 1px solid rgba(255,255,255,0.12) !important;
		color: #e0e0e0 !important;
		border-radius: 4px !important;
		padding: 0.6rem 0.8rem !important;
	}
	#respond input:focus,
	#respond textarea:focus {
		border-color: #8ab4f8 !important;
		outline: none !important;
	}
	#respond .form-submit input#submit {
		background: #8ab4f8 !important;
		color: #111 !important;
		border: none !important;
		padding: 0.6rem 1.5rem !important;
		border-radius: 4px !important;
		font-weight: 600 !important;
		cursor: pointer !important;
	}
	#respond .form-submit input#submit:hover {
		background: #6d9fd8 !important;
	}

	/* CPT single: meta styling */
	.single-forecast .entry-header .entry-meta,
	.single-indicator .entry-header .entry-meta,
	.single-ea .entry-header .entry-meta {
		color: #999 !important;
		font-size: 0.85rem;
		margin: 0 0 0.5rem 0;
	}
	.single-forecast .entry-header .entry-meta a,
	.single-indicator .entry-header .entry-meta a,
	.single-ea .entry-header .entry-meta a {
		color: #8ab4f8 !important;
	}
	.single-forecast .entry-header .entry-title,
	.single-indicator .entry-header .entry-title,
	.single-ea .entry-header .entry-title {
		color: #c5c5c5 !important;
	}
	.forex-shortcode-posts {
		display: flex;
		flex-direction: column;
		gap: 1.5rem;
	}
	.forex-sc-item {
		background: rgba(255,255,255,0.05);
		padding: 1.5rem;
		border-radius: 8px;
		border: 1px solid rgba(255,255,255,0.08);
	}
	.forex-sc-title {
		margin: 0 0 0.25rem 0;
		font-size: 1.3rem;
	}
	.forex-sc-title a {
		color: #e8e8e8;
		text-decoration: none;
	}
	.forex-sc-title a:hover {
		color: #ffffff;
		text-decoration: underline;
	}
	.forex-sc-meta {
		color: #888;
		font-size: 0.85rem;
		margin-bottom: 0.75rem;
	}
	.forex-sc-excerpt {
		color: #b0b0b0;
		line-height: 1.6;
		margin-bottom: 0.75rem;
	}
	.forex-sc-readmore {
		color: #8ab4f8;
		text-decoration: none;
		font-weight: 600;
	}
	.forex-sc-readmore:hover {
		color: #fff;
		text-decoration: underline;
	}
	.sc-arrow {
		display: inline-block;
		transition: transform 0.2s;
	}
	.forex-sc-readmore:hover .sc-arrow {
		transform: translateX(4px);
	}
	.forex-sc-pagination {
		margin-top: 2rem;
		text-align: center;
		display: flex;
		gap: 0.5rem;
		justify-content: center;
	}
	.forex-sc-pagination a,
	.forex-sc-pagination span {
		background: rgba(255,255,255,0.08);
		color: #b0b0b0;
		padding: 0.5rem 1rem;
		border-radius: 4px;
		text-decoration: none;
	}
	.forex-sc-pagination a:hover {
		background: rgba(255,255,255,0.15);
		color: #fff;
	}
	.forex-sc-pagination .current {
		background: #8ab4f8;
		color: #111;
	}
	';
	wp_add_inline_style( 'kadence-child-style', $css );
}