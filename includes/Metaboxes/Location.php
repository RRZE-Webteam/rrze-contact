<?php

namespace RRZE\Contact\Metaboxes;

defined('ABSPATH') || exit;

/**
 * Define Metaboxes for Kontakt-Edit
 */
class Location extends Metaboxes
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
		add_filter('cmb2_meta_boxes', array($this, 'cmb2_location_metaboxes'));

	}



	public function cmb2_location_metaboxes($meta_boxes)
	{
		$prefix = $this->prefix;


		// Meta-Box Locationinformation - fau_location_info
		$meta_boxes['fau_location_info'] = array(
			'id' => 'fau_location_info',
			'title' => __('Locationinformationen', 'rrze-contact'),
			'object_types' => array('location'), // post type
			//'show_on' => array( 'key' => 'submenu-slug', 'value' => 'kontakt' ),        
			'context' => 'normal',
			'priority' => 'default',
			'fields' => array(
					array(
					'name' => __('Straße und Hausnummer', 'rrze-contact'),
					'desc' => '',
					'type' => 'text',
					'id' => $prefix . 'streetAddress',
					'default' => 'Schloßplatz 4'
				),
					array(
					'name' => __('Postleitzahl', 'rrze-contact'),
					//'desc' => 'Wenn der Ort aus UnivIS übernommen werden soll bitte leer lassen!',
					'desc' => __('Nur 5-stellige Zahlen erlaubt.', 'rrze-contact'),
					'type' => 'text_small',
					'id' => $prefix . 'postalCode',
					'sanitization_cb' => 'validate_plz',
					'default' => '91054'
				),
					array(
					'name' => __('Ort', 'rrze-contact'),
					'desc' => '',
					'type' => 'text',
					'id' => $prefix . 'addressLocality',
					'default' => 'Erlangen'
				),
					array(
					'name' => __('Land', 'rrze-contact'),
					'desc' => '',
					'type' => 'text',
					'id' => $prefix . 'addressCountry',
					'default' => 'Bayern'
				),
			)
		);

		return $meta_boxes;
	}
}