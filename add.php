<?php

require_once __DIR__ . '/init.php';

/**
 * @var $connection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $validateFormAddLot ;
 * @var $getAuthUser ;
 * @var $uploadImg ;
 * @var $addLot ;
 * @var $showError ;
 */

$cats = getAllCats($connection);
$user = getAuthUser($connection);

if ($user === false) {
    showError(403, 'Войдите на сайт, чтобы добавить свой лот', $cats, $user);
}

$formInputs = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

    $errors = validateFormAddLot($formInputs, $cats);

    if (empty($errors)) {
        $uploadStatus = uploadImg('lot-img');

        if (isset($uploadStatus['success'], $uploadStatus['imgPath'], $user['id']) && $uploadStatus['success']) {
            $formInputs['lot-img'] = $uploadStatus['imgPath'];
            $formInputs['userId'] = $user['id'];
            $lotId = addLot($connection, $formInputs);

            if ($lotId === false) {
                error_log(mysqli_error($connection));
                exit('При сохранении данных произошла ошибка.');
            }

            header('Location:/lot.php?id=' . $lotId);
            exit();
        }
        $errors['lot-img'] = $uploadStatus['error'] ?? '';
    }
}

$navContent = includeTemplate(
    'nav.php',
    [
        'cats' => $cats,
    ],
);

$pageContent = includeTemplate(
    'add.php',
    [
        'navContent' => $navContent,
        'cats' => $cats,
        'errors' => $errors,
        'formInputs' => $formInputs,
    ],
);

$layoutContent = includeTemplate(
    'layout.php',
    [
        'navContent' => $navContent,
        'pageContent' => $pageContent,
        'pageTitle' => '"Yeticave" - Добавление лота',
        'user' => $user,
    ],
);

print($layoutContent);
