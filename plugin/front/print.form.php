<?php

// Preserve POST bodies from forms rendered before an upgrade.
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$target = is_string($requestPath)
    ? preg_replace('~/front/print\.form\.php$~', '/Print', $requestPath)
    : null;
if (!is_string($target) || $target === $requestPath) {
    http_response_code(404);
    exit;
}
header('Location: ' . $target, true, 307);
exit;
