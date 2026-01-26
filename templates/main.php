<?php

/**
 * @var array $cats
 * @var array $lots
 */

?>
<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное
            снаряжение.</p>
        <ul class="promo__list">
            <?php foreach ($cats as $cat) : ?>
                <?php if (isset($cat['class'], $cat['id'], $cat['name'])) : ?>
                <li class="promo__item promo__item--<?= $cat['class']; ?>">
                    <a class="promo__link" href="/search.php?cat=<?= $cat['id'] ; ?>"><?= $cat['name']; ?></a>
                </li>
                <?php endif ; ?>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <?php foreach ($lots as $lot) : ?>
                <?php if (isset($lot['img_url'], $lot['category'], $lot['id'], $lot['name'], $lot['price'], $lot['date_exp'])) : ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?= htmlspecialchars($lot['img_url']); ?>" width="350" height="260" alt="<?= htmlspecialchars(
                            $lot['name'],
                        ); ?>">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= htmlspecialchars($lot['category']); ?></span>
                        <h3 class="lot__title"><a class="text-link"
                                                  href="/lot.php?id=<?= $lot['id']; ?>"><?= htmlspecialchars(
                                                      $lot['name'],
                                                  ); ?></a></h3>
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">Стартовая цена</span>
                                <span class="lot__cost"><?= formatPrice($lot['price']); ?></span>
                            </div>
                            <?php [$hours, $minutes] = getDtRange($lot['date_exp'], new DateTime()); ?>
                            <div class="<?= (int)$hours === 0 ? 'timer--finishing' : ''; ?> lot__timer timer">
                                <?= $hours; ?>:<?= $minutes; ?>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endif ; ?>
            <?php endforeach; ?>
        </ul>
    </section>
</main>
