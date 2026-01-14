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
$user = getAuthUser($connection);

if ($lotId && $lot = getLotById($connection, $lotId)) {
    $bids = getBidsByLot($connection, $lotId);

    $navContent = includeTemplate(
        'nav.php',
        [
            'cats' => $cats,
        ],
    );

    $pageContent = includeTemplate(
        'lot.php',
        [
            'navContent' => $navContent,
            'lot' => $lot,
            'bids' => $bids,
            'user' => $user,
        ],
    );

    $layoutContent = includeTemplate(
        'layout.php',
        [
            'navContent' => $navContent,
            'pageContent' => $pageContent,
            'pageTitle' => '"Yeticave" - ' . $lot['name'],
            'user' => $user,
        ],
    );

    print($layoutContent);
} else {
    showError(404, 'Данной страницы не существует на сайте.', $cats, $user);
}
