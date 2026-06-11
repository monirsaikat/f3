<?php

namespace App\Support;

use Base;
use DB\SQL;

/**
 * MySQL bootstrap. Creates the database and schema on demand using
 * CREATE ... IF NOT EXISTS, so there are no migration files to run or drop —
 * the app provisions itself the first time it talks to MySQL.
 */
final class Database
{
    /** Connect, ensure schema exists, and register the handle as 'DB'. */
    public static function boot(Base $f3): SQL
    {
        $cfg = $f3->get('db');

        foreach (['host', 'port', 'name', 'user'] as $key) {
            if (($cfg[$key] ?? '') === '') {
                throw new \RuntimeException(
                    "Missing db.$key — copy config/db.ini.example to config/db.ini and fill it in."
                );
            }
        }

        // 1) Connect to the server (no db selected) and create the database.
        $server = new SQL(
            "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4",
            $cfg['user'],
            (string) $cfg['password']
        );
        $server->exec(sprintf(
            'CREATE DATABASE IF NOT EXISTS `%s` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
            $cfg['name']
        ));

        // 2) Connect to the database itself and ensure the tables exist.
        $db = new SQL(
            "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4",
            $cfg['user'],
            (string) $cfg['password']
        );
        self::schema($db);

        $f3->set('DB', $db);

        return $db;
    }

    /** Idempotent table definitions. Add new tables here. */
    private static function schema(SQL $db): void
    {
        $db->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name       VARCHAR(190) NOT NULL,
                gender     VARCHAR(10) NULL,
                email      VARCHAR(190) NOT NULL,
                password   VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        // Self-migrate: add the password column to pre-existing installs.
        self::ensureColumn($db, 'users', 'password', 'VARCHAR(255) NULL AFTER email');

        $db->exec(
            'CREATE TABLE IF NOT EXISTS site_settings (
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                section    VARCHAR(50)  NOT NULL,
                `key`      VARCHAR(100) NOT NULL,
                value      TEXT         NULL,
                created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_site_settings (section, `key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $db->exec(
            'CREATE TABLE IF NOT EXISTS api_tokens (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id      INT UNSIGNED NOT NULL,
                token_hash   CHAR(64) NOT NULL,
                name         VARCHAR(100) NULL,
                last_used_at TIMESTAMP NULL,
                expires_at   TIMESTAMP NULL,
                created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_token_hash (token_hash),
                KEY idx_tokens_user (user_id),
                CONSTRAINT fk_tokens_user FOREIGN KEY (user_id)
                    REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    /**
     * Add a column only if it does not already exist.
     * Uses F3's DB\SQL::schema() for introspection — works across MySQL, SQLite and PostgreSQL.
     */
    private static function ensureColumn(SQL $db, string $table, string $column, string $definition): void
    {
        if (!\array_key_exists($column, $db->schema($table, null, 0))) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    }
}
