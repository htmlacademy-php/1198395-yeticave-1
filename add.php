<?php

require_once __DIR__ . '/init.php';

/**
 * @var $createConnection ;
 * @var $getAllCats ;
 * @var $includeTemplate ;
 * @var $dbGetPrepareStmt ;
 * @var $handleInputErrors ;
 */

$isAuth = rand(0, 1);

$userName = 'Борис'; // укажите здесь ваше имя

if (!file_exists(__DIR__ . '/config.php')) {
    exit('Файл конфигурации отсутствует.');
}
$config = require __DIR__ . '/config.php';

$connection = createConnection($config['db']);
$cats = getAllCats($connection);

$pageContent = includeTemplate(
    'add.php',
    [
        'cats' => $cats,
    ]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formInputs = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS, true);

    $errors = handleInputErrors($formInputs, $cats);

    validateFile('lot-img', $errors, $formInputs);

    $errors = array_filter($errors);

    if (!empty($errors)) {
        $pageContent = includeTemplate(
            'add.php',
            [
                'formInputs' => $formInputs,
                'cats' => $cats,
                'errors' => $errors
            ]
        );
    } else {
        $sql = 'INSERT INTO lots (created_at, name, cat_id, description, price, bid_step, date_exp, img_url, user_id) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, 1)';
        $stmt = dbGetPrepareStmt($connection, $sql, $formInputs);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            $lot_id = mysqli_insert_id($connection);

            header('Location:lot.php?id=' . $lot_id);
        }
    }
}

$layoutContent = includeTemplate(
    'layout.php',
    [
        'cats' => $cats,
        'pageContent' => $pageContent,
        'userName' => $userName,
        'pageTitle' => '"Yeticave" - Добавление лота',
        'isAuth' => $isAuth
    ]
);

print($layoutContent);
