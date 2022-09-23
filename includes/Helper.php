<?php

namespace RRZE\Contact;
use function RRZE\Contact\Config\getConstants;

defined('ABSPATH') || exit;

class Helper {
    /**
     * [isPluginAvailable description]
     * @param  [string  $plugin [description]
     * @return boolean         [description]
     */
    public static function isPluginAvailable($plugin) {
        if (is_network_admin()) {
            return file_exists(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin);
        } elseif (! function_exists('is_plugin_active')) {
            include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        return is_plugin_active($plugin);
    }
    
    
    public static  function array_orderby(){
		$args = func_get_args();
		$data = array_shift($args);
		foreach ($args as $n => $field) {
			if (is_string($field)) {
				$tmp = array();
				foreach ($data as $key => $row)
                                    if(isset($row[$field])) {
					$tmp[$key] = $row[$field];
                                    } else {
                                        $tmp[$key] = '';
                                    }
				$args[$n] = $tmp;
			}
		}
		$args[] = &$data;
		call_user_func_array('array_multisort', $args);
		return array_pop($args);
	}
	
    public static function sonderzeichen ($string) {
        $search = array("Ä", "Ö", "Ü", "ä", "ö", "ü", "ß", "´");
        $replace = array("Ae", "Oe", "Ue", "ae", "oe", "ue", "ss", "");
        return str_replace($search, $replace, $string);
    }
    
       //Überprüft bei neuen Seiten ob Person oder Einrichtung eingegeben wird, abhängig vom Feldtyp rrze_contact_typ
    public static function default_rrze_contact_typ( ) {     
        if(isset($_GET["rrze_contact_typ"]) && $_GET["rrze_contact_typ"] == 'einrichtung') {
            $default_rrze_contact_typ = 'einrichtung';
        } else {
            $default_rrze_contact_typ = 'realcontact';
        }
        return $default_rrze_contact_typ;
    }     
    
    public static function admin_notice_phone_number() {
    ?>
        <div class="notice notice-warning">
            <p><?php _e( 'Bitte korrigieren Sie das Format der Telefon- oder Faxnummer, die Anzeige ist nicht einheitlich!', 'rrze-contact' ); ?></p>
        </div>
        <?php
    }

    public static function isFAUTheme() {
	$constants = getConstants();
	$themelist = $constants['fauthemes'];
	$fautheme = false;
	$active_theme = wp_get_theme();
	$active_theme = $active_theme->get( 'Name' );
	if (in_array($active_theme, $themelist)) {
	    $fautheme = true;
	}
	return $fautheme;   
    }
    
    public static function get_html_var_dump($input) {
	$out = self::get_var_dump($input);
	
	$out = preg_replace("/=>[\r\n\s]+/", ' => ', $out);
	$out = preg_replace("/\s+bool\(true\)/", ' <span style="color:green">TRUE</span>,', $out);
	$out = preg_replace("/\s+bool\(false\)/", ' <span style="color:red">FALSE</span>,', $out);
	$out = preg_replace("/,([\r\n\s]+})/", "$1", $out);
	$out = preg_replace("/\s+string\(\d+\)/", '', $out);
	$out = preg_replace("/\[\"([a-z\-_0-9]+)\"\]/i", "[\"<span style=\"color:#dd8800\">$1</span>\"]", $out);
	
	return '<pre>'.$out.'</pre>';
    }
    public static function get_var_dump($input) {
	ob_start(); 
	var_dump($input);
	return "\n" . ob_get_clean();
    }
}