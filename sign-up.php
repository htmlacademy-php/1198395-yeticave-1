<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $addUser ;
 */

$cats = getAllCats($connection);
$pageData = [];

if (isset($_SESSION['user'])) {
    $pageTitle = '403 Вы уже зарегистрированы';

    $templateName = 'error.php';
    $pageData['errorTitle'] = $pageTitle;
    $pageData['errorMessage'] = 'Чтобы зарегистрировать нового пользователя, выйдите из текущего аккаунта';
    http_response_code(403);
} else {
    $pageTitle = 'Регистрация';
    $templateName = 'sign-up.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    $errors = validateFormSignUp($formInputs, $connection);

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
