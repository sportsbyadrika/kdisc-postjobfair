<?php

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $cfg = require __DIR__ . '/../config/database.php';
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $cfg['host'],
        $cfg['port'],
        $cfg['dbname'],
        $cfg['charset']
    );

    try {
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $exception) {
        error_log(sprintf(
            'Database connection failed for user "%s" on host "%s:%s" (db: %s): %s',
            (string) $cfg['username'],
            (string) $cfg['host'],
            (string) $cfg['port'],
            (string) $cfg['dbname'],
            $exception->getMessage()
        ));

        http_response_code(500);
        exit('Database connection failed. Please verify DB credentials in config/database.php or environment variables (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS).');
    }

    return $pdo;
}
