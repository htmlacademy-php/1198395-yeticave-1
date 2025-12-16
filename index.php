<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $isAuth ;
 * @var $userName ;
 * @var $getRecentLots ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 */

$lots = getRecentLots($connection);
$cats = getAllCats($connection);

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats
    ]
);

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
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - Главная страница',
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
