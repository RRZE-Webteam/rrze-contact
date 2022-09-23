<?php

namespace RRZE\Contact\Metaboxes;

use RRZE\Contact\Data;
// use RRZE\Lib\UnivIS\Data as UnivIS_Data;
use function RRZE\Contact\Config\getSocialMediaList;

defined('ABSPATH') || exit;

/**
 * Define Metaboxes for Contact-Edit
 */
class Contact extends Metaboxes
{

    protected $pluginFile;
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }


    public function onLoaded()
    {
        require_once(plugin_dir_path($this->pluginFile) . 'vendor/UnivIS/UnivIS.php');
        require_once(plugin_dir_path($this->pluginFile) . 'vendor/DIP/DIP.php');
        add_filter('cmb2_meta_boxes', array($this, 'cmb2_contact_metaboxes'));
    }


    public function cmb2_contact_metaboxes($meta_boxes)
    {
        $prefix = $this->prefix;

        $contactselect_connection = Data::get_contactdata(1);
        $standortselect =  Data::get_standortdata();
        $default_rrze_contact_typ = Data::get_default_rrze_contact_typ();

        $contact_id = 0;

        if (isset($_GET['post'])) {
            $contact_id = intval($_GET['post']);
        } elseif (isset($_POST['post_ID'])) {
            $contact_id = intval($_POST['post_ID']);
        }

        $univis_id = get_post_meta($contact_id, 'rrze_contact_univis_id', true);
        $univisdata = Data::get_fields($contact_id, $univis_id, 0, false, true);

        if ($univisdata) {
            $univis_sync = '';
        } else {
            $univis_sync = '<p class="cmb2-metabox-description">' . __('Derzeit sind keine Daten aus UnivIS syncronisiert.', 'rrze-contact') . '</p>';
        }
        $standort_default = Data::get_standort_defaults($contact_id);
        $univis_default = Data::univis_defaults($contact_id);

        $defaultkurzauszug = '';
        if (get_post_field('post_excerpt', $contact_id)) {
            $defaultkurzauszug  = get_post_field('post_excerpt', $contact_id);
        }
        // Meta-Box Weitere Informationen - rrze_contact_adds
        $meta_boxes['rrze_contact_textinfos'] = array(
            'id' => 'rrze_contact_textinfos',
            'title' => __('Contact Beschreibung in Kurzform', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'normal',
            'priority' => 'high',
            'fields' => array(
                array(
                    'name' => __('Kurzbeschreibung', 'rrze-contact'),
                    'desc' => __('Kurzform und Zusammenfassung der Contactbeschreibung bei Nutzung des Attributs <code>show="description"</code>.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $prefix . 'description',
                    'default'    => $defaultkurzauszug
                ),

                array(
                    'name' => __('Kurzbeschreibung (Sidebar und Kompakt)', 'rrze-contact'),
                    'desc' => __('Diese Kurzbeschreibung wird bei der Anzeige von <code>show="description"</code> in einer Sidebar (<code>format="sidebar"</code>) oder einer Liste (<code>format="kompakt"</code>) verwendet.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $prefix . 'small_description',
                    'default'    => $defaultkurzauszug
                ),

            )
        );



        // Meta-Box Contactinformation - rrze_contact_info
        $meta_boxes['rrze_contact_info'] = array(
            'id' => 'rrze_contact_info',
            'title' => __('Contactinformationen', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            //'show_on_cb' => array( 'key' => 'submenu-slug', 'value' => 'contact' ),        
            'context' => 'normal',
            'priority' => 'default',
            'fields' => array(
                array(
                    'name' => __('Titel (Präfix)', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'select',
                    'options' => array(
                        '' => __('Keine Angabe', 'rrze-contact'),
                        'Dr.' => __('Doktor', 'rrze-contact'),
                        'Prof.' => __('Professor', 'rrze-contact'),
                        'Prof. Dr.' => __('Professor Doktor', 'rrze-contact'),
                        'Prof. em.' => __('Professor (Emeritus)', 'rrze-contact'),
                        'Prof. Dr. em.' => __('Professor Doktor (Emeritus)', 'rrze-contact'),
                        'PD' => __('Privatdozent', 'rrze-contact'),
                        'PD Dr.' => __('Privatdozent Doktor', 'rrze-contact')
                    ),
                    'id' => $prefix . 'honorificPrefix',
                    'after' => $univis_default['honorificPrefix'],
                    'show_on_cb' => 'callback_cmb2_show_on_contact'
                ),
                array(
                    'name' => __('Vorname', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text',
                    'id' => $prefix . 'givenName',
                    'after' => $univis_default['givenName'],
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                    'attributes'  => array(
                        'placeholder' => $univisdata['givenName'],
                    ),
                ),
                array(
                    'name' => __('Nachname', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text',
                    'id' => $prefix . 'familyName',
                    'after' => $univis_default['familyName'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['familyName'],
                    ),
                    'show_on_cb' => 'callback_cmb2_show_on_contact'
                ),

                array(
                    'name' => __('Abschluss (Suffix)', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text',
                    'id' => $prefix . 'honorificSuffix',
                    'after' => $univis_default['honorificSuffix'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['honorificSuffix'],
                    ),
                    'show_on_cb' => 'callback_cmb2_show_on_contact'
                ),
                array(
                    'name' => __('Position/Funktion', 'rrze-contact'),
                    'desc' => '',
                    'id' => $prefix . 'jobTitle',
                    'type' => 'text',
                    'after' => $univis_default['jobTitle'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['jobTitle'],
                    ),
                ),
                array(
                    'name' => __('Organisation', 'rrze-contact'),
                    'desc' => __('Geben Sie hier die Organisation (Lehrstuhl oder Einrichtung) ein.', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'worksFor',
                    'after' => $univis_default['worksFor'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['worksFor'],
                    ),
                ),
                array(
                    'name' => __('Abteilung', 'rrze-contact'),
                    'desc' => __('Geben Sie hier die Abteilung oder Arbeitsgruppe ein.', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'department',
                    'after' => $univis_default['department'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['department'],
                    ),

                ),


                array(
                    'name' => __('Raum', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text',
                    'id' => $prefix . 'workLocation',
                    'after' => $univis_default['workLocation'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['workLocation'],
                    ),
                ),
                array(
                    'name' => __('Standort Telefon- und Faxanschluss', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'radio',
                    'id' => $prefix . 'telephone_select',
                    'options' => array(
                        'erl' => __('Uni-intern, Standort Erlangen', 'rrze-contact'),
                        'nbg' => __('Uni-intern, Standort Nürnberg', 'rrze-contact'),
                        'standard' => __('Allgemeine Rufnummer', 'rrze-contact')
                    ),
                    'default' => 'standard'
                ),
                array(
                    'name' => __('Telefon', 'rrze-contact'),
                    'desc' => __('Bitte geben Sie uni-interne Nummern für Erlangen in der internationalen Form +49 9131 85-22222 und für Nürnberg in der internationalen Form +49 911 5302-555 an.', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'telephone',
                    'sanitization_cb' => 'validate_number',
                    'after' => $univis_default['telephone'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['telephone'],
                    ),
                ),
                array(
                    'name' => __('Telefax', 'rrze-contact'),
                    'desc' => __('Bitte geben Sie uni-interne Nummern für Erlangen in der internationalen Form +49 9131 85-22222 und für Nürnberg in der internationalen Form +49 911 5302-555 an, uni-externe Nummern in der internationalen Form +49 9131 1111111.', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'faxNumber',
                    'sanitization_cb' => 'validate_number',
                    'after' => $univis_default['faxNumber'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['faxNumber'],
                    ),
                ),
                array(
                    'name' => __('Mobiltelefon', 'rrze-contact'),
                    'desc' => __('Bitte geben Sie die Nummer in der internationalen Form +49 176 1111111 an.', 'rrze-contact'),
                    'type' => 'text',
                    'sanitization_cb' => 'validate_number',
                    'id' => $prefix . 'mobilePhone',
                    'after' => $univis_default['mobilePhone'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['mobilePhone'],
                    ),
                ),
                array(
                    'name' => __('E-Mail', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text_email',
                    'id' => $prefix . 'email',
                    'after' => $univis_default['email'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['email'],
                    ),
                ),
                array(
                    'name' => __('Webseite', 'rrze-contact'),
                    'desc' => '',
                    'type' => 'text_url',
                    'id' => $prefix . 'url',
                    'after' => $univis_default['url'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['url'],
                    ),
                ),
                array(
                    'name' => __('Sortierfeld', 'rrze-contact'),
                    'desc' => __('Wird für eine Sortierung verwendet, die sich weder nach Name, Titel der Contactseite oder Vorname richten soll. Geben SIe hier Buchstaben oder Zahlen ein, nach denen sortiert werden sollen. Zur Sortierunge der Einträge geben Sie im Shortcode das Attribut <code>sort="sortierfeld"</code> ein.', 'rrze-contact'),
                    'type' => 'text_small',
                    'id' => $prefix . 'alternateName',
                    'attributes'  => array(
                        'placeholder' => $univisdata['alternateName'],
                    ),
                    'show_on_cb' => 'callback_cmb2_show_on_einrichtung'
                ),
                array(
                    'name' => __('Name und "Mehr"-Link verlinken auf Seite ...', 'rrze-contact'),
                    'desc' => __('Optionale URL-Angabe zu einer selbst gepflegten Seite für Details zum Contact. Wenn diese Angabe leer gelassen wird, wird zu der automatisch erstellten Contactseite verlinkt.', 'rrze-contact'),
                    'type' => 'text_url',
                    'id' => $prefix . 'link',
                    'attributes'  => array(
                        'placeholder' => get_permalink($contact_id),
                    ),
                    //'after' => '<hr>' . __('Zum Anzeigen der Person verwenden Sie bitte die ID', 'rrze-contact') . ' ' . $helpuse,                
                ),
            )
        );

        $meta_boxes['rrze_contact_adressdaten'] = array(
            'id' => 'rrze_contact_adressdaten',
            'title' => __('Postalische Adressdaten', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => array(
                array(
                    'name' => __('Zugeordneter Standort', 'rrze-contact'),
                    //'desc' => 'Der Standort, von dem die Daten angezeigt werden sollen.',
                    'type' => 'select',
                    'id' => $prefix . 'standort_id',
                    'options' => $standortselect,
                ),
                array(
                    'name' => __('Standort-Daten für Adressanzeige nutzen', 'rrze-contact'),
                    'desc' => __('Die Adressdaten werden aus dem Standort bezogen; die folgenden optionalen Felder und Adressdaten aus UnivIS werden überschrieben.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $prefix . 'standort_sync',
                ),
                array(
                    'name' => __('Straße und Hausnummer', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'streetAddress',
                    'after' =>  $standort_default['streetAddress'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['streetAddress'],
                    ),

                ),
                array(
                    'name' => __('Postleitzahl', 'rrze-contact'),
                    //'desc' => 'Wenn der Ort aus UnivIS übernommen werden soll bitte leer lassen!',
                    'type' => 'text_small',
                    'id' => $prefix . 'postalCode',
                    'sanitization_cb' => 'validate_plz',
                    'after' => $standort_default['postalCode'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['postalCode'],
                    ),
                ),
                array(
                    'name' => __('Ort', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'addressLocality',
                    'after' => $standort_default['addressLocality'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['addressLocality'],
                    ),
                ),
                array(
                    'name' => __('Land', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'addressCountry',
                    'after' => $standort_default['addressCountry'],
                    'attributes'  => array(
                        'placeholder' => $univisdata['addressCountry'],
                    ),
                ),

            )
        );


        /*  "instagram"=> [
		'title'  => 'Instagram',
		'class' => 'instagram'
	    ],
	*/


    // echo 'hier';
    // var_dump($meta_boxes);
    // exit;

        $somes = getSocialMediaList();
        $somefields = array();



        foreach ($somes as $key => $value) {
            $name = $somes[$key]['title'];
            $desc = '';
            if (isset($somes[$key]['desc'])) {
                $desc = $somes[$key]['desc'];
            }
            $thissome['name'] = $name . ' URL';
            $thissome['desc'] = $desc;
            $thissome['type'] = 'text_url';
            $thissome['id'] =  $prefix . $key . '_url';
            $thissome['protocols'] = array('https');

            array_push($somefields, $thissome);
        }



        // Meta-Box Social Media - rrze_contact_social_media
        $meta_boxes['rrze_contact_social_media'] = array(
            'id' => 'rrze_contact_social_media',
            'title' => __('Social Media', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $somefields,

        );

        // Meta-Box Weitere Informationen - rrze_contact_adds
        $meta_boxes['rrze_contact_adds'] = array(
            'id' => 'rrze_contact_adds',
            'title' => __('Sprechzeiten', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => array(

                array(
                    'name' => __('Sprechzeiten: Überschrift', 'rrze-contact'),
                    'desc' => __('Wird in Fettdruck über den Sprechzeiten ausgegeben.', 'rrze-contact'),
                    'type' => 'text',
                    'id' => $prefix . 'hoursAvailable_text'
                ),
                array(
                    'name' => __('Sprechzeiten: Allgemeines oder Anmerkungen', 'rrze-contact'),
                    'desc' => __('Zur Formatierung können HTML-Befehle verwendet werden (z.B. &lt;br&gt; für Zeilenumbruch). Wird vor den Sprechzeiten ausgegeben.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $prefix . 'hoursAvailable'
                ),
                array(
                    'id' => $prefix . 'hoursAvailable_group',
                    'type' => 'group',
                    'desc' => $univis_default['hoursAvailable_group'],
                    //'desc' => __('Bitte geben Sie die Sprechzeiten an.', 'rrze-contact'),
                    'options' => array(
                        'group_title' => __('Sprechzeit {#}', 'rrze-contact'),
                        'add_button' => __('Weitere Sprechzeit einfügen', 'rrze-contact'),
                        'remove_button' => __('Sprechzeit löschen', 'rrze-contact'),
                        //'sortable' => true,
                    ),
                    'fields' => array(
                        array(
                            'name' =>  __('Wiederholung', 'rrze-contact'),
                            'id' => 'repeat',
                            'type' => 'radio_inline',
                            'options' => array(
                                '-' => __('Keine', 'rrze-contact'),
                                'd1' => __('täglich', 'rrze-contact'),
                                'w1' => __('wöchentlich', 'rrze-contact'),
                                'w2' => __('alle 2 Wochen', 'rrze-contact'),
                            )
                        ),
                        array(
                            'name' =>  __('am', 'rrze-contact'),
                            'id' => 'repeat_submode',
                            'type' => 'multicheck',
                            'options' => array(
                                '1' => __('Montag', 'rrze-contact'),
                                '2' => __('Dienstag', 'rrze-contact'),
                                '3' => __('Mittwoch', 'rrze-contact'),
                                '4' => __('Donnerstag', 'rrze-contact'),
                                '5' => __('Freitag', 'rrze-contact'),
                                '6' => __('Samstag', 'rrze-contact'),
                                '7' => __('Sonntag', 'rrze-contact'),
                            )
                        ),
                        array(
                            'name' =>  __('von', 'rrze-contact'),
                            'id' => 'starttime',
                            'type' => 'text_time',
                            'time_format' => 'H:i',
                        ),
                        array(
                            'name' =>  __('bis', 'rrze-contact'),
                            'id' => 'endtime',
                            'type' => 'text_time',
                            'time_format' => 'H:i',
                        ),
                        array(
                            'name' =>  __('Raum', 'rrze-contact'),
                            'id' => 'office',
                            'type' => 'text_small',
                        ),
                        array(
                            'name' =>  __('Bemerkung', 'rrze-contact'),
                            'id' => 'comment',
                            'type' => 'text',
                        ),
                    ),

                ),

            )
        );


        // Meta-Box Synchronisierung mit externen Daten - rrze_contact_sync ab hier
        $meta_boxes['rrze_contact_sync'] = array(
            'id' => 'rrze_contact_sync',
            'title' => __('Metadaten zum Contact', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'side',
            'priority' => 'high',
            'fields' => array(
                array(
                    'name' => __('Typ des Eintrags', 'rrze-contact'),
                    'type' => 'select',
                    'options' => array(
                        'realcontact' => __('Person (allgemein)', 'rrze-contact'),
                        'realmale' => __('Person (männlich)', 'rrze-contact'),
                        'realfemale' => __('Person (weiblich)', 'rrze-contact'),
                        'einrichtung' => __('Einrichtung', 'rrze-contact'),
                        'pseudo' => __('Pseudonym', 'rrze-contact'),
                    ),
                    'id' => $prefix . 'typ',
                    'default' => $default_rrze_contact_typ
                ),
                array(
                    'name' => __('UnivIS-Id', 'rrze-contact'),
                    'desc' => 'UnivIS-Id des Contacts (<a href="/wp-admin/edit.php?post_type=contact&page=search-univis-id">UnivIS-Id suchen</a>)',
                    'type' => 'text_small',
                    'id' => $prefix . 'univis_id',
                    'sanitization_cb' => 'validate_univis_id',
                    'show_on_cb' => 'callback_cmb2_show_on_contact'
                ),
                array(
                    'name' => __('UnivIS-Daten verwenden', 'rrze-contact'),
                    'desc' => __('Daten aus UnivIS überschreiben die Contactdaten.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $prefix . 'univis_sync',
                    'after' => $univis_sync,
                    'show_on_cb' => 'callback_cmb2_show_on_contact'
                ),

            )
        );



        // Meta-Box um eine Contactcontact oder -Einrichtung zuzuordnen
        $meta_boxes['rrze_contact_connection'] = array(
            'id' => 'rrze_contact_connection',
            'title' => __('Ansprechpartner / verknüpfte Contacte', 'rrze-contact'),
            'object_types' => array('contact'), // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => array(
                array(
                    'name' => __('Art der Verknüpfung', 'rrze-contact'),
                    'desc' => __('Der hier eingegebene Text wird vor der Ausgabe des verknüpften Contactes angezeigt (z.B. Vorzimmer, Contact über).', 'rrze-contact'),
                    'id' => $prefix . 'connection_text',
                    'type' => 'text',
                ),
                array(
                    'name' => __('Verknüpfte Contacte auswählen', 'rrze-contact'),
                    'desc' => '',
                    'id' => $prefix . 'connection_id',
                    'type' => 'select',
                    'options' => $contactselect_connection,
                    'repeatable' => true,
                ),
                array(
                    'name' => __('Angezeigte Daten der verknüpften Contacte', 'rrze-contact'),
                    'desc' => '',
                    'id' => $prefix . 'connection_options',
                    'type' => 'multicheck',
                    'options' => array(
                        'contactPoint' => __('Adresse', 'rrze-contact'),
                        'telephone' => __('Telefon', 'rrze-contact'),
                        'faxNumber' => __('Telefax', 'rrze-contact'),
                        'email' => __('E-Mail', 'rrze-contact'),
                        'hoursAvailable' => __('Sprechzeiten', 'rrze-contact'),
                    )
                ),
                array(
                    'name' => __('Eigene Daten ausblenden', 'rrze-contact'),
                    'desc' => __('Ausschließlich die verknüpften Contacte werden in der Ausgabe angezeigt.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $prefix . 'connection_only',
                    //'before' => $standort_sync,
                ),
            )
        );





        return $meta_boxes;
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
    function callback_cmb2_show_on_einrichtung($field)
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
