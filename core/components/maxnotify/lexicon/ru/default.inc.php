<?php

$_lang['maxnotify'] = 'MaxNotify';
$_lang['area_maxnotify_main'] = 'Основные настройки';
$_lang['area_maxnotify_events'] = 'События miniShop2';
$_lang['area_maxnotify_api'] = 'API rumaxbot.ru';
$_lang['area_maxnotify_max_business'] = 'Официальный MAX Business API';

$_lang['setting_maxnotify.enabled'] = 'Включить MaxNotify';
$_lang['setting_maxnotify.enabled_desc'] = 'Главный переключатель отправки уведомлений.';
$_lang['setting_maxnotify.provider'] = 'Провайдер отправки';
$_lang['setting_maxnotify.provider_desc'] = 'rumaxbot — сервис rumaxbot.ru; maxbusiness — официальный API платформы MAX.';
$_lang['setting_maxnotify.api_url'] = 'URL API';
$_lang['setting_maxnotify.api_url_desc'] = 'Endpoint отправки сообщений rumaxbot.ru.';
$_lang['setting_maxnotify.api_key'] = 'API-ключ';
$_lang['setting_maxnotify.api_key_desc'] = 'Bearer-ключ канала из личного кабинета rumaxbot.ru.';
$_lang['setting_maxnotify.max_api_url'] = 'URL MAX Business API';
$_lang['setting_maxnotify.max_api_url_desc'] = 'Официальный endpoint отправки сообщений MAX. Обычно изменять не требуется.';
$_lang['setting_maxnotify.max_token'] = 'Токен MAX-бота';
$_lang['setting_maxnotify.max_token_desc'] = 'Токен бота из MAX для партнёров → Чат-боты → Расширенные настройки.';
$_lang['setting_maxnotify.max_recipient_type'] = 'Тип получателя MAX';
$_lang['setting_maxnotify.max_recipient_type_desc'] = 'chat_id для чата или канала; user_id для личного сообщения пользователю.';
$_lang['setting_maxnotify.max_recipient_ids'] = 'ID получателей MAX';
$_lang['setting_maxnotify.max_recipient_ids_desc'] = 'Один или несколько chat_id/user_id через запятую, пробел или точку с запятой.';
$_lang['setting_maxnotify.max_notify'] = 'Уведомлять участников';
$_lang['setting_maxnotify.max_notify_desc'] = 'Если выключено, MAX отправит сообщение без уведомления участников чата.';
$_lang['setting_maxnotify.max_disable_link_preview'] = 'Отключить превью ссылок';
$_lang['setting_maxnotify.max_disable_link_preview_desc'] = 'Не создавать превью ссылки на заказ в сообщении.';
$_lang['setting_maxnotify.format'] = 'Формат сообщения';
$_lang['setting_maxnotify.format_desc'] = 'Поддерживаются markdown и html.';
$_lang['setting_maxnotify.timeout'] = 'Таймаут HTTP';
$_lang['setting_maxnotify.timeout_desc'] = 'Максимальное время запроса к API в секундах.';
$_lang['setting_maxnotify.notify_new_order'] = 'Уведомлять о новом заказе';
$_lang['setting_maxnotify.notify_new_order_desc'] = 'Отправлять сообщение по событию msOnCreateOrder.';
$_lang['setting_maxnotify.notify_status_change'] = 'Уведомлять о смене статуса';
$_lang['setting_maxnotify.notify_status_change_desc'] = 'Отправлять сообщение по событию msOnChangeOrderStatus.';
$_lang['setting_maxnotify.statuses'] = 'Статусы заказа';
$_lang['setting_maxnotify.statuses_desc'] = 'ID статусов через запятую. Пустое значение разрешает все статусы.';
