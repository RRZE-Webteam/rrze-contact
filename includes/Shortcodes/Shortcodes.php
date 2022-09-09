<?php

namespace RRZE_Contact\Shortcodes;

defined('ABSPATH') || exit;

use RRZE_Contact\Shortcodes\Contact;
use RRZE_Contact\Shortcodes\Location;

/**
 * Laden und definieren der Shortcodes
 */
class Shortcodes
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
        $kontakt_shortcode = new Contact($this->pluginFile, $this->settings);
        $kontakt_shortcode->onLoaded();

        $standort_shortcode = new Location($this->pluginFile, $this->settings);
        $standort_shortcode->onLoaded();
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
