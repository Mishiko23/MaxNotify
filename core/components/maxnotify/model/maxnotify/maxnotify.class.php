<?php

class MaxNotify
{
    const VERSION = '1.1.1';

    /** @var modX */
    public $modx;

    /** @var array */
    protected $config = array();

    public function __construct(modX &$modx, array $config = array())
    {
        $this->modx =& $modx;

        $corePath = $this->modx->getOption(
            'maxnotify.core_path',
            $config,
            $this->modx->getOption('core_path') . 'components/maxnotify/'
        );

        $this->config = array_merge(array(
            'corePath' => $corePath,
            'provider' => strtolower((string) $this->modx->getOption(
                'maxnotify.provider',
                null,
                'rumaxbot'
            )),
            'apiUrl' => $this->modx->getOption(
                'maxnotify.api_url',
                null,
                'https://rumaxbot.ru/api/v1/messages'
            ),
            'apiKey' => trim((string) $this->modx->getOption('maxnotify.api_key', null, '')),
            'maxApiUrl' => $this->modx->getOption(
                'maxnotify.max_api_url',
                null,
                'https://platform-api2.max.ru/messages'
            ),
            'maxToken' => trim((string) $this->modx->getOption('maxnotify.max_token', null, '')),
            'maxCaCertPath' => trim((string) $this->modx->getOption(
                'maxnotify.max_ca_cert_path',
                null,
                ''
            )),
            'maxRecipientType' => strtolower((string) $this->modx->getOption(
                'maxnotify.max_recipient_type',
                null,
                'chat_id'
            )),
            'maxRecipientIds' => trim((string) $this->modx->getOption(
                'maxnotify.max_recipient_ids',
                null,
                ''
            )),
            'maxNotify' => (bool) $this->modx->getOption('maxnotify.max_notify', null, true),
            'maxDisableLinkPreview' => (bool) $this->modx->getOption(
                'maxnotify.max_disable_link_preview',
                null,
                true
            ),
            'format' => strtolower((string) $this->modx->getOption('maxnotify.format', null, 'markdown')),
            'timeout' => max(1, (int) $this->modx->getOption('maxnotify.timeout', null, 10)),
        ), $config);
    }

    /**
     * Send a notification for a newly created order.
     *
     * @param msOrder $order
     * @return bool
     */
    public function notifyOrderCreated($order)
    {
        return $this->sendOrderMessage($order, 'maxNotifyOrderCreated');
    }

    /**
     * Send a notification after an order status changes.
     *
     * @param msOrder $order
     * @param int $statusId
     * @return bool
     */
    public function notifyOrderStatus($order, $statusId)
    {
        if (!$this->isStatusAllowed($statusId)) {
            return true;
        }

        return $this->sendOrderMessage($order, 'maxNotifyOrderStatus', $statusId);
    }

    /**
     * @param msOrder $order
     * @param string $chunkName
     * @param int|null $statusId
     * @return bool
     */
    protected function sendOrderMessage($order, $chunkName, $statusId = null)
    {
        if (!$order || !is_object($order) || !method_exists($order, 'get')) {
            $this->log(modX::LOG_LEVEL_ERROR, 'Order object was not provided.');
            return false;
        }

        if ($this->config['format'] === 'html') {
            $chunkName .= 'Html';
        }

        $placeholders = $this->getOrderPlaceholders($order, $statusId);
        $message = trim((string) $this->modx->getChunk($chunkName, $placeholders));

        if ($message === '') {
            $this->log(modX::LOG_LEVEL_ERROR, 'Message chunk is empty or missing: ' . $chunkName);
            return false;
        }

        return $this->send($message);
    }

    /**
     * @param msOrder $order
     * @param int|null $statusId
     * @return array
     */
    protected function getOrderPlaceholders($order, $statusId = null)
    {
        $address = method_exists($order, 'getOne') ? $order->getOne('Address') : null;
        $addressFields = array(
            'receiver', 'phone', 'email', 'country', 'index', 'region', 'city',
            'metro', 'street', 'building', 'entrance', 'floor', 'room', 'comment',
            'text_address',
        );

        $values = array(
            'id' => $order->get('id'),
            'num' => $order->get('num'),
            'cost' => $this->formatNumber($order->get('cost')),
            'cart_cost' => $this->formatNumber($order->get('cart_cost')),
            'delivery_cost' => $this->formatNumber($order->get('delivery_cost')),
            'weight' => $this->formatNumber($order->get('weight')),
            'createdon' => $order->get('createdon'),
            'user_id' => $order->get('user_id'),
            'delivery_id' => $order->get('delivery'),
            'payment_id' => $order->get('payment'),
            'status_id' => $statusId !== null ? (int) $statusId : (int) $order->get('status'),
            'order_comment' => $order->get('order_comment'),
            'manager_url' => $this->getManagerUrl($order->get('id')),
        );

        foreach ($addressFields as $field) {
            $values[$field] = $address ? $address->get($field) : '';
        }

        $values['status_name'] = $this->getStatusName($values['status_id']);
        $values['delivery_name'] = $this->getRelatedName('msDelivery', $values['delivery_id']);
        $values['payment_name'] = $this->getRelatedName('msPayment', $values['payment_id']);
        $values['address'] = $this->getAddressText($values);

        foreach ($values as $key => $value) {
            $values[$key] = $key === 'manager_url'
                ? $this->escapeUrl($value)
                : $this->escape($value);
        }

        $values['products'] = $this->getProductsText($order);

        return $values;
    }

    /**
     * @param msOrder $order
     * @return string
     */
    protected function getProductsText($order)
    {
        if (!method_exists($order, 'getMany')) {
            return '';
        }

        $lines = array();
        foreach ((array) $order->getMany('Products') as $product) {
            $name = trim((string) $product->get('name'));
            if ($name === '') {
                $name = '#' . $product->get('product_id');
            }

            $line = $this->escape($name)
                . ' x ' . $this->escape($this->formatNumber($product->get('count')))
                . ' = ' . $this->escape($this->formatNumber($product->get('cost')));

            $lines[] = $this->config['format'] === 'html'
                ? '<li>' . $line . '</li>'
                : '- ' . $line;
        }

        if ($this->config['format'] === 'html') {
            return $lines ? '<ul>' . implode('', $lines) . '</ul>' : '';
        }

        return implode("\n", $lines);
    }

    /**
     * @return string
     */
    protected function getManagerUrl($orderId)
    {
        $managerUrl = (string) $this->modx->getOption('manager_url');
        if (!preg_match('#^https?://#i', $managerUrl)) {
            $managerUrl = rtrim((string) $this->modx->getOption('site_url'), '/')
                . '/' . ltrim($managerUrl, '/');
        }

        return rtrim($managerUrl, '/') . '/?a=mgr/orders&namespace=minishop2&order=' . (int) $orderId;
    }

    /**
     * @param string $class
     * @param int $id
     * @return string
     */
    protected function getRelatedName($class, $id)
    {
        $object = $this->modx->getObject($class, (int) $id);
        return $object ? (string) $object->get('name') : '';
    }

    /**
     * @param array $values
     * @return string
     */
    protected function getAddressText(array $values)
    {
        if (!empty($values['text_address'])) {
            return (string) $values['text_address'];
        }

        $parts = array();
        foreach (array('index', 'region', 'city', 'street', 'building') as $field) {
            if (!empty($values[$field])) {
                $parts[] = (string) $values[$field];
            }
        }

        foreach (array(
            'entrance' => 'подъезд',
            'floor' => 'этаж',
            'room' => 'кв./офис',
        ) as $field => $label) {
            if (!empty($values[$field])) {
                $parts[] = $label . ' ' . $values[$field];
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @param int $statusId
     * @return string
     */
    protected function getStatusName($statusId)
    {
        $status = $this->modx->getObject('msOrderStatus', (int) $statusId);
        return $status ? (string) $status->get('name') : (string) $statusId;
    }

    /**
     * @param int $statusId
     * @return bool
     */
    protected function isStatusAllowed($statusId)
    {
        $configured = trim((string) $this->modx->getOption('maxnotify.statuses', null, ''));
        if ($configured === '') {
            return true;
        }

        $statuses = array_filter(array_map('trim', explode(',', $configured)), 'strlen');
        $statuses = array_map('intval', $statuses);

        return in_array((int) $statusId, $statuses, true);
    }

    /**
     * @param string $message
     * @return bool
     */
    public function send($message)
    {
        $format = in_array($this->config['format'], array('markdown', 'html'), true)
            ? $this->config['format']
            : 'markdown';

        if (in_array($this->config['provider'], array('maxbusiness', 'max', 'official'), true)) {
            return $this->sendMaxBusiness((string) $message, $format);
        }

        return $this->sendRumaxbot((string) $message, $format);
    }

    /**
     * @param string $message
     * @param string $format
     * @return bool
     */
    protected function sendRumaxbot($message, $format)
    {
        if ($this->config['apiKey'] === '') {
            $this->log(modX::LOG_LEVEL_ERROR, 'System setting maxnotify.api_key is empty.');
            return false;
        }

        $payload = $this->encodePayload(array(
            'text' => (string) $message,
            'format' => $format,
        ));

        if ($payload === false) {
            return false;
        }

        return $this->sendRequest(
            $this->config['apiUrl'],
            $payload,
            'Bearer ' . $this->config['apiKey'],
            'rumaxbot.ru'
        );
    }

    /**
     * @param string $message
     * @param string $format
     * @return bool
     */
    protected function sendMaxBusiness($message, $format)
    {
        if ($this->config['maxToken'] === '') {
            $this->log(modX::LOG_LEVEL_ERROR, 'System setting maxnotify.max_token is empty.');
            return false;
        }
        if ($this->config['maxCaCertPath'] !== '' && !is_file($this->config['maxCaCertPath'])) {
            $this->log(
                modX::LOG_LEVEL_ERROR,
                'System setting maxnotify.max_ca_cert_path points to a missing certificate file.'
            );
            return false;
        }

        $recipientType = in_array($this->config['maxRecipientType'], array('chat_id', 'user_id'), true)
            ? $this->config['maxRecipientType']
            : 'chat_id';
        $recipientIds = preg_split('/[\s,;]+/', $this->config['maxRecipientIds'], -1, PREG_SPLIT_NO_EMPTY);
        $recipientIds = array_values(array_filter($recipientIds, function ($id) {
            return preg_match('/^-?\d+$/', $id);
        }));

        if (!$recipientIds) {
            $this->log(
                modX::LOG_LEVEL_ERROR,
                'System setting maxnotify.max_recipient_ids does not contain a valid recipient ID.'
            );
            return false;
        }

        $payload = $this->encodePayload(array(
            'text' => $this->limitText($message, 4000),
            'format' => $format,
            'notify' => $this->config['maxNotify'],
        ));

        if ($payload === false) {
            return false;
        }

        $success = true;
        foreach ($recipientIds as $recipientId) {
            $query = array(
                $recipientType => $recipientId,
                'disable_link_preview' => $this->config['maxDisableLinkPreview'] ? 'true' : 'false',
            );
            $separator = strpos($this->config['maxApiUrl'], '?') === false ? '?' : '&';
            $url = $this->config['maxApiUrl'] . $separator . http_build_query($query);

            if (!$this->sendRequest(
                $url,
                $payload,
                $this->config['maxToken'],
                'MAX Business',
                $this->config['maxCaCertPath']
            )) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * @param array $data
     * @return string|false
     */
    protected function encodePayload(array $data)
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            $this->log(modX::LOG_LEVEL_ERROR, 'Could not encode the API request as JSON.');
            return false;
        }

        return $payload;
    }

    /**
     * @param string $url
     * @param string $payload
     * @param string $authorization
     * @param string $service
     * @param string $caCertPath
     * @return bool
     */
    protected function sendRequest($url, $payload, $authorization, $service, $caCertPath = '')
    {
        if (function_exists('curl_init')) {
            return $this->sendWithCurl($url, $payload, $authorization, $service, $caCertPath);
        }

        return $this->sendWithStreams($url, $payload, $authorization, $service, $caCertPath);
    }

    /**
     * @param string $url
     * @param string $payload
     * @param string $authorization
     * @param string $service
     * @param string $caCertPath
     * @return bool
     */
    protected function sendWithCurl($url, $payload, $authorization, $service, $caCertPath = '')
    {
        $handle = curl_init($url);
        $options = array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->config['timeout'],
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $authorization,
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: MaxNotify/' . self::VERSION,
            ),
            CURLOPT_POSTFIELDS => $payload,
        );
        if ($caCertPath !== '') {
            $options[CURLOPT_CAINFO] = $caCertPath;
        }
        curl_setopt_array($handle, $options);

        $response = curl_exec($handle);
        $error = curl_error($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        if ($response === false) {
            $this->log(modX::LOG_LEVEL_ERROR, $service . ' transport error: ' . $error);
            return false;
        }

        return $this->validateResponse($status, $response, $service);
    }

    /**
     * @param string $url
     * @param string $payload
     * @param string $authorization
     * @param string $service
     * @param string $caCertPath
     * @return bool
     */
    protected function sendWithStreams($url, $payload, $authorization, $service, $caCertPath = '')
    {
        $contextOptions = array(
            'http' => array(
                'method' => 'POST',
                'timeout' => $this->config['timeout'],
                'ignore_errors' => true,
                'header' => implode("\r\n", array(
                    'Authorization: ' . $authorization,
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'User-Agent: MaxNotify/' . self::VERSION,
                )),
                'content' => $payload,
            ),
        );
        if ($caCertPath !== '') {
            $contextOptions['ssl'] = array(
                'cafile' => $caCertPath,
                'verify_peer' => true,
                'verify_peer_name' => true,
            );
        }
        $context = stream_context_create($contextOptions);

        $response = @file_get_contents($url, false, $context);
        $headers = isset($http_response_header) ? $http_response_header : array();
        $status = $this->getStatusFromHeaders($headers);

        if ($response === false && $status === 0) {
            $this->log(
                modX::LOG_LEVEL_ERROR,
                $service . ' transport error. Enable cURL or allow_url_fopen and verify outbound HTTPS access.'
            );
            return false;
        }

        return $this->validateResponse($status, (string) $response, $service);
    }

    /**
     * @param array $headers
     * @return int
     */
    protected function getStatusFromHeaders(array $headers)
    {
        $status = 0;
        foreach ($headers as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#i', $header, $matches)) {
                $status = (int) $matches[1];
            }
        }

        return $status;
    }

    /**
     * @param int $status
     * @param string $response
     * @param string $service
     * @return bool
     */
    protected function validateResponse($status, $response, $service)
    {
        if ($status >= 200 && $status < 300) {
            return true;
        }

        $body = trim(strip_tags((string) $response));
        if (strlen($body) > 500) {
            $body = substr($body, 0, 500) . '...';
        }

        $this->log(
            modX::LOG_LEVEL_ERROR,
            $service . ' returned HTTP ' . $status . ($body !== '' ? ': ' . $body : '')
        );

        return false;
    }

    /**
     * @param string $text
     * @param int $limit
     * @return string
     */
    protected function limitText($text, $limit)
    {
        $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length <= $limit) {
            return $text;
        }

        $suffix = "\n...";
        $sliceLength = $limit - strlen($suffix);
        $text = function_exists('mb_substr')
            ? mb_substr($text, 0, $sliceLength, 'UTF-8')
            : substr($text, 0, $sliceLength);

        return rtrim($text) . $suffix;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function escape($value)
    {
        $value = (string) $value;

        if ($this->config['format'] === 'html') {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return preg_replace('/([\\\\`*_{}\[\]()#+.!>|~-])/', '\\\\$1', $value);
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function escapeUrl($value)
    {
        $value = (string) $value;
        return $this->config['format'] === 'html'
            ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            : $value;
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function formatNumber($value)
    {
        $number = (float) $value;
        return number_format($number, $number == floor($number) ? 0 : 2, '.', ' ');
    }

    /**
     * @param int $level
     * @param string $message
     */
    protected function log($level, $message)
    {
        $this->modx->log($level, '[MaxNotify] ' . $message);
    }
}
