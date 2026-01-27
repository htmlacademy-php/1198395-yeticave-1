<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

if ($user === false || !isset($user['id'])) {
    showError(403, 'Войдите, чтобы посмотреть свои ставки.', $cats, false);
}

$bids = getUserBids($connection, $user['id']);

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'my-bets.php',
    [
        'bids' => $bids,
        'user' => $user,
        'navContent' => $navContent,
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Мои ставки.',
        'user' => $user,
    ],
);

print($layoutContent);
