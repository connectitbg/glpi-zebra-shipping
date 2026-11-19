<?php

namespace GlpiPlugin\Zebrashipping;

/** Encrypt the Professional license at rest with GLPI's instance key. */
final class LicenseStorage
{
    private const CONTEXT = 'plugin:zebrashipping';

    public static function get(): string
    {
        $settings = \Config::getConfigurationValues(self::CONTEXT);
        $stored = (string) ($settings['license_file'] ?? '');
        if ($stored === '') {
            return '';
        }

        // Migrate licenses saved as plaintext by plugin versions <= 0.2.6.
        if (self::isLicense($stored)) {
            $encrypted = self::encrypt($stored);
            \Config::setConfigurationValues(self::CONTEXT, ['license_file' => $encrypted]);
            return $stored;
        }

        $license = (new \GLPIKey())->decrypt($stored);
        return is_string($license) && self::isLicense($license) ? $license : '';
    }

    public static function store(string $license): void
    {
        $encrypted = $license === '' ? '' : self::encrypt($license);
        \Config::setConfigurationValues(self::CONTEXT, ['license_file' => $encrypted]);
    }

    public static function migrateStored(): void
    {
        self::get();
    }

    private static function encrypt(string $license): string
    {
        $encrypted = (new \GLPIKey())->encrypt($license);
        if ($encrypted === '') {
            throw new \RuntimeException('GLPI could not encrypt the Professional license.');
        }
        return $encrypted;
    }

    private static function isLicense(string $value): bool
    {
        $base64 = strtr($value, '-_', '+/');
        $base64 .= str_repeat('=', (4 - strlen($base64) % 4) % 4);
        $decoded = base64_decode($base64, true);
        if (!is_string($decoded)) {
            return false;
        }
        $envelope = json_decode($decoded, true);
        return is_array($envelope)
            && ($envelope['payload']['product'] ?? null) === 'glpi-zebra-shipping'
            && ($envelope['payload']['edition'] ?? null) === 'professional'
            && is_string($envelope['signature'] ?? null);
    }
}
