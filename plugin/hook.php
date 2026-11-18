<?php

use GlpiPlugin\Zebrashipping\Installer;

function plugin_zebrashipping_install(): bool
{
    return Installer::install();
}

function plugin_zebrashipping_uninstall(): bool
{
    // Keep plugin data by default; removal requires an explicit administrator action.
    return true;
}
