<?php

namespace RRZE\Contact\Metaboxes;

defined('ABSPATH') || exit;

use RRZE\Contact\Metaboxes\Contact;
use RRZE\Contact\Metaboxes\Location;
use RRZE\Contact\Metaboxes\Pages;
use RRZE\Contact\Metaboxes\Posts;
// use RRZE\Contact\Sanitize;

class Metaboxes
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
        require_once plugin_dir_path($this->pluginFile) . 'vendor/cmb2/init.php';
        require_once plugin_dir_path($this->pluginFile) . '/includes/Sanitize.php';

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
            return ($this->bUnivisSync && !empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : (!empty($this->postMeta[RRZE_CONTACT_PREFIX . $fieldname][0]) ? $this->postMeta[RRZE_CONTACT_PREFIX . $fieldname][0] : (!empty($this->univisData[$fieldname]) ? $this->univisData[$fieldname] : '')));
        } else {
            return ($this->bUnivisSync && !empty($this->univisData[$section][$nr][$fieldname]) ? $this->univisData[$section][$nr][$fieldname] : (!empty($this->postMeta[RRZE_CONTACT_PREFIX . $fieldname][0]) ? $this->postMeta[RRZE_CONTACT_PREFIX . $fieldname . $nr][0] : (!empty($this->univisData[$section][$nr][$fieldname]) ? $this->univisData[$section][$nr][$fieldname] : '')));
        }
    }

    public function getDesc($field_args, $field)
    {
        preg_match('/^' . RRZE_CONTACT_PREFIX . '(.*)Group_(\d)_' . RRZE_CONTACT_PREFIX . '(.*)/', $field_args["id"], $matches);

        if (!empty($matches[1])){
            $univisField = (!empty($this->univisData[$matches[1]][$matches[2]][$matches[3]]) ? $this->univisData[$matches[1]][$matches[2]][$matches[3]] : null);
        }else{
            $field = substr($field_args["id"], strlen(RRZE_CONTACT_PREFIX));
            $univisField = (!empty($this->univisData[$field]) ? $this->univisData[$field] : null);
        }
        return '<br><span class="cmb2-metabox-description">' . (!empty($univisField) ? $this->descFound . (is_array($univisField) ? implode(',', $univisField) : $univisField) : ($this->univisID ? $this->descNotFound : '')) . '</span>';
    }

    public function getReadonly($fieldname)
    {
        // echo '<pre>';
        // var_dump($this->aDisabled);
        // exit;
        return ($this->bUnivisSync && in_array($fieldname, $this->aDisabled));
    }

    public function makeCMB2fields($aFields)
    {
        $aRet = [];

        foreach ($aFields as $details) {
            $aRet[$details['name']] = [
                'name' => $details['label'],
                'type' => $details['type'],
                'id' => RRZE_CONTACT_PREFIX . $details['name'],
                'show_on_cb' => 'callback_cmb2_show_on_contact',
                'after_field' => [$this, 'getDesc'],
                'attributes' => [
                    'readonly' => $this->getReadonly($details['name']),
                ],
            ];
            if (!empty($details['options'])){
                $aRet[$details['name']]['options'] = $details['options'];
            }
            if (!empty($details['sanitization_cb'])){
                $aRet[$details['name']]['sanitization_cb'] = $details['sanitization_cb'];
            }            
            if (!empty($details['protocols'])){
                $aRet[$details['name']]['protocols'] = $details['protocols'];
            }            
        }

        return $aRet;
    }
}
