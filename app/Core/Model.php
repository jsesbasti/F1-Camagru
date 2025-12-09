<?php

namespace App\Core;

use PDO;

class Model
{
    protected static function db(): PDO
    {
        static $pdo = null;

        if ($pdo === null) {
            $config = require __DIR__ . '/../../config/config.php';

            $host = $config['host'];
            $database = $config['database'];
            $dsn = "mysql:host=$host;dbname=$database";
            // echo $dsn;
            $pdo = new PDO(
                $dsn,
                $config['user'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        return $pdo;
    }
}
