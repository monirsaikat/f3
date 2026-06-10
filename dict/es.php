<?php
/**
 * Spanish (Español) dictionary. Missing keys fall back to en.php.
 */
return [
    'lang_name' => 'Español',

    'nav' => [
        'home'      => 'Inicio',
        'about'     => 'Acerca de',
        'api'       => 'API',
        'login'     => 'Entrar',
        'register'  => 'Registrarse',
        'dashboard' => 'Panel',
        'logout'    => 'Salir',
    ],

    'auth' => [
        'login_title'     => 'Iniciar sesión',
        'register_title'  => 'Crear cuenta',
        'dashboard_title' => 'Panel',
        'name'            => 'Nombre',
        'email'           => 'Correo',
        'gender'          => 'Género',
        'gender_male'     => 'Masculino',
        'gender_female'   => 'Femenino',
        'gender_other'    => 'Otro',
        'password'        => 'Contraseña',
        'confirm'         => 'Confirmar contraseña',
        'submit_login'    => 'Iniciar sesión',
        'submit_register' => 'Crear cuenta',
        'have_account'    => '¿Ya tienes una cuenta?',
        'no_account'      => '¿No tienes una cuenta?',
        'welcome'         => 'Bienvenido',
        'profile'         => 'Tu perfil',
        'member_since'    => 'Miembro desde',
        'api_hint'        => 'Usa los endpoints de token para acceder a la API mediante programación.',
    ],

    'home' => [
        'title'     => 'Bienvenido a my-f3-app',
        'subtitle'  => 'Una aplicación PHP ligera construida sobre Fat-Free Framework: estructura MVC limpia, una API REST completa y un backend MySQL que crea su propio esquema.',
        'cta_learn' => 'Saber más',
        'cta_api'   => 'Explorar la API',
        'f_fast_t'  => 'Rápido y pequeño',
        'f_fast_d'  => 'Todo el framework es un núcleo de ~90 KB sin dependencias pesadas, por lo que las peticiones siguen siendo veloces.',
        'f_api_t'   => 'API RESTful',
        'f_api_d'   => 'Un recurso de usuarios completo que cubre cada verbo HTTP, parámetros de ruta, paginación y búsqueda.',
        'f_db_t'    => 'BD autoconfigurable',
        'f_db_d'    => 'Las tablas MySQL se crean con IF NOT EXISTS al arrancar: sin archivos de migración que ejecutar o eliminar.',
    ],

    'examples' => [
        'title'    => 'Ejemplos',
        'subtitle' => 'Las imágenes siguientes se sirven desde la carpeta local assets/img/.',
        'routing'  => 'Enrutamiento RESTful con parámetros y cadenas de consulta.',
        'database' => 'Modelos respaldados por MySQL a través del mapeador SQL.',
        'i18n'     => 'Cambia el idioma de la interfaz desde la barra de navegación.',
    ],

    'about' => [
        'title'      => 'Acerca de este proyecto',
        'lead'       => 'Qué es, cómo está construido y qué hay por dentro.',
        'overview_t' => 'Resumen',
        'overview_d' => 'my-f3-app es una aplicación de demostración construida sobre Fat-Free Framework (F3). Combina una estructura Modelo-Vista-Controlador limpia con una API REST JSON y un front-end renderizado en el servidor con Bootstrap 5.',
        'org_t'      => 'Cómo está organizado',
        'stack_t'    => 'Tecnologías',
        'endpoints_t'=> 'Endpoints de la API',
    ],

    'footer' => [
        'built' => 'Construido con Fat-Free Framework · MySQL · Bootstrap 5',
    ],
];
