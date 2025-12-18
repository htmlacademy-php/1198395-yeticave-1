CREATE DATABASE IF NOT EXISTS yeticave
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE yeticave;

CREATE TABLE IF NOT EXISTS cats
(
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(128) NOT NULL,
  class VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS users
(
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  email      VARCHAR(128) NOT NULL UNIQUE,
  name       VARCHAR(128) NOT NULL,
  password   CHAR(60)     NOT NULL,
  contacts   TEXT         NOT NULL
);

CREATE TABLE IF NOT EXISTS lots
(
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  name        VARCHAR(128) NOT NULL,
  description TEXT         NOT NULL,
  img_url     VARCHAR(128) NOT NULL,
  price       INT UNSIGNED NOT NULL,
  date_exp    DATE         NOT NULL,
  bid_step    INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NOT NULL,
  winner_id   INT UNSIGNED NULL,
  cat_id      INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (winner_id) REFERENCES users (id),
  FOREIGN KEY (cat_id) REFERENCES cats (id)
);

CREATE TABLE IF NOT EXISTS bids
(
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  amount     INT UNSIGNED NOT NULL,
  user_id    INT UNSIGNED NOT NULL,
  lot_id     INT UNSIGNED NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users (id),
  FOREIGN KEY (lot_id) REFERENCES lots (id)
);

CREATE INDEX u_date ON users (created_at);
CREATE INDEX u_email ON users (email);
CREATE INDEX l_date ON lots (created_at);
CREATE INDEX l_name ON lots (name);
CREATE INDEX b_date ON bids (created_at);
