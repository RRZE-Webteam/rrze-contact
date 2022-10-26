<?php

namespace RRZE\Contact\Metaboxes;

defined('ABSPATH') || exit;

use RRZE\Contact\Metaboxes\Contact;
use RRZE\Contact\Metaboxes\Location;
use RRZE\Contact\Metaboxes\Pages;
use RRZE\Contact\Metaboxes\Posts;

class Metaboxes
{
    protected $pluginFile;
    private $settings = '';
    public $prefix = '_rrze_contact_'; // starts with an underscore to hide fields from custom fields list 

    public function __construct($pluginFile, $settings)
    {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded()
    {
        require_once plugin_dir_path($this->pluginFile) . 'vendor/cmb2/init.php';
        $contactmb = new Contact($this->pluginFile, $this->settings);
        $contactmb->onLoaded();

        $locationmb = new Location($this->pluginFile, $this->settings);
        $locationmb->onLoaded();

        $pagesmb = new Pages($this->pluginFile, $this->settings);
        $pagesmb->onLoaded();

        $postsmb = new Posts($this->pluginFile, $this->settings);
        $postsmb->onLoaded();
    }

    public function getVal($fieldname, $section = null, $nr = null)
    {
        if ($section == null) {
            return ($this->bUnivisSync && !empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : (!empty($this->postMeta[$this->prefix . $fieldname][0]) ? $this->postMeta[$this->prefix . $fieldname][0] : (!empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : '')));
        } else {
            return ($this->bUnivisSync && !empty($this->univisData[$section][$nr][$fieldname]) ? $this->univisData[$section][$nr][$fieldname] : (!empty($this->postMeta[$this->prefix . $fieldname][0]) ? $this->postMeta[$this->prefix . $fieldname . $nr][0] : (!empty($this->univisData[$section][$nr][$fieldname]) ? $this->univisData[$section][$nr][$fieldname] : '')));
        }
    }

    public function getDesc($field_args, $field)
    {
        preg_match('/^' . $this->prefix . '(.*)Group_(\d)_' . $this->prefix . '(.*)/', $field_args["id"], $matches);

        if (!empty($matches[0])){
            $univisField = (!empty($this->univisData[$matches[1]][$matches[2]][$matches[3]]) ? $this->univisData[$matches[1]][$matches[2]][$matches[3]] : null);
        }else{
            $field = substr($field_args["id"], strlen($this->prefix));
            $univisField = (!empty($this->univisData[$field]) ? $this->univisData[$field] : null);
        }
        return '<br><span class="cmb2-metabox-description">' . (!empty($univisField) ? $this->descFound . $univisField : ($this->univisID ? $this->descNotFound : '')) . '</span>';
    }

    public function getReadonly($fieldname)
    {
        return ($this->bUnivisSync && in_array($fieldname, $this->aDisabled));
    }

    public function makeCMB2fields($aFields)
    {
        $aRet = [];

        foreach ($aFields as $details) {
            $aRet[$details['name']] = [
                'name' => $details['label'],
                'type' => 'text',
                'id' => $this->prefix . $details['name'],
                'show_on_cb' => 'callback_cmb2_show_on_contact',
                'after_field' => [$this, 'getDesc'],
                'attributes' => [
                    'readonly' => $this->getReadonly($details['name']),
                ],
            ];
        }
        return $aRet;
    }
}
