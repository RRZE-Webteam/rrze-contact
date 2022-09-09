<?php

namespace RRZE_Contact\Taxonomy;

use function RRZE_Contact\Config\get_rrze_contact_capabilities;
use RRZE\Lib\UnivIS\Config;
use RRZE_Contact\Data;
use RRZE_Contact\Schema;

defined('ABSPATH') || exit;

/**
 * Posttype contact
 */
class Kontakt extends Taxonomy
{

    protected $postType = 'contact';
    protected $taxonomy = 'contact_category';

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
            'name' => _x('Contacts', 'Post Type General Name', 'rrze-contact'),
            'singular_name' => _x('Contact', 'Post Type Singular Name', 'rrze-contact'),
            'menu_name' => __('Contacts', 'rrze-contact'),
            'parent_item_colon' => __('Superordinate contact', 'rrze-contact'),
            'all_items' => __('All contacts', 'rrze-contact'),
            'view_item' => __('Show contact', 'rrze-contact'),
            'add_new_item' => __('Add contact', 'rrze-contact'),
            'add_new' => __('New contact', 'rrze-contact'),
            'edit_item' => __('Edit contact', 'rrze-contact'),
            'update_item' => __('Update contact', 'rrze-contact'),
            'search_items' => __('Search contact', 'rrze-contact'),
            'not_found' => __('No contacts found', 'rrze-contact'),
            'not_found_in_trash' => __('No contacts found in trash', 'rrze-contact'),
        ];
        $has_archive_page = true;
        if (isset($this->settings->options) && isset($this->settings->options['constants_has_archive_page'])) {
            $has_archive_page = $this->settings->options['constants_has_archive_page'];
        }
        $caps = get_rrze_contact_capabilities();
        $contact_args = array(
            'label' => __('Contact', 'rrze-contact'),
            'description' => __('Contact\'s informations', 'rrze-contact'),
            'labels' => $labels,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'revisions'),
            'taxonomies' => array('contact_category'),
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
            'query_var' => 'contact',
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

        register_post_type($this->postType, $contact_args);

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
        add_action('restrict_manage_posts', [$this, 'contact_restrict_manage_posts']);
        add_filter('parse_query', [$this, 'taxonomy_filter_post_type_request']);
        // Kontakttyp als zusätzliche Spalte in Übersicht

        add_filter('manage_contact_posts_columns', array($this, 'change_columns'));
        add_action('manage_contact_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        // Sortierung der zusätzlichen Spalte

        add_filter('manage_edit-contact_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'posttype_contact_custom_columns_orderby'));

    }

    public function taxonomy_filter_post_type_request($query)
    {
        global $pagenow, $typenow;
        if ($typenow == 'contact') {
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
    public function contact_restrict_manage_posts()
    {
        global $typenow;
        if ($typenow == 'contact') {
            $typenow = $this->postType;
            $filters = get_object_taxonomies($typenow);
            foreach ($filters as $tax_slug) {
                $tax_obj = get_taxonomy($tax_slug);
                wp_dropdown_categories(array(
                    'show_option_all' => sprintf(__('Show all %s', 'rrze-contact'), $tax_obj->label),
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
            'title' => __('Title', 'rrze-contact'),
            'thumb' => __('Image', 'rrze-contact'),
            'fullname' => __('Shown name', 'rrze-contact'),
            'contact' => __('Contact', 'rrze-contact'),
            'source' => __('Data source', 'rrze-contact'),
            'author' => __('Editor', 'rrze-contact'),
            'date' => __('Date', 'rrze-contact'),
        );

        return $cols;
    }

    public function custom_columns($column, $post_id)
    {
        $dipid = get_post_meta($post_id, 'rrze_contact_univis_id', true);
        $data = Data::get_fields($post_id, $dipid, 0);
        $univisconfig = Config::get_Config();
        $api_url = $univisconfig['api_url'];

        switch ($column) {
            case 'thumb':
                $thumb = Data::create_kontakt_image($post_id, 'contact-thumb-v3', '', true, false, '', false);
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
                if ($dipid) {
                    echo __('DIP', 'rrze-contact') . ' (Id: <a target="univis" href="' . $api_url . '?search=contacts&id=' . $dipid . '&show=info">' . $dipid . '</a>)';
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

    public function posttype_contact_custom_columns_orderby($query)
    {
        if (!is_admin()) {
            return;
        }

        $post_type = $query->query['post_type'];
        if ($post_type == 'contact') {

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
                        'key' => 'rrze_contact_univis_id',
                        'compare' => 'NOT EXISTS',
                        'value' => 0,
                    ),
                    array(
                        'key' => 'rrze_contact_univis_id',
                        'compare' => 'EXISTS',
                    ),
                );

                $query->set('meta_query', $meta_query);
            }
        }
    }

}
