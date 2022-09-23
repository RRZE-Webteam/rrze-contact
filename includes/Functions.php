<?php

namespace RRZE\Contact;

defined('ABSPATH') || exit;

class Functions
{

    protected $pluginFile;
    const TRANSIENT_PREFIX = 'rrze_campo_cache_';
    const TRANSIENT_EXPIRATION = DAY_IN_SECONDS;


    public function __construct($pluginFile)
    {
        $this->pluginFile = $pluginFile;
    }

    public function onLoaded()
    {
        // add_action('admin_enqueue_scripts', [$this, 'adminEnqueueScripts']);
        add_action('wp_ajax_GetCampoData', [$this, 'ajaxGetCampoData']);
        add_action('wp_ajax_nopriv_GetCampoData', [$this, 'ajaxGetCampoData']);
        add_action('wp_ajax_GetCampoDataForBlockelements', [$this, 'ajaxGetCampoDataForBlockelements']);
        add_action('wp_ajax_nopriv_GetCampoDataForBlockelements', [$this, 'ajaxGetCampoDataForBlockelements']);
    }

    // public function adminEnqueueScripts()
    // {
        // wp_enqueue_script(
        //     'rrze-unvis-ajax',
        //     plugins_url('js/rrze-contact.js', plugin_basename($this->pluginFile)),
        //     ['jquery'],
        //     null
        // );

        // wp_localize_script('rrze-unvis-ajax', 'campo_ajax', [
        //     'ajax_url' => admin_url('admin-ajax.php'),
        //     'nonce' => wp_create_nonce('contact-ajax-nonce'),
        // ]);

    // }

    public function getTableHTML($aIn)
    {
        if (!is_array($aIn)) {
            return $aIn;
        }
        $ret = '<table class="wp-list-table widefat striped"><thead><tr><td><b><i>Univ</i>IS</b> ID</td><td><strong>Name</strong></td></tr></thead>';
        foreach ($aIn as $ID => $val) {
            $ret .= "<tr><td>$ID</td><td style='word-wrap: break-word;'>$val</td></tr>";
        }
        $ret .= '</table>';
        return $ret;
    }

    public static function setDataToCache($data = '', $aAtts = []){
        set_transient(self::TRANSIENT_PREFIX . implode('-', $aAtts), $data, self::TRANSIENT_EXPIRATION);
    }

    public static function getDataFromCache($aAtts = [])
    {
        return get_transient(self::TRANSIENT_PREFIX . implode('-', $aAtts));
    }

    public function ajaxGetCampoData()
    {
        check_ajax_referer('contact-ajax-nonce', 'nonce');
        $inputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $response = $this->getTableHTML($this->getCampoData(null, $inputs['dataType'], $inputs['keyword']));
        wp_send_json($response);
    }

    public function getSelectHTML($aIn)
    {
        if (!is_array($aIn)) {
            return "<option value=''>$aIn</option>";
        }
        $ret = '<option value="">' . __('-- All --', 'rrze-contact') . '</option>';
        natsort($aIn);
        foreach ($aIn as $ID => $val) {
            $ret .= "<option value='$ID'>$val</option>";
        }
        return $ret;
    }

    public function getCampoData($campoOrgID = null, $dataType = '', $keyword = null)
    {
        $data = false;
        $ret = __('No matching entries found.', 'rrze-contact');

        $options = get_option('rrze-contact');
        $data = 0;
        $CampoURL = (!empty($options['basic_campo_url']) ? $options['basic_campo_url'] : 'https://contact.uni-erlangen.de');
        $campoOrgID = (!empty($campoOrgID) ? $campoOrgID : (!empty($options['basic_CampoOrgNr']) ? $options['basic_CampoOrgNr'] : 0));

        if ($CampoURL) {
            $contact = new CampoAPI($CampoURL, $campoOrgID, null);
            $data = $contact->getData($dataType, $keyword);
        } elseif (!$CampoURL) {
            $ret = __('Link to Contact is missing.', 'rrze-contact');
        }

        if ($data) {
            $ret = [];
            switch ($dataType) {
                // case 'departmentByName':
                //     foreach ($data as $entry) {
                //         if (isset($entry['orgnr'])) {
                //             $ret[$entry['orgnr']] = $entry['name'];
                //         }
                //     }
                //     break;
                // case 'contactByName':
                //     foreach ($data as $entry) {
                //         if (isset($entry['contact_id'])) {
                //             $ret[$entry['contact_id']] = $entry['lastname'] . ', ' . $entry['firstname'];
                //         }
                //     }
                //     break;
                // case 'contactAll':
                //     foreach ($data as $position => $entries) {
                //         foreach ($entries as $entry) {
                //             if (isset($entry['contact_id'])) {
                //                 $ret[$entry['contact_id']] = $entry['lastname'] . ', ' . $entry['firstname'];
                //             }
                //         }
                //     }
                //     break;
                case 'lectureByName':
                    foreach ($data as $entry) {
                        if (isset($entry['lecture_id'])) {
                            $ret[$entry['lecture_id']] = $entry['name'];
                        }
                    }
                    break;
                case 'lectureByDepartment':
                    foreach ($data as $type => $entries) {
                        foreach ($entries as $entry) {
                            if (isset($entry['lecture_id'])) {
                                $ret[$entry['lecture_id']] = $entry['name'];
                            }
                        }
                    }
                    break;
                default:
                    $ret = 'unknown dataType';
                    break;
            }
        }

        return $ret;
    }

    public function ajaxGetCampoDataForBlockelements()
    {
        check_ajax_referer('contact-ajax-nonce', 'nonce');
        $inputs = filter_input(INPUT_POST, 'data', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
        $response = $this->getSelectHTML($this->getCampoData($inputs['campoOrgID'], $inputs['dataType']));
        wp_send_json($response);
    }

    public static function makeLinkToICS($type, $lecture, $term, $t)
    {
        $aProps = [
            'SUMMARY' => $lecture['title'],
            'LOCATION' => (!empty($t['room']) ? $t['room'] : null),
            'DESCRIPTION' => (!empty($lecture['comment']) ? $lecture['comment'] : null),
            'URL' => get_permalink(),
            'MAP' => (!empty($term['room']['north']) && !empty($term['room']['east']) ? 'https://karte.fau.de/api/v1/iframe/marker/' . $term['room']['north'] . ',' . $term['room']['east'] . '/zoom/16' : ''),
            'FILENAME' => sanitize_file_name($type),
        ];

        if (empty($term['startdate']) || empty($term['enddate'])) {
            $thisMonth = date('m');

            if ($thisMonth > 2 && $thisMonth < 8) {
                $sem = 'ss';
            } else {
                $sem = 'ws';
            }

            $options = get_option('rrze-univis');
            $semStart = (!empty($options['basic_' . $sem . 'Start']) ? $options['basic_' . $sem . 'Start'] : null);
            $semEnd = (!empty($options['basic_' . $sem . 'End']) ? $options['basic_' . $sem . 'End'] : null);

            if (empty($semStart) || empty($semEnd)) {
                $defaults = getFields();
                foreach ($defaults['basic'] as $nr => $aVal) {
                    if ($aVal['name'] == $sem . 'Start') {
                        $semStart = $aVal['default'];
                        break;
                    } elseif ($aVal['name'] == $sem . 'End') {
                        $semEnd = $aVal['default'];
                        break;
                    }
                }

                $semStart = (!empty($semStart) ? $semStart : $defaults['basic'][$sem . 'Start']['default']);
                $semEnd = (!empty($semEnd) ? $semEnd : $defaults['basic'][$sem . 'End']['default']);
            }
        }

        $aFreq = [
            "w1" => 'WEEKLY;INTERVAL=1',
            "w2" => 'WEEKLY;INTERVAL=2',
            "w3" => 'WEEKLY;INTERVAL=3',
            "w4" => 'WEEKLY;INTERVAL=4',
            "m1" => 'MONTHLY;INTERVAL=1',
            "m2" => 'MONTHLY;INTERVAL=2',
            "m3" => 'MONTHLY;INTERVAL=3',
            "m4" => 'MONTHLY;INTERVAL=4',
        ];

        $aWeekdays = [
            '1' => [
                'short' => 'MO',
                'long' => 'Monday',
            ],
            '2' => [
                'short' => 'TU',
                'long' => 'Tuesday',
            ],
            '3' => [
                'short' => 'WE',
                'long' => 'Wednesday',
            ],
            '4' => [
                'short' => 'TH',
                'long' => 'Thursday',
            ],
            '5' => [
                'short' => 'FR',
                'long' => 'Friday',
            ],
        ];

        $aGivenDays = [];

        if (!empty($term['repeatNr'])) {
            $aParts = explode(' ', $term['repeatNr']);
            if (!empty($aFreq[$aParts[0]])) {
                $aProps['FREQ'] = $aFreq[$aParts[0]];
                $aGivenDays = explode(',', $aParts[1]);
                $aProps['REPEAT'] = '';
                foreach ($aWeekdays as $nr => $val) {
                    if (in_array($nr, $aGivenDays)) {
                        $aProps['REPEAT'] .= $val['short'] . ',';
                    }
                }
                $aProps['REPEAT'] = rtrim($aProps['REPEAT'], ',');
            }
        }

        $tStart = (empty($term['starttime']) ? '00:00' : $term['starttime']);
        $tEnd = (empty($term['endtime']) ? '23:59' : $term['endtime']);
        $dStart = (empty($term['startdate']) ? $semStart : $term['startdate']);
        $dEnd = (empty($term['startdate']) ? $semEnd : $term['enddate']);
        $aProps['DTSTART'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dStart)) . date('Hi', strtotime($tStart))));
        $aProps['DTEND'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dStart)) . date('Hi', strtotime($tEnd))));
        $aProps['UNTIL'] = date('Ymd\THis', strtotime(date('Ymd', strtotime($dEnd)) . date('Hi', strtotime($tEnd))));

        if (!empty($aGivenDays)) {
            // check if day of week of DTSTART is a member of the REPEAT days
            $givenWeekday = date('N', strtotime($aProps['DTSTART']));
            if (!in_array($givenWeekday, $aGivenDays)) {
                // move to next possible date
                while(!in_array($givenWeekday, $aGivenDays)){
                    $givenWeekday++;
                    $givenWeekday = ($givenWeekday > 5 ? 1 : $givenWeekday);
                    if (in_array($givenWeekday, $aGivenDays)){
                        $aProps['DTSTART'] = date('Ymd', strtotime("next " . $aWeekdays[$givenWeekday]['long'], strtotime($aProps['DTSTART'])));
                        $aProps['DTEND'] = $aProps['DTSTART'] . date('\THis', strtotime($tEnd));
                        $aProps['DTSTART'] .= date('\THis', strtotime($tStart));
                        break;
                    }
                }
            }
        }

        $propsEncoded = base64_encode(openssl_encrypt(json_encode($aProps), 'AES-256-CBC', hash('sha256', AUTH_KEY), 0, substr(hash('sha256', AUTH_SALT), 0, 16)));
        $linkParams = [
            'v' => $propsEncoded,
            'h' => hash('sha256', $propsEncoded),
        ];

        $screenReaderTxt = __('ICS', 'rrze-univis') . ': ' . __('Date', 'rrze-univis') . ' ' . (!empty($t['repeat']) ? $t['repeat'] : '') . ' ' . (!empty($t['date']) ? $t['date'] . ' ' : '') . $t['time'] . ' ' . __('import to calendar', 'rrze-univis');

        return [
            'link' => wp_nonce_url(plugin_dir_url(__DIR__) . 'ics.php?' . http_build_query($linkParams), 'createICS', 'ics_nonce'),
            'linkTxt' => __('ICS', 'rrze-univis') . ': ' . __('Date', 'rrze-univis') . ' ' . (!empty($t['repeat']) ? $t['repeat'] : '') . ' ' . (!empty($t['date']) ? $t['date'] . ' ' : '') . $t['time'] . ' ' . __('import to calendar', 'rrze-univis'),
        ];
    }


}
