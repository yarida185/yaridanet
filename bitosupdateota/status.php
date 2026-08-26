<?php
// status.php — отдаёт текущие данные из manifest.json для интерфейса
header('Content-Type: application/json');

$manifestPath = __DIR__ . '/manifest.json';
if (!file_exists($manifestPath)) {
    // Если файла нет, возвращаем значения по умолчанию
    echo json_encode([
        'version' => '—',
        'url' => '',
        'eol' => [],
        'fileExists' => false
    ]);
    exit;
}

$data = json_decode(file_get_contents($manifestPath), true);
if (!is_array($data)) {
    $data = ['version' => '—', 'url' => '', 'eol' => []];
}

// Проверяем, существует ли файл по ссылке
$fileExists = false;
if (!empty($data['url'])) {
    // Извлекаем путь из URL
    $path = parse_url($data['url'], PHP_URL_PATH);
    if ($path) {
        $localPath = __DIR__ . $path;
        if (file_exists($localPath)) {
            $fileExists = true;
        }
    }
}
$data['fileExists'] = $fileExists;

echo json_encode($data);
?>
