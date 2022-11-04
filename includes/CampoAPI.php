<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class DIPAPI
{

    protected $api;
    // protected $orgID;
    protected $atts;
    protected $DIPParam;
    protected $sem;
    protected $gast;

    // public function __construct($api, $orgID, $atts)
    public function __construct($atts)
    {
        $this->setAPI();
        $this->atts = $atts;
    }


    private function getKey()
    {
        $DIPOptions = get_option('rrze-contact');

        if (!empty($DIPOptions['basic_ApiKey'])) {
            return $DIPOptions['basic_ApiKey'];
        }
        elseif (is_multisite()) {
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->DIP_apiKey)) {
                return $settingsOptions->plugins->DIP_apiKey;
            }
        }
        else {
            return '';
        }
    }

    public function getResponse($sParam = NULL)
    {
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

        if ($apiResponse['response']['code'] != 200) {
            $aRet = [
                'valid' => FALSE,
                'content' => $apiResponse['response']['code'] . ': ' . $apiResponse['response']['message']
            ];
        }
        else {
            $content = json_decode($apiResponse['body'], true);
            $aRet = [
                'valid' => TRUE,
                'content' => $content['data']
            ];
        }

        return $aRet;
    }


    private function setAPI()
    {
        $this->api = 'https://api.fau.de/pub/v1/mschema/person';
    }

    private static function log(string $method, string $logType = 'error', string $msg = '')
    {
        // uses plugin rrze-log
        $pre = __NAMESPACE__ . ' ' . $method . '() : ';
        if ($logType == 'DB') {
            global $wpdb;
            do_action('rrze.log.error', $pre . '$wpdb->last_result= ' . json_encode($wpdb->last_result) . '| $wpdb->last_query= ' . json_encode($wpdb->last_query . '| $wpdb->last_error= ' . json_encode($wpdb->last_error)));
        }
        else {
            do_action('rrze.log.' . $logType, __NAMESPACE__ . ' ' . $method . '() : ' . $msg);
        }
    }

    public function getData($dataType, $DIPParam = null)
    {
        $this->DIPParam = urlencode($DIPParam);

        if (!$url) {
            return 'Set DIP Org ID in settings.';
        }
        $data = file_get_contents($url);
        if (!$data) {
            DIPAPI::log('getData', 'error', "no data returned using $url");
            return false;
        }
        $data = json_decode($data, true);
        $data = $this->mapIt($dataType, $data);
        $data = $this->dict($data);
        $data = $this->sortGroup($dataType, $data);
        return $data;
    }


    public function getMap($dataType)
    {
        $map = [];

        switch ($dataType) {
            case 'personByID':
            case 'personByOrga':
            case 'personByOrgaPhonebook':
            case 'personByName':
            case 'personAll':
                $map = [
                    'node' => 'Person',
                    'fields' => [
                        'person_id' => 'id',
                        'key' => 'key',
                        'title' => 'title',
                        'atitle' => 'atitle',
                        'givenName' => 'givenName',
                        'lastname' => 'lastname',
                        'work' => 'work',
                        'officehours' => 'officehour',
                        'department' => 'orgname',
                        'organization' => ['orgunit', 1],
                        'locations' => 'location',
                    ],
                ];
                break;
            case 'roomByID':
            case 'roomByName':
                $map = [
                    'node' => 'Room',
                    'fields' => [
                        'room_id' => 'id',
                        'key' => 'key',
                        'name' => 'name',
                        'short' => 'short',
                        'roomno' => 'roomno',
                        'buildno' => 'buildno',
                        'north' => 'north',
                        'east' => 'east',
                        'address' => 'address',
                        'size' => 'size',
                        'description' => 'description',
                        'blackboard' => 'tafel',
                        'flipchart' => 'flip',
                        'beamer' => 'beam',
                        'microphone' => 'mic',
                        'audio' => 'audio',
                        'overheadprojector' => 'ohead',
                        'tv' => 'tv',
                        'internet' => 'inet',
                    ],
                ];
                break;
            case 'orga':
                $map = [
                    'node' => 'Org',
                    'fields' => [
                        'orga_positions' => 'job',
                    ],
                ];
                break;
            case 'departmentByName':
            case 'departmentAll':
                $map = [
                    'node' => 'Org',
                    'fields' => [
                        'orgnr' => 'orgnr',
                        'name' => 'name',
                    ],
                ];
                break;
        }

        return $map;
    }


    public function mapIt($dataType, &$data)
    {
        $map = $this->getMap($dataType);

        if (empty($map)) {
            return $data;
        }

        $ret = [];
        $show = true;

        if (isset($data[$map['node']])) {
            foreach ($data[$map['node']] as $nr => $entry) {
                foreach ($map['fields'] as $k => $v) {
                    if (is_array($v)) {
                        if (is_int($v[1])) {
                            if (isset($data[$map['node']][$nr][$v[0]][$v[1]])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][$v[1]];
                            }
                            elseif (isset($data[$map['node']][$nr][$v[0]][0])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][0];
                            }
                        }
                        else {
                            $y = 0;
                            while (isset($data[$map['node']][$nr][$v[0]][$y][$v[1]])) {
                                $ret[$nr][$k] = $data[$map['node']][$nr][$v[0]][$y][$v[1]];
                                $y++;
                            }
                        }
                    }
                    else {
                        if (isset($data[$map['node']][$nr][$v])) {
                            $ret[$nr][$k] = $data[$map['node']][$nr][$v];
                        }
                    }
                }
            }
        }

        return $ret;
    }

    public function sortGroup($dataType, &$data)
    {
        if (empty($data)) {
            return [];
        }
        // sort by name
        if (in_array($dataType, ['departmentByName', 'departmentAll'])) {
            usort($data, [$this, 'sortByName']);
        }

        return $data;
    }

    private function filterByLang($arr)
    {
        $ret = [];
        foreach ($arr as $key => $val) {
            if (!empty($val['leclanguage']) && ($val['leclanguage'] == $this->atts['lang'])) {
                $ret[$key] = $val;
            }
        }
        return $ret;
    }

    private function multiMap($val)
    {
        return trim(strtolower($val));
    }

    private function groupBy($arr, $key)
    {
        $ret = [];
        foreach ($arr as $val) {
            if (!empty($val[$key])) {
                $ret[$val[$key]][] = $val;
            }
        }
        return $ret;
    }

    private function sortByLastname($a, $b)
    {
        return strcasecmp($a["lastname"], $b["lastname"]);
    }

    private function sortByName($a, $b)
    {
        return strcasecmp($a["name"], $b["name"]);
    }


    private function dict(&$data)
    {
        $fields = [
            'title' => [
                "Dr." => __('Doctor', 'rrze-univis'),
                "Prof." => __('Professor', 'rrze-univis'),
                "Dipl." => __('Diploma', 'rrze-univis'),
                "Inf." => __('Computer Science', 'rrze-univis'),
                "Wi." => __('Business Informatics', 'rrze-univis'),
                "Ma." => __('Math', 'rrze-univis'),
                "Ing." => __('Engineering', 'rrze-univis'),
                "B.A." => __('Bachelor', 'rrze-univis'),
                "M.A." => __('Magister Artium', 'rrze-univis'),
                "phil." => __('Humanities', 'rrze-univis'),
                "pol." => __('Political Science', 'rrze-univis'),
                "nat." => __('Natural Science', 'rrze-univis'),
                "soc." => __('Social Science', 'rrze-univis'),
                "techn." => __('Technical Sciences', 'rrze-univis'),
                "vet.med." => __('Veterinary Medicine', 'rrze-univis'),
                "med.dent." => __('Dentistry', 'rrze-univis'),
                "h.c." => __('honorary', 'rrze-univis'),
                "med." => __('medicine', 'rrze-univis'),
                "jur." => __('law', 'rrze-univis'),
                "rer." => "",
            ],
            'repeat' => [
                "w1" => "",
                "w2" => __('Every other week', 'rrze-univis'),
                "w3" => __('Every third week', 'rrze-univis'),
                "w4" => __('Every fourth week', 'rrze-univis'),
                "w5" => "",
                "m1" => "",
                "s1" => __('single appointment on', 'rrze-univis'),
                "bd" => __('block event', 'rrze-univis'),
                '0' => __(' Su', 'rrze-univis'),
                '1' => __(' Mo', 'rrze-univis'),
                '2' => __(' Tue', 'rrze-univis'),
                '3' => __(' Wed', 'rrze-univis'),
                '4' => __(' Thu', 'rrze-univis'),
                '5' => __(' Fr', 'rrze-univis'),
                '6' => __(' Sa', 'rrze-univis'),
                '7' => __(' Su', 'rrze-univis'),
            ],
            'locations' => '',
            'organizational' => '',
        ];

        foreach ($data as $nr => $row) {
            foreach ($fields as $field => $values) {
                if (isset($data[$nr][$field]) && ($field == 'locations')) {
                    foreach ($data[$nr]['locations'] as $l_nr => $location) {
                        if (!empty($location['tel'])) {
                            $data[$nr]['locations'][$l_nr]['tel'] = self::correctPhone($data[$nr]['locations'][$l_nr]['tel']);
                            $data[$nr]['locations'][$l_nr]['tel_call'] = '+' . self::getInt($data[$nr]['locations'][$l_nr]['tel']);
                        }
                        if (!empty($location['fax'])) {
                            $data[$nr]['locations'][$l_nr]['fax'] = self::correctPhone($data[$nr]['locations'][$l_nr]['fax']);
                        }
                        if (!empty($location['mobile'])) {
                            $data[$nr]['locations'][$l_nr]['mobile'] = self::correctPhone($data[$nr]['locations'][$l_nr]['mobile']);
                            $data[$nr]['locations'][$l_nr]['mobile_call'] = '+' . self::getInt($data[$nr]['locations'][$l_nr]['mobile']);
                        }
                    }
                }
                elseif ($field == 'repeat') {
                    if (isset($data[$nr]['officehours'])) {
                        foreach ($data[$nr]['officehours'] as $c_nr => $entry) {
                            if (isset($data[$nr]['officehours'][$c_nr]['repeat'])) {
                                $data[$nr]['officehours'][$c_nr]['repeat'] = trim(str_replace(array_keys($values), array_values($values), $data[$nr]['officehours'][$c_nr]['repeat']));
                            }
                        }
                    }
                }
                elseif ($field == 'organizational') {
                    if (isset($data[$nr][$field])) {
                        $data[$nr][$field] = self::formatDIP($data[$nr][$field]);
                    }
                }
            }
        }
        return $data;
    }

}
