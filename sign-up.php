<?php

require_once __DIR__ . '/init.php';

if (isset($_SESSION['user'])) {
    http_response_code(403);
    exit();
}

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $isEmailUnique ;
 * @var $addUser ;
 */

$cats = getAllCats($connection);
$pageData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS, true);

    $errors = validateFormSignUp($formInputs, isEmailUnique(...), $connection);

    if (!empty($errors)) {
        $pageData +=
            [
                'formInputs' => $formInputs,
                'errors' => $errors
            ];
    } else {
        if (!addUser($connection, $formInputs)) {
            error_log(mysqli_error($connection));
            exit('Не удалось отправить данные на сервер.');
        }

        header('Location:/login.php');
        exit();
    }
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats
    ]
);

$pageData['navContent'] = $navContent;

$pageContent = includeTemplate(
    'sign-up.php',
    $pageData
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Регистрация.',
    ]
);

print($layoutContent);
