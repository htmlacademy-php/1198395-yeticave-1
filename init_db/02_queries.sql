-- Вставка категорий.
USE yeticave;

SET NAMES 'utf8mb4';
SET CHARACTER SET 'utf8mb4';

INSERT INTO cats (name, class)
VALUES ('Доски и лыжи', 'boards'),
       ('Крепления', 'attachment'),
       ('Ботинки', 'boots'),
       ('Одежда', 'clothing'),
       ('Инструменты', 'tools'),
       ('Разное', 'other');

-- Создание тестовых юзеров.
INSERT INTO users (email, name, password, contacts)
VALUES ('a@mail.ru', 'Иван', '123qwe', 'vk/tg'),
       ('b@mail.ru', 'Джон', '123qwe', 'fb/tel'),
       ('c@mail.ru', 'Олег', '123qwe', 'tw'),
       ('d@mail.ru', 'Настя', '123qwe', 'tel:899999999');

-- Создание тестовых лотов.
INSERT INTO lots (created_at, name, description, img_url, price, date_exp, bid_step, user_id, cat_id)
VALUES ('2025-12-04 10:10:10', '2014 Rossignol District Snowboard', 'Описание', '/img/lot-1.jpg', 10999, '2026-02-01',
        1000, 1, 1),
       ('2025-12-04 11:10:10', 'DC Ply Mens 2016/2017 Snowboard', 'Описание', '/img/lot-2.jpg', 159999, '2027-12-14',
        1000, 2, 1),
       ('2025-12-04 12:10:10', 'Крепления Union Contact Pro 2015 года размер L/XL', 'Описание', '/img/lot-3.jpg', 8000,
        '2026-02-06', 1000, 3, 2),
       ('2025-12-04 13:10:10', 'Ботинки для сноуборда DC Mutiny Charcoal', 'Описание', '/img/lot-4.jpg', 10999,
        '2026-01-30', 1000, 1, 3),
       ('2025-12-04 14:10:10', 'Куртка для сноуборда DC Mutiny Charcoal', 'Описание', '/img/lot-5.jpg', 7500,
        '2026-01-20', 1000, 2, 4),
       ('2025-12-04 15:10:10', 'Маска Oakley Canopy', 'Описание', '/img/lot-6.jpg', 5400, '2026-01-31', 1000, 4, 6);

-- Создание тестовых ставок.
INSERT INTO bids (created_at, amount, user_id, lot_id)
VALUES ('2025-12-04 16:10:10', 13000, 1, 1),
       ('2025-12-04 16:11:10', 14000, 2, 1),
       ('2025-12-04 16:12:10', 15000, 3, 1),
       ('2025-12-04 16:13:10', 180000, 1, 2),
       ('2025-12-04 16:14:10', 190000, 2, 2),
       ('2025-12-04 16:15:10', 195000, 3, 2),
       ('2025-12-04 16:16:10', 10000, 4, 3),
       ('2025-12-04 16:17:10', 11000, 1, 4),
       ('2025-12-04 16:18:10', 10000, 2, 5),
       ('2025-12-04 16:19:10', 6000, 2, 6);

-- Получить все категории.
SELECT name
FROM cats;

-- Получить самые новые, открытые лоты. Каждый лот включает название, стартовую цену, ссылку на изображение, текущую максимальную ставку, название категории.
SELECT l.name, l.price, l.img_url, MAX(b.amount) AS max_price, c.name
FROM lots l
       LEFT JOIN bids b
                 ON b.lot_id = l.id
       JOIN cats c
            ON l.cat_id = c.id
GROUP BY l.id, l.created_at
ORDER BY l.created_at DESC
LIMIT 5;

-- Показать лот по id. Получить также название категории, к которой принадлежит лот.
SELECT lots.*, cats.name
FROM lots
       JOIN cats
            ON lots.id = cats.id
WHERE lots.id = 1;

-- Обновить название лота по его идентификатору.
UPDATE lots
SET lots.name = 'Очки Oakley Canopy'
WHERE lots.id = 6;

-- Получить список ставок для лота по его идентификатору с сортировкой по дате.
SELECT *
FROM bids
WHERE lot_id = 1
ORDER BY created_at DESC;
