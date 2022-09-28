<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class CampoAPI extends API
{

    public function __construct()
    {
        parent::__construct();
    }

    private function getKey()
    {
        $key = parent::getKey('contact');

        if (!empty($key)) {
            return $key;
        }

        if (is_multisite()) {
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->campo_apiKey)) {
                return $settingsOptions->plugins->campo_apiKey;
            }
        } else {
            return '';
        }
    }

    private function setAPI()
    {
        $this->api = 'https://api.fau.de/pub/v1/mschema/contacts';
        // $this->api = 'https://api.fau.de/pub/v1/mschema/organizations';
    }



    public function getInt($str)
    {
        preg_match_all('/\d+/', $str, $matches);
        return implode('', $matches[0]);
    }


}
