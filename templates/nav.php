<?php

/**
 * @var array $cats ;
 */

?>

<nav class="nav">
    <ul class="nav__list container">
        <?php foreach ($cats as $cat) : ?>
            <li class="nav__item">
                <a href="/search.php?cat=<?= $cat['id'] ; ?>"><?= $cat['name']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
