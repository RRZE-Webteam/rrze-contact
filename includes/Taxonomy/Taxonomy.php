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
    protected $pluginFile;
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        $contact_posttype = new Contact($this->pluginFile, $this->settings);
        $contact_posttype->onLoaded();

        $standort_posttype = new Location($this->pluginFile, $this->settings);
        $standort_posttype->onLoaded();

        // 2DO: improve this. no need for transient as long as fired after update
        if (get_site_transient('rrze-contact-options-updated')) {
            flush_rewrite_rules();
            delete_site_transient('rrze-contact-options-updated');
        }
    }

    public function registerCPT($aParams = ['name' => '', 'supports' => [], 'icon' => '', 'has_archive_page' => '', 'archive_slug' => '', 'archive_title' => ''])
    {
        $lowerName = strtolower($aParams['name']);

        $labels = [
            'name' => _x($aParams['archive_title'], 'Post Type General Name', 'rrze-faq'),
            'singular_name' => _x($aParams['name'], 'Post Type Singular Name', 'rrze-faq'),
            'menu_name' => __($aParams['name'], 'rrze-faq'),
            'add_new' => __("Add {$aParams['name']}", 'rrze-faq'),
            'add_new_item' => __("Add new {$aParams['name']}", 'rrze-faq'),
            'edit_item' => __("Edit {$aParams['name']}", 'rrze-faq'),
            'all_items' => __("All {$aParams['name']}", 'rrze-faq'),
            'search_items' => __("Search {$aParams['name']}", 'rrze-faq'),
        ];

        $rewrite = [
            'slug' => $aParams['archive_slug'],
            'with_front' => true,
            'pages' => true,
            'feeds' => true,
        ];

        $capabilities = [
            'edit_post' => 'edit_' . $lowerName,
            'read_post' => 'read_' . $lowerName,
            'delete_post' => 'delete_' . $lowerName,
            'edit_posts' => 'edit_' . $lowerName . 's',
            'edit_others_posts' => 'edit_others_' . $lowerName . 's',
            'publish_posts' => 'publish_' . $lowerName . 's',
            'read_private_posts' => 'read_private_' . $lowerName . 's',
            'delete_posts' => 'delete_' . $lowerName . 's',
            'delete_private_posts' => 'delete_private_' . $lowerName . 's',
            'delete_published_posts' => 'delete_published_' . $lowerName . 's',
            'delete_others_posts' => 'delete_others_' . $lowerName . 's',
            'edit_private_posts' => 'edit_private_' . $lowerName . 's',
            'edit_published_posts' => 'edit_published_' . $lowerName . 's'
        ];
    
        $args = [
            'label' => __($aParams['name'], 'rrze-faq'),
            'description' => __("{$aParams['name']} informations", 'rrze-faq'),
            'labels' => $labels,
            'supports' => $aParams['supports'],
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_icon' => $icon,
            'can_export' => true,
            'has_archive' => $aParams['has_archive_page'],
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => $lowerName,
            'rewrite' => $rewrite,
            'capability_type' => $lowerName,
            'capabilities' => $capabilities,
            'map_meta_cap' => true,
        ];

        if ($aParams['show_in_rest']) {
            $args['show_in_rest'] = true;
            $args['rest_base'] = $lowerName;
            $args['rest_controller_class'] = 'WP_REST_Posts_Controller';
        }

        register_post_type($lowerName, $args);
    }

}
