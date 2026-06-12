# MaxNotify для miniShop2

**MaxNotify** — компонент для MODX Revolution 2, который отправляет сведения
о заказах miniShop2 в мессенджер MAX через сервис
[rumaxbot.ru](https://rumaxbot.ru).

Компонент помогает владельцу и менеджерам интернет-магазина быстро узнавать
о новых заказах и изменениях их статуса без постоянной проверки панели MODX.

## Автор

- Разработчик: **Mishiko23**
- Email: **bigo2008@gmail.com**

## Возможности

- уведомление сразу после создания заказа miniShop2;
- уведомление при изменении статуса заказа;
- фильтрация уведомлений по ID статусов;
- номер, сумма и состав заказа;
- имя, телефон и email покупателя;
- адрес доставки и комментарий клиента;
- название способа доставки и оплаты;
- ссылка на конкретный заказ в панели MODX;
- сообщения в формате Markdown или HTML;
- редактируемые чанки сообщений;
- запись ошибок API и соединения в журнал MODX.

## Требования

- MODX Revolution 2.8+;
- miniShop2 2.x или 4.x;
- PHP 7.2+;
- PHP cURL или включённый `allow_url_fopen`;
- канал и API-ключ сервиса rumaxbot.ru.

Компонент проверен с MODX Revolution 2.8.8-pl и miniShop2 4.4.2-pl.

## Установка

1. Откройте в MODX раздел **Пакеты → Установщик**.
2. Найдите компонент **MaxNotify** в репозитории
   [modstore.pro](https://modstore.pro).
3. Нажмите **Скачать**, затем **Установить**.
4. После установки очистите кэш MODX.

## Настройка

Откройте **Системные настройки** и выберите пространство имён `maxnotify`.

Основные параметры:

- `maxnotify.enabled` — включает или отключает компонент;
- `maxnotify.api_key` — API-ключ канала rumaxbot.ru;
- `maxnotify.api_url` — адрес API отправки сообщений;
- `maxnotify.notify_new_order` — уведомления о новых заказах;
- `maxnotify.notify_status_change` — уведомления о смене статуса;
- `maxnotify.statuses` — ID статусов через запятую, пустое поле разрешает все;
- `maxnotify.format` — формат `markdown` или `html`;
- `maxnotify.timeout` — таймаут API-запроса в секундах.

## Подключение rumaxbot.ru

1. Зарегистрируйтесь на rumaxbot.ru и подтвердите email.
2. Создайте канал.
3. Подключите MAX-бота к каналу по инструкции сервиса.
4. Создайте API-ключ канала.
5. Укажите ключ в настройке `maxnotify.api_key`.

API-ключ нельзя публиковать или добавлять в репозиторий.

## Шаблоны сообщений

После установки в категории элементов `MaxNotify` будут созданы чанки:

- `maxNotifyOrderCreated` — новый заказ в Markdown;
- `maxNotifyOrderStatus` — новый статус в Markdown;
- `maxNotifyOrderCreatedHtml` — новый заказ в HTML;
- `maxNotifyOrderStatusHtml` — новый статус в HTML.

Доступные плейсхолдеры: `num`, `cost`, `receiver`, `phone`, `email`,
`address`, `comment`, `order_comment`, `products`, `delivery_name`,
`payment_name`, `status_name`, `manager_url` и другие поля заказа.
