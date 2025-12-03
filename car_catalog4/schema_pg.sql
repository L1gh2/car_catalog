-- УДАЛЯЕМ старые таблицы, если они есть
DROP TABLE IF EXISTS cars;
DROP TABLE IF EXISTS models;
DROP TABLE IF EXISTS manufacturers;
DROP TABLE IF EXISTS users;

-- =============================
-- Таблица производителей
-- =============================
CREATE TABLE manufacturers (
                               id   SERIAL PRIMARY KEY,
                               name VARCHAR(100) NOT NULL
);

-- =============================
-- Таблица моделей
-- =============================
CREATE TABLE models (
                        id              SERIAL PRIMARY KEY,
                        name            VARCHAR(100) NOT NULL,
                        manufacturer_id INT NOT NULL REFERENCES manufacturers(id) ON DELETE CASCADE,
                        year            INT,
                        image           VARCHAR(255),
                        description     TEXT,
                        price           NUMERIC(10,2),
                        engine_volume   NUMERIC(4,1)
);

-- =============================
-- Таблица автомобилей по годам
-- =============================
CREATE TABLE cars (
                      id              SERIAL PRIMARY KEY,
                      model           VARCHAR(100) NOT NULL,
                      manufacturer_id INT NOT NULL REFERENCES manufacturers(id) ON DELETE CASCADE,
                      start_year      INT,
                      end_year        INT
);

-- =============================
-- Пользователи (админка)
-- =============================
CREATE TABLE users (
                       id       SERIAL PRIMARY KEY,
                       login    VARCHAR(50) UNIQUE NOT NULL,
                       password VARCHAR(255) NOT NULL,
                       role     VARCHAR(16) NOT NULL DEFAULT 'admin'
);

-- =============================
-- НАЧАЛЬНЫЕ ДАННЫЕ
-- =============================

INSERT INTO manufacturers (name) VALUES
                                     ('Bravado'),
                                     ('Karin'),
                                     ('Benefactor');

INSERT INTO models (name, manufacturer_id, year, image, description, price, engine_volume)
VALUES
    ('Bravado Banshee', 1, 2015, 'img/Bravado Banshee.png',
     'Легендарный спорткар, идеален для кастома в стиле Benny''s.',
     95000.00, 4.0),
    ('Karin Sultan RS', 2, 2013, 'img/Karin Sultan RS.jpg',
     'Культовая машина для тюнинга, гибрид ралли и стритрейсинга.',
     80000.00, 3.5),
    ('Benefactor Schafter V12', 3, 2016, 'img/Benefactor Schafter V12.jpg',
     'Бизнес-седан с бешеным V12 под капотом.',
     120000.00, 5.5);

INSERT INTO cars (model, manufacturer_id, start_year, end_year)
VALUES
    ('Bravado Banshee', 1, 2013, 2020),
    ('Karin Sultan', 2, 2010, 2018),
    ('Benefactor Schafter', 3, 2012, 2021);

INSERT INTO users (login, password, role)
VALUES ('admin', 'admin', 'admin');