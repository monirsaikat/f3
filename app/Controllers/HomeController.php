<?php

namespace App\Controllers;

use Base;

class HomeController extends Controller
{
    public function index(Base $f3): void
    {
        $this->json([
            'app'       => 'my-f3-app',
            'message'   => 'Hello, World!',
            'endpoints' => [
                'GET    ' . $f3->alias('user_list') . '?page=1&limit=10&search=ada',
                'POST   ' . $f3->alias('user_create'),
                'GET    ' . $f3->alias('user_show', ['id' => 1]),
                'PUT    ' . $f3->alias('user_replace', ['id' => 1]),
                'PATCH  ' . $f3->alias('user_update', ['id' => 1]),
                'DELETE ' . $f3->alias('user_delete', ['id' => 1]),
            ],
        ]);
    }
}
