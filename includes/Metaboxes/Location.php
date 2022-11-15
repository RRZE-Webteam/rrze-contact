<?php

namespace RRZE\Contact\Metaboxes;

use function RRZE\Contact\Config\getFields;

defined('ABSPATH') || exit;

class Location extends Metaboxes
{
    protected $pluginFile;
    private $settings = '';
	public $bUnivisSync = false;
    public $univisID = 0;


    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        add_action('cmb2_admin_init', [$this, 'makeMetaboxes']);

    }

    public function makeMetaboxes()
    {

		$aVisibleFields = ['street', 'city', 'room'];
		$aAllFields = getFields('locations');

		foreach($aAllFields as $nr => $aDetails){
			if (!in_array($aDetails['name'], $aVisibleFields)){
				unset($aAllFields[$nr]);
			}
		}

		$aFields = $this->makeCMB2fields($aAllFields);

        $cmb = new_cmb2_box([
            'id' => 'rrze_location_info',
            'title' => __('Location\'s informations', 'rrze-contact'),
            'object_types' => ['location'], // post type
            'context' => 'normal',
            'priority' => 'default',
            'fields' => $aFields,
        ]);
    }

}
