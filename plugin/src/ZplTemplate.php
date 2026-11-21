<?php

namespace GlpiPlugin\Zebrashipping;

/** Persist and validate an optional Professional ZPL label template. */
final class ZplTemplate
{
    public const MAX_LENGTH = 16384;

    private const CONTEXT = 'plugin:zebrashipping';

    private const TOKENS = [
        'width_dots', 'height_dots', 'dpi', 'city', 'recipient', 'address', 'phone',
        'asset_name', 'asset_type', 'inventory_number', 'serial_number', 'notes',
    ];

    public static function get(): string
    {
        $settings = \Config::getConfigurationValues(self::CONTEXT);
        $template = $settings['zpl_template'] ?? '';
        return is_string($template) ? $template : '';
    }

    public static function store(string $template): void
    {
        \Config::setConfigurationValues(self::CONTEXT, [
            'zpl_template' => self::validate($template),
        ]);
    }

    public static function validate(string $template): string
    {
        $template = trim(str_replace(["\r\n", "\r"], "\n", $template));
        if ($template === '') {
            return '';
        }
        if (strlen($template) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(__('The ZPL template is too long.', 'zebrashipping'));
        }
        if (!str_starts_with($template, '^XA') || !str_ends_with($template, '^XZ')) {
            throw new \InvalidArgumentException(__('A ZPL template must start with ^XA and end with ^XZ.', 'zebrashipping'));
        }

        preg_match_all('/\{\{([a-z_]+)\}\}/', $template, $matches);
        foreach ($matches[1] as $token) {
            if (!in_array($token, self::TOKENS, true)) {
                throw new \InvalidArgumentException(__('The ZPL template contains an unsupported placeholder.', 'zebrashipping'));
            }
        }
        if (str_contains($template, '{{') || str_contains($template, '}}')) {
            throw new \InvalidArgumentException(__('The ZPL template contains an invalid placeholder.', 'zebrashipping'));
        }

        return $template;
    }
}
