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

    private function getResponse($sParam = null)
    {
        $aRet = [
            'valid' => false,
            'content' => '',
        ];

        $apiResponse = file_get_contents($this->api . $sParam);
        $apiResponse = json_decode($apiResponse, true);

        if (empty($apiResponse['Person'])) {
            $aRet = [
                'valid' => false,
                'content' => __('No contact found.', 'rrze-contact'),
            ];
            Functions::log('getResponse', 'error', $this->api . $sParam . ' returns ' . $aRet['content']);
        } else {
            $aRet = [
                'valid' => true,
                'content' => $apiResponse['Person'],
            ];
        }

        return $aRet;
    }

    private function sanitizeData($data)
    {
        $aPhoneTypes = ['phone', 'fax', 'mobile'];

        foreach ($data as $nr => $person) {
            if (!empty($person['locations'])) {
                foreach ($person['locations'] as $lnr => $location) {
                    foreach ($aPhoneTypes as $phoneType) {
                        if (!empty($location[$phoneType])) {
                            $data[$nr]['locations'][$lnr][$phoneType] = Functions::formatPhone($data[$nr]['locations'][$lnr][$phoneType]);
                        }

                    }
                    if (!empty($data[$nr]['locations'][$lnr]['email'])) {
                        $data[$nr]['locations'][$lnr]['email'] = sanitize_email($data[$nr]['locations'][$lnr]['email']);
                    }
                }
            }
        }

        return $data;
    }

    private function mapData($data)
    {
        $map = [
            'personID' => 'id',
            'IDM' => 'idm_id',
            'key' => 'key',
            'honorificPrefix' => 'title',
            'honorificSuffix' => 'atitle',
            'firstName' => 'firstname',
            'familyName' => 'lastname',
            'position' => 'work',
            'organization_de' => [
                'orgunit' => [
                    'institution' => 0,
                    'organization' => 1,
                    'department' => 2,
                ],
            ],
            'organization_en' => [
                'orgunit_en' => [
                    'institution' => 0,
                    'organization' => 1,
                    'department' => 2,
                ],
            ],
            'locations' => [
                'location' => [
                    'city' => 'ort',
                    'street' => 'street',
                    'room' => 'office',
                    'phone' => 'tel',
                    'mobile' => 'mobile',
                    'fax' => 'fax',
                    'email' => 'email',
                    'pgp' => 'pgp',
                    'url' => 'url',
                ],
            ],
            'consultations' => [
                'officehour' => [
                    'starttime' => 'starttime',
                    'endtime' => 'endtime',
                    'repeat' => 'repeat',
                    'room' => 'office',
                    'comment' => 'comment',
                    'phone' => 'tel',
                    'mobile' => 'mobile',
                    'fax' => 'fax',
                    'email' => 'email',
                    'pgp' => 'pgp',
                    'url' => 'url',
                ],
            ],
        ];

        $ret = [];
        foreach ($data as $person) {
            $tmp = [];
            foreach ($map as $field => $univisField) {
                if (is_array($univisField)) {
                    foreach ($univisField as $univisSubfield => $aDetails) {
                        if (!in_array($univisSubfield, ['orgunit', 'orgunit_en'])) {
                            if (!empty($person[$univisSubfield])) {
                                $i = 0;
                                foreach ($person[$univisSubfield] as $nr => $val) {
                                    $tmpDetails = [];
                                    foreach ($aDetails as $subfield => $univisSubSubfield) {
                                        if (!empty($person[$univisSubfield][$nr][$univisSubSubfield])) {
                                            $tmpDetails[$subfield] = $person[$univisSubfield][$nr][$univisSubSubfield];
                                        }
                                    }
                                    $tmp[$field][] = $tmpDetails;
                                    $i++;
                                }
                                $tmp[$field . 'Count'] = $i;

                            }
                        } else {
                            $tmpDetails = [];
                            foreach ($aDetails as $subfield => $univisSubSubfield) {
                                if (!empty($person[$univisSubfield][$univisSubSubfield])) {
                                    $tmpDetails[$subfield] = $person[$univisSubfield][$univisSubSubfield];
                                }

                            }
                            $tmp[$field] = $tmpDetails;
                        }
                    }
                } elseif (!empty($person[$univisField])) {
                    $tmp[$field] = $person[$univisField];
                }
            }
            $ret[] = $tmp;
        }
        return $ret;
    }

    public function getPerson($sParam = null)
    {
        // $sParam = 'id=40014582'; // TEST

        $apiResponse = $this->getResponse($sParam);

        // echo '<pre>';
        // var_dump($apiResponse);
        // exit;

        if ($apiResponse['valid']) {
            return [
                'valid' => true,
                'content' => $this->sanitizeData($this->mapData($apiResponse['content'])),
            ];
        } else {
            return $apiResponse;
        }
    }

}
