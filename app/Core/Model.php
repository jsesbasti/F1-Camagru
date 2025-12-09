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

            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['database']}",
                $config['user'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        return $pdo;
    }
}
