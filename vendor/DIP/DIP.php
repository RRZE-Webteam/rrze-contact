<?php

namespace RRZE\OldLib;

/**
 * The initation loader for DIP, and the main plugin file.
 *
 * @category     WordPress Plugin and Library
 * @package      DIP
 * @author       RRZE Webteam
 * @license      GPL-2.0+
 * @link         https://blogs.fau.de/webworking
 *
 * Version:      1.0.0
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

defined('ABSPATH') || exit;
// require_once 'Config.php';
// require_once 'Sanitizer.php';
require_once 'Data.php';


class DIP
{


    const VERSION = '1.0.0';

    public static $single_instance = null;


    public static function initiate()
    {
        if (null === self::$single_instance) {
            self::$single_instance = new self();
        }
        return self::$single_instance;
    }


    public function __construct()
    {
        if (!function_exists('add_action')) {
            // We are running outside of the context of WordPress.
            return;
        }

        add_action('init', array($this, 'include_dip'));
    }


    public function include_dip()
    {
        if (class_exists('DIP', false)) {
            return;
        }

        if (!defined('DIP_VERSION')) {
            define('DIP_VERSION', self::VERSION);
        }

        if (!defined('DIP_DIR')) {
            define('DIP_DIR', trailingslashit(dirname(__FILE__)));
        }

        // Include helper functions.
        //require_once DIP_DIR . 'includes/Config.php';

        // Now kick off the class autoloader.
        spl_autoload_register('RRZE\OldLib\dip_autoload_classes');
    }
}

// Make it so...
DIP::initiate();

function dip_autoload_classes($class_name)
{
    $prefix = 'RRZE\OldLib\DIP';
    $base_dir = DIP_DIR; //  . '/includes/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class_name, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class_name, $len);
    $file = $base_dir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}
