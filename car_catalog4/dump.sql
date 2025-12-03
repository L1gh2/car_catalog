-- Создаём базу (если нужно)
CREATE DATABASE IF NOT EXISTS car_catalog
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE car_catalog;

-- =========================
-- Таблица производителей
-- =========================
CREATE TABLE IF NOT EXISTS manufacturers (
                                             id   INT AUTO_INCREMENT PRIMARY KEY,
                                             name VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Таблица моделей (models)
-- ВАЖНО: сюда добавляем
--  image, description, price, engine_volume
-- =========================
CREATE TABLE IF NOT EXISTS models (
                                      id              INT AUTO_INCREMENT PRIMARY KEY,
                                      name            VARCHAR(100) NOT NULL,
    manufacturer_id INT NOT NULL,
    year            INT,
    image           VARCHAR(255),       -- путь к картинке (например img/bmw_m3.png)
    description     TEXT,
    price           DECIMAL(10,2),      -- цена, например 35000.00
    engine_volume   DECIMAL(4,1),       -- объём, например 3.5
    CONSTRAINT fk_models_manufacturer
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Таблица cars (для скриптов cars.php, cars_by_period.php)
-- =========================
CREATE TABLE IF NOT EXISTS cars (
                                    id              INT AUTO_INCREMENT PRIMARY KEY,
                                    model           VARCHAR(100) NOT NULL,
    manufacturer_id INT NOT NULL,
    start_year      INT,
    end_year        INT,
    CONSTRAINT fk_cars_manufacturer
    FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id)
    ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- Таблица пользователей (только admin)
-- =========================
CREATE TABLE IF NOT EXISTS users (
                                     id       INT AUTO_INCREMENT PRIMARY KEY,
                                     login    VARCHAR(50)  NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role     ENUM('admin') DEFAULT 'admin'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Один простой админ: логин admin, пароль admin
-- (для продакшена так делать нельзя, но для учебного проекта ок)
INSERT INTO users (login, password, role)
VALUES ('admin', 'admin', 'admin')
    ON DUPLICATE KEY UPDATE login = login;

-- =========================
-- Немного тестовых данных
-- =========================

INSERT INTO manufacturers (name) VALUES
                                     ('Bravado'),
                                     ('Karin'),
                                     ('Benefactor')
    ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Привяжем id производителей (можно не заморачиваться, это только пример)
-- предполагаем, что:
-- 1 = Bravado, 2 = Karin, 3 = Benefactor

INSERT INTO models (name, manufacturer_id, year, image, description, price, engine_volume)
VALUES
    ('Bravado Banshee', 1, 2015, 'img/banshee.png',
     'Легендарный спорткар, идеален для кастома в стиле Benny''s.',
     95000.00, 4.0),
    ('Karin Sultan RS', 2, 2013, 'img/sultan_rs.png',
     'Культовая машина для тюнинга, гибрид ралли и стритрейсинга.',
     80000.00, 3.5),
    ('Benefactor Schafter V12', 3, 2016, 'img/schafter_v12.png',
     'Бизнес-седан с бешеным V12 под капотом.',
     120000.00, 5.5);

INSERT INTO cars (model, manufacturer_id, start_year, end_year)
VALUES
    ('Bravado Banshee', 1, 2013, 2020),
    ('Karin Sultan', 2, 2010, 2018),
    ('Benefactor Schafter', 3, 2012, 2021);
