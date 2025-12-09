<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        $users = User::all();

        $this->view('home', [
            'title' => 'Página de inicio',
            'users' => $users
        ]);
    }
}
