<?php

require_once __DIR__ . '/init.php';

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

if (isset($_SESSION['user'])) {
    $pageTitle = '403 Вы уже вошли в аккаунт';

    $templateName = 'error.php';
    $pageData['errorTitle'] = $pageTitle;
    $pageData['errorMessage'] = 'Вы уже выполнили вход на сайт';
    http_response_code(403);
} else {
    $pageTitle = 'Вход';
    $templateName = 'login.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
    $validStatus = validateFormLogin($formInputs, $connection);

    if ($validStatus['success']) {
        $_SESSION['user'] = $validStatus['user'];
        header('Location:/');
        exit();
    }

    $pageData +=
        [
            'formInputs' => $formInputs,
            'errors' => $validStatus['errors']
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
