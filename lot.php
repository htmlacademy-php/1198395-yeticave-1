<?php

require_once __DIR__ . '/init.php';

/**
 * @var $createConnection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 */

$isAuth = rand(0, 1);

$userName = 'Борис'; // укажите здесь ваше имя

if (!file_exists(__DIR__ . '/config.php')) {
    exit('Файл конфигурации отсутствует.');
}
$config = require __DIR__ . '/config.php';

$connection = createConnection($config['db']);
$lotId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$lot = getLotById($connection, $lotId);
$cats = getAllCats($connection);
var_dump($lot);
$pageContent = includeTemplate(
    'lot.php',
    [
        'lot' => $lot
    ]
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'cats' => $cats,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - ' . $lot['name'],
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
