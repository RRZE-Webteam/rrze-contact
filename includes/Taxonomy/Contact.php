<?php

namespace RRZE\Contact\Taxonomy;

use RRZE\Contact\Data;
use RRZE\Contact\Schema;
use RRZE\OldLib\UnivIS\Config;
// use function RRZE\Contact\Config\get_RRZE\Contact_capabilities;

defined('ABSPATH') || exit;

/**
 * Posttype contact
 */
class Contact extends Taxonomy
{

    protected $postType = 'contact';
    protected $taxonomy = 'contact_category';
	private $settings = '';


    public function __construct($settings)
    {
		$this->settings = $settings;
    }

    public function onLoaded()
    {
        add_action('init', [$this, 'register']);
        register_taxonomy_for_object_type($this->taxonomy, $this->postType);
        add_action('restrict_manage_posts', [$this, 'contact_restrict_manage_posts']);
        add_filter('parse_query', [$this, 'taxonomy_filter_post_type_request']);
        // Kontakttyp als zusätzliche Spalte in Übersicht

        add_filter('manage_contact_posts_columns', array($this, 'change_columns'));
        add_action('manage_contact_posts_custom_column', array($this, 'custom_columns'), 10, 2);
        // Sortierung der zusätzlichen Spalte

        add_filter('manage_edit-contact_sortable_columns', array($this, 'sortable_columns'));
        add_action('pre_get_posts', array($this, 'posttype_contact_custom_columns_orderby'));

        // prevent using the archive slug (which is editable) as slug for posts, pages or media
        add_filter('wp_unique_post_slug_is_bad_hierarchical_slug', [$this, 'archiveSlugIsBadHierarchicalSlug'], 10, 4);
        add_filter('wp_unique_post_slug_is_bad_hierarchical_slug', [$this, 'archiveSlugIsBadFlatSlug'], 10, 3);
    }
    
    public function register()
    {
        $archive_slug = (!empty($this->settings->options['constants_has_archive_page']) ? $this->settings->options['constants_has_archive_page'] : $this->postType);
		$archive_slug = ($archive_slug == 1 ? $this->postType : $archive_slug);

		$has_archive_page = (!empty($this->settings->options['constants_has_archive_page']) && ($this->settings->options['constants_has_archive_page'] == $this->postType) ? true : false);
		$archive_page = get_page_by_path($archive_slug, OBJECT, 'page');
		$archive_title = (!empty($archive_page) ? $archive_page->post_title : __('Contacts', 'rrze-contact'));

        $aParams = [
            'postType' => $this->postType,
            'slug' => $archive_slug,
            'singular_name' => __('Contact', 'rrze-contact'),
            'plural_name' => __('Contacts', 'rrze-contact'),
            'supports' => ['title', 'editor', 'author', 'thumbnail', 'revisions'],
            'has_archive_page' => $has_archive_page,
		    'archive_title' => $archive_title,
            'show_in_menu' => true,
            'menu_name' => __('Contacts', 'rrze-contact'),
			'menu_icon' => 'dashicons-id-alt',
        ];

        parent::registerCPT($aParams);

        register_taxonomy(
            'contacts_category',
            'contact',
            [
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => [
                    'slug' => 'contact_category',
                    'with_front' => false,
                ],
            ]
        );
    }

    // public function register()
    // {
    //     register_taxonomy_for_object_type($this->taxonomy, $this->postType);
    //     add_action('restrict_manage_posts', [$this, 'contact_restrict_manage_posts']);
    //     add_filter('parse_query', [$this, 'taxonomy_filter_post_type_request']);
    //     // Kontakttyp als zusätzliche Spalte in Übersicht

    //     add_filter('manage_contact_posts_columns', array($this, 'change_columns'));
    //     add_action('manage_contact_posts_custom_column', array($this, 'custom_columns'), 10, 2);
    //     // Sortierung der zusätzlichen Spalte

    //     add_filter('manage_edit-contact_sortable_columns', array($this, 'sortable_columns'));
    //     add_action('pre_get_posts', array($this, 'posttype_contact_custom_columns_orderby'));

    // }

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
            'ID' => __('ID', 'rrze-contact'),
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
        $univis_id = get_post_meta($post_id, 'rrze_contact_univis_id', true);
        $dip_id = get_post_meta($post_id, 'rrze_contact_dip_id', true);
        $data = Data::get_fields($post_id, $univis_id, 0);

        switch ($column) {
            case 'thumb':
                $thumb = Data::create_contact_image($post_id, 'contact-thumb-v3', '', true, false, '', false);
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
                if ($univis_id) {
                    echo __('UnivIS', 'rrze-contact') . ' (ID: <a target="univis" href="' . UNIVIS_URL . '?search=contacts&id=' . $univis_id . '&show=info">' . $univis_id . '</a>)';
                } elseif ($dip_id) {
                    echo __('DIP', 'rrze-contact') . ' (ID: <a target="univis" href="' . DIP_URL . '?' . $dipid . '&show=info">' . $dipid . '</a>)';
                } else {
                    echo __('Local', 'rrze-contact');
                }
                break;
            case 'ID':
                echo $post_id;
                break;

        }
    }

    // Make these columns sortable
    public function sortable_columns($columns)
    {
        $columns = array(
            'ID' => 'ID',
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

    private function archiveSlugIsBadHierarchicalSlug( $isBadSlug, $slug, $post_type, $post_parent ) {
        if ($post_type == 'contact'){
            return false;
        }
        $CPTdata = get_post_type_object('contact');
        $archiveSlug = $CPTdata->rewrite['slug'];

        if ( !$post_parent && $slug == $archiveSlug ){
            return true;
        }
        return $isBadSlug;
    }

    private function archiveSlugIsBadFlatSlug( $isBadSlug, $slug, $post_type) {
        if ($post_type == 'contact'){
            return false;
        }
        $CPTdata = get_post_type_object('contact');
        $archiveSlug = $CPTdata->rewrite['slug'];

        if ($slug == $archiveSlug ){
            return true;
        }
        return $isBadSlug;
    }


}
