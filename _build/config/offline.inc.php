<?php

$database_dsn = 'mysql:host=127.0.0.1;dbname=offline;charset=utf8';
$database_user = '';
$database_password = '';
$table_prefix = 'modx_';
$site_id = 'maxnotify-build';
$uuid = '00000000-0000-0000-0000-000000000000';
$config_options = array(
    'core_path' => MODX_CORE_PATH,
    'base_path' => MODX_BASE_PATH,
    'assets_path' => MODX_ASSETS_PATH,
    'base_url' => MODX_BASE_URL,
    'assets_url' => MODX_ASSETS_URL,
    'cache_path' => MODX_CORE_PATH . 'cache/',
    'log_level' => modX::LOG_LEVEL_INFO,
    'log_target' => 'ECHO',
);
$driver_options = array();

return true;
