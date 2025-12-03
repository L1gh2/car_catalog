<?php
// admin/vk_callback.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../db.php';

// здесь ты уже получаешь code от VK ID (он в $_GET['code'])
if (empty($_GET['code'])) {
    exit('VK: не передан параметр code');
}

$code = $_GET['code'];

// === ТУТ ТВОЙ КОД ОБМЕНА code -> vkUserId ЧЕРЕЗ VK ID SDK/HTTP ===
// Должна получиться переменная $vkUserId с числом / строкой VK ID пользователя.

$vkUserId = /* твоя логика получения VK ID пользователя */ 0;

// Ищем пользователя-админа по vk_id
$stmt = $conn->prepare('SELECT * FROM users WHERE vk_id = :vk_id LIMIT 1');
$stmt->execute([':vk_id' => $vkUserId]);
$user = $stmt->fetch();

if (!$user) {
    exit('VK аккаунт не привязан к админской учётке');
}

// СТАВИМ ТОТ ЖЕ ФЛАГ, ЧТО И ПРИ ЛОГИНЕ/ПАРОЛЕ
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id']        = $user['id'];
$_SESSION['admin_name']      = $user['login'];
$_SESSION['admin_role']      = $user['role'];

header('Location: admin.php');
exit;


