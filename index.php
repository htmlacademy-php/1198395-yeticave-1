<?php
require_once __DIR__ . '/helpers.php';

/**
 * @var $includeTemplate;
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$isAuth = rand(0, 1);

$userName = 'Борис'; // укажите здесь ваше имя

$cats = [
    [
        'category' => 'Доски и лыжи',
        'cssClass' => 'promo__item--boards'
    ],
    [
        'category' => 'Крепления',
        'cssClass' => 'promo__item--attachment'
    ],
    [
        'category' => 'Ботинки',
        'cssClass' => 'promo__item--boots'
    ],
    [
        'category' => 'Одежда',
        'cssClass' => 'promo__item--clothing'
    ],
    [
        'category' => 'Инструменты',
        'cssClass' => 'promo__item--tools'
    ],
    [
        'category' => 'Разное',
        'cssClass' => 'promo__item--other'
    ]
];

$products = [
    [
        'name' => '2014 Rossignol District Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 10999,
        'imgUrl' => '/img/lot-1.jpg'
    ],
    [
        'name' => 'DC Ply Mens 2016/2017 Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 159999,
        'imgUrl' => '/img/lot-2.jpg'
    ],
    [
        'name' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'category' => 'Крепления',
        'price' => 8000,
        'imgUrl' => '/img/lot-3.jpg'
    ],
    [
        'name' => 'Ботинки для сноуборда DC Mutiny Charcoal',
        'category' => 'Ботинки',
        'price' => 10999,
        'imgUrl' => '/img/lot-4.jpg'
    ],
    [
        'name' => 'Куртка для сноуборда DC Mutiny Charcoal',
        'category' => 'Одежда',
        'price' => 7500,
        'imgUrl' => '/img/lot-5.jpg'
    ],
    [
        'name' => 'Маска Oakley Canopy',
        'category' => 'Разное',
        'price' => 5400,
        'imgUrl' => '/img/lot-6.jpg'
    ],
];
$pageContent = includeTemplate(
    'main.php',
    [
        'products' => $products,
        'cats' => $cats
    ]);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'cats' => $cats,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => 'Главная страница',
        'isAuth' => $isAuth]
);

print($layoutContent);
