<?php

namespace App\Core;

class Controller
{
    protected function requireAuth(): void
    {
        if (!Session::check()) {
            header('Location: /login', true, 302);
            exit;
        }
    }

    protected function requireGuest(): void
    {
        if (Session::check()) {
            header('Location: /', true, 302);
            exit;
        }
    }

    protected function view(string $template, array $data = [])
    {
        extract($data);

        $content = __DIR__ . "/../../app/Views/$template.php";
        require __DIR__ . "/../../app/Views/layouts/app.php";
    }
}
