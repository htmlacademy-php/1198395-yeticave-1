<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getBidsByLotId ;
 */

$cats = getAllCats($connection);
$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$pageTitle = '';
$templateName = 'lot.php';
$pageData = [];

if (!$lotId || !$lot = getLotById($connection, $lotId)) {
    $pageTitle = 'Страницы не существует';

    $templateName = '404.php';
    http_response_code(404);
} else {
    $bids = getBidsByLot($connection, $lotId);
    $pageTitle = $lot['name'];
    $pageData +=
        [
            'lot' => $lot,
            'bids' => $bids
        ];
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats
    ]
);

$pageData['navContent'] = $navContent;

$pageContent = includeTemplate(
    $templateName,
    $pageData
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - ' . $pageTitle,
    ]
);

print($layoutContent);
