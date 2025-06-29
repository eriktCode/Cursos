<?php
$uri = $_SERVER['REQUEST_URI'];
$parsedUri = parse_url($uri, PHP_URL_PATH);

// Si la ruta es un backend/controller con PATH_INFO
if (preg_match("#^/controllers/([^/]+\.php)(/.*)?$#", $parsedUri, $matches)) {
    $script = $matches[1];
    $pathInfo = $matches[2] ?? '';

    $file = __DIR__ . "/backend/controllers/$script";

    if (file_exists($file)) {
        $_SERVER['PATH_INFO'] = $pathInfo;
        require $file;
        exit;
    } else {
        http_response_code(404);
        echo "Archivo no encontrado: $file";
        exit;
    }
}

// Archivos frontend y views
$viewsPath = __DIR__ . "/frontend/views{$parsedUri}";

if (preg_match('#^/frontend/views/#', $parsedUri)) {
    if (file_exists(__DIR__ . $parsedUri)) {
        return false; // Permitir servir el archivo directamente
    } else {
        http_response_code(404);
        echo "Archivo de vista no encontrado: " . __DIR__ . $parsedUri;
        exit;
    }
}

if ($parsedUri === '/' || $parsedUri === '') {
    require __DIR__ . '/frontend/views/login.php'; // Aquí corregido según estructura
    exit;
} elseif (file_exists($frontendPath)) {
    return false; // Permitir servir archivos estáticos en /frontend
}

// Otros backend sin PATH_INFO
$backendDirs = ['models', 'api', 'config'];
foreach ($backendDirs as $dir) {
    if (preg_match("#^/{$dir}/#", $parsedUri)) {
        $backendFile = __DIR__ . "/backend{$parsedUri}";
        if (file_exists($backendFile)) {
            return false;
        }
    }
}

http_response_code(404);
echo "Ruta no encontrada: $parsedUri";
exit;
