<?php
// upload_manifest.php — принимает загруженный manifest.json и сохраняет его
header('Content-Type: application/json');

// Проверка файла
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен']);
    exit;
}

$file = $_FILES['file'];
if ($file['name'] !== 'manifest.json' && mime_content_type($file['tmp_name']) !== 'application/json') {
    echo json_encode(['success' => false, 'message' => 'Должен быть JSON-файл']);
    exit;
}

// Проверяем, что это валидный JSON
$content = file_get_contents($file['tmp_name']);
if (json_decode($content) === null) {
    echo json_encode(['success' => false, 'message' => 'Некорректный JSON']);
    exit;
}

// Сохраняем в корень
if (file_put_contents(__DIR__ . '/manifest.json', $content) === false) {
    echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'manifest.json успешно обновлён']);
?>
