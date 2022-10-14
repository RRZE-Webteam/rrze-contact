<?php

namespace RRZE\Contact\API;

defined('ABSPATH') || exit;

use RRZE\Contact\Functions;


class UnivIS extends API
{

    public function __construct()
    {
        parent::__construct(UNIVIS_URL . '?show=json&search=persons&');
    }

    private function getResponse($sParam = NULL){
        $aRet = [
            'valid' => FALSE, 
            'content' => ''
        ];

        $apiResponse = file_get_contents($this->api . $sParam);
        $apiResponse = json_decode($apiResponse, true);

        if (empty($apiResponse['Person'])){
            $aRet = [
                'valid' => FALSE, 
                'content' => __('No contact found.', 'rrze-contact')
            ];  
            Functions::log('getResponse', 'error', $this->api . $sParam . ' returns ' . $aRet['content']);
        }else{
            $aRet = [
                'valid' => TRUE, 
                'content' => $apiResponse['Person']
            ];
        }

        return $aRet;
    }

    private function sanitizeData($data){
        foreach($data as $nr => $person){
            if (!empty($data[$nr]['phone'])){
                $data[$nr]['phone'] = Functions::formatPhone($data[$nr]['phone']);
            }
            if (!empty($data[$nr]['fax'])){
                $data[$nr]['fax'] = Functions::formatPhone($data[$nr]['fax']);
            }
            if (!empty($data[$nr]['mobile'])){
                $data[$nr]['mobile'] = Functions::formatPhone($data[$nr]['mobile']);
            }
            if (!empty($data[$nr]['email'])){
                $data[$nr]['email'] = sanitize_email($data[$nr]['email']);
            }
        }
        
        return $data;
    }

    private function mapData($data){
        $map = [
            'personID' => 'id',
            'key' => 'key',
            'honorificPrefix' => 'title',
            'honorificSuffix' => 'atitle',
            'firstName' => 'firstname',
            'familyName' => 'lastname',
            'work' => 'work',
            'officehours' => 'officehour',
            'department' => 'orgname',
            // 'organization' => ['orgunit', 1],
        ];

        $map_location = [
            'city' => 'ort',
            'street' => 'street',
            'office' => 'office',
            'phone' => 'tel',
            'fax' => 'fax',
            'email' => 'email',
            'url' => 'url'
        ];
        $ret = [];

        foreach($data as $person){
                $tmp = [];
                foreach($map as $field => $univisField){
                    if (!empty($person[$univisField])){
                        $tmp[$field] = $person[$univisField];
                    }
                }
                foreach($person['location'] as $nr => $locationDetails){
                    foreach($map_location as $field => $univisField){
                        $tmp[$field] = $locationDetails[$univisField];
                    }
                }
                $ret[] = $tmp;
        }
        return $ret;
    }


    public function getPerson($sParam = NULL){
        $apiResponse = $this->getResponse($sParam);

        if ($apiResponse['valid']){
            return [
                'valid' => TRUE,
                'content' => $this->sanitizeData($this->mapData($apiResponse['content'])),
                ];
        }else{
            return $apiResponse;
        }
    }



}
