<?php

namespace GlpiPlugin\Zebrashipping;

final class Installer
{
    public static function install(): bool
    {
        global $DB;

        // GLPI owns the connection. Schema changes are tracked by Migration.
        $table = 'glpi_plugin_zebrashipping_jobs';
        $migration = new \Migration(PLUGIN_ZEBRASHIPPING_VERSION);
        if ($DB->tableExists($table)) {
            if (!$DB->fieldExists($table, 'printer_id')) {
                $migration->addField($table, 'printer_id', 'string', ['value' => '']);
            }
            $migration->executeMigration();
            InstallationState::get();
            return true;
        }

        $query = "CREATE TABLE `$table` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `request_id` varchar(64) NOT NULL,
            `itemtype` varchar(100) NOT NULL,
            `items_id` int unsigned NOT NULL,
            `shipment_ref` varchar(64) NOT NULL DEFAULT '',
            `printer_id` varchar(64) NOT NULL DEFAULT '',
            `destination_city` varchar(150) NOT NULL DEFAULT '',
            `recipient` varchar(255) NOT NULL DEFAULT '',
            `address` text NOT NULL,
            `phone` varchar(80) NOT NULL DEFAULT '',
            `notes` text NOT NULL,
            `created_by` int unsigned NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `service_job_id` varchar(64) DEFAULT NULL,
            `status` varchar(32) NOT NULL DEFAULT 'queued',
            `response_text` text,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_request_id` (`request_id`),
            KEY `item_ref` (`itemtype`, `items_id`),
            KEY `created_at` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $DB->doQuery($query, $DB->error());
        $created = $DB->tableExists($table);
        if ($created) {
            InstallationState::get();
        }
        return $created;
    }
}
