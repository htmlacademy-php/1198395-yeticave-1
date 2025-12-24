<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $validateFormAddLot ;
 */

$cats = getAllCats($connection);
$pageData = [];

if (!isset($_SESSION['user'])) {
    $pageTitle = '403 Войдите на сайт';

    $templateName = 'error.php';
    $pageData['errorTitle'] = $pageTitle;
    $pageData['errorMessage'] = 'Войдите на сайт, чтобы добавить свой лот';
    http_response_code(403);
} else {
    $pageTitle = 'Добавление лота';
    $templateName = 'add.php';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    $errors = validateFormAddLot($formInputs, $cats);

    if (empty($errors)) { // Не пытаемся обработать файл, если в форме есть другие ошибки
        $uploadStatus = uploadImg('lot-img');
        $uploadStatus['success']
            ? $formInputs['lot-img'] = $uploadStatus['imgPath']
            : $errors['lot-img'] = $uploadStatus['error'];
    }

    if (!empty($errors)) {
        $pageData +=
            [
                'errors' => $errors,
                'formInputs' => $formInputs
            ];
    } else {
        $formInputs['userId'] = $_SESSION['user']['id'];
        $lotId = addLot($connection, $formInputs);

        if ($lotId === false) {
            error_log(mysqli_error($connection));
            exit('Не удалось отправить данные на сервер.');
        }

        header('Location:/lot.php?id=' . $lotId);
        exit();
    }
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats
    ]
);

$pageData +=
    [
        'navContent' => $navContent,
        'cats' => $cats
    ];

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
