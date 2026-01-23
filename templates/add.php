<?php

/**
 * @var string $navContent ;
 * @var array $cats ;
 * @var array $errors ;
 * @var array $formInputs ;
 */

?>
<main>
    <?= $navContent; ?>
    <form class="form form--add-lot container <?= empty($errors) ? '' : 'form--invalid'; ?>" action="/add.php"
          method="post" enctype="multipart/form-data">
        <h2>Добавление лота</h2>
        <div class="form__container-two">
            <div class="form__item <?= isset($errors['lot-name']) ? 'form__item--invalid' : ''; ?>">
                <label for="lot-name">Наименование <sup>*</sup></label>
                <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота"
                       value="<?= $formInputs['lot-name'] ?? ''; ?>">
                <span class="form__error"><?= $errors['lot-name'] ?? ''; ?></span>
            </div>
            <div class="form__item <?= isset($errors['category']) ? 'form__item--invalid' : ''; ?>">
                <label for="category">Категория <sup>*</sup></label>
                <select id="category" name="category">
                    <option>Выберите категорию</option>
                    <?php foreach ($cats as $category) : ?>
                        <option
                            value="<?= $category['id'] ?? 1; ?>" <?= isset($formInputs['category'], $category['id']) && $formInputs['category'] === $category['id'] ? 'selected' : ''; ?>>
                            <?= $category['name'] ?? ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form__error"><?= $errors['category'] ?? ''; ?></span>
            </div>
        </div>
        <div class="form__item form__item--wide <?= isset($errors['description']) ? 'form__item--invalid' : ''; ?>">
            <label for="message">Описание <sup>*</sup></label>
            <textarea id="message" name="description"
                      placeholder="Напишите описание лота"><?= $formInputs['description'] ?? ''; ?></textarea>
            <span class="form__error"><?= $errors['description'] ?? ''; ?></span>
        </div>
        <div class="form__item form__item--file <?= isset($errors['lot-img']) ? 'form__item--invalid' : ''; ?>">
            <label>Изображение <sup>*</sup></label>
            <div class="form__input-file">
                <input class="visually-hidden" name="lot-img" type="file" id="lot-img" value="">
                <label for="lot-img">
                    Добавить
                </label>
            </div>
            <span class="form__error"><?= $errors['lot-img'] ?? ''; ?></span>
        </div>
        <div class="form__container-three">
            <div class="form__item form__item--small <?= isset($errors['lot-price']) ? 'form__item--invalid' : ''; ?>">
                <label for="lot-rate">Начальная цена <sup>*</sup></label>
                <input id="lot-rate" type="text" name="lot-price" placeholder="0"
                       value="<?= $formInputs['lot-price'] ?? ''; ?>">
                <span class="form__error"><?= $errors['lot-price'] ?? ''; ?></span>
            </div>
            <div class="form__item form__item--small <?= isset($errors['lot-step']) ? 'form__item--invalid' : ''; ?>">
                <label for="lot-step">Шаг ставки <sup>*</sup></label>
                <input id="lot-step" type="text" name="lot-step" placeholder="0"
                       value="<?= $formInputs['lot-step'] ?? ''; ?>">
                <span class="form__error"><?= $errors['lot-step'] ?? ''; ?></span>
            </div>
            <div class="form__item <?= isset($errors['lot-date']) ? 'form__item--invalid' : ''; ?>">
                <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
                <input class="form__input-date" id="lot-date" type="text" name="lot-date"
                       placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= $formInputs['lot-date'] ?? ''; ?>">
                <span class="form__error"><?= $errors['lot-date'] ?? ''; ?></span>
            </div>
        </div>
        <?= empty($errors) ? '' : '<span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>' ?>
        <button type="submit" class="button">Добавить лот</button>
    </form>
</main>
