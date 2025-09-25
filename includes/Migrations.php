<?php

namespace JensiAI;

/**
 * Migrations class.
 *
 * @class Migrations single point of entry for database migration
 */
final class Migrations
{
    /**
     * Run the migration.
     *
     * @param string $prefix application prefix
     * @param string $currentVersion the current version
     * @return Migrations
     */
    public function run($prefix, $currentVersion)
    {
        // Make sure has the correct permissions
        if (!current_user_can('activate_plugins')) {
            return $this;
        }

        global $wpdb;
        $settings_table = "{$wpdb->prefix}jensi_ai_settings";
        $lastVersion = '0.0.0';
        $result = null;
        if ($wpdb->get_var("SHOW TABLES LIKE '$settings_table'") === $settings_table) {
            $result = $wpdb->get_row("SELECT * FROM $settings_table");
            if ($result) {
                $lastVersion = $result->last_migrated_version;
            }
        }

        // If current version is not greater than the installed, return
        if (version_compare($lastVersion, $currentVersion, '>=')) {
            return $this;
        }

        // Apply migrations in order
        $this->applyMigration($lastVersion, '0.0.1', 'migration_0_0_1');
        $this->applyMigration($lastVersion, '1.0.1', 'migration_1_0_1');

        // $this->applyMigration($lastVersion, '1.0.3', 'migration_1_0_3');
        // ...

        // Update the last migrated version for future updates
        if (!$result) {
            // If table was just created, insert first row
            $wpdb->insert($settings_table, ['last_migrated_version' => $currentVersion]);
        } else {
            // Update if table already present
            $wpdb->update($settings_table, ['last_migrated_version' => $currentVersion], ['id' => $result->id]);
        }

        return $this;
    }

    /**
     * Function that help apply application migration.
     *
     * @param string $lastVersion the migrated version
     * @param string $applyVersion the migration to apply version
     * @param string $migration_func the migration function
     * @return void
     */
    public function applyMigration($lastVersion, $applyVersion, $migration_func)
    {
        if (version_compare($lastVersion, $applyVersion, '>=')) {
            return;
        }
        call_user_func([$this, $migration_func]);
    }

    /**
     * Database cleanup to run during plugin uninstall.
     *
     * @param string $prefix
     * @param array $settings
     * @return void
     */
    public function cleanUp($prefix, $settings)
    {
        // don't do anything if configured to not cleanup db
        if (!isset($settings['cleanup_db_on_plugin_uninstall']) || !$settings['cleanup_db_on_plugin_uninstall']) {
            return;
        }

        global $wpdb;

        // Remove tables
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jensi_ai_jobs");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jensi_ai_settings");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jensi_ai_configs");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}jensi_ai_agents");

        // Remove options
        // delete_option($prefix . $option_name);
    }

    /**
     * DB Migration for v0.0.1.
     *
     * @return void
     */
    public function migration_0_0_1()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sqlQuery = "CREATE TABLE {$wpdb->prefix}jensi_ai_jobs (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(256) NOT NULL,
            `post_id` BIGINT NOT NULL,
            `type` VARCHAR(256) NOT NULL,
            `content` LONGTEXT NULL,
            `meta` LONGTEXT NULL,
            `processed` TINYINT NOT NULL DEFAULT 0,
            `failed` TINYINT NOT NULL DEFAULT 0,
            `errors` MEDIUMTEXT NULL,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}jensi_ai_settings (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `last_migrated_version` TEXT NOT NULL,
            `settings` TEXT NULL,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;

        CREATE TABLE {$wpdb->prefix}jensi_ai_configs (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `post_type` TEXT NOT NULL,
            `taxonomy` TEXT NULL,
            `terms` TEXT NULL,
            `enabled` TINYINT NOT NULL DEFAULT 1,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sqlQuery);
    }

    /**
     * DB Migration for v1.0.1 - Create agents table.
     *
     * @return void
     */
    public function migration_1_0_1()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sqlQuery = "CREATE TABLE {$wpdb->prefix}jensi_ai_agents (
            `id` mediumint(9) NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(255) NOT NULL,
            `agent_id` VARCHAR(255) NOT NULL,
            `data_source_id` VARCHAR(255) NOT NULL,
            `enabled` TINYINT NOT NULL DEFAULT 1,
            `avatar_url` TEXT NULL,
            `welcome_message` TEXT NULL,
            `bottom_offset` INT NOT NULL DEFAULT 20,
            `right_offset` INT NOT NULL DEFAULT 20,
            `primary_color` VARCHAR(255) NOT NULL DEFAULT '#667eea',
            `secondary_color` VARCHAR(255) NOT NULL DEFAULT '#764ba2',
            `background_color` VARCHAR(255) NOT NULL DEFAULT '#ffffff',
            `text_color` VARCHAR(255) NOT NULL DEFAULT '#000000',
            `secondary_text_color` VARCHAR(255) NOT NULL DEFAULT '#ffffff',
            `post_type` TEXT NULL,
            `taxonomy` TEXT NULL,
            `terms` TEXT NULL,
            `display_everywhere` TINYINT NOT NULL DEFAULT 0,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        dbDelta($sqlQuery);
    }

    // ... Add more migration methods as needed
}
