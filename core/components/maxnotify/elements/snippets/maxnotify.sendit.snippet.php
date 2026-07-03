<?php
/** @var modX $modx */

if (!$modx->getOption('maxnotify.enabled', null, true)
    || !$modx->getOption('maxnotify.enable_sendit', null, true)
) {
    return true;
}

$corePath = $modx->getOption(
    'maxnotify.core_path',
    null,
    $modx->getOption('core_path') . 'components/maxnotify/'
);

/** @var MaxNotify $maxNotify */
$maxNotify = $modx->getService('maxnotify', 'MaxNotify', $corePath . 'model/maxnotify/');
if (!$maxNotify) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[MaxNotify] Could not load the MaxNotify service.');
    return true;
}

$fields = array();
foreach (array('fields', 'data', 'form', 'values') as $name) {
    if (isset(${$name}) && is_array(${$name})) {
        $fields = ${$name};
        break;
    }
}
if (!$fields && isset($hook) && is_object($hook) && method_exists($hook, 'getValues')) {
    $fields = (array) $hook->getValues();
}
if (!$fields && !empty($_POST)) {
    $fields = $_POST;
}

$title = $modx->getOption(
    'maxnotify.sendit_title',
    null,
    $modx->getOption('maxnotify.form_title', null, 'Новая заявка SendIt')
);
$maxNotify->notifyForm('SendIt', $fields, $title);

return true;
