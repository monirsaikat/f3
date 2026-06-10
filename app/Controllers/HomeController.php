<?php

namespace App\Controllers;

use Base;

class HomeController extends Controller
{
    /** GET / */
    public function index(Base $f3): void
    {
        $this->view('home.html', [
            'title'  => 'Home',
            'active' => 'home',
        ]);
    }

    /** GET /about */
    public function about(Base $f3): void
    {
        $this->view('about.html', [
            'title'  => 'About',
            'active' => 'about',
        ]);
    }
}
