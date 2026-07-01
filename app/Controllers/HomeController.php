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
        $feed = Image::feedPage($feedPageSize, 0);
        $currentUser = null;
        
        if (Session::check()) {
            $currentUser = User::find(id: Session::getId());
        }

        $this->view('home', [
            'title' => 'Página de inicio',
            'users' => $users,
            'posts' => $feed['posts'],
            'hasMorePosts' => $feed['hasMore'],
            'feedPageSize' => $feedPageSize,
            'nextFeedPage' => $feed['hasMore'] ? 2 : null,
            'currentUser' => $currentUser
        ]);
    }
}
