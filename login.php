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
 * @var $authUser ;
 * @var $getUser ;
 * @var $validateFormLogin ;
 */

$cats = getAllCats($connection);
$pageData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS, true);
    $errors = validateFormLogin($formInputs);

    if (empty($errors)) {
        $authData = authUser($formInputs, getUser(...), $connection);

        if ($authData['success']) {
            $_SESSION['user'] = $authData['data'];
            header('Location:/index.php');
            exit();
        }

        $errors = $authData['data'];
    }

    $pageData +=
        [
            'formInputs' => $formInputs,
            'errors' => $errors
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
    'login.php',
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
