<?php

/**
 * @var string $navContent ;
 * @var string $searchInfo ;
 * @var array $lots ;
 * @var int $pages ;
 * @var int $page ;
 */

?>
<main>
<?= $navContent ; ?>
<div class="container">
    <section class="lots">
    <?php if ($searchInfo['isCatValid'] && $searchInfo['isTextValid'] && isset($searchInfo['text'], $searchInfo['catId'], $searchInfo['catName'])) : ?>
    <?php $searchLink = '/search.php?search=' . htmlspecialchars($searchInfo['text']) . '&cat=' . $searchInfo['catId'] ; ?>
    <h2>Все лоты в категории «<?= $searchInfo['catName'] ?>» по запросу «<span><?= htmlspecialchars($searchInfo['text']) ; ?></span>»</h2>
    <?php elseif ($searchInfo['isCatValid'] && isset($searchInfo['catId'], $searchInfo['catName'])) : ?>
    <?php $searchLink = '/search.php?&cat=' . $searchInfo['catId'] ; ?>
    <h2>Все лоты в категории «<?= $searchInfo['catName'] ?>»</h2>
    <?php else : ?>
    <?php $searchLink = '/search.php?search=' . htmlspecialchars($searchInfo['text'] ?? '') ; ?>
    <h2>Результаты поиска по запросу «<span><?= htmlspecialchars($searchInfo['text'] ?? '') ; ?></span>»</h2>
    <?php endif ; ?>
    <ul class="lots__list">
        <?php if (empty($lots)) : ?>
        <p>Ничего не найдено по вашему запросу.</p>
        <?php else : ?>
        <?php foreach ($lots as $lot) : ?>
        <?php if (isset($lot['img_url'], $lot['name'], $lot['category'], $lot['id'], $lot['price'], $lot['date_exp'])) : ?>
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
        <? endif ; ?>
        <?php endforeach ; ?>
        <?php endif ; ?>
    </ul>
    </section>
    <?php if ($pages > 1) : ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <a href="<?= $searchLink . '&page=' . ($page > 1 ? $page - 1 : $page) ; ?>">Назад</a>
        </li>
        <?php $i = 1 ; ?>
        <?php $page = $page ? $page : 1 ; ?>
        <?php while ($i <= $pages) : ?>
        <li class="pagination-item <?= $page === $i ? 'pagination-item-active' : '' ; ?>">
            <a href="<?= $searchLink . '&page=' . $i ; ?>"><?= $i ; ?></a>
        </li>
        <?php $i += 1 ; ?>
        <?php endwhile ; ?>
        <li class="pagination-item pagination-item-next">
            <a href="<?= $searchLink . '&page=' . ($page < $pages ? $page + 1 : $page) ; ?>">Вперед</a>
        </li>
    </ul>
    <?php endif ; ?>
</div>
</main>
