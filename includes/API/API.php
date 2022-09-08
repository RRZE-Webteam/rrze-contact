<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class API
{

    protected $url;
    protected $api;
    protected $atts;
    protected $APIParam;

    public function __construct()
    {
    }


    private function getKey($provider){
        $options = get_option('rrze-contact');

        if (!empty($options['basic_' . $provider . '_ApiKey'])){
            return $options['basic_' . $provider . '_ApiKey'];
        }elseif(is_multisite()){
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->{$provider . '_apiKey'})){
                return $settingsOptions->plugins->{$provider . '_apiKey'};
            }
        }else{
            return '';
        }
    }

    public function getResponse($sParam = NULL){
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $aGetArgs = [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Api-Key' => $this->getKey(),
                ]
            ];

        $apiResponse = wp_remote_get($this->api . $sParam, $aGetArgs);

        if ($apiResponse['response']['code'] != 200){
            $aRet = [
                'valid' => FALSE, 
                'content' => $apiResponse['response']['code'] . ': ' . $apiResponse['response']['message']
            ];    
        }else{
            $content = json_decode($apiResponse['body'], true);
            $aRet = [
                'valid' => TRUE, 
                'content' => $content['data']
            ];
        }

        return $aRet;
    }

    public static function normalizePhone($phone)
    {
        if ((strpos($phone, '+49 9131 85-') !== 0) && (strpos($phone, '+49 911 5302-') !== 0)) {
            if (!preg_match('/\+49 [1-9][0-9]{1,4} [1-9][0-9]+/', $phone)) {
                $phone_data = preg_replace('/\D/', '', $phone);
                $vorwahl_erl = '+49 9131 85-';
                $vorwahl_nbg = '+49 911 5302-';

                switch (strlen($phone_data)) {
                    case '3':
                        $phone = $vorwahl_nbg . $phone_data;
                        break;

                    case '5':
                        if (strpos($phone_data, '06') === 0) {
                            $phone = $vorwahl_nbg . substr($phone_data, -3);
                            break;
                        }
                        $phone = $vorwahl_erl . $phone_data;
                        break;

                    case '7':
                        if (strpos($phone_data, '85') === 0 || strpos($phone_data, '06') === 0) {
                            $phone = $vorwahl_erl . substr($phone_data, -5);
                            break;
                        }

                        if (strpos($phone_data, '5302') === 0) {
                            $phone = $vorwahl_nbg . substr($phone_data, -3);
                            break;
                        }

                    // no break
                    default:
                        if (strpos($phone_data, '9115302') !== false) {
                            $durchwahl = explode('9115302', $phone_data);
                            if (strlen($durchwahl[1]) === 3 || strlen($durchwahl[1]) === 5) {
                                $phone = $vorwahl_nbg . $durchwahl[1];
                            }
                            break;
                        }

                        if (strpos($phone_data, '913185') !== false) {
                            $durchwahl = explode('913185', $phone_data);
                            if (strlen($durchwahl[1]) === 5) {
                                $phone = $vorwahl_erl . $durchwahl[1];
                            }
                            break;
                        }

                        if (strpos($phone_data, '09131') === 0 || strpos($phone_data, '499131') === 0) {
                            $durchwahl = explode('9131', $phone_data);
                            $phone = "+49 9131 " . $durchwahl[1];
                            break;
                        }

                        if (strpos($phone_data, '0911') === 0 || strpos($phone_data, '49911') === 0) {
                            $durchwahl = explode('911', $phone_data);
                            $phone = "+49 911 " . $durchwahl[1];
                            break;
                        }
                }
            }
        }
        return $phone;
    }


    private static function log(string $method, string $logType = 'error', string $msg = '')
    {
        // uses plugin rrze-log
        $pre = __NAMESPACE__ . ' ' . $method . '() : ';
        if ($logType == 'DB') {
            global $wpdb;
            do_action('rrze.log.error', $pre . '$wpdb->last_result= ' . json_encode($wpdb->last_result) . '| $wpdb->last_query= ' . json_encode($wpdb->last_query . '| $wpdb->last_error= ' . json_encode($wpdb->last_error)));
        } else {
            do_action('rrze.log.' . $logType, __NAMESPACE__ . ' ' . $method . '() : ' . $msg);
        }
    }


}
