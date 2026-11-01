<?php
/**
 * Vercel Serverless Entrypoint & Front Controller
 * Ashvkatha – Admire the Nature
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = trim($uri, '/');

$project_root = dirname(__DIR__);

// Handle static assets if routed through here
$static_path = $project_root . '/' . $uri;
if (!empty($uri) && file_exists($static_path) && !is_dir($static_path) && !str_ends_with($static_path, '.php')) {
    $mimes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'webp'  => 'image/webp',
        'svg'   => 'image/svg+xml',
        'gif'   => 'image/gif',
        'mp4'   => 'video/mp4',
        'woff2' => 'font/woff2',
        'woff'  => 'font/woff',
        'ttf'   => 'font/ttf',
    ];
    $ext = strtolower(pathinfo($static_path, PATHINFO_EXTENSION));
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
    }
    readfile($static_path);
    exit;
}

// Map route to appropriate PHP file
$target = null;

if (empty($uri) || $uri === 'index.php') {
    $target = $project_root . '/index.php';
} elseif (file_exists($project_root . '/' . $uri) && str_ends_with($uri, '.php')) {
    $target = $project_root . '/' . $uri;
} elseif (file_exists($project_root . '/' . $uri . '.php')) {
    $target = $project_root . '/' . $uri . '.php';
} elseif (is_dir($project_root . '/' . $uri) && file_exists($project_root . '/' . $uri . '/index.php')) {
    $target = $project_root . '/' . $uri . '/index.php';
}

if ($target && file_exists($target)) {
    chdir(dirname($target));
    require $target;
} else {
    chdir($project_root);
    if (file_exists($project_root . '/index.php')) {
        require $project_root . '/index.php';
    } else {
        http_response_code(404);
        echo "<h1 style='text-align:center;margin-top:20%;font-family:sans-serif;'>404 - Page Not Found</h1>";
    }
}
