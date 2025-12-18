<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $isAuth ;
 * @var $userName ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $validateFormAddLot ;
 */

$cats = getAllCats($connection);
$pageData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS, true);

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
        $lotId = addLot($connection, $formInputs);

        if ($lotId === false) {
            error_log(mysqli_error($connection));
            exit('Не удалось отправить данные на сервер.');
        }

        header('Location:lot.php?id=' . $lotId);
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
    'add.php',
    $pageData
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - Добавление лота',
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
