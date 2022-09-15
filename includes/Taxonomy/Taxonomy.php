<?php

namespace RRZE\Contact\Taxonomy;

defined('ABSPATH') || exit;

// use RRZE_Contact\Main;
// use RRZE_Contact\Taxonomy\Contact;
// use RRZE_Contact\Taxonomy\Location;

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

        if (get_transient('rrze-contact-options')) {
            flush_rewrite_rules();
            delete_transient('rrze-contact-options');
        }
    }

    public function registerCPT($name = '', $supports = [], $icon = '') {	    
        $lowerName = strtolower($name);
        $labels = array(
                'name'                => _x($name, 'Post Type General Name', 'rrze-faq' ),
                'singular_name'       => _x($name, 'Post Type Singular Name', 'rrze-faq' ),
                'menu_name'           => __($name, 'rrze-faq' ),
                'add_new'             => __("Add $name", 'rrze-faq' ),
                'add_new_item'        => __("Add new $name", 'rrze-faq' ),
                'edit_item'           => __("Edit $name", 'rrze-faq' ),
                'all_items'           => __("All $name", 'rrze-faq' ),
                'search_items'        => __("Search $name", 'rrze-faq' ),
        );
        $rewrite = array(
                'slug'                => $lowerName,
                'with_front'          => true,
                'pages'               => true,
                'feeds'               => true,
        );
        $args = array(
                'label'               => __($name, 'rrze-faq' ),
                'description'         => __("$name informations", 'rrze-faq' ),
                'labels'              => $labels,
                'supports'            => $supports,
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => false,
                'show_in_admin_bar'   => true,
                'menu_icon'		  => $icon,
                'can_export'          => true,
                'has_archive'         => (!empty($this->settings->options['constants_has_archive_page']) ? $this->settings->options['constants_has_archive_page'] : true),
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'query_var'           => $lowerName,
                'rewrite'             => $rewrite,
                'show_in_rest'        => true,
                'rest_base'           => $lowerName,
                'rest_controller_class' => 'WP_REST_Posts_Controller',
                'capability_type' => $lowerName,
                'capabilities' => [
                    'edit_post'	=> 'edit_contact',
                    'read_post'	=> 'read_contact',
                    'delete_post'	=> 'delete_contact',
                    'edit_posts'	=> 'edit_contacts',
                    'edit_others_posts' => 'edit_others_contacts',
                    'publish_posts'	=> 'publish_contacts',
                    'read_private_posts' => 'read_private_contacts',
                    'delete_posts'	=> 'delete_contacts',
                    'delete_private_posts' => 'delete_private_contacts',
                    'delete_published_posts' => 'delete_published_contacts',
                    'delete_others_posts' => 'delete_others_contacts',
                    'edit_private_posts' => 'edit_private_contacts',
                    'edit_published_posts' => 'edit_published_contacts'
                    ],
                'map_meta_cap' => true,
    
        );
        register_post_type($lowerName, $args );
    }

}
