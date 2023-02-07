<?php

/**
 * Plugin Name:     RRZE Contact
 * Plugin URI:      https://github.com/RRZE-Webteam/rrze-contact
 * Description:     Einbindung von Daten aus Contact
 * Version:         0.1.27
 * Requires at least: 6.1
 * Requires PHP:      8.0
 * Author:          RRZE-Webteam
 * Author URI:      https://blogs.fau.de/webworking/
 * License:         GNU General Public License v3
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:     /languages
 * Text Domain:     rrze-contact
 */

namespace RRZE\Contact;

defined('ABSPATH') || exit;

use RRZE\Contact\Main;
use function RRZE\Contact\Config\getSettingsFields;

// Laden der Konfigurationsdatei
require_once __DIR__ . '/config/config.php';

// spl_autoload_extensions(".php"); // comma-separated list
// spl_autoload_register();

// Automatische Laden von Klassen.
// Autoloader (PSR-4)
spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__;
    $base_dir = __DIR__ . '/includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

const RRZE_PHP_VERSION = '8.0';
const RRZE_WP_VERSION = '6.1';

// Registriert die Plugin-Funktion, die bei Aktivierung des Plugins ausgeführt werden soll.
register_activation_hook(__FILE__, __NAMESPACE__ . '\activation');
// Registriert die Plugin-Funktion, die ausgeführt werden soll, wenn das Plugin deaktiviert wird.
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\deactivation');
// Wird aufgerufen, sobald alle aktivierten Plugins geladen wurden.
add_action('plugins_loaded', __NAMESPACE__ . '\loaded');

/**
 * Einbindung der Sprachdateien.
 */
function loadTextDomain()
{
    load_plugin_textdomain('rrze-univis', false, sprintf('%s/languages/', dirname(plugin_basename(__FILE__))));
}

/**
 * Überprüft die Systemvoraussetzungen.
 */
function systemRequirements(): string
{
    $error = '';
    if (version_compare(PHP_VERSION, RRZE_PHP_VERSION, '<')) {
        $error = sprintf(__('The server is running PHP version %1$s. The Plugin requires at least PHP version %2$s.', 'rrze-rsvp'), PHP_VERSION, RRZE_PHP_VERSION);
    }
    elseif (version_compare($GLOBALS['wp_version'], RRZE_WP_VERSION, '<')) {
        $error = sprintf(__('The server is running WordPress version %1$s. The Plugin requires at least WordPress version %2$s.', 'rrze-rsvp'), $GLOBALS['wp_version'], RRZE_WP_VERSION);
    }
    return $error;
}

/**
 * Wird nach der Aktivierung des Plugins ausgeführt.
 */
function activation()
{
    // Sprachdateien werden eingebunden.
    loadTextDomain();

    // Überprüft die minimal erforderliche PHP- u. WP-Version.
    // Wenn die Überprüfung fehlschlägt, dann wird das Plugin automatisch deaktiviert.
    if ($error = systemRequirements()) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die($error);
    }

    // Endpoint hinzufügen
    add_endpoint(true);
}

function add_endpoint()
{
    add_rewrite_endpoint('id', EP_PERMALINK | EP_PAGES);
    flush_rewrite_rules();
}

function showFAUPersonNotice()
{
    echo '<div class="notice notice-info is-dismissible">
          <p>' . __('Plugin FAU-Person has automatically been deactivated. Please use RRZE-Contact instead. All old shortcodes will work. There is no update to be done.', 'rrze-contact') . '</p>
          </div>';
}

function update()
{
    // update only one time
    // $version = '0.0.25';
    // $isUpdated = get_option('rrze-contact-updated', FALSE);

    // if ($isUpdated == $version) {
    //     return;
    // }

    $postIDs = get_posts([
        'post_type' => 'person',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]);

    foreach ($postIDs as $postID) {
        set_post_type($postID, 'contact');
    }

    $postIDs = get_posts([
        'post_type' => 'standort',
        'post_status' => 'any',
        'numberposts' => -1,
        'fields' => 'ids',
    ]);

    foreach ($postIDs as $postID) {
        set_post_type($postID, 'location');
    }

    $oldOptions = get_option('_fau_person');

    $newOptions = get_option('rrze-contact');

    if (!empty($oldOptions)){

        if (!empty($oldOptions['sidebar_titel'])){
            $newOptions['sidebar_honorificPrefix'] = $oldOptions['sidebar_titel'];
        }
        if (!empty($oldOptions['sidebar_familyName'])){
            $newOptions['sidebar_familyName'] = $oldOptions['sidebar_familyName'];
        }
        if (!empty($oldOptions['sidebar_givenName'])){
            $newOptions['sidebar_givenName'] = $oldOptions['sidebar_givenName'];
        }
        if (!empty($oldOptions['sidebar_position'])){
            $newOptions['sidebar_position'] = $oldOptions['sidebar_position'];
        }
        if (!empty($oldOptions['sidebar_titel'])){
            $newOptions['sidebar_honorificPrefix'] = $oldOptions['sidebar_titel'];
        }
        if (!empty($oldOptions['sidebar_suffix'])){
            $newOptions['sidebar_honorificSuffix'] = $oldOptions['sidebar_suffix'];
        }
        if (!empty($oldOptions['sidebar_suffix'])){
            $newOptions['sidebar_honorificSuffix'] = $oldOptions['sidebar_suffix'];
        }


        // Testen! on => true, off => false



        // sidebar_name ?

        // sidebar_adresse ?

        // sidebar_workLocation ?

        // sidebar_telefon ?

        // sidebar_mobil ?

        // sidebar_fax ?

        // sidebar_mail ?

        // sidebar_webseite ?

        // sidebar_sprechzeiten ?

        // sidebar_kurzauszug ?

        // sidebar_bild ?

        // sidebar_socialmedia ?

        // sidebar_ansprechpartner ?

        // constants_view_telefonlink ? (besser weglassen)

        // constants_view_telefon_intformat ? (besser weglassen)
        
        // constants_view_some_position ?

        if (!empty($oldOptions['constants_view_raum_prefix'])){
            $newOptions['layout_room_text'] = $oldOptions['constants_view_raum_prefix'];
        }
        if (!empty($oldOptions['constants_view_kontakt_linktext'])){
            $newOptions['layout_moreButton_text'] = $oldOptions['constants_view_kontakt_linktext'];
        }
        if (!empty($oldOptions['constants_view_kontakt_page_imagecaption'])){
            $newOptions['layout_imagecaption'] = $oldOptions['constants_view_kontakt_page_imagecaption'];
        }
        if (!empty($oldOptions['constants_view_kontakt_page_imagesize'])){
            $newOptions['layout_imagesize'] = $oldOptions['constants_view_kontakt_page_imagesize'];
        }
        if (!empty($oldOptions['constants_view_thumb_size'])){
            $newOptions['layout_view_thumb_size'] = $oldOptions['constants_view_thumb_size'];
        }
        if (!empty($oldOptions['constants_view_card_size'])){
            $newOptions['layout_view_card_size'] = $oldOptions['constants_view_card_size'];
        }
        if (!empty($oldOptions['constants_view_card_linkimage'])){
            $newOptions['layout_imagelink'] = $oldOptions['constants_view_card_linkimage'];
        }
        if (!empty($oldOptions['constants_backend_view_metabox_kontaktlist'])){
            $newOptions['layout_backend_view_metabox_contactlist'] = $oldOptions['constants_backend_view_metabox_kontaktlist'];
        }
        if (!empty($oldOptions['sidebar_organisation'])){
            $newOptions['sidebar_organization'] = $oldOptions['sidebar_organisation'];
        }
        if (!empty($oldOptions['sidebar_abteilung'])){
            $newOptions['sidebar_department'] = $oldOptions['sidebar_abteilung'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_has_archive_page'])){
            $newOptions['layout_has_archive_page'] = $oldOptions['constants_has_archive_page'];
        }
        if (!empty($oldOptions['constants_view_kontakt_linkname'])){
            $newOptions['layout_view_contact_linkname'] = $oldOptions['layout_view_contact_linkname'];
        }
    


    echo '<pre>';
    var_dump($oldOptions);


    // $newOptions = getSettingsFields();

    var_dump($newOptions);

    }

    exit;

    // 2DO: get old settings and use them for this plugin

    // deactivate rrze-contact
    if (is_plugin_active('rrze-contact/rrze-contact.php')) {
        deactivate_plugins('rrze-contact/rrze-contact.php');
        add_action('admin_notices', 'RRZE\Contact\showFAUPersonNotice');
    }

    update_option('rrze-contact-updated', $version);
}


/**
 * Wird durchgeführt, nachdem das Plugin deaktiviert wurde.
 */
function deactivation()
{
// Hier können die Funktionen hinzugefügt werden, die
// bei der Deaktivierung des Plugins aufgerufen werden müssen.
// Bspw. delete_option, wp_clear_scheduled_hook, flush_rewrite_rules, etc.
}

/**
 * Instantiate Plugin class.
 * @return object Plugin
 */
function plugin() {
    static $instance;
    if (null === $instance) {
        $instance = new Plugin(__FILE__);
    }

    return $instance;
}

/**
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 */
function loaded()
{
    // Sprachdateien werden eingebunden.
    loadTextDomain();
    plugin()->onLoaded();

    if ($error = systemRequirements()) {
        add_action('admin_init', function () use ($error) {
            if (current_user_can('activate_plugins')) {
                $pluginData = get_plugin_data(plugin()->getFile());
                $pluginName = $pluginData['Name'];
                $tag = is_plugin_active_for_network(plugin()->getBaseName()) ? 'network_admin_notices' : 'admin_notices';
                add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' .
                            /* translators: 1: The plugin name, 2: The error string. */
                            __('Plugins: %1$s: %2$s', 'rrze-newsletter') .
                            '</p></div>',
                        esc_html($pluginName),
                        esc_html($error)
                    );
                });
            }
        });
        return;
    }

    // Hauptklasse (Main) wird instanziiert.
    $main = new Main(__FILE__);
    $main->onLoaded();

    update();
}
