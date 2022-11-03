<?php

namespace RRZE\Contact\Shortcode;

defined('ABSPATH') || exit;

use RRZE\Contact\Shortcode\Contact;
use RRZE\Contact\Shortcode\Location;

/**
 * Laden und definieren der Shortcodes
 */
class Shortcode
{
    protected $pluginFile;
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
        add_action('admin_enqueue_scripts', [$this, 'enqueueGutenberg']);
    }

    public function onLoaded()
    {
        $contact_shortcode = new Contact($this->pluginFile, $this->settings);
        $contact_shortcode->onLoaded();

        // $standort_shortcode = new Location($this->pluginFile, $this->settings);
        // $standort_shortcode->onLoaded();
    }

    public function isGutenberg()
    {
        $postID = get_the_ID();
        if ($postID && !use_block_editor_for_post($postID)) {
            return false;
        }

        return true;
    }

    public function enqueueGutenberg()
    {
        if (!$this->isGutenberg()) {
            return;
        }

        // include gutenberg lib
        wp_enqueue_script(
            'RRZE-Gutenberg',
            plugins_url('../../js/gutenberg.js', __FILE__),
            array(
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-editor',
            ),
            null
        );
    }
}
