<?php

namespace RRZE\Contact;

use function RRZE\Contact\Config\getFields;
// use RRZE\Contact\API\UnivIS;

defined('ABSPATH') || exit;

class Data
{

    // private static function get_viewsettings($lookup = 'constants')
    // {
    //     $settings = new Settings(__DIR__);
    //     $settings->onLoaded();
    //     $options = $settings->options;

    //     $viewopt = array();

    //     foreach ($options as $section => $field) {
    //         if ($lookup == 'sidebar') {
    //             if (substr($section, 0, 7) === 'sidebar') {
    //                 $keyname = preg_replace('/sidebar_/i', '', $section);
    //                 $viewopt[$keyname] = $options[$section];
    //             }
    //         } else {
    //             if (substr($section, 0, 9) === 'constants') {
    //                 $keyname = preg_replace('/constants_/i', '', $section);
    //                 $viewopt[$keyname] = $options[$section];
    //             }
    //         }
    //     }
    //     return $viewopt;
    // }

    // public static function get_contactdata($connection = 0)
    // {
    //     $args = array(
    //         'post_type' => 'contact',
    //         'numberposts' => -1,
    //         'meta_key' => 'rrze_contact_typ',
    //     );

    //     $contactlist = get_posts($args);

    //     if ($contactlist) {
    //         foreach ($contactlist as $key => $value) {
    //             $contactlist[$key] = (array) $contactlist[$key];
    //             $name = $contactlist[$key]['post_title'];
    //             switch (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_typ', true)) {
    //                 case 'realcontact':
    //                 case 'realmale':
    //                 case 'realfemale':
    //                     if (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_familyName', true)) {
    //                         $lastname = get_post_meta($contactlist[$key]['ID'], 'rrze_contact_familyName', true);
    //                         if (get_post_meta($contactlist[$key]['ID'], 'rrze_contact_firstName', true)) {
    //                             $name = $lastname . ', ' . get_post_meta($contactlist[$key]['ID'], 'rrze_contact_firstName', true);
    //                         } elseif (ltrim(strpos($name, $lastname))) {
    //                             $name = $lastname . ', ' . ltrim(str_replace($lastname, '', $name));
    //                         } else {
    //                             $name = $lastname;
    //                         }
    //                     } else {
    //                         if (ltrim(strpos($name, ' '))) {
    //                             $lastname = ltrim(strrchr($name, ' '));
    //                             $givenName = ltrim(str_replace($lastname, '', $name));
    //                             $name = $lastname . ', ' . $givenName;
    //                         }
    //                     }
    //                     break;
    //                 default:
    //                     break;
    //             }
    //             $temp[$contactlist[$key]['ID']] = $name;
    //         }
    //         natcasesort($temp);

    //         foreach ($temp as $key => $value) {
    //             $contactselect[$key] = $key . ': ' . $value;
    //         }
    //         // Für Auswahlfeld bei verknüpften Kontakten
    //         if ($connection) {
    //             $contactselect = array('0' => __('Kein Kontakt ausgewählt.', 'rrze-contact')) + $contactselect;
    //         }
    //     } else {
    //         // falls noch keine Kontakte vorhanden sind
    //         $contactselect[0] = __('Noch keine Kontakte eingepflegt.', 'rrze-contact');
    //     }
    //     return $contactselect;
    // }

    // public static function get_standortdata()
    // {
    //     $args = array(
    //         'post_type' => 'location',
    //         'numberposts' => -1,
    //     );

    //     $standortlist = get_posts($args);
    //     if ($standortlist) {
    //         foreach ($standortlist as $key => $value) {
    //             $standortlist[$key] = (array) $standortlist[$key];
    //             $standortselect[$standortlist[$key]['ID']] = $standortlist[$key]['post_title'];
    //         }
    //         asort($standortselect);
    //         $standortselect = array('0' => __('Kein Standort ausgewählt.', 'rrze-contact')) + $standortselect;
    //     } else {
    //         $standortselect[0] = __('Noch kein Standort eingepflegt.', 'rrze-contact');
    //     }
    //     return $standortselect;
    // }

    // public static function get_default_rrze_contact_typ()
    // {
    //     if (isset($_GET["rrze_contact_typ"]) && $_GET["rrze_contact_typ"] == 'einrichtung') {
    //         $default_rrze_contact_typ = 'einrichtung';
    //     } else {
    //         $default_rrze_contact_typ = 'realcontact';
    //     }
    //     return $default_rrze_contact_typ;
    // }

    // //gibt die Werte des Standorts an, für Standort-Synchronisation $edfaults=1
    // public static function get_fields_standort($id, $standort_id, $defaults)
    // {
    //     $standort_sync = 0;
    //     $fields = array();
    //     if ($standort_id) {
    //         $standort_sync = 1;
    //     }
    //     $fields_standort = array(
    //         'streetAddress' => '',
    //         'postalCode' => '',
    //         'addressLocality' => '',
    //         'addressCountry' => '',
    //     );

    //     foreach ($fields_standort as $key => $value) {
    //         if ($standort_sync) {
    //             $value = self::sync_standort($id, $standort_id, $key, $defaults);
    //         } else {
    //             if ($defaults) {
    //                 $value = '<p class="cmb2-metabox-description">' . __('Im Standort ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
    //             } elseif ($id) {
    //                 $value = get_post_meta($id, 'rrze_contact_' . $key, true);
    //             }
    //         }
    //         $fields[$key] = $value;
    //     }
    //     return $fields;
    // }

    // public static function get_more_link($targeturl, $screenreadertext = '', $class = 'contact-info-more', $withdiv = true, $linktitle = '')
    // {
    //     if ((!isset($targeturl)) || empty($targeturl)) {
    //         return;
    //     }

    //     $viewopts = self::get_viewsettings();
    //     $res = '';

    //     if ($withdiv) {
    //         $res .= '<div ';
    //         if (!empty($class)) {
    //             $res .= 'class="' . esc_attr($class) . '"';
    //         }
    //         $res .= '>';
    //     }
    //     $res .= '<a href="' . esc_url($targeturl) . '"';
    //     if ($withdiv == false) {
    //         if (!empty($class)) {
    //             $res .= ' class="' . esc_attr($class) . '"';
    //         }
    //     } else {
    //         $res .= ' class="standard-btn primary-btn"';
    //     }
    //     if (!empty($linktitle)) {
    //         $res .= ' title="' . esc_attr($linktitle) . '"';
    //     }
    //     $res .= '>';
    //     if ((isset($viewopts['view_contact_moreButton_text'])) && (!empty($viewopts['view_contact_moreButton_text']))) {
    //         $res .= esc_html($viewopts['view_contact_moreButton_text']);
    //     } else {
    //         $res .= __('Profil aufrufen', 'rrze-contact');
    //     }
    //     if (!empty($screenreadertext)) {
    //         $res .= ' <span class="screen-reader-text">' . $screenreadertext . '</span>';
    //     }
    //     $res .= '</a>';
    //     if ($withdiv) {
    //         $res .= '</div>';
    //     }
    //     return $res;
    // }

    // // $id = ID des Personeneintrags,
    // // $standort_id = ID des Standorteintrags,
    // // $rrze_contact_var = Bezeichnung des Feldes im Personenplugin,
    // // $defaults = Default-Wert 1 für Ausgabe der hinterlegten Werte im Personeneingabeformular
    // public static function sync_standort($id, $standort_id, $rrze_contact_var, $defaults)
    // {
    //     $value = get_post_meta($standort_id, 'rrze_contact_' . $rrze_contact_var, true);
    //     //wird benötigt, falls jeder einzelne Wert abgefragt werden soll
    //     if ($defaults) {
    //         if (!empty($value)) {
    //             $val = '<p class="cmb2-metabox-description">' . __('Von Standort angezeigter Wert:', 'rrze-contact') . ' <code>' . $value . '</code></p>';
    //         } else {
    //             $val = '<p class="cmb2-metabox-description">' . __('Im Standort ist hierfür kein Wert hinterlegt.', 'rrze-contact') . '</p>';
    //         }
    //     } else {
    //         if (!empty($value) && get_post_meta($id, 'rrze_contact_standort_sync', true)) {
    //             $val = $value;
    //         } else {
    //             $val = get_post_meta($id, 'rrze_contact_' . $rrze_contact_var, true);
    //         }
    //     }
    //     return $val;
    // }

    // public static function get_standort_defaults($id = 0)
    // {
    //     $post = get_post($id);
    //     if (!is_null($post) && $post->post_type === 'contact' && get_post_meta($id, 'rrze_contact_standort_id', true)) {
    //         $standort_id = get_post_meta($id, 'rrze_contact_standort_id', true);
    //         $standort_default = self::get_fields_standort($id, $standort_id, 1);
    //         return $standort_default;
    //     } else {
    //         return self::get_fields_standort(0, 0, 0);
    //     }
    // }

    // public static function get_morelink_url($data, $args)
    // {
    //     $url = '';
    //     if ((isset($data['link'])) && (!empty(esc_url($data['link'])))) {
    //         $url = $data['link'];
    //     } elseif ((isset($data['permalink'])) && (!empty(esc_url($data['permalink'])))) {
    //         $url = $data['permalink'];
    //     } elseif ((isset($data['url'])) && (!empty(esc_url($data['url'])))) {
    //         $url = $data['url'];
    //     }
    //     if (isset($args['view_contact_linkname'])) {
    //         if (!empty($args['view_contact_linkname'])) {

    //             if ($args['view_contact_linkname'] == 'force-nolink') {
    //                 $url = '';
    //             } elseif ($args['view_contact_linkname'] == 'url') {
    //                 if ((isset($data['url'])) && (!empty(esc_url($data['url'])))) {
    //                     $url = $data['url'];
    //                 }
    //             } elseif ($args['view_contact_linkname'] == 'permalink') {
    //                 if ((isset($data['permalink'])) && (!empty(esc_url($data['permalink'])))) {
    //                     $url = $data['permalink'];
    //                 }
    //             } elseif ($args['view_contact_linkname'] == 'use-link') {
    //                 if ((isset($data['link'])) && (!empty(esc_url($data['link'])))) {
    //                     $url = $data['link'];
    //                 }
    //             }
    //         }
    //     }
    //     return $url;
    // }

    // BK
    private static function getDescription(&$post, &$postMeta)
    {
        if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'small_description'][0])) {
            return $postMeta[RRZE_CONTACT_PREFIX . 'small_description'][0];
        }
        if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'description'][0])) {
            return $postMeta[RRZE_CONTACT_PREFIX . 'description'][0];
        }
        if (!empty($post['post_excerpt'])) {
            return $post['post_excerpt'];
        } else {
            return '';
        }
    }

    public static function getImage($postID, &$aDisplayfields, $imageLink, $showCaption, $imageSize, $contactType)
    {
        $aRet = [];

        $aRet['alt'] = get_the_title($postID);

        if ($imageLink) {
            $aRet['imagelink'] = get_permalink($postID);
        }

        if (has_post_thumbnail($postID)) {
            $imageID = get_post_thumbnail_id($postID);
            $imageAtts = wp_get_attachment_image_src($imageID, $imageSize);

            if (!empty($imageAtts)){
                $aRet['src'] = $imageAtts[0];
                $aRet['width'] = $imageAtts[1];
                $aRet['height'] = $imageAtts[2];
                $aRet['srcset'] = wp_get_attachment_image_srcset($imageID, $imageSize);
                $aRet['sizes'] = wp_get_attachment_image_sizes($imageID, $imageSize);
            }

            if ($showCaption) {
                $attachment = get_post($imageID);

                if (isset($attachment) && isset($attachment->post_excerpt)) {
                    $caption = trim(strip_tags($attachment->post_excerpt));
                    if (!empty($caption)){
                        $aRet['caption'] = $caption;
                    }
                }
            }
        } else {
            $aRet['src'] = plugin_dir_url(__DIR__) . 'images/placeholder-' . $contactType . '.png';
            $aRet['width'] = 120;
            $aRet['height'] = 160;
        }

        $aRet['css'] = $imageSize;
        return $aRet;
    }

    // BK
    public static function getContactData($postID, &$aDisplayfields, &$pluginSettings)
    {
        $aRet = [];

        if (in_array('permalink', $aDisplayfields) || in_array('all', $aDisplayfields)) {
            $aRet['permalink'] = get_permalink($postID);
        }

        $aFields = getFields('contact');

        if (!empty($aFields)) {
            $postMeta = get_post_meta($postID);

            if (in_array('description', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                $post = get_post($postID, ARRAY_A);
    
                $desc = self::getDescription($post, $postMeta);
                if (!empty($desc)) {
                    $aRet['description'] = $desc;
                }
    
                if (in_array('post_content', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                    if (!empty($post['post_content'])) {
                        $aRet['post_content'] = $post['post_content'];
                    }
                }
            }
    
            foreach ($aFields as $aDetails) {
                if ((in_array($aDetails['name'], $aDisplayfields) || in_array('all', $aDisplayfields)) && !empty($postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0])) {
                    $aRet[$aDetails['name']] = $postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0];
                }
            }

            $aGroups = [];

            if (in_array('locations', $aDisplayfields) || in_array('all', $aDisplayfields)) {                
                $aGroups[] = 'locations';
            }

            if (in_array('consultations', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                $aGroups[] = 'consultations';
            }

            foreach ($aGroups as $group) {
                if (!empty($postMeta[RRZE_CONTACT_PREFIX . $group . 'Group'])) {
                    $aLocations = unserialize($postMeta[RRZE_CONTACT_PREFIX . $group . 'Group'][0]);
                    foreach ($aLocations as $location) {
                        $aTmp = [];
                        foreach ($location as $field => $value) {
                            $fieldName = substr($field, strlen(RRZE_CONTACT_PREFIX));
                            if (in_array($fieldName, $aDisplayfields) || in_array('all', $aDisplayfields)) {
                                $aTmp[$fieldName] = $value;
                            }
                        }
                        $aRet[$group][] = $aTmp;
                    }
                }
            }

            if (in_array('consultations', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'consultation_headline'][0])) {
                    $aRet['consultation_headline'] = $postMeta[RRZE_CONTACT_PREFIX . 'consultation_headline'][0];
                }

                if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'consultation_notice'][0])) {
                    $aRet['consultation_notice'] = $postMeta[RRZE_CONTACT_PREFIX . 'consultation_notice'][0];
                }
            }

            if (in_array('socialmedia', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                $aFields = getFields('socialmedia');
                $isSocialMedia = false;

                foreach ($aFields as $aDetails) {
                    if (!empty($postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0])) {
                        $aRet[$aDetails['name']] = $postMeta[RRZE_CONTACT_PREFIX . $aDetails['name']][0];
                        $isSocialMedia = true;
                    }
                }

                if ($isSocialMedia){
                    $aRet['socialmedia'] = true;
                    $aRet['socialmedia-' . $pluginSettings['layout_socialmedia']] = true;    
                    $aRet['screen-reader-text'] = __('page by', 'rrze-contact') . ' ' . (!empty($aRet['givenName']) ? $aRet['givenName'] : '') . ' ' . (!empty($aRet['lastName']) ? $aRet['lastName'] : '');
                }
            }

            if (in_array('image', $aDisplayfields) || in_array('all', $aDisplayfields)) {
                $imageLink = $pluginSettings['layout_imagelink'];
                $imageSize = $pluginSettings['layout_imagesize'];
                $showCaption = (!empty($pluginSettings['layout_imagecaption']) ? true : false);
                $contactType = (!empty($postMeta[RRZE_CONTACT_PREFIX . 'contactType'][0]) ? $postMeta[RRZE_CONTACT_PREFIX . 'contactType'][0] : '');

                $aRet['image'] = self::getImage($postID, $aDisplayfields, $imageLink, $showCaption, $imageSize, $contactType);
            }

            if (in_array('moreButton', $aDisplayfields)) {
                $aRet['moreButton'] = true;
                $aRet['moreButton_text'] =  (!empty($pluginSettings['layout_moreButton_text']) ? $pluginSettings['layout_moreButton_text'] : __('More', 'rrze-contact') . ' >');
                $aRet['screen-reader-moreButton_text'] = __('Details to', 'rrze-contact') . ' ' . (!empty($aRet['givenName']) ? $aRet['givenName'] : '') . ' ' . (!empty($aRet['lastName']) ? $aRet['lastName'] : '');                
            }

            $aRet['room_text'] = $pluginSettings['layout_room_text'];
        }

        return $aRet;
    }


    // public static function rrze_contact_connection($connection_text, $connection_options, $connections, $hstart)
    // {
    //     $content = '';
    //     $contactlist = '';
    //     $viewopts = self::get_viewsettings();

    //     foreach ($connections as $key => $value) {
    //         extract($connections[$key]);

    //         $data = $connections[$key];
    //         if (isset($connection_options) && is_array($connection_options)) {
    //             foreach ($connection_options as $i => $key) {
    //                 $par[$key] = true;
    //             }
    //             if (!isset($par['contactPoint'])) {
    //                 $data['streetAddress'] = '';
    //                 $data['addressLocality'] = '';
    //                 $data['postalCode'] = '';
    //                 $data['addressRegion'] = '';
    //                 $data['addressCountry'] = '';
    //                 $data['workLocation'] = '';
    //             }
    //             if (!isset($par['hoursAvailable'])) {
    //                 $data['hoursAvailable'] = '';
    //                 $data['hoursAvailable_group'] = '';
    //                 $data['hoursAvailable_text'] = '';
    //             }
    //             if (!isset($par['telephone'])) {
    //                 $data['telephone'] = '';
    //             }
    //             if (!isset($par['faxNumber'])) {
    //                 $data['faxNumber'] = '';
    //             }
    //             if (!isset($par['email'])) {
    //                 $data['email'] = '';
    //             }
    //         }
    //         $contactpoint = '';
    //         $surroundingtag = 'span';

    //         $contactlist .= '<li itemscope itemtype="http://schema.org/Person">';

    //         $data['permalink'] = get_permalink($data['nr']);
    //         if ($data['link']) {
    //             $data['url'] = $data['link'];
    //         }
    //         if ((isset($viewopts['view_raum_prefix'])) && (!empty(trim($viewopts['view_raum_prefix'])))
    //             && (isset($data['workLocation']) && (!empty($data['workLocation'])))
    //         ) {
    //             $data['workLocation'] = $viewopts['view_raum_prefix'] . ' ' . $data['workLocation'];
    //         }
    //         $oldurl = '';
    //         if (isset($data['url'])) {
    //             $oldurl = $data['url'];
    //         }
    //         if (($data['permalink']) || ($data['url'])) {
    //             $surroundingtag = 'a';
    //         }
    //         if (!empty(get_the_title($data['nr']))) {
    //             $data['name'] = get_the_title($data['nr']);
    //         }
    //         $fullname = Schema::create_Name($data, 'name', '', $surroundingtag, false, $viewopts);
    //         $data['url'] = $oldurl;
    //         $contactlist .= $fullname;

    //         $contactlist .= Schema::create_PostalAdress($data, 'address', '', 'address', true);
    //         $contactlist .= Schema::create_contactpointlist($data, 'ul', '', 'contactlist', 'li', $viewopts);
    //         $contactlist .= Schema::create_ContactPoint($data);
    //         $contactlist .= '</li>';
    //     }

    //     if (!empty($contactlist)) {
    //         $content = '<div class="connection">';
    //         if ($connection_text) {
    //             $content .= '<h' . ($hstart + 1) . '>' . $connection_text . '</h' . ($hstart + 1) . '>';
    //         }
    //         $content .= '<ul class="connection-list">';
    //         $content .= $contactlist;
    //         $content .= '</ul>';
    //         $content .= '</div>';
    //     }

    //     return $content;
    // }


    // public static function create_fau_standort($id, $showfields, $titletag = 'h2')
    // {
    //     if (!isset($id)) {
    //         return;
    //     }
    //     $id = sanitize_key($id);
    //     if (!is_array($showfields)) {
    //         return;
    //     }
    //     $fields = self::get_fields($id, get_post_meta($id, 'rrze_contact_univisID', true), 0);
    //     $permalink = get_permalink($id);

    //     if (isset($showfields['kurzbeschreibung']) && ($showfields['kurzbeschreibung'])) {
    //         $excerpt = get_post_field('post_excerpt', $id);
    //         $fields['description'] = $excerpt;
    //     }
    //     if (isset($showfields['adresse']) && ($showfields['adresse'])) {
    //         $schemaadr = true;
    //     } else {
    //         $schemaadr = false;
    //     }
    //     $schema = Schema::create_Place($fields, 'location', '', 'div', true, $schemaadr);

    //     $title = '';
    //     if (isset($showfields['title']) && ($showfields['title'])) {
    //         if (!empty(get_the_title($id))) {
    //             $title .= get_the_title($id);
    //         }
    //     }

    //     $content = '<div class="rrze-contact standort" itemscope itemtype="http://schema.org/Organization">';
    //     if (!empty($title)) {
    //         $content .= '<' . $titletag . ' itemprop="name">';
    //         if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
    //             $content .= '<a href="' . $permalink . '">';
    //         }
    //         $content .= $title;
    //         if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
    //             $content .= '</a>';
    //         }
    //         $content .= '</' . $titletag . '>';
    //     }

    //     if (!empty($schema)) {
    //         $content .= $schema;
    //     }

    //     if (isset($showfields['bild']) && ($showfields['bild']) && has_post_thumbnail($id)) {
    //         $content .= Data::create_contact_image($id, 'full', "standort-image", false, false, '');
    //     }

    //     if (isset($showfields['content']) && ($showfields['content'])) {
    //         $post = get_post($id);
    //         if ($post->post_content) {
    //             $content .= '<div class="content">' . $post->post_content . '</div>';
    //         }
    //     }
    //     $content .= '</div>';
    //     return $content;
    // }

    // public static function create_fau_standort_plain($id, $showfields, $titletag = '')
    // {
    //     if (!isset($id)) {
    //         return;
    //     }
    //     $id = sanitize_key($id);
    //     if (!is_array($showfields)) {
    //         return;
    //     }
    //     $fields = self::get_fields($id, get_post_meta($id, 'rrze_contact_univisID', true), 0);
    //     $permalink = get_permalink($id);

    //     if (isset($showfields['kurzbeschreibung']) && ($showfields['kurzbeschreibung'])) {
    //         $excerpt = get_post_field('post_excerpt', $id);
    //         $fields['description'] = $excerpt;
    //     }
    //     if (isset($showfields['adresse']) && ($showfields['adresse'])) {
    //         $schemaadr = true;
    //     } else {
    //         $schemaadr = false;
    //     }
    //     $schema = Schema::create_Place($fields, '', '', 'span', false, $schemaadr);

    //     $title = '';
    //     if (isset($showfields['title']) && ($showfields['title'])) {
    //         if (!empty(get_the_title($id))) {
    //             $title .= get_the_title($id);
    //         }
    //     }

    //     $content = '';
    //     if (!empty($title)) {
    //         if (!empty($titletag)) {
    //             $content .= '<' . $titletag . '>';
    //         }
    //         if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
    //             $content .= '<a href="' . $permalink . '">';
    //         }
    //         $content .= $title;
    //         if (isset($showfields['permalink']) && ($showfields['permalink']) && ($permalink)) {
    //             $content .= '</a>';
    //         }
    //         if (!empty($titletag)) {
    //             $content .= '</' . $titletag . '>';
    //         }
    //     }

    //     if (!empty($schema)) {
    //         $content .= $schema;
    //     }

    //     if (isset($showfields['bild']) && ($showfields['bild']) && has_post_thumbnail($id)) {
    //         $content .= Data::create_contact_image($id, 'full', "standort-image", false, false, '');
    //     }

    //     if (isset($showfields['content']) && ($showfields['content'])) {
    //         $post = get_post($id);
    //         if ($post->post_content) {
    //             $content .= '<div class="content">' . $post->post_content . '</div>';
    //         }
    //     }
    //     return $content;
    // }

}
