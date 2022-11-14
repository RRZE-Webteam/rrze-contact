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
    private $pluginSettings;
    private $lastTitle = '';

    public function __construct($pluginFile, $pluginSettings)
    {
        $this->pluginSettings = (array) $pluginSettings;
        $this->pluginSettings = $this->pluginSettings['options'];
        $this->pluginFile = $pluginFile;
        $this->settings = getShortcodeSettings('contact');
        add_action('init', [$this, 'initGutenberg']);
    }

    public function onLoaded()
    {
        add_shortcode('contact', [$this, 'shortcode_contact']);
        // add_shortcode('consultation', [$this, 'shortcode_contact']); // next feature

        // remove_filter('the_content', 'wpautop'); // still needed?
    }

    private function getCSSClass($class, $border, $background)
    {
        return 'rrze-contact' . (!empty($class) ? ' ' . esc_attr($class) : '') . ' ' . $border . (in_array($background, getConstants('bgColors')) ? 'background-' . esc_atts($background) : '');
    }

    private function getPostIDsByCategory($category)
    {
        $aRet = [];
        $aCategory = get_term_by('slug', $category, 'contacts_category', ARRAY_A);

        if (!empty($aCategory['term_id'])) {
            $aRet = get_posts([
                'post_type' => 'contact',
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
                'fields' => 'ids',
                'suppress_filters' => false,
                'tax_query' => [[
                    'taxonomy' => 'contacts_category',
                    'field' => 'term_id',
                    'terms' => $aCategory['term_id'],
                ]],
                'nopaging' => true,
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

    private function makeCollapseTitle(&$data, &$group)
    {
        $ret = '?';

        switch ($group) {
            case 'organigram':
                $ret = (!empty($data['positionGroup']) ? $data['positionGroup'] : '.');
                break;
            case 'a-z':
            default:
                if (!empty($data['familyName'])) {
                    $ret = strtoupper(substr($data['familyName'], 0, 1));
                }
                break;
        }

        return $ret;
    }

    private function makeAccordion(&$data, &$i, &$max, &$tite, &$group)
    {
        $data['accordion'] = true;
        $data['collapsibles_start'] = ($i == 1 ? true : false);
        $data['collapsibles_end'] = ($i < $max ? false : true);
        $data['collapse_title'] = $this->makeCollapseTitle($data, $group);
        $data['collapse_start'] = ($data['collapse_title'] ? true : false);
        $data['collapse_end'] = ($data['collapse_start'] && $i > 1 ? true : false);

        return $data;
    }

    public function shortcode_contact($atts, $content = null)
    {
        wp_enqueue_style('rrze-contact');

        $atts = shortcode_atts(getShortcodeDefaults($this->settings), $atts);

        $aDisplayfields = $this->getDisplayFields($this->settings, $atts['format'], $atts['show'], $atts['hide']);

        // we must check if given format exists because format.html as template will be used
        if (!in_array($atts['format'], $this->aAllFormats)) {
            return __('Unknown format', 'rrze-contact') . ' "' . $atts['format'] . '"';
        }

        $class = self::getCSSClass($atts['class'], (in_array('border', $aDisplayfields) ? 'border' : 'noborder'), $atts['background']);

        if (!empty($atts['category'])) {
            $aPostIDs = $this->getPostIDsByCategory($atts['category']);
        } elseif (!empty($atts['id'])) {
            $aPostIDs = array_map('trim', explode(',', $atts['id']));
        } else {
            return __('id or category is needed', 'rrze-contact');
        }


        $aData = [];
        $iMax = 0;
        foreach ($aPostIDs as $postID) {
            $data = Data::getContactData($postID, $aDisplayfields, $this->pluginSettings);
            if (!empty($data)){
                $data['class'] = $class;
                $aData[] = $data;
                $iMax++;    
            }
        }


        if (!empty($atts['accordion'])) {
            $aTmp = [];

            foreach($aData as $data){
                $aTmp[$this->makeCollapseTitle($data, $atts['accordion'])][] = $data;
            }

            $aData = $aTmp;
            array_multisort(array_keys($aData), SORT_NATURAL | SORT_FLAG_CASE, $aData);

            $aTmp = [];
            foreach($aData as $title => $aEntries){
                $i = 1;

                foreach($aEntries as $nr => $data){
                    $data['accordion'] = true;
                    $data['collapsibles_start'] = ($nr == 0 ? true : false);
                    $data['collapse_title'] = ($nr == 0 ? $title : false);
                    $data['collapsibles_end'] = ($i < $iMax ? false : true);
                    $data['collapse_start'] = ($data['collapse_title'] ? true : false);
                    $data['collapse_end'] = ($i == count($aEntries) ? true : false);                    
                    $aTmp[] = $data;        
                    $i++;
                }
            }
            $aData = $aTmp;
        }

        $template = 'shortcodes/contact/' . $atts['format'] . '.html';

        foreach ($aData as $data) {
            $content .= Template::getContent($template, $data);
        }

        $content = do_shortcode($content);

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
