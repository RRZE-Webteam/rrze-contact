<?php

namespace RRZE\Contact\Shortcode;

use function RRZE\Contact\Config\getShortcodeSettings;
use function RRZE\Contact\Config\getShortcodeDefaults;
use RRZE\Contact\API\UnivIS;
use RRZE\Contact\Data;

defined('ABSPATH') || exit;

/**
 * Define Shortcodes
 */
class Contact extends Shortcode
{
    public $pluginFile = '';
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings();
        add_action('init', [$this, 'initGutenberg']);
    }

    public function onLoaded()
    {
        add_shortcode('contact', [$this, 'shortcode_contact']);
        add_shortcode('contact', [$this, 'shortcode_contact']);
        add_shortcode('contactliste', [$this, 'shortcode_contactlist']);
        add_shortcode('contacts', [$this, 'shortcode_contactlist']);
    }

    public function shortcode_contact($atts, $content = null)
    {

        // echo '<pre>';
        // var_dump($this->settings);
        // exit;

        $atts = shortcode_atts(getShortcodeDefaults($this->settings['contact']), $atts);

        $displayfield = Data::get_display_field($atts['format'], $atts['show'], $atts['hide']);

        if (!empty($atts['category'])) {
            return $this->shortcode_contactlist($atts, $content);
        }

        if (!empty($atts['id'])) {
            wp_enqueue_style('rrze-contact');

            $class = 'rrze-contact';
            if ($atts['class']) {
                $class .= ' ' . esc_attr($atts['class']);
            }
            if (!empty($displayfield['border'])) {
                if ($displayfield['border']) {
                    $class .= ' border';
                } else {
                    $class .= ' noborder';
                }
            }
            if (!empty($atts['background'])) {
                $bg_array = array('grau', 'fau', 'phil', 'med', 'nat', 'tf', 'rw');
                if (in_array($atts['background'], $bg_array)) {
                    $class .= ' background-' . esc_attr($atts['background']);
                }
            }
            $format = '';
            if (!empty($atts['format'])) {
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

            $list_ids = array_map('trim', explode(',', $atts['id']));
            $number = count($list_ids);
            $i = 1;
            foreach ($list_ids as $value) {
                $post = get_post($value);
                if ($post && $post->post_type == 'contact') {

                    switch ($format) {
                        case 'liste':
                            $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $atts);
                            if (!empty($thisentry)) {
                                $content .= $thisentry;
                            }
                            break;
                        case 'name':
                        case 'shortlist':
                            $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $atts);
                            if (!empty($thisentry)) {
                                $content .= $thisentry;
                                if ($i < $number) {
                                    $content .= ", ";
                                }
                            }
                            break;

                        case 'table':
                            $content .= Data::RRZE_Contact_tablerow($value, $displayfield, $atts);
                            break;
                        case 'page':
                            $content .= Data::RRZE_Contact_page($value, $displayfield, $atts, true);
                            break;
                        case 'sidebar':
                            $content .= Data::RRZE_Contact_sidebar($value, $displayfield, $atts);
                            break;
                        case 'card':
                            $content .= Data::RRZE_Contact_card($value, $displayfield, $atts);
                            break;

                        default:
                            $content .= Data::RRZE_Contact_markup($value, $displayfield, $atts);}
                    $i++;

                } else {
                    $content .= sprintf(__('No contact entry could be found with the specified ID %s.', 'rrze-contact'), $value);
                }

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

            return $content;
        }
    }

    public function shortcode_contactlist($atts, $content = null)
    {
        $atts = shortcode_atts(getShortcodeDefaults($this->settings['contactlist']), $atts);

        $displayfield = Data::get_display_field($atts['format'], $atts['show'], $atts['hide']);
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

            if (isset($displayfield['border'])) {
                if ($displayfield['border']) {
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
                        $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $atts);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                        }
                        break;
                    case 'name':
                    case 'shortlist':
                        $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $atts);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                            if ($i < $number) {
                                $content .= ", ";
                            }
                        }
                        break;

                    case 'table':
                        $content .= Data::RRZE_Contact_tablerow($value, $displayfield, $atts);
                        break;
                    case 'page':
                        $content .= Data::RRZE_Contact_page($value, $displayfield, $atts, true);
                        break;
                    case 'sidebar':
                        $content .= Data::RRZE_Contact_sidebar($value, $displayfield, $atts);
                        break;
                    case 'card':
                        $content .= Data::RRZE_Contact_card($value, $displayfield, $atts);
                        break;
                    default:
                        $content .= Data::RRZE_Contact_markup($value, $displayfield, $atts);
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

        $mySettings = $this->settings['contact'];
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
