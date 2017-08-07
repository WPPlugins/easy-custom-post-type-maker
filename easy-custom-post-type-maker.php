<?php
/*
Plugin Name: Easy Custom Post Type Maker
Plugin URI: https://www.cleantoshine.com.au/
Description: Easy Custom Post Type Maker create Custom Post Types and custom Taxonomies in a user friendly very easy way.
Version: 1.0
Author: Clean to Shine
Author URI: https://www.cleantoshine.com.au/


Originally by: https://www.cleantoshine.com.au/

Released under the GPL v.2, http://www.gnu.org/copyleft/gpl.html

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
*/
/**
 * @author		https://www.cleantoshine.com.au/
 * @copyright	Copyright (c) 2017, Clean to Shine
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GPLv2
 * @version	 	1.0
 */

//avoid direct calls to this file
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/**
 * Main class to run the plugin
 *
 * @since	1.0.0
 */
class eCptm {

	// vars
	var $dir,
		$path,
		$version;

	function __construct() {

		// vars
		$this->dir = plugins_url( '', __FILE__ );
		$this->path = plugin_dir_path( __FILE__ );
		$this->version = '1.0';

		// actions
		add_action( 'init', array($this, 'init') );
		add_action( 'init', array($this, 'ecptm_create_custom_post_types') );
		add_action( 'admin_menu', array($this, 'ecptm_admin_menu') );
		add_action( 'admin_enqueue_scripts', array($this, 'ecptm_styles') );
		add_action( 'add_meta_boxes', array($this, 'ecptm_create_meta_boxes') );
		add_action( 'save_post', array($this, 'ecptm_save_post') );
		add_action( 'manage_posts_custom_column', array($this, 'ecptm_custom_columns'), 10, 2 );
		add_action( 'manage_posts_custom_column', array($this, 'ecptm_tax_custom_columns'), 10, 2 );
		add_action( 'admin_footer', array($this,'ecptm_admin_footer') );
		add_action( 'wp_prepare_attachment_for_js', array($this, 'wp_prepare_attachment_for_js'), 10, 3 );

		// filters
		add_filter( 'manage_ecptm_posts_columns', array($this, 'ecptm_change_columns') );
		add_filter( 'manage_edit-ecptm_sortable_columns', array($this, 'ecptm_sortable_columns') );
		add_filter( 'manage_ecptm_tax_posts_columns', array($this, 'ecptm_tax_change_columns') );
		add_filter( 'manage_edit-ecptm_tax_sortable_columns', array($this, 'ecptm_tax_sortable_columns') );
		add_filter( 'post_updated_messages', array($this, 'ecptm_post_updated_messages') );

		// set textdomain
		load_plugin_textdomain( 'ecptm', false, basename( dirname(__FILE__) ).'/lang' );

	}  // # function __construct()

	public function init() {

		// Create ecptm post type
		$labels = array(
			'name' => __( 'Easy Custom Post Type Maker', 'easy-custom-post-type-maker' ),
			'singular_name' => __( 'Easy Custom Post Type', 'easy-custom-post-type-maker' ),
			'add_new' => __( 'Add New' , 'easy-custom-post-type-maker' ),
			'add_new_item' => __( 'Add New Easy Custom Post Type' , 'easy-custom-post-type-maker' ),
			'edit_item' =>  __( 'Edit Easy Custom Post Type' , 'easy-custom-post-type-maker' ),
			'new_item' => __( 'New Easy Custom Post Type' , 'easy-custom-post-type-maker' ),
			'view_item' => __('View Easy Custom Post Type', 'easy-custom-post-type-maker' ),
			'search_items' => __('Search Easy Custom Post Types', 'easy-custom-post-type-maker' ),
			'not_found' =>  __('No Easy Custom Post Types found', 'easy-custom-post-type-maker' ),
			'not_found_in_trash' => __('No Easy Custom Post Types found in Trash', 'easy-custom-post-type-maker' ),
		);

		register_post_type( 'ecptm', array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'_builtin' =>  false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => 'ecptm',
			'supports' => array(
				'title'
			),
			'show_in_menu' => false,
		));

		// Create ecptm_tax post type
		$labels = array(
			'name' => __( 'Easy Custom Taxonomies', 'easy-custom-post-type-maker' ),
			'singular_name' => __( 'Easy Custom Taxonomy', 'easy-custom-post-type-maker' ),
			'add_new' => __( 'Add New' , 'easy-custom-post-type-maker' ),
			'add_new_item' => __( 'Add New Easy Custom Taxonomy' , 'easy-custom-post-type-maker' ),
			'edit_item' =>  __( 'Edit Easy Custom Taxonomy' , 'easy-custom-post-type-maker' ),
			'new_item' => __( 'New Easy Custom Taxonomy' , 'easy-custom-post-type-maker' ),
			'view_item' => __('View Easy Custom Taxonomy', 'easy-custom-post-type-maker' ),
			'search_items' => __('Search Easy Custom Taxonomies', 'easy-custom-post-type-maker' ),
			'not_found' =>  __('No Easy Custom Taxonomies found', 'easy-custom-post-type-maker' ),
			'not_found_in_trash' => __('No Easy Custom Taxonomies found in Trash', 'easy-custom-post-type-maker' ),
		);

		register_post_type( 'ecptm_tax', array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'_builtin' =>  false,
			'capability_type' => 'page',
			'hierarchical' => false,
			'rewrite' => false,
			'query_var' => 'ecptm_tax',
			'supports' => array(
				'title'
			),
			'show_in_menu' => false,
		));

		// Add image size for the Easy Custom Post Type icon
		if ( function_exists( 'add_image_size' ) ) {
			add_image_size( 'ecptm_icon', 16, 16, true );
		}

	} // # function init()

	public function ecptm_admin_menu() {

		// add ecptm page to options menu
		add_menu_page( __('ECPT Maker', 'easy-custom-post-type-maker' ), __('Easy Post Types', 'easy-custom-post-type-maker' ), 'manage_options', 'edit.php?post_type=ecptm', '', 'dashicons-layout' );
		add_submenu_page( 'edit.php?post_type=ecptm', __('Taxonomies', 'easy-custom-post-type-maker' ), __('Taxonomies', 'easy-custom-post-type-maker' ), 'manage_options', 'edit.php?post_type=ecptm_tax' );

	} // # function ecptm_admin_menu()

	public function ecptm_styles( $ehook ) {

		// register overview style
		if ( $ehook == 'edit.php' && isset($_GET['post_type']) && ( $_GET['post_type'] == 'ecptm' || $_GET['post_type'] == 'ecptm_tax' ) ) {
			wp_register_style( 'cptm_admin_styles', $this->dir . '/css/overview.css' );
			wp_enqueue_style( 'cptm_admin_styles' );

			wp_register_script( 'cptm_admin_js', $this->dir . '/js/overview.js', 'jquery', '0.0.1', true );
			wp_enqueue_script( 'cptm_admin_js' );

			wp_enqueue_script( array( 'jquery', 'thickbox' ) );
			wp_enqueue_style( array( 'thickbox' ) );
		}

		// register add / edit style
		if ( ( $ehook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'ecptm' ) || ( $ehook == 'post.php' && isset($_GET['post']) && get_post_type( $_GET['post'] ) == 'ecptm' ) || ( $ehook == 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'ecptm_tax' ) || ( $ehook == 'post.php' && isset($_GET['post']) && get_post_type( $_GET['post'] ) == 'ecptm_tax' ) ) {
			wp_register_style( 'cptm_add_edit_styles', $this->dir . '/css/add-edit.css' );
			wp_enqueue_style( 'cptm_add_edit_styles' );

			wp_register_script( 'cptm_admin__add_edit_js', $this->dir . '/js/add-edit.js', 'jquery', '0.0.1', true );
			wp_enqueue_script( 'cptm_admin__add_edit_js' );

			wp_enqueue_media();
		}

	} // # function ecptm_styles()

	public function ecptm_create_custom_post_types() {

		// vars
		$ecptms = array();
		$ecptm_taxs = array();

		// query custom post types
		$get_ecptm = array(
			'numberposts' 	   => -1,
			'post_type' 	   => 'ecptm',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);
		$ecptm_post_types = get_posts( $get_ecptm );

		// create array of post meta
		if( $ecptm_post_types ) {
			foreach( $ecptm_post_types as $ecptm ) {
				$ecptm_meta = get_post_meta( $ecptm->ID, '', true );

				// text
				$ecptm_name                = ( array_key_exists( 'ecptm_name', $ecptm_meta ) && $ecptm_meta['ecptm_name'][0] ? esc_html( $ecptm_meta['ecptm_name'][0] ) : 'no_name' );
				$ecptm_label               = ( array_key_exists( 'ecptm_label', $ecptm_meta ) && $ecptm_meta['ecptm_label'][0] ? esc_html( $ecptm_meta['ecptm_label'][0] ) : $ecptm_name );
				$ecptm_singular_name       = ( array_key_exists( 'ecptm_singular_name', $ecptm_meta ) && $ecptm_meta['ecptm_singular_name'][0] ? esc_html( $ecptm_meta['ecptm_singular_name'][0] ) : $ecptm_label );
				$ecptm_description         = ( array_key_exists( 'ecptm_description', $ecptm_meta ) && $ecptm_meta['ecptm_description'][0] ? $ecptm_meta['ecptm_description'][0] : '' );

				// Custom post icon (uploaded)
				$ecptm_icon_url = ( array_key_exists( 'ecptm_icon_url', $ecptm_meta ) && $ecptm_meta['ecptm_icon_url'][0] ? $ecptm_meta['ecptm_icon_url'][0] : false );				
				
				// Custom post icon (dashicons)
				$ecptm_icon_slug = ( array_key_exists( 'ecptm_icon_slug', $ecptm_meta ) && $ecptm_meta['ecptm_icon_slug'][0] ? $ecptm_meta['ecptm_icon_slug'][0] : false );
				
				// If DashIcon is set ignore uploaded
				if ( !empty($ecptm_icon_slug) ) {
					$ecptm_icon_name = $ecptm_icon_slug;
				} else {
					$ecptm_icon_name = $ecptm_icon_url;
				}
				
				$ecptm_custom_rewrite_slug = ( array_key_exists( 'ecptm_custom_rewrite_slug', $ecptm_meta ) && $ecptm_meta['ecptm_custom_rewrite_slug'][0] ? esc_html( $ecptm_meta['ecptm_custom_rewrite_slug'][0] ) : $ecptm_name );
				$ecptm_menu_position       = ( array_key_exists( 'ecptm_menu_position', $ecptm_meta ) && $ecptm_meta['ecptm_menu_position'][0] ? (int) $ecptm_meta['ecptm_menu_position'][0] : null );

				// dropdown
				$ecptm_public              = ( array_key_exists( 'ecptm_public', $ecptm_meta ) && $ecptm_meta['ecptm_public'][0] == '1' ? true : false );
				$ecptm_show_ui             = ( array_key_exists( 'ecptm_show_ui', $ecptm_meta ) && $ecptm_meta['ecptm_show_ui'][0] == '1' ? true : false );
				$ecptm_has_archive         = ( array_key_exists( 'ecptm_has_archive', $ecptm_meta ) && $ecptm_meta['ecptm_has_archive'][0] == '1' ? true : false );
				$ecptm_exclude_from_search = ( array_key_exists( 'ecptm_exclude_from_search', $ecptm_meta ) && $ecptm_meta['ecptm_exclude_from_search'][0] == '1' ? true : false );
				$ecptm_capability_type     = ( array_key_exists( 'ecptm_capability_type', $ecptm_meta ) && $ecptm_meta['ecptm_capability_type'][0] ? $ecptm_meta['ecptm_capability_type'][0] : 'post' );
				$ecptm_hierarchical        = ( array_key_exists( 'ecptm_hierarchical', $ecptm_meta ) && $ecptm_meta['ecptm_hierarchical'][0] == '1' ? true : false );
				$ecptm_rewrite             = ( array_key_exists( 'ecptm_rewrite', $ecptm_meta ) && $ecptm_meta['ecptm_rewrite'][0] == '1' ? true : false );
				$ecptm_withfront           = ( array_key_exists( 'ecptm_withfront', $ecptm_meta ) && $ecptm_meta['ecptm_withfront'][0] == '1' ? true : false );
				$ecptm_feeds               = ( array_key_exists( 'ecptm_feeds', $ecptm_meta ) && $ecptm_meta['ecptm_feeds'][0] == '1' ? true : false );
				$ecptm_pages               = ( array_key_exists( 'ecptm_pages', $ecptm_meta ) && $ecptm_meta['ecptm_pages'][0] == '1' ? true : false );
				$ecptm_query_var           = ( array_key_exists( 'ecptm_query_var', $ecptm_meta ) && $ecptm_meta['ecptm_query_var'][0] == '1' ? true : false );
				$ecptm_show_in_menu        = ( array_key_exists( 'ecptm_show_in_menu', $ecptm_meta ) && $ecptm_meta['ecptm_show_in_menu'][0] == '1' ? true : false );

				// checkbox
				$ecptm_supports            = ( array_key_exists( 'ecptm_supports', $ecptm_meta ) && $ecptm_meta['ecptm_supports'][0] ? $ecptm_meta['ecptm_supports'][0] : 'a:2:{i:0;s:5:"title";i:1;s:6:"editor";}' );
				$ecptm_builtin_taxonomies  = ( array_key_exists( 'ecptm_builtin_taxonomies', $ecptm_meta ) && $ecptm_meta['ecptm_builtin_taxonomies'][0] ? $ecptm_meta['ecptm_builtin_taxonomies'][0] : 'a:0:{}' );

				$ecptm_rewrite_options     = array();
				if ( $ecptm_rewrite )      { $ecptm_rewrite_options['slug'] = _x( $ecptm_custom_rewrite_slug, 'URL Slug', 'easy-custom-post-type-maker' ); }
				if ( $ecptm_withfront )    { $ecptm_rewrite_options['with_front'] = $ecptm_withfront; }
				if ( $ecptm_feeds )        { $ecptm_rewrite_options['feeds'] = $ecptm_feeds; }
				if ( $ecptm_pages )        { $ecptm_rewrite_options['pages'] = $ecptm_pages; }

				$ecptms[] = array(
					'ecptm_id'                 => $ecptm->ID,
					'ecptm_name'               => $ecptm_name,
					'ecptm_label'              => $ecptm_label,
					'ecptm_singular_name'      => $ecptm_singular_name,
					'ecptm_description'        => $ecptm_description,
					'ecptm_icon_name'          => $ecptm_icon_name,
					'ecptm_custom_rewrite_slug'=> $ecptm_custom_rewrite_slug,
					'ecptm_menu_position'       => $ecptm_menu_position,
					'ecptm_public'              => (bool) $ecptm_public,
					'ecptm_show_ui'             => (bool) $ecptm_show_ui,
					'ecptm_has_archive'         => (bool) $ecptm_has_archive,
					'ecptm_exclude_from_search' => (bool) $ecptm_exclude_from_search,
					'ecptm_capability_type'     => $ecptm_capability_type,
					'ecptm_hierarchical'        => (bool) $ecptm_hierarchical,
					'ecptm_rewrite'             => $ecptm_rewrite_options,
					'ecptm_query_var'           => (bool) $ecptm_query_var,
					'ecptm_show_in_menu'        => (bool) $ecptm_show_in_menu,
					'ecptm_supports'            => unserialize( $ecptm_supports ),
					'ecptm_builtin_taxonomies'  => unserialize( $ecptm_builtin_taxonomies ),
				);

				// register custom post types
				if ( is_array( $ecptms ) ) {
					foreach ($ecptms as $ecptm_post_type) {

						$labels = array(
							'name'                => __( $ecptm_post_type['ecptm_label'], 'easy-custom-post-type-maker' ),
							'singular_name'       => __( $ecptm_post_type['ecptm_singular_name'], 'easy-custom-post-type-maker' ),
							'add_new'             => __( 'Add New' , 'easy-custom-post-type-maker' ),
							'add_new_item'        => __( 'Add New ' . $ecptm_post_type['ecptm_singular_name'] , 'easy-custom-post-type-maker' ),
							'edit_item'           => __( 'Edit ' . $ecptm_post_type['ecptm_singular_name'] , 'easy-custom-post-type-maker' ),
							'new_item'            => __( 'New ' . $ecptm_post_type['ecptm_singular_name'] , 'easy-custom-post-type-maker' ),
							'view_item'           => __( 'View ' . $ecptm_post_type['ecptm_singular_name'], 'easy-custom-post-type-maker' ),
							'search_items'        => __( 'Search ' . $ecptm_post_type['ecptm_label'], 'easy-custom-post-type-maker' ),
							'not_found'           => __( 'No ' .  $ecptm_post_type['ecptm_label'] . ' found', 'easy-custom-post-type-maker' ),
							'not_found_in_trash'  => __( 'No ' .  $ecptm_post_type['ecptm_label'] . ' found in Trash', 'easy-custom-post-type-maker' ),
						);

						$args = array(
							'labels'              => $labels,
							'description'         => $ecptm_post_type['ecptm_description'],
							'menu_icon'           => $ecptm_post_type['ecptm_icon_name'],
							'rewrite'             => $ecptm_post_type['ecptm_rewrite'],
							'menu_position'       => $ecptm_post_type['ecptm_menu_position'],
							'public'              => $ecptm_post_type['ecptm_public'],
							'show_ui'             => $ecptm_post_type['ecptm_show_ui'],
							'has_archive'         => $ecptm_post_type['ecptm_has_archive'],
							'exclude_from_search' => $ecptm_post_type['ecptm_exclude_from_search'],
							'capability_type'     => $ecptm_post_type['ecptm_capability_type'],
							'hierarchical'        => $ecptm_post_type['ecptm_hierarchical'],
							'show_in_menu'        => $ecptm_post_type['ecptm_show_in_menu'],
							'query_var'           => $ecptm_post_type['ecptm_query_var'],
							'publicly_queryable'  => true,
							'_builtin'            => false,
							'supports'            => $ecptm_post_type['ecptm_supports'],
							'taxonomies'          => $ecptm_post_type['ecptm_builtin_taxonomies']
						);

						if( $ecptm_post_type['ecptm_name'] != 'no_name' )
							register_post_type( $ecptm_post_type['ecptm_name'], $args);
					}
				}
			}
		}

		// query easy custom taxonomies
		$eget_cptm_tax = array(
			'numberposts' 	   => -1,
			'post_type' 	   => 'ecptm_tax',
			'post_status'      => 'publish',
			'suppress_filters' => false,
		);
		$ecptm_taxonomies = get_posts( $eget_cptm_tax );

		// create array of post meta
		if( $ecptm_taxonomies ) {
			foreach( $ecptm_taxonomies as $ecptm_tax ) {
				$ecptm_meta = get_post_meta( $ecptm_tax->ID, '', true );

				// text
				$ecptm_tax_name                = ( array_key_exists( 'ecptm_tax_name', $ecptm_meta ) && $ecptm_meta['ecptm_tax_name'][0] ? esc_html( $ecptm_meta['ecptm_tax_name'][0] ) : 'no_name' );
				$ecptm_tax_label               = ( array_key_exists( 'ecptm_tax_label', $ecptm_meta ) && $ecptm_meta['ecptm_tax_label'][0] ? esc_html( $ecptm_meta['ecptm_tax_label'][0] ) : $ecptm_tax_name );
				$ecptm_tax_singular_name       = ( array_key_exists( 'ecptm_tax_singular_name', $ecptm_meta ) && $ecptm_meta['ecptm_tax_singular_name'][0] ? esc_html( $ecptm_meta['ecptm_tax_singular_name'][0] ) : $ecptm_tax_label );
				$ecptm_tax_custom_rewrite_slug = ( array_key_exists( 'ecptm_tax_custom_rewrite_slug', $ecptm_meta ) && $ecptm_meta['ecptm_tax_custom_rewrite_slug'][0] ? esc_html( $ecptm_meta['ecptm_tax_custom_rewrite_slug'][0] ) : $ecptm_tax_name );

				// dropdown
				$ecptm_tax_show_ui             = ( array_key_exists( 'ecptm_tax_show_ui', $ecptm_meta ) && $ecptm_meta['ecptm_tax_show_ui'][0] == '1' ? true : false );
				$ecptm_tax_hierarchical        = ( array_key_exists( 'ecptm_tax_hierarchical', $ecptm_meta ) && $ecptm_meta['ecptm_tax_hierarchical'][0] == '1' ? true : false );
				$ecptm_tax_rewrite             = ( array_key_exists( 'ecptm_tax_rewrite', $ecptm_meta ) && $ecptm_meta['ecptm_tax_rewrite'][0] == '1' ? array( 'slug' => _x( $ecptm_tax_custom_rewrite_slug, 'URL Slug', 'easy-custom-post-type-maker' ) ) : false );
				$ecptm_tax_query_var           = ( array_key_exists( 'ecptm_tax_query_var', $ecptm_meta ) && $ecptm_meta['ecptm_tax_query_var'][0] == '1' ? true : false );

				// checkbox
				$ecptm_tax_post_types          = ( array_key_exists( 'ecptm_tax_post_types', $ecptm_meta ) && $ecptm_meta['ecptm_tax_post_types'][0] ? $ecptm_meta['ecptm_tax_post_types'][0] : 'a:0:{}' );

				$ecptm_taxs[] = array(
					'ecptm_tax_id'                  => $ecptm_tax->ID,
					'ecptm_tax_name'                => $ecptm_tax_name,
					'ecptm_tax_label'               => $ecptm_tax_label,
					'ecptm_tax_singular_name'       => $ecptm_tax_singular_name,
					'ecptm_tax_custom_rewrite_slug' => $ecptm_tax_custom_rewrite_slug,
					'ecptm_tax_show_ui'             => (bool) $ecptm_tax_show_ui,
					'ecptm_tax_hierarchical'        => (bool) $ecptm_tax_hierarchical,
					'ecptm_tax_rewrite'             => $ecptm_tax_rewrite,
					'ecptm_tax_query_var'           => (bool) $ecptm_tax_query_var,
					'ecptm_tax_builtin_taxonomies'  => unserialize( $ecptm_tax_post_types ),
				);

				// register custom post types
				if ( is_array( $ecptm_taxs ) ) {
					foreach ($ecptm_taxs as $ecptm_taxonomy) {

						$elabels = array(
							'name'                       => _x( $ecptm_taxonomy['ecptm_tax_label'], 'taxonomy general name', 'easy-custom-post-type-maker' ),
							'singular_name'              => _x( $ecptm_taxonomy['ecptm_tax_singular_name'], 'taxonomy singular name' ),
							'search_items'               => __( 'Search ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' ),
							'popular_items'              => __( 'Popular ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' ),
							'all_items'                  => __( 'All ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' ),
							'parent_item'                => __( 'Parent ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' ),
							'parent_item_colon'          => __( 'Parent ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' . ':' ),
							'edit_item'                  => __( 'Edit ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' ),
							'update_item'                => __( 'Update ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' ),
							'add_new_item'               => __( 'Add New ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' ),
							'new_item_name'              => __( 'New ' . $ecptm_taxonomy['ecptm_tax_singular_name'], 'easy-custom-post-type-maker' . ' Name' ),
							'separate_items_with_commas' => __( 'Seperate ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' . ' with commas' ),
							'add_or_remove_items'        => __( 'Add or remove ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' ),
							'choose_from_most_used'      => __( 'Choose from the most used ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' ),
							'menu_name'                  => __( 'All ' . $ecptm_taxonomy['ecptm_tax_label'], 'easy-custom-post-type-maker' )
						);

						$args = array(
							'label'               => $ecptm_taxonomy['ecptm_tax_label'],
							'labels'              => $elabels,
							'rewrite'             => $ecptm_taxonomy['ecptm_tax_rewrite'],
							'show_ui'             => $ecptm_taxonomy['ecptm_tax_show_ui'],
							'hierarchical'        => $ecptm_taxonomy['ecptm_tax_hierarchical'],
							'query_var'           => $ecptm_taxonomy['ecptm_tax_query_var'],
						);

						if( $ecptm_taxonomy['ecptm_tax_name'] != 'no_name' )
							register_taxonomy( $ecptm_taxonomy['ecptm_tax_name'], $ecptm_taxonomy['ecptm_tax_builtin_taxonomies'], $args );
					}
				}
			}
		}

		// flush permalink structure
		// global $wp_rewrite;
		// $wp_rewrite->flush_rules();

	} // # function ecptm_create_custom_post_types()

	public function ecptm_create_meta_boxes() {

		// add options meta box
		add_meta_box(
			'cptm_options',
			__( 'Options', 'easy-custom-post-type-maker' ),
			array($this, 'cptm_meta_box'),
			'ecptm',
			'advanced',
			'high'
		);
		add_meta_box(
			'cptm_tax_options',
			__( 'Options', 'easy-custom-post-type-maker' ),
			array($this, 'cptm_tax_meta_box'),
			'ecptm_tax',
			'advanced',
			'high'
		);

	} // # function ecptm_create_meta_boxes()

	public function cptm_meta_box( $post ) {

		// get post meta values
		$values = get_post_custom( $post->ID );

		// text fields
		$ecptm_name                          = isset( $values['ecptm_name'] ) ? sanitize_text_field( $values['ecptm_name'][0] ) : '';
		$ecptm_label                         = isset( $values['ecptm_label'] ) ? sanitize_text_field( $values['ecptm_label'][0] ) : '';
		$ecptm_singular_name                 = isset( $values['ecptm_singular_name'] ) ? sanitize_text_field( $values['ecptm_singular_name'][0] ) : '';
		$ecptm_description                   = isset( $values['ecptm_description'] ) ? wp_kses_post( $values['ecptm_description'][0] ) : '';


		// Custom post icon (uploaded)
		$ecptm_icon_url                      = isset( $values['ecptm_icon_url'] ) ? sanitize_text_field( $values['ecptm_icon_url'][0] ) : '';
		
		// Custom post icon (dashicons)
		$ecptm_icon_slug                     = isset( $values['ecptm_icon_slug'] ) ? sanitize_text_field( $values['ecptm_icon_slug'][0] ) : ''; 
		
		// If DashIcon is set ignore uploaded
		if ( !empty($ecptm_icon_slug) ) {
			$ecptm_icon_name = $ecptm_icon_slug;
		} else {
			$ecptm_icon_name = $ecptm_icon_url;
		}
		
		
		$ecptm_custom_rewrite_slug           = isset( $values['ecptm_custom_rewrite_slug'] ) ? sanitize_text_field( $values['ecptm_custom_rewrite_slug'][0] ) : '';
		$ecptm_menu_position                 = isset( $values['ecptm_menu_position'] ) ? sanitize_text_field( $values['ecptm_menu_position'][0] ) : '';

		// select fields
		$ecptm_public                        = isset( $values['ecptm_public'] ) ? sanitize_text_field( $values['ecptm_public'][0] ) : '';
		$ecptm_show_ui                       = isset( $values['ecptm_show_ui'] ) ? sanitize_text_field( $values['ecptm_show_ui'][0] ) : '';
		$ecptm_has_archive                   = isset( $values['ecptm_has_archive'] ) ? sanitize_text_field( $values['ecptm_has_archive'][0] ) : '';
		$ecptm_exclude_from_search           = isset( $values['ecptm_exclude_from_search'] ) ? sanitize_text_field( $values['ecptm_exclude_from_search'][0] ) : '';
		$ecptm_capability_type               = isset( $values['ecptm_capability_type'] ) ? sanitize_text_field( $values['ecptm_capability_type'][0] ) : '';
		$ecptm_hierarchical                  = isset( $values['ecptm_hierarchical'] ) ? sanitize_text_field( $values['ecptm_hierarchical'][0] ) : '';
		$ecptm_rewrite                       = isset( $values['ecptm_rewrite'] ) ? sanitize_text_field( $values['ecptm_rewrite'][0] ) : '';
		$ecptm_withfront                     = isset( $values['ecptm_withfront'] ) ? sanitize_text_field( $values['ecptm_withfront'][0] ) : '';
		$ecptm_feeds                         = isset( $values['ecptm_feeds'] ) ? sanitize_text_field( $values['ecptm_feeds'][0] ) : '';
		$ecptm_pages                         = isset( $values['ecptm_pages'] ) ? sanitize_text_field( $values['ecptm_pages'][0] ) : '';
		$ecptm_query_var                     = isset( $values['ecptm_query_var'] ) ? sanitize_text_field( $values['ecptm_query_var'][0] ) : '';
		$ecptm_show_in_menu                  = isset( $values['ecptm_show_in_menu'] ) ? sanitize_text_field( $values['ecptm_show_in_menu'][0] ) : '';

		// checkbox fields
		$ecptm_supports                      = isset( $values['ecptm_supports'] ) ? unserialize( $values['ecptm_supports'][0] ) : array();
		$cptm_supports_title                = ( isset( $values['ecptm_supports'] ) && in_array( 'title', $ecptm_supports ) ? 'title' : '' );
		$cptm_supports_editor               = ( isset( $values['ecptm_supports'] ) && in_array( 'editor', $ecptm_supports ) ? 'editor' : '' );
		$cptm_supports_excerpt              = ( isset( $values['ecptm_supports'] ) && in_array( 'excerpt', $ecptm_supports ) ? 'excerpt' : '' );
		$cptm_supports_trackbacks           = ( isset( $values['ecptm_supports'] ) && in_array( 'trackbacks', $ecptm_supports ) ? 'trackbacks' : '' );
		$cptm_supports_custom_fields        = ( isset( $values['ecptm_supports'] ) && in_array( 'custom-fields', $ecptm_supports ) ? 'custom-fields' : '' );
		$cptm_supports_comments             = ( isset( $values['ecptm_supports'] ) && in_array( 'comments', $ecptm_supports ) ? 'comments' : '' );
		$cptm_supports_revisions            = ( isset( $values['ecptm_supports'] ) && in_array( 'revisions', $ecptm_supports ) ? 'revisions' : '' );
		$cptm_supports_featured_image       = ( isset( $values['ecptm_supports'] ) && in_array( 'thumbnail', $ecptm_supports ) ? 'thumbnail' : '' );
		$cptm_supports_author               = ( isset( $values['ecptm_supports'] ) && in_array( 'author', $ecptm_supports ) ? 'author' : '' );
		$cptm_supports_page_attributes      = ( isset( $values['ecptm_supports'] ) && in_array( 'page-attributes', $ecptm_supports ) ? 'page-attributes' : '' );
		$cptm_supports_post_formats         = ( isset( $values['ecptm_supports'] ) && in_array( 'post-formats', $ecptm_supports ) ? 'post-formats' : '' );

		$ecptm_builtin_taxonomies            = isset( $values['ecptm_builtin_taxonomies'] ) ? unserialize( $values['ecptm_builtin_taxonomies'][0] ) : array();
		$cptm_builtin_taxonomies_categories = ( isset( $values['ecptm_builtin_taxonomies'] ) && in_array( 'category', $ecptm_builtin_taxonomies ) ? 'category' : '' );
		$cptm_builtin_taxonomies_tags       = ( isset( $values['ecptm_builtin_taxonomies'] ) && in_array( 'post_tag', $ecptm_builtin_taxonomies ) ? 'post_tag' : '' );

		// nonce
		wp_nonce_field( 'cptm_meta_box_nonce_action', 'cptm_meta_box_nonce_field' );

		// set defaults if new Easy Custom Post Type is being created
		global $pagenow;
		$cptm_supports_title                = $pagenow === 'post-new.php' ? 'title' : $cptm_supports_title;
		$cptm_supports_editor               = $pagenow === 'post-new.php' ? 'editor' : $cptm_supports_editor;
		$cptm_supports_excerpt              = $pagenow === 'post-new.php' ? 'excerpt' : $cptm_supports_excerpt;
		?>
		<table class="cptm">
			<tr>
				<td class="label">
					<label for="ecptm_name"><span class="required">*</span> <?php _e( 'Easy Custom Post Type Name', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'The post type name. Used to retrieve easy custom post type content. Must be all in lower-case and without any spaces.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. movies', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_name" id="ecptm_name" class="widefat" tabindex="1" value="<?php echo $ecptm_name; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_label"><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'A plural descriptive name for the post type.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. Movies', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_label" id="ecptm_label" class="widefat" tabindex="2" value="<?php echo $ecptm_label; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_singular_name"><?php _e( 'Singular Name', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'A singular descriptive name for the post type.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. Movie', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_singular_name" id="ecptm_singular_name" class="widefat" tabindex="3" value="<?php echo $ecptm_singular_name; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label top">
					<label for="e"><?php _e( 'Description', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'A short descriptive summary of what the post type is.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<textarea name="ecptm_description" id="ecptm_description" class="widefat" tabindex="4" rows="4"><?php echo $ecptm_description; ?></textarea>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Visibility', 'easy-custom-post-type-maker' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_public"><?php _e( 'Public', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether a post type is intended to be used publicly either via the admin interface or by front-end users.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_public" id="ecptm_public" tabindex="5">
						<option value="1" <?php selected( $ecptm_public, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_public, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Rewrite Options', 'easy-custom-post-type-maker' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_rewrite"><?php _e( 'Rewrite', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Triggers the handling of rewrites for this post type.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_rewrite" id="ecptm_rewrite" tabindex="6">
						<option value="1" <?php selected( $ecptm_rewrite, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_rewrite, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_withfront"><?php _e( 'With Front', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Should the permastruct be prepended with the front base.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_withfront" id="ecptm_withfront" tabindex="7">
						<option value="1" <?php selected( $ecptm_withfront, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_withfront, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_custom_rewrite_slug"><?php _e( 'Custom Rewrite Slug', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Customize the permastruct slug.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'Default: [Easy Custom Post Type Name]', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_custom_rewrite_slug" id="ecptm_custom_rewrite_slug" class="widefat" tabindex="8" value="<?php echo $ecptm_custom_rewrite_slug; ?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Front-end Options', 'easy-custom-post-type-maker' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_feeds"><?php _e( 'Feeds', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Should a feed permastruct be built for this post type. Defaults to "has_archive" value.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_feeds" id="ecptm_feeds" tabindex="9">
						<option value="0" <?php selected( $ecptm_feeds, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="1" <?php selected( $ecptm_feeds, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_pages"><?php _e( 'Pages', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Should the permastruct provide for pagination.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_pages" id="ecptm_pages" tabindex="10">
						<option value="1" <?php selected( $ecptm_pages, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_pages, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_exclude_from_search"><?php _e( 'Exclude From Search', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether to exclude posts with this post type from front end search results.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_exclude_from_search" id="ecptm_exclude_from_search" tabindex="11">
						<option value="0" <?php selected( $ecptm_exclude_from_search, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="1" <?php selected( $ecptm_exclude_from_search, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_has_archive"><?php _e( 'Has Archive', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Enables post type archives.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_has_archive" id="ecptm_has_archive" tabindex="12">
						<option value="0" <?php selected( $ecptm_has_archive, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="1" <?php selected( $ecptm_has_archive, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Admin Menu Options', 'easy-custom-post-type-maker' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_show_ui"><?php _e( 'Show UI', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether to generate a default UI for managing this post type in the admin.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_show_ui" id="ecptm_show_ui" tabindex="13">
						<option value="1" <?php selected( $ecptm_show_ui, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_show_ui, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_menu_position"><?php _e( 'Menu Position', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'The position in the menu order the post type should appear. "Show in Menu" must be true.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_menu_position" id="ecptm_menu_position" class="widefat" tabindex="14" value="<?php echo $ecptm_menu_position; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_show_in_menu"><?php _e( 'Show in Menu', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Where to show the post type in the admin menu. "Show UI" must be true.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_show_in_menu" id="ecptm_show_in_menu" tabindex="15">
						<option value="1" <?php selected( $ecptm_show_in_menu, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_show_in_menu, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="current-cptm-icon"><?php _e( 'Icon', 'easy-custom-post-type-maker' ); ?></label>
                    <p><?php _e( 'This icon will be overriden if a Dash Icon is specified in the field below.', 'easy-custom-post-type-maker' ); ?></p>
                </td>
				<td>
					<div class="cptm-icon">
						<div class="current-cptm-icon"><?php if ( $ecptm_icon_url ) { ?><img src="<?php echo $ecptm_icon_url; ?>" /><?php } ?></div>
						<a href="/" class="remove-cptm-icon button-secondary"<?php if ( ! $ecptm_icon_url ) { ?> style="display: none;"<?php } ?>>Remove icon</a>
						<a  href="/"class="media-uploader-button button-primary" data-post-id="<?php echo $post->ID; ?>"><?php if ( ! $ecptm_icon_url ) { ?><?php _e( 'Add icon', 'easy-custom-post-type-maker' ); ?><?php } else { ?><?php _e( 'Edit icon', 'easy-custom-post-type-maker' ); ?><?php } ?></a>
					</div>
					<input type="hidden" name="ecptm_icon_url" id="ecptm_icon_url" class="widefat" value="<?php echo $ecptm_icon_url; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_icon_slug"><?php _e( 'Slug Icon', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'This uses (<a href="https://developer.wordpress.org/resource/dashicons/">Dash Icons</a>) and <strong>overrides</strong> the uploaded icon above.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<?php if ( $ecptm_icon_slug ) { ?><span id="cptm_icon_slug_before" class="dashicons-before <?php echo $ecptm_icon_slug; ?>"><?php } ?></span>
					<input type="text" name="ecptm_icon_slug" id="ecptm_icon_slug" class="widefat" tabindex="15" value="<?php echo $ecptm_icon_slug; ?>" />
				</td>
			</tr>
			<tr>
				<td colspan="2" class="section">
					<h3><?php _e( 'Wordpress Integration', 'easy-custom-post-type-maker' ); ?></h3>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_capability_type"><?php _e( 'Capability Type', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'The post type to use to build the read, edit, and delete capabilities.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_capability_type" id="ecptm_capability_type" tabindex="16">
						<option value="post" <?php selected( $ecptm_capability_type, 'post' ); ?>><?php _e( 'Post', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="page" <?php selected( $ecptm_capability_type, 'page' ); ?>><?php _e( 'Page', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_hierarchical"><?php _e( 'Hierarchical', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether the post type is hierarchical (e.g. page).', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_hierarchical" id="ecptm_hierarchical" tabindex="17">
						<option value="0" <?php selected( $ecptm_hierarchical, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="1" <?php selected( $ecptm_hierarchical, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_query_var"><?php _e( 'Query Var', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Sets the query_var key for this post type.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_query_var" id="ecptm_query_var" tabindex="18">
						<option value="1" <?php selected( $ecptm_query_var, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_query_var, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label top">
					<label for="ecptm_supports"><?php _e( 'Supports', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Adds the respective meta boxes when creating content for this Easy Custom Post Type.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="checkbox" tabindex="19" name="ecptm_supports[]" id="cptm_supports_title" value="title" <?php checked( $cptm_supports_title, 'title' ); ?> /> <label for="cptm_supports_title"><?php _e( 'Title', 'easy-custom-post-type-maker' ); ?> <span class="default">(<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</span></label><br />
					<input type="checkbox" tabindex="20" name="ecptm_supports[]" id="cptm_supports_editor" value="editor" <?php checked( $cptm_supports_editor, 'editor' ); ?> /> <label for="cptm_supports_editor"><?php _e( 'Editor', 'easy-custom-post-type-maker' ); ?> <span class="default">(<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</span></label><br />
					<input type="checkbox" tabindex="21" name="ecptm_supports[]" id="cptm_supports_excerpt" value="excerpt" <?php checked( $cptm_supports_excerpt, 'excerpt' ); ?> /> <label for="cptm_supports_excerpt"><?php _e( 'Excerpt', 'easy-custom-post-type-maker' ); ?> <span class="default">(<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</span></label><br />
					<input type="checkbox" tabindex="22" name="ecptm_supports[]" id="cptm_supports_trackbacks" value="trackbacks" <?php checked( $cptm_supports_trackbacks, 'trackbacks' ); ?> /> <label for="cptm_supports_trackbacks"><?php _e( 'Trackbacks', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="23" name="ecptm_supports[]" id="cptm_supports_custom_fields" value="custom-fields" <?php checked( $cptm_supports_custom_fields, 'custom-fields' ); ?> /> <label for="cptm_supports_custom_fields"><?php _e( 'Custom Fields', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="24" name="ecptm_supports[]" id="cptm_supports_comments" value="comments" <?php checked( $cptm_supports_comments, 'comments' ); ?> /> <label for="cptm_supports_comments"><?php _e( 'Comments', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="25" name="ecptm_supports[]" id="cptm_supports_revisions" value="revisions" <?php checked( $cptm_supports_revisions, 'revisions' ); ?> /> <label for="cptm_supports_revisions"><?php _e( 'Revisions', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="26" name="ecptm_supports[]" id="cptm_supports_featured_image" value="thumbnail" <?php checked( $cptm_supports_featured_image, 'thumbnail' ); ?> /> <label for="cptm_supports_featured_image"><?php _e( 'Featured Image', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="27" name="ecptm_supports[]" id="cptm_supports_author" value="author" <?php checked( $cptm_supports_author, 'author' ); ?> /> <label for="cptm_supports_author"><?php _e( 'Author', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="28" name="ecptm_supports[]" id="cptm_supports_page_attributes" value="page-attributes" <?php checked( $cptm_supports_page_attributes, 'page-attributes' ); ?> /> <label for="cptm_supports_page_attributes"><?php _e( 'Page Attributes', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="29" name="ecptm_supports[]" id="cptm_supports_post_formats" value="post-formats" <?php checked( $cptm_supports_post_formats, 'post-formats' ); ?> /> <label for="cptm_supports_post_formats"><?php _e( 'Post Formats', 'easy-custom-post-type-maker' ); ?></label><br />
				</td>
			</tr>
			<tr>
				<td class="label top">
					<label for="ecptm_builtin_taxonomies"><?php _e( 'Built-in Taxonomies', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( '', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="checkbox" tabindex="30" name="ecptm_builtin_taxonomies[]" id="cptm_builtin_taxonomies_categories" value="category" <?php checked( $cptm_builtin_taxonomies_categories, 'category' ); ?> /> <label for="cptm_builtin_taxonomies_categories"><?php _e( 'Categories', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="31" name="ecptm_builtin_taxonomies[]" id="cptm_builtin_taxonomies_tags" value="post_tag" <?php checked( $cptm_builtin_taxonomies_tags, 'post_tag' ); ?> /> <label for="cptm_builtin_taxonomies_tags"><?php _e( 'Tags', 'easy-custom-post-type-maker' ); ?></label><br />
				</td>
			</tr>
		</table>

		<?php

	} // # function cptm_meta_box()

	public function cptm_tax_meta_box( $post ) {

		// get post meta values
		$values = get_post_custom( $post->ID );

		// text fields
		$ecptm_tax_name                          = isset( $values['ecptm_tax_name'] ) ? sanitize_text_field( $values['ecptm_tax_name'][0] ) : '';
		$ecptm_tax_label                         = isset( $values['ecptm_tax_label'] ) ? sanitize_text_field( $values['ecptm_tax_label'][0] ) : '';
		$ecptm_tax_singular_name                 = isset( $values['ecptm_tax_singular_name'] ) ? sanitize_text_field( $values['ecptm_tax_singular_name'][0] ) : '';
		$ecptm_tax_custom_rewrite_slug           = isset( $values['ecptm_tax_custom_rewrite_slug'] ) ? sanitize_text_field( $values['ecptm_tax_custom_rewrite_slug'][0] ) : '';

		// select fields
		$ecptm_tax_show_ui                       = isset( $values['ecptm_tax_show_ui'] ) ? sanitize_text_field( $values['ecptm_tax_show_ui'][0] ) : '';
		$ecptm_tax_hierarchical                  = isset( $values['ecptm_tax_hierarchical'] ) ? sanitize_text_field( $values['ecptm_tax_hierarchical'][0] ) : '';
		$ecptm_tax_rewrite                       = isset( $values['ecptm_tax_rewrite'] ) ? sanitize_text_field( $values['ecptm_tax_rewrite'][0] ) : '';
		$ecptm_tax_query_var                     = isset( $values['ecptm_tax_query_var'] ) ? sanitize_text_field( $values['ecptm_tax_query_var'][0] ) : '';

		// checkbox fields
		$cptm_tax_supports                      = isset( $values['cptm_tax_supports'] ) ? unserialize( $values['cptm_tax_supports'][0] ) : array();
		$cptm_tax_supports_title                = ( isset( $values['cptm_tax_supports'] ) && in_array( 'title', $ecptm_supports ) ? 'title' : '' );
		$cptm_tax_supports_editor               = ( isset( $values['cptm_tax_supports'] ) && in_array( 'editor', $ecptm_supports ) ? 'editor' : '' );
		$cptm_tax_supports_excerpt              = ( isset( $values['cptm_tax_supports'] ) && in_array( 'excerpt', $ecptm_supports ) ? 'excerpt' : '' );
		$cptm_tax_supports_trackbacks           = ( isset( $values['cptm_tax_supports'] ) && in_array( 'trackbacks', $ecptm_supports ) ? 'trackbacks' : '' );
		$cptm_tax_supports_custom_fields        = ( isset( $values['cptm_tax_supports'] ) && in_array( 'custom-fields', $ecptm_supports ) ? 'custom-fields' : '' );
		$cptm_tax_supports_comments             = ( isset( $values['cptm_tax_supports'] ) && in_array( 'comments', $ecptm_supports ) ? 'comments' : '' );
		$cptm_tax_supports_revisions            = ( isset( $values['cptm_tax_supports'] ) && in_array( 'revisions', $ecptm_supports ) ? 'revisions' : '' );
		$cptm_tax_supports_featured_image       = ( isset( $values['cptm_tax_supports'] ) && in_array( 'thumbnail', $ecptm_supports ) ? 'thumbnail' : '' );
		$cptm_tax_supports_author               = ( isset( $values['cptm_tax_supports'] ) && in_array( 'author', $ecptm_supports ) ? 'author' : '' );
		$cptm_tax_supports_page_attributes      = ( isset( $values['cptm_tax_supports'] ) && in_array( 'page-attributes', $ecptm_supports ) ? 'page-attributes' : '' );
		$cptm_tax_supports_post_formats         = ( isset( $values['cptm_tax_supports'] ) && in_array( 'post-formats', $ecptm_supports ) ? 'post-formats' : '' );

		$ecptm_tax_post_types                    = isset( $values['ecptm_tax_post_types'] ) ? unserialize( $values['ecptm_tax_post_types'][0] ) : array();
		$cptm_tax_post_types_post               = ( isset( $values['ecptm_tax_post_types'] ) && in_array( 'post', $ecptm_tax_post_types ) ? 'post' : '' );
		$cptm_tax_post_types_page               = ( isset( $values['ecptm_tax_post_types'] ) && in_array( 'page', $ecptm_tax_post_types ) ? 'page' : '' );

		// nonce
		wp_nonce_field( 'cptm_meta_box_nonce_action', 'cptm_meta_box_nonce_field' );
		?>
		<table class="cptm">
			<tr>
				<td class="label">
					<label for="ecptm_tax_name"><span class="required">*</span> <?php _e( 'Custom Taxonomy Name', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'The taxonomy name. Used to retrieve custom taxonomy content.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. movies', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_tax_name" id="ecptm_tax_name" class="widefat" tabindex="1" value="<?php echo $ecptm_tax_name; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_label"><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'A plural descriptive name for the taxonomy.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. Movies', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_tax_label" id="ecptm_tax_label" class="widefat" tabindex="2" value="<?php echo $ecptm_tax_label; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_singular_name"><?php _e( 'Singular Name', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'A singular descriptive name for the taxonomy.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'e.g. Movie', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_tax_singular_name" id="ecptm_tax_singular_name" class="widefat" tabindex="3" value="<?php echo $ecptm_tax_singular_name; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_show_ui"><?php _e( 'Show UI', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether to generate a default UI for managing this taxonomy in the admin.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_tax_show_ui" id="ecptm_tax_show_ui" tabindex="4">
						<option value="1" <?php selected( $ecptm_tax_show_ui, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_tax_show_ui, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_hierarchical"><?php _e( 'Hierarchical', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Whether the taxonomy is hierarchical (e.g. page).', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_tax_hierarchical" id="ecptm_tax_hierarchical" tabindex="5">
						<option value="0" <?php selected( $ecptm_tax_hierarchical, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="1" <?php selected( $ecptm_tax_hierarchical, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_rewrite"><?php _e( 'Rewrite', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Triggers the handling of rewrites for this taxonomy.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_tax_rewrite" id="ecptm_tax_rewrite" tabindex="6">
						<option value="1" <?php selected( $ecptm_tax_rewrite, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_tax_rewrite, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_custom_rewrite_slug"><?php _e( 'Custom Rewrite Slug', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Customize the permastruct slug.', 'easy-custom-post-type-maker' ); ?></p>
					<p><?php _e( 'Default: [Custom Taxonomy Name]', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="text" name="ecptm_tax_custom_rewrite_slug" id="ecptm_tax_custom_rewrite_slug" class="widefat" tabindex="7" value="<?php echo $ecptm_tax_custom_rewrite_slug; ?>" />
				</td>
			</tr>
			<tr>
				<td class="label">
					<label for="ecptm_tax_query_var"><?php _e( 'Query Var', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( 'Sets the query_var key for this taxonomy.', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<select name="ecptm_tax_query_var" id="ecptm_tax_query_var" tabindex="8">
						<option value="1" <?php selected( $ecptm_tax_query_var, '1' ); ?>><?php _e( 'True', 'easy-custom-post-type-maker' ); ?> (<?php _e( 'default', 'easy-custom-post-type-maker' ); ?>)</option>
						<option value="0" <?php selected( $ecptm_tax_query_var, '0' ); ?>><?php _e( 'False', 'easy-custom-post-type-maker' ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="label top">
					<label for="ecptm_tax_post_types"><?php _e( 'Post Types', 'easy-custom-post-type-maker' ); ?></label>
					<p><?php _e( '', 'easy-custom-post-type-maker' ); ?></p>
				</td>
				<td>
					<input type="checkbox" tabindex="9" name="ecptm_tax_post_types[]" id="cptm_tax_post_types_post" value="post" <?php checked( $cptm_tax_post_types_post, 'post' ); ?> /> <label for="cptm_tax_post_types_post"><?php _e( 'Posts', 'easy-custom-post-type-maker' ); ?></label><br />
					<input type="checkbox" tabindex="10" name="ecptm_tax_post_types[]" id="cptm_tax_post_types_page" value="page" <?php checked( $cptm_tax_post_types_page, 'page' ); ?> /> <label for="cptm_tax_post_types_page"><?php _e( 'Pages', 'easy-custom-post-type-maker' ); ?></label><br />
					<?php
						$post_types = get_post_types( array( 'public' => true, '_builtin' => false ) );
						$i = 10;
						foreach ( $post_types as $post_type ) {
							$checked = in_array( $post_type, $ecptm_tax_post_types )  ? 'checked="checked"' : '';
							?>
							<input type="checkbox" tabindex="<?php echo $i; ?>" name="ecptm_tax_post_types[]" id="cptm_tax_post_types_<?php echo $post_type; ?>" value="<?php echo $post_type; ?>" <?php echo $checked; ?> /> <label for="cptm_tax_post_types_<?php echo $post_type; ?>"><?php echo ucfirst( $post_type ); ?></label><br />
							<?php
							$i++;
						}
					?>
				</td>
			</tr>
		</table>
		<?php

	} // # function cptm_meta_box()

	public function ecptm_save_post( $post_id ) {

		// verify if this is an auto save routine.
		// If it is our form has not been submitted, so we dont want to do anything
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		// if our nonce isn't there, or we can't verify it, bail
		if( !isset( $_POST['cptm_meta_box_nonce_field'] ) || !wp_verify_nonce( $_POST['cptm_meta_box_nonce_field'], 'cptm_meta_box_nonce_action' ) ) return;

		// update easy custom post type meta values
		if( isset($_POST['ecptm_name']) )
			update_post_meta( $post_id, 'ecptm_name', sanitize_text_field( str_replace( ' ', '', $_POST['ecptm_name'] ) ) );

		if( isset($_POST['ecptm_label']) )
			update_post_meta( $post_id, 'ecptm_label', sanitize_text_field( $_POST['ecptm_label'] ) );

		if( isset($_POST['ecptm_singular_name']) )
			update_post_meta( $post_id, 'ecptm_singular_name', sanitize_text_field( $_POST['ecptm_singular_name'] ) );

		if( isset($_POST['ecptm_description']) )
			update_post_meta( $post_id, 'ecptm_description', esc_textarea( $_POST['ecptm_description'] ) );

		if( isset($_POST['ecptm_icon_slug']) )
			update_post_meta( $post_id, 'ecptm_icon_slug', esc_textarea( $_POST['ecptm_icon_slug'] ) );
		
        if( isset($_POST['ecptm_icon_url']) )
			update_post_meta( $post_id, 'ecptm_icon_url', esc_textarea( $_POST['ecptm_icon_url'] ) );

		if( isset( $_POST['ecptm_public'] ) )
			update_post_meta( $post_id, 'ecptm_public', sanitize_text_field( $_POST['ecptm_public'] ) );

		if( isset( $_POST['ecptm_show_ui'] ) )
			update_post_meta( $post_id, 'ecptm_show_ui', sanitize_text_field( $_POST['ecptm_show_ui'] ) );

		if( isset( $_POST['ecptm_has_archive'] ) )
			update_post_meta( $post_id, 'ecptm_has_archive', sanitize_text_field( $_POST['ecptm_has_archive'] ) );

		if( isset( $_POST['ecptm_exclude_from_search'] ) )
			update_post_meta( $post_id, 'ecptm_exclude_from_search', sanitize_text_field( $_POST['ecptm_exclude_from_search'] ) );

		if( isset( $_POST['ecptm_capability_type'] ) )
			update_post_meta( $post_id, 'ecptm_capability_type', sanitize_text_field( $_POST['ecptm_capability_type'] ) );

		if( isset( $_POST['ecptm_hierarchical'] ) )
			update_post_meta( $post_id, 'ecptm_hierarchical', sanitize_text_field( $_POST['ecptm_hierarchical'] ) );

		if( isset( $_POST['ecptm_rewrite'] ) )
			update_post_meta( $post_id, 'ecptm_rewrite', sanitize_text_field( $_POST['ecptm_rewrite'] ) );

		if( isset( $_POST['ecptm_withfront'] ) )
			update_post_meta( $post_id, 'ecptm_withfront', sanitize_text_field( $_POST['ecptm_withfront'] ) );

		if( isset( $_POST['ecptm_feeds'] ) )
			update_post_meta( $post_id, 'ecptm_feeds', sanitize_text_field( $_POST['ecptm_feeds'] ) );

		if( isset( $_POST['ecptm_pages'] ) )
			update_post_meta( $post_id, 'ecptm_pages', sanitize_text_field( $_POST['ecptm_pages'] ) );

		if( isset($_POST['ecptm_custom_rewrite_slug']) )
			update_post_meta( $post_id, 'ecptm_custom_rewrite_slug', sanitize_text_field( $_POST['ecptm_custom_rewrite_slug'] ) );

		if( isset( $_POST['ecptm_query_var'] ) )
			update_post_meta( $post_id, 'ecptm_query_var', sanitize_text_field( $_POST['ecptm_query_var'] ) );

		if( isset($_POST['ecptm_menu_position']) )
			update_post_meta( $post_id, 'ecptm_menu_position', sanitize_text_field( $_POST['ecptm_menu_position'] ) );

		if( isset( $_POST['ecptm_show_in_menu'] ) )
			update_post_meta( $post_id, 'ecptm_show_in_menu', sanitize_text_field( $_POST['ecptm_show_in_menu'] ) );

		$ecptm_supports = isset( $_POST['ecptm_supports'] ) ? sanitize_text_field( $_POST['ecptm_supports'] ) : array();
			update_post_meta( $post_id, 'ecptm_supports', $ecptm_supports );

		$ecptm_builtin_taxonomies = isset( $_POST['ecptm_builtin_taxonomies'] ) ? sanitize_text_field( $_POST['ecptm_builtin_taxonomies'] ) : array();
			update_post_meta( $post_id, 'ecptm_builtin_taxonomies', $ecptm_builtin_taxonomies );

		// update taxonomy meta values
		if( isset($_POST['ecptm_tax_name']) )
			update_post_meta( $post_id, 'ecptm_tax_name', sanitize_text_field( str_replace( ' ', '', $_POST['ecptm_tax_name'] ) ) );

		if( isset($_POST['ecptm_tax_label']) )
			update_post_meta( $post_id, 'ecptm_tax_label', sanitize_text_field( $_POST['ecptm_tax_label'] ) );

		if( isset($_POST['ecptm_tax_singular_name']) )
			update_post_meta( $post_id, 'ecptm_tax_singular_name', sanitize_text_field( $_POST['ecptm_tax_singular_name'] ) );

		if( isset( $_POST['ecptm_tax_show_ui'] ) )
			update_post_meta( $post_id, 'ecptm_tax_show_ui', sanitize_text_field( $_POST['ecptm_tax_show_ui'] ) );

		if( isset( $_POST['ecptm_tax_hierarchical'] ) )
			update_post_meta( $post_id, 'ecptm_tax_hierarchical', sanitize_text_field( $_POST['ecptm_tax_hierarchical'] ) );

		if( isset( $_POST['ecptm_tax_rewrite'] ) )
			update_post_meta( $post_id, 'ecptm_tax_rewrite', sanitize_text_field( $_POST['ecptm_tax_rewrite'] ) );

		if( isset($_POST['ecptm_tax_custom_rewrite_slug']) )
			update_post_meta( $post_id, 'ecptm_tax_custom_rewrite_slug', sanitize_text_field( $_POST['ecptm_tax_custom_rewrite_slug'] ) );

		if( isset( $_POST['ecptm_tax_query_var'] ) )
			update_post_meta( $post_id, 'ecptm_tax_query_var', sanitize_text_field( $_POST['ecptm_tax_query_var'] ) );

		$ecptm_tax_post_types = isset( $_POST['ecptm_tax_post_types'] ) ? sanitize_text_field ( $_POST['ecptm_tax_post_types'] ) : array();
			update_post_meta( $post_id, 'ecptm_tax_post_types', $ecptm_tax_post_types );

	} // # function save_post()

	function ecptm_change_columns( $cols ) {

		$cols = array(
			'cb'                    => '<input type="checkbox" />',
			'title'                 => __( 'Post Type', 'easy-custom-post-type-maker' ),
			'custom_post_type_name' => __( 'Easy Custom Post Type Name', 'easy-custom-post-type-maker' ),
			'label'                 => __( 'Label', 'easy-custom-post-type-maker' ),
			'description'           => __( 'Description', 'easy-custom-post-type-maker' ),
		);
		return $cols;

	} // # function ecptm_change_columns()

	function ecptm_sortable_columns() {

		return array(
			'title'                 => 'title'
		);

	} // # function ecptm_sortable_columns()

	function ecptm_custom_columns( $column, $post_id ) {

		switch ( $column ) {
			case "custom_post_type_name":
				echo get_post_meta( $post_id, 'ecptm_name', true);
				break;
			case "label":
				echo get_post_meta( $post_id, 'ecptm_label', true);
				break;
			case "description":
				echo get_post_meta( $post_id, 'ecptm_description', true);
				break;
		}

	} // # function ecptm_custom_columns()

	function ecptm_tax_change_columns( $cols ) {

		$cols = array(
			'cb'                    => '<input type="checkbox" />',
			'title'                 => __( 'Taxonomy', 'easy-custom-post-type-maker' ),
			'custom_post_type_name' => __( 'Custom Taxonomy Name', 'easy-custom-post-type-maker' ),
			'label'                 => __( 'Label', 'easy-custom-post-type-maker' )
		);
		return $cols;

	} // # function ecptm_tax_change_columns()

	function ecptm_tax_sortable_columns() {

		return array(
			'title'                 => 'title'
		);

	} // # function ecptm_tax_sortable_columns()

	function ecptm_tax_custom_columns( $column, $post_id ) {

		switch ( $column ) {
			case "custom_post_type_name":
				echo get_post_meta( $post_id, 'ecptm_tax_name', true);
				break;
			case "label":
				echo get_post_meta( $post_id, 'ecptm_tax_label', true);
				break;
		}

	} // # function ecptm_tax_custom_columns()

	function ecptm_admin_footer() {

		global $post_type;
		?>
		<div id="cptm-col-right" class="hidden">

			<div class="wp-box">
				<div class="inner">
					<h2><?php _e( 'Easy Custom Post Type Maker', 'easy-custom-post-type-maker' ); ?></h2>
					<p class="version"><?php _e( 'Version', 'easy-custom-post-type-maker' ); ?> <?php echo $this->version; ?></p>
					<h3><?php _e( 'Useful links', 'easy-custom-post-type-maker' ); ?></h3>
					<ul>
						<li><a class="thickbox" href="#"><?php _e( 'Changelog', 'easy-custom-post-type-maker' ); ?></a></li>
						<li><a href="#" target="_blank"><?php _e( 'Support Forums', 'easy-custom-post-type-maker' ); ?></a></li>
					</ul> 
				</div>
				<div class="footer footer-blue">
					<ul class="left">
						<li><?php _e("Created by",'ecptm' ); ?> <a href="https://www.cleantoshine.com.au/" target="_blank" title="Clean to Shine">Clean to Shine</a></li>
                        <li></li>
                        <li><small>Originally by: https://www.cleantoshine.com.au/</small></li>
					</ul>
					<ul class="right">
						<li><a href="http://wordpress.org/extend/plugins/easy-custom-post-type-maker-2/" target="_blank"><?php _e( 'Vote', 'easy-custom-post-type-maker' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
		<?php
		if( 'ecptm' == $post_type ) {

			// Get all public Custom Post Types
			$post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'objects' );
			// Get all Custom Post Types created by Easy Custom Post Type Maker
			$cptm_posts = get_posts( array( 'post_type' => 'ecptm' ) );
			// Remove all Custom Post Types created by the Easy Custom Post Type Maker plugin
			foreach ( $cptm_posts as $cptm_post ) {
				$values = get_post_custom( $cptm_post->ID );
				unset( $post_types[ $values['ecptm_name'][0] ] );
			}

			if ( count( $post_types ) != 0 ) {
			?>
			<div id="cptm-cpt-overview" class="hidden">
				<div id="icon-edit" class="icon32 icon32-posts-cptm"><br></div>
				<h2><?php _e( 'Other registered Custom Post Types', 'easy-custom-post-type-maker' ); ?></h2>
				<p><?php _e( 'The Custom Post Types below are registered in WordPress but were not created by the Easy Custom Post Type Maker plugin.', 'easy-custom-post-type-maker' ); ?></p>
				<table class="wp-list-table widefat fixed posts" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column">
							</th>
							<th scope="col" id="title" class="manage-column column-title">
								<span><?php _e( 'Post Type', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" id="custom_post_type_name" class="manage-column column-custom_post_type_name">
								<span><?php _e( 'Easy Custom Post Type Name', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" id="label" class="manage-column column-label">
								<span><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" id="description" class="manage-column column-description">
								<span><?php _e( 'Description', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column">
							</th>
							<th scope="col" class="manage-column column-title">
								<span><?php _e( 'Post Type', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" class="manage-column column-custom_post_type_name">
								<span><?php _e( 'Easy Custom Post Type Name', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" class="manage-column column-label">
								<span><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" class="manage-column column-description">
								<span><?php _e( 'Description', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
						</tr>
					</tfoot>

					<tbody id="the-list">
						<?php
							// Create list of all other registered Custom Post Types
							foreach ( $post_types as $post_type ) {
								?>
						<tr valign="top">
							<th scope="row" class="check-column">
							</th>
							<td class="post-title page-title column-title">
								<strong><?php echo $post_type->labels->name; ?></strong>
							</td>
							<td class="custom_post_type_name column-custom_post_type_name"><?php echo $post_type->name; ?></td>
							<td class="label column-label"><?php echo $post_type->labels->name; ?></td>
							<td class="description column-description"><?php echo $post_type->description; ?></td>
						</tr>
								<?php
							}

							if ( count( $post_types ) == 0 ) {
								?>
						<tr class="no-items"><td class="colspanchange" colspan="5"><?php _e( 'No Custom Post Types found' , 'easy-custom-post-type-maker' ); ?>.</td></tr>
								<?php
							}
						?>
					</tbody>
				</table>

				<div class="tablenav bottom">
					<div class="tablenav-pages one-page">
						<span class="displaying-num">
							<?php
							$count = count( $post_types );
							printf( _n( '%d item', '%d items', $count ), $count );
							?>
						</span>
						<br class="clear">
					</div>
				</div>

			</div>
			<?php
			}
		}
		if( 'ecptm_tax' == $post_type ) {

			// Get all public easy custom Taxonomies
			$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
			// Get all easy custom Taxonomies created by Easy Custom Post Type Maker
			$cptm_tax_posts = get_posts( array( 'post_type' => 'ecptm_tax' ) );
			// Remove all easy custom Taxonomies created by the Easy Custom Post Type Maker plugin
			foreach ( $cptm_tax_posts as $cptm_tax_post ) {
				$values = get_post_custom( $cptm_tax_post->ID );
				unset( $taxonomies[ $values['ecptm_tax_name'][0] ] );
			}

			if ( count( $taxonomies ) != 0 ) {
			?>
			<div id="cptm-cpt-overview" class="hidden">
				<div id="icon-edit" class="icon32 icon32-posts-cptm"><br></div>
				<h2><?php _e( 'Other registered Easy custom Taxonomies', 'easy-custom-post-type-maker' ); ?></h2>
				<p><?php _e( 'The easy custom Taxonomies below are registered in WordPress but were not created by the Easy Custom Post Type Maker plugin.', 'easy-custom-post-type-maker' ); ?></p>
				<table class="wp-list-table widefat fixed posts" cellspacing="0">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column">
							</th>
							<th scope="col" id="title" class="manage-column column-title">
								<span><?php _e( 'Taxonomy', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" id="custom_post_type_name" class="manage-column column-custom_taxonomy_name">
								<span><?php _e( 'Custom Taxonomy Name', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" id="label" class="manage-column column-label">
								<span><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
						</tr>
					</thead>

					<tfoot>
						<tr>
							<th scope="col" class="manage-column column-cb check-column">
							</th>
							<th scope="col" class="manage-column column-title">
								<span><?php _e( 'Taxonomy', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" class="manage-column column-custom_post_type_name">
								<span><?php _e( 'Custom Taxonomy Name', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
							<th scope="col" class="manage-column column-label">
								<span><?php _e( 'Label', 'easy-custom-post-type-maker' ); ?></span><span class="sorting-indicator"></span>
							</th>
						</tr>
					</tfoot>

					<tbody id="the-list">
						<?php
							// Create list of all other registered Custom Post Types
							foreach ( $taxonomies as $taxonomy ) {
								?>
						<tr valign="top">
							<th scope="row" class="check-column">
							</th>
							<td class="post-title page-title column-title">
								<strong><?php echo $taxonomy->labels->name; ?></strong>
							</td>
							<td class="custom_post_type_name column-custom_post_type_name"><?php echo $taxonomy->name; ?></td>
							<td class="label column-label"><?php echo $taxonomy->labels->name; ?></td>
						</tr>
								<?php
							}

							if ( count( $taxonomies ) == 0 ) {
								?>
						<tr class="no-items"><td class="colspanchange" colspan="4"><?php _e( 'No easy custom Taxonomies found' , 'easy-custom-post-type-maker' ); ?>.</td></tr>
								<?php
							}
						?>
					</tbody>
				</table>

				<div class="tablenav bottom">
					<div class="tablenav-pages one-page">
						<span class="displaying-num">
							<?php
							$count = count( $taxonomies );
							printf( _n( '%d item', '%d items', $count ), $count );
							?>
						</span>
						<br class="clear">
					</div>
				</div>

			</div>
			<?php
			}
		}

	} // # function ecptm_admin_footer()

	function ecptm_post_updated_messages( $messages ) {

		global $post, $post_ID;

		$messages['ecptm' ] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => __( 'Easy Custom Post Type updated.', 'easy-custom-post-type-maker' ),
			2 => __( 'Easy Custom Post Type updated.', 'easy-custom-post-type-maker' ),
			3 => __( 'Easy Custom Post Type deleted.', 'easy-custom-post-type-maker' ),
			4 => __( 'Easy Custom Post Type updated.', 'easy-custom-post-type-maker' ),
			/* translators: %s: date and time of the revision */
			5 => isset($_GET['revision']) ? sprintf( __( 'Custom Post Type restored to revision from %s', 'easy-custom-post-type-maker' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => __( 'Easy Custom Post Type published.', 'easy-custom-post-type-maker' ),
			7 => __( 'Easy Custom Post Type saved.', 'easy-custom-post-type-maker' ),
			8 => __( 'Easy Custom Post Type submitted.', 'easy-custom-post-type-maker' ),
			9 => __( 'Easy Custom Post Type scheduled for.', 'easy-custom-post-type-maker' ),
			10 => __( 'Easy Custom Post Type draft updated.', 'easy-custom-post-type-maker' ),
		);

		return $messages;

	} // # function ecptm_post_updated_messages()

	function wp_prepare_attachment_for_js( $response, $attachment, $meta )
	{
		// only for image
		if( $response['type'] != 'image' )
		{
			return $response;
		}


		$attachment_url = $response['url'];
		$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

		if( isset( $meta['sizes'] ) && is_array($meta['sizes']) )
		{
			foreach( $meta['sizes'] as $k => $v )
			{
				if( !isset($response['sizes'][ $k ]) )
				{
					$response['sizes'][ $k ] = array(
						'height'      =>  $v['height'],
						'width'       =>  $v['width'],
						'url'         => $base_url .  $v['file'],
						'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
					);
				}
			}
		}

		return $response;
	} // # function wp_prepare_attachment_for_js()

}

/**
 * Instantiate the main class
 *
 * @since	1.0.0
 * @access	public
 *
 * @var	object	$ecptm holds the instantiated class {@uses eCptm}
 */
$ecptm = new eCptm();
