<?php

return [
    'host' => getenv('DB_SERVER') ?: $_ENV['DB_SERVER'] ?? null,
    'database' => trim(file_get_contents('/run/secrets/db_name')),
    'user' => trim(file_get_contents('/run/secrets/db_user')),
    'password' => trim(file_get_contents('/run/secrets/db_password'))
];
