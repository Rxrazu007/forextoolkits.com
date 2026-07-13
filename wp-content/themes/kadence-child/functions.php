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
// ৬. CPT archive — sidebar layout & custom styles
// =============================================================================
add_filter( 'kadence_post_layout', 'forex_cpt_archive_layout', 99, 2 );

function forex_cpt_archive_layout( $layout, $context ) {
	if ( is_post_type_archive( array( 'forecast', 'indicator', 'ea' ) ) || is_tax( 'tool_category' ) ) {
		// Right sidebar layout — for ads/widgets
		$layout['layout']    = 'normal';
		$layout['side']      = 'right';
		$layout['sidebar_id'] = 'sidebar-primary';
		$layout['class']     = 'has-sidebar';
	}
	return $layout;
}

add_action( 'wp_enqueue_scripts', 'forex_cpt_archive_styles', 20 );

function forex_cpt_archive_styles() {
	if ( ! is_post_type_archive( array( 'forecast', 'indicator', 'ea' ) ) && ! is_tax( 'tool_category' ) ) {
		return;
	}
	$css = '
	/* Reduce archive page hero/header padding */
	.archive .entry-hero-container-inner {
		padding-top: 1.5rem !important;
		padding-bottom: 0.5rem !important;
		min-height: auto !important;
	}
	.archive .page-header-title {
		font-size: 1.8rem !important;
		margin-bottom: 0 !important;
	}
	.archive .archive-description {
		margin-top: 0.25rem !important;
	}

	/* Light text on dark cards */
	.archive .entry-title a {
		color: #e8e8e8 !important;
	}
	.archive .entry-title a:hover {
		color: #fff !important;
	}
	.archive .entry-summary {
		color: #b8b8b8 !important;
	}
	.archive .entry-meta {
		color: #999 !important;
	}
	.archive .entry-meta a {
		color: #bbb !important;
	}

	/* Sidebar — dark bg + rounded, match site theme */
	.archive .widget-area {
		background: #16162a !important;
		padding: 1.5rem 1rem !important;
		border-radius: 8px !important;
		border: 1px solid rgba(255,255,255,0.06) !important;
	}
	.archive .widget-area .widget-title {
		color: #e0e0e0 !important;
	}
	.archive .widget-area .widget {
		color: #b0b0b0 !important;
	}
	.archive .widget-area a {
		color: #8ab4f8 !important;
	}

	/* Card background lighter for contrast */
	.archive .content-bg {
		background: rgba(255,255,255,0.05) !important;
		padding: 1.25rem !important;
	}
	';
	wp_add_inline_style( 'kadence-child-style', $css );
}