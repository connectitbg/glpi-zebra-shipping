<?php

declare(strict_types=1);

function __(string $message, string $domain): string
{
    return $message;
}

require_once __DIR__ . '/../plugin/src/ZplTemplate.php';

$validTemplate = '^XA^FD{{recipient}} {{address}} {{serial_number}}^FS^XZ';
if (\GlpiPlugin\Zebrashipping\ZplTemplate::validate($validTemplate) !== $validTemplate) {
    throw new RuntimeException('Valid placeholders should be retained.');
}

$invalidTemplates = [
    '^XA^FD{{unknown}}^FS^XZ',
    '^XA^FD{{recipient}^FS^XZ',
    '^XA^FD{{recipient}}}^FS^XZ',
    '^FD{{recipient}}^FS',
];
foreach ($invalidTemplates as $template) {
    try {
        \GlpiPlugin\Zebrashipping\ZplTemplate::validate($template);
        throw new RuntimeException('Invalid template was accepted: ' . $template);
    } catch (InvalidArgumentException) {
        // Expected.
    }
}

echo "ZPL template validation checks passed.\n";
