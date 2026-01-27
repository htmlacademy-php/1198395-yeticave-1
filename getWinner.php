<?php

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/functions/mailer.php';

/**
 * @var $connection ;
 */

$lots = getExpiredLots($connection);

if (!isset($config['mailer'])) {
    exit('Ошибка конфигурации mailer');
}

$mailer = setMailer($config['mailer']);

foreach ($lots as $lot) {
    if (isset($lot['id'], $lot['user_id']) && setLotWinner($connection, $lot['id'], $lot['user_id'])) {
        try {
            sendMessage($mailer, $lot, $config['mailer']);
        } catch (TransportExceptionInterface $e) {
            error_log('Ошибка при отправке письма: ' . $e->getMessage());
        }
    } else {
        error_log('Ошибка при сохранении данных');
    }
}
