<?php

namespace RRZE\Contact;

use RRZE\Contact\Helper;

defined('ABSPATH') || exit;

/**
 * Define Templates
 */
class Templates
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
        add_filter('single_template', array($this, 'include_single_template'));
        add_filter('archive_template', array($this, 'include_archive_template'));
    }

    public function include_single_template($template_path)
    {
        global $post;
        if ($post->post_type == 'contact') {
            if (Helper::isFAUTheme()) {
                $template_path = dirname($this->pluginFile) . '/templates/single-contact-fau-theme.php';
            } else {
                $template_path = dirname($this->pluginFile) . '/templates/single-contact.php';
            }
        }
        if ($post->post_type == 'location') {
            if (Helper::isFAUTheme()) {
                $template_path = dirname($this->pluginFile) . '/templates/single-location-fau-theme.php';
            } else {
                $template_path = dirname($this->pluginFile) . '/templates/single-location.php';
            }
        }
        return $template_path;
    }

    public function include_archive_template($template_path)
    {
        global $post;
        if ($post->post_type == 'contact') {
            if (Helper::isFAUTheme()) {
                $template_path = dirname($this->pluginFile) . '/templates/archive-contact-fau-theme.php';
            } else {
                $template_path = dirname($this->pluginFile) . '/templates/archive-contact.php';
            }
        }
        if ($post->post_type == 'location') {
            if (Helper::isFAUTheme()) {
                $template_path = dirname($this->pluginFile) . '/templates/archive-location-fau-theme.php';
            } else {
                $template_path = dirname($this->pluginFile) . '/templates/archive-location.php';
            }
        }
        return $template_path;
    }
}
