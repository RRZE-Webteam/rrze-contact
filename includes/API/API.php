<?php

namespace RRZE\Contact\API;

defined('ABSPATH') || exit;

use RRZE\Contact\Functions;
use RRZE\Contact\Sanitize;

class API
{

    protected $url;
    protected $api;
    protected $atts;
    protected $APIParam;

    public function __construct($api)
    {
        $this->api = $api;
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

    private function getResponse($sParam = NULL){
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
            Functions::log('getResponse', 'error', $this->api . $sParam . ' returns ' . $aRet['content']);
        }else{
            $content = json_decode($apiResponse['body'], true);
            $aRet = [
                'valid' => TRUE, 
                'content' => $content['data']
            ];
        }

        return $aRet;
    }



}
