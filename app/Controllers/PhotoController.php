<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class PhotoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $id = Session::getId();
        $this->view('photo', [
            'title' => 'Subir foto',
            'currentUser' => User::find(id: $id),
            'styles' => [
                'photo'
            ],
            'scripts' => [
                'photo'
            ]
        ]);
    }

    public function uploadPhoto(): void
    {
        $this->requireAuth();

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            header('Location: /photo', true, 302);
            exit;
        }

        $tmpPath = $_FILES['image']['tmp_name'];
        $originalName = $_FILES['image']['name'];

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed, true)) {
            die('Formato no permitido');
        }

        $uploadDir = __DIR__ . '/../../public/uploads/photos';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            die('No se pudo preparar el directorio de subida');
        }

        $newName = uniqid('img_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $newName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            die('No se pudo guardar la imagen');
        }

        header('Location: /photo', true, 302);
        exit;
    }
}
