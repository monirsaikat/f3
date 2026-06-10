<?php
/**
 * English dictionary (default / fallback language).
 * Keys are accessed in templates as {{ @nav.home }}, {{ @home.title }}, etc.
 */
return [
    'lang_name' => 'English',

    'nav' => [
        'home'  => 'Home',
        'about' => 'About',
        'api'   => 'API',
    ],

    'home' => [
        'title'       => 'Welcome to my-f3-app',
        'subtitle'    => 'A lightweight PHP application built on the Fat-Free Framework — clean MVC structure, a full REST API, and a MySQL backend that provisions its own schema.',
        'cta_learn'   => 'Learn more',
        'cta_api'     => 'Explore the API',
        'f_fast_t'    => 'Fast & tiny',
        'f_fast_d'    => 'The whole framework is a ~90 KB core with no heavy dependencies, so requests stay snappy.',
        'f_api_t'     => 'RESTful API',
        'f_api_d'     => 'A complete users resource covering every HTTP verb, route params, pagination and search.',
        'f_db_t'      => 'Self-provisioning DB',
        'f_db_d'      => 'MySQL tables are created with IF NOT EXISTS at boot — no migration files to run or drop.',
    ],

    'examples' => [
        'title'    => 'Examples',
        'subtitle' => 'Images below are served from the local assets/img/ folder.',
        'routing'  => 'RESTful routing with parameters and query strings.',
        'database' => 'Models backed by MySQL through the SQL mapper.',
        'i18n'     => 'Switch the interface language from the navbar.',
    ],

    'about' => [
        'title'      => 'About this project',
        'lead'       => "What it is, how it's built, and what's under the hood.",
        'overview_t' => 'Overview',
        'overview_d' => 'my-f3-app is a demonstration application built on the Fat-Free Framework (F3). It pairs a clean Model–View–Controller layout with a JSON REST API and a server-rendered front end styled with Bootstrap 5.',
        'org_t'      => "How it's organised",
        'stack_t'    => 'Tech stack',
        'endpoints_t'=> 'API endpoints',
    ],

    'footer' => [
        'built' => 'Built with Fat-Free Framework · MySQL · Bootstrap 5',
    ],
];
