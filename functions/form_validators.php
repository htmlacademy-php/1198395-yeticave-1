<?php

/**
 * Проверяет данные поискового запроса.
 *
 * @param string|false $text Текст запроса.
 * @param int|false $catId Id категории.
 * @param array $cats Список категорий.
 *
 * @return array Ассоциативный массив с ключами:
 * `bool isTextValid` - валиден ли текст поиска (непустой);
 * `string text` - текст запроса;
 * `bool isCatValid` - валидна ли категория запроса (если идет поиск по категориям);
 * `string catName` - название категории;
 * `int catId` - `id` категории.
 */
function validateSearch(string|false $text, int|false $catId, array $cats): array
{
    $result =
        [
            'isTextValid' => false,
            'text' => '',
            'isCatValid' => false,
            'catName' => '',
            'catId' => 0,
        ];

    $text = $text !== false ? trim($text) : '';

    if (!empty($text)) {
        $result['isTextValid'] = true;
        $result['text'] = $text;
    }

    if ($catId) {
        foreach ($cats as $cat) {
            if ((int)$cat['id'] === $catId) {
                $result['isCatValid'] = true;
                $result['catName'] = $cat['name'];
                $result['catId'] = $catId;
            }
        }
    }

    return $result;
}

/**
 * Принимает данные формы входа на сайт, проверяет их и собирает ошибки в массив.
 *
 * @param array $formInputs Массив данных из формы входа.
 * @param mysqli $connection Ресурс соединения. Нужен для сверки логина/пароля с БД.
 *
 * @return array Ассоциативный массив с ключами:
 * `bool success` - успех/неуспех валидации;
 * `array user` - информация пользователя при совпадении пары email/пароль;
 * `array errors` - ошибки валидации.
 */
function validateFormLogin(array $formInputs, mysqli $connection): array
{
    $result =
        [
            'success' => false,
            'user' => [],
        ];

    $rules =
        [
            'email' => function (string $value): null|string {
                return validateEmail($value, 128);
            },
            'password' => function (string $value): null|string {
                return validateTextLength($value, 8, 128);
            },
        ];

    $result['errors'] = validateForm($formInputs, $rules);

    if (!empty($result['errors']) || !isset($formInputs['email'], $formInputs['password'])) {
        return $result;
    }

    $user = getUser($connection, $formInputs['email']);

    $result['success'] = $user !== false && isset($user['id'], $user['name'], $user['email']) && password_verify(
            $formInputs['password'],
            $user['password']
        );

    if (!$result['success']) {
        $result['errors'] =
            [
                'email' => 'Вы ввели неверный email/пароль',
                'password' => 'Вы ввели неверный email/пароль',
            ];
        return $result;
    }

    $result['user'] =
        [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
        ];

    return $result;
}

/**
 * Принимает данные формы регистрации на сайте, проверяет их и собирает ошибки в массив.
 *
 * @param array $fromInputs Массив данных из формы регистрации.
 * @param mysqli $connection Ресурс соединения.
 *
 * @return array Массив выявленных ошибок в форме.
 */
function validateFormSignUp(array $fromInputs, mysqli $connection): array
{
    $rules =
        [
            'email' => function (string $value): null|string {
                return validateEmail($value, 128);
            },
            'password' => function (string $value): null|string {
                return validateTextLength($value, 8, 128);
            },
            'name' => function (string $value): null|string {
                return validateTextLength($value, 1, 128);
            },
            'message' => function (string $value): null|string {
                return validateEmptyText($value);
            },
        ];

    $errors = validateForm($fromInputs, $rules);

    if (!isset($errors['email']) && !isEmailUnique($connection, $fromInputs['email'])) {
        $errors['email'] = 'Пользователь с таким email уже зарегистрирован.';
    }

    return $errors;
}

/**
 * Принимает данные формы добавления лота, проверяет их и собирает ошибки в массив.
 *
 * @param array $formInputs Данные из формы.
 * @param array $cats Массив с существующими категориями на сервере.
 *
 * @return array Массив выявленных ошибок в форме или `false` при пустых данных формы.
 */
function validateFormAddLot(array $formInputs, array $cats): array
{
    $rules =
        [
            'lot-name' => function (string $value): null|string {
                return validateTextLength($value, 1, 128);
            },
            'description' => function (string $value): null|string {
                return validateEmptyText($value);
            },
            'lot-price' => function (string $value): null|string {
                return validateNumberFormat($value);
            },
            'lot-step' => function (string $value): null|string {
                return validateNumberFormat($value);
            },
            'lot-date' => function (string $value): null|string {
                return validateDateFormat($value);
            },
            'category' => function (string $value) use ($cats): null|string {
                return validateCategory($value, $cats);
            },
        ];

    return validateForm($formInputs, $rules);
}

/**
 * Принимает данные формы добавления ставки, проверяет их и собирает ошибки в массив.
 *
 * @param array $formInputs Массив данных из формы.
 * @param int $minBid Минимальная ставка.
 *
 * @return array Массив выявленных ошибок в форме.
 */
function validateFormBids(array $formInputs, int $minBid): array
{
    $rules =
        [
            'cost' => function (string $value) use ($minBid): null|string {
                return (int)$value < $minBid ? 'Минимальная ставка ' . $minBid . ' р.' : null;
            },
        ];

    return validateForm($formInputs, $rules);
}

/**
 * Принимает данные формы и словарь с правилами-валидаторами для нее. Применяет для каждого поля свой валидатор и собирает ошибки в массив.
 *
 * @param array $formInputs Массив данных из формы.
 * @param array $rules Словарь с правилами-валидаторами (ключ = имя поля формы, значение - функция-валидатор).
 *
 * @return array Массив выявленных ошибок в форме.
 */
function validateForm(array $formInputs, array $rules): array
{
    $errors = [];

    foreach ($formInputs as $key => $value) {
        if (isset($rules[$key])) {
            $ruleFunc = $rules[$key];
            $errors[$key] = $ruleFunc($value);
        }
    }

    return array_filter($errors);
}

/**
 * Проверяет, чтобы длина переданного текста соответствовала заданным параметрам.
 *
 * @param string $text Текст для валидации.
 * @param int $min Минимальное значение.
 * @param int $max Максимальное значение.
 *
 * @return string|null Текст ошибки либо null, если ошибок нет.
 */
function validateTextLength(string $text, int $min, int $max): string|null
{
    $textLength = mb_strlen($text, 'UTF-8');
    if ($textLength < $min || $textLength > $max) {
        return "Значение поля должно быть от $min до $max символов.";
    }

    return null;
}

/**
 * Проверяет текстовое поле, чтобы оно не было пустым.
 *
 * @param string $text Текст для проверки.
 *
 * @return string|null Текст ошибки либо null, если ошибок нет.
 */
function validateEmptyText(string $text): string|null
{
    if (empty($text)) {
        return 'Значение поля не должно быть пустым.';
    }

    return null;
}

/**
 * Проверяет число на соответствие формату (целочисленное и больше нуля).
 *
 * @param string $number Число для проверки.
 *
 * @return string|null Текст ошибки либо null, если ошибок нет.
 */
function validateNumberFormat(string $number): string|null
{
    if (!filter_var($number, FILTER_VALIDATE_INT) || (int)$number <= 0) {
        return 'Число должно быть целым и больше нуля.';
    }

    return null;
}

/**
 * Проверяет дату, переданную в строковом виде. Дата должна соответствовать формату ГГГГ-ММ-ДД
 * и быть больше текущей даты хотя бы на один день.
 *
 * @param string $date Дата в виде строки.
 *
 * @return string|null Текст ошибки либо null, если ошибок нет.
 */
function validateDateFormat(string $date): string|null
{
    if (!isDateValid($date, 'Y-m-d')) {
        return 'Введите дату в формате "ГГГГ-ММ-ДД".';
    }

    $endDate = date_create($date);
    $currentDate = date_create();

    if ($currentDate > $endDate) {
        return 'Введите дату позже текущей хотя бы на один день.';
    }

    return null;
}

/**
 * Проверяет наличие переданной категории в массиве существующих категорий.
 *
 * @param string $category Категория для проверки.
 * @param array $cats Массив с существующими категориями.
 *
 * @return string|null Текст ошибки либо null, если ошибок нет.
 */
function validateCategory(string $category, array $cats): string|null
{
    foreach ($cats as $catInfo) {
        if (in_array($category, $catInfo)) {
            return null;
        }
    }
    return 'Выберете категорию из списка.';
}

/**
 * Проверяет соответствие переданной строки email-формату. Удостоверяется, что переданный email является уникальным.
 * Возвращает либо строку с описанием ошибки, либо null, если email валиден.
 *
 * @param string $email Строка с предполагаемым email.
 * @param int $max Максимальная длина email.
 *
 * @return string|null Текст ошибки либо null, если ошибки нет.
 */
function validateEmail(string $email, int $max): string|null
{
    if (mb_strlen($email, 'UTF-8') > $max) {
        return 'Длина email не может превышать ' . $max . ' символов.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Введите email в корректном формате.';
    }

    return null;
}

/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * isDateValid('2019-01-01'); // true
 * isDateValid('2016-02-29'); // true
 * isDateValid('2019-04-31'); // false
 * isDateValid('10.10.2010'); // false
 * isDateValid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function isDateValid(string $date, string $format): bool
{
    $dateTimeObj = date_create_from_format($format, $date);

    return $dateTimeObj !== false && !date_get_last_errors();
}
