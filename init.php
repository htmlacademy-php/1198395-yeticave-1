<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/functions/templates.php';
require_once __DIR__ . '/functions/db_fetching.php';
require_once __DIR__ . '/functions/form_validators.php';
require_once __DIR__ . '/functions/files.php';

if (!file_exists(__DIR__ . '/config.php')) {
    exit('Файл конфигурации отсутствует.');
}
$config = require __DIR__ . '/config.php';

if (!isset($config['db'])) {
    exit('Ошибка конфигурации db: отсутствует необходимый ключ.');
}

$connection = createConnection($config['db']);
