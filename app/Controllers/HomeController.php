<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Core\Session;

class HomeController extends Controller
{
    public function index()
    {
        $users = User::all();
        $currentUser = null;
        
        if (Session::check()) {
            $currentUser = User::find($_SESSION['user_id']);
        }

        $this->view('home', [
            'title' => 'Página de inicio',
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }
}
