<?php

namespace RRZE\Contact\Metaboxes;

use function RRZE\Contact\Config\getFields;
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
    public $aDisabled = [];
    public $univisData = [];
    public $descFound = '';
    public $descNotFound = '';
    public $univisID = 0;

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
        $this->descFound = __('Value displayed from UnivIS:', 'rrze-contact') . ' ';
        $this->descNotFound = __('No value is stored for this in UnivIS.', 'rrze-contact');
        add_action('save_post_contact', [$this, 'deleteTransients'], 10, 3);
        add_action('save_post', [$this, 'saveMeta'], 12, 3); // priority 10 would not work because post_meta is not stored yet. save_post_contact would not work because save_post is fired after save_post_contact
    }

    public function onLoaded()
    {
        add_action('cmb2_admin_init', [$this, 'makeMetaboxes']);

    }

    public function deleteTransients()
    {
        $aTransients = get_option('rrze-contact-shortcode-transients');

        if (!empty($aTransients)){
            foreach ($aTransients as $transient) {
                delete_transient($transient);
            }
            update_option('rrze-contact-shortcode-transients', '');
        }
    }

    public function saveMeta($postID, $post_after, $post_before)
    {

        // 2DO: 
        // on-save: store vcf + vcf.qr as post_meta
        // social media is not stored


        // $vcard = new Vcard($this->univisData);
        // echo $vcard->showCard();

        // $generator = new QRCode($data, $options); -> https://github.com/psyon/php-qrcode
        /* Output directly to standard output. */
        // $generator->output_image();
        // /* Create bitmap image. */
        // $image = $generator->render_image();
        // imagepng($image);
        // imagedestroy($image);

        if (get_post_type($postID) != 'contact') {
            return;
        }

        $postMeta = get_post_meta($postID);

        // f.e. auto-draft
        // if (empty($postMeta)){
        //     return;
        // }

        // echo '<pre>';
        // var_dump($postMeta);
        // exit;

        $aDisabled = [];
        $this->bUnivisSync = (!empty($postMeta[RRZE_CONTACT_PREFIX . 'univisSync'][0]) ? true : false);
        $this->univisID = (!empty($postMeta[RRZE_CONTACT_PREFIX . 'univisID'][0]) ? $postMeta[RRZE_CONTACT_PREFIX . 'univisID'][0] : 0);
        $aAddressFields = ['street', 'city', 'room'];

        if (!empty($this->bUnivisSync) && !empty($this->univisID)) {
            $univis = new UnivIS();
            $univisResponse = $univis->getContact('id=' . $this->univisID);

            if ($univisResponse['valid']) {
                $this->univisData = $univisResponse['content'][0];

                if (!empty($this->univisData)) {
                    $aFields = getFields('contact');
                    foreach ($aFields as $aDetails) {
                        if (!empty($this->univisData[$aDetails['name']])) {
                            $value = $this->univisData[$aDetails['name']];
                            $aDisabled[] = RRZE_CONTACT_PREFIX . $aDetails['name'];
                            update_post_meta($postID, RRZE_CONTACT_PREFIX . $aDetails['name'], $value);
                        }
                    }

                    // check if location is associated
                    if (empty($postMeta[RRZE_CONTACT_PREFIX . 'associatedLocation'][0])) {
                        $aGroups = ['locations', 'consultations'];
                    } else {
                        $aGroups = ['consultations'];

                        if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'associatedLocationID'][0])) {
                            $locationPostMeta = get_post_meta($postMeta[RRZE_CONTACT_PREFIX . 'associatedLocationID'][0]);
                            $aTmp = [];

                            foreach ($aAddressFields as $field) {
                                if (!empty($locationPostMeta[RRZE_CONTACT_PREFIX . $field][0])) {
                                    $aTmp[0][RRZE_CONTACT_PREFIX . $field] = $locationPostMeta[RRZE_CONTACT_PREFIX . $field][0];
                                    $aDisabled[] = RRZE_CONTACT_PREFIX . 'locationsGroup_0_' . RRZE_CONTACT_PREFIX . $field;
                                }
                            }

                            update_post_meta($postID, RRZE_CONTACT_PREFIX . 'locationsGroup', $aTmp);
                        }
                    }

                    foreach ($aGroups as $group) {
                        $aFields = getFields($group);
                        if (!empty($this->univisData[$group])) {
                            $aTmp = [];
                            foreach ($this->univisData[$group] as $nr => $location) {
                                $tmp = [];
                                foreach ($location as $field => $value) {
                                    $tmp[RRZE_CONTACT_PREFIX . $field] = $value;
                                    $aDisabled[] = RRZE_CONTACT_PREFIX . $group . 'Group_' . $nr . '_' . RRZE_CONTACT_PREFIX . $field;
                                }
                                $aTmp[$nr] = $tmp;
                            }
                            update_post_meta($postID, RRZE_CONTACT_PREFIX . $group . 'Group', $aTmp);
                        }
                    }

                    // echo '<pre>';
                    // var_dump($postMeta);
                    // exit;

                }
            }

        }

        // check if there are contact associations
        if (!empty($postMeta[RRZE_CONTACT_PREFIX . 'connectionID'][0])) {
            $aConnetionIDs = unserialize($postMeta[RRZE_CONTACT_PREFIX . 'connectionID'][0]);
            $bOnlyConnectionFields = (!empty($postMeta[RRZE_CONTACT_PREFIX . 'connectionFields'][0]) ? true : false);
            $aFields = unserialize($postMeta[RRZE_CONTACT_PREFIX . 'connectionFields'][0]);

            if (in_array('address', $aFields)) {
                $aFields = array_merge($aFields, $aAddressFields);
            }

            $aLocations = [];

            foreach ($aConnetionIDs as $connectionID) {
                // $locationPostMeta = get_post_meta($connectionID, RRZE_CONTACT_PREFIX . 'locationsGroup');
                $locationPostMeta = get_post_meta($connectionID);
                // $locationPostMeta = (!empty($locationPostMeta[0]) ? $locationPostMeta[0] : []);
                $aLocationsPostID = [];

                echo '<pre>';
                var_dump($locationPostMeta);
                exit;

                foreach ($locationPostMeta as $aLocation) {
                    $aLoc = [];
                    foreach ($aFields as $field) {
                        if (!empty($aLocation[RRZE_CONTACT_PREFIX . $field])) {
                            $aLoc[RRZE_CONTACT_PREFIX . $field] = $aLocation[RRZE_CONTACT_PREFIX . $field];
                            // $aDisabled[] = RRZE_CONTACT_PREFIX . 'locationsGroup_0_' . RRZE_CONTACT_PREFIX . $field;
                        }
                    }
                    if (!empty($aLoc)) {
                        $aLocationsPostID[] = $aLoc;
                    }
                }

                if (!empty($aLocationsPostID)) {
                    $aLocations[] = $aLocationsPostID;
                }
            }

            $aStoredLocations = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'locationsGroup');
            // $aLocations = (!empty($aStoredLocations[0]) ? $aStoredLocations[0] : []) + $aLocations;

            echo 'stored:<br><pre>';
            var_dump($aStoredLocations);
            echo '<br><br><br>';
            var_dump($aLocations);
            exit;

            update_post_meta($postID, RRZE_CONTACT_PREFIX . 'locationsGroup', $aLocations);

            // if ($bOnlyConnectionFields) {

            // } else {

            // }

        }

        // $aStoredLocations = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'locationsGroup');
        // // $aLocations = (!empty($aStoredLocations[0]) ? $aStoredLocations[0] : []) + $aLocations;

        // echo 'stored:<br><pre>';
        // var_dump($aStoredLocations);
        // exit;

        update_post_meta($postID, RRZE_CONTACT_PREFIX . 'disabled', $aDisabled);

        $familyName = $postMeta[RRZE_CONTACT_PREFIX . 'familyName'][0];
        $givenName = $postMeta[RRZE_CONTACT_PREFIX . 'givenName'][0];
        $name = (!empty($familyName) ? $familyName : ' ') . (!empty($givenName) ? ', ' . $givenName : ' ');

        if (empty($name)) {
            $title = strip_tags(get_the_title($postID));
            $aParts = explode(' ', $title);
            $name = (!empty($aParts[1]) ? $aParts[1] : ' ') . (!empty($aParts[0]) ? ', ' . $aParts[0] : ' ');
        }

        update_post_meta($postID, RRZE_CONTACT_PREFIX . 'name', $name);
    }

    public function makeMetaboxes()
    {
        $postID = intval(!empty($_GET['post']) ? $_GET['post'] : (!empty($_POST['post_ID']) ? $_POST['post_ID'] : 0));

        $this->bUnivisSync = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'univisSync', true);
        $this->bUnivisSync = !empty($this->bUnivisSync);
        $this->aDisabled = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'disabled', true);
        $this->aDisabled = (empty($this->aDisabled) ? [] : $this->aDisabled);
        $this->univisID = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'univisID', true);

        $univisSyncTxt = '';

        // Meta-Box Synchronize
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_sync',
            'title' => __('Metadaten zum Contact', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'side',
            'priority' => 'high',
            'fields' => [
                [
                    'name' => __('Typ des Eintrags', 'rrze-contact'),
                    'type' => 'select',
                    'options' => [
                        'unisex' => __('Person (allgemein)', 'rrze-contact'),
                        'man' => __('Person (männlich)', 'rrze-contact'),
                        'woman' => __('Person (weiblich)', 'rrze-contact'),
                        'organization' => __('Einrichtung', 'rrze-contact'),
                        'pseudo' => __('Pseudonym', 'rrze-contact'),
                    ],
                    'id' => RRZE_CONTACT_PREFIX . 'contactType',
                    'default' => 'unisex',
                ],
                [
                    'name' => __('UnivIS-Id', 'rrze-contact'),
                    'desc' => 'UnivIS-Id des Contacts (<a href="/wp-admin/edit.php?post_type=contact&page=search-univis-id">UnivIS-Id suchen</a>)',
                    'type' => 'text_small',
                    'id' => RRZE_CONTACT_PREFIX . 'univisID',
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ],
                [
                    'name' => __('UnivIS-Daten verwenden', 'rrze-contact'),
                    'desc' => __('Overwrite contact data with data from UnivIS.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => RRZE_CONTACT_PREFIX . 'univisSync',
                    'after' => $univisSyncTxt,
                    'show_on_cb' => 'callback_cmb2_show_on_contact',
                ],
            ],
        ]);

        // Metabox Contact's informations
        $aFields = $this->makeCMB2fields(getFields('contact'));

        if ($this->bUnivisSync) {
            $aFields['honorificPrefix']['type'] = 'text'; // Because W3C does not support "readonly" for select type is set to text
        } else {
            $honoricPrefix = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'honorificPrefix', true);
            $honoricPrefix = (empty($honoricPrefix) ? '' : $honoricPrefix);
            $aFields['honorificPrefix']['options'] = $this->getHonorificPrefixOptions($honoricPrefix);
        }

        $aFields['sortField'] = [
            'name' => __('Sortfield', 'rrze-contact'),
            'description' => __('Wird für eine Sortierung verwendet, die sich weder nach Name, Titel der Contactseite oder Vorname richten soll. Geben SIe hier Buchstaben oder Zahlen ein, nach denen sortiert werden sollen. Zur Sortierunge der Einträge geben Sie im Shortcode das Attribut <code>sort="sortierfeld"</code> ein.', 'rrze-contact'),
            'type' => 'text_small',
            'id' => RRZE_CONTACT_PREFIX . 'sortField',
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
            'id' => RRZE_CONTACT_PREFIX . 'link',
            'options' => $linkOptions,
            'default' => $myUrl,
        ];

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_info',
            'title' => __('Contact\'s informations', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $aFields,
        ]);

        // Meta-Box Excerpt
        $defaultExcerpt = get_post_field('post_excerpt', $postID);

        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_textinfos',
            'title' => __('Contact description in shortform', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'high',
            'fields' => [
                [
                    'name' => __('Excerpt', 'rrze-contact'),
                    'desc' => __('Kurzform und Zusammenfassung der Contactbeschreibung bei Nutzung des Attributs <code>show="description"</code>.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => RRZE_CONTACT_PREFIX . 'description',
                    'default' => $defaultExcerpt,
                ],

                [
                    'name' => __('Kurzbeschreibung (Sidebar und Kompakt)', 'rrze-contact'),
                    'desc' => __('Diese Kurzbeschreibung wird bei der Anzeige von <code>show="description"</code> in einer Sidebar (<code>format="sidebar"</code>) oder einer Liste (<code>format="kompakt"</code>) verwendet.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => RRZE_CONTACT_PREFIX . 'small_description',
                    'default' => $defaultExcerpt,
                ],
            ],
        ]);

        // Meta-Box Locations
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_locations',
            'title' => __('Locations', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Associated location', 'rrze-contact'),
                    'type' => 'select',
                    'id' => RRZE_CONTACT_PREFIX . 'associatedLocationID',
                    'options' => $this->getLocationOptions(),
                ],
                [
                    'name' => __('Standort-Daten für Adressanzeige nutzen', 'rrze-contact'),
                    'desc' => __('Die Adressdaten werden aus dem Standort bezogen; die folgenden optionalen Felder und Adressdaten aus UnivIS werden überschrieben.', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => RRZE_CONTACT_PREFIX . 'associatedLocation',
                ],
            ],
        ]);

        $groupID = $cmb->add_field([
            'id' => RRZE_CONTACT_PREFIX . 'locationsGroup',
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
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_socialmedia',
            'title' => __('Social Media', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $this->makeCMB2fields(getFields('socialmedia')),
        ]);

        // Meta-Box Consultations
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_consultations',
            'title' => __('Consultations', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Headline', 'rrze-contact'),
                    'desc' => __('Show as bold above the consultations', 'rrze-contact'),
                    'type' => 'text',
                    'id' => RRZE_CONTACT_PREFIX . 'consultation_headline',
                ],
                [
                    'name' => __('Allgemeines oder Anmerkungen', 'rrze-contact'),
                    'desc' => __('Zur Formatierung können HTML-Befehle verwendet werden (z.B. &lt;br&gt; für Zeilenumbruch). Wird vor den Sprechzeiten ausgegeben.', 'rrze-contact'),
                    'type' => 'textarea_small',
                    'id' => RRZE_CONTACT_PREFIX . 'consultation_notice',
                ],
            ],
        ]);

        $groupID = $cmb->add_field([
            'id' => RRZE_CONTACT_PREFIX . 'consultationsGroup',
            'type' => 'group',
            'repeatable' => !$this->bUnivisSync,
            'options' => [
                'group_title' => __('Consultation', 'rrze-contact') . ' {#}',
                'add_button' => __('Add consultation', 'rrze-contact'),
                'remove_button' => __('Delete consultation', 'rrze-contact'),
            ],
            'fields' => $this->makeCMB2fields(getFields('consultations')),
        ]);

        // Meta-Box to associate contact to a location
        $cmb = new_cmb2_box([
            'id' => 'rrze_contact_connection',
            'title' => __('Associated contact', 'rrze-contact'),
            'object_types' => ['contact'],
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [
                [
                    'name' => __('Type of association', 'rrze-contact'),
                    'desc' => __('The text entered here is displayed before the output of the linked contact (e.g. anteroom, contact through).', 'rrze-contact'),
                    'id' => RRZE_CONTACT_PREFIX . 'connection_text',
                    'type' => 'text',
                ],
                [
                    'name' => __('Select combined contacts', 'rrze-contact'),
                    'desc' => '',
                    'id' => RRZE_CONTACT_PREFIX . 'connectionID',
                    'type' => 'select',
                    'options' => $this->getConnectOptions(),
                    'repeatable' => true,
                    'add_row_text' => __('Add a combined contact', 'rrze-contact'),
                ],
                [
                    'name' => __('These fields will be used from the selected contacts', 'rrze-contact'),
                    'desc' => '',
                    'id' => RRZE_CONTACT_PREFIX . 'connectionFields',
                    'type' => 'multicheck',
                    'options' => [
                        'address' => __('Address', 'rrze-contact'),
                        'phone' => __('Phone', 'rrze-contact'),
                        'fax' => __('Fax', 'rrze-contact'),
                        'email' => __('E-Mail', 'rrze-contact'),
                        'consultations' => __('Consultations', 'rrze-contact'),
                    ],
                ],
                [
                    'name' => __('Use associated contacts\' fields', 'rrze-contact'),
                    'desc' => __('Show selected fields from associated contacts only', 'rrze-contact'),
                    'type' => 'checkbox',
                    'id' => RRZE_CONTACT_PREFIX . 'onlyConnectionFields',
                ],
            ],
        ]);
    }

    private function getConnectOptions()
    {
        $aRet = [];
        $aPosts = get_posts([
            'post_type' => 'contact',
            'post_status' => 'publish',
            'fields' => 'ids',
            'suppress_filters' => false,
            'nopaging' => true,
        ]);

        if (!empty($aPosts)) {
            $firstItem = [0 => '-- ' . __('No connection selected', 'rrze-contact') . ' --'];

            foreach ($aPosts as $postID) {
                $aRet[$postID] = get_post_meta($postID, RRZE_CONTACT_PREFIX . 'name', true);
            }

            natcasesort($aRet);
        } else {
            $firstItem = [0 => '-- ' . __('No connection entered', 'rrze-contact') . ' --'];
        }

        return $firstItem + $aRet;
    }

    private function getLocationOptions()
    {
        $aRet = [];
        $firstItem = [0 => '-- ' . __('No location selected', 'rrze-contact') . ' --'];

        $aPosts = get_posts([
            'post_type' => 'location',
            'post_status' => 'publish',
            'suppress_filters' => false,
            'nopaging' => true,
            'orderby' => 'title',
        ]);

        if (!empty($aPosts)) {
            foreach ($aPosts as $post) {
                $aRet[$post->ID] = $post->post_title;
            }

            natcasesort($aRet);
        } else {
            $firstItem = [0 => '-- ' . __('No location entered', 'rrze-contact') . ' --'];
        }

        return $firstItem + $aRet;
    }

    private function getHonorificPrefixOptions($newVal)
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
