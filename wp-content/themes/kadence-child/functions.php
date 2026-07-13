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
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'forecast' ),
		'menu_icon'    => 'dashicons-chart-area',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest' => true,
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
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'indicator' ),
		'menu_icon'    => 'dashicons-visibility',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest' => true,
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
		'public'       => true,
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'expert-advisor' ),
		'menu_icon'    => 'dashicons-admin-generic',
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest' => true,
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