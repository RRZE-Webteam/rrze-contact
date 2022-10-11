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
            //if (is_single()) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ($theme_file = locate_template(array('single-contact.php'))) {
                $template_path = $theme_file;
            } else {
                if (Helper::isFAUTheme()) {
                    $template_path = dirname($this->pluginFile) . '/templates/single-contact-fau-theme.php';
                } else {
                    $template_path = dirname($this->pluginFile) . '/templates/single-contact.php';
                }
            }
            //}
        }
        if ($post->post_type == 'location') {
            //if (is_single()) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ($theme_file = locate_template(array('single-location.php'))) {
                $template_path = $theme_file;
            } else {
                if (Helper::isFAUTheme()) {
                    $template_path = dirname($this->pluginFile) . '/templates/single-location-fau-theme.php';
                } else {
                    $template_path = dirname($this->pluginFile) . '/templates/single-location.php';
                }
            }
            //}
        }
        return $template_path;
    }

    public function include_archive_template($template_path)
    {
        global $post;
        if ($post->post_type == 'contact') {
            //if (is_single()) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ($theme_file = locate_template(array('archive-contact.php'))) {
                $template_path = $theme_file;
            } else {

                if (Helper::isFAUTheme()) {
                    $template_path = dirname($this->pluginFile) . '/archive-contact-fau-theme.php';
                } else {
                    $template_path = dirname($this->pluginFile) . '/archive-contact.php';
                }

            }
            //}
        }
        if ($post->post_type == 'location') {
            //if (is_single()) {
            // checks if the file exists in the theme first,
            // otherwise serve the file from the plugin
            if ($theme_file = locate_template(array('archive-location.php'))) {
                $template_path = $theme_file;
            } else {
                if (Helper::isFAUTheme()) {
                    $template_path = dirname($this->pluginFile) . '/archive-location-fau-theme.php';
                } else {
                    $template_path = dirname($this->pluginFile) . '/archive-location.php';
                }
            }
            //}
        }
        return $template_path;
    }
}
