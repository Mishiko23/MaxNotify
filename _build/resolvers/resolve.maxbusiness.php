<?php

if (!isset($object) || !is_object($object) || !isset($object->xpdo)) {
    return true;
}

/** @var modX $modx */
$modx =& $object->xpdo;

$oldUrl = 'https://platform-api.max.ru/messages';
$newUrl = 'https://platform-api2.max.ru/messages';

/** @var modSystemSetting $setting */
$setting = $modx->getObject('modSystemSetting', array('key' => 'maxnotify.max_api_url'));
if ($setting && trim((string) $setting->get('value')) === $oldUrl) {
    $setting->set('value', $newUrl);
    $setting->save();
}

return true;
