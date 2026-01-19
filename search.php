<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getAuthUser ;
 * @var $search ;
 * @var $validateSearch ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

$text = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? false;
$catId = filter_input(INPUT_GET, 'cat', FILTER_VALIDATE_INT) ?? false;

$searchInfo = validateSearch($text, $catId, $cats);

if (!$searchInfo['isTextValid'] && !$searchInfo['isCatValid']) {
    header('Location:/');
    exit();
}

$lotsPerPage = 9;

$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? false;

$searchResult = search($connection, $searchInfo, $lotsPerPage, $page);

$pages = $searchResult['pages'];
$lots = $searchResult['lots'];

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
        'lots' => $lots,
        'pages' => $pages,
        'page' => $page,
        'searchInfo' => $searchInfo,
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Поиск.',
        'user' => $user,
        'search' => $searchInfo['text'],
    ],
);

print($layoutContent);
