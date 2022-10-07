<?php

/**
 * Plugin Name:     RRZE Contact
 * Plugin URI:      https://github.com/RRZE-Webteam/rrze-contact
 * Description:     Einbindung von Daten aus Contact
 * Version:         0.0.28
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

const RRZE_PHP_VERSION = '7.4';
const RRZE_WP_VERSION = '5.3';

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
    flush_rewrite_rules();
}

// update();


function add_endpoint()
{
    add_rewrite_endpoint('id', EP_PERMALINK | EP_PAGES);
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
    $version = '0.0.25';
    $isUpdated = get_option('rrze-contact-updated', FALSE);

    if ($isUpdated == $version) {
        return;
    }

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

    // deactivate FAU-Person
    if (is_plugin_active('fau-person/fau-person.php')) {
        deactivate_plugins('fau-person/fau-person.php');
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
 * Wird durchgeführt, nachdem das WP-Grundsystem hochgefahren
 * und alle Plugins eingebunden wurden.
 */
function loaded()
{
    // Sprachdateien werden eingebunden.
    loadTextDomain();

    // Überprüft die Systemvoraussetzungen.
    if ($error = systemRequirements()) {
        add_action('admin_init', function () use ($error) {
            $pluginData = get_plugin_data(__FILE__);
            $pluginName = $pluginData['Name'];
            $tag = is_plugin_active_for_network(plugin_basename(__FILE__)) ? 'network_admin_notices' : 'admin_notices';
            add_action($tag, function () use ($pluginName, $error) {
                    printf(
                        '<div class="notice notice-error"><p>' . __('Plugins: %1$s: %2$s', 'rrze-univis') . '</p></div>',
                        esc_html($pluginName),
                        esc_html($error)
                    );
                }
                );
            });
        // Das Plugin wird nicht mehr ausgeführt.
        return;
    }

    // Hauptklasse (Main) wird instanziiert.
    $main = new Main(__FILE__);
    $main->onLoaded();

    update();
}
