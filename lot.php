<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 */

$cats = getAllCats($connection);
$lotId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$user = getAuthUser($connection);

if (!$lotId || (!$lot = getLotById($connection, $lotId)) || !isset($lot['price'], $lot['bid_step'], $lot['name'])) {
    showError(404, 'Данной страницы не существует на сайте.', $cats, $user);
}

$bids = getBidsByLot($connection, $lotId);
$price = (int)($lot['max_price'] ?? $lot['price']);
$minBid = $price + (int)$lot['bid_step'];

$formInputs = [];
$errors = [];

if ($user !== false && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    if (is_null($formInputs) || $formInputs === false) {
        exit('Ошибка получения данных формы');
    }

    $errors = validateFormBids($formInputs, $minBid);

    if (empty($errors) && isset($formInputs['cost'], $user['id'], $lot['id'])) {
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
        'formInputs' => $formInputs,
        'errors' => $errors,
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
