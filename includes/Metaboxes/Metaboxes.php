<?php

namespace RRZE\Contact\Metaboxes;

defined('ABSPATH') || exit;

use RRZE\Contact\Main;
use RRZE\Contact\Metaboxes\Contact;
use RRZE\Contact\Metaboxes\Location;
use RRZE\Contact\Metaboxes\Pages;
use RRZE\Contact\Metaboxes\Posts;
use RRZE\OldLib\UnivIS\Data as UnivIS_Data;


class Metaboxes  {
     protected $pluginFile;
     private $settings = '';
     public $prefix = 'rrze_contact_';
     
    public function __construct($pluginFile, $settings) {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()     {
		require_once(plugin_dir_path($this->pluginFile) . 'vendor/cmb2/init.php');
		$contactmb = new Contact($this->pluginFile,  $this->settings);
		$contactmb->onLoaded();

		$locationmb = new Location($this->pluginFile,  $this->settings);
		$locationmb->onLoaded();

		$pagesmb = new Pages($this->pluginFile,  $this->settings);
		$pagesmb->onLoaded();

		$postsmb = new Posts($this->pluginFile,  $this->settings);
		$postsmb->onLoaded();
    }
    
    public function getVal($fieldname)
    {
        return ($this->bUnivisSync && !empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : (!empty($this->postMeta[$this->prefix . $fieldname][0]) ? $this->postMeta[$this->prefix . $fieldname][0] : (!empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : '')));
    }

    public function getDesc($fieldname)
    {
        return (!empty($this->univisData[$fieldname]) ? $this->descFound . $this->univisData[$fieldname] : ($this->univisID ? $this->descNotFound : ''));
    }

    public function getDisabled($fieldname)
    {
        return ($this->bUnivisSync && !empty($this->univisData[$fieldname]));
    }
    
	public function makeCMB2fields($aFields){
		$aRet = [];
		foreach($aFields as $details){
			$aRet[$details['name']] = [
				'name' => $details['label'],
				'type' => 'text',
				'id' => $this->prefix . $details['name'],
				'description' => $this->getDesc($details['name']),
				'show_on_cb' => 'callback_cmb2_show_on_contact',
				'attributes' => [
					'value' => $this->getVal($details['name']),
					'disabled' => $this->getDisabled($details['name']),
				],
			];
		}
		return $aRet;
	}
}