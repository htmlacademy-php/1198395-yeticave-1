<?php

/**
 * @var array $lot ;
 * @var string $navContent ;
 * @var array $bids ;
 * @var int $price ;
 * @var int $minBid ;
 * @var bool $showBids ;
 * @var array $formInputs ;
 * @var array $errors ;
 */

?>

<main>
    <?= $navContent; ?>
    <section class="lot-item container">
        <h2><?= $lot['name'] ?? ''; ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img
                        src="<?= $lot['img_url'] ?? ''; ?>"
                        width="730"
                        height="548"
                        alt="<?= $lot['name'] ?? ''; ?>"
                    />
                </div>
                <p class="lot-item__category">Категория: <span><?= $lot['category'] ?? ''; ?></span></p>
                <p class="lot-item__description"><?= $lot['description'] ?? ''; ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state">
                    <?php [$hours, $minutes] = getDtRange($lot['date_exp'] ?? '', new DateTime()); ?>
                    <?php if ($hours === '00' && $minutes === '00' || isset($lot['winner_id'])) : ?>
                    <p>Торги окончены.</p>
                    <?php else : ?>
                    <div class="lot-item__timer <?= (int)$hours === 0 ? 'timer--finishing' : ''; ?>  timer">
                        <?= $hours; ?>:<?= $minutes; ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= formatPrice($price); ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= formatPrice($minBid); ?></span>
                        </div>
                    </div>
                    <?php endif ; ?>
                    <?php if ($showBids): ?>
                    <form
                        class="lot-item__form"
                        action="/lot.php?id=<?= $lot['id'] ?? '' ; ?>"
                        method="post"
                        autocomplete="off"
                    >
                        <p class="lot-item__form-item form__item <?= empty($errors) ? '' : 'form__item--invalid' ; ?>">
                            <label for="cost">Ваша ставка</label>
                            <input id="cost" type="text" name="cost"
                                   placeholder="<?= $minBid ; ?>" value="<?= empty($formInputs) ? '' : $formInputs['cost'] ?? '' ; ?>"/>
                            <?php if (!empty($errors)): ?>
                            <span class="form__error"><?= $errors['cost'] ?? '' ; ?></span>
                            <?php endif ; ?>
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                    </form>
                    <?php endif ; ?>
                </div>
                <div class="history">
                    <h3>История ставок (<span><?= count($bids); ?></span>)</h3>
                    <table class="history__list">
                        <?php foreach ($bids as $bid) : ?>
                            <?php if (isset($bid['user_name'], $bid['amount'], $bid['created_at'])) : ?>
                            <tr class="history__item">
                                <td class="history__name"><?= $bid['user_name']; ?></td>
                                <td class="history__price"><?= formatPrice($bid['amount']); ?></td>
                                <td class="history__time"><?= getTimePassedAfterDate(
                                    $bid['created_at'],
                                    new DateTime(),
                                ); ?></td>
                            </tr>
                            <?php endif ; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
