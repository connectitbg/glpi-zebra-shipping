<?php

// Compatibility redirect for old bookmarks; current GLPI 11 UI uses the
// controller route so it always runs after the GLPI bootstrap.
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$target = is_string($requestPath)
    ? preg_replace('~/front/config\.form\.php$~', '/Config', $requestPath)
    : null;
if (!is_string($target) || $target === $requestPath) {
    http_response_code(404);
    exit;
}
header('Location: ' . $target, true, 302);
exit;
