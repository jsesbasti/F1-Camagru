<?php

namespace App\Core;

class Mailer
{
    public static function baseUrl(): string
    {
        $url = trim((string) getenv('APP_URL'), " \t\n\r\"'/");
        if ($url === '') {
            return 'http://localhost:8080';
        }
        return preg_match('#^https?://#i', $url) ? $url : "http://$url";
    }

    public static function send(string $to, string $subject, string $body): bool
    {
        $sent = mail($to, $subject, $body);
        if (!$sent) {
            error_log("Mailer: could not send '$subject' to $to");
        }
        return $sent;
    }
}
