<?php

define('PKG_NAME', 'MaxNotify');
define('PKG_NAME_LOWER', 'maxnotify');
define('PKG_VERSION', '1.0.1');
define('PKG_RELEASE', 'pl');
define('PKG_ROOT', dirname(__DIR__) . '/');
define('PKG_CORE', PKG_ROOT . 'core/components/' . PKG_NAME_LOWER . '/');

if (!defined('MODX_CORE_PATH')) {
    $modxCorePath = getenv('MODX_CORE_PATH');
    if (!$modxCorePath) {
        $candidates = array(
            PKG_ROOT . 'core/',
            dirname(PKG_ROOT) . '/core/',
            dirname(dirname(PKG_ROOT)) . '/core/',
        );
        foreach ($candidates as $candidate) {
            if (is_file($candidate . 'model/modx/modx.class.php')) {
                $modxCorePath = $candidate;
                break;
            }
        }
    }
    if (!$modxCorePath) {
        throw new RuntimeException(
            'MODX core was not found. Set the MODX_CORE_PATH environment variable.'
        );
    }
    define('MODX_CORE_PATH', rtrim($modxCorePath, '/\\') . '/');
}
