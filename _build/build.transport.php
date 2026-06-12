<?php

require_once __DIR__ . '/build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

class MaxNotifyBuildModX extends modX
{
    /** @var bool */
    public $suppressEvents = true;

    public function invokeEvent($eventName, array $params = array())
    {
        if ($this->suppressEvents) {
            return false;
        }

        return parent::invokeEvent($eventName, $params);
    }
}

$modx = new MaxNotifyBuildModX();
$modx->initialize('mgr');
$modx->suppressEvents = false;
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx->loadClass('transport.modPackageBuilder', '', false, true);

class MaxNotifyPackageBuilder extends modPackageBuilder
{
    public function __construct(modX &$modx)
    {
        $this->modx =& $modx;
        $this->modx->loadClass('transport.modTransportVehicle', '', false, true);
        $this->modx->loadClass('transport.xPDOTransport', XPDO_CORE_PATH, true, true);
        $this->directory = MODX_CORE_PATH . 'packages/';
        $this->autoselects = array();
    }
}

$builder = new MaxNotifyPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER, PKG_VERSION, PKG_RELEASE);

$namespace = $modx->newObject('modNamespace');
$namespace->fromArray(array(
    'name' => PKG_NAME_LOWER,
    'path' => '{core_path}components/' . PKG_NAME_LOWER . '/',
), '', true, true);
$builder->registerNamespace($namespace, false, true);

$category = $modx->newObject('modCategory');
$category->set('category', PKG_NAME);

$chunks = array(
    'maxNotifyOrderCreated' => 'maxnotify.order_created.chunk.tpl',
    'maxNotifyOrderCreatedHtml' => 'maxnotify.order_created_html.chunk.tpl',
    'maxNotifyOrderStatus' => 'maxnotify.order_status.chunk.tpl',
    'maxNotifyOrderStatusHtml' => 'maxnotify.order_status_html.chunk.tpl',
);

$chunkObjects = array();
foreach ($chunks as $name => $file) {
    $chunk = $modx->newObject('modChunk');
    $chunk->fromArray(array(
        'name' => $name,
        'description' => 'Шаблон уведомления MaxNotify для MAX',
        'snippet' => file_get_contents(PKG_CORE . 'elements/chunks/' . $file),
    ), '', true, true);
    $chunkObjects[] = $chunk;
}
$category->addMany($chunkObjects);

$pluginCode = file_get_contents(PKG_CORE . 'elements/plugins/maxnotify.plugin.php');
$pluginCode = preg_replace('/^<\?php\s*/', '', $pluginCode);

$plugin = $modx->newObject('modPlugin');
$plugin->fromArray(array(
    'name' => PKG_NAME,
    'description' => 'Уведомления о заказах miniShop2 в мессенджер MAX через rumaxbot.ru',
    'plugincode' => $pluginCode,
), '', true, true);

$pluginEvents = array();
foreach (array('msOnCreateOrder', 'msOnChangeOrderStatus') as $eventName) {
    $event = $modx->newObject('modPluginEvent');
    $event->fromArray(array(
        'event' => $eventName,
        'priority' => 0,
        'propertyset' => 0,
    ), '', true, true);
    $pluginEvents[] = $event;
}
$plugin->addMany($pluginEvents);
$pluginObjects = array($plugin);
$category->addMany($pluginObjects);

$vehicle = $builder->createVehicle($category, array(
    xPDOTransport::UNIQUE_KEY => 'category',
    xPDOTransport::PRESERVE_KEYS => false,
    xPDOTransport::UPDATE_OBJECT => true,
    xPDOTransport::RELATED_OBJECTS => true,
    xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
        'Chunks' => array(
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => false,
        ),
        'Plugins' => array(
            xPDOTransport::UNIQUE_KEY => 'name',
            xPDOTransport::PRESERVE_KEYS => false,
            xPDOTransport::UPDATE_OBJECT => true,
            xPDOTransport::RELATED_OBJECTS => true,
            xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array(
                'PluginEvents' => array(
                    xPDOTransport::UNIQUE_KEY => array('pluginid', 'event'),
                    xPDOTransport::PRESERVE_KEYS => true,
                    xPDOTransport::UPDATE_OBJECT => true,
                ),
            ),
        ),
    ),
));

$vehicle->resolve('file', array(
    'source' => PKG_CORE,
    'target' => "return MODX_CORE_PATH . 'components/';",
));
$builder->putVehicle($vehicle);

$settings = array(
    'enabled' => array('value' => true, 'xtype' => 'combo-boolean', 'area' => 'maxnotify_main'),
    'api_url' => array('value' => 'https://rumaxbot.ru/api/v1/messages', 'xtype' => 'textfield', 'area' => 'maxnotify_api'),
    'api_key' => array('value' => '', 'xtype' => 'textfield', 'area' => 'maxnotify_api'),
    'format' => array('value' => 'markdown', 'xtype' => 'textfield', 'area' => 'maxnotify_api'),
    'timeout' => array('value' => 10, 'xtype' => 'numberfield', 'area' => 'maxnotify_api'),
    'notify_new_order' => array('value' => true, 'xtype' => 'combo-boolean', 'area' => 'maxnotify_events'),
    'notify_status_change' => array('value' => false, 'xtype' => 'combo-boolean', 'area' => 'maxnotify_events'),
    'statuses' => array('value' => '', 'xtype' => 'textfield', 'area' => 'maxnotify_events'),
);

foreach ($settings as $key => $data) {
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array(
        'key' => PKG_NAME_LOWER . '.' . $key,
        'value' => $data['value'],
        'xtype' => $data['xtype'],
        'namespace' => PKG_NAME_LOWER,
        'area' => $data['area'],
    ), '', true, true);

    $settingVehicle = $builder->createVehicle($setting, array(
        xPDOTransport::UNIQUE_KEY => 'key',
        xPDOTransport::PRESERVE_KEYS => true,
        xPDOTransport::UPDATE_OBJECT => false,
    ));
    $builder->putVehicle($settingVehicle);
}

$builder->setPackageAttributes(array(
    'name' => PKG_NAME,
    'author' => 'Mishiko23',
    'email' => 'bigo2008@gmail.com',
    'description' => 'Уведомления о новых заказах и смене статусов miniShop2 в мессенджер MAX через API rumaxbot.ru.',
    'license' => file_get_contents(PKG_ROOT . 'LICENSE'),
    'readme' => file_get_contents(PKG_ROOT . 'README.md'),
    'changelog' => file_get_contents(PKG_ROOT . 'CHANGELOG.md'),
));

$builder->pack();
