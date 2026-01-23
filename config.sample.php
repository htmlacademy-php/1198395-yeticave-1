<?php

return [
    'db' =>
    [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'yeticave',
    ],
    'mailer' => // Настройки SMTP сервера.
    [
    'login' => '',
    'password' => '',
    'host' => '',
    'port' => '',
    'email' => '',
    'properties' => '', // Свойства, разделенные '&'. Пример: `encryption=ssl&auth_mode=login`
    'url' => '', // Ссылка на главную страницу сайта (нужна для вставки в письмо для победителя)
    ],
];
