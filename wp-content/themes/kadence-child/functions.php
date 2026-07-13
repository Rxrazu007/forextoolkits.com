<?php
/**
 * Kadence Child Theme — functions.php
 * 
 * Adds: Custom Post Types (Forecast, Indicator, Expert Advisor),
 *       Tool Categories taxonomy for Pages,
 *       Dynamic sub-menu for tool pages
 */

// =============================================================================
// ১. Parent theme styles
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
// ২. Inherit parent Kadence Customizer settings
// =============================================================================
add_filter( 'pre_option_theme_mods_kadence-child', 'forex_inherit_parent_mods', 5 );

function forex_inherit_parent_mods( $pre ) {
	$child_mods  = is_array( $pre ) ? $pre : array();
	$parent_mods = get_option( 'theme_mods_kadence', array() );
	return array_merge( $parent_mods, $child_mods );
}


// =============================================================================
// ৩. Tool Categories taxonomy (for Pages)
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
// ৪. Custom Post Types — Forecast, Indicator, Expert Advisor
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
// ৫. Flush permalinks on theme switch
// =============================================================================
add_action( 'after_switch_theme', 'forex_flush_rewrite' );

function forex_flush_rewrite() {
	flush_rewrite_rules();
}


// =============================================================================
// ৬. Dynamic sub-menu — auto-shows tool_category pages under menu item
//     Menu item must have CSS class:  menu-dynamic-tools
// =============================================================================
add_filter( 'wp_nav_menu_objects', 'forex_dynamic_menu_items', 10, 2 );
add_action( 'wp_enqueue_scripts', 'forex_dynamic_menu_styles' );

function forex_dynamic_menu_styles() {
	$css = '
	/* Dynamic tools submenu — Kadence-compatible */
	.dynamic-tool-item > a {
		border-bottom: 1px solid rgba(255,255,255,0.08) !important;
	}
	.dynamic-tool-item:last-child > a {
		border-bottom: none !important;
	}
	';
	wp_add_inline_style( 'kadence-style', $css );
}

function forex_dynamic_menu_items( $items, $args ) {

	$tool_parent_key = null;

	foreach ( $items as $key => $item ) {
		$classes = ! empty( $item->classes ) ? $item->classes : array();
		if ( in_array( 'menu-dynamic-tools', $classes, true ) ) {
			$tool_parent_key = $key;
			break;
		}
	}

	if ( null === $tool_parent_key ) {
		return $items;
	}

	$parent_item = $items[ $tool_parent_key ];

	// WP_Query দিয়ে ফিল্টার (get_pages tax_query বাগি)
	$tool_terms = get_terms( array(
		'taxonomy'   => 'tool_category',
		'fields'     => 'ids',
		'hide_empty' => true,
	) );

	if ( empty( $tool_terms ) || is_wp_error( $tool_terms ) ) {
		return $items;
	}

	$query = new WP_Query( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
		'tax_query'      => array( array(
			'taxonomy'         => 'tool_category',
			'field'            => 'term_id',
			'terms'            => $tool_terms,
			'include_children' => true,
		) ),
	) );

	$tools = $query->posts;

	if ( empty( $tools ) ) {
		return $items;
	}

	// Kadence-compatible classes + hover-ready structure
	$parent_item->classes[] = 'menu-item-has-children';
	$parent_item->classes[] = 'menu-item-has-toggle';

	$child_items = array();
	$offset      = 0;

	foreach ( $tools as $page ) {
		$child = new stdClass();
		$child->ID                    = $page->ID;
		$child->db_id                 = 0;
		$child->menu_item_parent      = $parent_item->ID;
		$child->object_id             = $page->ID;
		$child->object                = 'page';
		$child->type                  = 'post_type';
		$child->type_label            = 'Page';
		$child->title                 = get_the_title( $page );
		$child->url                   = get_permalink( $page );
		$child->target                = '';
		$child->attr_title            = '';
		$child->description           = '';
		$child->xfn                   = '';
		$child->menu_order            = $parent_item->menu_order * 100 + $offset + 1;
		$child->current               = ( get_queried_object_id() == $page->ID );
		$child->current_item_ancestor = false;
		$child->current_item_parent   = false;
		// Kadence uses these classes for submenu styling
		$child->classes = array(
			'menu-item',
			'menu-item-type-post_type',
			'menu-item-object-page',
			'dynamic-tool-item',
		);
		$child_items[] = $child;
		$offset++;
	}

	array_splice( $items, $tool_parent_key + 1, 0, $child_items );

	return $items;
}