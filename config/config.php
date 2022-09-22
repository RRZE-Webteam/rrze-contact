<?php

namespace RRZE\Contact\Config;

defined('ABSPATH') || exit;

/**
 * Gibt der Name der Option zurück.
 *
 * @return string [description]
 */
function getOptionName()
{
    return 'rrze-contact';
}

function getConstants()
{
    $options = [
        'fauthemes' => [
            'FAU-Einrichtungen',
            'FAU-Einrichtungen-BETA',
            'FAU-Medfak',
            'FAU-RWFak',
            'FAU-Philfak',
            'FAU-Techfak',
            'FAU-Natfak',
            'FAU-Blog',
            'FAU-Jobs',
        ],
        'rrzethemes' => [
            'RRZE 2019',
        ],
        'langcodes' => [
            'de' => __('German', 'rrze-contact'),
            'en' => __('English', 'rrze-contact'),
            'es' => __('Spanish', 'rrze-contact'),
            'fr' => __('French', 'rrze-contact'),
            'ru' => __('Russian', 'rrze-contact'),
            'zh' => __('Chinese', 'rrze-contact'),
        ],
        'colors' => [
            'med',
            'nat',
            'rw',
            'phil',
            'tk',
        ],
    ];

    return $options;
}

/**
 * Gibt die Einstellungen des Menus zurück.
 *
 * @return array [description]
 */
function getMenuSettings()
{
    return [
        'page_title' => __('RRZE Contact', 'rrze-contact'),
        'menu_title' => __('RRZE Contact', 'rrze-contact'),
        'capability' => 'manage_options',
        'menu_slug' => 'rrze-contact',
        'title' => __('RRZE Contact Settings', 'rrze-contact'),
    ];
}

/**
 * Gibt die Einstellungen der Optionsbereiche zurück.
 *
 * @return array [description]
 */
function getSections()
{
    return [
        [
            'id' => 'sidebar',
            'title' => __('Sidebar Contact', 'rrze-contact'),
        ],

        [
            'id' => 'constants',
            'title' => __('Erweiterte Einstellungen', 'rrze-contact'),
        ],
        [
            'id' => 'api',
            'title' => __('API Settings', 'rrze-contact'),
        ],
    ];
}

/**
 * Gibt die Einstellungen der Optionsfelder zurück.
 *
 * @return array [description]
 */
function getFields()
{

    $imagesizes = array();
    $isizes = get_all_image_sizes();

    foreach ($isizes as $key => $value) {
        if (($value['width'] > 0) && ($value['height'] > 0)) {
            $name = ucfirst($key);
            $imagesizes[$key] = $name . ' (' . $value['width'] . ' x ' . $value['height'] . ')';
        }
    }

    $archive_options =	[
		'person' => __('Automatisch generieren', 'rrze-contact') . ' ('  . site_url( '/person/') . ')',
		'0' => __('Keine Übersichtsseite anzeigen', 'rrze-contact'),
	];

	$pages = get_pages(); 
	foreach($pages  as $page){
		$archive_options[$page->post_name] = ($page->post_parent != 0 ? '- ' : '') . $page->post_title;
	}

    return [
        'sidebar' => [
            [
                'name' => 'titel',
                'label' => __('Akademischer Titel', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'familyName',
                'label' => __('Nachname', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'givenName',
                'label' => __('Vorname', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'name',
                'label' => __('Vollständiger Name', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'suffix',
                'label' => __('Suffix (nachgestellter Titel)', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'position',
                'label' => __('Position', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'organisation',
                'label' => __('Organisation', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'abteilung',
                'label' => __('Abteilung', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'adresse',
                'label' => __('Adresse', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'workLocation',
                'label' => __('Raum', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'telefon',
                'label' => __('Telefonnummer', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'mobil',
                'label' => __('Handynummer anzeigen', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'fax',
                'label' => __('Faxnummer', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => false,
                'default' => false,
            ],
            [
                'name' => 'mail',
                'label' => __('E-Mail-Adresse', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'webseite',
                'label' => __('Website', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'sprechzeiten',
                'label' => __('Sprechzeiten', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'kurzauszug',
                'label' => __('Kurzbeschreibung', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'bild',
                'label' => __('Bild', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'socialmedia',
                'label' => __('Social Media Links', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'ansprechpartner',
                'label' => __('Ansprechpartner', 'rrze-contact'),
                'desc' => __('Im Sidebar-Widget werden nur dann Ansprechpartner gezeigt, wenn dieser Wert aktiviert ist oder alternativ bei dem Personeneintrag eingestellt ist, daß der Kontakt ausschließlich über angegebene Ansprechpartner erfolgt.', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => false,
                'default' => false,
            ],
        ],

        'constants' => [
            [
                'name' => 'view_telefonlink',
                'label' => __('Telefonnummer als Link', 'rrze-contact'),
                'desc' => __('Setzt die Telefonnummer als Link, so dass mobile Endgeräte und darauf vorbereitet Software bei einem Klick die Telefonwahlfunktion aufrufen.', 'rrze-contact'),
                'type' => 'checkbox',
                'default' => true,
            ],
            [
                'name' => 'view_telefon_intformat',
                'label' => __('Internationales Nummernformat', 'rrze-contact'),
                'desc' => __('Die Telefonnnummer wird in dem internationalen Format angezeigt.', 'rrze-contact'),
                'type' => 'checkbox',
                'default' => true,
            ],
            [
                'name' => 'view_some_position',
                'label' => __('Position Social Media Icons', 'rrze-contact'),
                'default' => 'nach-kontakt',
                'type' => 'Select',
                'options' => [
                    'nach-kontakt' => __('Nach den Kontaktdaten', 'rrze-contact'),
                    'nach-name' => __('Direkt nach dem Namen', 'rrze-contact'),
                ],
            ],

            [
                'name' => 'view_raum_prefix',
                'default' => __('Raum', 'rrze-contact'),
                'placeholder' => __('Raum', 'rrze-contact'),
                'label' => __('Anzuzeigender Text vor der Raumangabe', 'rrze-contact'),
                'field_type' => 'text',
                'type' => 'text',
            ],
            [
                'name' => 'view_kontakt_linktext',
                'default' => __('Mehr', 'rrze-contact') . ' ›',
                'placeholder' => __('Mehr', 'rrze-contact') . ' ›',
                'label' => __('Linktext für Kontaktseite', 'rrze-contact'),
                'field_type' => 'text',
                'type' => 'text',
            ],
            [
                'name' => 'view_kontakt_linkname',
                'label' => __('Link auf Kontaktname', 'rrze-contact'),
                'default' => 'use-link',
                'type' => 'Select',
                'options' => [
                    'use-link' => __('Linkziel im Kontakteintrag', 'rrze-contact'),
                    'permalink' => __('Kontaktseite', 'rrze-contact'),
                    'url' => __('URL aus Profil', 'rrze-contact'),
                    'force-nolink' => __('Nicht verlinken, URL im Kontakteintrag ignorieren', 'rrze-contact'),
                ],
            ],
            [
                'name' => 'view_kontakt_page_imagecaption',
                'label' => __('Bildbeschriftung Kontaktseite', 'rrze-contact'),
                'desc' => __('Zeigt auf der Kontaktvisitenkarte und bei Nutzung des Shortcodes mit dem Attribut <code>format="page"</code> die Bildunterschriften eines Kontaktbildes an.', 'rrze-contact'),
                'type' => 'checkbox',
                'checked' => true,
                'default' => true,
            ],
            [
                'name' => 'view_kontakt_page_imagesize',
                'label' => __('Bildformat Kontaktseite', 'rrze-contact'),
                'desc' => __('Setzt auf der Kontaktseite oder bei Nutzung des Shortcodes mit dem Attribut <code>format="page"</code> das zu verwendete Bildformat.', 'rrze-contact'),
                'default' => 'person-thumb-page-v3',
                'type' => 'Selectimagesizes',
                'options' => $imagesizes,
            ],
            [
                'name' => 'view_thumb_size',
                'label' => __('Größe Thumbnail', 'rrze-contact'),
                'desc' => __('Größe der Thumbnails bei der Anzeige von Kontaktlisten und Kontakt-Shortcodes, die nicht vom Format "page" oder "card" sind.', 'rrze-contact'),
                'default' => 'small',
                'type' => 'Select',
                'options' => [
                    'small' => __('Klein (80 Pixel)', 'rrze-contact'),
                    'medium' => __('Mittel (100 Pixel)', 'rrze-contact'),
                    'large' => __('Groß (120 Pixel)', 'rrze-contact'),
                    // Notice: Take sure, that the maximum size will match to the thumbnail-size defined in the options array
                ],
            ],
            [
                'name' => 'view_card_size',
                'label' => __('Größe Personen-Karten', 'rrze-contact'),
                'desc' => __('Größe der Personen-Karten bei Nutzung des Formats <code>format="card"</code>.', 'rrze-contact'),
                'default' => 'small',
                'type' => 'Select',
                'options' => [
                    'xsmall' => __('Sehr klein (150 Pixel)', 'rrze-contact'),
                    'small' => __('Klein (200 Pixel)', 'rrze-contact'),
                    'medium' => __('Mittel (250 Pixel)', 'rrze-contact'),
                    'large' => __('Groß (300 Pixel)', 'rrze-contact'),
                ],
            ],
            [
                'name' => 'view_card_linkimage',
                'label' => __('Personenbild verlinken', 'rrze-contact'),
                'desc' => __('In der Card-Ansicht auch das Personenbild als Link setzen.', 'rrze-contact'),
                'type' => 'checkbox',
                'default' => false,
            ],
            [
                'name' => 'backend_view_metabox_kontaktlist',
                'label' => __('Metabox Kontakte', 'rrze-contact'),
                'desc' => __('Zeigt in der Bearbeitung von Seiten und Beiträgen eine Kontakt-Box an, aus der man bequem die Liste der Kontakte ablesen kann.', 'rrze-contact'),
                'type' => 'checkbox',
                'default' => true,
            ],
            [
                'name' => 'has_archive_page',
                'label' => __('Kontakt-Übersichtsseite', 'rrze-contact'),
                'desc' => __('Die automatisch generierte Übersichtsseite zeigt alle Kontakte an und ist stets aktuell.', 'rrze-contact'),
                'default' => 'person',
                'type' => 'Select',
                'options' => $archive_options,
            ],
        ],
        'api' => [
            [
                'name' => 'url',
                'label' => __('Link to Campo', 'rrze-contact'),
                'desc' => __('', 'rrze-contact'),
                'placeholder' => __('', 'rrze-contact'),
                'type' => 'text',
                'default' => 'https://www.campo.fau.de/',
                'sanitize_callback' => 'sanitize_url',
            ],
            [
                'name' => 'linkTxt',
                'label' => __('Text for the link to Campo', 'rrze-contact'),
                'desc' => __('', 'rrze-contact'),
                'placeholder' => __('', 'rrze-contact'),
                'type' => 'text',
                'default' => __('Campo - Information System of the FAU', 'rrze-contact'),
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'Campo_ApiKey',
                'label' => __('Campo ApiKey', 'rrze-contact'),
                'desc' => __('If you are not using a multisite installation of Wordpress, contact rrze-integration@fau.de to receive this key.', 'rrze-settings'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'campoID',
                'label' => __('Campo ID', 'rrze-contact'),
                'desc' => __('To receive lectures from another department use the attribute <strong>campoID</strong> in the shortcode. F.e. [lectures campoID="123"]', 'rrze-contact'),
                'placeholder' => '',
                'type' => 'text',
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            [
                'name' => 'hstart',
                'label' => __('Headline\'s size', 'rrze-contact'),
                'desc' => __('Headlines start at this size.', 'rrze-contact'),
                'min' => 2,
                'max' => 10,
                'step' => '1',
                'type' => 'number',
                'default' => '2',
                'sanitize_callback' => 'floatval',
            ],
        ],
    ];
}

/**
 * Gibt die Einstellungen der Parameter für Shortcode für den klassischen Editor und für Gutenberg zurück.
 *
 * @return array [description]
 */
function getShortcodeSettings()
{
    return [
        'contact' => [
            'block' => [
                'blocktype' => 'rrze-contact/contact',
                'blockname' => 'contact',
                'title' => __('RRZE Contact', 'rrze-contact'),
                'category' => 'widgets',
                'icon' => 'id',
                'show_block' => 'content', // 'right' or 'content'
            ],
            'id' => [
                'default' => 0,
                'label' => __('Id-Number of the contact', 'rrze-contact'),
                'message' => __('Number of the contact entry in the backend. It is not identical to the optional UnivIS number.', 'rrze-contact'),
                'field_type' => 'text',
                'type' => 'number',
            ],
            'slug' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Slug (URI) of the contact', 'rrze-contact'),
                'type' => 'text', // Variablentyp der Eingabe
            ],
            'category' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Category', 'rrze-contact'),
                'type' => 'text', // Variablentyp der Eingabe
            ],

            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'rrze-contact'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'rrze-contact'),
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Short list', 'rrze-contact'),
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'rrze-contact'),
                    ],
                    [
                        'id' => 'page',
                        'val' => __('Page', 'rrze-contact'),
                    ],
                    [
                        'id' => 'list',
                        'val' => __('List', 'rrze-contact'),
                    ],
                    [
                        'id' => 'plain',
                        'val' => __('Unformatted', 'rrze-contact'),
                    ],
                    [
                        'id' => 'compact',
                        'val' => __('compact', 'rrze-contact'),
                    ],
                    [
                        'id' => 'card',
                        'val' => __('Card', 'rrze-contact'),
                    ],
                    [
                        'id' => 'table',
                        'val' => __('Table', 'rrze-contact'),
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
            'sort' => [
                'default' => 'title',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'title',
                        'val' => __('Titel', 'rrze-contact'),
                    ],
                    [
                        'id' => 'nachname',
                        'val' => __('Nachname', 'rrze-contact'),
                    ],
                    [
                        'id' => 'name',
                        'val' => __('Vorname und Nachname', 'rrze-contact'),
                    ],
                ],
            ],
            'order' => [
                'default' => 'asc',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'asc',
                        'val' => __('Von A bis Z', 'rrze-contact'),
                    ],
                    [
                        'id' => 'desc',
                        'val' => __('Von Z bis A', 'rrze-contact'),
                    ],
                ],
            ],

            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'rrze-contact'),
                'type' => 'integer',
            ],
            'class' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('CSS Klassen, die der Shordcode erhalten soll.', 'rrze-contact'),
                'type' => 'string',
            ],
            'background' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Farbcode für den Hintergrund.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('Kein', 'rrze-contact'),
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'rrze-contact'),
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'rrze-contact'),
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'rrze-contact'),
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'rrze-contact'),
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'rrze-contact'),
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'rrze-contact'),
                    ],
                ],
            ],
        ],
        'contactlist' => [
            'block' => [
                'blocktype' => 'rrze-contact/contactlist',
                'blockname' => 'contactlist',
                'title' => __('RRZE Contact List', 'rrze-contact'),
                'category' => 'widgets',
                'icon' => 'id-alt',
                'show_block' => 'content', // 'right' or 'content'
            ],
            'category' => [
                'default' => '',
                'field_type' => 'string',
                'label' => __('Category', 'rrze-contact'),
                'type' => 'string', // Variablentyp der Eingabe
            ],

            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'rrze-contact'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'rrze-contact'),
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Short list', 'rrze-contact'),
                    ],
                    [
                        'id' => 'full',
                        'val' => __('Komplett', 'rrze-contact'),
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'rrze-contact'),
                    ],
                    [
                        'id' => 'liste',
                        'val' => __('Liste', 'rrze-contact'),
                    ],
                    [
                        'id' => 'listentry',
                        'val' => __('Listeneintrag', 'rrze-contact'),
                    ],
                    [
                        'id' => 'plain',
                        'val' => __('Unformatiert', 'rrze-contact'),
                    ],
                    [
                        'id' => 'kompakt',
                        'val' => __('Kompakt', 'rrze-contact'),
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
            'sort' => [
                'default' => 'title',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'title',
                        'val' => __('Titel', 'rrze-contact'),
                    ],
                    [
                        'id' => 'nachname',
                        'val' => __('Nachname', 'rrze-contact'),
                    ],
                    [
                        'id' => 'name',
                        'val' => __('Vorname und Nachname', 'rrze-contact'),
                    ],
                ],
            ],
            'order' => [
                'default' => 'asc',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'asc',
                        'val' => __('Von A bis Z', 'rrze-contact'),
                    ],
                    [
                        'id' => 'desc',
                        'val' => __('Von Z bis A', 'rrze-contact'),
                    ],
                ],
            ],
            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'rrze-contact'),
                'type' => 'integer',
            ],
            'class' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('CSS Klassen, die der Shordcode erhalten soll.', 'rrze-contact'),
                'type' => 'string',
            ],
            'background' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Farbcode für den Hintergrund.', 'rrze-contact'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('Kein', 'rrze-contact'),
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'rrze-contact'),
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'rrze-contact'),
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'rrze-contact'),
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'rrze-contact'),
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'rrze-contact'),
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'rrze-contact'),
                    ],
                ],
            ],
        ],
        'location' => [
            'block' => [
                'blocktype' => 'rrze-contact/standort',
                'blockname' => 'standort',
                'title' => 'RRZE Standort',
                'category' => 'widgets',
                'icon' => 'location-alt',
                'show_block' => 'content', // 'right' or 'content'
            ],

            'id' => [
                'default' => 0,
                'label' => __('Id-Number des Standorteintrags', 'rrze-contact'),
                'message' => __('Nummer der Eintrags im Backend.', 'rrze-contact'),
                'field_type' => 'text',
                'type' => 'number',
            ],
            'slug' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Slug (URI) des Kontakteintrags', 'rrze-contact'),
                'type' => 'string', // Variablentyp der Eingabe
            ],
            'titletag' => [
                'default' => 'h2',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('HTML-Element zur Darstellung des Standortnamens', 'rrze-contact'),
                'type' => 'string', // Variablentyp der Eingabe
            ],
            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'rrze-contact'),
                'type' => 'integer',
            ],
            'adresse' => [
                'default' => true,
                'field_type' => 'checkbox',
                'label' => __('Telefonnummer anzeigen', 'rrze-contact'),
                'type' => 'boolean',
            ],
            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'rrze-contact'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'rrze-contact'),
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Kurzliste', 'rrze-contact'),
                    ],
                    [
                        'id' => 'full',
                        'val' => __('Komplett', 'rrze-contact'),
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'rrze-contact'),
                    ],
                    [
                        'id' => 'liste',
                        'val' => __('Liste', 'rrze-contact'),
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'rrze-contact'),
                'type' => 'string',
            ],
        ],
    ];
}

function get_all_image_sizes()
{

    $image_sizes = array();

    $ownsizes = getConstants();
    if (isset($ownsizes['images']['default_person_thumb_width'])) {
        $image_sizes['person-thumb-v3']['width'] = $ownsizes['images']['default_person_thumb_width'];
        $image_sizes['person-thumb-v3']['height'] = $ownsizes['images']['default_person_thumb_height'];

    }
    if (isset($ownsizes['images']['default_person_thumb_page_width'])) {
        $image_sizes['person-thumb-page-v3']['width'] = $ownsizes['images']['default_person_thumb_page_width'];
        $image_sizes['person-thumb-page-v3']['height'] = $ownsizes['images']['default_person_thumb_page_height'];
    }

    return $image_sizes;
}

function getSocialMediaList()
{
    $SocialMedia = array(
        "twitter" => [
            'title' => 'Twitter',
            'class' => 'twitter',
        ],
        "facebook" => [
            'title' => 'Facebook',
            'class' => 'facebook',
        ],
        "linkedin" => [
            'title' => 'LinkedIn',
            'class' => 'linkedin',
        ],
        "instagram" => [
            'title' => 'Instagram',
            'class' => 'instagram',
        ],
        "xing" => [
            'title' => 'Xing',
            'class' => 'xing',
        ],
        "youtube" => [
            'title' => 'YouTube',
            'class' => 'youtube',
        ],
        "github" => [
            'title' => 'GitHub',
            'class' => 'github',
        ],
        "publons" => [
            'title' => 'Publons',
            'class' => 'publons',
        ],
        "scopus" => [
            'title' => 'Scopus',
            'class' => 'scopus',
        ],
        "googlescholar" => [
            'title' => 'Google Scholar',
            'class' => 'google-scholar',
        ],
        "orcid" => [
            'title' => 'ORCID',
            'class' => 'orcid',
        ],
        "researchgate" => [
            'title' => 'Research Gate',
            'class' => 'researchgate',
        ],
        "tiktok" => [
            'title' => 'TikTok',
            'class' => 'tiktok',
        ],
    );

    // für ergänzende Optionen aus anderen Plugins
    $SocialMedia = apply_filters('rrze_contact_socialmedialist', $SocialMedia);
    return $SocialMedia; // Standard-Array für zukünftige Optionen
}
