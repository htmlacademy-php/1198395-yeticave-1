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
            'properties' => '',
            // Свойства, разделенные '&'. Пример: `encryption=ssl&auth_mode=login`
            'url' => '',
            // Ссылка на главную страницу сайта типа "http://localhost:8080/" (нужна для вставки в письмо для победителя)
        ],
    'pagination' =>
        [
            'lots_per_page' => 9,
        ]
];
