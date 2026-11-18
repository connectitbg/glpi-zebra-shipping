<?php

namespace GlpiPlugin\Zebrashipping;

/** First install metadata stored outside the plugin folder. */
final class InstallationState
{
    private static function path(): string
    {
        if (!defined('GLPI_VAR_DIR')) {
            throw new \RuntimeException('GLPI_VAR_DIR is not configured');
        }
        return rtrim(GLPI_VAR_DIR, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'zebrashipping'
            . DIRECTORY_SEPARATOR . 'installation.json';
    }

    public static function get(): array
    {
        $path = self::path();
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Cannot create private plugin state directory');
        }
        chmod($directory, 0700);

        $handle = fopen($path, 'c+');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open plugin installation state');
        }
        try {
            if (!flock($handle, LOCK_EX)) {
                throw new \RuntimeException('Cannot lock plugin installation state');
            }
            $contents = stream_get_contents($handle);
            $state = is_string($contents) && trim($contents) !== '' ? json_decode($contents, true) : null;
            if (!is_array($state) || empty($state['installation_id']) || empty($state['installed_at'])) {
                $state = [
                    'installation_id' => bin2hex(random_bytes(20)),
                    'installed_at'    => gmdate('c'),
                ];
                ftruncate($handle, 0);
                rewind($handle);
                fwrite($handle, json_encode($state, JSON_UNESCAPED_SLASHES));
                fflush($handle);
            }
            chmod($path, 0600);
            flock($handle, LOCK_UN);
            return $state;
        } finally {
            fclose($handle);
        }
    }
}
