<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

if ($user !== false) {
    showError(403, 'Вы уже выполнили вход на сайт', $cats, $user);
}

$formInputs = [];
$errors = [];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    if (is_null($formInputs) || $formInputs === false) {
        exit('Ошибка получения данных формы.');
    }

    $validStatus = validateFormLogin($formInputs, $connection);

    if (isset($validStatus['success'], $validStatus['user']) && $validStatus['success']) {
        $_SESSION['user'] = $validStatus['user'];
        header('Location:/');
        exit();
    }

    $errors = $validStatus['errors'] ?? [];
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
        'formInputs' => $formInputs,
        'errors' => $errors,
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
