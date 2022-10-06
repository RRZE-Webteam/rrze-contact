<?php

namespace RRZE\Contact\API;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIP extends API
{

    public function __construct()
    {
        parent::__construct('https://api.fau.de/pub/v1/mschema/contacts');
    }

}
