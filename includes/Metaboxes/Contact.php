<?php

namespace RRZE\Contact\Metaboxes;

use function RRZE\Contact\Config\getFields;
use RRZE\Contact\API\UnivIS;
use RRZE\Contact\Data;
use RRZE\Contact\Functions;
use RRZE\Contact\Sanitize;

defined('ABSPATH') || exit;

/**
 * Define Metaboxes for Contact-Edit
 */
class Contact extends Metaboxes
{

    protected $pluginFile;
    private $settings = '';
    public $bUnivisSync = false;
    public $aDisabled = [];
    public $univisData = [];
    public $postMeta = [];
    public $descFound = '';
    public $descNotFound = '';
    public $univisID = 0;

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
        $this->descFound = __('Value displayed from UnivIS:', 'rrze-contact') . ' ';
        $this->descNotFound = __('No value is stored for this in UnivIS.', 'rrze-contact');

        add_action('save_post', [$this, 'saveMeta'], 12, 3); // priority 10 would not work because post_meta is not stored yet. save_post_contact would not work because save_post is fired after save_post_contact
    }

    public function saveMeta($postID, $post_after, $post_before)
    {
        if (get_post_type($postID) != 'contact') {
            return;
        }

        $this->bUnivisSync = get_post_meta($postID, $this->prefix . 'univis_sync', true);
        $this->univisID = get_post_meta($postID, $this->prefix . 'univis_id', true);

        if (!empty($this->bUnivisSync) && !empty($this->univisID)) {
            $aDisabled = [];
            $univis = new UnivIS();
            $univisResponse = $univis->getPerson('id=' . $this->univisID);

            if ($univisResponse['valid']) {
                $this->univisData = $univisResponse['content'][0];

                // echo '<pre>';
                // var_dump($this->univisData);
                // exit;

                if (!empty($this->univisData)) {
                    $aFields = getFields('contact');
                    foreach ($aFields as $aDetails) {
                        if (!empty($this->univisData[$aDetails['name']])) {
                            $value = $this->univisData[$aDetails['name']];
                            $aDisabled[] = $aDetails['name'];
                            update_post_meta($postID, $this->prefix . $aDetails['name'], $value);
                        }
                    }

                    $aGroups = ['locations', 'consultations'];
                    foreach ($aGroups as $group) {
                        $aFields = getFields($group);
                        if (!empty($this->univisData[$group])) {
                            $aLocationsGroup = [];
                            foreach ($this->univisData[$group] as $nr => $location) {
                                $tmp = [];
                                foreach ($location as $field => $value) {
                                    $tmp[$this->prefix . $field] = $value;
                                    $aDisabled[] = $this->prefix . $group . 'Group_' . $nr . '_' . $this->prefix . $field;
                                }
                                $aLocationsGroup[$nr] = $tmp;
                            }
                            update_post_meta($postID, $this->prefix . $group . 'Group', $aLocationsGroup);
                        }
                    }
                }
            }

            if (!empty($this->bUnivisSync)) {
                update_post_meta($postID, $this->prefix . 'disabled', $aDisabled);
            }
        }
    }

    public function onLoaded()
    {
        add_action('cmb2_admin_init', [$this, 'makeContactMetaboxes']);

    }

    public function makeContactMetaboxes()
    {
        // Meta-Box Contact
        $contactselect_connection = Data::get_contactdata(1);
        $default_rrze_contact_typ = Data::get_default_rrze_contact_typ();

        $postID = intval(!empty($_GET['post']) ? $_GET['post'] : (!empty($_POST['post_ID']) ? $_POST['post_ID'] : 0));

        $this->bUnivisSync = get_post_meta($postID, $this->prefix . 'univis_sync', true);
        $this->bUnivisSync = !empty($this->bUnivisSync);
        $this->aDisabled = get_post_meta($postID, $this->prefix . 'disabled', true);
        $this->univisID = get_post_meta($postID, $this->prefix . 'univis_id', true);

        $univisSyncTxt = '';


        // $test = 'w2 4,5';
        // $aRet = Functions::getRepeat($test);
        // echo '<pre>';
        // var_dump($aRet);
        // exit;

        // $test = get_post_meta($postID);
        // echo '<pre>';
        // var_dump($test['_rrze_contact_consultationsGroup']);
        // exit;
        


        if ($this->univisID) {
            $univis = new UnivIS();
            $univisResponse = $univis->getPerson('id=' . $this->univisID);

            if ($univisResponse['valid']) {
                $this->univisData = $univisResponse['content'][0];
            } else {
                $univisSyncTxt = '<p class="cmb2-metabox-description">' . __('Derzeit sind keine Daten aus UnivIS syncronisiert.', 'rrze-contact') . '</p>';
            }
        }

        $aFields = $this->makeCMB2fields(getFields('contact'));
        
        if ($this->bUnivisSync) {
            $aFields['honorificPrefix']['type'] = 'text'; // Because W3C does not support "readonly" for select type is set to text
        } else {
            $honoricPrefix = get_post_meta($postID, $this->prefix . 'honorificPrefix', true);
            $honoricPrefix = (empty($honoricPrefix) ? '' : $honoricPrefix);
            $aFields['honorificPrefix']['options'] = $this->getHonorificPrefixOptions($honoricPrefix);
        }

        $aFields['sortField'] = [
            'name' => __('Sortierfeld', 'rrze-contact'),
            'description' => __('Wird für eine Sortierung verwendet, die sich weder nach Name, Titel der Contactseite oder Vorname richten soll. Geben SIe hier Buchstaben oder Zahlen ein, nach denen sortiert werden sollen. Zur Sortierunge der Einträge geben Sie im Shortcode das Attribut <code>sort="sortierfeld"</code> ein.', 'rrze-contact'),
            'type' => 'text_small',
            'id' => $this->prefix . 'sortField',
            'show_on_cb' => 'callback_cmb2_show_on_institution',
        ];

        $myUrl = get_permalink($postID);
        $linkOptions = [$myUrl => __('Automatically generated contact page', 'rrze-contact')];

        $pages = get_pages();
        foreach ($pages as $page) {
            $linkOptions[$page->post_name] = ($page->post_parent != 0 ? '- ' : '') . $page->post_title;
        }

        $aFields['link'] = [
            'name' => __('Name und "Mehr"-Link verlinken auf Seite ...', 'rrze-contact'),
            'desc' => __('Choose a page or the automatically generated page for contact details.', 'rrze-contact'),
            'type' => 'select',
            'id' => $this->prefix . 'link',
            'options' => $linkOptions,
            'default' => $myUrl,
        ];

        // $vcard = new Vcard($this->univisData);
        // echo $vcard->showCard();
        // $vcard->showCardQR();
        // echo '<img src="' . $vcard->showCardQR() . '">';
        // exit;

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_info',
            'title' => __('Contact\'s informations', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $aFields,
        ]);

        // Meta-Box Excerpt
        $defaultExcerpt = get_post_field('post_excerpt', $postID);

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_textinfos',
            'title' => __('Contact description in shortform', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                [
                    'name' => __('Excerpt', 'rrze-contact'),
                    'desc' => __('Kurzform und Zusammenfassung der Contactbeschreibung bei Nutzung des Attributs <code>show="description"</code>.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $this->prefix . 'description',
                    'default' => $defaultExcerpt,
                ],

                [
                    'name' => __('Kurzbeschreibung (Sidebar und Kompakt)', 'rrze-contact'),
                    'desc' => __('Diese Kurzbeschreibung wird bei der Anzeige von <code>show="description"</code> in einer Sidebar (<code>format="sidebar"</code>) oder einer Liste (<code>format="kompakt"</code>) verwendet.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $this->prefix . 'small_description',
                    'default' => $defaultExcerpt,
                ],
            ],
        ]);

        // Meta-Box Locations
        $locationDefault = Data::get_standort_defaults($postID);
        $locationSelect = Data::get_standortdata();

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_locations',
            'title' => __('Locations', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Zugeordneter Standort', 'rrze-contact'),
                    'type' => 'select',
                    'id' => $this->prefix . 'standort_id',
                    'options' => $locationSelect,
                ],
                [
                    'name' => __('Standort-Daten für Adressanzeige nutzen', 'rrze-contact'),
                    'desc' => __('Die Adressdaten werden aus dem Standort bezogen; die folgenden optionalen Felder und Adressdaten aus UnivIS werden überschrieben.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $this->prefix . 'standort_sync',
                ],
            ],
        ]);

        $groupID = $cmb->add_field([
            'id' => $this->prefix . 'locationsGroup',
            'type' => 'group',
            'repeatable' => !$this->bUnivisSync,
            'options' => [
                'group_title' => __('Location', 'rrze-contact') . ' {#}',
                'add_button' => __('Add location', 'rrze-contact'),
                'remove_button' => __('Delete location', 'rrze-contact'),
            ],
            'fields' => $this->makeCMB2fields(getFields('locations')),
        ]);

        // Meta-Box Social Media

        // foreach ($smList as $key => $value) {
        //     $smFields[] = [
        //         'name' => $smList[$key]['title'] . ' URL',
        //         'id' => $this->prefix . $key . '_url',
        //         'type' => 'text_url',
        //         'protocols' => ['https'],
        //     ];
        // }

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_socialmedia',
            'title' => __('Social Media', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $this->makeCMB2fields(getFields('socialmedia')),
        ]);

        // Meta-Box Cosultations
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_consultations',
            'title' => __('Consultations', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Cosultations: headline', 'rrze-contact'),
                    'desc' => __('Show as bold above the csultations', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $this->prefix . 'hoursAvailable_text',
                ],
                [
                    'name' => __('Sprechzeiten: Allgemeines oder Anmerkungen', 'rrze-contact'),
                    'desc' => __('Zur Formatierung können HTML-Befehle verwendet werden (z.B. &lt;br&gt; für Zeilenumbruch). Wird vor den Sprechzeiten ausgegeben.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $this->prefix . 'hoursAvailable',
                ],
            ],
        ]);

        $groupID = $cmb->add_field([
            'id' => $this->prefix . 'consultationsGroup',
            'type' => 'group',
            'repeatable' => !$this->bUnivisSync,
            'options' => [
                'group_title' => __('Consultation', 'rrze-contact') . ' {#}',
                'add_button' => __('Add consultation', 'rrze-contact'),
                'remove_button' => __('Delete consultation', 'rrze-contact'),
            ],
            'fields' => $this->makeCMB2fields(getFields('consultations')),
        ]);

        //         [
        //             'id' => $this->prefix . 'hoursAvailable_group',
        //             'type' => 'group',
        //             'options' => [
        //                 'group_title' => __('Sprechzeit {#}', 'rrze-contact'),
        //                 'add_button' => __('Weitere Sprechzeit einfügen', 'rrze-contact'),
        //                 'remove_button' => __('Sprechzeit löschen', 'rrze-contact'),
        //             ],
        //             'fields' => [
        //                 [
        //                     'name' => __('Wiederholung', 'rrze-contact'),
        //                     'id' => 'repeat',
        //                     'type' => 'radio_inline',
        //                     'options' => [
        //                         '-' => __('Keine', 'rrze-contact'),
        //                         'd1' => __('täglich', 'rrze-contact'),
        //                         'w1' => __('wöchentlich', 'rrze-contact'),
        //                         'w2' => __('alle 2 Wochen', 'rrze-contact'),
        //                     ],
        //                 ],
        //                 [
        //                     'name' => __('am', 'rrze-contact'),
        //                     'id' => 'repeat_submode',
        //                     'type' => 'multicheck',
        //                     'options' => [
        //                         '1' => __('Montag', 'rrze-contact'),
        //                         '2' => __('Dienstag', 'rrze-contact'),
        //                         '3' => __('Mittwoch', 'rrze-contact'),
        //                         '4' => __('Donnerstag', 'rrze-contact'),
        //                         '5' => __('Freitag', 'rrze-contact'),
        //                         '6' => __('Samstag', 'rrze-contact'),
        //                         '7' => __('Sonntag', 'rrze-contact'),
        //                     ],
        //                 ],
        //                 [
        //                     'name' => __('von', 'rrze-contact'),
        //                     'id' => 'starttime',
        //                     'type' => 'text_time',
        //                     'time_format' => 'H:i',
        //                 ],
        //                 [
        //                     'name' => __('bis', 'rrze-contact'),
        //                     'id' => 'endtime',
        //                     'type' => 'text_time',
        //                     'time_format' => 'H:i',
        //                 ],
        //                 [
        //                     'name' => __('Raum', 'rrze-contact'),
        //                     'id' => 'office',
        //                     'type' => 'text_small',
        //                 ],
        //                 [
        //                     'name' => __('Bemerkung', 'rrze-contact'),
        //                     'id' => 'comment',
        //                     'type' => 'text',
        //                 ],
        //             ],
        //         ],
        //     ],
        // ]);

        // Meta-Box Synchronisierung mit externen Daten - rrze_contact_sync ab hier
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_sync',
            'title' => __('Metadaten zum Contact', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'side',
            'priority' => 'high',
            'fields' => [
                [
                    'name' => __('Typ des Eintrags', 'rrze-contact'),
                    'type' => 'select',
                    'options' => [
                        'realcontact' => __('Person (allgemein)', 'rrze-contact'),
                        'realmale' => __('Person (männlich)', 'rrze-contact'),
                        'realfemale' => __('Person (weiblich)', 'rrze-contact'),
                        'einrichtung' => __('Einrichtung', 'rrze-contact'),
                        'pseudo' => __('Pseudonym', 'rrze-contact'),
                    ],
                    'id' => $this->prefix . 'typ',
                    'default' => $default_rrze_contact_typ,
                ],
                [
                    'name' => __('UnivIS-Id', 'rrze-contact'),
                    'desc' => 'UnivIS-Id des Contacts (<a href="/wp-admin/edit.php?post_type=contact&page=search-univis-id">UnivIS-Id suchen</a>)',
                    'type' => 'text_small',
                    'id' => $this->prefix . 'univis_id',
                    'sanitization_cb' => 'validate_univis_id',
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ],
                [
                    'name' => __('UnivIS-Daten verwenden', 'rrze-contact'),
                    'desc' => __('Overwrite contact data with data from UnivIS.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $this->prefix . 'univis_sync',
                    'after' => $univisSyncTxt,
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ],
            ],
        ]);

        // Meta-Box um eine Contactcontact oder -Einrichtung zuzuordnen
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_connection',
            'title' => __('Ansprechpartner / verknüpfte Contacte', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Art der Verknüpfung', 'rrze-contact'),
                    'desc' => __('Der hier eingegebene Text wird vor der Ausgabe des verknüpften Contactes angezeigt (z.B. Vorzimmer, Contact über).', 'rrze-contact'),
                    'id' => $this->prefix . 'connection_text',
                    'type' => 'text',
                ],
                [
                    'name' => __('Verknüpfte Contacte auswählen', 'rrze-contact'),
                    'desc' => '',
                    'id' => $this->prefix . 'connection_id',
                    'type' => 'select',
                    'options' => $contactselect_connection,
                    'repeatable' => true,
                ],
                [
                    'name' => __('Angezeigte Daten der verknüpften Contacte', 'rrze-contact'),
                    'desc' => '',
                    'id' => $this->prefix . 'connection_options',
                    'type' => 'multicheck',
                    'options' => [
                        'contactPoint' => __('Adresse', 'rrze-contact'),
                        'telephone' => __('Telefon', 'rrze-contact'),
                        'faxNumber' => __('Telefax', 'rrze-contact'),
                        'email' => __('E-Mail', 'rrze-contact'),
                        'hoursAvailable' => __('Sprechzeiten', 'rrze-contact'),
                    ],
                ],
                [
                    'name' => __('Eigene Daten ausblenden', 'rrze-contact'),
                    'desc' => __('Ausschließlich die verknüpften Contacte werden in der Ausgabe angezeigt.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $this->prefix . 'connection_only',
                    //'before' => $standort_sync,
                ],
            ],
        ]);

    }

    public function getHonorificPrefixOptions($newVal)
    {
        $aOptions = [
            '' => __('No indication', 'rrze-contact'),
            'Dr.' => 'Dr.',
            'Prof.' => 'Prof.',
            'Prof. Dr.' => 'Prof. Dr.',
            'Prof. em.' => 'Prof. em.',
            'Prof. Dr. em.' => 'Prof. Dr. em.',
            'PD' => 'PD',
            'PD Dr.' => 'PD Dr.',
        ];
        if (!array_key_exists($newVal, $aOptions)) {
            $aOptions += [$newVal => $newVal];
        }
        ksort($aOptions);
        return $aOptions;
    }

    //Anzeigen des Feldes nur bei Personen
    function callback_cmb2_show_on_contact($field)
    {
        $default_rrze_contact_typ = Data::default_rrze_contact_typ();
        $typ = get_post_meta($field->object_id, 'rrze_contact_typ', true);
        if ($typ == 'pseudo' || $typ == 'einrichtung' || $default_rrze_contact_typ == 'einrichtung') {
            $contact = false;
        } else {
            $contact = true;
        }
        return $contact;
    }

    //Anzeigen des Feldes nur bei Einrichtungen
    function callback_cmb2_show_on_institution($field)
    {
        $default_rrze_contact_typ = Data::default_rrze_contact_typ();
        $typ = get_post_meta($field->object_id, 'rrze_contact_typ', true);
        if ($typ == 'pseudo' || $typ == 'einrichtung' || $default_rrze_contact_typ == 'einrichtung') {
            $einrichtung = true;
        } else {
            $einrichtung = false;
        }
        return $einrichtung;
    }

}
