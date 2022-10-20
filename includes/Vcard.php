<?php

namespace RRZE\Contact;


defined('ABSPATH') || exit;

/**
 * Generates a vCard 4.0
 */
class Vcard
{
    protected $vCard = '';


    public function __construct(array $aPerson = []){
        $aCard = [];
        $aCard[] = 'BEGIN:VCARD\nVERSION:4.0';
        $aCard[] = 'N:' . (!empty($aPerson['familyName']) ? $aPerson['familyName'] : '') . ';' . (!empty($aPerson['firstName']) ? $aPerson['firstName'] : '') . ';;' . (!empty($aPerson['honorificPrefix']) ? $aPerson['honorificPrefix'] : '') . ';' . (!empty($aPerson['honorificSuffix']) ? $aPerson['honorificSuffix'] : '');
        $aCard[] = 'FN:' . (!empty($aPerson['honorificPrefix']) ? $aPerson['honorificPrefix'] . ' ' : '') . (!empty($aPerson['honorificSuffix']) ? '(' . $aPerson['honorificSuffix'] . ') ' : '') . (!empty($aPerson['firstName']) ? $aPerson['firstName'] . ' ' : '') . (!empty($aPerson['familyName']) ? $aPerson['familyName'] : '');
        if (!empty($aPerson['organization_de'])){
            $aCard[] = 'ORG:' . implode(';', $aPerson['organization_de']);
        }
        if (!empty($aPerson['position'])){
            $aCard[] = 'ROLE:' . $aPerson['position'];
        }
        if (!empty($aPerson['organization_de']['department'])){
            $aCard[] = 'TITLE:' . $aPerson['organization_de']['department'];
        }
        // if (!empty($aPerson[])){
        //     $aCard[] = 'PHOTO;MEDIATYPE=image/jpeg::' . $aPerson[];
        // }
        if (!empty($aPerson['locations'])){
            foreach($aPerson['locations'] as $location){
                if (!empty($location['phone'])){
                    $aCard[] = 'TEL;TYPE=VOICE,WORK;VALUE=uri:tel:' . $location['phone'];
                }
                if (!empty($location['mobile'])){
                    $aCard[] = 'TEL;TYPE=VOICE,CELL;VALUE=uri:tel:' . $location['mobile'];
                }
                if (!(empty($location['street']) && empty($location['city']))){
                    // ADR;TYPE=home;LABEL="Heidestraße 17\n51147 Köln\nDeutschland":;;Heidestraße 17;Köln;;51147;Germany
                    // Post Office Address; Extended Address; Street; Locality; Region; Postal Code; Country
                    if (!empty($location['city'])){
                        $aParts = explode(' ', $location['city']);
                        if (count($aParts) > 1){
                            $location['plz'] = $aParts[0];
                            $location['city'] = $aParts[1];
                        }
                    }
                    $aCard[] = 'ADR;TYPE=home;LABEL="' . (!empty($location['street']) ? $location['street'] . '\n' : '') . (!empty($location['plz']) ? $location['plz'] . '\n' : '') . (!empty($location['city']) ? $location['city'] . '\n' : '') . ':;;' . (!empty($location['street']) ? $location['street'] . ';' : '') . (!empty($location['city']) ? $location['city'] . ';' : '') . (!empty($location['plz']) ? $location['plz'] . ';' : '');
                }
                if (!empty($location['email'])){
                    $aCard[] = 'EMAIL:' . $location['email'];
                }
            }
        }

        $dt = new \DateTime();
        $aCard[] = 'REV:' . $dt->format('Ymd\THisZ');
        $aCard[] = 'END:VCARD';

        $this->vCard = implode('\r\n', $aCard);
    }

    public function showCard():string{
        return $this->vCard;
    }

    public function showCardQR(){
        if ((include_once 'phpqrcode/qrlib.php') == TRUE) {
            // \QRcode::png($this->vCard); // this displays the image directly in the browser - I want data to be returned - 
        }else{
            return 'QR Lib is missing';
        }
    }

    public function encodeCard(){
        return '';
    }

    public function downloadCard(){

        // header("Content-type:text/vcard; charset=utf-8");
        // header("Content-Disposition: attachment; filename=vcardexport.vcf");
       
    }

    
}