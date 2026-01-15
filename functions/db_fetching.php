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
 * Получает список из 6 недавно добавленных лотов, у которых не истек срок торгов (lot.date_exp).
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
 * Получает данные об одном лоте по его id и возвращает их в виде массива. Завершает сценарий при ошибке.
 * @param mysqli $connection Готовое соединение.
 * @param int $id Id лота.
 *
 * @return array|false Данные из БД в виде массива.
 */
function getLotById(mysqli $connection, int $id): array|false
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

    return mysqli_fetch_assoc($result) ?? false;
}

/**
 * Получает данные о ставках для лота по его id, отсортированные по дате (от ранних к поздним).
 * @param mysqli $connection Ресурс соединения.
 * @param int $lotId Id лота.
 *
 * @return array Массив со ставками.
 */
function getBidsByLot(mysqli $connection, int $lotId): array
{
    $query = 'SELECT bids.*, users.name AS user_name FROM bids '
        . 'JOIN users ON bids.user_id = users.id WHERE bids.lot_id = ' . $lotId
        . ' ORDER BY bids.created_at DESC LIMIT 10';

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
 * Добавляет лот на сервер через подготовленное выражение. Возвращает либо id лота, либо false при ошибке.
 * @param mysqli $connection Ресурс соединения.
 * @param array $formInputs Данные из формы.
 *
 * @return int|false Id лота, либо false при ошибке.
 */
function addLot(mysqli $connection, array $formInputs): int|false
{
    $query = 'INSERT INTO lots (created_at, name, cat_id, description, price, bid_step, date_exp, img_url, user_id) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = dbGetPrepareStmt($connection, $query, $formInputs);

    mysqli_stmt_execute($stmt);
    $lotId = mysqli_insert_id($connection);
    return $lotId > 0 ? $lotId : false;
}

/**
 * Получает информацию о пользователе по переданному email.
 * @var mysqli $connection Ресурс соединения.
 * @var string $email Строка с email.
 *
 * @return array|false Данные о пользователе или false, если пользователь не найден.
 */
function getUser(mysqli $connection, string $email): array|false
{
    $query = 'SELECT * FROM users WHERE users.email = ?';
    $stmt = dbGetPrepareStmt($connection, $query, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_fetch_assoc($result) ?? false;
}

/**
 * Проверяет наличие открытой сессии для пользователя. При наличии таковой, удостоверяется, что пользователь всё ещё есть в БД.
 * Если пользователь не был найден, обнуляет сессию.
 * @param mysqli $connection Ресурс соединения.
 * @return array|false Либо массив с данными о пользователе, либо false.
 */
function getAuthUser(mysqli $connection): array|false
{
    if (!isset($_SESSION['user'])) {
        return false;
    }

    $result = getUser($connection, $_SESSION['user']['email']);

    if ($result === false) {
        $_SESSION = [];
    }

    return $result;
}

/**
 * Проверяет, есть ли в БД переданный email.
 * @return bool True, если такого email еще нет, false - если уже есть.
 * @var mysqli $connection Ресурс соединения.
 * @var string $email Строка с email.
 */
function isEmailUnique(mysqli $connection, string $email): bool
{
    $query = 'SELECT EXISTS(SELECT users.email FROM users WHERE users.email = ?) AS is_unique';

    $stmt = dbGetPrepareStmt($connection, $query, [$email]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_assoc($result);

    return (int)$result['is_unique'] === 0;
}

/**
 * Добавляет нового пользователя в БД.
 * @param mysqli $connection Ресурс соединения.
 * @param array $formInputs Данные формы.
 *
 * @return bool Создан пользователь или нет.
 */
function addUser(mysqli $connection, array $formInputs): bool
{
    $query = 'INSERT INTO users (email, password, name, contacts) VALUES (?, ?, ?, ?)';
    $formInputs['password'] = password_hash($formInputs['password'], PASSWORD_DEFAULT);

    $stmt = dbGetPrepareStmt($connection, $query, $formInputs);
    if (!mysqli_stmt_execute($stmt)) {
        error_log(mysqli_error($connection));
        return false;
    }
    return true;
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param mysqli $link Ресурс соединения
 * @param string $sql  SQL запрос с плейсхолдерами вместо значений
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
        $stmtData = [];

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
            $stmtData[] = $value;
        }

        $values = array_merge([$stmt, $types], $stmtData);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Получает общее количество найденных активных лотов, подходящих под запрос.
 * @param mysqli $connection Ресурс соединения.
 * @param string $text Текст запроса.
 * @return int Количество лотов.
 */
function getLotsAmount(mysqli $connection, string $text): int
{
    $query = 'SELECT COUNT(lots.id) AS amount FROM lots WHERE MATCH lots.name, lots.description AGAINST (?) AND lots.date_exp > CURDATE()';

    $stmt = dbGetPrepareStmt($connection, $query, [ $text ]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_assoc($result);

    return (int)$result['amount'];
}

/**
 * Выполняет поиск лотов по запросу.
 * @param mysqli $connection Ресурс соединения.
 * @param string $text Текст запроса.
 * @param int $page Текущая страница пагинации.
 * @param int $limit Количество лотов на одной странице.
 * @return array Найденные лоты.
 */
function search(mysqli $connection, string $text, int $page, int $limit): array
{
    $offset = ($page - 1) * $limit;
    $query = 'SELECT lots.*, cats.name AS category FROM lots'
            . ' JOIN cats ON lots.cat_id = cats.id WHERE MATCH lots.name, lots.description AGAINST (?)'
            . ' AND lots.date_exp > CURDATE() ORDER BY lots.created_at DESC LIMIT '
            . $limit . ' OFFSET ' . $offset;

    $stmt = dbGetPrepareStmt($connection, $query, [ $text ]);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $result = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $result;
}
