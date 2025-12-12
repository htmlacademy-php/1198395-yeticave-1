<?php

require_once __DIR__ . '/init.php';

/**
 * @var $createConnection ;
 * @var $getRecentLots ;
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
$lots = getRecentLots($connection);
$cats = getAllCats($connection);

$pageContent = includeTemplate(
    'main.php',
    [
        'lots' => $lots,
        'cats' => $cats
    ]
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'cats' => $cats,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - Главная страница',
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
