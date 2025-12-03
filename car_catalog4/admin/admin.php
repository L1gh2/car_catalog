<?php
session_start();
require_once __DIR__ . '/../db.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
// Защита: только админ
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

$messages = [];
$errors   = [];

/* ===== Список производителей (если есть) ===== */
$manufacturers = [];
try {
    $stmt = $conn->query('SELECT id, name FROM manufacturers ORDER BY name ASC');
    $manufacturers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // если таблицы нет — просто ничего
}

/* ===== ОБРАБОТКА ФОРМ ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    /* --- Добавление модели --- */
    if ($action === 'add_model') {
        $name           = trim($_POST['name'] ?? '');
        $manufacturerId = (int)($_POST['manufacturer_id'] ?? 0);
        $year           = (int)($_POST['year'] ?? 0);
        $price          = $_POST['price'] ?? null;
        $engineVolume   = $_POST['engine_volume'] ?? null;
        $description    = trim($_POST['description'] ?? '');

        $imagePath = null;

        if ($name === '') {
            $errors[] = 'Введите название модели.';
        }

        // Загрузка изображения (добавление модели)
        $imagePath = null;

        if (!empty($_FILES['image']['name'])) {
            // Папка img в КОРНЕ сайта, а не в /admin
            $uploadDir = __DIR__ . '/../img/';

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
                // В БД кладём путь ОТ КОРНЯ сайта
                $imagePath = 'img/' . $fileName;
            } else {
                $errors[] = 'Не удалось загрузить изображение.';
            }
        }


        if (!$errors) {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO models (name, manufacturer_id, year, image, description, price, engine_volume)
                    VALUES (:name, :manufacturer_id, :year, :image, :description, :price, :engine_volume)
                ");

                $stmt->execute([
                        ':name'            => $name,
                        ':manufacturer_id' => $manufacturerId ?: 1,
                        ':year'            => $year ?: null,
                        ':image'           => $imagePath,
                        ':description'     => $description,
                        ':price'           => $price !== '' ? $price : null,
                        ':engine_volume'   => $engineVolume !== '' ? $engineVolume : null,
                ]);

                $messages[] = 'Модель добавлена.';
            } catch (PDOException $e) {
                $errors[] = 'Ошибка БД при добавлении модели: ' . $e->getMessage();
            }
        }
    }

    /* --- Добавление администратора --- */
    if ($action === 'add_admin') {
        $login    = trim($_POST['login'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if ($login === '' || $password === '') {
            $errors[] = 'Укажите логин и пароль для нового администратора.';
        } else {
            try {
                $stmt = $conn->prepare('SELECT id FROM users WHERE login = ? LIMIT 1');
                $stmt->execute([$login]);
                if ($stmt->fetch()) {
                    $errors[] = 'Пользователь с таким логином уже существует.';
                } else {
                    $stmt = $conn->prepare("
                        INSERT INTO users (login, password, role)
                        VALUES (:login, :password, 'admin')
                    ");

                    // Пока храним пароль открытым текстом (как в дампе)
                    $stmt->execute([
                            ':login'    => $login,
                            ':password' => $password
                    ]);

                    $messages[] = 'Администратор добавлен.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Ошибка БД при добавлении администратора: ' . $e->getMessage();
            }
        }
    }

    /* --- Удаление модели --- */
    if ($action === 'delete_model') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            try {
                // Найдём модель, чтобы удалить её картинку
                $stmt = $conn->prepare('SELECT image FROM models WHERE id = ?');
                $stmt->execute([$id]);
                $model = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($model) {
                    // Удаляем строку из БД
                    $del = $conn->prepare('DELETE FROM models WHERE id = ?');
                    $del->execute([$id]);

                    // Удаляем файл картинки (если он внутри img/)
                    if (!empty($model['image'])) {
                        $path = __DIR__ . '/' . $model['image'];
                        if (is_file($path)) {
                            @unlink($path);
                        }
                    }

                    $messages[] = 'Модель удалена.';
                } else {
                    $errors[] = 'Модель не найдена.';
                }
            } catch (PDOException $e) {
                $errors[] = 'Ошибка БД при удалении модели: ' . $e->getMessage();
            }
        }
    }
}

/* ===== Список моделей для таблицы ===== */

$modelsList = [];
try {
    $stmt = $conn->query("
        SELECT id, name, year, price
        FROM models
        ORDER BY id DESC
    ");
    $modelsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = 'Ошибка БД при загрузке списка моделей: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Benny's — Админка</title>
    <link rel="stylesheet" href="style.css?v=16">
</head>
<body>

<div style="max-width:1200px;margin:40px auto 80px;padding:28px 30px;border-radius:20px;
            background:rgba(5,3,15,.88);box-shadow:0 18px 40px rgba(0,0,0,.6);color:#fff;">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <h1 style="font-family:'Barlow Condensed',sans-serif;text-transform:uppercase;
                   letter-spacing:.18em;font-size:28px;">
            Benny&apos;s Admin Panel
        </h1>
        <div style="font-size:14px;">
            <?= htmlspecialchars($_SESSION['login'] ?? 'admin') ?> ·
            <a href="logout.php" style="color:#ffb5f9;">Выйти</a>
        </div>
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

    <div style="display:flex;gap:26px;flex-wrap:wrap;margin-bottom:30px;">

        <!-- Форма добавления модели -->
        <section style="flex:1 1 420px;background:rgba(15,8,40,.9);border-radius:16px;
                        padding:20px 22px;">
            <h2 style="font-family:'Barlow Condensed',sans-serif;text-transform:uppercase;
                       letter-spacing:.16em;font-size:20px;margin-bottom:12px;">
                Добавить модель
            </h2>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_model">

                <label>Название модели</label><br>
                <input type="text" name="name" required
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
                            <option value="<?= (int)$man['id'] ?>">
                                <?= htmlspecialchars($man['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                <?php else: ?>
                    <label>ID производителя</label><br>
                    <input type="number" name="manufacturer_id" value="1"
                           style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                                  border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                                  color:#fff;"><br>
                <?php endif; ?>

                <div style="display:flex;gap:10px;">
                    <div style="flex:1;">
                        <label>Год</label><br>
                        <input type="number" name="year"
                               style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                                      border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                                      color:#fff;">
                    </div>
                    <div style="flex:1;">
                        <label>Цена ($)</label><br>
                        <input type="number" step="0.01" name="price"
                               style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                                      border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                                      color:#fff;">
                    </div>
                    <div style="flex:1;">
                        <label>Объём (л)</label><br>
                        <input type="number" step="0.1" name="engine_volume"
                               style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                                      border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                                      color:#fff;">
                    </div>
                </div>

                <label>Описание</label><br>
                <textarea name="description" rows="4"
                          style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                                 border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                                 color:#fff;resize:vertical;"></textarea><br>

                <label>Изображение (jpg, png)</label><br>
                <input type="file" name="image" accept="image/*"
                       style="margin:4px 0 18px;color:#fff;"><br>

                <button type="submit"
                        style="border-radius:999px;padding:9px 22px;border:none;
                               background:linear-gradient(120deg,#ff007f,#7b2cff);
                               color:#fff;text-transform:uppercase;letter-spacing:.12em;
                               font-size:12px;cursor:pointer;">
                    Сохранить модель
                </button>
            </form>
        </section>

        <!-- Форма добавления администратора -->
        <section style="flex:1 1 320px;background:rgba(15,8,40,.9);border-radius:16px;
                        padding:20px 22px;">
            <h2 style="font-family:'Barlow Condensed',sans-serif;text-transform:uppercase;
                       letter-spacing:.16em;font-size:20px;margin-bottom:12px;">
                Добавить администратора
            </h2>

            <form method="post">
                <input type="hidden" name="action" value="add_admin">

                <label>Логин</label><br>
                <input type="text" name="login" required
                       style="width:100%;margin:4px 0 12px;padding:8px 10px;border-radius:10px;
                              border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                              color:#fff;"><br>

                <label>Пароль</label><br>
                <input type="text" name="password" required
                       style="width:100%;margin:4px 0 18px;padding:8px 10px;border-radius:10px;
                              border:1px solid rgba(255,255,255,.2);background:rgba(5,3,15,.9);
                              color:#fff;"><br>

                <button type="submit"
                        style="border-radius:999px;padding:9px 22px;border:1px solid rgba(255,255,255,.4);
                               background:transparent;color:#fff;text-transform:uppercase;
                               letter-spacing:.12em;font-size:12px;cursor:pointer;">
                    Создать аккаунт
                </button>
            </form>
        </section>

    </div>


    <!-- ===== Таблица моделей ===== -->
    <section style="margin-top:20px;background:rgba(15,8,40,.9);border-radius:16px;padding:18px 20px;">
        <h2 style="font-family:'Barlow Condensed',sans-serif;text-transform:uppercase;
                   letter-spacing:.16em;font-size:20px;margin-bottom:14px;">
            Список моделей
        </h2>

        <?php if (!$modelsList): ?>
            <p style="font-size:14px;color:rgba(255,255,255,.7);">Модели пока не добавлены.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:14px;">
                    <thead>
                    <tr style="background:rgba(255,255,255,.04);">
                        <th style="text-align:left;padding:8px 6px;">ID</th>
                        <th style="text-align:left;padding:8px 6px;">Название</th>
                        <th style="text-align:left;padding:8px 6px;">Год</th>
                        <th style="text-align:left;padding:8px 6px;">Цена</th>
                        <th style="text-align:right;padding:8px 6px;">Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($modelsList as $m): ?>
                        <tr style="border-top:1px solid rgba(255,255,255,.06);">
                            <td style="padding:6px 6px;"><?= (int)$m['id'] ?></td>
                            <td style="padding:6px 6px;"><?= htmlspecialchars($m['name']) ?></td>
                            <td style="padding:6px 6px;"><?= htmlspecialchars($m['year']) ?></td>
                            <td style="padding:6px 6px;">
                                <?= $m['price'] !== null ? '$' . htmlspecialchars($m['price']) : '—' ?>
                            </td>
                            <td style="padding:6px 6px;text-align:right;white-space:nowrap;">
                                <a href="model_edit.php?id=<?= (int)$m['id'] ?>"
                                   style="display:inline-block;margin-right:6px;padding:4px 10px;
                                          border-radius:999px;border:1px solid rgba(255,255,255,.4);
                                          font-size:12px;color:#fff;text-decoration:none;">
                                    Редактировать
                                </a>

                                <form method="post" style="display:inline;"
                                      onsubmit="return confirm('Удалить модель «<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>»?');">
                                    <input type="hidden" name="action" value="delete_model">
                                    <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                    <button type="submit"
                                            style="padding:4px 10px;border-radius:999px;border:1px solid rgba(255,80,120,.7);
                                                   background:transparent;color:#ff8080;font-size:12px;
                                                   cursor:pointer;">
                                        Удалить
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

</div>

</body>
</html>




