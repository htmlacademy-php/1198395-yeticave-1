<?php

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
function isDateValid(string $date): bool
{
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
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

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественного числа
 */
function getNounPluralForm(int $number, string $one, string $two, string $many): string
{
    $number = abs($number);
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    return match (true) {
        $mod100 >= 11 && $mod100 <= 20, $mod10 > 5, $mod10 == 0 => $many,
        $mod10 === 1 => $one,
        $mod10 >= 2 && $mod10 <= 4 => $two
    };
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 * @return string Итоговый HTML
 */
function includeTemplate(string $name, array $data = []): string
{
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    return ob_get_clean();
}

/**
 * Форматирует цену товара, добавляя знак рубля в конце и отступы, если число многозначное
 * @param int $price Цена товара в целочисленном формате
 * @return string Отформатированная цена
 */
function formatPrice(int $price): string
{
    return (
        $price > 1000
            ? number_format($price, 0, '', ' ')
            : $price
        )
        . '<b class="rub">р</b>';
}

/**
 * Принимает будущую дату и вычисляет, сколько осталось целых часов и минут до этой даты от текущей
 * @param string $date Будущая дата в строковом формате (ГГГГ-ММ-ДД)
 * @return string[] Массив, в котором первый элемент - часы, второй - минуты
 */
function getDtRange(string $date): array
{
    $currentDate = date_create();

    try {
        $endDate = date_create($date);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        return ['00', '00'];
    }

    if ($currentDate > $endDate) {
        return ['00', '00'];
    }

    $dateDiff = date_diff($currentDate, $endDate);

    $resultHours = $dateDiff->days * 24 + $dateDiff->h;
    $resultHours = str_pad($resultHours, 2, '0', STR_PAD_LEFT);

    $minutesLeft = str_pad($dateDiff->i, 2, '0', STR_PAD_LEFT);

    return [$resultHours, $minutesLeft];
}
