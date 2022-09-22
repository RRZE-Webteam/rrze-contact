<?php

namespace RRZE\Contact\Metaboxes;
// use FAU_Person\Data;
// use FAU_Person\Helper;

defined('ABSPATH') || exit;

/**
 * Define Metaboxes for Kontakt-Edit
 */
class Pages extends Metaboxes {

    protected $pluginFile;
    private $settings = '';
    
    public function __construct($pluginFile, $settings) {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;	
    }

    public function onLoaded()    {
	add_filter('cmb2_meta_boxes', array( $this, 'cmb2_pages_metaboxes') );
	
    }
   
    

    public function cmb2_pages_metaboxes( $meta_boxes ) {
	$prefix = $this->prefix;
	
	if (isset($this->settings->options) && isset($this->settings->options['constants_backend_view_metabox_kontaktlist']) && ($this->settings->options['constants_backend_view_metabox_kontaktlist'])) {
	    // Meta-Box zur Anzeige der verfügbaren Kontakte auf post und page, um die Personen-ID schneller herauszufinden
	    $contactselect = Data::get_contactdata();
	    $meta_boxes['rrze_contact_page_metabox'] = array(
		'id'		=> 'rrze_contact_page_metabox',
		'title'		=> __( 'Kontaktinformationen', 'rrze-contact' ),
		'object_types'    => array('page'), // post type
		'context'	=> 'side',
		'priority'	=> 'default',
		'show_names'	=> true, // Show field names on the left
		'fields'		=> array(
		    array(
			'name' => __('Verfügbare Kontakte anzeigen', 'rrze-contact'),
			'desc' => '<p id="rrze_contact_showhint">Shortcode:<br> <code id="copyshortcode"></code> <button class="button-link" type="button" aria-expanded="false" id="rrze_contact_cp_shortcode">'.__('Kopieren','rrze-contact').'</button></p>',
			'id' => $prefix . 'contactselect',
			'type' => 'select',
			'options' => $contactselect,
		    ),
		)        
	    );
	}
	return $meta_boxes;

	
    }
}