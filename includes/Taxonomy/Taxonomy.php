<?php

namespace RRZE\Contact\Taxonomy;

defined('ABSPATH') || exit;

use RRZE\Contact\Taxonomy\Contact;
use RRZE\Contact\Taxonomy\Location;

/**
 * Laden und definieren der Posttypes
 */
class Taxonomy
{

    public function __construct()
    {
    }

    public function onLoaded()
    {
        $contact_posttype = new Contact();
        $contact_posttype->onLoaded();

        $standort_posttype = new Location();
        $standort_posttype->onLoaded();

        flush_rewrite_rules();
    }

    public function registerCPT($aParams = ['slug' => '', 'singular_name' => '', 'plural_name' => '', 'supports' => [],  'has_archive_page' => false, 'archive_title' => '', 'show_in_menu' => false, 'menu_name' => '', 'menu_icon' => ''])
    {
        $pluralSlug = $aParams['slug'] . 's';

        $labels = [
            'name' => _x($aParams['plural_name'], 'Post Type General Name', 'rrze-contact'),
            'singular_name' => _x($aParams['singular_name'], 'Post Type Singular Name', 'rrze-contact'),
            'add_new' => __("Add {$aParams['singular_name']}", 'rrze-contact'),
            'add_new_item' => __("Add new {$aParams['singular_name']}", 'rrze-contact'),
            'edit_item' => __("Edit {$aParams['singular_name']}", 'rrze-contact'),
            'all_items' => __("All {$aParams['plural_name']}", 'rrze-contact'),
            'search_items' => __("Search {$aParams['plural_name']}", 'rrze-contact'),
        ];

        $rewrite = [
            'slug' => $aParams['slug'],
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        ];

        $capabilities = [
            'edit_post' => 'edit_' . $aParams['slug'],
            'read_post' => 'read_' . $aParams['slug'],
            'delete_post' => 'delete_' . $aParams['slug'],
            'edit_posts' => 'edit_' . $pluralSlug,
            'edit_others_posts' => 'edit_others_' . $pluralSlug,
            'publish_posts' => 'publish_' . $pluralSlug,
            'read_private_posts' => 'read_private_' . $pluralSlug,
            'create_posts' => 'edit_' . $pluralSlug,
            'delete_posts' => 'delete_' . $pluralSlug,
            'delete_private_posts' => 'delete_private_' . $pluralSlug,
            'delete_published_posts' => 'delete_published_' . $pluralSlug,
            'delete_others_posts' => 'delete_others_' . $pluralSlug,
            'edit_private_posts' => 'edit_private_' . $pluralSlug,
            'edit_published_posts' => 'edit_published_' . $pluralSlug
        ];
    
        $args = [
            'label' => __($aParams['singular_name'], 'rrze-contact'),
            'description' => __("{$aParams['singular_name']} informations", 'rrze-contact'),
            'labels' => $labels,
            'supports' => $aParams['supports'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'can_export' => true,
            'has_archive' => $aParams['has_archive_page'],
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => $aParams['slug'],
            'rewrite' => $rewrite,
            'capability_type' => $aParams['slug'],
            'capabilities' => $capabilities,
            'map_meta_cap' => true,
        ];

        if (!empty($aParams['show_in_menu'])){
            $args['show_in_menu'] = $aParams['show_in_menu'];
        }

        if (!empty($aParams['menu_name'])){
            $args['menu_name'] = $aParams['menu_name'];
            $args['menu_icon'] = $aParams['menu_icon'];
        }            

        register_post_type($aParams['slug'], $args);
    }
}
