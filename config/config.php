<?php

namespace RRZE\Campo\Config;

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
        'lectures' => [
            'block' => [
                'blocktype' => 'rrze-contact/campolectures',
                'blockname' => 'campolectures',
                'title' => 'RRZE-Campo',
                'category' => 'widgets',
                'icon' => 'bank',
                'tinymce_icon' => 'paste',
            ],
            'id' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecture ID', 'rrze-contact'),
                'type' => 'string',
            ],
            'name' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Firstname, Lastname', 'rrze-contact'),
                'type' => 'string',
            ],
            'campoid' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Person ID', 'rrze-contact'),
                'type' => 'string',
            ],
            'lecturerID' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Lecturer ID', 'rrze-contact'),
                'type' => 'string',
            ],
            'type' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Type f.e. vorl (=Vorlesung)', 'rrze-contact'),
                'type' => 'string',
            ],
            'order' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Sort by type f.e. "vorl,ueb"', 'rrze-contact'),
                'type' => 'string',
            ],
            'sem' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Semester f.e. 2020w', 'rrze-contact'),
                'type' => 'string',
            ],
            'lang' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Language', 'rrze-contact'),
                'type' => 'string',
            ],
            'show' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Show', 'rrze-contact'),
                'type' => 'string',
            ],
            'hide' => [
                'default' => '',
                'field_type' => 'text',
                'label' => __('Hide', 'rrze-contact'),
                'type' => 'string',
            ],
            'hstart' => [
                'default' => 2,
                'field_type' => 'text',
                'label' => __('Headline\'s size', 'rrze-contact'),
                'type' => 'number',
            ],
            'nodata' => [
                'default' => __('No matching entries found.', 'rrze-contact'),
                'field_type' => 'text',
                'label' => __('Show', 'rrze-contact'),
                'type' => 'string',
            ],
        ],
    ];
}
