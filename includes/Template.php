<?php

namespace RRZE\Contact;

use function RRZE\Contact\Config\getConstants;

defined('ABSPATH') || exit;

/**
 * Define Template
 */
class Template
{

    protected $pluginFile;
    private $settings = '';
    private $isFauTheme = false;

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        $this->isFauTheme = self::isFAUTheme();
        add_filter('single_template', array($this, 'include_single_template'));
        add_filter('archive_template', array($this, 'include_archive_template'));
    }

    private static function isFAUTheme() {
        $active_theme = wp_get_theme();
        return in_array($active_theme->get('Name'), getConstants('fauthemes'));
    }
    

    public function include_single_template($template_path)
    {
        global $post;

        if (!($post->post_type == 'contact' || $post->post_type == 'location')){
            return $template_path;
        }

        if ($this->isFauTheme) {
            if ($post->post_type == 'contact') {
                $template_path = '/templates/single/single-contact-fau-theme.php';
            } else {
                $template_path = '/templates/single/single-location-fau-theme.php';
            }
        } else {
            if ($post->post_type == 'contact') {
                $template_path = '/templates/single/single-contact.php';
            } else {
                $template_path = '/templates/single/single-location.php';
            }
        }

        return dirname($this->pluginFile) . $template_path;
    }

    public function include_archive_template($template_path)
    {
        global $post;

        if (!($post->post_type == 'contact' || $post->post_type == 'location')){
            return $template_path;
        }

        if ($this->isFauTheme) {
            if ($post->post_type == 'contact') {
                $template_path = '/templates/archive/archive-contact-fau-theme.php';
            } else {
                $template_path = '/templates/archive/archive-location-fau-theme.php';
            }
        } else {
            if ($post->post_type == 'contact') {
                $template_path = '/templates/archive/archive-contact.php';
            } else {
                $template_path = '/templates/archive/archive-location.php';
            }
        }

        return dirname($this->pluginFile) . $template_path;
    }


    /**
     * [getContent description]
     * @param  string $template [description]
     * @param  array  $data     [description]
     * @param  bool   $file     [description]
     * @return string           [description]
     */
    public static function getContent(string $template = '', array $data = []): string
    {
        return self::parseContent($template, $data);
    }

    /**
     * [parseContent description]
     * @param  string $template [description]
     * @param  array  $data     [description]
     * @return string           [description]
     */
    protected static function parseContent(string $template, array $data): string
    {
        $content = self::getTemplate($template);
        if (empty($content)) {
            return '';
        }
        if (empty($data)) {
            return $content;
        }
        $parser = new Parser();
        return $parser->parse($content, $data);
    }

    /**
     * [getTemplate description]
     * @param  string $template [description]
     * @return string           [description]
     */
    protected static function getTemplate(string $template): string
    {
        $content = '';
        $templateFile = sprintf(
            '%1$stemplates/%2$s',
            plugin()->getDirectory(),
            $template
        );
        if (is_readable($templateFile)) {
            ob_start();
            include($templateFile);
            $content = ob_get_contents();
            @ob_end_clean();
        }else{
            echo $templateFile . ' not readable';
            exit;
        }
        return $content;
    }
}
