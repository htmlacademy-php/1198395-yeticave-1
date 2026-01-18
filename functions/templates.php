<?php

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
        $mod100 >= 11 && $mod100 <= 20, $mod10 >= 5, $mod10 === 0 => $many,
        $mod10 === 1 => $one,
        default => $two,
    };
}

/**
 * Принимает дату и вычисляет, сколько времени прошло после нее. В зависимости от количества времени, возвращает результат
 * в разном формате:
 *  - больше суток - дату и время создания
 *  - меньше часа - количество прошедших минут
 *  - больше часа  - количество часов
 * @param string $date Дата в строковом формате (ГГГГ-ММ-ДД)
 * @param DateTime $currentDate Текущая дата
 *
 * @return string Прошедшее время с указанной даты
 */
function getTimePassedAfterDate(string $date, DateTime $currentDate): string
{
    try {
        $createdAt = date_create($date);
    } catch (Throwable $e) {
        error_log($e->getMessage());
        return $currentDate->format('Y-m-d H:i');
    }

    $dateDiff = $createdAt->diff($currentDate);

    return match (true) {
        $dateDiff->d > 0, $createdAt > $currentDate => $createdAt->format('d.m.y в H:i'),
        $dateDiff->h === 1 => 'Час назад',
        $dateDiff->i === 1 => 'Минуту назад',
        $dateDiff->h < 1 => $dateDiff->i . ' ' . getNounPluralForm(
            $dateDiff->i,
            'минуту',
            'минуты',
            'минут',
        ) . ' назад',
        default => $dateDiff->h . ' ' . getNounPluralForm($dateDiff->h, 'час', 'часа', 'часов') . ' назад',
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
 * @param DateTime $currentDate Текущая дата
 * @return string[] Массив, в котором первый элемент - часы, второй - минуты
 */
function getDtRange(string $date, DateTime $currentDate): array
{
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

/**
 * Показывает шаблон ошибки с заданным кодом и сообщением.
 * @param int $code Код ошибки.
 * @param string $message Сообщение ошибки.
 * @param array $cats Категории (необходимы для отображения навигации).
 * @param array|false $user Информация о пользователе (необходима для шаблона).
 */
function showError(int $code, string $message, array $cats, array|false $user): void
{
    $errorTitle = 'Ошибка ' . $code;
    $navContent = includeTemplate(
        'nav.php',
        [
            'cats' => $cats,
        ],
    );

    $pageContent = includeTemplate(
        'error.php',
        [
            'navContent' => $navContent,
            'errorMessage' => $message,
            'errorTitle' => $errorTitle,
        ],
    );

    $layoutContent = includeTemplate(
        'layout.php',
        [
            'navContent' => $navContent,
            'pageContent' => $pageContent,
            'pageTitle' => '"Yeticave" - ' . $errorTitle,
            'user' => $user,
        ],
    );

    http_response_code($code);
    print($layoutContent);
    exit();
}

/**
 * Вычисляет, показывать ставки на странице или нет.
 * @param array|false $user Залогинен ли пользователь. Если нет, ставки не показываются.
 * @param array $lot Информация о лоте. Если истек срок ставок или лот уже был выигран, ставки не показываются.
 * @param array $bids Информация о ставках. Если последняя ставка была сделана залогиненым пользователем, ставки не показываются.
 */
function showBids(array|false $user, array $lot, array $bids)
{
    [$hours, $minutes] = getDtRange($lot['date_exp'], new DateTime());
    $isExp = $hours === '00' && $minutes === '00';
    $result = $user !== false && !$isExp && (int)$user['id'] !== (int)$lot['user_id'] && !isset($lot['winner_id']);
    if (isset($bids[0])) {
        $result = $result && (int)$user['id'] !== (int)$bids[0]['user_id'];
    }

    return $result;
}
