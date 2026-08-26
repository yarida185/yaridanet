<?php
// update_manifest.php — обновление версии и eol в manifest.json
header('Content-Type: application/json');

// --- Проверка авторизации ---
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

$version = isset($_POST['version']) ? trim($_POST['version']) : '';
$eolRaw = isset($_POST['eol']) ? trim($_POST['eol']) : '';
$eolList = array_map('trim', explode(',', $eolRaw));
$eolList = array_filter($eolList);

if ($version === '') {
    echo json_encode(['success' => false, 'message' => 'Версия не может быть пустой']);
    exit;
}

$manifestPath = __DIR__ . '/manifest.json';
$manifest = [];
if (file_exists($manifestPath)) {
    $manifest = json_decode(file_get_contents($manifestPath), true);
    if (!is_array($manifest)) $manifest = [];
}
$manifest['version'] = $version;
$manifest['eol'] = $eolList;
// Поле url не трогаем

file_put_contents($manifestPath, json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'message' => 'manifest.json обновлён',
    'version' => $version,
    'eol' => $eolList
]);
?>
