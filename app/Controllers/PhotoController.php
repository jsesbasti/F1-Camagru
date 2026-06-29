<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class PhotoController extends Controller
{
    public function index(): void
    {
        if (!Session::check()) {
            header('Location: /login', true, 302);
            exit;
        }

        $this->view('photo', [
            'title' => 'Subir foto',
            'currentUser' => null,
        ]);
    }
}
