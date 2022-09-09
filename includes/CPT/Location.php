<?php

namespace RRZE_Contact\Taxonomy;

defined('ABSPATH') || exit;

/**
 * Posttype location
 */
class location
{

    protected $postType = 'location';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        add_action('init', [$this, 'set']);
        // add_action('admin_init', [$this, 'register']);

    }

    public function set()
    {

        $location_labels = array(
            'name' => _x('Locations', 'Post Type General Name', 'rrze-contact'),
            'singular_name' => _x('Location', 'Post Type Singular Name', 'rrze-contact'),
            'menu_name' => __('Location', 'rrze-contact'),
            'parent_item_colon' => __('Superordinate location', 'rrze-contact'),
            'all_items' => __('All locations', 'rrze-contact'),
            'view_item' => __('Show location', 'rrze-contact'),
            'add_new_item' => __('Add location', 'rrze-contact'),
            'add_new' => __('New location', 'rrze-contact'),
            'edit_item' => __('Edit location', 'rrze-contact'),
            'update_item' => __('Update location', 'rrze-contact'),
            'search_items' => __('Search location', 'rrze-contact'),
            'not_found' => __('No location found', 'rrze-contact'),
            'not_found_in_trash' => __('No location found in trash', 'rrze-contact'),
        );
        $location_rewrite = array(
            'slug' => 'location',
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        );
        $location_args = array(
            'label' => __('location', 'rrze-contact'),
            'description' => __('Location\'s informations', 'rrze-contact'),
            'labels' => $location_labels,
            'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=contact',
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => 'location',
            'rewrite' => $location_rewrite,
            'capability_type' => 'location',
            'capabilities' => array(
                'edit_post' => 'edit_location',
                'read_post' => 'read_location',
                'delete_post' => 'delete_location',
                'edit_posts' => 'edit_locations',
                'edit_others_posts' => 'edit_others_locations',
                'publish_posts' => 'publish_locations',
                'read_private_posts' => 'read_private_locations',
                'delete_posts' => 'delete_locations',
                'delete_private_posts' => 'delete_private_locations',
                'delete_published_posts' => 'delete_published_locations',
                'delete_others_posts' => 'delete_others_locations',
                'edit_private_posts' => 'edit_private_locations',
                'edit_published_posts' => 'edit_published_locations',
            ),
            'map_meta_cap' => true,
        );

        register_post_type($this->postType, $location_args);

    }

    public function register()
    {

    }

}
