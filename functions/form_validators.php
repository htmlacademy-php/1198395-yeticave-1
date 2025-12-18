<?php

/**
 * Принимает данные формы регистрации на сайте, проверяет их и собирает ошибки в массив.
 * @param array $fromInputs Массив данных из формы регистрации.
 * @param callable $isEmailUnique Функция проверки уникальности введенного email.
 * @param mysqli $connection Ресурс соединения.
 *
 * @return array Массив выявленных ошибок в форме.
 */
function validateFormSignUp(array $fromInputs, callable $isEmailUnique, $connection): array
{
    $rules =
        [
            'email' => function ($value) use ($isEmailUnique, $connection) {
                return validateEmail($value, $isEmailUnique, $connection);
            },
            'password' => function ($value) {
                return validateTextLength($value, 8, 128);
            },
            'name' => function ($value) {
                return validateTextLength($value, 1, 128);
            },
            'message' => function ($value) {
                return validateEmptyText($value);
            }
        ];

    return validateForm($fromInputs, $rules);
}

/**
 * Принимает данные формы, введенные пользователем, проверяет их и собирает ошибки в массив.
 * @param array $formInputs Массив данных из формы.
 * @param array $cats Данные о категориях, существующих на сервере.
 *
 * @return array Массив выявленных ошибок в форме.
 */
function validateFormAddLot(array $formInputs, array $cats): array
{
    $rules =
        [
            'lot-name' => function ($value) {
                return validateTextLength($value, 1, 128);
            },
            'description' => function ($value) {
                return validateEmptyText($value);
            },
            'lot-price' => function ($value) {
                return validateNumberFormat($value);
            },
            'lot-step' => function ($value) {
                return validateNumberFormat($value);
            },
            'lot-date' => function ($value) {
                return validateDateFormat($value);
            },
            'category' => function ($value) use ($cats) {
                return validateCategory($value, $cats);
            }
        ];

    return validateForm($formInputs, $rules);
}

/**
 * Принимает данные формы и словарь с правилами-валидаторами для нее. Применяет для каждого поля свой валидатор и собирает ошибки в массив.
 * @param array $formInputs Массив данных из формы.
 * @param array $rules Словарь с правилами-валидаторами (ключ = имя поля формы, значение - функция-валидатор).
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
 * Проверяет файл, который пользователь добавил в форму. При успешной валидации загружает файл на сервер и возвращает путь к файлу на сервере,
 * при неуспешной - возвращает сообщение ошибки.
 * @param string $filename Имя файла в системе пользователя.
 *
 * @return array Массив, состоящий из статуса загрузки файла, сообщения об ошибке и пути к файлу на сервере.
 */
function uploadImg(string $filename): array
{
    $error = '';
    $imgPath = '';

    if (!empty($_FILES[$filename]['tmp_name'])) {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileTempName = $_FILES[$filename]['tmp_name'];
        $fileSize = $_FILES[$filename]['size'];

        $fileType = finfo_file($fileInfo, $fileTempName);

        $acceptedTypes =
            [
                'image/jpeg' => '.jpg',
                'image/png' => '.png'
            ];

        if (!isset($acceptedTypes[$fileType])) {
            $error = 'Загрузите картинку в формате "jpeg" или "png".';
        } elseif ($fileSize > 2000000) {
            $error = 'Максимальный размер файла: 2МБ.';
        } else {
            $fileType = $acceptedTypes[$fileType];
            $filePath = 'uploads/' . uniqid() . $fileType;

            move_uploaded_file($fileTempName, $filePath)
                ? $imgPath = '/' . $filePath
                : $error = 'Ошибка при загрузке файла.';
        }
    } else {
        $error = 'Загрузите картинку в формате "jpeg" или "png".';
    }

    return [
        'success' => empty($error) && !empty($imgPath),
        'error' => $error,
        'imgPath' => $imgPath
    ];
}

/**
 * Проверяет, чтобы длина переданного текста соответствовала заданным параметрам.
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
 * @param string $email Строка с предполагаемым email.
 * @param callable $isEmailUnique Функция проверки уникальности email на сервере.
 * @param mysqli $connection Ресурс соединения.
 *
 * @return string|null Текст ошибки либо null, если ошибки нет.
 */
function validateEmail(string $email, callable $isEmailUnique, mysqli $connection): string|null {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Введите email в корректном формате.';
    }

   return $isEmailUnique($connection, $email) ? null : 'Пользователь с таким email уже зарегистрирован.';
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
