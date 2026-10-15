<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Image;
use App\Core\Session;

class HomeController extends Controller
{
    public function index()
    {
        $users = User::all();
        $feedPageSize = 4;
        $currentUser = null;

        if (Session::check()) {
            $currentUser = User::find(id: Session::getId());
        }

        $feed = Image::feedPage($feedPageSize, 0, (int) ($currentUser['id'] ?? 0));

        $this->view('home', [
                'title' => 'Página de inicio',
                'users' => $users,
                'posts' => $feed['posts'],
                'hasMorePosts' => $feed['hasMore'],
                'feedPageSize' => $feedPageSize,
                'nextFeedPage' => $feed['hasMore'] ? 2 : null,
                'currentUser' => $currentUser,
                'header' => 'header',
                'footer' => 'footer-nav',
                'scripts' => [
                    'home'
                ]
            ]
        );
    }
}
