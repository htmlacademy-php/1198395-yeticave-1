<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getBidsByLotId ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

$text = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
$text = $text ? trim($text) : '';

if (empty($text)) {
    header('Location:/');
    exit();
}

$lotsPerPage = 9;
$lotsAmount = getLotsAmount($connection, $text);
$pages = (int)ceil($lotsAmount / $lotsPerPage);

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if (!$page || $page < 1 || $page > $pages) {
    $page = 1;
}

$lots = search($connection, $text, $page, $lotsPerPage);

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'search.php',
    [
        'navContent' => $navContent,
        'text' => $text,
        'lots' => $lots,
        'pages' => $pages,
        'page' => $page,
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Поиск.',
        'user' => $user,
        'search' => $text,
    ],
);

print($layoutContent);
