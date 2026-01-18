<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $authUser ;
 * @var $getUser ;
 * @var $validateFormLogin ;
 * @var $getAuthUser ;
 * @var $showError ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

if ($user !== false) {
    showError(403, 'Вы уже выполнили вход на сайт', $cats, $user);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $validStatus = validateFormLogin($formInputs, $connection);

    if ($validStatus['success']) {
        $_SESSION['user'] = $validStatus['user'];
        header('Location:/');
        exit();
    }

    $errors = $validStatus['errors'];
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'login.php',
    [
        'navContent' => $navContent,
        'formInputs' => $formInputs ?? [],
        'errors' => $errors ?? [],
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Вход',
        'user' => $user,
    ],
);

print($layoutContent);
