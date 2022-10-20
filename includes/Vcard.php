<?php

namespace RRZE\Contact;


defined('ABSPATH') || exit;

/**
 * Generates a vCard 4.0
 */
class Vcard
{

    // private props = [
    //     BEGIN:VCARD
    //     VERSION:4.0
    //     N:Mustermann;Erika;;Dr.;
    //     FN:Dr. Erika Mustermann
    //     ORG:Wikimedia
    //     ROLE:Kommunikation
    //     TITLE:Redaktion & Gestaltung
    //     PHOTO;MEDIATYPE=image/jpeg:http://commons.wikimedia.org/wiki/File:Erika_Mustermann_2010.jpg
    //     TEL;TYPE=work,voice;VALUE=uri:tel:+49-221-9999123
    //     TEL;TYPE=home,voice;VALUE=uri:tel:+49-221-1234567
    //     ADR;TYPE=home;LABEL="Heidestraße 17\n51147 Köln\nDeutschland":;;Heidestraße 17;Köln;;51147;Germany
    //     EMAIL:erika@mustermann.de
    //     REV:20140301T221110Z
    //     END:VCARD
    // ];
    private $header = 'BEGIN:VCARD\nVERSION:4.0';
    private $footer =  'END:VCARD';

    public function __construct()
    {
    }

    public function createCard(array $aPerson = []):string {

        $aCard = [];

        $aCard[] = $this->header;
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
        $aCard[] = $this->footer;

        return implode('\r\n', $aCard);
    }

    public function showCard(array $aPerson = []):string{
        return $this->createCard($aPerson);
    }

    public function encodeCard(){
        return '';
    }

    public function downloadCard(){

        // header("Content-type:text/vcard; charset=utf-8");
        // header("Content-Disposition: attachment; filename=vcardexport.vcf");
       
    }

    
}