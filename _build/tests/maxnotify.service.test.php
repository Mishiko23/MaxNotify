<?php

class modX
{
    const LOG_LEVEL_ERROR = 1;

    public $logs = array();

    public function getOption($key, $options = null, $default = null)
    {
        return $default;
    }

    public function log($level, $message)
    {
        $this->logs[] = array($level, $message);
    }
}

require_once dirname(dirname(__DIR__))
    . '/core/components/maxnotify/model/maxnotify/maxnotify.class.php';

class TestMaxNotify extends MaxNotify
{
    public $requests = array();

    protected function sendRequest($url, $payload, $authorization, $service, $caCertPath = '')
    {
        $this->requests[] = compact('url', 'payload', 'authorization', 'service', 'caCertPath');
        return true;
    }
}

function assertTrue($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$modx = new modX();
$official = new TestMaxNotify($modx, array(
    'provider' => 'maxbusiness',
    'maxApiUrl' => 'https://platform-api2.max.ru/messages',
    'maxToken' => 'official-token',
    'maxCaCertPath' => __FILE__,
    'maxRecipientType' => 'chat_id',
    'maxRecipientIds' => '123, 456',
    'maxNotify' => true,
    'maxDisableLinkPreview' => true,
    'format' => 'markdown',
));

assertTrue($official->send(str_repeat('Я', 4100)), 'MAX Business send should succeed.');
assertTrue(count($official->requests) === 2, 'Two recipient IDs should produce two requests.');
assertTrue(
    $official->requests[0]['authorization'] === 'official-token',
    'Official MAX token must not use the Bearer prefix.'
);
assertTrue(
    $official->requests[0]['caCertPath'] === __FILE__,
    'Official MAX certificate path must be passed to the transport layer.'
);
assertTrue(
    strpos($official->requests[0]['url'], 'chat_id=123') !== false,
    'Official MAX request must contain chat_id.'
);
assertTrue(
    strpos($official->requests[1]['url'], 'chat_id=456') !== false,
    'Second official MAX recipient must be used.'
);

$officialPayload = json_decode($official->requests[0]['payload'], true);
assertTrue($officialPayload['format'] === 'markdown', 'Message format must be passed to MAX.');
assertTrue($officialPayload['notify'] === true, 'Notify flag must be passed to MAX.');
assertTrue(mb_strlen($officialPayload['text'], 'UTF-8') <= 4000, 'MAX text limit must be enforced.');

$rumaxbot = new TestMaxNotify($modx, array(
    'provider' => 'rumaxbot',
    'apiUrl' => 'https://rumaxbot.ru/api/v1/messages',
    'apiKey' => 'rumax-key',
    'format' => 'html',
));

assertTrue($rumaxbot->send('<b>Order</b>'), 'rumaxbot send should succeed.');
assertTrue(count($rumaxbot->requests) === 1, 'rumaxbot should produce one request.');
assertTrue(
    $rumaxbot->requests[0]['authorization'] === 'Bearer rumax-key',
    'rumaxbot must keep the Bearer authorization scheme.'
);

echo "MaxNotify service tests passed.\n";
