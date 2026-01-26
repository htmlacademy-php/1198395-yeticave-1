<?php

/**
 * Создаёт соединение с БД. Завершает работу сценария, если возникает ошибка соединения к БД.
 * @param array $config Массив с настройками БД.
 *
 * @return mysqli Готовое соединение.
 */
function createConnection(array $config): mysqli
{
    if (!isset($config['host'], $config['user'], $config['password'], $config['database'])) {
        exit('Ошибка конфигурации БД.');
    }

    try {
        $connection = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        exit('Ошибка соединения с базой данных.');
    }

    if (!$connection) {
        error_log(mysqli_connect_error());
        exit('Ошибка соединения с базой данных.');
    }

    setUnicode($connection);

    return $connection;
}

/**
 * Устанавливает юникод 'utf8mb4'. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
 *
 * @return void
 */
function setUnicode(mysqli $connection): void
{
    if (!mysqli_set_charset($connection, 'utf8mb4')) {
        error_log(mysqli_error($connection));
        exit('Ошибка при загрузке набора символов utf8mb4.');
    }
}

/**
 * Проверяет наличие открытой сессии для пользователя. При наличии таковой, удостоверяется, что пользователь всё ещё есть в БД.
 * Если пользователь не был найден, обнуляет сессию.
 * @param mysqli $connection Ресурс соединения.
 * @return array|false Либо массив с данными о пользователе, либо false.
 */
function getAuthUser(mysqli $connection): array|false
{
    if (!isset($_SESSION['user'], $_SESSION['user']['email'])) {
        return false;
    }

    $result = getUser($connection, $_SESSION['user']['email']);

    if ($result === false) {
        $_SESSION = [];
    }

    return $result;
}
