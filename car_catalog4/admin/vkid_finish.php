<?php
// admin/vkid_finish.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json; charset=utf-8');

// читаем JSON из тела запроса
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

$vkId = $data['vk_id'] ?? null;

if (!$vkId) {
    echo json_encode(['ok' => false, 'error' => 'Не передан vk_id']);
    exit;
}

try {
    // в таблице users должно быть поле vk_id
    $stmt = $conn->prepare('SELECT * FROM users WHERE vk_id = :vk_id LIMIT 1');
    $stmt->execute([':vk_id' => $vkId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode(['ok' => false, 'error' => 'Ошибка БД: ' . $e->getMessage()]);
    exit;
}

if (!$user) {
    echo json_encode([
        'ok'    => false,
        'error' => 'Этот VK-аккаунт не привязан к админской учётке (vk_id = ' . $vkId . ')'
    ]);
    exit;
}

// Успешный вход
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id']        = $user['id'];
$_SESSION['admin_name']      = $user['login'];

echo json_encode(['ok' => true]);

