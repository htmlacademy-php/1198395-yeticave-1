<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getBidsByLotId ;
 * @var $getAuthUser ;
 * @var $search ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

$text = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS);
$text = $text ? trim($text) : '';

$catId = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT);
$isCatValid = false;
$catName = null;

foreach ($cats as $cat) {
    if ((int)$cat['id'] === (int)$catId) {
        $isCatValid = true;
        $catName = $cat['name'];
    }
}

if (empty($text) && !$isCatValid) {
    header('Location:/');
    exit();
}

$lotsPerPage = 9;

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if (!$page || $page < 1 || $page > $pages) {
    $page = 1;
}

$searchInfo = search($connection, $text, $catId, $lotsPerPage, $page);

$pages = $searchInfo['pages'];
$lots = $searchInfo['lots'];

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
        'catName' => $catName,
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
