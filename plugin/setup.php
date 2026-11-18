<?php

use GlpiPlugin\Zebrashipping\ShippingTab;

define('PLUGIN_ZEBRASHIPPING_VERSION', '0.2.2');

require_once __DIR__ . '/src/autoload.php';

function zebrashipping_escape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function plugin_version_zebrashipping(): array
{
    return [
        'name'         => 'GLPI Zebra Shipping',
        'version'      => PLUGIN_ZEBRASHIPPING_VERSION,
        'author'       => 'GLPI Zebra Shipping',
        'license'      => 'Commercial; distribution terms pending legal review',
        'requirements' => [
            'glpi' => ['min' => '11.0'],
            'php'  => ['min' => '8.2'],
        ],
    ];
}

function plugin_init_zebrashipping(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['zebrashipping'] = true;
    Plugin::registerClass(ShippingTab::class, [
        'addtabon' => [
            'Computer', 'Monitor', 'Printer', 'NetworkEquipment', 'Peripheral',
            'Phone', 'Ticket',
        ],
    ]);
    // GLPI 11 controllers are routed through the core bootstrap. The legacy
    // front/ PHP URL is not safe to expose directly from the public symlink.
    $PLUGIN_HOOKS['config_page']['zebrashipping'] = 'Config';
}

function plugin_zebrashipping_check_prerequisites(): bool
{
    return version_compare(GLPI_VERSION, '11.0', '>=')
        && PHP_VERSION_ID >= 80200
        && extension_loaded('curl');
}

function plugin_zebrashipping_check_config(): bool
{
    return true;
}
