<?php
// about.php — страница "О компании / о мастерской"
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Benny's — О мастерской</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=9">
</head>
<body id="aboutPage">

<header>
    <div class="content">

        <!-- Навигация -->
        <nav>
            <ul>
                <li><button onclick="location.href='index.php'">Главная</button></li>
                <li><button onclick="location.href='allmodels.php'">Модели</button></li>
                <li><button onclick="location.href='about.php'">О компании</button></li>
            </ul>
        </nav>

        <!-- Логотип -->
        <div class="top-logo">
            <img src="img/benny-logo.webp" alt="Benny's Original Motorworks">
        </div>

        <!-- HERO-блок about -->
        <div class="header-block">
            <div class="text" style="text-align:center; width:100%;">
                <h2 style="margin-bottom: 14px;">
                    <span>ABOUT</span> BENNY'S<br> MOTORWORKS
                </h2>
                <p style="max-width: 560px; margin: 0 auto;">
                    Мастерская, где обычные тачки превращаются в культовые проекты:
                    lowrider’ы, дрифткары, трековые монстры и редкие рестомоды —
                    всё в стиле Los Santos.
                </p>
            </div>
        </div>

    </div>
</header>

<main class="page-content">

    <!-- Основной текст "О мастерской" -->
    <section class="about-section">
        <h1 class="about-title">О МАСТЕРСКОЙ</h1>

        <div class="about-text">
            <p>
                Здесь ты можешь собрать свой проект: от олдскульного lowrider до
                безумного трекового монстра. Мы фиксируем модели, годы выпуска,
                производителей и примерный ценник тюнинга.
            </p>

            <p>
                Каталог на сайте — это база твоих идей. Смотри, какие машины есть,
                как они выглядят после кастома и сколько примерно может стоить проект.
            </p>

            <p>
                Используй раздел «Модели», чтобы подобрать машину мечты, а потом уже
                решай, какой стиль ей больше всего подходит: стрит, шоу-кар,
                daily-дрип или чистый олдскул.
            </p>
        </div>
    </section>

</main>

</body>
</html>
