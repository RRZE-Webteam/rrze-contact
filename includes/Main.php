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
        // add_action('save_post_contact', [$this, 'clearData'], 10, 3 );
        // add_filter( 'update_post_metadata', [$this, 'clearData'], 10, 4 );

        // prevent using the archive slug (which is editable) as slug for posts, pages or media
        add_filter( 'wp_unique_post_slug_is_bad_hierarchical_slug', [$this, 'archiveSlugIsBadHierarchicalSlug'], 10, 4 );
        add_filter( 'wp_unique_post_slug_is_bad_hierarchical_slug', [$this, 'archiveSlugIsBadFlatSlug'], 10, 4 );

        // sanatize our meta data
        add_filter('sanitize_post_meta_rrze_contact_phone', ['Functions', 'formatPhone'] );


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

    public function test($value){

        echo "value = " . $value;
        exit;

    }

    public function clearData($post_id, $post, $update){
        // public function clearData($post_id, $post, $update){
        // fires if clicked on "Add new" as well, but at this moment we don't have any details to be handled 
        if ( !$update ) {
            return;
        }

        echo 'clearData';
        var_dump($post);
        echo '<br> $post_id = ' . $post_id;
        echo '<br>' . ($update?'is an update' : 'is not an update');
        exit;
    }

    function archiveSlugIsBadHierarchicalSlug( $isBadSlug, $slug, $post_type, $post_parent ) {
        if ($post_type == 'contact'){
            return false;
        }
        $CPTdata = get_post_type_object('contact');
        $archiveSlug = $CPTdata->rewrite['slug'];

        if ( !$post_parent && $slug == $archiveSlug ){
            return true;
        }
        return $isBadSlug;
    }

    function archiveSlugIsBadFlatSlug( $isBadSlug, $slug, $post_type) {
        if ($post_type == 'contact'){
            return false;
        }
        $CPTdata = get_post_type_object('contact');
        $archiveSlug = $CPTdata->rewrite['slug'];

        if ($slug == $archiveSlug ){
            return true;
        }
        return $isBadSlug;
    }

}
