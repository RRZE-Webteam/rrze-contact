<?php

namespace RRZE\Contact;

use function RRZE\Contact\Config\getFields;
use RRZE\Contact\API\UnivIS;

defined('ABSPATH') || exit;

class Data
{

    private static function get_viewsettings($lookup = 'constants')
    {
        $settings = new Settings(__DIR__);
        $settings->onLoaded();
        $options = $settings->options;

        $viewopt = array();

        foreach ($options as $section => $field) {
            if ($lookup == 'sidebar') {
                if (substr($section, 0, 7) === 'sidebar') {
                    $keyname = preg_replace('/sidebar_/i', '', $section);
                    $viewopt[$keyname] = $options[$section];
                }
            } else {
                if (substr($section, 0, 9) === 'constants') {
                    $keyname = preg_replace('/constants_/i', '', $section);
                    $viewopt[$keyname] = $options[$section];
                }
            }
        }
        return $viewopt;
    }

    public static function get_contactdata($connection = 0)
    {
        $args = array(
            'post_type' => 'contact',
            'numberposts' => -1,
            'meta_key' => 'rrze_contact_typ',
        );

        $contactlist = get_posts($args);

        if ($contactlist) {
            foreach ($contactlist as $key => $value) {
                $contactlist[$key] = (array) $contactlist[$key];
                $name = $contactlist[$key]['post_title'];
                switch (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_typ', true)) {
                    case 'realcontact':
                    case 'realmale':
                    case 'realfemale':
                        if (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_familyName', true)) {
                            $lastname = get_post_meta($contactlist[$key]['ID'], 'rrze_contact_familyName', true);
                            if (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_firstName', true)) {
                                $name = $lastname . ', ' . get_post_meta($contactlist[$key]['ID'], 'rrze_contact_firstName', true);
                            } elseif (ltrim(strpos($name, $lastname))) {
                                $name = $lastname . ', ' . ltrim(str_replace($lastname, '', $name));
                            } else {
                                $name = $lastname;
                            }
                        } else {
                            if (ltrim(strpos($name, ' '))) {
                                $lastname = ltrim(strrchr($name, ' '));
                                $firstName = ltrim(str_replace($lastname, '', $name));
                                $name = $lastname . ', ' . $firstName;
                            }
                        }
                        break;
                    default:
                        break;
                }
                $temp[$contactlist[$key]['ID']] = $name;
            }
            natcasesort($temp);

            foreach ($temp as $key => $value) {
                $contactselect[$key] = $key . ': ' . $value;
            }
            // Für Auswahlfeld bei verknüpften Kontakten
            if ($connection) {
                $contactselect = array('0' => __('Kein Kontakt ausgewählt.', 'rrze-contact')) + $contactselect;
            }
        } else {
            // falls noch keine Kontakte vorhanden sind
            $contactselect[0] = __('Noch keine Kontakte eingepflegt.', 'rrze-contact');
        }
        return $contactselect;
    }

    public static function get_standortdata()
    {
        $args = array(
            'post_type' => 'location',
            'numberposts' => -1,
        );

        $standortlist = get_posts($args);
        if ($standortlist) {
            foreach ($standortlist as $key => $value) {
                $standortlist[$key] = (array) $standortlist[$key];
                $standortselect[$standortlist[$key]['ID']] = $standortlist[$key]['post_title'];
            }
            asort($standortselect);
            $standortselect = array('0' => __('Kein Standort ausgewählt.', 'rrze-contact')) + $standortselect;
        } else {
            $standortselect[0] = __('Noch kein Standort eingepflegt.', 'rrze-contact');
        }
        return $standortselect;
    }

    public static function get_default_rrze_contact_typ()
    {
        if (isset($_GET["rrze_contact_typ"]) && $_GET["rrze_contact_typ"] == 'einrichtung') {
            $default_rrze_contact_typ = 'einrichtung';
        } else {
            $default_rrze_contact_typ = 'realcontact';
        }
        return $default_rrze_contact_typ;
    }

    //gibt die Werte des Standorts an, für Standort-Synchronisation $edfaults=1
    public static function get_fields_standort($id, $standort_id, $defaults)
    {
        $standort_sync = 0;
        $fields = array();
        if ($standort_id) {
            $standort_sync = 1;
        }
        $fields_standort = array(
            'streetAddress' => '',
            'postalCode' => '',
            'addressLocality' => '',
            'addressCountry' => '',
        );

        foreach ($fields_standort as $key => $value) {
            if ($standort_sync) {
                $value = self::sync_standort($id, $standort_id, $key, $defaults);
            } else {
                if ($defaults) {
                    $value = '<p class="cmb2-metabox-description">' . __('Im Standort ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
                } elseif ($id) {
                    $value = get_post_meta($id, 'rrze_contact_' . $key, true);
                }
            }
            $fields[$key] = $value;
        }
        return $fields;
    }
    public static function get_more_link($targeturl, $screenreadertext = '', $class = 'contact-info-more', $withdiv = true, $linktitle = '')
    {
        if ((!isset($targeturl)) || empty($targeturl)) {
            return;
        }

        $viewopts = self::get_viewsettings();
        $res = '';

        if ($withdiv) {
            $res .= '<div ';
            if (!empty($class)) {
                $res .= 'class="' . esc_attr($class) . '"';
            }
            $res .= '>';
        }
        $res .= '<a href="' . esc_url($targeturl) . '"';
        if ($withdiv == false) {
            if (!empty($class)) {
                $res .= ' class="' . esc_attr($class) . '"';
            }
        } else {
            $res .= ' class="standard-btn primary-btn"';
        }
        if (!empty($linktitle)) {
            $res .= ' title="' . esc_attr($linktitle) . '"';
        }
        $res .= '>';
        if ((isset($viewopts['view_contact_linktext'])) && (!empty($viewopts['view_contact_linktext']))) {
            $res .= esc_html($viewopts['view_contact_linktext']);
        } else {
            $res .= __('Profil aufrufen', 'rrze-contact');
        }
        if (!empty($screenreadertext)) {
            $res .= ' <span class="screen-reader-text">' . $screenreadertext . '</span>';
        }
        $res .= '</a>';
        if ($withdiv) {
            $res .= '</div>';
        }
        return $res;
    }

    // $id = ID des Personeneintrags,
    // $standort_id = ID des Standorteintrags,
    // $rrze_contact_var = Bezeichnung des Feldes im Personenplugin,
    // $defaults = Default-Wert 1 für Ausgabe der hinterlegten Werte im Personeneingabeformular
    public static function sync_standort($id, $standort_id, $rrze_contact_var, $defaults)
    {
        $value = get_post_meta($standort_id, 'rrze_contact_' . $rrze_contact_var, true);
        //wird benötigt, falls jeder einzelne Wert abgefragt werden soll
        if ($defaults) {
            if (!empty($value)) {
                $val = '<p class="cmb2-metabox-description">' . __('Von Standort angezeigter Wert:', 'rrze-contact') . ' <code>' . $value . '</code></p>';
            } else {
                $val = '<p class="cmb2-metabox-description">' . __('Im Standort ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
            }
        } else {
            if (!empty($value) && get_post_meta($id, 'rrze_contact_standort_sync', true)) {
                $val = $value;
            } else {
                $val = get_post_meta($id, 'rrze_contact_' . $rrze_contact_var, true);
            }
        }
        return $val;
    }

    public static function get_standort_defaults($id = 0)
    {
        $post = get_post($id);
        if (!is_null($post) && $post->post_type === 'contact' && get_post_meta($id, 'rrze_contact_standort_id', true)) {
            $standort_id = get_post_meta($id, 'rrze_contact_standort_id', true);
            $standort_default = self::get_fields_standort($id, $standort_id, 1);
            return $standort_default;
        } else {
            return self::get_fields_standort(0, 0, 0);
        }
    }

    // Sortierung eines Arrays mit Objekten (z.B. bei einer Kategorie) alphabetisch nach Titel oder Nachname, je nach Typ
    public static function sort_contact_posts($contactlist, $sorttype = 'name', $order = 'asc')
    {
        if (is_array($contactlist)) {
            $temp = array();
            foreach ($contactlist as $key => $value) {
                if (is_int($value)) {
                    // value ist Post-ID
                    $id = $value;
                } else {
                    $id = $contactlist[$key]['ID'];
                }
                $fields = self::get_contact_data($id);

                // Bei Personen Prüfung, ob Nachname im Feld eingetragen ist (ggf. aus UnivIS), wenn nicht letztes Wort von Titel als Nachname angenommen
                switch ($fields['typ']) {
                    case 'realcontact':
                    case 'realmale':
                    case 'realfemale':

                        if (($sorttype == 'firstName') || ($sorttype == 'vorname') || ($sorttype == 'fullname')) {
                            $sortname = $fields['firstName'] . " - " . $fields['familyName'];
                        } elseif (($sorttype == 'familyName') || ($sorttype == 'nachname') || ($sorttype == 'name')) {
                            $sortname = $fields['familyName'] . " - " . $fields['firstName'];
                        } elseif ($sorttype == 'title') {
                            $sortname = $fields['contact_title'];
                        } elseif ($sorttype == 'sortierfeld') {
                            if (!empty($fields['sortField'])) {
                                $sortname = $fields['sortField'];
                            } else {
                                $sortname = "Z " . $fields['contact_title'];
                            }
                        } else {
                            $sortname = $fields['contact_title'];
                        }

                        break;
                    default:
                        if ($sorttype == 'sortierfeld') {
                            if (!empty($fields['sortField'])) {
                                $sortname = $fields['sortField'];
                            } else {
                                $sortname = "Z " . $fields['contact_title'];
                            }
                        } else {
                            $sortname = $fields['contact_title'];
                        }

                        break;
                }
                if (empty($sortname)) {
                    $sortname = $fields['contact_title'];
                }

                $temp[$id] = strtolower($sortname);
            }
            if ($order == 'desc') {
                arsort($temp);
            } else {
                asort($temp);
            }

            $result = array();
            foreach ($temp as $key => $string) {
                $result[] = $key;
            }
            return $result;
        }
    }

    public static function create_contact_image($id = 0, $size = 'contact-thumb-page-v3', $class = '', $defaultimage = false, $showlink = false, $linkttitle = '', $showcaption = true, $linktarget = '')
    {
        if ($id == 0) {
            return;
        }
        $res = '';
        $imagedata = array();
        $fields = self::get_contact_data($id);
        $alttext = esc_attr($fields['contact_title']);
        $targetlink = '';

        $imagedata['alt'] = $alttext;
        if ($showlink) {
            if ((!empty($linktarget)) && (!empty(esc_url($linktarget)))) {
                $targetlink = $linktarget;
            } else {
                $targetlink = get_permalink($id);
            }
        }

        if (has_post_thumbnail($id)) {
            $image_id = get_post_thumbnail_id($id);
            $imga = wp_get_attachment_image_src($image_id, $size);
            if (is_array($imga)) {
                $imgsrcset = wp_get_attachment_image_srcset($image_id, $size);
                $imgsrcsizes = wp_get_attachment_image_sizes($image_id, $size);
                $imagedata['src'] = $imga[0];
                $imagedata['width'] = $imga[1];
                $imagedata['height'] = $imga[2];
                $imagedata['srcset'] = $imgsrcset;
                $imagedata['sizes'] = $imgsrcsizes;
            }
            if ($showcaption) {
                $attachment = get_post($image_id);
                if (isset($attachment) && isset($attachment->post_excerpt)) {
                    $imagedata['caption'] = trim(strip_tags($attachment->post_excerpt));
                }
            }
        } elseif ($defaultimage) {
            $type = $fields['typ'];

            $pluginfile = __DIR__;

            if ($type == 'realmale') {
                $bild = plugin_dir_url($pluginfile) . 'images/platzhalter-mann.png';
            } elseif ($type == 'realfemale') {
                $bild = plugin_dir_url($pluginfile) . 'images/platzhalter-frau.png';
            } elseif ($type == 'einrichtung') {
                $bild = plugin_dir_url($pluginfile) . 'images/platzhalter-organisation.png';
            } else {
                $bild = plugin_dir_url($pluginfile) . 'images/platzhalter-unisex.png';
            }
            if ($bild) {
                $imagedata['src'] = $bild;
                $imagedata['width'] = 120;
                $imagedata['height'] = 160;
            }
        }
        $res = Schema::create_Image($imagedata, 'figure', 'image', $class, true, $targetlink, $linkttitle);
        return $res;
    }

    public static function get_morelink_url($data, $args)
    {
        $url = '';
        if ((isset($data['link'])) && (!empty(esc_url($data['link'])))) {
            $url = $data['link'];
        } elseif ((isset($data['permalink'])) && (!empty(esc_url($data['permalink'])))) {
            $url = $data['permalink'];
        } elseif ((isset($data['url'])) && (!empty(esc_url($data['url'])))) {
            $url = $data['url'];
        }
        if (isset($args['view_contact_linkname'])) {
            if (!empty($args['view_contact_linkname'])) {

                if ($args['view_contact_linkname'] == 'force-nolink') {
                    $url = '';
                } elseif ($args['view_contact_linkname'] == 'url') {
                    if ((isset($data['url'])) && (!empty(esc_url($data['url'])))) {
                        $url = $data['url'];
                    }
                } elseif ($args['view_contact_linkname'] == 'permalink') {
                    if ((isset($data['permalink'])) && (!empty(esc_url($data['permalink'])))) {
                        $url = $data['permalink'];
                    }
                } elseif ($args['view_contact_linkname'] == 'use-link') {
                    if ((isset($data['link'])) && (!empty(esc_url($data['link'])))) {
                        $url = $data['link'];
                    }
                }
            }
        }
        return $url;
    }




    // BK
    private static function getDescription(&$post, &$format){
        if (($format == 'sidebar') || ($format == 'kompakt')) {
            if (!empty($post['small_description'])) {
                return $post['small_description'];
            }
        }
        if (!empty($post['description'])) {
            return $post['description'];
        }
    
        // Fallback
        if (!empty($post['post_excerpt'])) {
            return $post['post_excerpt'];
        }else{
            return '';
        }
    }

    // BK
    public static function getContactData($postID, $aDisplayfields, $format = 'page')
    {
        $aRet = [];

        if (in_array('all', $aDisplayfields) || in_array('description', $aDisplayfields) || $format == 'page'){
            $post = get_post($postID, ARRAY_N);

            if (in_array('all', $aDisplayfields) || in_array('description', $aDisplayfields)){
                $desc = self::getDescription($post, $format);
                if (!empty($desc)) {
                    $aRet['description'] = $desc;
                }    
            }

            if (in_array('all', $aDisplayfields) || $format == 'page'){
                if (!empty($post['post_content'])) {
                    $aRet['post_content'] = $post['post_content'];
                }    
            }
        }

        if (in_array('all', $aDisplayfields) || in_array('permalink', $aDisplayfields)){
            $aRet['permalink'] = get_permalink($postID);
        }

        $aFields = getFields('contact');

        if (!empty($aFields)){
            $postMeta = get_post_meta($postID);

            foreach ($aFields as $aDetails) {
                if ((in_array('all', $aDisplayfields) || in_array($aDetails['name'], $aDisplayfields)) && !empty($postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0])) {
                    $aRet[$aDetails['name']] = $postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0];
                }
            }

            $aGroups = [];

            if (in_array('all', $aDisplayfields) || in_array('locations', $aDisplayfields)){
                $aGroups[] = 'locations';
            }

            if (in_array('all', $aDisplayfields) || in_array('consultations', $aDisplayfields)){
                $aGroups[] = 'consultations';
            }

            foreach ($aGroups as $group) {
                if (!empty($postMeta[RRZE_CONTACT_PREFIX . $group . 'Group'])) {
                    $aLocations = unserialize($postMeta[RRZE_CONTACT_PREFIX . $group . 'Group'][0]);
                    foreach ($aLocations as $location) {
                        $aTmp = [];
                        foreach ($location as $field => $value) {
                            $fieldName = substr($field, strlen(RRZE_CONTACT_PREFIX));
                            if (in_array('all', $aDisplayfields) || in_array($fieldName, $aDisplayfields)){
                                $aTmp[$fieldName] = $value;
                            }
                        }
                        $aRet[$group][] = $aTmp;
                    }
                }
            }
    
            if (in_array('all', $aDisplayfields) || in_array('consultations', $aDisplayfields)){
                if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'consultation_headline'][0])) {
                    $aRet['consultation_headline'] = $postMeta[RRZE_CONTACT_PREFIX . 'consultation_headline'][0];
                }
        
                if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'consultation_notice'][0])) {
                    $aRet['consultation_notice'] = $postMeta[RRZE_CONTACT_PREFIX . 'consultation_notice'][0];
                }
            }    

            if (in_array('all', $aDisplayfields) || in_array('socialmedia', $aDisplayfields)){
                $aFields = getFields('socialmedia');
                foreach ($aFields as $aDetails) {
                    if (!empty($postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0])) {
                        $aRet[$aDetails['name']] = $postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0];
                    }
                }
            }
        }

        return $aRet;
    }

    public static function rrze_contact_markup($postID, $display = array(), $arguments = array())
    {
        if ($postID == 0) {
            return;
        }


        // BK 2DO : getContactData() display-fields übergeben
        $fields = self::getContactData($postID);


        $viewopts = self::get_viewsettings();

        $content = '';
        // wp_enqueue_style('rrze-contact');

        // $fields['description'] = self::get_description($id, $arguments['format'], $fields);
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);

        $data = self::filter_fields($fields, $display);

        $fullname = Schema::create_Name($data, 'name', '', 'a', false, $viewopts);
        $hoursavailable_output = Schema::create_ContactPoint($data);

        $class = 'rrze-contact';
        if (isset($viewopts['view_thumb_size'])) {
            if ((isset($arguments['class'])) && (!empty($arguments['class'])) && (preg_match("/thumb\-size\-/i", $arguments['class']))) {
                // thumb-size ist in der Class bereits enthalten, daher ignoriere ich die globale Setting einstweile
                // und lass es über die Class individuell durch den Redakteuer steuern, der es als class eingibt
            } else {
                $class .= ' thumb-size-' . esc_attr($viewopts['view_thumb_size']);
            }
        }
        if ((isset($arguments['class'])) && (!empty($arguments['class']))) {
            $class .= ' ' . esc_attr($arguments['class']);
        }
        if (isset($display['border'])) {
            if ($display['border']) {
                $class .= ' border';
            }
        }
        if (isset($arguments['background']) && (!empty($arguments['background']))) {
            $bg_array = array('grau', 'fau', 'phil', 'med', 'nat', 'tf', 'rw');
            if (in_array($arguments['background'], $bg_array)) {
                $class .= ' background-' . esc_attr($arguments['background']);
            }
        }

        $content .= '<div class="' . $class . '" itemscope itemtype="http://schema.org/Person">' . "\n";

        $content .= '<div class="row">';

        if (isset($display['bild']) && (!empty($display['bild']))) {
            $content .= Data::create_contact_image($id, 'contact-thumb-v3', "contact-thumb", true, true, '', false);
            $content .= '<div class="contact-default-thumb">';
        } else {
            $content .= '<div class="contact-default">';
        }
        if ($arguments['hstart']) {
            $hstart = intval($arguments['hstart']);
        } else {
            $hstart = 2;
        }
        if (($hstart < 1) || ($hstart > 6)) {
            $hstart = 2;
        }

        $content .= '<h' . $hstart . '>';
        $content .= $fullname;
        $content .= '</h' . $hstart . '>';

        $datacontent = '';
        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] == 'nach-name') {
            $datacontent .= Schema::create_SocialMedialist($data);
        }

        if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
            && (isset($data['workLocation']) && (!empty($data['workLocation'])))
        ) {
            $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
        }

        if (isset($data['jobTitle']) && (!empty($data['jobTitle']))) {
            $datacontent .= '<span class="contact-info-position" itemprop="jobTitle">' . $data['jobTitle'] . '</span><br>';
        }
        $orgadata = array();
        if (isset($data['worksFor']) && (!empty($data['worksFor']))) {
            $orgadata['name'] = $data['worksFor'];
        }
        if (isset($data['department']) && (!empty($data['department']))) {
            $orgadata['department'] = $data['department'];
        }
        $datacontent .= Schema::create_Organization($orgadata, 'p', 'worksFor', '', false, false, false);

        if ((!isset($data['connection_only']))
            || ((isset($data['connection_only']) && $data['connection_only'] == false))
        ) {
            $adresse = Schema::create_PostalAdress($data, 'address', '', 'address', true);
            if (isset($adresse)) {
                $datacontent .= $adresse;
            }

            $datacontent .= Schema::create_contactpointlist($data, 'ul', '', 'contactlist', 'li', $viewopts);
        }
        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] !== 'nach-name') {
            $datacontent .= Schema::create_SocialMedialist($data);
        }

        if ((!empty($data['connection_text']) || !empty($data['connection_options']) || !empty($data['connections'])) && isset($display['ansprechpartner']) && $display['ansprechpartner'] == true) {
            $datacontent .= self::rrze_contact_connection($data['connection_text'], $data['connection_options'], $data['connections'], $hstart);
        }
        if (!empty($datacontent)) {
            $content .= '<div class="contact-info">';
            $content .= $datacontent;
            $content .= '</div>';
        }

        $morecontent = '';
        if (isset($display['sprechzeiten']) && $display['sprechzeiten'] == true) {
            if ((!isset($data['connection_only']))
                || ((isset($data['connection_only']) && $data['connection_only'] == false))
            ) {
                $morecontent .= Schema::create_ContactPoint($data);
            }
        }

        if (!empty($data['description']) && isset($display['description']) && (!empty($display['description']))) {
            $morecontent .= '<div class="contact-info-description" itemprop="description"><p>' . $data['description'] . '</p></div>' . "\n";
        }

        if (isset($display['link']) && (!empty($display['link']))) {
            $morelink = self::get_morelink_url($data, $viewopts);
            $screenreaderlink = __('Details zu', 'rrze-contact') . ' ' . Schema::create_NameforAttributs($data);
            $morecontent .= self::get_more_link($morelink, $screenreaderlink);
        }

        if (!empty($morecontent)) {
            $content .= '</div><div class="contact-default-more">'; // ende div class compactindex
            $content .= $morecontent;
        }

        $content .= '</div>'; // row

        $content .= '</div>';
        $content .= '</div>';
        return $content;
    }

    

    public static function rrze_contact_page($id, $display = array(), $arguments = array(), $is_shortcode = false)
    {
        $fields = self::get_contact_data($id);

        wp_enqueue_style('rrze-contact');
        $viewopts = self::get_viewsettings();
        if (empty($display)) {
            $display = self::get_display_field('page');
        }
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);
        $data = self::filter_fields($fields, $display);

        if ((isset($arguments['hstart'])) && (!empty($arguments['hstart']))) {
            $hstart = intval($arguments['hstart']);
        } else {
            $hstart = 2;
        }
        if (($hstart < 1) || ($hstart > 6)) {
            $hstart = 2;
        }

        $class = 'rrze-contact page';
        if ((isset($arguments['class'])) && (!empty($arguments['class']))) {
            $class .= ' ' . esc_attr($arguments['class']);
        }
        if (isset($display['border'])) {
            if ($display['border']) {
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

        $viewcaption = true;
        if (isset($viewopts['view_contact_page_imagecaption'])) {
            $viewcaption = $viewopts['view_contact_page_imagecaption'];
        }
        $use_size = 'contact-thumb-page-v3';
        if (isset($viewopts['view_contact_page_imagesize'])) {
            $use_size = $viewopts['view_contact_page_imagesize'];
        }
        if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
            && (isset($data['workLocation']) && (!empty($data['workLocation'])))
        ) {
            $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
        }

        $content = '<div class="' . $class . '" itemscope itemtype="http://schema.org/Person">';

        // if ( $is_shortcode) {
        $data['morelink'] = '';
        $content .= Schema::create_Name($data, 'name', '', 'h' . $hstart, false, $viewopts);
        // }
        $content .= '<div class="contact-meta">';
        $content .= Data::create_contact_image($id, $use_size, "contact-image alignright $use_size", false, false, '', $viewcaption);

        $content .= '<div class="contact-info">';

        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] == 'nach-name') {
            $content .= Schema::create_SocialMedialist($data);
        }

        if (isset($data['jobTitle']) && (!empty($data['jobTitle']))) {
            $content .= '<span class="contact-info-position" itemprop="jobTitle">' . $data['jobTitle'] . '</span><br>';
        }
        $orgadata = array();
        if (isset($data['worksFor']) && (!empty($data['worksFor']))) {
            $orgadata['name'] = $data['worksFor'];
        }
        if (isset($data['department']) && (!empty($data['department']))) {
            $orgadata['department'] = $data['department'];
        }
        $content .= Schema::create_Organization($orgadata, 'p', 'worksFor', '', false, false, false);

        if ((!isset($data['connection_only'])) ||
            ((isset($data['connection_only']) && $data['connection_only'] == false))
        ) {
            $adresse = Schema::create_PostalAdress($data, 'address', '', 'address', true);
            if (isset($adresse)) {
                $content .= $adresse;
            }

            $content .= Schema::create_contactpointlist($data, 'ul', '', 'contactlist', 'li', $viewopts);
        }
        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] !== 'nach-name') {
            $content .= Schema::create_SocialMedialist($data);
        }

        if ((!isset($data['connection_only'])) ||
            ((isset($data['connection_only']) && $data['connection_only'] == false))
        ) {
            $content .= Schema::create_ContactPoint($data, 'div', 'contactPoint', '', 'h3');
        }

        $content .= '</div>';
        $content .= '</div>';

        if (!empty($data['connection_text']) || !empty($data['connection_options']) || !empty($data['connections'])) {
            $content .= self::rrze_contact_connection($data['connection_text'], $data['connection_options'], $data['connections'], $hstart);
        }
        $postContent = $data['content'];

        if ($postContent) {
            $postContent = self::stripShortcode(['contact', 'contact', 'contactliste', 'contacts'], $postContent);
            $content .= '<div class="desc" itemprop="description">' . PHP_EOL;
            $content .= apply_filters('the_content', $postContent);
            $content .= '</div>';
        }
        $content .= '</div>';
        return $content;
    }

    protected static function stripShortcode(array $tagAry, string $content): string
    {
        global $shortcode_tags;

        foreach ($tagAry as $tag) {
            $stack = $shortcode_tags;
            $shortcode_tags = [$tag => 1];
            $content = strip_shortcodes($content);
            $shortcode_tags = $stack;
        }
        return $content;
    }

    public static function rrze_contact_tablerow($id = 0, $display = array(), $arguments = array())
    {
        if ($id == 0) {
            return;
        }
        $fields = self::get_contact_data($id);
        $viewopts = self::get_viewsettings();

        $content = '';
        wp_enqueue_style('rrze-contact');

        $fields['description'] = self::get_description($id, $arguments['format'], $fields);
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);

        $data = self::filter_fields($fields, $display);
        if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
            && (isset($data['workLocation']) && (!empty($data['workLocation'])))
        ) {
            $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
        }
        $content .= '<tr class="contact-info" itemscope itemtype="http://schema.org/Person">';
        $content .= '<td>' . Schema::create_Name($data, 'name', '', 'a', false, $viewopts) . '</td>';
        $content .= Schema::create_contactpointlist($data, '', '', '', 'td', $viewopts, true, false);

        $showorg = false;
        $orgadata = [];
        if (!empty($display['worksFor'])) {
            $showorg = true;
            if (isset($data['worksFor']) && (!empty($data['worksFor']))) {
                $orgadata['name'] = $fields['worksFor'];
            }
        }
        if (!empty($display['department'])) {
            $showorg = true;
            if (isset($data['department']) && (!empty($data['department']))) {
                $orgadata['department'] = $data['department'];
            }
        }
        if ($showorg) {
            $org = Schema::create_Organization($orgadata, 'td', 'worksFor', '', false, false, false);
            if (!empty($org)) {
                $content .= $org;
            } else {
                $content .= "<td>&nbsp;</td>";
            }
        }

        if (isset($display['description']) && $display['description']) {
            $content .= "<td>" . $data['description'] . '</td>';
        }
        if (isset($display['sprechzeiten']) && (!empty($display['sprechzeiten']))) {
            if ((!isset($data['connection_only'])) ||
                ((isset($data['connection_only']) && $data['connection_only'] == false))
            ) {
                $content .= '<td>' . Schema::create_ContactPoint($data) . '</td>';
            }
        }
        if (isset($display['socialmedia']) && $display['socialmedia']) {
            $content .= "<td>" . Schema::create_SocialMedialist($data) . '</td>';
        }

        $content .= '</tr>';

        return $content;
    }

    public static function rrze_contact_card($id = 0, $display = array(), $arguments = array())
    {
        if ($id == 0) {
            return;
        }
        $fields = self::get_contact_data($id);
        $viewopts = self::get_viewsettings();

        $content = '';
        wp_enqueue_style('rrze-contact');

        $fields['description'] = self::get_description($id, $arguments['format'], $fields);
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);

        $data = self::filter_fields($fields, $display);
        $class = 'card-item';
        if (isset($viewopts['view_card_size'])) {
            if ((isset($arguments['class'])) && (!empty($arguments['class'])) && (preg_match("/card\-/i", $arguments['class']))) {
                // thumb-size ist in der Class bereits enthalten, daher ignoriere ich die globale Setting einstweile
                // und lass es über die Class individuell durch den Redakteuer steuern, der es als class eingibt
                $class .= ' ' . esc_attr($arguments['class']);
            } else {
                $class .= ' card-' . esc_attr($viewopts['view_card_size']);
            }
        }

        $content .= '<div class="' . $class . '" itemscope itemtype="http://schema.org/Person">';

        if ((isset($display['bild'])) && (!empty($display['bild']))) {
            $thisurl = '';
            if (isset($viewopts['view_card_linkimage']) && $viewopts['view_card_linkimage'] == true) {
                if (isset($data['morelink'])) {
                    $thisurl = $data['morelink'];
                }
            }
            if ($thisurl) {
                $content .= Data::create_contact_image($id, 'medium', "contact-thumb", true, true, '', false, $thisurl);
            } else {
                $content .= Data::create_contact_image($id, 'medium', "contact-thumb", true, false, '', false);
            }
        }

        $fullname = Schema::create_Name($data, 'name', '', 'a', false, $viewopts);

        if ($arguments['hstart']) {
            $hstart = intval($arguments['hstart']);
        } else {
            $hstart = 3;
        }
        if (($hstart < 1) || ($hstart > 6)) {
            $hstart = 3;
        }

        $content .= '<h' . $hstart . '>';
        $content .= $fullname;
        $content .= '</h' . $hstart . '>';

        if (isset($data['jobTitle']) && (!empty($data['jobTitle']))) {
            $content .= '<span class="contact-info-position" itemprop="jobTitle">' . $data['jobTitle'] . '</span><br>';
        }

        if ((isset($arguments['class'])) && (!empty($arguments['class'])) && (preg_match("/shrink\-contact/i", $arguments['class']))) {
            $viewopts['view_telefonlink'] = true;
        }

        $content .= Schema::create_contactpointlist($data, 'ul', '', 'contactpoints', 'li', $viewopts);

        if (isset($display['socialmedia']) && $display['socialmedia']) {
            $content .= Schema::create_SocialMedialist($data);
        }

        $content .= '</div>';

        return $content;
    }

    public static function rrze_contact_shortlist($id, $display = array(), $arguments = array())
    {
        if ($id == 0) {
            return;
        }
        $fields = self::get_contact_data($id);
        $viewopts = self::get_viewsettings();

        $content = '';
        wp_enqueue_style('rrze-contact');

        $fields['description'] = self::get_description($id, $arguments['format'], $fields);
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);

        $data = self::filter_fields($fields, $display);

        if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
            && (isset($data['workLocation']) && (!empty($data['workLocation'])))
        ) {
            $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
        }

        if ($display['format'] == 'liste') {
            $content .= '<li itemscope itemtype="http://schema.org/Person">';
            $content .= Schema::create_Name($data, 'name', '', 'a', false, $viewopts);
        } else {
            $content .= '<span itemscope itemtype="http://schema.org/Person">';
            $content .= Schema::create_Name($data, 'name', '', 'a', true, $viewopts);
        }

        $cp = '';
        if ((!isset($data['connection_only'])) ||
            ((isset($data['connection_only']) && $data['connection_only'] == false))
        ) {
            if ($display['format'] == 'liste') {

                $cp = Schema::create_contactpointlist($data, 'span', '', '', 'span', $viewopts, false, true);
                if (!empty($cp)) {
                    $content .= ' (' . $cp . ')';
                }
            }
        }
        if (isset($display['socialmedia']) && $display['socialmedia']) {
            $sm = Schema::create_SocialMedialist($data, 'span', 'socialmedia', 'span');
            if (!empty($sm)) {
                $content .= ' ' . $sm;
            }
        }

        if ((isset($data['description'])) && (!empty($data['description'])) && isset($display['description']) && $display['description']) {
            $content .= "<br>" . $data['description'];
        }

        if ($display['format'] == 'liste') {
            $content .= '</li>';
        } else {
            $content .= '</span>';
        }
        return $content;
    }

    public static function rrze_contact_sidebar($id, $display = array(), $arguments = array())
    {
        if ($id == 0) {
            return;
        }
        $fields = self::get_contact_data($id);
        $viewopts = self::get_viewsettings();

        $content = '';
        wp_enqueue_style('rrze-contact');

        $fields['description'] = self::get_description($id, 'sidebar', $fields);
        $fields['morelink'] = self::get_morelink_url($fields, $viewopts);

        $data = self::filter_fields($fields, $display);

        if (isset($arguments['hstart'])) {
            $hstart = intval($arguments['hstart']);
        } else {
            $hstart = 2;
        }
        if (($hstart < 1) || ($hstart > 6)) {
            $hstart = 2;
        }

        $fullname = Schema::create_Name($data, 'name', '', 'a', false, $viewopts);

        $class = 'rrze-contact contact sidebar';
        if (isset($viewopts['view_thumb_size'])) {
            if ((isset($arguments['class'])) && (!empty($arguments['class'])) && (preg_match("/thumb\-size\-/i", $arguments['class']))) {
                // thumb-size ist in der Class bereits enthalten, daher ignoriere ich die globale Setting einstweile
                // und lass es über die Class individuell durch den Redakteuer steuern, der es als class eingibt
            } else {
                $class .= ' thumb-size-' . esc_attr($viewopts['view_thumb_size']);
            }
        }
        if ((isset($arguments['class'])) && (!empty($arguments['class']))) {
            $class .= ' ' . esc_attr($arguments['class']);
        }
        if (isset($display['border'])) {
            if ($display['border']) {
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

        if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
            && (isset($data['workLocation']) && (!empty($data['workLocation'])))
        ) {
            $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
        }

        $content = '<div class="' . $class . '" itemscope itemtype="http://schema.org/Person">' . "\n";
        $content .= '<div class="side">';
        $content .= '<div class="row">' . "\n";

        if ((isset($display['bild'])) && (!empty($display['bild'])) && (has_post_thumbnail($id))) {
            $content .= Data::create_contact_image($id, 'contact-thumb-v3', "contact-thumb", false, false, '', false);
        }

        $content .= '<div class="contact-sidebar">' . "\n";
        $content .= '<h' . $hstart . '>';
        $content .= $fullname;
        $content .= '</h' . $hstart . '>' . "\n";

        $content .= '<div class="contact-info">';
        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] == 'nach-name') {
            $content .= Schema::create_SocialMedialist($data);
        }

        if (isset($data['jobTitle']) && (!empty($data['jobTitle']))) {
            $content .= '<span class="contact-info-position" itemprop="jobTitle">' . $data['jobTitle'] . '</span><br>';
        }
        $orgadata = array();
        if (isset($data['worksFor']) && (!empty($data['worksFor']))) {
            $orgadata['name'] = $fields['worksFor'];
        }
        if (isset($data['department']) && (!empty($data['department']))) {
            $orgadata['department'] = $data['department'];
        }
        $content .= Schema::create_Organization($orgadata, 'p', 'worksFor', '', false, false, false);

        if ((!isset($data['connection_only'])) ||
            ((isset($data['connection_only']) && $data['connection_only'] == false))
        ) {
            $adresse = Schema::create_PostalAdress($data, 'address', '', 'address', true);
            if (isset($adresse)) {
                $content .= $adresse;
            }
            $content .= Schema::create_contactpointlist($data, 'ul', '', 'contactlist', 'li', $viewopts);
        }
        if (isset($viewopts['view_some_position']) && $viewopts['view_some_position'] !== 'nach-name') {
            $content .= Schema::create_SocialMedialist($data);
        }
        if (isset($display['sprechzeiten']) && (!empty($display['sprechzeiten']))) {
            if ((!isset($data['connection_only'])) ||
                ((isset($data['connection_only']) && $data['connection_only'] == false))
            ) {
                $sprechzeitentitletag = 'h' . ($hstart + 1);
                $content .= Schema::create_ContactPoint($data, 'div', 'contactPoint', '', $sprechzeitentitletag);
            }
        }
        $content .= '</div>';

        if (!empty($data['description']) && isset($display['description'])) {
            $content .= '<div class="contact-info-description"><span class="screen-reader-text">' . __('Beschreibung', 'rrze-contact') . ': </span><span itemprop="description">' . $data['description'] . '</span></div>' . "\n";
        }

        if ((!empty($data['connection_text']) || !empty($data['connection_options']) || !empty($data['connections'])) && isset($display['ansprechpartner']) && $display['ansprechpartner'] == true) {
            $content .= self::rrze_contact_connection($data['connection_text'], $data['connection_options'], $data['connections'], $hstart);
        }

        $content .= '</div><!-- /sidebar -->';
        $content .= '</div><!-- /row -->';
        $content .= '</div><!-- /side -->';
        $content .= '</div><!-- /rrze-contact -->';

        return $content;
    }

    public static function rrze_contact_connection($connection_text, $connection_options, $connections, $hstart)
    {
        $content = '';
        $contactlist = '';
        $viewopts = self::get_viewsettings();

        foreach ($connections as $key => $value) {
            extract($connections[$key]);

            $data = $connections[$key];
            if (isset($connection_options) && is_array($connection_options)) {
                foreach ($connection_options as $i => $key) {
                    $par[$key] = true;
                }
                if (!isset($par['contactPoint'])) {
                    $data['streetAddress'] = '';
                    $data['addressLocality'] = '';
                    $data['postalCode'] = '';
                    $data['addressRegion'] = '';
                    $data['addressCountry'] = '';
                    $data['workLocation'] = '';
                }
                if (!isset($par['hoursAvailable'])) {
                    $data['hoursAvailable'] = '';
                    $data['hoursAvailable_group'] = '';
                    $data['hoursAvailable_text'] = '';
                }
                if (!isset($par['telephone'])) {
                    $data['telephone'] = '';
                }
                if (!isset($par['faxNumber'])) {
                    $data['faxNumber'] = '';
                }
                if (!isset($par['email'])) {
                    $data['email'] = '';
                }
            }
            $contactpoint = '';
            $surroundingtag = 'span';

            $contactlist .= '<li itemscope itemtype="http://schema.org/Person">';

            $data['permalink'] = get_permalink($data['nr']);
            if ($data['link']) {
                $data['url'] = $data['link'];
            }
            if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
                && (isset($data['workLocation']) && (!empty($data['workLocation'])))
            ) {
                $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
            }
            $oldurl = '';
            if (isset($data['url'])) {
                $oldurl = $data['url'];
            }
            if (($data['permalink']) || ($data['url'])) {
                $surroundingtag = 'a';
            }
            if (!empty(get_the_title($data['nr']))) {
                $data['name'] = get_the_title($data['nr']);
            }
            $fullname = Schema::create_Name($data, 'name', '', $surroundingtag, false, $viewopts);
            $data['url'] = $oldurl;
            $contactlist .= $fullname;

            $contactlist .= Schema::create_PostalAdress($data, 'address', '', 'address', true);
            $contactlist .= Schema::create_contactpointlist($data, 'ul', '', 'contactlist', 'li', $viewopts);
            $contactlist .= Schema::create_ContactPoint($data);
            $contactlist .= '</li>';
        }

        if (!empty($contactlist)) {
            $content = '<div class="connection">';
            if ($connection_text) {
                $content .= '<h' . ($hstart + 1) . '>' . $connection_text . '</h' . ($hstart + 1) . '>';
            }
            $content .= '<ul class="connection-list">';
            $content .= $contactlist;
            $content .= '</ul>';
            $content .= '</div>';
        }

        return $content;
    }

    public static function filter_fields($input, $filter)
    {
        if (!isset($input)) {
            return;
        }
        if (!isset($filter)) {
            return $input;
        }
        $res = array();

        if ((isset($filter['_all'])) && ($filter['_all'])) {
            return $input;
        }

        foreach ($input as $key => $value) {
            if (isset($filter[$key])) {
                if ($filter[$key] == true) {
                    $res[$key] = $input[$key];
                } else {
                    unset($res[$key]);
                }
            }
        }

        /*
         * Felder, die nicht gelöscht werden sollen, wieder einfügen
         */
        $dontfilter = "content, morelink, permalink, connection_only, hoursAvailable_group, contact_title";
        $stay = explode(',', $dontfilter);
        foreach ($stay as $value) {
            $key = esc_attr(trim($value));
            if ((!empty($key)) && isset($input[$key])) {
                $res[$key] = $input[$key];
            }
        }

        /*
         * Sonderbehandlung für Gruppenfilter, bei denen sonst Infos wegfallen
         */
        if ((isset($filter['socialmedia'])) && ($filter['socialmedia'])) {
            $list = getFields('socialmedia');
            foreach ($list as $key => $value) {
                $datakey = $key . "_url";
                if (isset($input[$datakey])) {
                    $res[$datakey] = $input[$datakey];
                }
            }
        }
        if ((isset($filter['adresse'])) && ($filter['adresse'])) {
            $adressfields = "streetAddress, postalCode, addressLocality, addressRegion, addressCountry";

            $adresskeys = explode(',', $adressfields);
            foreach ($adresskeys as $value) {
                $key = esc_attr(trim($value));
                if ((!empty($key)) && isset($input[$key])) {
                    $res[$key] = $input[$key];
                }
            }
        }
        if ((isset($filter['sprechzeiten'])) && ($filter['sprechzeiten'])) {
            $adressfields = "officehours, hoursAvailable, hoursAvailable_text";

            $adresskeys = explode(',', $adressfields);
            foreach ($adresskeys as $value) {
                $key = esc_attr(trim($value));
                if ((!empty($key)) && isset($input[$key])) {
                    $res[$key] = $input[$key];
                }
            }
        }

        if (((isset($filter['ansprechpartner'])) && ($filter['ansprechpartner'])) || ((isset($input['connection_only']) && $input['connection_only'] == false))) {
            $adressfields = "connections, connection_text, connection_options";

            $adresskeys = explode(',', $adressfields);
            foreach ($adresskeys as $value) {
                $key = esc_attr(trim($value));
                if ((!empty($key)) && isset($input[$key])) {
                    $res[$key] = $input[$key];
                }
            }
        }

        return $res;
    }

    public static function create_fau_standort($id, $showfields, $titletag = 'h2')
    {
        if (!isset($id)) {
            return;
        }
        $id = sanitize_key($id);
        if (!is_array($showfields)) {
            return;
        }
        $fields = self::get_fields($id, get_post_meta($id, 'rrze_contact_univisID', true), 0);
        $permalink = get_permalink($id);

        if (isset($showfields['kurzbeschreibung']) && ($showfields['kurzbeschreibung'])) {
            $excerpt = get_post_field('post_excerpt', $id);
            $fields['description'] = $excerpt;
        }
        if (isset($showfields['adresse']) && ($showfields['adresse'])) {
            $schemaadr = true;
        } else {
            $schemaadr = false;
        }
        $schema = Schema::create_Place($fields, 'location', '', 'div', true, $schemaadr);

        $title = '';
        if (isset($showfields['title']) && ($showfields['title'])) {
            if (!empty(get_the_title($id))) {
                $title .= get_the_title($id);
            }
        }

        $content = '<div class="rrze-contact standort" itemscope itemtype="http://schema.org/Organization">';
        if (!empty($title)) {
            $content .= '<' . $titletag . ' itemprop="name">';
            if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
                $content .= '<a href="' . $permalink . '">';
            }
            $content .= $title;
            if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
                $content .= '</a>';
            }
            $content .= '</' . $titletag . '>';
        }

        if (!empty($schema)) {
            $content .= $schema;
        }

        if (isset($showfields['bild']) && ($showfields['bild']) && has_post_thumbnail($id)) {
            $content .= Data::create_contact_image($id, 'full', "standort-image", false, false, '');
        }

        if (isset($showfields['content']) && ($showfields['content'])) {
            $post = get_post($id);
            if ($post->post_content) {
                $content .= '<div class="content">' . $post->post_content . '</div>';
            }
        }
        $content .= '</div>';
        return $content;
    }

    public static function create_fau_standort_plain($id, $showfields, $titletag = '')
    {
        if (!isset($id)) {
            return;
        }
        $id = sanitize_key($id);
        if (!is_array($showfields)) {
            return;
        }
        $fields = self::get_fields($id, get_post_meta($id, 'rrze_contact_univisID', true), 0);
        $permalink = get_permalink($id);

        if (isset($showfields['kurzbeschreibung']) && ($showfields['kurzbeschreibung'])) {
            $excerpt = get_post_field('post_excerpt', $id);
            $fields['description'] = $excerpt;
        }
        if (isset($showfields['adresse']) && ($showfields['adresse'])) {
            $schemaadr = true;
        } else {
            $schemaadr = false;
        }
        $schema = Schema::create_Place($fields, '', '', 'span', false, $schemaadr);

        $title = '';
        if (isset($showfields['title']) && ($showfields['title'])) {
            if (!empty(get_the_title($id))) {
                $title .= get_the_title($id);
            }
        }

        $content = '';
        if (!empty($title)) {
            if (!empty($titletag)) {
                $content .= '<' . $titletag . '>';
            }
            if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
                $content .= '<a href="' . $permalink . '">';
            }
            $content .= $title;
            if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
                $content .= '</a>';
            }
            if (!empty($titletag)) {
                $content .= '</' . $titletag . '>';
            }
        }

        if (!empty($schema)) {
            $content .= $schema;
        }

        if (isset($showfields['bild']) && ($showfields['bild']) && has_post_thumbnail($id)) {
            $content .= Data::create_contact_image($id, 'full', "standort-image", false, false, '');
        }

        if (isset($showfields['content']) && ($showfields['content'])) {
            $post = get_post($id);
            if ($post->post_content) {
                $content .= '<div class="content">' . $post->post_content . '</div>';
            }
        }
        return $content;
    }

    // Get Data Cache
    private static function get_data_cache()
    {
        if (isset($GLOBALS['rrze_contact_data_cache'])) {
            return $GLOBALS['rrze_contact_data_cache'];
        }
        return;
    }

    // Set Data Cache
    private static function set_data_cache($id, $data)
    {
        if ((isset($id)) && (isset($data))) {
            $GLOBALS['rrze_contact_data_cache'][$id] = $data;
            //    error_log( 'Save Data for id '.$id );
            return $GLOBALS['rrze_contact_data_cache'][$id];
        }
        return;
    }
    // Zentraler Wrapper für self::get_fields(), bei dem das Ergebnis aus den
    // Datenfelder zwischengespeichert wird, damit weitere Datenbankanfragen unterbleiben
    // können
    public static function get_contact_data($id)
    {
        if (isset($id)) {
            $cacheddata = self::get_data_cache();

            if ((isset($cacheddata)) && (isset($cacheddata[$id]))) {
                return $cacheddata[$id];
            }
            $univisID = get_post_meta($id, 'rrze_contact_univisID', true);
            $data = self::get_fields($id, $univisID, 0);
            $thispost = get_post($id);
            $postContent = $thispost->post_content;
            if (isset($postContent)) {
                $data['content'] = $postContent;
            }

            $post_excerpt = get_post_field('post_excerpt', $id);
            if ($post_excerpt) {
                $data['post_excerpt'] = $post_excerpt;
            }
            $data['permalink'] = get_permalink($id);
            $data['contact_title'] = get_the_title($id);

            if ((isset($data)) && (!empty($data))) {
                return self::set_data_cache($id, $data);
            }
        }
        return;
    }

    //gibt die Werte der Person an, Inhalte abhängig von UnivIS,
    //Übergabewerte: ID der Person, UnivIS-ID der Person,
    //Default-Wert 1 für Ausgabe der hinterlegten Werte im Personeneingabeformular,
    //$ignore_connection=1 wenn die verknüpften Kontakte einer Person ignoriert werden sollen (z.B. wenn die Person selbst schon eine verknüpfte Kontaktcontact ist)
    public static function get_fields($id, $univisID, $defaults, $ignore_connection = 0, $setfields = false)
    {
        $univisSync = 0;
        $contact = array();
        if ($univisID) {
            $univis = new UnivIS();
            $contact = $univis->getContact('id=' . $univisID);
            $univisSync = 1;
        }
        $fields = array();
        // Ab hier Definition aller Feldzuordnungen, $key ist Name der Metaboxen, $value ist Name in UnivIS
        $fields_univis = array(
            'department' => 'orgname',
            'honorificPrefix' => 'title',
            'honorificSuffix' => 'atitle',
            'firstName' => 'firstName',
            'familyName' => 'lastname',
            'jobTitle' => 'work',
        );
        $fields_univis_location = array(
            'telephone' => 'tel',
            'mobilePhone' => 'mobile',
            'pgp' => 'pgp',
            'faxNumber' => 'fax',
            'email' => 'email',
            'url' => 'url',
            'streetAddress' => 'street',
            'addressLocality' => 'ort',
            'workLocation' => 'office',
        );
        $fields_univis_officehours = array(
            'hoursAvailable_group' => 'officehours',
        );
        // Die Detailfelder zu den Sprechzeiten
        $subfields_univis_officehours = array(
            /* von der UnivIS-Doku:
             * repeat mode is encoded in a string
             * syntax: <modechar><numbers><space><args>
             * mode  description
             * d     daily
             * w     weekly
             * m     monthly
             * y     yearly
             * b     block
             * numbers: number of skips between repeats
             * example:  "d2":      every second day
             * weekly and monthly have additional arguments:
             * weekly: argument is comma-separated list of weekdays where event is repeated
             * example:  "w3 1,2":  every third week on Monday and Tuesday
             * also possible: „we“ and „wo"
             * e = even calender week
             * o = odd calender week
             * monthly: argument has syntax "<submodechar><numbers>"
             * submode description
             * d       monthly by date
             * w       monthly by week
             * numbers: monthly by date: number of day (1-31)
             * monthly by week: number of week (1-5,e,o))
             * special case: 5 = last week of month
             * examples:  "m1 d23": on the 23rd day of every month
             * "m2 w5":  in the last week of every second month
             * Laut UnivIS-Live-Daten werden für die Sprechzeiten aber nur wöchentlich an verschiedenen Tagen, 2-wöchentlich und täglich verwendet. Sollte noch was anderes benötigt werden, muss nachprogrammiert werden.
             */
            'comment' => 'comment',
            'endtime' => 'endtime',
            'repeat' => 'repeat',
            //'repeat_mode' => 'repeat_mode',
            'repeat_submode' => '',
            'office' => 'office',
            'starttime' => 'starttime',
        );
        $fields_univis_orgunits = array(
            'worksFor' => 'orgunit',
        );

        /*
         * Felder, die nur aus FAU Person kommen und nicht aus UnivIS:
         */
        $fields_faucontact = array(
            'contactPoint' => '',
            'typ' => '',
            'sortField' => '',
            'addressCountry' => '',
            'link' => '',
            'hoursAvailable_text' => '',
            'hoursAvailable' => '',
            'description' => '',
            'small_description' => '',
        );

        $fields_exception = array(
            'postalCode' => '',
        );
        $fields_connection = array( // hier alle Felder ergänzen, die für die Anzeige der verknüpften Kontakte benötigt werden
            'connection_text' => '',
            'connection_only' => '',
            'connection_options' => array(),
            'connection_honorificPrefix' => 'honorificPrefix',
            'connection_firstName' => 'firstName',
            'connection_familyName' => 'familyName',
            'connection_honorificSuffix' => 'honorificSuffix',
            'connection_sortField' => 'sortField',
            'connection_streetAddress' => 'streetAddress',
            'connection_postalCode' => 'postalCode',
            'connection_addressLocality' => 'addressLocality',
            'connection_addressCountry' => 'addressCountry',
            'connection_workLocation' => 'workLocation',
            'connection_telephone' => 'telephone',
            'connection_mobilePhone' => 'mobilePhone',
            'connection_faxNumber' => 'faxNumber',
            'connection_email' => 'email',
            'connection_hoursAvailable' => 'hoursAvailable',
            'connection_hoursAvailable_group' => 'hoursAvailable_group',
            'connection_nr' => 'nr',
            'connection_link' => 'link',
        );
        foreach ($fields_univis as $key => $value) {
            if ($univisSync && array_key_exists($value, $contact)) {
                if ($value == 'orgname') {
                    $language = get_locale();
                    if (strpos($language, 'en_') === 0 && array_key_exists('orgname_en', $contact)) {
                        $value = 'orgname_en';
                    } else {
                        $value = 'orgname';
                    }
                }
                $value = sync_univis($id, $contact, $key, $value, $defaults);
            } else {
                if ($defaults) {
                    $value = '<p class="cmb2-metabox-description">' . __('In UnivIS ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
                } else {
                    $value = get_post_meta($id, 'rrze_contact_' . $key, true);
                }
            }
            if (($setfields) && (!isset($value))) {
                $value = '';
            }
            $fields[$key] = $value;
        }
        foreach ($fields_univis_location as $key => $value) {
            if ($univisSync && array_key_exists('locations', $contact) && array_key_exists('location', $contact['locations'][0])) {
                $contact_location = $contact['locations'][0]['location'][0];
                if (($key == 'telephone' || $key == 'faxNumber' || $key == 'mobilePhone') && !$defaults) {
                    $phone_number = sync_univis($id, $contact_location, $key, $value, $defaults);
                    switch (get_post_meta($id, 'rrze_contact_telephone_select', true)) {
                        case 'erl':
                            $value = Sanitize::phone($phone_number, 'erl');
                            break;
                        case 'nbg':
                            $value = Sanitize::phone($phone_number, 'nbg');
                            break;
                        default:
                            $value = Sanitize::phone($phone_number, 'standard');
                            break;
                    }
                } else {
                    $value = sync_univis($id, $contact_location, $key, $value, $defaults);
                }
            } else {
                if ($defaults) {
                    $value = '<p class="cmb2-metabox-description">' . __('In UnivIS ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
                } else {
                    if ($key == 'telephone' || $key == 'faxNumber' || $key == 'mobilePhone') {
                        $phone_number = Sanitize::phone(get_post_meta($id, 'rrze_contact_' . $key, true));
                    } else {
                        $value = get_post_meta($id, 'rrze_contact_' . $key, true);
                    }
                }
            }
            //add_action( 'admin_notices', array( 'RRZE\Contact', 'admin_notice_phone_number' ) );
            $fields[$key] = $value;
        }

        foreach ($fields_univis_officehours as $key => $value) {
            // ist eine UnivIS-ID vorhanden?
            switch ($univisSync) {
                case true:
                    if (array_key_exists('officehours', $contact) && array_key_exists('officehour', $contact['officehours'][0])) { // sind in UnivIS überhaupt Sprechzeiten hinterlegt?
                        if (get_post_meta($id, 'rrze_contact_univis_sync', true) || $defaults) { // ist der Haken zur Synchronisation da bzw. werden die UnivIS-Werte für das Backend abgefragt
                            $contact_officehours = $contact['officehours'][0]['officehour'];
                            $officehours = array();
                            foreach ($contact_officehours as $num => $num_val) {
                                $repeat = isset($contact_officehours[$num]['repeat']) ? $contact_officehours[$num]['repeat'] : 0;
                                $repeat_submode = isset($contact_officehours[$num]['repeat_submode']) ? $contact_officehours[$num]['repeat_submode'] : 0;
                                $starttime = isset($contact_officehours[$num]['starttime']) ? $contact_officehours[$num]['starttime'] : 0;
                                $endtime = isset($contact_officehours[$num]['endtime']) ? $contact_officehours[$num]['endtime'] : 0;
                                $office = isset($contact_officehours[$num]['office']) ? $contact_officehours[$num]['office'] : 0;
                                $comment = isset($contact_officehours[$num]['comment']) ? $contact_officehours[$num]['comment'] : 0;
                                $officehour = UnivIS_Data::officehours_repeat($repeat, $repeat_submode, $starttime, $endtime, $office, $comment);
                                array_push($officehours, $officehour);
                            }
                            if ($defaults) {
                                $value = implode(',', $officehours);
                                $officehours = '<p class="cmb2-metabox-description">' . __('Aus UnivIS angezeigter Wert:', 'rrze-contact') . ' <code>' . $value . '</code></p>';
                            }
                            break;
                        }
                    } elseif ($defaults) { // in UnivIS stehen keine Sprechzeiten

                        $officehours = '<p class="cmb2-metabox-description">' . __('In UnivIS ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
                        break;
                    }
                default: // keine UnivIS-ID da bzw. kein Haken bei Datenanzeige aus UnivIS => die Feldinhalte werden ausgegeben
                    $contact_officehours = get_post_meta($id, 'rrze_contact_hoursAvailable_group', true);
                    $officehours = array();
                    if (!empty($contact_officehours)) {
                        foreach ($contact_officehours as $num => $num_val) {
                            $repeat = isset($contact_officehours[$num]['repeat']) ? $contact_officehours[$num]['repeat'] : 0;
                            $repeat_submode = isset($contact_officehours[$num]['repeat_submode']) ? $contact_officehours[$num]['repeat_submode'] : 0;
                            $starttime = isset($contact_officehours[$num]['starttime']) ? $contact_officehours[$num]['starttime'] : 0;
                            $endtime = isset($contact_officehours[$num]['endtime']) ? $contact_officehours[$num]['endtime'] : 0;
                            $office = isset($contact_officehours[$num]['office']) ? $contact_officehours[$num]['office'] : 0;
                            $comment = isset($contact_officehours[$num]['comment']) ? $contact_officehours[$num]['comment'] : 0;
                            $officehour = UnivIS_Data::officehours_repeat($repeat, $repeat_submode, $starttime, $endtime, $office, $comment);
                            array_push($officehours, $officehour);
                        }
                    }
            }
            $fields[$key] = $officehours;
        }

        foreach ($fields_univis_orgunits as $key => $value) {
            $language = get_locale();
            if (strpos($language, 'en_') === 0 && array_key_exists('orgunit_ens', $contact)) {
                $orgunit = 'orgunit_en';
                $orgunits = 'orgunit_ens';
            } else {
                $orgunit = 'orgunit';
                $orgunits = 'orgunits';
            }
            if (array_key_exists($orgunits, $contact)) {
                $contact_orgunits = $contact[$orgunits][0][$orgunit];
                $i = count($contact_orgunits);
                if ($i > 1) {
                    $i = count($contact_orgunits) - 2;
                }
                $value = sync_univis($id, $contact_orgunits, $key, $i, $defaults);
            } else {
                if ($defaults) {
                    $value = '<p class="cmb2-metabox-description">' . __('In UnivIS ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
                } else {
                    $value = get_post_meta($id, 'rrze_contact_' . $key, true);
                }
            }
            $fields[$key] = $value;
        }
        foreach ($fields_faucontact as $key => $value) {
            $value = get_post_meta($id, 'rrze_contact_' . $key, true);
            $fields[$key] = $value;
        }

        $SocialMedia = getFields('socialmedia');
        foreach ($SocialMedia as $key => $value) {
            $datakey = $key . "_url";
            $value = get_post_meta($id, 'rrze_contact_' . $datakey, true);
            $fields[$datakey] = $value;
        }

        foreach ($fields_exception as $key => $value) {
            if ($key == 'postalCode') {
                if (get_post_meta($id, 'rrze_contact_univis_sync', true) && array_key_exists('locations', $contact) && array_key_exists('location', $contact['locations'][0]) && array_key_exists('ort', $contact['locations'][0]['location'][0])) {
                    $value = '';
                } else {
                    $value = get_post_meta($id, 'rrze_contact_' . $key, true);
                }
            }
            $fields[$key] = $value;
        }
        if (!$ignore_connection) {
            $connections = get_post_meta($id, 'rrze_contact_connection_id', true);
        }

        if (!empty($connections)) {
            $connection = array();
            foreach ($connections as $ckey => $cvalue) {
                $connection_fields[$ckey] = self::get_fields($cvalue, get_post_meta($cvalue, 'rrze_contact_univisID', true), 0, 1);
                $connection_fields[$ckey]['nr'] = $cvalue;
            }
            foreach ($connection_fields as $key => $value) {
                foreach ($fields_connection as $fckey => $fcvalue) {
                    if ($fckey == 'connection_text' || $fckey == 'connection_only' || $fckey == 'connection_options') {
                        $value = get_post_meta($id, 'rrze_contact_' . $fckey, true);
                        $fields[$fckey] = $value;
                    } else {
                        $value = $connection_fields[$key][$fcvalue];
                        $connection[$key][$fcvalue] = $value;
                    }
                }
            }
            $fields['connections'] = $connection;
        }

        if (get_post_meta($id, 'rrze_contact_standort_sync', true) && get_post_meta($id, 'rrze_contact_standort_id', true)) {
            $fields_standort = self::get_fields_standort($id, get_post_meta($id, 'rrze_contact_standort_id', true), 0);
            $fields = array_merge($fields, $fields_standort);
        }
        return $fields;
    }

    public static function get_default_display($format = '')
    {
        $display = '';
        switch ($format) {
            case 'name':
                $display = 'titel, familyName, firstName, name, suffix, permalink, url, link';
                break;
            case 'shortlist':
                $display = 'titel, familyName, firstName, name, mail, telefon, suffix, permalink, url, link';
                break;
            case 'plain':
                $display = 'titel, familyName, firstName, name';
                break;
            case 'full':
            case 'compactindex':
            case 'kompakt':
                $display = 'titel, familyName, firstName, name, suffix, position, telefon, mobilePhone, email,  socialmedia, adresse, bild, permalink, url, border, border, link';
                break;
            case 'page':
                $display = '_all';
                break;
            case 'listentry':
            case 'liste':
                $display = 'titel, familyName, firstName, name, suffix, description, permalink, url, link';
                break;
            case 'sidebar':
                $display = '';
                $sitebaropts = self::map_old_keys(self::get_viewsettings('sidebar'));
                if (isset($sitebaropts)) {
                    foreach ($sitebaropts as $key => $value) {
                        if (!empty($value)) {
                            if (!empty($display)) {
                                $display .= ", ";
                            }
                            $display .= $key;
                        }
                    }
                }
                if (empty(trim($display))) {
                    $display = 'titel, familyName, firstName, name, suffix, workLocation, worksFor, jobTitle, telefon, mobilePhone, email, socialmedia, fax, url, adresse, bild, permalink, url, sprechzeiten, ansprechpartner, description';
                }
                break;
            case 'table':
                $display = 'titel, familyName, firstName, name, suffix, telefon, email, permalink, url, link';
                break;
            case 'card':
                $display = 'titel, familyName, firstName, name, suffix, bild, position, permalink, socialmedia';
                break;
            default:
                $display = 'titel, familyName, firstName, name, suffix, worksFor, department, jobTitle, telefon, email, permalink, border, ansprechpartner';
        }
        return $display;
    }

    public static function get_display_field($format = '', $show = '', $hide = '')
    {
        $display = self::get_default_display($format);
        $showfields = self::parse_liste($display, true);

        if ((isset($show)) && (!empty($show))) {
            $showfields = self::parse_liste($show, true, $showfields);
        }
        if ((isset($hide)) && (!empty($hide))) {
            $showfields = self::parse_liste($hide, false, $showfields);
        }

        $showfields = self::map_old_keys($showfields);
        if (!empty($format)) {
            $showfields['format'] = $format;
        }
        return $showfields;
    }

    public static function map_old_keys($liste)
    {
        $newlist = array();
        foreach ($liste as $key => $value) {
            switch ($key) {
                case 'kurzbeschreibung':
                case 'kurzauszug':
                case 'excerpt':
                    $newlist['description'] = $liste[$key];
                    break;
                case 'organisation':
                case 'institution':
                    $newlist['worksFor'] = $liste[$key];
                    break;
                case 'abteilung':
                    $newlist['department'] = $liste[$key];
                    break;
                case 'position':
                    $newlist['jobTitle'] = $liste[$key];
                    break;
                case 'bild':
                    $newlist['bild'] = $liste[$key];
                    $newlist['showthumb'] = $liste[$key];
                    break;
                case 'compactindex':
                    $newlist['kompakt'] = $liste[$key];
                    break;
                case 'mail':
                    $newlist['email'] = $liste[$key];
                    break;
                case 'fax':
                    $newlist['faxNumber'] = $liste[$key];
                    break;
                case 'telefon':
                    $newlist['telephone'] = $liste[$key];
                    break;
                case 'fax':
                    $newlist['faxNumber'] = $liste[$key];
                    break;
                case 'mobil':
                    $newlist['mobilePhone'] = $liste[$key];
                    break;
                case 'webseite':
                    $newlist['url'] = $liste[$key];
                    break;
                case 'mehrlink':
                    $newlist['link'] = $liste[$key];
                    break;
                case 'suffix':
                    $newlist['honorificSuffix'] = $liste[$key];
                    break;
                case 'title':
                case 'titel':
                case 'prefix':
                    $newlist['honorificPrefix'] = $liste[$key];
                    break;
                case 'sprechzeiten':
                    $newlist['hoursAvailable'] = $liste[$key];
                    $newlist['sprechzeiten'] = $liste[$key];
                    break;
                case 'ansprechpartner':
                    $newlist['ansprechpartner'] = $liste[$key];
                    break;
                case 'rahmen':
                    $newlist['border'] = $liste[$key];
                    break;
                case 'raum':
                case 'room':
                    $newlist['workLocation'] = $liste[$key];
                    break;

                default:
                    $newlist[$key] = $liste[$key];
            }
        }
        return $newlist;
    }

    public static function parse_liste($liste = '', $resbool = true, $showarray = array())
    {
        if (!empty($liste)) {

            $showvals = explode(',', $liste);
            foreach ($showvals as $value) {
                $key = esc_attr(trim($value));
                if (!empty($key)) {
                    $showarray[$key] = $resbool;
                }
            }
        }
        return $showarray;
    }
}
