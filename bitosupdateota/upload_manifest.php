<?php
header('Content-Type: application/json');

// Простая проверка пароля (передаётся в POST)
$pass = isset($_POST['password']) ? $_POST['password'] : '';
if ($pass !== 'тдл!5') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
    exit;
}

$file = $_FILES['file'];
if ($file['name'] !== 'manifest.json' && mime_content_type($file['tmp_name']) !== 'application/json') {
    echo json_encode(['success' => false, 'message' => 'Должен быть JSON-файл']);
    exit;
}

$content = file_get_contents($file['tmp_name']);
if (json_decode($content) === null) {
    echo json_encode(['success' => false, 'message' => 'Некорректный JSON']);
    exit;
}

if (file_put_contents(__DIR__ . '/manifest.json', $content) === false) {
    echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'manifest.json успешно обновлён']);
?>
