<?php

/**
 * Получает список из 9 недавно добавленных лотов, у которых не истек срок торгов.
 *
 * @param mysqli $connection Готовое соединение.
 *
 * @return array Многомерный ассоциативный массив с ключами:
 * `id` - id лота;
 * `name` - имя лота;
 * `img_url` - путь к изображению лота;
 * `price` - начальная цена лота;
 * `date_exp` - дата истечения срока торгов на лот;
 * `category` - категория, к которой относится лот.
 */
function getRecentLots(mysqli $connection): array
{
    $query = 'SELECT `lots`.`id`, `lots`.`name`, `lots`.`img_url`, `lots`.`price`, `lots`.`date_exp`, `cats`.`name` AS `category` '
        . 'FROM `lots` JOIN `cats` ON `lots`.`cat_id` = `cats`.`id` '
        . 'WHERE `lots`.`date_exp` > CURDATE() '
        . 'AND `lots`.`winner_id` IS NULL '
        . 'ORDER BY `lots`.`created_at` DESC LIMIT 9';

    return getData($connection, $query);
}

/**
 * Получает список всех категорий.
 *
 * @param mysqli $connection Готовое соединение.
 *
 * @return array Многомерный ассоциативный массив с ключами:
 * `id` - id категории;
 * `name` - название категории;
 * `class` - css класс категории.
 */
function getAllCats(mysqli $connection): array
{
    $query = 'SELECT `cats`.`id`, `cats`.`name`, `cats`.`class` FROM `cats`';

    return getData($connection, $query);
}

/**
 * Получает данные об одном лоте по его id и возвращает их в виде массива. Завершает сценарий при ошибке.
 *
 * @param mysqli $connection Готовое соединение.
 * @param int $lotId Id лота.
 *
 * @return array|false Ассоциативный массив с ключами:
 * `id` - id лота;
 * `name` - имя лота;
 * `price` - начальная цена лота;
 * `img_url` - путь к изображению лота;
 * `date_exp` - дата истечения срока торгов на лот;
 * `description` - описание лота;
 * `winner_id` - id пользователя-победителя торгов на лот;
 * `bid_step` - шаг ставки;
 * `user_id` - id пользователя-создателя лота;
 * `category` - категория, к которой относится лот;
 * `max_price` - максимальная ставка на лот на данный момент.
 */
function getLotById(mysqli $connection, int $lotId): array|false
{
    $query = 'SELECT `lots`.`id`, `lots`.`name`, `lots`.`price`, `lots`.`img_url`, `lots`.`date_exp`, `lots`.`description`, `lots`.`winner_id`, `lots`.`bid_step`, `lots`.`user_id`, `cats`.`name` AS `category`, MAX(`bids`.`amount`) AS `max_price` '
        . 'FROM `lots` JOIN `cats` ON `lots`.`cat_id` = `cats`.`id` '
        . 'LEFT JOIN `bids` ON `lots`.`id` = `bids`.`lot_id` '
        . 'WHERE `lots`.`id` = ' . $lotId . ' '
        . 'GROUP BY `lots`.`id`';

    $result = mysqli_query($connection, $query);

    if (!$result) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных.');
    }

    return mysqli_fetch_assoc($result) ?? false;
}

/**
 * Получает данные о ставках для лота по его id, отсортированные по дате (от ранних к поздним).
 *
 * @param mysqli $connection Ресурс соединения.
 * @param int $lotId Id лота.
 *
 * @return array Многомерный ассоциативный массив с ключами:
 * `user_id` - id пользователя, сделавшего ставку;
 * `amount` - величина ставки;
 * `created_at` - время ставки;
 * `lot_id` - id лота, на который делалась ставка;
 * `user_name` - имя пользователя, сделавшего ставку.
 */
function getBidsByLot(mysqli $connection, int $lotId): array
{
    $query = 'SELECT `bids`.`user_id`, `bids`.`amount`, `bids`.`created_at`, `bids`.`lot_id`, `users`.`name` AS `user_name` FROM `bids` '
        . 'JOIN `users` ON `bids`.`user_id` = `users`.`id` WHERE `bids`.`lot_id` = ' . $lotId
        . ' ORDER BY `bids`.`created_at` DESC LIMIT 10';

    return getData($connection, $query);
}

/**
 * Получает данные из БД и возвращает их в виде многомерного ассоциативного массива. Завершает сценарий при ошибке.
 *
 * @param mysqli $connection Готовое соединение.
 * @param string $query Запрос к БД.
 *
 * @return array Данные из БД в виде массива.
 */
function getData(mysqli $connection, string $query): array
{
    $result = mysqli_query($connection, $query);
    if (!$result) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных.');
    }

    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Добавляет лот на сервер через подготовленное выражение.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param array $formInputs Данные из формы.
 *
 * @return int|false `id` лота, либо `false` при ошибке.
 */
function addLot(mysqli $connection, array $formInputs): int|false
{
    $query = 'INSERT INTO `lots` (`created_at`, `name`, `cat_id`, `description`, `price`, `bid_step`, `date_exp`, `img_url`, `user_id`) VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';
    $stmt = dbGetPrepareStmt($connection, $query, $formInputs);

    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    $lotId = mysqli_insert_id($connection);
    $lotId = (int)$lotId;
    return $lotId > 0 ? $lotId : false;
}

/**
 * Получает информацию о пользователе по переданному email.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param string $email Строка с email.
 *
 * @return array|false Ассоциативный массив с ключами:
 * `id` - id пользователя;
 * `email` - почта пользователя;
 * `name` - имя пользователя;
 * `password` - хэш-пароль;
 * `contacts` - контакты пользователя для связи;
 * или `false`, если пользователь не найден, или произошла ошибка при получении данных.
 */
function getUser(mysqli $connection, string $email): array|false
{
    $query = 'SELECT `users`.`id`, `users`.`email`, `users`.`name`, `users`.`password`, `users`.`contacts` FROM `users` WHERE `users`.`email` = ?';
    $stmt = dbGetPrepareStmt($connection, $query, [$email]);

    if (!mysqli_stmt_execute($stmt)) {
        return false;
    }

    $result = mysqli_stmt_get_result($stmt);

    if (!$result) {
        return false;
    }

    return mysqli_fetch_assoc($result) ?? false;
}

/**
 * Проверяет, есть ли в БД пользователь с переданным email.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param string $email Строка с email.
 *
 * @return bool `true`, если такого email еще нет, `false` - если уже есть.
 */
function isEmailUnique(mysqli $connection, string $email): bool
{
    $query = 'SELECT EXISTS(SELECT `users`.`email` FROM `users` WHERE `users`.`email` = ?) AS `is_unique`';

    $stmt = dbGetPrepareStmt($connection, $query, [$email]);

    if (!mysqli_stmt_execute($stmt) || !$result = mysqli_stmt_get_result($stmt)) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных об email.');
    }

    $result = mysqli_fetch_assoc($result);

    if (!isset($result['is_unique'])) {
        error_log(mysqli_error($connection));
        exit('Ошибка при получении данных об email.');
    }

    return (int)$result['is_unique'] === 0;
}

/**
 * Добавляет нового пользователя в БД.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param array $formInputs Данные формы.
 *
 * @return bool `true` - пользователь добавлен в БД, `false` - произошла ошибка.
 */
function addUser(mysqli $connection, array $formInputs): bool
{
    $query = 'INSERT INTO `users` (`email`, `password`, `name`, `contacts`) VALUES (?, ?, ?, ?)';

    if (!isset($formInputs['password'])) {
        return false;
    }

    $formInputs['password'] = password_hash($formInputs['password'], PASSWORD_DEFAULT);

    $stmt = dbGetPrepareStmt($connection, $query, $formInputs);
    return mysqli_stmt_execute($stmt);
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param mysqli $link Ресурс соединения
 * @param string $sql SQL запрос с плейсхолдерами вместо значений
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
            } elseif (is_double($value)) {
                $type = 'd';
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
 *
 * @param mysqli $connection Ресурс соединения.
 * @param string $searchQuery Запрос.
 * @param array $values Значение запроса.
 *
 * @return int Количество лотов.
 */
function getMatchedLotsCount(mysqli $connection, string $searchQuery, array $values): int
{
    $query = 'SELECT COUNT(`lots`.`id`) AS `amount` FROM `lots` WHERE ' . $searchQuery . ' AND `lots`.`date_exp` > CURDATE()';

    $stmt = dbGetPrepareStmt($connection, $query, $values);

    if (!mysqli_stmt_execute($stmt) || !$result = mysqli_stmt_get_result($stmt)) {
        return 0;
    }

    $result = mysqli_fetch_assoc($result);

    if (!isset($result['amount'])) {
        return 0;
    }

    return (int)$result['amount'];
}

/**
 * Выполняет поиск лотов по запросу.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param array $searchInfo Поисковая информация, полученная при валидации запроса.
 * @param int|false $page Запрашиваемая страница пагинации.
 * @param int $lotsPerPage Количество лотов на одной странице.
 *
 * @return array Общее количество страниц и найденные лоты для переданной страницы.
 */
function search(mysqli $connection, array $searchInfo, int $lotsPerPage, int|false $page): array
{
    $textQuery = 'MATCH `lots`.`name`, `lots`.`description` AGAINST (?)';
    $catQuery = '`lots`.`cat_id` = ?';

    $result =
        [
            'pages' => 0,
            'lots' => [],
        ];

    if (!isset($searchInfo['isTextValid'], $searchInfo['isCatValid'], $searchInfo['catId'], $searchInfo['text'])) {
        error_log('Отсутствуют необходимые ключи в массиве $searchInfo');
        return $result;
    }

    switch (true) {
        case $searchInfo['isTextValid'] && $searchInfo['isCatValid']:
            $searchQuery = $catQuery . ' AND ' . $textQuery;
            $values =
                [
                    $searchInfo['catId'],
                    $searchInfo['text'],
                ];
            break;
        case $searchInfo['isTextValid']:
            $searchQuery = $textQuery;
            $values = [$searchInfo['text']];
            break;
        case $searchInfo['isCatValid']:
            $searchQuery = $catQuery;
            $values = [$searchInfo['catId']];
            break;
        default:
            return $result;
    }

    $lotsCount = getMatchedLotsCount($connection, $searchQuery, $values);

    $result['pages'] = (int)ceil($lotsCount / $lotsPerPage);

    if (!$page || $page < 1 || $page > $result['pages']) {
        $page = 1;
    }

    $offset = ($page - 1) * $lotsPerPage;
    $query = 'SELECT `lots`.`id`, `lots`.`name`, `lots`.`price`, `lots`.`img_url`, `lots`.`date_exp`, `cats`.`name` AS `category` FROM `lots`'
        . ' JOIN `cats` ON `lots`.`cat_id` = `cats`.`id` WHERE ' . $searchQuery
        . ' AND `lots`.`date_exp` > CURDATE() AND `lots`.`winner_id` IS NULL ORDER BY `lots`.`created_at` DESC LIMIT '
        . $lotsPerPage . ' OFFSET ' . $offset;

    $stmt = dbGetPrepareStmt($connection, $query, $values);

    if (mysqli_stmt_execute($stmt) && $lots = mysqli_stmt_get_result($stmt)) {
        $result['lots'] = mysqli_fetch_all($lots, MYSQLI_ASSOC);
    }

    return $result;
}

/**
 * Добавляет ставку к лоту.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param array $values Массив из величины ставки, id лота и пользователя.
 *
 * @return bool `true` - ставка добавлена, `false` - при ошибке.
 */
function addBid(mysqli $connection, array $values): bool
{
    $query = 'INSERT INTO `bids` (`amount`, `user_id`, `lot_id`) VALUES (?, ?, ?)';
    $stmt = dbGetPrepareStmt($connection, $query, $values);
    return mysqli_stmt_execute($stmt);
}

/**
 * Получает ставки пользователя по его id.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param int $userId Id пользователя.
 *
 * @return array Массив с информацией о ставках.
 */
function getUserBids(mysqli $connection, int $userId): array
{
    $query = 'SELECT `bids`.`id`, `bids`.`created_at`, `bids`.`amount`, `bids`.`user_id`, `bids`.`lot_id`, `lots`.`created_at` AS `lot_created`, `lots`.`name`, `lots`.`img_url`, `lots`.`date_exp`, `lots`.`winner_id`, `cats`.`name` AS `category`, `users`.`contacts` FROM `bids` '
        . 'JOIN `lots` ON `lots`.`id` = `bids`.`lot_id` '
        . 'JOIN `cats` ON `lots`.`cat_id` = `cats`.`id` '
        . 'JOIN `users` ON `users`.`id` = `lots`.`user_id` WHERE `bids`.`user_id` = ' . $userId . ' '
        . 'ORDER BY `bids`.`created_at` DESC';
    return getData($connection, $query);
}

/**
 * Получает массив из истекших по времени лотов со ставками и без победителя.
 * Также получает `id` пользователя, сделавшего наибольшую ставку на лот.
 *
 * @param mysqli $connection Ресурс соединения.
 *
 * @return array Многомерный ассоциативный массив из лотов и id пользователя с наибольшей ставкой.
 */
function getExpiredLots(mysqli $connection): array
{
    $query = 'SELECT `lots`.`id`, `lots`.`name`, `bids`.`user_id`, `users`.`email`, `users`.`name` AS `userName` '
        . 'FROM `lots` JOIN `bids` ON `lots`.`id` = `bids`.`lot_id` JOIN `users` ON `bids`.`user_id` = `users`.`id` '
        . 'WHERE CURDATE() >= `lots`.`date_exp` AND `lots`.`winner_id` IS NULL '
        . 'AND `bids`.`amount` = (SELECT MAX(`bids`.`amount`) FROM `bids` WHERE `bids`.`lot_id` = `lots`.`id`)';
    return getData($connection, $query);
}

/**
 * Назначает победителя для лота.
 *
 * @param mysqli $connection Ресурс соединения.
 * @param int $lotId Id лота.
 * @param int $userId Id победителя.
 *
 * @return bool Успешно ли обновились данные о лоте.
 */
function setLotWinner(mysqli $connection, int $lotId, int $userId): bool
{
    $query = 'UPDATE `lots` SET `lots`.`winner_id` = ? WHERE `lots`.`id` = ?';

    $stmt = dbGetPrepareStmt($connection, $query, [$userId, $lotId]);
    return mysqli_stmt_execute($stmt);
}
