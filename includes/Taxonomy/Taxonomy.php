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
}
