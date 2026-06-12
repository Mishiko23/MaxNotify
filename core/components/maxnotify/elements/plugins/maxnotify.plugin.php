<?php
/** @var modX $modx */

if (!$modx->getOption('maxnotify.enabled', null, true)) {
    return;
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
    return;
}

switch ($modx->event->name) {
    case 'msOnCreateOrder':
        if ($modx->getOption('maxnotify.notify_new_order', null, true) && isset($msOrder)) {
            $maxNotify->notifyOrderCreated($msOrder);
        }
        break;

    case 'msOnChangeOrderStatus':
        if ($modx->getOption('maxnotify.notify_status_change', null, false) && isset($order, $status)) {
            $maxNotify->notifyOrderStatus($order, $status);
        }
        break;
}
