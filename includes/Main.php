<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

use function RRZE\Contact\Config\getConstants;
use RRZE\Contact\Settings;
use RRZE\Contact\Taxonomy\Taxonomy;
use RRZE\Contact\Templates;
use RRZE\Contact\Metaboxes\Metaboxes;
use RRZE\Contact\Shortcode\Shortcode;

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
    private $settings = '';


    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
        add_action('init', 'RRZE\Contact\add_endpoint');
    }

    public function onLoaded()
    {
        add_action('wp_enqueue_scripts', [$this, 'registerPluginStyles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);

        $functions = new Functions($this->pluginFile);
        $functions->onLoaded();

        $settings = new Settings($this->pluginFile);
        $settings->onLoaded();
        $this->settings = $settings;

        $taxonomy = new Taxonomy($settings);
        $taxonomy->onLoaded();

        $templates = new Templates($this->pluginFile, $settings);
        $templates->onLoaded();

        $metaboxes = new Metaboxes($this->pluginFile, $settings);
        $metaboxes->onLoaded();

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


    // public static function getThemeGroup()
    // {
    //     $constants = getConstants();
    //     $ret = '';
    //     $active_theme = wp_get_theme();
    //     $active_theme = $active_theme->get('Name');

    //     if (in_array($active_theme, $constants['fauthemes'])) {
    //         $ret = 'fauthemes';
    //     } elseif (in_array($active_theme, $constants['rrzethemes'])) {
    //         $ret = 'rrzethemes';
    //     }
    //     return $ret;
    // }

    public function registerPluginStyles()
    {
        wp_register_style('rrze-contact', plugins_url('css/rrze-contact.css', plugin_basename($this->pluginFile)));
    }

    public function enqueueAdminScripts()
    {
        wp_register_style('rrze-contact-adminstyle', plugins_url('css/rrze-contact-admin.css', plugin_basename($this->pluginFile)));
        wp_enqueue_style('rrze-contact-adminstyle');
        wp_register_script('rrze-contact-adminscripts', plugins_url('js/rrze-contact-admin.js', plugin_basename($this->pluginFile)));
        wp_enqueue_script('rrze-contact-adminscripts');
        wp_enqueue_script('jquery');

    }


}
