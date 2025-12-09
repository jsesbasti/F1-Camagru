<?php

namespace App\Core;

class Controller
{
    protected function view(string $template, array $data = [])
    {
        extract($data);

        require __DIR__ . "/../../app/Views/$template.php";
    }
}