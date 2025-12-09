<?php

return [
    'host' => 'db',
    'database' => getenv('MYSQL_DATABASE'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD')
];
