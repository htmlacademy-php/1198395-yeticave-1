<?php

/**
 * Создаёт соединение с БД. Завершает работу сценария, если возникает ошибка соединения к БД.
 * @param array $config Массив с настройками БД.
 *
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
 * Получает список 6 недавно добавленных лотов.
 * @param mysqli $connection Готовое соединение.
 *
 * @return array Массив с лотами.
 */
function getRecentLots(mysqli $connection): array
{
    $query = 'SELECT lots.*, cats.name AS category '
        . 'FROM lots JOIN cats ON lots.cat_id = cats.id '
        . 'WHERE lots.date_exp > CURDATE() '
        . 'ORDER BY lots.created_at DESC LIMIT 6';

    return getData($connection, $query);
}

/**
 * Получает список всех категорий.
 * @param mysqli $connection Готовое соединение.
 *
 * @return array Массив с категориями.
 */
function getAllCats(mysqli $connection): array
{
    $query = 'SELECT * FROM cats';

    return getData($connection, $query);
}

/**
 * Получает данные из БД и возвращает их в виде многомерного массива. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
 * @param string $query Запрос к БД.
 *
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

/**
 * Получает данные об одном лоте по его id и возвращает их в виде массива. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
 * @param int $id Id лота.
 *
 * @return array|null Данные из БД в виде массива.
 */
function getLotById(mysqli $connection, int $id): array|null
{
    $query = 'SELECT lots.*, cats.name AS category, MAX(bids.amount) AS max_price '
        . 'FROM lots JOIN cats ON lots.cat_id = cats.id '
        . 'LEFT JOIN bids ON lots.id = bids.lot_id '
        . 'WHERE lots.id = ' . $id . ' '
        . 'GROUP BY lots.id';

    if (!$result = mysqli_query($connection, $query)) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных.');
    }
    return mysqli_fetch_assoc($result);
}

/**
 * Получает данные о ставках для лота по его id, отсортированные по дате
 * @param mysqli $connection Ресурс соединения
 * @param int $id Id лота
 *
 * @return array Массив со ставками
 */
function getBidsByLotId(mysqli $connection, int $id): array
{
    $query = 'SELECT bids.*, users.name AS user_name FROM bids '
        . 'JOIN users ON bids.user_id = users.id WHERE bids.lot_id = ' . $id
        . ' ORDER BY bids.created_at DESC LIMIT 10';
    return getData($connection, $query);
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function dbGetPrepareStmt(mysqli $link, string $sql, array $data = []): mysqli_stmt
{
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else {
                if (is_double($value)) {
                    $type = 'd';
                }
            }

            $types .= $type;
            $stmt_data[] = $value;
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}
