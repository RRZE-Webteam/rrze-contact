<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

if (!function_exists('__')) {
    function __($txt, $domain)
    {
        return $txt;
    }
}

class CampoAPI
{

    protected $api;
    // protected $orgID;
    protected $atts;
    protected $campoParam;
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
        $campoOptions = get_option('rrze-contact');

        if (!empty($campoOptions['basic_ApiKey'])) {
            return $campoOptions['basic_ApiKey'];
        }
        elseif (is_multisite()) {
            $settingsOptions = get_site_option('rrze_settings');
            if (!empty($settingsOptions->plugins->campo_apiKey)) {
                return $settingsOptions->plugins->campo_apiKey;
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

    public function getData($dataType, $campoParam = null)
    {
        $this->campoParam = urlencode($campoParam);

        if (!$url) {
            return 'Set Campo Org ID in settings.';
        }
        $data = file_get_contents($url);
        if (!$data) {
            CampoAPI::log('getData', 'error', "no data returned using $url");
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
                        'firstname' => 'firstname',
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

    public static function correctPhone($phone)
    {
        if ((strpos($phone, '+49 9131 85-') !== 0) && (strpos($phone, '+49 911 5302-') !== 0)) {
            if (!preg_match('/\+49 [1-9][0-9]{1,4} [1-9][0-9]+/', $phone)) {
                $phone_data = preg_replace('/\D/', '', $phone);
                $vorwahl_erl = '+49 9131 85-';
                $vorwahl_erl_p1_p6 = '+49 9131 81146-'; // see: https://github.com/RRZE-Webteam/fau-person/issues/353
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

                        // see: https://github.com/RRZE-Webteam/fau-person/issues/353
                        if (strpos($phone_data, '913181146') !== FALSE) {
                            $durchwahl = explode('913181146', $phone_data);
                            $phone = $vorwahl_erl_p1_p6 . $durchwahl[1];
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

    public function getInt($str)
    {
        preg_match_all('/\d+/', $str, $matches);
        return implode('', $matches[0]);
    }

    public function formatCampo($txt)
    {
        $subs = array(
            '/^\-+\s+(.*)?/mi' => '<ul><li>$1</li></ul>', // list
            '/(<\/ul>\n(.*)<ul>*)+/' => '', // list
            '/\*{2}/m' => '/\*/', // **
            '/_{2}/m' => '/_/', // __
            '/\|(.*)\|/m' => '<i>$1</i>', // |itallic|
            '/_(.*)_/m' => '<sub>$1</sub>', // H_2_O
            '/\^(.*)\^/m' => '<sup>$1</sup>', // pi^2^
            '/\[([^\]]*)\]\s{0,1}((http|https|ftp|ftps):\/\/\S*)/mi' => '<a href="$2">$1</a>', // [link text] http...
            '/\[([^\]]*)\]\s{0,1}(mailto:)([^")\s<>]+)/mi' => '<a href="mailto:$3">$1</a>', // find [link text] mailto:email@address.tld but not <a href="mailto:email@address.tld">mailto:email@address.tld</a>
            '/\*(.*)\*/m' => '<strong>$1</strong>', // *bold*
        );

        $txt = preg_replace(array_keys($subs), array_values($subs), $txt);
        $txt = nl2br($txt);
        $txt = make_clickable($txt);
        return $txt;
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
                        $data[$nr][$field] = self::formatCampo($data[$nr][$field]);
                    }
                }
            }
        }
        return $data;
    }

}
