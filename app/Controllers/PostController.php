<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Helper;
use App\Models\Image;

class PostController extends Controller
{
    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = max(1, min((int) ($_GET['limit'] ?? 4), 20));
        $offset = ($page - 1) * $limit;

        $feed = Image::feedPage($limit, $offset);

        Helper::json_response([
            'posts' => $feed['posts'],
            'hasMore' => $feed['hasMore'],
            'page' => $page,
            'limit' => $limit,
        ]);
    }
}
