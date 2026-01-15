<?php

/**
 * @var string $navContent ;
 * @var string $text ;
 * @var array $lots ;
 * @var int $pages ;
 * @var int $page ;
 */

?>
<main>
<?= $navContent ; ?>
<div class="container">
    <section class="lots">
    <h2>Результаты поиска по запросу «<span><?= $text !== false ? htmlspecialchars($text) : '' ; ?></span>»</h2>
    <ul class="lots__list">
        <?php if (empty($lots)) : ?>
        <p>Ничего не найдено по вашему запросу.</p>
        <?php else : ?>
        <?php foreach ($lots as $lot) : ?>
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
        <?php endforeach ; ?>
        <?php endif ; ?>
    </ul>
    </section>
    <?php if ($pages > 1) : ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <a href="<?= "/search.php?search=$text&page=" . ($page > 1 ? $page - 1 : $page) ; ?>">Назад</a>
        </li>
        <?php $i = 1 ; ?>
        <?php while ($i <= $pages) : ?>
        <li class="pagination-item <?= $page === $i ? 'pagination-item-active' : '' ; ?>">
            <a href="<?= "/search.php?search=$text&page=$i" ; ?>"><?= $i ; ?></a>
        </li>
        <?php $i += 1 ; ?>
        <?php endwhile ; ?>
        <li class="pagination-item pagination-item-next">
            <a href="<?= "/search.php?search=$text&page=" . ($page < $pages ? $page + 1 : $page) ; ?>">Вперед</a>
        </li>
    </ul>
    <?php endif ; ?>
</div>
</main>
