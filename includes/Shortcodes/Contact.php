<?php

namespace RRZE_Contact\Shortcodes;

use function RRZE_Contact\Config\getShortcodeDefaults;
use function RRZE_Contact\Config\getShortcodeSettings;
use RRZE_Contact\Data;
use RRZE_Contact\Main;

defined('ABSPATH') || exit;

/**
 * Define Shortcodes
 */
class Contact extends Shortcodes
{
    public $pluginFile = '';
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings();
        $this->settings = $this->settings['contact'];
        add_action('init', [$this, 'initGutenberg']);
    }

    public function onLoaded()
    {
        add_shortcode('kontakt', [$this, 'shortcode_contact']);
        add_shortcode('contact', [$this, 'shortcode_contact']);
        add_shortcode('kontaktliste', [$this, 'shortcode_contactlist']);
        add_shortcode('contacts', [$this, 'shortcode_contactlist']);
    }

    public static function shortcode_kontakt($atts, $content = null)
    {
        $defaults = getShortcodeDefaults('contact');
        $arguments = shortcode_atts($defaults, $atts);
        $arguments = self::translate_parameters($arguments);
        $displayfield = Data::get_display_field($arguments['format'], $arguments['show'], $arguments['hide']);

        // extract(shortcode_atts($defaults, $atts));

        if ((isset($arguments['category'])) && (!empty($arguments['category']))) {
            return self::shortcode_contactlist($atts, $content);
        }

        $id = 0;
        if (isset($arguments['id'])) {
            $id = $arguments['id'];
        }
        $slug = '';
        if (isset($arguments['slug'])) {
            $slug = $arguments['slug'];
        }

        if (empty($id)) {
            if (empty($slug)) {
                // return '<div class="alert alert-danger">' . sprintf(__('Please specify the contact's titel.', 'rrze-contact'), $slug) . '</div>';
            } else {
                $posts = get_posts(array('name' => $slug, 'post_type' => 'contact', 'post_status' => 'publish'));
                if ($posts) {
                    $post = $posts[0];
                    $id = $post->ID;
                } else {
                    return '<div class="alert alert-danger">' . sprintf(__('No entry could be found with the provided title "%s". Try specifying the ID of the contact entry instead.', 'rrze-contact'), $slug) . '</div>';
                }
            }
        }

        if (!empty($id)) {
            Main::enqueueForeignThemes();

            $class = 'rrze-contact';
            if ($arguments['class']) {
                $class .= ' ' . esc_attr($arguments['class']);
            }
            if (isset($displayfield['border'])) {
                if ($displayfield['border']) {
                    $class .= ' border';
                } else {
                    $class .= ' noborder';
                }
            }
            if (isset($arguments['background']) && (!empty($arguments['background']))) {
                $bg_array = array('grau', 'fau', 'phil', 'med', 'nat', 'tf', 'rw');
                if (in_array($arguments['background'], $bg_array)) {
                    $class .= ' background-' . esc_attr($arguments['background']);
                }
            }
            $format = '';
            if (isset($arguments['format'])) {
                $format = $arguments['format'];
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

            $list_ids = array_map('trim', explode(',', $id));
            $number = count($list_ids);
            $i = 1;
            foreach ($list_ids as $value) {
                $post = get_post($value);
                if ($post && $post->post_type == 'contact') {

                    switch ($format) {
                        case 'liste':
                            $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $arguments);
                            if (!empty($thisentry)) {
                                $content .= $thisentry;
                            }
                            break;
                        case 'name':
                        case 'shortlist':
                            $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $arguments);
                            if (!empty($thisentry)) {
                                $content .= $thisentry;
                                if ($i < $number) {
                                    $content .= ", ";
                                }
                            }
                            break;

                        case 'table':
                            $content .= Data::RRZE_Contact_tablerow($value, $displayfield, $arguments);
                            break;
                        case 'page':
                            $content .= Data::RRZE_Contact_page($value, $displayfield, $arguments, true);
                            break;
                        case 'sidebar':
                            $content .= Data::RRZE_Contact_sidebar($value, $displayfield, $arguments);
                            break;
                        case 'card':
                            $content .= Data::RRZE_Contact_card($value, $displayfield, $arguments);
                            break;

                        default:
                            $content .= Data::RRZE_Contact_markup($value, $displayfield, $arguments);}
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

    public static function shortcode_contactlist($atts, $content = null)
    {

        $defaults = getShortcodeDefaults('contactlist');
        $arguments = shortcode_atts($defaults, $atts);
        $arguments = self::translate_parameters($arguments);
        $displayfield = Data::get_display_field($arguments['format'], $arguments['show'], $arguments['hide']);
        $limit = (!empty($atts['unlimited']) ? -1 : 100);

        if (isset($arguments['category'])) {
            $category = get_term_by('slug', $arguments['category'], 'contacts_category');
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
            if ($arguments['class']) {
                $class .= ' ' . esc_attr($arguments['class']);
            }

            if (isset($displayfield['border'])) {
                if ($displayfield['border']) {
                    $class .= ' border';
                } else {
                    $class .= ' noborder';
                }
            }

            if (isset($arguments['background']) && (!empty($arguments['background']))) {
                $bg_array = array('grau', 'fau', 'phil', 'med', 'nat', 'tf', 'rw');
                if (in_array($arguments['background'], $bg_array)) {
                    $class .= ' background-' . esc_attr($arguments['background']);
                }
            }
            $format = '';

            if (isset($arguments['format'])) {
                $format = $arguments['format'];
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

            $posts = Data::sort_contact_posts($posts, $arguments['sort'], $arguments['order']);

            foreach ($posts as $value) {
                switch ($format) {
                    case 'liste':
                        $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $arguments);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                        }
                        break;
                    case 'name':
                    case 'shortlist':
                        $thisentry = Data::RRZE_Contact_shortlist($value, $displayfield, $arguments);
                        if (!empty($thisentry)) {
                            $content .= $thisentry;
                            if ($i < $number) {
                                $content .= ", ";
                            }
                        }
                        break;

                    case 'table':
                        $content .= Data::RRZE_Contact_tablerow($value, $displayfield, $arguments);
                        break;
                    case 'page':
                        $content .= Data::RRZE_Contact_page($value, $displayfield, $arguments, true);
                        break;
                    case 'sidebar':
                        $content .= Data::RRZE_Contact_sidebar($value, $displayfield, $arguments);
                        break;
                    case 'card':
                        $content .= Data::RRZE_Contact_card($value, $displayfield, $arguments);
                        break;
                    default:
                        $content .= Data::RRZE_Contact_markup($value, $displayfield, $arguments);
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

    // Copies old direkt paraneters of the shortcode into show/hide-Parameter
    private static function translate_parameters($arguments)
    {
        if (!isset($arguments)) {
            return;
        }
        $show = '';
        if (isset($arguments['show'])) {
            $show = $arguments['show'];
        }
        $hide = '';
        if (isset($arguments['hide'])) {
            $hide = $arguments['hide'];
        }

        // First we copy arguments, that stay as they was
        $validpars = 'id, slug, category, hstart, class, sort, format, order, background';

        $oldargs = explode(',', $validpars);
        foreach ($oldargs as $value) {
            $key = esc_attr(trim($value));
            if ((!empty($key)) && (isset($arguments[$key]))) {
                $res[$key] = $arguments[$key];
            }
        }

        $oldparams = 'showlink, showfax, showwebsite, showaddress, showroom, showdescription, showthumb, showoffice, showtitle, showsuffix, showposition,showinstitution,showabteilung,showmail,showtelefon,showmobile,showborder';
        $oldargs = explode(',', $oldparams);
        foreach ($oldargs as $value) {
            $key = esc_attr(strtolower(trim($value)));
            $key = preg_replace('/^show/', '', $key);
            if ((!empty($key)) && (isset($arguments[$key]))) {
                if (($arguments[$key] == 1)
                    || ($arguments[$key] == "ja")
                    || ($arguments[$key] == "true")
                    || ($arguments[$key] == "+")
                    || ($arguments[$key] == "x")) {

                    if (!empty($show)) {
                        $show .= ', ' . $key;
                    } else {
                        $show = $key;
                    }

                } elseif (($arguments[$key] == 0)
                    || empty($arguments[$key])
                    || ($arguments[$key] == "-")
                    || ($arguments[$key] == "nein")
                    || ($arguments[$key] == "false")
                    || ($arguments[$key] == "no")) {

                    if (!empty($hide)) {
                        $hide .= ', ' . $key;
                    } else {
                        $hide = $key;
                    }
                }
            }
        }
        if (!empty($show)) {
            $res['show'] = $show;
        } else {
            $res['show'] = '';
        }
        if (!empty($hide)) {
            $res['hide'] = $hide;
        } else {
            $res['hide'] = '';
        }

        $format = '';
        if (isset($arguments['format'])) {
            $format = $arguments['format'];
        } else {
            if (isset($arguments['shortlist']) && ($arguments['shortlist'])) {
                $format = 'shortlist';
            } elseif (isset($arguments['page']) && ($arguments['page'])) {
                $format = 'page';
            } elseif (isset($arguments['list']) && ($arguments['list'])) {
                $format = 'liste';
            } elseif (isset($arguments['sidebar']) && ($arguments['sidebar'])) {
                $format = 'sidebar';
            } elseif (isset($arguments['compactindex']) && ($arguments['compactindex'])) {
                $format = 'kompakt';
            }
        }
        if (!empty($format)) {
            $res['format'] = $format;
        }

        return $res;
    }

    public function fillGutenbergOptions()
    {
        // we don't need slug because we have id
        unset($this->settings['slug']);

        // fill select "id"
        $this->settings['id']['field_type'] = 'select';
        $this->settings['id']['default'] = '';
        $this->settings['id']['type'] = 'string';
        $this->settings['id']['items'] = array('type' => 'text');
        $this->settings['id']['values'] = array();
        $this->settings['id']['values'][] = ['id' => '', 'val' => __('-- All --', 'rrze-contact')];

        $aContact = get_posts(array('posts_per_page' => -1, 'post_type' => 'contact', 'orderby' => 'title', 'order' => 'ASC'));
        foreach ($aContact as $contact) {
            $this->settings['id']['values'][] = [
                'id' => $contact->ID,
                'val' => str_replace("'", "", str_replace('"', "", $contact->post_title)),
            ];
        }

        // fill select "category"
        $this->settings['category']['field_type'] = 'select';
        $this->settings['category']['default'] = '';
        $this->settings['category']['type'] = 'string';
        $this->settings['category']['items'] = array('type' => 'text');
        $this->settings['category']['values'] = array();
        $this->settings['category']['values'][] = ['id' => '', 'val' => __('-- Alle --', 'rrze-contact')];

        $aTerms = get_terms(array('taxonomy' => 'contacts_category', 'hide_empty' => false));
        foreach ($aTerms as $term) {
            $this->settings['category']['values'][] = [
                'id' => $term->slug,
                'val' => html_entity_decode($term->name),
            ];
        }

        return $this->settings;
    }

    public function initGutenberg()
    {
        if (!$this->isGutenberg()) {
            return;
        }

        // get prefills for dropdowns
        $this->settings = $this->fillGutenbergOptions();

        // register js-script to inject php config to call gutenberg lib
        $editor_script = $this->settings['block']['blockname'] . '-block';
        $js = '../../js/' . $editor_script . '.js';

        wp_register_script(
            $editor_script,
            plugins_url($js, __FILE__),
            array(
                'RRZE-Gutenberg',
            ),
            null
        );
        wp_localize_script($editor_script, $this->settings['block']['blockname'] . 'Config', $this->settings);

        // register block
        register_block_type($this->settings['block']['blocktype'], array(
            'editor_script' => $editor_script,
            'render_callback' => [$this, 'shortcode_kontakt'],
            'attributes' => $this->settings,
        )
        );
    }

}
