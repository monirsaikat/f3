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
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_users_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
