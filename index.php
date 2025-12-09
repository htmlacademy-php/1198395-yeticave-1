<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/helpers.php';

/**
 * @var $includeTemplate ;
 */

$connection = mysqli_connect('mysql', 'root', '', 'yeticave');
if (!$connection) {
    printf('Ошибка подключения к БД: %s', mysqli_connect_error());
    die();
}

if (!mysqli_set_charset($connection, 'utf8mb4')) {
    printf('Ошибка установки Юникода : %s', mysqli_connect_error());
    die();
};

$lotsSql = 'SELECT lots.*, cats.name AS category '
        . 'FROM lots JOIN cats ON lots.cat_id = cats.id '
        . 'ORDER BY lots.created_at DESC LIMIT 6';

$lotsResult = mysqli_query($connection, $lotsSql);
$lots = mysqli_fetch_all($lotsResult, MYSQLI_ASSOC);

$catsSql = 'SELECT * FROM cats';

$catsResult = mysqli_query($connection, $catsSql);
$cats = mysqli_fetch_all($catsResult, MYSQLI_ASSOC);

$isAuth = rand(0, 1);

$userName = 'Борис'; // укажите здесь ваше имя

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
