<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/functions/templates.php';
require_once __DIR__ . '/functions/db_fetching.php';
require_once __DIR__ . '/functions/form_validators.php';

$isAuth = rand(0, 1);

$userName = 'Борис'; // укажите здесь ваше имя

if (!file_exists(__DIR__ . '/config.php')) {
    exit('Файл конфигурации отсутствует.');
}
$config = require __DIR__ . '/config.php';

$connection = createConnection($config['db']);
