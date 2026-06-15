<?php

$_lang['maxnotify'] = 'MaxNotify';
$_lang['area_maxnotify_main'] = 'General';
$_lang['area_maxnotify_events'] = 'miniShop2 events';
$_lang['area_maxnotify_api'] = 'rumaxbot.ru API';
$_lang['area_maxnotify_max_business'] = 'Official MAX Business API';

$_lang['setting_maxnotify.enabled'] = 'Enable MaxNotify';
$_lang['setting_maxnotify.enabled_desc'] = 'Master switch for all notifications.';
$_lang['setting_maxnotify.provider'] = 'Delivery provider';
$_lang['setting_maxnotify.provider_desc'] = 'rumaxbot for rumaxbot.ru; maxbusiness for the official MAX platform API.';
$_lang['setting_maxnotify.api_url'] = 'API URL';
$_lang['setting_maxnotify.api_url_desc'] = 'rumaxbot.ru message endpoint.';
$_lang['setting_maxnotify.api_key'] = 'API key';
$_lang['setting_maxnotify.api_key_desc'] = 'Channel Bearer key from rumaxbot.ru.';
$_lang['setting_maxnotify.max_api_url'] = 'MAX Business API URL';
$_lang['setting_maxnotify.max_api_url_desc'] = 'Official MAX messages endpoint. Usually does not need to be changed.';
$_lang['setting_maxnotify.max_token'] = 'MAX bot token';
$_lang['setting_maxnotify.max_token_desc'] = 'Bot token from MAX for Partners → Chatbots → Advanced settings.';
$_lang['setting_maxnotify.max_recipient_type'] = 'MAX recipient type';
$_lang['setting_maxnotify.max_recipient_type_desc'] = 'chat_id for a chat or channel; user_id for a direct user message.';
$_lang['setting_maxnotify.max_recipient_ids'] = 'MAX recipient IDs';
$_lang['setting_maxnotify.max_recipient_ids_desc'] = 'One or more chat_id/user_id values separated by commas, spaces, or semicolons.';
$_lang['setting_maxnotify.max_notify'] = 'Notify participants';
$_lang['setting_maxnotify.max_notify_desc'] = 'When disabled, MAX sends the message without notifying chat participants.';
$_lang['setting_maxnotify.max_disable_link_preview'] = 'Disable link previews';
$_lang['setting_maxnotify.max_disable_link_preview_desc'] = 'Do not create a preview for the order link.';
$_lang['setting_maxnotify.format'] = 'Message format';
$_lang['setting_maxnotify.format_desc'] = 'Supported values are markdown and html.';
$_lang['setting_maxnotify.timeout'] = 'HTTP timeout';
$_lang['setting_maxnotify.timeout_desc'] = 'Maximum API request time in seconds.';
$_lang['setting_maxnotify.notify_new_order'] = 'Notify about new orders';
$_lang['setting_maxnotify.notify_new_order_desc'] = 'Send a message on msOnCreateOrder.';
$_lang['setting_maxnotify.notify_status_change'] = 'Notify about status changes';
$_lang['setting_maxnotify.notify_status_change_desc'] = 'Send a message on msOnChangeOrderStatus.';
$_lang['setting_maxnotify.statuses'] = 'Order statuses';
$_lang['setting_maxnotify.statuses_desc'] = 'Comma-separated status IDs. Empty allows every status.';
