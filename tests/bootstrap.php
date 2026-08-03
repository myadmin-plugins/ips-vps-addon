<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap file for myadmin-ips-vps-addon tests.
 *
 * Loads the autoloader, the module settings and constants MyAdmin would have set up
 * for the vps module, and the stand-ins for the framework services the addon calls,
 * so the plugin hooks and the addon's summary callback can be exercised for real.
 */

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/stubs/framework.php';

// Settings the vps module registers at request time.
if (!defined('VPS_IP_COST')) {
    define('VPS_IP_COST', 3.5);
}
if (!defined('VPS_MAX_IPS')) {
    define('VPS_MAX_IPS', 5);
}

// Result-set mode flag the addon passes to $db->next_record()
if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', 1);
}

register_module('vps', [
    'PREFIX' => 'vps',
    'TABLE' => 'vps',
    'TBLNAME' => 'VPS',
    'TITLE' => 'VPS Services',
]);
