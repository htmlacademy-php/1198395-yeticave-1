<?php

require_once __DIR__ . '/init.php';

/**
 * @var $includeTemplate ;
 * @var $isAuth ;
 * @var $userName ;
 * @var $createConnection ;
 * @var $setUnicode ;
 * @var $getData ;
 * @var $queryGetRecentLots ;
 * @var $queryGetCategories ;
 * @var $config ;
 */

$connection = createConnection($config);
setUnicode($connection);
$lots = getData($connection, $queryGetRecentLots);
$cats = getData($connection, $queryGetCategories);

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
        'pageTitle' => 'Главная страница',
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
