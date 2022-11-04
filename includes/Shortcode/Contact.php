<?php

namespace RRZE\Contact\Shortcode;

use function RRZE\Contact\Config\getConstants;
use function RRZE\Contact\Config\getDisplayFields;
use function RRZE\Contact\Config\getShortcodeDefaults;
use function RRZE\Contact\Config\getShortcodeSettings;
use RRZE\Contact\Data;
use RRZE\Contact\Template;

defined('ABSPATH') || exit;

/**
 * Define Shortcodes
 */
class Contact extends Shortcode
{
    public $pluginFile = '';
    private $settings = '';
    private $aAllFormats = [];

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings('contact');
        add_action('init', [$this, 'initGutenberg']);
    }

    public function onLoaded()
    {
        add_shortcode('contact', [$this, 'shortcode_contact']);
        // add_shortcode('contact', [$this, 'shortcode_contact']);
        // add_shortcode('contactliste', [$this, 'shortcode_contactlist']);
        // add_shortcode('contacts', [$this, 'shortcode_contactlist']);
    }

    private function getCSSClass($class, $border, $background)
    {
        return 'rrze-contact' . (!empty($class) ? ' ' . esc_attr($class) : '') . ' ' . $border . (in_array($background, getConstants('bgColors')) ? 'background-' . esc_atts($background) : '');
    }

    private function getPostIDsByCategory($category)
    {
        $aRet = [];
        $catObj = get_term_by('slug', $category, 'contacts_category');
        if (is_object($catObj)) {
            $aRet = get_posts([
                'post_type' => 'contact',
                'post_status' => 'publish',
                'numberposts' => $limit,
                'orderby' => 'title',
                'order' => 'ASC',
                'fields' => 'ids',
                'suppress_filters' => false,
                'tax_query' => [
                    'taxonomy' => 'contacts_category',
                    'field' => 'id',
                    'terms' => $catObj->term_id,
                ],
            ]);
        }
        return $aRet;
    }

    private function getDisplayFields(&$shortcodeSettings, $format = '', $show = '', $hide = '')
    {
        $aRet = [];
        $aFormat = (!empty($shortcodeSettings['format']['values']) ? $shortcodeSettings['format']['values'] : []);
        $this->aAllFormats = [];

        foreach ($aFormat as $nr => $aVal) {
            if ($aVal['id'] == $format) {
                $aRet = $aVal['fields'];
            }
            $this->aAllFormats[] = $aVal['id'];
        }

        $aRet = array_diff($aRet, array_map('trim', explode(',', $hide)));
        $aRet = $aRet + array_map('trim', explode(',', $show));

        return $aRet;
    }

    public function shortcode_contact($atts, $content = null)
    {
        wp_enqueue_style('rrze-contact');

        $atts = shortcode_atts(getShortcodeDefaults($this->settings), $atts);

        $aDisplayfields = $this->getDisplayFields($this->settings, $atts['format'], $atts['show'], $atts['hide']);

        if (!in_array($atts['format'], $this->aAllFormats)) {
            return __('Unknown format', 'rrze-contact') . ' "' . $atts['format'] . '"';
        }

        $class = (!empty($aDisplayfields['class']) ? $aDisplayfields['class'] : '');
        $border = (!empty($aDisplayfields['border']) ? 'border' : 'noborder');
        $background = (!empty($aDisplayfields['background']) ? $aDisplayfields['background'] : '');
        $class = self::getCSSClass($class, $border, $background);

        if (!empty($atts['category'])) {
            $aPostIDs = getPostIDsByCategory($atts['category']);
        } elseif (!empty($atts['id'])) {
            $aPostIDs = array_map('trim', explode(',', $atts['id']));
        } else {
            return __('id or category is needed', 'rrze-contact');
        }

        foreach ($aPostIDs as $postID) {
            $data = Data::getContactData($postID, $aDisplayfields, $atts['format']);
            $data['class'] = $class;

            // echo '<pre>';
            // var_dump($data);
            // exit;

            if (!empty($data)) {
                $template = 'shortcodes/contact/' . $atts['format'] . '.html';
                $content .= Template::getContent($template, $data);
                if ($atts['format'] == 'organization') {
                    $content = do_shortcode($content);
                }
            } else {
                $content .= sprintf(__('No contact entry could be found with the specified ID %s.', 'rrze-contact'), $postID);
            }

        }

        return $content;
    }

    public function shortcode_contactlist($atts, $content = null)
    {
        $atts = shortcode_atts(getShortcodeDefaults($this->settings['contactlist']), $atts);

        $aDisplayfields = Data::get_display_field($atts['format'], $atts['show'], $atts['hide']);
        $limit = (!empty($atts['unlimited']) ? -1 : 100);

        if (isset($atts['category'])) {
            $category = get_term_by('slug', $atts['category'], 'contacts_category');
            if (is_object($category)) {
                $posts = get_posts(array('post_type' => 'contact', 'fields' => 'ids', 'post_status' => 'publish', 'numberposts' => $limit, 'orderby' => 'title', 'order' => 'ASC', 'tax_query' => array(
                    array(
                        'taxonomy' => 'contacts_category',
                        'field' => 'id', // can be slug or id - a CPT-onomy term's ID is the same as its post ID
                        'terms' => $category->term_id, // Notice: Trying to get property of non-object bei unbekannter Kategorie
                    ),
                ), 'suppress_filters' => false));
            }
        }

        if (isset($posts)) {
            $class = 'rrze-contact';
            if ($atts['class']) {
                $class .= ' ' . esc_attr($atts['class']);
            }

            if (isset($aDisplayfields['border'])) {
                if ($aDisplayfields['border']) {
                    $class .= ' border';
                } else {
                    $class .= ' noborder';
                }
            }

            if (isset($atts['background']) && (!empty($atts['background']))) {
                $bg_array = array('grau', 'fau', 'phil', 'med', 'nat', 'tf', 'rw');
                if (in_array($atts['background'], $bg_array)) {
                    $class .= ' background-' . esc_attr($atts['background']);
                }
            }
            $format = '';

            if (isset($atts['format'])) {
                $format = $atts['format'];
            }

            switch ($format) {
                case 'table':
                    $content = '<table class="' . $class . '">';
                    break;
                case 'name':
                case 'shortlist':
                    $class .= ' contact liste-contact';
                    $content = '<span class="' . $class . '">';
                    break;
                case 'liste':
                    $class .= ' contact liste-contact';
                    $content = '<ul class="' . $class . '">';
                    break;
                case 'card':
                    $class .= ' contact-card';
                    $content = '<div class="' . $class . '">';
                    break;
                default:
                    $content = '';
            }

            $number = count($posts);
            $i = 1;

            $posts = Data::sort_contact_posts($posts, $atts['sort'], $atts['order']);

            foreach ($posts as $value) {
                switch ($format) {
                    case 'liste':
                        $thisentry = Data::RRZE_Contact_shortlist($value, $aDisplayfields, $atts);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                        }
                        break;
                    case 'name':
                    case 'shortlist':
                        $thisentry = Data::RRZE_Contact_shortlist($value, $aDisplayfields, $atts);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                            if ($i < $number) {
                                $content .= ", ";
                            }
                        }
                        break;

                    case 'table':
                        $content .= Data::RRZE_Contact_tablerow($value, $aDisplayfields, $atts);
                        break;
                    case 'page':
                        $content .= Data::RRZE_Contact_page($value, $aDisplayfields, $atts, true);
                        break;
                    case 'sidebar':
                        $content .= Data::RRZE_Contact_sidebar($value, $aDisplayfields, $atts);
                        break;
                    case 'card':
                        $content .= Data::RRZE_Contact_card($value, $aDisplayfields, $atts);
                        break;
                    default:
                        $content .= Data::RRZE_Contact_markup($value, $aDisplayfields, $atts);
                }
                $i++;
            }

            switch ($format) {
                case 'table':
                    $content .= '</table>';
                    break;
                case 'name':
                case 'shortlist':
                    $content .= '</span>';
                    break;
                case 'liste':
                    $content .= '</ul>';
                    break;
                case 'card':
                    $content .= '</div>';
                    break;
                default:
            }

        } else {
            if (is_object($category)) {
                $content = '<p>' . sprintf(__('No contacts were found in the category "%s".', 'rrze-contact'), $category->slug) . '</p>';
            } else {
                $content = '<p>' . sprintf(__('Sorry, the category "%s" could not be found.', 'rrze-contact'), $atts['category']) . '</p>';
            }
        }

        return $content;
    }

    public function fillGutenbergOptions()
    {

        $mySettings = $this->settings;
        // we don't need slug because we have id
        unset($mySettings['slug']);

        // fill select "id"
        $mySettings['id']['field_type'] = 'select';
        $mySettings['id']['default'] = '';
        $mySettings['id']['type'] = 'string';
        $mySettings['id']['items'] = array('type' => 'text');
        $mySettings['id']['values'] = array();
        $mySettings['id']['values'][] = ['id' => '', 'val' => __('-- All --', 'rrze-contact')];

        $aContact = get_posts(array('posts_per_page' => -1, 'post_type' => 'contact', 'orderby' => 'title', 'order' => 'ASC'));
        foreach ($aContact as $contact) {
            $mySettings['id']['values'][] = [
                'id' => $contact->ID,
                'val' => str_replace("'", "", str_replace('"', "", $contact->post_title)),
            ];
        }

        // fill select "category"
        $mySettings['category']['field_type'] = 'select';
        $mySettings['category']['default'] = '';
        $mySettings['category']['type'] = 'string';
        $mySettings['category']['items'] = array('type' => 'text');
        $mySettings['category']['values'] = array();
        $mySettings['category']['values'][] = ['id' => '', 'val' => __('-- Alle --', 'rrze-contact')];

        $aTerms = get_terms(array('taxonomy' => 'contacts_category', 'hide_empty' => false));

        foreach ($aTerms as $term) {
            $mySettings['category']['values'][] = [
                'id' => $term->slug,
                'val' => html_entity_decode($term->name),
            ];
        }

        return $mySettings;
    }

    public function initGutenberg()
    {
        if (!$this->isGutenberg()) {
            return;
        }

        // get prefills for dropdowns
        $mySettings = $this->fillGutenbergOptions();

        // register js-script to inject php config to call gutenberg lib
        $editor_script = $mySettings['block']['blockname'] . '-block';
        $js = '../../js/' . $editor_script . '.js';

        wp_register_script(
            $editor_script,
            plugins_url($js, __FILE__),
            array(
                'RRZE-Gutenberg',
            ),
            null
        );
        wp_localize_script($editor_script, $mySettings['block']['blockname'] . 'Config', $mySettings);

        // register block
        register_block_type($mySettings['block']['blocktype'], array(
            'editor_script' => $editor_script,
            'render_callback' => [$this, 'shortcode_contact'],
            'attributes' => $mySettings,
        )
        );
    }

}
