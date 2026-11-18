<?php

namespace GlpiPlugin\Zebrashipping;

/** Normalize empty-value markers returned by different GLPI item types. */
final class MetadataValue
{
    public static function display(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));
        if (in_array(mb_strtolower($text), ['n/a', 'na', 'not applicable', 'not available'], true)) {
            return '';
        }
        return $text;
    }
}
