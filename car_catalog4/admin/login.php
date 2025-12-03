<?php
// admin/login.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/config.php';

// Если уже залогинен — сразу в админку
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

// ------------ ОБЫЧНЫЙ ВХОД ЛОГИН/ПАРОЛЬ ------------
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login    = trim($_POST['login'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($login === '' || $password === '') {
        $error = 'Заполните логин и пароль.';
    } else {
        try {
            $stmt = $conn->prepare('SELECT * FROM users WHERE login = :login LIMIT 1');
            $stmt->execute([':login' => $login]);
            $user = $stmt->fetch();

            // В БД сейчас пароль в виде обычного текста
            if ($user && $user['password'] === $password) {
                // ЕДИНЫЙ ФЛАГ ЛОГИНА ДЛЯ АДМИНКИ
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $user['id'];
                $_SESSION['admin_name']      = $user['login'];
                $_SESSION['admin_role']      = $user['role'];

                header('Location: admin.php');
                exit;
            } else {
                $error = 'Неверный логин или пароль.';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка БД: ' . $e->getMessage();
        }
    }
}

// ------------ ССЫЛКА ДЛЯ ВХОДА ЧЕРЕЗ VK ID ------------

// токен состояния (для защиты от CSRF), если захочешь использовать
if (empty($_SESSION['vk_oauth_state'])) {
    $_SESSION['vk_oauth_state'] = bin2hex(random_bytes(16));
}
$state = $_SESSION['vk_oauth_state'];

$vkAuthUrl = 'https://id.vk.com/authorize?' . http_build_query([
                'client_id'     => VK_CLIENT_ID,
                'redirect_uri'  => VK_REDIRECT_URI,
                'response_type' => 'code',
                'scope'         => 'openid', // для VK ID этого достаточно
                'state'         => $state,
        ]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в админку Benny's</title>
    <link rel="stylesheet" href="../style.css?v=999">
    <style>
        body {
            background:
                    radial-gradient(circle at 20% 0%, #ffce59 0%, #ff4f84 28%, transparent 55%),
                    radial-gradient(circle at 85% 0%, #ffd74a 0%, transparent 50%),
                    linear-gradient(180deg, #ffb347 0%, #3b014b 55%, #050010 100%);
            background-repeat: no-repeat;
            background-size: cover;
            color: #fff;
            font-family: 'Roboto', sans-serif;
        }
        .admin-login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .admin-login-card {
            background: rgba(10, 5, 30, .9);
            border-radius: 18px;
            padding: 30px 34px 28px;
            box-shadow: 0 18px 40px rgba(0,0,0,.55);
            max-width: 430px;
            width: 100%;
        }
        .admin-login-card h1 {
            text-align: center;
            font-family: 'Barlow Condensed', sans-serif;
            text-transform: uppercase;
            letter-spacing: .22em;
            font-size: 26px;
            margin-bottom: 18px;
        }
        .admin-login-card p {
            font-size: 14px;
            color: rgba(255,255,255,.85);
            text-align: center;
            margin-bottom: 18px;
        }
        .admin-login-card form {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            margin-bottom: 4px;
            color: rgba(255,255,255,.8);
        }
        .form-group input {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,.35);
            background: rgba(5,3,10,.65);
            color: #fff;
            outline: none;
        }
        .form-group input:focus {
            border-color: #ff007f;
        }
        .btn-main {
            width: 100%;
            margin-top: 6px;
            border-radius: 999px;
            padding: 9px 22px;
            border: 1px solid rgba(255,255,255,.45);
            background: rgba(5,3,10,.9);
            color: #fff;
            cursor: pointer;
            font-family: 'Barlow Condensed', sans-serif;
            text-transform: uppercase;
            letter-spacing: .16em;
            font-size: 13px;
            transition: .2s;
        }
        .btn-main:hover {
            background: radial-gradient(circle at top, rgba(255,0,127,.8), #10061c);
            border-color: #ff007f;
            box-shadow: 0 0 18px rgba(255,0,127,.6);
            transform: translateY(-1px);
        }
        .error {
            color: #ffb3c6;
            font-size: 13px;
            text-align: center;
            margin-bottom: 10px;
        }
        .divider {
            text-align: center;
            margin: 14px 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .14em;
            color: rgba(255,255,255,.6);
        }
        .btn-vk-login {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 26px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            background: #0077FF;
            color: #fff;
            font-family: 'Barlow Condensed', sans-serif;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-size: 13px;
            text-decoration: none;
            box-shadow: 0 0 18px rgba(0, 119, 255, .45);
            transition: .2s;
        }
        .btn-vk-login:hover {
            filter: brightness(1.08);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="admin-login-wrapper">
    <div class="admin-login-card">
        <h1>Админ-панель</h1>
        <p>Войдите с логином/паролем или через VK.</p>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Обычный логин -->
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" name="login" id="login" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button class="btn-main" type="submit">Войти</button>
        </form>

        <div class="divider">или</div>

        <!-- Вход через VK -->
        <a class="btn-vk-login" href="<?= htmlspecialchars($vkAuthUrl) ?>">
            Войти через VK
        </a>
    </div>
</div>

</body>
</html>

