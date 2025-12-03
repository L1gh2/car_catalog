<?php
session_start();
require_once __DIR__ . '/../db.php';

// Только админы
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('Неверный ID модели.');
}

$messages = [];
$errors   = [];

/* Загружаем производителей (если есть) */
$manufacturers = [];
try {
    $stmt = $conn->query('SELECT id, name FROM manufacturers ORDER BY name ASC');
    $manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ок
}

/* Загружаем модель */
try {
    $stmt = $conn->prepare('SELECT * FROM models WHERE id = ?');
    $stmt->execute([$id]);
    $model = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$model) {
        die('Модель не найдена.');
    }
} catch (PDOException $e) {
    die('Ошибка БД: ' . $e->getMessage());
}

/* Обработка сохранения */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name           = trim($_POST['name'] ?? '');
    $manufacturerId = (int)($_POST['manufacturer_id'] ?? 0);
    $year           = (int)($_POST['year'] ?? 0);
    $price          = $_POST['price'] ?? null;
    $engineVolume   = $_POST['engine_volume'] ?? null;
    $description    = trim($_POST['description'] ?? '');
    $imagePath      = $model['image'];

    if ($name === '') {
        $errors[] = 'Введите название модели.';
    }


    // Загрузка изображения (добавление модели)
    $imagePath = null;

    // Новое изображение (опционально)
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/../img/';   // а не '/img/'

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $originalName = $_FILES['image']['name'];
        $ext  = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $base = preg_replace('~[^a-zA-Z0-9_-]+~', '_', pathinfo($originalName, PATHINFO_FILENAME));
        if ($base === '') {
            $base = 'car';
        }

        $fileName = $base . '.' . $ext;
        if (file_exists($uploadDir . $fileName)) {
            $fileName = $base . '_' . time() . '.' . $ext;
        }

        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // удаляем старый файл, если был
            if (!empty($model['image'])) {
                $oldPath = __DIR__ . '/../' . $model['image'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $imagePath = 'img/' . $fileName;
        } else {
            $errors[] = 'Не удалось загрузить изображение.';
        }
    }



    if (!$errors) {
        try {
            $stmt = $conn->prepare("
                UPDATE models
                SET name = :name,
                    manufacturer_id = :manufacturer_id,
                    year = :year,
                    image = :image,
                    description = :description,
                    price = :price,
                    engine_volume = :engine_volume
                WHERE id = :id
            ");

            $stmt->execute([
                ':name'            => $name,
                ':manufacturer_id' => $manufacturerId ?: 1,
                ':year'            => $year ?: null,
                ':image'           => $imagePath,
                ':description'     => $description,
                ':price'           => $price !== '' ? $price : null,
                ':engine_volume'   => $engineVolume !== '' ? $engineVolume : null,
                ':id'              => $id,
            ]);

            $messages[] = 'Изменения сохранены.';
            // Обновим $model, чтобы форма показала свежие данные
            $stmt = $conn->prepare('SELECT * FROM models WHERE id = ?');
            $stmt->execute([$id]);
            $model = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = 'Ошибка БД при сохранении: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование модели — Benny&apos;s</title>
    <link rel="stylesheet" href="style.css?v=16">
</head>
<body>

<div style="max-width:900px;margin:40px auto 80px;padding:28px 30px;border-radius:20px;
            background:rgba(5,3,15,.88);box-shadow:0 18px 40px rgba(0,0,0,.6);color:#fff;">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h1 style="font-family:'Barlow Condensed',sans-serif;text-transform:uppercase;
                   letter-spacing:.18em;font-size:26px;">
            Редактирование модели
        </h1>
        <a href="admin.php" style="color:#ffb5f9;font-size:14px;">← в админку</a>
    </div>

    <div style="font-size:14px;margin-bottom:10px;color:rgba(255,255,255,.8);">
        ID: <?= (int)$model['id'] ?> · <?= htmlspecialchars($model['name']) ?>
    </div>

    <?php if ($messages): ?>
        <div style="background:rgba(0,180,80,.18);border-radius:10px;padding:10px 14px;
                    font-size:14px;margin-bottom:14px;">
            <?php foreach ($messages as $m): ?>
                <div>✔ <?= htmlspecialchars($m) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div style="background:rgba(255,0,80,.18);border-radius:10px;padding:10px 14px;
                    font-size:14px;margin-bottom:14px;">
            <?php foreach ($errors as $e): ?>
                <div>✖ <?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label>Название модели</label><br>
        <input type="text" name="name" required
               value="<?= htmlspecialchars($model['name']) ?>"
               style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                      border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                      color:#fff;"><br>

        <?php if ($manufacturers): ?>
            <label>Производитель</label><br>
            <select name="manufacturer_id"
                    style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                           border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                           color:#fff;">
                <?php foreach ($manufacturers as $man): ?>
                    <option value="<?= (int)$man['id'] ?>"
                        <?= $man['id'] == $model['manufacturer_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($man['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>
        <?php else: ?>
            <label>ID производителя</label><br>
            <input type="number" name="manufacturer_id"
                   value="<?= (int)$model['manufacturer_id'] ?>"
                   style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                          border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                          color:#fff;"><br>
        <?php endif; ?>

        <div style="display:flex;gap:10px;">
            <div style="flex:1;">
                <label>Год</label><br>
                <input type="number" name="year"
                       value="<?= htmlspecialchars($model['year']) ?>"
                       style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                              border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                              color:#fff;">
            </div>
            <div style="flex:1;">
                <label>Цена ($)</label><br>
                <input type="number" step="0.01" name="price"
                       value="<?= htmlspecialchars($model['price']) ?>"
                       style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                              border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                              color:#fff;">
            </div>
            <div style="flex:1;">
                <label>Объём (л)</label><br>
                <input type="number" step="0.1" name="engine_volume"
                       value="<?= htmlspecialchars($model['engine_volume']) ?>"
                       style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                              border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                              color:#fff;">
            </div>
        </div>

        <label>Описание</label><br>
        <textarea name="description" rows="5"
                  style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                         border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                         color:#fff;resize:vertical;"><?= htmlspecialchars($model['description']) ?></textarea><br>

        <label>Текущее изображение</label><br>
        <?php if (!empty($model['image'])): ?>
            <div style="margin:6px 0 12px;">
                <img src="<?= htmlspecialchars($model['image']) ?>" alt=""
                     style="max-width:240px;border-radius:10px;box-shadow:0 10px 20px rgba(0,0,0,.5);">
            </div>
        <?php else: ?>
            <p style="font-size:13px;color:rgba(255,255,255,.7);margin:4px 0 12px;">
                Изображение не задано.
            </p>
        <?php endif; ?>

        <label>Заменить изображение (по желанию)</label><br>
        <input type="file" name="image" accept="image/*"
               style="margin:4px 0 18px;color:#fff;"><br>

        <button type="submit"
                style="border-radius:999px;padding:10px 26px;border:none;
                       background:linear-gradient(120deg,#ff007f,#7b2cff);
                       color:#fff;text-transform:uppercase;letter-spacing:.12em;
                       font-size:13px;cursor:pointer;">
            Сохранить изменения
        </button>
    </form>

</div>

</body>
</html>

