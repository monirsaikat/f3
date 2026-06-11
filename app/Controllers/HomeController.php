<?php

namespace App\Controllers;

use App\Models\SiteSetting;
use Base;

class HomeController extends WebController
{
    /** GET / */
    public function index(Base $f3): void
    {
        $this->view('home.html', [
            'title'         => 'Home',
            'active'        => 'home',
            'home_settings' => SiteSetting::withDefaults('home'),
        ]);
    }

    /** GET /about */
    public function about(Base $f3): void
    {
        $this->view('about.html', [
            'title'          => 'About',
            'active'         => 'about',
            'about_settings' => SiteSetting::withDefaults('about'),
        ]);
    }

    /** GET /services */
    public function services(Base $f3): void
    {
        $this->view('services.html', [
            'title'  => 'Services',
            'active' => 'services',
        ]);
    }

    /** GET /portfolio */
    public function portfolio(Base $f3): void
    {
        $this->view('portfolio.html', [
            'title'  => 'Portfolio',
            'active' => 'portfolio',
        ]);
    }

    /** GET /contact */
    public function contact(Base $f3): void
    {
        $this->view('contact.html', [
            'title'            => 'Contact',
            'active'           => 'contact',
            'contact_settings' => SiteSetting::withDefaults('contact'),
        ]);
    }

    /** GET /blog */
    public function blog(Base $f3): void
    {
        $this->view('blog.html', [
            'title'         => 'Blog',
            'active'        => 'blog',
            'blog_settings' => SiteSetting::withDefaults('blog'),
        ]);
    }
}
