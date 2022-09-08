<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

use function RRZE\Contact\Config\getConstants;
use RRZE\Contact\Settings;
use RRZE\Contact\Shortcode;

/**
 * Hauptklasse (Main)
 */
class Main
{
    /**
     * Der vollständige Pfad- und Dateiname der Plugin-Datei.
     * @var string
     */
    protected $pluginFile;
    protected $widget;

    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
        add_action('init', 'RRZE\Contact\add_endpoint');
    }

    public function onLoaded()
    {
        $functions = new Functions($this->pluginFile);
        $functions->onLoaded();

        $settings = new Settings($this->pluginFile);
        $settings->onLoaded();

        $this->settings = $settings;

        $shortcode = new Shortcode($this->pluginFile, $settings);
        $shortcode->onLoaded();

        // Widget
        $this->widget = new ContactWidget($this->pluginFile, $settings);
        add_action('widgets_init', [$this, 'loadWidget']);
        add_theme_support('widgets-block-editor');
        apply_filters('gutenberg_use_widgets_block_editor', get_theme_support('widgets-block-editor'));
    }

    public function loadWidget()
    {
        register_widget($this->widget);
    }


    public static function getThemeGroup()
    {
        $constants = getConstants();
        $ret = '';
        $active_theme = wp_get_theme();
        $active_theme = $active_theme->get('Name');

        if (in_array($active_theme, $constants['fauthemes'])) {
            $ret = 'fauthemes';
        } elseif (in_array($active_theme, $constants['rrzethemes'])) {
            $ret = 'rrzethemes';
        }
        return $ret;
    }

}
