<?php

namespace RRZE\Contact\Taxonomy;

defined('ABSPATH') || exit;

/**
 * Posttype location
 */
class Location extends Taxonomy
{

    protected $postType = 'location';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        add_action('init', [$this, 'register']);
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


}
