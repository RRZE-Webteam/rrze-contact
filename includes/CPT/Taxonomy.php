<?php

namespace RRZE_Contact\Taxonomy;

defined('ABSPATH') || exit;

use RRZE_Contact\Main;
use RRZE_Contact\Taxonomy\Kontakt;
use RRZE_Contact\Taxonomy\Standort;

/**
 * Laden und definieren der Posttypes
 */
class Taxonomy extends Main {
    protected $pluginFile;
    private $settings = '';
    
    public function __construct($pluginFile, $settings) {
        $this->pluginFile = $pluginFile;
        $this->settings = $settings;
    }

    public function onLoaded() {
        $kontakt_posttype = new Kontakt( $this->pluginFile,  $this->settings);
        $kontakt_posttype->onLoaded();

        $standort_posttype = new Standort( $this->pluginFile,  $this->settings);
        $standort_posttype->onLoaded();
	
	
	if( get_transient('rrze-contact-options') ) {
            flush_rewrite_rules();
            delete_transient('rrze-contact-options');
	}
    }
}
