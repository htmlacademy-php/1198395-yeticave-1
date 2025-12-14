<?php

/**
 * Принимает данные формы, введенные пользователем, проверяет их и собирает ошибки в массив.
 * @param array $formInputs Массив данных из формы.
 * @param array $cats Данные о категориях, существующих на сервере.
 *
 * @return array Массив выявленных ошибок в форме.
 */
function handleInputErrors(array $formInputs, array $cats): array
{
    $errors = [];
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

    foreach ($formInputs as $key => $value) {
        if (isset($rules[$key])) {
            $ruleFunc = $rules[$key];
            $errors[$key] = $ruleFunc($value);
        }
    }

    return $errors;
}

/**
 * Проверяет файл, который пользователь добавил в форму. При успешной валидации загружает файл на сервер,
 * при неуспешной - добавляет запись в массив ошибок.
 * @param string $filename Имя файла в системе пользователя.
 * @param array &$errors Ссылка на массив существующих ошибок.
 * @param array &$formInputs Ссылка на введенные пользователем данные. Нужны, чтобы добавить в них путь к файлу при
 * успешной валидации.
 *
 * @return void
 */
function validateFile(string $filename, array &$errors, array &$formInputs): void
{
    if (!empty($_FILES[$filename]['tmp_name'])) {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileTempName = $_FILES[$filename]['tmp_name'];
        $fileSize = $_FILES[$filename]['size'];

        $fileType = finfo_file($fileInfo, $fileTempName);

        $acceptedTypes = ['image/jpeg', 'image/png'];

        if (!in_array($fileType, $acceptedTypes)) {
            $errors[$filename] = 'Загрузите картинку в формате "jpeg" или "png".';
        } elseif ($fileSize > 2000000) {
            $errors[$filename] = 'Максимальный размер файла: 2МБ.';
        } else {
            $fileName = 'uploads/' . uniqid() . '.jpeg';
            move_uploaded_file($fileTempName, $fileName);
            $formInputs[$filename] = $fileName;
        }
    } else {
        $errors[$filename] = 'Загрузите картинку в формате "jpeg" или "png".';
    }
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
    $textLength = strlen($text);
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
