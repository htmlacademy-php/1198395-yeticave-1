<?php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/functions/mailer.php';

/**
 * @var $getExpiredLots ;
 * @var $setMailer ;
 * @var $setLotWinner ;
 * @var $sendMessage ;
 */

$lots = getExpiredLots($connection);

if (!isset($config['mailer'])) {
    exit('Ошибка конфигурации mailer');
}

$mailer = setMailer($config['mailer']);

foreach ($lots as $lot) {
    if (isset($lot['id'], $lot['user_id']) && setLotWinner($connection, $lot['id'], $lot['user_id'])) {
        sendMessage($mailer, $lot, $config['mailer']);
    } else {
        error_log('Ошибка при сохранении данных');
    }
}
