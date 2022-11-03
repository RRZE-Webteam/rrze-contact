<?php

namespace RRZE\Contact\Shortcode;

use function RRZE\Contact\Config\getShortcodeSettings;

// use function RRZE_Contact\Config\getShortcodeDefaults;
// use function RRZE_Contact\Config\getShortcodeSettings;
// use RRZE_Contact\Data;

defined('ABSPATH') || exit;

/**
 * Define Shortcodes for Standort Custom Type
 */
class Location extends Shortcode
{
    protected $pluginFile;
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings('location');
        $this->settings = $this->settings['location'];
        add_action('init', [$this, 'initGutenberg']);
    }

    public function onLoaded()
    {
        add_shortcode('location', [$this, 'shortcode_location']);
        add_shortcode('location', [$this, 'shortcode_location']);
    }

    public static function shortcode_location($atts, $content = null)
    {
        $defaults = getShortcodeDefaults('location');
        extract(shortcode_atts($defaults, $atts));

        switch ($format) {
            case 'name':
            case 'shortlist':
                $display = 'title, permalink';
                break;
            case 'full':
            case 'page':
                $display = 'title, telefon, email, fax, url, content, adresse, bild, permalink';
                break;
            case 'liste':
                $display = 'title, telefon, email, fax, url, kurzbeschreibung, permalink';
                break;
            case 'sidebar':
                $display = 'title, telefon, email, fax, url, adresse, bild, permalink';
                break;
            default:
                $display = 'title, telefon, email, fax, url, adresse, bild, permalink';
        }
        $adisplay = array_map('trim', explode(',', $display));
        $showfields = array();
        foreach ($adisplay as $val) {
            $showfields[$val] = 1;
        }
        if (isset($titletag)) {
            $titletag = sanitize_html_class($titletag);
        }
        if (isset($hstart)) {
            // hstart überschreibt titletag, wenn gesetzt
            $hstart = intval($hstart);

            if (($hstart < 1) || ($hstart > 6)) {
                $hstart = 2;
            }
            $titletag = 'h' . $hstart;
        }

        //Wenn neue Felder dazukommen, hier die Anzeigeoptionen auch mit einstellen
        if (!empty($show)) {
            $show = array_map('trim', explode(',', $show));
            if (in_array('kurzbeschreibung', $show)) {
                $showfields['kurzbeschreibung'] = true;
            }

            if (in_array('adresse', $show)) {
                $showfields['adresse'] = true;
            }

            if (in_array('bild', $show)) {
                $showfields['bild'] = true;
            }

            if (in_array('title', $show)) {
                $showfields['title'] = true;
            }

            if (in_array('email', $show)) {
                $showfields['email'] = true;
            }

            if (in_array('telephone', $show)) {
                $showfields['telephone'] = true;
            }

            if (in_array('faxNumber', $show)) {
                $showfields['faxNumber'] = true;
            }

            if (in_array('url', $show)) {
                $showfields['url'] = true;
            }

            if (in_array('content', $show)) {
                $showfields['content'] = true;
            }

            if (in_array('permalink', $show)) {
                $showfields['permalink'] = true;
            }

        }
        if (!empty($hide)) {
            $hide = array_map('trim', explode(',', $hide));
            if (in_array('kurzbeschreibung', $hide)) {
                $showfields['kurzbeschreibung'] = false;
            }

            if (in_array('adresse', $hide)) {
                $showfields['adresse'] = false;
            }

            if (in_array('bild', $hide)) {
                $showfields['bild'] = false;
            }

            if (in_array('title', $hide)) {
                $showfields['title'] = false;
            }

            if (in_array('email', $hide)) {
                $showfields['email'] = false;
            }

            if (in_array('telephone', $hide)) {
                $showfields['telephone'] = false;
            }

            if (in_array('faxNumber', $hide)) {
                $showfields['faxNumber'] = false;
            }

            if (in_array('url', $hide)) {
                $showfields['url'] = false;
            }

            if (in_array('content', $hide)) {
                $showfields['content'] = false;
            }

            if (in_array('permalink', $hide)) {
                $showfields['permalink'] = false;
            }

        }

        if (empty($id)) {
            if (empty($slug)) {
                return '<div class="alert alert-danger">' . sprintf(__('Please provide the Title or ID of the location listing.', 'rrze-contact'), $slug) . '</div>';
            } else {
                $posts = get_posts(array('name' => $slug, 'post_type' => 'location', 'post_status' => 'publish'));
                if ($posts) {
                    $post = $posts[0];
                    $id = $post->ID;
                } else {
                    return '<div class="alert alert-danger">' . sprintf(__('No location entry could be found with the specified title "%s". Try specifying the ID of the location record instead.', 'rrze-contact'), $slug) . '</div>';
                }
            }
        }

        if (!empty($id)) {
            if (is_numeric($id)) {
                return Data::create_fau_location($id, $showfields, $titletag);
            }

            $list_ids = array_map('trim', explode(',', $id));
            $number = count($list_ids);
            $output = '';

            $i = 1;
            foreach ($list_ids as $value) {
                if (is_numeric($value)) {
                    $post = get_post($value);
                    if ($post && $post->post_type == 'location') {

                        switch ($format) {
                            case 'name':
                                $thisout = Data::create_fau_location_plain($value, $showfields);
                                if (!empty($thisout)) {
                                    $output .= $thisout;
                                    if ($i < $number) {
                                        $output .= ", ";
                                    }

                                    $i++;
                                }
                                break;
                            case 'shortlist':
                                $thisout = Data::create_fau_location_plain($value, $showfields);
                                if (!empty($thisout)) {
                                    $output .= "<li>" . $thisout . "</li>";
                                    $i++;
                                }
                                break;
                            case 'liste':
                                $thisout = Data::create_fau_location($value, $showfields, $titletag);
                                if (!empty($thisout)) {
                                    $output .= "<li>" . $thisout . "</li>";
                                    $i++;
                                }
                                break;
                            default:
                                $thisout = Data::create_fau_location($value, $showfields, $titletag);
                                if (!empty($thisout)) {
                                    $output .= $thisout;
                                    $i++;
                                }
                        }
                    }
                }
            }
            if (($format == 'liste') || ($format == 'shortlist')) {
                $content = '<div class="rrze-contact location">';
                $content .= '<ul>' . $output . '</ul>';
                $content .= '</div>';
                return $content;
            } elseif ($format == 'name') {
                $content = '<div class="rrze-contact location">';
                $content .= '<p>' . $output . '</p>';
                $content .= '</div>';
                return $content;
            }

            return $output;

        }
    }

    public function fillGutenbergOptions()
    {
        // we don't need slug because we have id
        unset($this->settings['slug']);

        // fill select "id"
        $this->settings['id']['field_type'] = 'select';
        $this->settings['id']['default'] = 0;
        $this->settings['id']['type'] = 'string';
        $this->settings['id']['items'] = array('type' => 'text');
        $this->settings['id']['values'] = array();
        $this->settings['id']['values'][] = ['id' => 0, 'val' => __('-- All --', 'rrze-contact')];

        $aContact = get_posts(array('posts_per_page' => -1, 'post_type' => 'contact', 'orderby' => 'title', 'order' => 'ASC'));
        foreach ($aContact as $contact) {
            $this->settings['id']['values'][] = [
                'id' => $contact->ID,
                'val' => str_replace("'", "", str_replace('"', "", $contact->post_title)),
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
            'render_callback' => [$this, 'shortcode_location'],
            'attributes' => $this->settings,
        )
        );
    }
}
