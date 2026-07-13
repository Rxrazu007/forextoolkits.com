<?php
/**
 * Kadence Child Theme — functions.php
 * 
 * যোগ করে: কাস্টম পোস্ট টাইপ (forecast, indicator, ea),
 *          পেজ ট্যাক্সোনমি (tool_category),
 *          ডায়নামিক মেনু
 */

// =============================================================================
// ১. প্যারেন্ট থিমের স্টাইল লোড (Kadence কম্প্যাটিবল)
// =============================================================================
add_action( 'wp_enqueue_scripts', 'kadence_child_enqueue_styles' );

function kadence_child_enqueue_styles() {
	// প্যারেন্ট Kadence থিমের style.css লোড
	wp_enqueue_style(
		'kadence-style',
		get_template_directory_uri() . '/style.css',
		array(),
		wp_get_theme()->parent()->get( 'Version' )
	);
	// চাইল্ড থিমের style.css - প্যারেন্টের পরে লোড হবে
	wp_enqueue_style(
		'kadence-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'kadence-style' ),
		wp_get_theme()->get( 'Version' )
	);
}


// =============================================================================
// ১.বি — প্যারেন্ট Kadence থিমের সব Customizer সেটিংস ইনহেরিট করবে
//        theme_mods_kadence-child থাকলেও প্যারেন্ট থেকেই রিড করবে
// =============================================================================

// সব theme_mod সেটিংস — প্যারেন্ট থিম থেকেই নেবে
add_filter( 'pre_option_theme_mods_kadence-child', 'forex_inherit_parent_mods', 5 );

function forex_inherit_parent_mods( $pre ) {
	$parent_mods = get_option( 'theme_mods_kadence' );
	if ( ! empty( $parent_mods ) ) {
		return $parent_mods;
	}
	return $pre;
}

// Kadence নির্দিষ্ট অপশনগুলোর জন্যও — প্যারেন্ট ব্যবহার করবে
// (kadence customizer settings, palette, layout, etc.)
add_action( 'after_setup_theme', 'forex_bind_parent_customizer_settings' );

function forex_bind_parent_customizer_settings() {
	// Kadence get_theme_mod কলগুলো প্যারেন্ট থিমের সেটিংস দেখবে
	add_filter( 'theme_mod_kadence-child', function( $value, $key ) {
		$parent_mod = get_theme_mod( $key, null ); // প্যারেন্ট থেকে রিড (কোনো ডিফল্ট না দিলে)
		return ( null !== $parent_mod ) ? $parent_mod : $value;
	}, 10, 2 );
}


// =============================================================================
// ২. পেজের জন্য কাস্টম ট্যাক্সোনমি — টুল ক্যাটাগরি
// =============================================================================
add_action( 'init', 'forex_register_tool_taxonomy' );

function forex_register_tool_taxonomy() {
	$labels = array(
		'name'              => 'টুল ক্যাটাগরি',
		'singular_name'     => 'টুল ক্যাটাগরি',
		'search_items'      => 'টুল খুঁজুন',
		'all_items'         => 'সকল টুল ক্যাটাগরি',
		'edit_item'         => 'এডিট ক্যাটাগরি',
		'update_item'       => 'আপডেট ক্যাটাগরি',
		'add_new_item'      => 'নতুন ক্যাটাগরি',
		'new_item_name'     => 'নতুন ক্যাটাগরির নাম',
		'menu_name'         => 'টুল ক্যাটাগরি',
	);

	register_taxonomy( 'tool_category', 'page', array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_menu'      => true,
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
// ৩. কাস্টম পোস্ট টাইপ — ফরকাস্ট, ইন্ডিকেটর, ইএ
// =============================================================================
add_action( 'init', 'forex_register_custom_post_types' );

function forex_register_custom_post_types() {

	// --- ফরেক্স ফরকাস্ট ---
	register_post_type( 'forecast', array(
		'labels'             => array(
			'name'          => 'ফরেক্স ফরকাস্ট',
			'singular_name' => 'ফরকাস্ট',
			'add_new'       => 'নতুন ফরকাস্ট',
			'add_new_item'  => 'নতুন ফরকাস্ট যোগ করুন',
			'edit_item'     => 'ফরকাস্ট এডিট করুন',
			'view_item'     => 'ফরকাস্ট দেখুন',
			'search_items'  => 'ফরকাস্ট খুঁজুন',
			'all_items'     => 'সকল ফরকাস্ট',
			'menu_name'     => 'ফরকাস্ট',
		),
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'forecast' ),
		'menu_icon'          => 'dashicons-chart-area',
		'menu_position'      => 5,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'       => true,
	) );

	// --- ইন্ডিকেটর ---
	register_post_type( 'indicator', array(
		'labels'             => array(
			'name'          => 'ইন্ডিকেটর',
			'singular_name' => 'ইন্ডিকেটর',
			'add_new'       => 'নতুন ইন্ডিকেটর',
			'add_new_item'  => 'নতুন ইন্ডিকেটর যোগ করুন',
			'edit_item'     => 'ইন্ডিকেটর এডিট করুন',
			'view_item'     => 'ইন্ডিকেটর দেখুন',
			'search_items'  => 'ইন্ডিকেটর খুঁজুন',
			'all_items'     => 'সকল ইন্ডিকেটর',
			'menu_name'     => 'ইন্ডিকেটর',
		),
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'indicator' ),
		'menu_icon'          => 'dashicons-visibility',
		'menu_position'      => 6,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'       => true,
	) );

	// --- এক্সপার্ট এডভাইজর ---
	register_post_type( 'ea', array(
		'labels'             => array(
			'name'          => 'এক্সপার্ট এডভাইজর',
			'singular_name' => 'ইএ',
			'add_new'       => 'নতুন ইএ',
			'add_new_item'  => 'নতুন ইএ যোগ করুন',
			'edit_item'     => 'ইএ এডিট করুন',
			'view_item'     => 'ইএ দেখুন',
			'search_items'  => 'ইএ খুঁজুন',
			'all_items'     => 'সকল ইএ',
			'menu_name'     => 'ইএ',
		),
		'public'             => true,
		'has_archive'        => true,
		'rewrite'            => array( 'slug' => 'expert-advisor' ),
		'menu_icon'          => 'dashicons-admin-generic',
		'menu_position'      => 7,
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
		'show_in_rest'       => true,
	) );
}


// =============================================================================
// ৪. থিম অ্যাক্টিভেট — পার্মালিংক ফ্লাশ
// =============================================================================
add_action( 'after_switch_theme', 'forex_flush_rewrite' );

function forex_flush_rewrite() {
	flush_rewrite_rules();
}




// =============================================================================
// ৫. ডায়নামিক মেনু
//    "লাইভ ফরেক্স টুলস"  মেনু আইটেমের CSS ক্লাস:  menu-dynamic-tools
//    "ফরেক্স ক্যালকুলেটরস" মেনু আইটেমের CSS ক্লাস: menu-dynamic-calculators
// =============================================================================
add_filter( 'wp_nav_menu_objects', 'forex_dynamic_menu_items', 10, 2 );

function forex_dynamic_menu_items( $items, $args ) {

	$tool_parent_key = null;

	// "menu-dynamic-tools" CSS ক্লাস আছে এমন মেনু আইটেম খুঁজি
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

	// টুল ক্যাটাগরি সেট করা পেজগুলো নিয়ে আসি
	$parent_item = $items[ $tool_parent_key ];

	$tool_terms = get_terms( array(
		'taxonomy'   => 'tool_category',
		'fields'     => 'ids',
		'hide_empty' => true,
	) );

	if ( empty( $tool_terms ) || is_wp_error( $tool_terms ) ) {
		return $items;
	}

	$tools_query = get_pages( array(
		'tax_query'   => array(
			array(
				'taxonomy'         => 'tool_category',
				'field'            => 'term_id',
				'terms'            => $tool_terms,
				'include_children' => true,
			),
		),
		'sort_column' => 'menu_order',
		'sort_order'  => 'ASC',
	) );

	if ( empty( $tools_query ) ) {
		return $items;
	}

	// ডায়নামিক চাইল্ড মেনু আইটেম তৈরি
	$child_items = array();
	$insert_pos  = $tool_parent_key + 1;
	$offset      = 0;

	foreach ( $tools_query as $tool_page ) {
		$child = new stdClass();
		$child->ID                    = $tool_page->ID;
		$child->db_id                 = 0; // 0 = ডায়নামিক
		$child->menu_item_parent      = $parent_item->ID;
		$child->object_id             = $tool_page->ID;
		$child->object                = 'page';
		$child->type                  = 'post_type';
		$child->title                 = get_the_title( $tool_page );
		$child->url                   = get_permalink( $tool_page );
		$child->target                = '';
		$child->classes               = array( 'dynamic-tool-item' );
		$child->current               = false;
		$child->current_item_ancestor = false;
		$child->current_item_parent   = false;
		$child->menu_order            = $parent_item->menu_order * 100 + $offset + 1;
		$child_items[]                = $child;
		$offset++;
	}

	array_splice( $items, $insert_pos, 0, $child_items );

	return $items;
}