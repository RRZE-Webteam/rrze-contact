<?php

namespace RRZE\Contact\Metaboxes;

use function RRZE\Contact\Config\getFields;
use function RRZE\Contact\Config\getSocialMediaList;
use RRZE\Contact\API\UnivIS;
use RRZE\Contact\Data;

defined('ABSPATH') || exit;

/**
 * Define Metaboxes for Contact-Edit
 */
class Contact extends Metaboxes
{

    protected $pluginFile;
    private $settings = '';
    public $bUnivisSync = false;
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
    }

    public function onLoaded()
    {
        // add_filter('cmb2_meta_boxes', [$this, 'cmb2_contact_metaboxes']);
        add_action('cmb2_admin_init', [$this, 'makeContactMetaboxes']);
        add_filter('cmb2_render_address', [$this, 'cmb2_render_address_field_callback'], 10, 5 );

    }

    public function makeContactMetaboxes()
    {

        // Meta-Box Contact
        $contactselect_connection = Data::get_contactdata(1);
        $default_rrze_contact_typ = Data::get_default_rrze_contact_typ();

        $contactID = intval(!empty($_GET['post']) ? $_GET['post'] : (!empty($_POST['post_ID']) ? $_POST['post_ID'] : 0));

        $this->postMeta = get_post_meta($contactID);

        $this->bUnivisSync = (!empty($this->postMeta[$this->prefix . 'univis_sync'][0]) ? $this->postMeta[$this->prefix . 'univis_sync'][0] : false); // get_post_meta() can return '' for this field but we need a real false to set 'disabled'

        $univisSyncTxt = '';
        $this->univisID = (!empty($this->postMeta[$this->prefix . 'univis_id'][0]) ? $this->postMeta[$this->prefix . 'univis_id'][0] : 0);

        if ($this->univisID) {
            $univis = new UnivIS();

            $this->univisID = 40014582; // TEST

            $univisResponse = $univis->getPerson('id=' . $this->univisID);

            // echo '<pre>';
            // var_dump($univisResponse);
            // exit;

            if ($univisResponse['valid']) {
                $this->univisData = $univisResponse['content'][0];
            } else {
                $univisSyncTxt = '<p class="cmb2-metabox-description">' . __('Derzeit sind keine Daten aus UnivIS syncronisiert.', 'rrze-contact') . '</p>';
            }
        }

        $aFields = $this->makeCMB2fields(getFields('contact'));

        $aFields['honorificPrefix']['type'] = 'select';
        $aFields['honorificPrefix']['options'] = [
            '' => __('No indication', 'rrze-contact'),
            'Dr.' => __('Doktor', 'rrze-contact'),
            'Prof.' => __('Professor', 'rrze-contact'),
            'Prof. Dr.' => __('Professor Doktor', 'rrze-contact'),
            'Prof. em.' => __('Professor (Emeritus)', 'rrze-contact'),
            'Prof. Dr. em.' => __('Professor Doktor (Emeritus)', 'rrze-contact'),
            'PD' => __('Privatdozent', 'rrze-contact'),
            'PD Dr.' => __('Privatdozent Doktor', 'rrze-contact'),
        ];

        $aFields['sortField'] = [
            'name' => __('Sortierfeld', 'rrze-contact'),
            'description' => __('Wird für eine Sortierung verwendet, die sich weder nach Name, Titel der Contactseite oder Vorname richten soll. Geben SIe hier Buchstaben oder Zahlen ein, nach denen sortiert werden sollen. Zur Sortierunge der Einträge geben Sie im Shortcode das Attribut <code>sort="sortierfeld"</code> ein.', 'rrze-contact'),
            'type' => 'text_small',
            'id' => $this->prefix . 'sortField',
            'attributes' => array(
                'value' => $this->getVal('sortField'),
            ),
            'show_on_cb' => 'callback_cmb2_show_on_institution',
        ];

        $myUrl = get_permalink($contactID);
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

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_info',
            'title' => __('Contact\'s informations', 'rrze-contact'),
            'object_types' => ['contact'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $aFields,
        ]);

        // Meta-Box Excerpt
        $defaultExcerpt = get_post_field('post_excerpt', $contactID);

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
        $locationDefault = Data::get_standort_defaults($contactID);
        $locationSelect = Data::get_standortdata();

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_adressdaten',
            'title' => __('Locations', 'rrze-contact'),
            'object_types' => array('contact'), // post type
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


        $iCnt = (!empty($this->univisData['locationsCount']) ? $this->univisData['locationsCount'] : 0);


        // echo '<pre>';
        // var_dump($this->makeCMB2fields(getFields('location'), 'locations', 0));
        // exit;
        // $aFields = [];
        $groupID = $cmb->add_field(
            [
            'id' => $this->prefix . 'locationsGroup',
            'type' => 'group',
            'repeatable'  => true,
            'options' => [
                'group_title' => __('Location', 'rrze-contact') . ' {#}',
                'add_button' => __('Add location', 'rrze-contact'),
                'remove_button' => __('Delete location', 'rrze-contact'),
            ],
        ]);


        for ($i=0; $i < $iCnt; $i++){
            $groupID = $cmb->add_field(
                [
                'id' => $this->prefix . 'locationsGroup' . $i,
                'type' => 'group',
                'repeatable'  => false,
                'options' => [
                    'group_title' => __('Location', 'rrze-contact') . ' {#}',
                    'add_button' => __('Add location', 'rrze-contact'),
                    'remove_button' => __('Delete location', 'rrze-contact'),
                ],
            ]);

            $locationsID = $cmb->add_group_field($groupID, 
                [
                'id' => $this->prefix . 'location' . $i,
                'type' => 'address',
                'repeatable'  => false,
                'options' => [
                    // 'group_  title' => __('TEST Location', 'rrze-contact') . ' {#}',
                ],
            ]);

            // echo $locationsID . ' == $locationsID <br>';


            // custom field? 
            // https://github.com/CMB2/CMB2-Snippet-Library/blob/master/custom-field-types/address-field-type/class-cmb2-render-address-field.php

            // oder doch Carbon Fields zusätzlich? https://github.com/CMB2/CMB2/issues/565

            // wahrscheinlich über einen Filter mit dem das Field-Set eingebunden wird: 
            // https://github.com/CMB2/CMB2/wiki/Adding-your-own-field-types#example-4-multiple-inputs-one-field-lets-create-an-address-field

            // $aFields = $this->makeCMB2fields(getFields('location'), 'locations', $i);
            // foreach($aFields as $aVal){
            //     $cmb->add_group_field($this->prefix . 'location', $aVal);
            // }
        }

        return;


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
            $thissome['id'] = $this->prefix . $key . '_url';
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
                    'id' => $this->prefix . 'hoursAvailable_text',
                ),
                array(
                    'name' => __('Sprechzeiten: Allgemeines oder Anmerkungen', 'rrze-contact'),
                    'desc' => __('Zur Formatierung können HTML-Befehle verwendet werden (z.B. &lt;br&gt; für Zeilenumbruch). Wird vor den Sprechzeiten ausgegeben.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => $this->prefix . 'hoursAvailable',
                ),
                array(
                    'id' => $this->prefix . 'hoursAvailable_group',
                    'type' => 'group',
                    // 'desc' => $univis_default['hoursAvailable_group'],
                    //'desc' => __('Bitte geben Sie die Sprechzeiten an.', 'rrze-contact'),
                    'options' => array(
                        'group_title' => __('Sprechzeit {#}', 'rrze-contact'),
                        'add_button' => __('Weitere Sprechzeit einfügen', 'rrze-contact'),
                        'remove_button' => __('Sprechzeit löschen', 'rrze-contact'),
                        //'sortable' => true,
                    ),
                    'fields' => array(
                        array(
                            'name' => __('Wiederholung', 'rrze-contact'),
                            'id' => 'repeat',
                            'type' => 'radio_inline',
                            'options' => array(
                                '-' => __('Keine', 'rrze-contact'),
                                'd1' => __('täglich', 'rrze-contact'),
                                'w1' => __('wöchentlich', 'rrze-contact'),
                                'w2' => __('alle 2 Wochen', 'rrze-contact'),
                            ),
                        ),
                        array(
                            'name' => __('am', 'rrze-contact'),
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
                            ),
                        ),
                        array(
                            'name' => __('von', 'rrze-contact'),
                            'id' => 'starttime',
                            'type' => 'text_time',
                            'time_format' => 'H:i',
                        ),
                        array(
                            'name' => __('bis', 'rrze-contact'),
                            'id' => 'endtime',
                            'type' => 'text_time',
                            'time_format' => 'H:i',
                        ),
                        array(
                            'name' => __('Raum', 'rrze-contact'),
                            'id' => 'office',
                            'type' => 'text_small',
                        ),
                        array(
                            'name' => __('Bemerkung', 'rrze-contact'),
                            'id' => 'comment',
                            'type' => 'text',
                        ),
                    ),

                ),

            ),
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
                    'id' => $this->prefix . 'typ',
                    'default' => $default_rrze_contact_typ,
                ),
                array(
                    'name' => __('UnivIS-Id', 'rrze-contact'),
                    'desc' => 'UnivIS-Id des Contacts (<a href="/wp-admin/edit.php?post_type=contact&page=search-univis-id">UnivIS-Id suchen</a>)',
                    'type' => 'text_small',
                    'id' => $this->prefix . 'univis_id',
                    'sanitization_cb' => 'validate_univis_id',
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ),
                array(
                    'name' => __('UnivIS-Daten verwenden', 'rrze-contact'),
                    'desc' => __('Overwrite contact data with data from UnivIS.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $this->prefix . 'univis_sync',
                    'after' => $univisSyncTxt,
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ),

            ),
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
                    'id' => $this->prefix . 'connection_text',
                    'type' => 'text',
                ),
                array(
                    'name' => __('Verknüpfte Contacte auswählen', 'rrze-contact'),
                    'desc' => '',
                    'id' => $this->prefix . 'connection_id',
                    'type' => 'select',
                    'options' => $contactselect_connection,
                    'repeatable' => true,
                ),
                array(
                    'name' => __('Angezeigte Daten der verknüpften Contacte', 'rrze-contact'),
                    'desc' => '',
                    'id' => $this->prefix . 'connection_options',
                    'type' => 'multicheck',
                    'options' => array(
                        'contactPoint' => __('Adresse', 'rrze-contact'),
                        'telephone' => __('Telefon', 'rrze-contact'),
                        'faxNumber' => __('Telefax', 'rrze-contact'),
                        'email' => __('E-Mail', 'rrze-contact'),
                        'hoursAvailable' => __('Sprechzeiten', 'rrze-contact'),
                    ),
                ),
                array(
                    'name' => __('Eigene Daten ausblenden', 'rrze-contact'),
                    'desc' => __('Ausschließlich die verknüpften Contacte werden in der Ausgabe angezeigt.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => $this->prefix . 'connection_only',
                    //'before' => $standort_sync,
                ),
            ),
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

    function cmb2_render_address_field_callback( $field, $value, $object_id, $object_type, $field_type ) {

        // make sure we specify each part of the value we need.
        $value = wp_parse_args( $value, array(
            'address-1' => '',
            'address-2' => '',
            'city'      => '',
            'state'     => '',
            'zip'       => '',
        ) );
    
        ?>
        <div><p><label for="<?php echo $field_type->_id( '_address_1' ); ?>">Address 1</label></p>
            <?php echo $field_type->input( array(
                'name'  => $field_type->_name( '[address-1]' ),
                'id'    => $field_type->_id( '_address_1' ),
                'value' => $value['address-1'],
                'desc'  => '',
            ) ); ?>
        </div>
        <div><p><label for="<?php echo $field_type->_id( '_address_2' ); ?>'">Address 2</label></p>
            <?php echo $field_type->input( array(
                'name'  => $field_type->_name( '[address-2]' ),
                'id'    => $field_type->_id( '_address_2' ),
                'value' => $value['address-2'],
                'desc'  => '',
            ) ); ?>
        </div>
        <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_city' ); ?>'">City</label></p>
            <?php echo $field_type->input( array(
                'class' => 'cmb_text_small',
                'name'  => $field_type->_name( '[city]' ),
                'id'    => $field_type->_id( '_city' ),
                'value' => $value['city'],
                'desc'  => '',
            ) ); ?>
        </div>
        <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_state' ); ?>'">State</label></p>
            <?php echo $field_type->select( array(
                'name'    => $field_type->_name( '[state]' ),
                'id'      => $field_type->_id( '_state' ),
                // 'options' => cmb2_get_state_options( $value['state'] ),
                'desc'    => '',
            ) ); ?>
        </div>
        <div class="alignleft"><p><label for="<?php echo $field_type->_id( '_zip' ); ?>'">Zip</label></p>
            <?php echo $field_type->input( array(
                'class' => 'cmb_text_small',
                'name'  => $field_type->_name( '[zip]' ),
                'id'    => $field_type->_id( '_zip' ),
                'value' => $value['zip'],
                'type'  => 'number',
                'desc'  => '',
            ) ); ?>
        </div>
        <br class="clear">
        <?php
        echo $field_type->_desc( true );
    
    }
}
