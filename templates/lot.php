<?php

/**
 * @var $lot ;
 * @var $navContent ;
 * @var $bids ;
 */

?>

<main>
    <?= $navContent; ?>
    <section class="lot-item container">
        <h2><?= $lot['name']; ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img
                        src="<?= $lot['img_url']; ?>"
                        width="730"
                        height="548"
                        alt="<?= $lot['name']; ?>"
                    />
                </div>
                <p class="lot-item__category">Категория: <span><?= $lot['category']; ?></span></p>
                <p class="lot-item__description"><?= $lot['description']; ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state">
                    <?php [$hours, $minutes] = getDtRange($lot['date_exp'], new DateTime()); ?>
                    <div class="lot-item__timer <?= (int)$hours === 0 ? 'timer--finishing' : ''; ?>  timer">
                        <?= $hours; ?>:<?= $minutes; ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <?php $lotCurrentPrice = $lot['max_price'] ?? $lot['price']; ?>
                            <span class="lot-item__cost"><?= formatPrice($lotCurrentPrice); ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= formatPrice((int)$lotCurrentPrice + (int)$lot['bid_step']); ?></span>
                        </div>
                    </div>
                    <form
                        class="lot-item__form"
                        action="https://echo.htmlacademy.ru"
                        method="post"
                        autocomplete="off"
                    >
                        <p class="lot-item__form-item form__item form__item--invalid">
                            <label for="cost">Ваша ставка</label>
                            <input id="cost" type="text" name="cost"
                                   placeholder="<?= (int)$lotCurrentPrice + (int)$lot['bid_step']; ?>"/>
                            <span class="form__error">Введите наименование лота</span>
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                    </form>
                </div>
                <div class="history">
                    <h3>История ставок (<span><?= count($bids); ?></span>)</h3>
                    <table class="history__list">
                        <?php foreach ($bids as $bid) : ?>
                            <tr class="history__item">
                                <td class="history__name"><?= $bid['user_name']; ?></td>
                                <td class="history__price"><?= formatPrice($bid['amount']); ?></td>
                                <td class="history__time"><?= getTimePassedAfterDate(
                                        $bid['created_at'],
                                        new DateTime()
                                    ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </section>
</main>
