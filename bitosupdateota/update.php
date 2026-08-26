<?php
// upload.php — загрузка файла и обновление версии в manifest.json
header('Content-Type: application/json');

// --- Проверка авторизации ---
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Если сессии нет, проверяем пароль в POST (для прямых запросов)
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($pass !== 'тдл!5') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
        exit;
    }
    $_SESSION['authenticated'] = true;
}

// --- Проверка загруженного файла ---
if (!isset($_FILES['firmware']) || $_FILES['firmware']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен или ошибка']);
    exit;
}

$file = $_FILES['firmware'];
$version = isset($_POST['version']) ? trim($_POST['version']) : '';
if ($version === '') {
    echo json_encode(['success' => false, 'message' => 'Не указана версия']);
    exit;
}

// --- Сохранение файла с оригинальным расширением ---
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$targetFile = $uploadDir . 'firmware.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл']);
    exit;
}

// --- Формируем URL для скачивания ---
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$downloadUrl = $protocol . '://' . $host . '/bitosupdateota/uploads/firmware.' . $ext;

// --- Обновляем manifest.json ---
$manifestPath = __DIR__ . '/manifest.json';
$manifest = [];
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (!is_array($manifest)) $manifest = [];
}
// Обновляем только версию и url, eol не трогаем
$manifest['version'] = $version;
$manifest['url'] = $downloadUrl;
// Если eol отсутствует, создаём пустой массив
if (!isset($manifest['eol'])) {
    $manifest['eol'] = [];
}
file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

// --- Ответ ---
echo json_encode([
    'success' => true,
    'message' => 'Файл загружен, manifest.json обновлён',
    'version' => $version,
    'downloadUrl' => $downloadUrl
]);
?>
