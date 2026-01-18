<?php

/**
 * @var string $navContent ;
 * @var array $bids ;
 * @var array $user ;
 */

?>
<main>
<?= $navContent ; ?>
    <section class="rates container">
        <h2>Мои ставки</h2>
        <table class="rates__list">
        <?php foreach ($bids as $bid) : ?>
            <?php
            [$hours, $minutes] = getDtRange($bid['date_exp'], new DateTime());
            $isWinner = (int)$bid['winner_id'] === (int)$user['id'];
            $isExp = $hours === '00' && $minutes === '00';
            ?>
            <tr class="rates__item 
            <?php if ($isWinner) : ?>
            <?= 'rates__item--win' ; ?>
            <?php elseif ($isExp) : ?>
            <?= 'rates__item--end' ; ?>
            <?php endif ; ?>
            ">
                <td class="rates__info">
                <div class="rates__img">
                    <img src="<?= $bid['img_url'] ; ?>" width="54" height="40" alt="<?= $bid['name'] ; ?>">
                </div>
                <div>
                    <h3 class="rates__title"><a href="/lot.php?id=<?= $bid['lot_id'] ; ?>"><?= $bid['name'] ; ?></a></h3>
                    <?php if ($isWinner) : ?>
                    <p><?= $bid['contacts'] ; ?></p>
                    <?php endif ; ?>
                </div>
                </td>
                <td class="rates__category">
                <?= $bid['category'] ; ?>
                </td>
                <td class="rates__timer">
                <div class="timer
                <?php if ($isWinner) : ?>
                <?= 'timer--win">' ; ?>
                    <?= 'Ставка выиграла' ; ?>
                <?php elseif ($isExp) : ?>
                <?= 'timer--end">' ; ?>
                    <?= 'Торги окончены' ; ?>
                <?php else : ?>
                <?= (int)$hours === 0 ? 'timer--finishing">' : '">' ; ?>
                    <?= $hours . ':' . $minutes ; ?>
                <?php endif ; ?>
                </div>
                </td>
                <td class="rates__price">
                <?= formatPrice($bid['amount']); ?>
                </td>
                <td class="rates__time">
                <?= getTimePassedAfterDate(
                    $bid['created_at'],
                    new DateTime(),
                ); ?>
                </td>
            </tr>
        <?php endforeach ; ?>
        </table>
    </section>
</main>
