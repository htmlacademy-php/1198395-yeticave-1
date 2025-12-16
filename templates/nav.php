<?php
/**
 * @var $cats ;
 */

?>

<nav class="nav">
    <ul class="nav__list container">
        <?php foreach ($cats as $cat) : ?>
            <li class="nav__item">
                <a href="/index.php"><?= $cat['name']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
