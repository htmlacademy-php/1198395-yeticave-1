<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getRecentLots ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 */

$lots = getRecentLots($connection);
$cats = getAllCats($connection);
$user = getAuthUser($connection);

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'main.php',
    [
        'lots' => $lots,
        'cats' => $cats,
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Главная страница',
        'user' => $user,
    ],
);

print($layoutContent);
