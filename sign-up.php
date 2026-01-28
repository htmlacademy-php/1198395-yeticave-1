<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

if ($user !== false) {
    showError(403, 'Чтобы зарегистрировать нового пользователя, выйдите из текущего аккаунта', $cats, $user);
}

$formInputs = [];
$errors = [];

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    if (is_null($formInputs) || $formInputs === false) {
        exit('Ошибка получения данных формы.');
    }

    $errors = validateFormSignUp($formInputs, $connection);

    if (empty($errors)) {
        $success = addUser($connection, $formInputs);
        if ($success) {
            header('Location:/login.php');
            exit();
        } else {
            exit('При сохранении данных произошла ошибка.');
        }
    }
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'sign-up.php',
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
        'pageTitle' => '"Yeticave" - Регистрация.',
        'user' => $user,
    ],
);

print($layoutContent);
