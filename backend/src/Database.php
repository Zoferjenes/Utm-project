<?php
declare(strict_types=1);

namespace FixIt;

use PDO;

final class Database
{
    public static function connect(array $env): PDO
    {
        $host = self::first($env, ['DB_HOST', 'MYSQLHOST'], '127.0.0.1');
        $port = self::first($env, ['DB_PORT', 'MYSQLPORT'], '3306');
        $name = self::first($env, ['DB_NAME', 'MYSQLDATABASE'], 'fixit_arcade');
        $user = self::first($env, ['DB_USER', 'MYSQLUSER'], 'root');
        $pass = self::first($env, ['DB_PASS', 'MYSQLPASSWORD'], '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private static function first(array $env, array $keys, string $default): string
    {
        foreach ($keys as $key) {
            if (isset($env[$key]) && trim((string)$env[$key]) !== '') {
                return trim((string)$env[$key]);
            }
        }

        return $default;
    }
}
