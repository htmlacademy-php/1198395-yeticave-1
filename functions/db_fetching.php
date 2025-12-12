<?php

/**
 * Создаёт соединение с БД. Завершает работу сценария, если возникает ошибка соединения к БД.
 * @param array $config Массив с настройками БД.
 * @return mysqli Готовое соединение.
 */
function createConnection(array $config): mysqli
{
    try {
        $connection = mysqli_connect($config['host'], $config['user'], $config['password'], $config['database']);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        exit('Ошибка соединения с базой данных.');
    }


    if (!$connection) {
        error_log(mysqli_connection_error());
        exit('Ошибка соединения с базой данных.');
    }

    setUnicode($connection);

    return $connection;
}

/**
 * Устанавливает юникод 'utf8mb4'. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
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
 * Получает список 6 недавно добавленных лотов.
 * @param mysqli $connection Готовое соединение.
 * @return array Массив с лотами.
 */
function getRecentLots($connection): array
{
    $query = 'SELECT lots.*, cats.name AS category '
    . 'FROM lots JOIN cats ON lots.cat_id = cats.id '
    . 'ORDER BY lots.created_at DESC LIMIT 6';

    return getData($connection, $query);
}

/**
 * Получает список всех категорий.
 * @param mysqli $connection Готовое соединение.
 * @return array Массив с категориями.
 */
function getAllCats($connection): array
{
    $query = 'SELECT * FROM cats';

    return getData($connection, $query);
}

/**
 * Получает данные из БД и возвращает их в виде многомерного массива. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
 * @param string $query Запрос к БД.
 * @return array Данные из БД в виде массива.
 */
function getData(mysqli $connection, string $query): array
{
    if (!$result = mysqli_query($connection, $query)) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных.');
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}
