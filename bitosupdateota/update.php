<?php
// upload.php с отладкой
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Проверка, что файл загружен
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешён']);
    exit;
}

// Проверка сессии или пароля
session_start();
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    $pass = isset($_POST['password']) ? $_POST['password'] : '';
    if ($pass !== 'тдл!5') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Доступ запрещён']);
        exit;
    }
    $_SESSION['authenticated'] = true;
}

if (!isset($_FILES['firmware']) || $_FILES['firmware']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Файл не загружен или ошибка (код: ' . $_FILES['firmware']['error'] . ')']);
    exit;
}

$file = $_FILES['firmware'];
$version = isset($_POST['version']) ? trim($_POST['version']) : '';
if ($version === '') {
    echo json_encode(['success' => false, 'message' => 'Не указана версия']);
    exit;
}

$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Не удалось создать папку uploads']);
        exit;
    }
}

$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$targetFile = $uploadDir . 'firmware.' . $ext;
if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'message' => 'Не удалось сохранить файл (ошибка перемещения)']);
    exit;
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$downloadUrl = $protocol . '://' . $host . '/bitosupdateota/uploads/firmware.' . $ext;

$manifestPath = __DIR__ . '/manifest.json';
$manifest = [];
if (file_exists($manifestPath)) {
    $content = file_get_contents($manifestPath);
    $manifest = json_decode($content, true);
    if (!is_array($manifest)) $manifest = [];
}
$manifest['version'] = $version;
$manifest['url'] = $downloadUrl;
if (!isset($manifest['eol'])) $manifest['eol'] = [];

if (file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) === false) {
    echo json_encode(['success' => false, 'message' => 'Не удалось записать manifest.json']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Файл загружен, manifest.json обновлён',
    'version' => $version,
    'downloadUrl' => $downloadUrl
]);
?>
