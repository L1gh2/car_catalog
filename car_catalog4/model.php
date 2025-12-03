<?php
require_once 'db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: allmodels.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, name, image, description, price, year
        FROM models
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $model = $stmt->fetch();
} catch (PDOException $e) {
    $model = null;
}

if (!$model) {
    echo "Модель не найдена.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($model['name']) ?> — Benny's</title>
    <link rel="stylesheet" href="style.css?v=12">
</head>

<body id="modelPage">

<header>
    <div class="content">

        <nav>
            <ul>
                <li><button onclick="location.href='index.php'">Главная</button></li>
                <li><button onclick="location.href='allmodels.php'">Модели</button></li>
                <li><button onclick="location.href='about.php'">О компании</button></li>
            </ul>
        </nav>

        <div class="top-logo">
            <img src="img/benny-logo.webp" alt="">
        </div>

        <div class="model-hero">
            <h1><?= htmlspecialchars($model['name']) ?></h1>
        </div>

    </div>
</header>


<main class="model-wrapper">

    <div class="model-photo">
        <img src="<?= htmlspecialchars($model['image']) ?>" alt="<?= htmlspecialchars($model['name']) ?>">
    </div>

    <div class="model-info">

        <p class="model-description">
            <?= nl2br(htmlspecialchars($model['description'])) ?>
        </p>

        <div class="model-params">
            <div><span>Год выпуска:</span> <?= htmlspecialchars($model['year']) ?></div>
            <div><span>Цена:</span> $<?= htmlspecialchars($model['price']) ?></div>
        </div>

        <button class="btn-back" onclick="history.back()">⬅ Вернуться назад</button>
        <button class="btn-order" onclick="openOrder()">🚗 Заказать сейчас</button>

    </div>

</main>
<!-- МОДАЛКА ЗАКАЗА -->
<div id="orderModal" class="order-modal">
    <div class="order-box">
        <h2>Заказ модели</h2>
        <p>📞 Позвоните нам — <strong>+1 (555) 123-4567</strong></p>
        <p>или напишите в Telegram:</p>
        <a href="https://t.me/username" class="order-tg" target="_blank">@bennys_ls</a>

        <button class="order-close" onclick="closeOrder()">Закрыть</button>
    </div>
</div>

<!-- МОДАЛЬНОЕ ОКНО "ЗАКАЗ МОДЕЛИ" -->
<div id="orderModal" class="order-modal">
    <div class="order-box">
        <h2>ЗАКАЗ МОДЕЛИ</h2>

        <p>📞 Позвоните нам — <strong>+1 (555) 123-4567</strong></p>
        <p>или напишите в Telegram:</p>

        <a href="https://t.me/username" class="order-tg" target="_blank">@bennys_ls</a>

        <button class="order-close" onclick="closeOrder()">Закрыть</button>
    </div>
</div>

<script>
    function openOrder() {
        document.getElementById('orderModal').classList.add('show');
    }
    function closeOrder() {
        document.getElementById('orderModal').classList.remove('show');
    }
</script>



</body>
</html>




