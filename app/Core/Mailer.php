<?php

namespace App\Core;

class Mailer
{
    public static function baseUrl(): string
    {
        // CLOUD_URL is set by `make cloud` while the tunnel is up.
        $trim = " \t\n\r\"'/";
        $url = trim((string) getenv('CLOUD_URL'), $trim) ?: trim((string) getenv('APP_URL'), $trim);
        if ($url === '') {
            return 'http://localhost:8080';
        }
        return preg_match('#^https?://#i', $url) ? $url : "http://$url";
    }

    // Escapes user-provided text (usernames, comments, emails) for an HTML body.
    public static function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    // <a> to a path of the app, showing $text instead of the full URL.
    public static function link(string $text, string $path): string
    {
        $url = self::e(self::baseUrl() . $path);
        return "<a href=\"$url\" style=\"color:#e10600;font-weight:bold\">" . self::e($text) . '</a>';
    }

    // $body is HTML: escape user text with e(); line breaks become <br>.
    public static function send(string $to, string $subject, string $body): bool
    {
        $html = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;font-size:15px;line-height:1.5;color:#1f1f27">'
            . nl2br($body) . '</body></html>';
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8";
        $sent = mail($to, $subject, $html, $headers);
        if (!$sent) {
            error_log("Mailer: could not send '$subject' to $to");
        }
        return $sent;
    }
}
