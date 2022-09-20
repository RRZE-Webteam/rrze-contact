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
        add_action('init', [$this, 'registerCPT']);
    }
    
    public function registerCPT()
    {
        $archive_slug = (!empty($this->settings->options['constants_has_archive_page']) ? $this->settings->options['constants_has_archive_page'] : $this->postType);
		$has_archive_page = (!empty($this->settings->options['constants_has_archive_page']) && ($this->settings->options['constants_has_archive_page'] == $this->postType) ? true : false);
		$archive_page = get_page_by_path($archive_slug, OBJECT, 'page');
		$archive_title = (!empty($archive_page) ? $archive_page->post_title : 'Kontakte');

        $aParams = [
            'name' => 'Location',
            'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
            'icon' => '',
            'has_archive_page' => true,
            'archive_slug' => 'location',
		    'archive_title' => 'Locations',
            'show_in_menu' => 'edit.php?post_type=contact',
            'show_in_rest' => false,
        ];

        parent::registerCPT($aParams);
    }


}
