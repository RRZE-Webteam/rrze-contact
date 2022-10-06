<?php

namespace RRZE\Contact\Taxonomy;

defined('ABSPATH') || exit;

/**
 * Posttype location
 */
class Location extends Taxonomy
{

    protected $postType = 'location';

    public function __construct()
    {
    }

    public function onLoaded()
    {
        add_action('init', [$this, 'register']);
        add_action('admin_menu', array($this, 'contact_menu_subpages'));
    }
    
    public function register()
    {
        $aParams = [
            'slug' => 'location',
            'singular_name' => __('Location', 'rrze-contact'),
            'plural_name' => __('Locations', 'rrze-contact'),
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'has_archive_page' => true,
            'archive_slug' => 'location',
            'show_in_menu' => 'edit.php?post_type=contact',
        ];

        parent::registerCPT($aParams);

    }

    public function contact_menu_subpages(){
        add_submenu_page(
            'edit.php?post_type=contact', 
            __('Add Location', 'rrze-contact'), 
            __('New Location', 'rrze-contact'), 
            'edit_contacts', 
            'post-new.php?post_type=location', 
        );
    }
}
