<?php

namespace RRZE_Contact\Taxonomy;

use RRZE_Contact\Data;
use RRZE_Contact\Schema;
use function RRZE_Contact\Config\get_fau_person_capabilities;
use RRZE\Lib\UnivIS\Config;

defined('ABSPATH') || exit;

/**
 * Posttype Person
 */
class Kontakt extends Taxonomy
{

    protected $postType = 'person';
    protected $taxonomy = 'persons_category';

    protected $pluginFile;
    private $settings = '';

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }
    public function onLoaded()
    {
        add_action('init', [$this, 'set']);
        add_action('admin_init', [$this, 'register']);

    }

    public function set()
    {
        $labels = [
            'name' => _x('Kontakte', 'Post Type General Name', 'rrze-contact'),
            'singular_name' => _x('Kontakt', 'Post Type Singular Name', 'rrze-contact'),
            'menu_name' => __('Kontakte', 'rrze-contact'),
            'parent_item_colon' => __('Übergeordneter Kontakt', 'rrze-contact'),
            'all_items' => __('Alle Kontakte', 'rrze-contact'),
            'view_item' => __('Kontakt ansehen', 'rrze-contact'),
            'add_new_item' => __('Kontakt hinzufügen', 'rrze-contact'),
            'add_new' => __('Neuer Kontakt', 'rrze-contact'),
            'edit_item' => __('Kontakt bearbeiten', 'rrze-contact'),
            'update_item' => __('Kontakt aktualisieren', 'rrze-contact'),
            'search_items' => __('Kontakte suchen', 'rrze-contact'),
            'not_found' => __('Keine Kontakte gefunden', 'rrze-contact'),
            'not_found_in_trash' => __('Keine Kontakte in Papierkorb gefunden', 'rrze-contact'),
        ];
        $has_archive_page = true;
        if (isset($this->settings->options) && isset($this->settings->options['constants_has_archive_page'])) {
            $has_archive_page = $this->settings->options['constants_has_archive_page'];
        }
        $caps = get_fau_person_capabilities();
        $person_args = array(
            'label' => __('Kontakt', 'rrze-contact'),
            'description' => __('Kontaktinformationen', 'rrze-contact'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'revisions'),
            'taxonomies' => array('persons_category'),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_position' => 20,
            'menu_icon' => 'dashicons-id-alt',
            'can_export' => true,
            'has_archive' => $has_archive_page,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => 'person',
            'rewrite' => [
                'slug' => $this->postType,
                'with_front' => true,
                'pages' => true,
                'feeds' => true,
            ],
            'capability_type' => $this->postType,
            'capabilities' => $caps,
            'map_meta_cap' => true,
        );

        register_post_type($this->postType, $person_args);

        register_taxonomy(
            $this->taxonomy,
            $this->postType,
            [
                'hierarchical' => true,
                //    'labels'        => $labels,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => $this->taxonomy,
                    'with_front' => false,
                ],
            ]
        );
    }

    public function register()
    {
        register_taxonomy_for_object_type($this->taxonomy, $this->postType);
        add_action('restrict_manage_posts', [$this, 'person_restrict_manage_posts']);
        add_filter('parse_query', [$this, 'taxonomy_filter_post_type_request']);
        // Kontakttyp als zusätzliche Spalte in Übersicht

        add_filter('manage_person_posts_columns', array($this, 'change_columns'));
        add_action('manage_person_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        // Sortierung der zusätzlichen Spalte

        add_filter('manage_edit-person_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'posttype_person_custom_columns_orderby'));

    }

    public function taxonomy_filter_post_type_request($query)
    {
        global $pagenow, $typenow;
        if ($typenow == 'person') {
            if ('edit.php' == $pagenow) {
                $filters = get_object_taxonomies($typenow);

                foreach ($filters as $tax_slug) {
                    $var = &$query->query_vars[$tax_slug];
                    if (isset($var)) {
                        $term = get_term_by('id', $var, $tax_slug);
                        if (!empty($term)) {
                            $var = $term->slug;
                        }

                    }
                }
            }
        }
    }
    public function person_restrict_manage_posts()
    {
        global $typenow;
        if ($typenow == 'person') {
            $typenow = $this->postType;
            $filters = get_object_taxonomies($typenow);
            foreach ($filters as $tax_slug) {
                $tax_obj = get_taxonomy($tax_slug);
                wp_dropdown_categories(array(
                    'show_option_all' => sprintf(__('Alle %s anzeigen', 'rrze-contact'), $tax_obj->label),
                    'taxonomy' => $tax_slug,
                    'name' => $tax_obj->name,
                    'orderby' => 'name',
                    'selected' => isset($_GET[$tax_slug]) ? $_GET[$tax_slug] : '',
                    'hierarchical' => $tax_obj->hierarchical,
                    'show_count' => true,
                    'hide_if_empty' => true,
                ));
            }
        }
    }

    // Change the columns for the edit CPT screen
    public function change_columns($cols)
    {
        $cols = array(
            'cb' => '<input type="checkbox" />',
            'title' => __('Titel', 'rrze-contact'),
            'thumb' => __('Bild', 'rrze-contact'),
            'fullname' => __('Angezeigter Name', 'rrze-contact'),
            'contact' => __('Kontakt', 'rrze-contact'),
            'source' => __('Datenquelle', 'rrze-contact'),
            'author' => __('Bearbeiter', 'rrze-contact'),
            'date' => __('Datum', 'rrze-contact'),
        );

        return $cols;
    }

    public function custom_columns($column, $post_id)
    {
        $univisid = get_post_meta($post_id, 'fau_person_univis_id', true);
        $data = Data::get_fields($post_id, $univisid, 0);
        $univisconfig = Config::get_Config();
        $api_url = $univisconfig['api_url'];

        switch ($column) {
            case 'thumb':
                $thumb = Data::create_kontakt_image($post_id, 'person-thumb-v3', '', true, false, '', false);
                echo $thumb;
                break;

            case 'fullname':

                $fullname = Schema::create_Name($data, '', '', 'span', false);
                if (empty(trim($fullname))) {
                    $fullname = get_the_title($post_id);
                }
                echo $fullname;
                break;
            case 'contact':
                // echo $data['email'];
                echo Schema::create_contactpointlist($data, 'ul', '', '', 'li');

                break;
            case 'source':
                if ($univisid) {
                    echo __('UnivIS', 'rrze-contact') . ' (Id: <a target="univis" href="' . $api_url . '?search=persons&id=' . $univisid . '&show=info">' . $univisid . '</a>)';
                } else {
                    echo __('Lokal', 'rrze-contact');
                }
                break;

        }
    }

    // Make these columns sortable
    public function sortable_columns($columns)
    {
        $columns = array(
            'title' => 'title',
            'source' => 'source',
            'date' => 'date',
        );
        return $columns;
    }

    public function posttype_person_custom_columns_orderby($query)
    {
        if (!is_admin()) {
            return;
        }

        $post_type = $query->query['post_type'];
        if ($post_type == 'person') {

            /*
            $admin_posts_per_page = 25;
            if (isset($this->settings->constants) && isset($this->settings->constants['admin_posts_per_page'])) {
            $admin_posts_per_page = $this->settings->constants['admin_posts_per_page'];
            }
            $orderby = $query->get( 'orderby' );

            //  $query->set( 'posts_per_page', $admin_posts_per_page );
             */

            if (!isset($query->query['orderby'])) {
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                $orderby = 'title';
            } else {
                $orderby = $query->query['orderby'];
            }

            if ('source' == $orderby) {
                $query->set('orderby', 'meta_value');

                $meta_query = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'fau_person_univis_id',
                        'compare' => 'NOT EXISTS',
                        'value' => 0,
                    ),
                    array(
                        'key' => 'fau_person_univis_id',
                        'compare' => 'EXISTS',
                    ),
                );

                $query->set('meta_query', $meta_query);
            }
        }
    }

}
