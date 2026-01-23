<?php

/**
 * @var string $userName ;
 * @var string $lotName ;
 * @var string $lotId ;
 * @var string $url ;
 */

?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= $userName ; ?></p>
<p>Ваша ставка для лота <a href="<?= $url ; ?>/lot.php?id=<?= $lotId ; ?>"><?= $lotName ; ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?= $url ; ?>/my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет-Аукцион "YetiCave"</small>