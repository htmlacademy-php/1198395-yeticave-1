<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Создаёт экземпляр класса Mailer с настройками по переданной конфигурации.
 * @param array $config Массив с конфигурацией.
 * @return Mailer Экземпляр класса Mailer.
 */
function setMailer(array $config): Mailer
{
    if (!isset($config['login'], $config['password'], $config['host'], $config['port'], $config['properties'])) {
        exit('Ошибка конфигурации mailer.');
    }

    $dsn = 'smtp://' . $config['login'] . ':' . $config['password'] . '@' . $config['host'] . ':' . $config['port'];
    if (!empty($config['properties'])) {
        $dsn .= '/?' . $config['properties'];
    }
    $transport = Transport::fromDsn($dsn);

    return new Mailer($transport);
}

/**
 * Отправляет письмо победителю о выигрыше в аукционе.
 * @param Mailer $mailer Настроенный экземпляр класса Mailer.
 * @param array $lot Информация о лоте.
 * @param array $config Конфигурация Mailer.
 * @return bool `true|false` Успех/неуспех при отправке письма.
 */
function sendMessage(Mailer $mailer, array $lot, array $config): bool
{
    if (!isset($lot['userName'], $lot['name'], $lot['id'], $config['url'], $lot['email'], $config['email'])) {
        error_log('Отсутствуют необходимые ключи в передаваемом массиве lot или config.');
        return false;
    }

    $message = new Email();
    $mailTemplate = includeTemplate(
        'email.php',
        [
            'userName' => $lot['userName'],
            'lotName' => $lot['name'],
            'lotId' => $lot['id'],
            'url' => $config['url'],
        ],
    );
    $message->to($lot['email']);
    $message->from($config['email']);
    $message->subject('Ваша ставка победила');
    $message->html($mailTemplate);
    $mailer->send($message);
    return true;
}
