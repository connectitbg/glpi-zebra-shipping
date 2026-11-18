<?php

/** Small PSR-4 loader; the plugin has no third-party PHP dependencies. */
spl_autoload_register(static function (string $class): void {
    $prefix = 'GlpiPlugin\\Zebrashipping\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});
