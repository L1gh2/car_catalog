<?php
require_once 'db.php';

// --- СОРТИРОВКА ---
$sort = $_GET['sort'] ?? 'default';

switch ($sort) {
    case 'price_asc':
        $orderBy = 'price ASC';
        break;
    case 'price_desc':
        $orderBy = 'price DESC';
        break;
    case 'year_new':
        $orderBy = 'year DESC';
        break;
    case 'year_old':
        $orderBy = 'year ASC';
        break;
    default:
        $orderBy = 'id ASC';
}

// Загружаем модели из БД с учётом сортировки
try {
    $stmt = $conn->query("
        SELECT id, name, price, image, year
        FROM models
        ORDER BY $orderBy
    ");
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $models = [];
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Benny's — Все модели</title>
    <link rel="stylesheet" href="style.css?v=14">
</head>
<body class="city-page">

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
        <img src="img/benny-logo.webp" alt="Benny's Motorworks">
    </div>

</div>

<main class="city-list">

    <h1>ТОРГОВЫЙ ЗАЛ</h1>
    <p>Выбирай свой проект: классика, muscle, JDM, люкс.</p>

    <!-- Сортировка -->
    <div class="sort-wrapper">
        <form method="get">
            <label for="sort">СОРТИРОВКА:</label>
            <select name="sort" id="sort" onchange="this.form.submit()">
                <option value="default"   <?= $sort === 'default'   ? 'selected' : '' ?>>По умолчанию</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена ↑</option>
                <option value="price_desc"<?= $sort === 'price_desc'? 'selected' : '' ?>>Цена ↓</option>
                <option value="year_new"  <?= $sort === 'year_new'  ? 'selected' : '' ?>>Сначала новые</option>
                <option value="year_old"  <?= $sort === 'year_old'  ? 'selected' : '' ?>>Сначала старые</option>
            </select>
        </form>
    </div>

    <!-- Карточки моделей -->
    <div class="benny-grid">
        <?php foreach ($models as $model): ?>
            <a class="benny-card-link" href="model.php?id=<?= htmlspecialchars($model['id']) ?>">
                <article class="benny-card">
                    <div class="benny-card-photo">
                        <img src="<?= htmlspecialchars($model['image']) ?>" alt="<?= htmlspecialchars($model['name']) ?>">
                    </div>
                    <div class="benny-card-info">
                        <span class="benny-card-name"><?= htmlspecialchars($model['name']) ?></span>
                        <span class="benny-card-price">$<?= htmlspecialchars($model['price']) ?></span>
                    </div>
                </article>
            </a>
        <?php endforeach; ?>
    </div>

</main>

</body>
</html>







