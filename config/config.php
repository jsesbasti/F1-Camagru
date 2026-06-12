<?php

return [
    'host' => getenv('DB_SERVER') ?: $_ENV['DB_SERVER'] ?? null,
    'database' => getenv('MYSQL_DATABASE') ?: $_ENV['MYSQL_DATABASE'] ?? null,
    'user' => getenv('MYSQL_USER') ?: $_ENV['MYSQL_USER'] ?? null,
    'password' => getenv('MYSQL_PASSWORD') ?: $_ENV['MYSQL_PASSWORD'] ?? null
];
