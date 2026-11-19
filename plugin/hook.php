<?php

use GlpiPlugin\Zebrashipping\Installer;
use GlpiPlugin\Zebrashipping\InstallationState;

function plugin_zebrashipping_install(): bool
{
    return Installer::install();
}

function plugin_zebrashipping_upgrade(string $oldVersion): bool
{
    return Installer::install();
}

function plugin_zebrashipping_uninstall(): bool
{
    global $DB;

    $migration = new Migration(PLUGIN_ZEBRASHIPPING_VERSION);
    $migration->dropTable('glpi_plugin_zebrashipping_jobs');
    Config::deleteConfigurationValues('plugin:zebrashipping');
    InstallationState::delete();

    return !$DB->tableExists('glpi_plugin_zebrashipping_jobs');
}
