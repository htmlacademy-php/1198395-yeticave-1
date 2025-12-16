<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $isAuth ;
 * @var $userName ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getBidsByLotId ;
 */

$cats = getAllCats($connection);
$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$pageTitle = '';

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats
    ]
);

if (!$lotId || !$lot = getLotById($connection, $lotId)) {
    $pageTitle = 'Страницы не существует';

    $pageContent = includeTemplate(
        '404.php',
        [
            'navContent' => $navContent
        ]
    );
    http_response_code(404);
} else {
    $bids = getBidsByLot($connection, $lotId);
    $pageTitle = $lot['name'];

    $pageContent = includeTemplate(
        'lot.php',
        [
            'lot' => $lot,
            'bids' => $bids,
            'navContent' => $navContent
        ]
    );
}

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - ' . $pageTitle,
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
