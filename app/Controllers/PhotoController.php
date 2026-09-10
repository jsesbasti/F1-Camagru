<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Image;
use App\Models\User;

class PhotoController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $id = Session::getId();
        $this->view('photo', [
                'title' => 'Upload Photo',
            'currentUser' => User::find(id: $id),
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
        $userId = Session::getId();

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed, true)) {
            die('Format not allowed');
        }

        $uploadDir = __DIR__ . '/../../public/uploads/photos';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
            die('Could not prepare upload directory');
        }

        $newName = uniqid('img_', true) . '.' . $extension;
        $destination = $uploadDir . '/' . $newName;

        if (!move_uploaded_file($tmpPath, $destination)) {
            die('Could not save image');
        }

            // Save image to database
            Image::create([
                'user_id' => $userId,
                'filename' => '/uploads/photos/' . $newName,
                'description' => ''
            ]);

        header('Location: /photo', true, 302);
        exit;
    }
}
