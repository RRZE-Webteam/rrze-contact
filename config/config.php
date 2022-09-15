<?php

namespace RRZE\Contact\Config;

defined('ABSPATH') || exit;

/**
 * Gibt der Name der Option zurück.
 * @return array [description]
 */
function getOptionName()
{
    return 'rrze-contact';
}

function getConstants()
{
    $options = array(
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
            "de" => __('German', 'rrze-synonym'),
            "en" => __('English', 'rrze-synonym'),
            "es" => __('Spanish', 'rrze-synonym'),
            "fr" => __('French', 'rrze-synonym'),
            "ru" => __('Russian', 'rrze-synonym'),
            "zh" => __('Chinese', 'rrze-synonym'),
        ],
        'colors' => [
            'med',
            'nat',
            'rw',
            'phil',
            'tk',
        ],
    );
    return $options;
}

/**
 * Gibt die Einstellungen des Menus zurück.
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
 * @return array [description]
 */
function getSections()
{
    return [
        [
            'id' => 'basic',
            'title' => __('Campo Settings', 'rrze-contact'),
        ],
    ];
}

/**
 * Gibt die Einstellungen der Optionsfelder zurück.
 * @return array [description]
 */
function getFields()
{
    return [
        'basic' => [
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
 * @return array [description]
 */

function getShortcodeSettings()
{
    return [
        'contact' => [
            'block' => [
                'blocktype' => 'fau-person/kontakt',
                'blockname' => 'kontakt',
                'title' => 'RRZE Kontakt',
                'category' => 'widgets',
                'icon' => 'id',
                'show_block' => 'content', // 'right' or 'content' 
            ],
            'id' => [
                'default' => 0,
                'label' => __('Id-Number des Kontakteintrags', 'fau-person'),
                'message' => __('Nummer der Eintrags der Kontaktliste im Backend. Nicht identisch mit einer optionalen UnivIS-Nummer.', 'fau-person'),
                'field_type' => 'text',
                'type' => 'number'
            ],
            'slug' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Slug (URI) des Kontakteintrags', 'fau-person'),
                'type' => 'text' // Variablentyp der Eingabe
            ],
            'category' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Kategorie', 'fau-person'),
                'type' => 'text' // Variablentyp der Eingabe
            ],

            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'fau-person'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'fau-person')
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Kurzliste', 'fau-person')
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'fau-person')
                    ],
                    [
                        'id' => 'page',
                        'val' => __('Seite', 'fau-person')
                    ],
                    [
                        'id' => 'liste',
                        'val' => __('Liste', 'fau-person')
                    ],
                    [
                        'id' => 'plain',
                        'val' => __('Unformatiert', 'fau-person')
                    ],
                    [
                        'id' => 'kompakt',
                        'val' => __('Kompakt', 'fau-person')
                    ],
                    [
                        'id' => 'card',
                        'val' => __('Karte', 'fau-person')
                    ],
                    [
                        'id' => 'table',
                        'val' => __('Tabelle', 'fau-person')
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],
            'sort' => [
                'default' => 'title',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'title',
                        'val' => __('Titel', 'fau-person')
                    ],
                    [
                        'id' => 'nachname',
                        'val' => __('Nachname', 'fau-person')
                    ],
                    [
                        'id' => 'name',
                        'val' => __('Vorname und Nachname', 'fau-person')
                    ],
                ],
            ],
            'order' => [
                'default' => 'asc',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'asc',
                        'val' => __('Von A bis Z', 'fau-person')
                    ],
                    [
                        'id' => 'desc',
                        'val' => __('Von Z bis A', 'fau-person')
                    ],
                ],
            ],

            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'fau-person'),
                'type' => 'integer'
            ],
            'class' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('CSS Klassen, die der Shordcode erhalten soll.', 'fau-person'),
                'type' => 'string'
            ],
            'background' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Farbcode für den Hintergrund.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('Kein', 'fau-person')
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'fau-person')
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'fau-person')
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'fau-person')
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'fau-person')
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'fau-person')
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'fau-person')
                    ],
                ],
            ],
        ],
        'contactlist' => [
            'block' => [
                'blocktype' => 'fau-person/kontaktliste',
                'blockname' => 'kontaktliste',
                'title' => 'RRZE Kontaktliste',
                'category' => 'widgets',
                'icon' => 'id-alt',
                'show_block' => 'content', // 'right' or 'content' 
            ],
            'category' => [
                'default' => '',
                'field_type' => 'string',
                'label' => __('Kategorie', 'fau-person'),
                'type' => 'string' // Variablentyp der Eingabe
            ],

            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'fau-person'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'fau-person')
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Kurzliste', 'fau-person')
                    ],
                    [
                        'id' => 'full',
                        'val' => __('Komplett', 'fau-person')
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'fau-person')
                    ],
                    [
                        'id' => 'liste',
                        'val' => __('Liste', 'fau-person')
                    ],
                    [
                        'id' => 'listentry',
                        'val' => __('Listeneintrag', 'fau-person')
                    ],
                    [
                        'id' => 'plain',
                        'val' => __('Unformatiert', 'fau-person')
                    ],
                    [
                        'id' => 'kompakt',
                        'val' => __('Kompakt', 'fau-person')
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],
            'sort' => [
                'default' => 'title',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'title',
                        'val' => __('Titel', 'fau-person')
                    ],
                    [
                        'id' => 'nachname',
                        'val' => __('Nachname', 'fau-person')
                    ],
                    [
                        'id' => 'name',
                        'val' => __('Vorname und Nachname', 'fau-person')
                    ],
                ],
            ],
            'order' => [
                'default' => 'asc',
                'field_type' => 'select',
                'label' => __('Bei der Ausgabe mehrerer Kontakten kann nach diesem Feld sortiert werden.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => 'asc',
                        'val' => __('Von A bis Z', 'fau-person')
                    ],
                    [
                        'id' => 'desc',
                        'val' => __('Von Z bis A', 'fau-person')
                    ],
                ],
            ],
            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'fau-person'),
                'type' => 'integer'
            ],
            'class' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('CSS Klassen, die der Shordcode erhalten soll.', 'fau-person'),
                'type' => 'string'
            ],
            'background' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Farbcode für den Hintergrund.', 'fau-person'),
                'type' => 'array',
                'values' => [
                    [
                        'id' => '',
                        'val' => __('Kein', 'fau-person')
                    ],
                    [
                        'id' => 'med',
                        'val' => __('Med: Blau', 'fau-person')
                    ],
                    [
                        'id' => 'phil',
                        'val' => __('Phil: Ocker', 'fau-person')
                    ],
                    [
                        'id' => 'tf',
                        'val' => __('TF: Silbern', 'fau-person')
                    ],
                    [
                        'id' => 'nat',
                        'val' => __('Nat: Meeresgrün', 'fau-person')
                    ],
                    [
                        'id' => 'rw',
                        'val' => __('RW: Bordeaurot', 'fau-person')
                    ],
                    [
                        'id' => 'fau',
                        'val' => __('FAU: Dunkelblau', 'fau-person')
                    ],
                ],
            ],

        ],
        'location' => [
            'block' => [
                'blocktype' => 'fau-person/standort',
                'blockname' => 'standort',
                'title' => 'RRZE Standort',
                'category' => 'widgets',
                'icon' => 'location-alt',
                'show_block' => 'content', // 'right' or 'content' 
            ],

            'id' => [
                'default' => 0,
                'label' => __('Id-Number des Standorteintrags', 'fau-person'),
                'message' => __('Nummer der Eintrags im Backend.', 'fau-person'),
                'field_type' => 'text',
                'type' => 'number'
            ],
            'slug' => [
                'default' => '',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('Slug (URI) des Kontakteintrags', 'fau-person'),
                'type' => 'string' // Variablentyp der Eingabe
            ],
            'titletag' => [
                'default' => 'h2',
                'field_type' => 'text', // Art des Feldes im Gutenberg Editor
                'label' => __('HTML-Element zur Darstellung des Standortnamens', 'fau-person'),
                'type' => 'string' // Variablentyp der Eingabe
            ],
            'hstart' => [
                'default' => 3,
                'field_type' => 'number',
                'label' => __('Überschriftenebene der ersten Überschrift', 'fau-person'),
                'type' => 'integer'
            ],
            'adresse' => [
                'default' => true,
                'field_type' => 'checkbox',
                'label' => __('Telefonnummer anzeigen', 'fau-person'),
                'type' => 'boolean'
            ],
            'format' => [
                'default' => '',
                'field_type' => 'select',
                'label' => __('Format', 'fau-person'),
                'type' => 'string',
                'values' => [
                    [
                        'id' => 'name',
                        'val' => __('Name', 'fau-person')
                    ],
                    [
                        'id' => 'shortlist',
                        'val' => __('Kurzliste', 'fau-person')
                    ],
                    [
                        'id' => 'full',
                        'val' => __('Komplett', 'fau-person')
                    ],
                    [
                        'id' => 'sidebar',
                        'val' => __('Sidebar', 'fau-person')
                    ],
                    [
                        'id' => 'liste',
                        'val' => __('Liste', 'fau-person')
                    ],
                ],
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Anzuzeigende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Zu versteckende Felder, obige Checkboxen überschreibend', 'fau-person'),
                'type' => 'string'
            ],

        ]

    ];
}


function get_rrze_contact_capabilities() {
	return [
		'edit_post'	=> 'edit_person',
		'read_post'	=> 'read_person',
		'delete_post'	=> 'delete_person',
		'edit_posts'	=> 'edit_persons',
		'edit_others_posts' => 'edit_others_persons',
		'publish_posts'	=> 'publish_persons',
		'read_private_posts' => 'read_private_persons',
		'delete_posts'	=> 'delete_persons',
		'delete_private_posts' => 'delete_private_persons',
		'delete_published_posts' => 'delete_published_persons',
		'delete_others_posts' => 'delete_others_persons',
		'edit_private_posts' => 'edit_private_persons',
		'edit_published_posts' => 'edit_published_persons'
	    ];
    }