<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $getBidsByLot ;
 * @var $validateFormBids ;
 * @var $addBid ;
 * @var $getAuthUser ;
 * @var $showError ;
 * @var $showBids ;
 */

$cats = getAllCats($connection);
$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = getAuthUser($connection);

if (!$lotId || !$lot = getLotById($connection, $lotId)) {
    showError(404, 'Данной страницы не существует на сайте.', $cats, $user);
}

$bids = getBidsByLot($connection, $lotId);
$price = (int)($lot['max_price'] ?? $lot['price']);
$minBid = $price + (int)$lot['bid_step'];

if ($user !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $errors = validateFormBids($formInputs, $minBid);

    if (empty($errors)) {
        $values =
            [
                $formInputs['cost'],
                $user['id'],
                $lot['id'],
            ];
        if (addBid($connection, $values)) {
            header('Location:/lot.php?id= ' . $lot['id']);
            exit();
        } else {
            exit('При сохранении данных произошла ошибка.');
        }
    }
}

$showBids = showBids($user, $lot, $bids);

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
        'price' => $price,
        'minBid' => $minBid,
        'showBids' => $showBids,
        'formInputs' => $formInputs ?? [],
        'errors' => $errors ?? [],
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
