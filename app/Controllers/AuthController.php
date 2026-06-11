<?php

namespace App\Controllers;

use App\Models\User;
use App\Support\Auth;
use Base;

/**
 * Web panel authentication: register, login, logout and a protected dashboard.
 */
class AuthController extends WebController
{
    /** GET /login */
    public function showLogin(): void
    {
        $this->requireGuest();
        $this->view('auth/login.html', ['title' => 'Login', 'active' => 'login']);
    }

    /** POST /login */
    public function login(Base $f3): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $email    = trim((string) $f3->get('POST.email'));
        $password = (string) $f3->get('POST.password');

        if (!Auth::attempt($email, $password)) {
            $this->flash('danger', 'Invalid email or password.');
            $f3->reroute('@login');
        }

        $this->flash('success', 'Welcome back!');
        $f3->reroute('@dashboard');
    }

    /** GET /register */
    public function showRegister(): void
    {
        $this->requireGuest();
        $this->view('auth/register.html', ['title' => 'Register', 'active' => 'register']);
    }

    /** POST /register */
    public function register(Base $f3): void
    {
        $this->requireGuest();
        $this->verifyCsrf();

        $data = [
            'name'   => trim((string) $f3->get('POST.name')),
            'email'  => trim((string) $f3->get('POST.email')),
            'gender' => $f3->get('POST.gender') ?: null,
        ];
        $password = (string) $f3->get('POST.password');
        $confirm  = (string) $f3->get('POST.password_confirm');

        $errors = User::validate($data);
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }

        if ($errors) {
            $this->flash('danger', implode(' ', $errors));
            $f3->reroute('@register');
        }

        $user = new User();
        $user->copyfrom(array_intersect_key($data, array_flip(User::FIELDS)));
        $user->setPassword($password);

        try {
            $user->save();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->flash('danger', 'That email is already registered.');
                $f3->reroute('@register');
            }
            throw $e;
        }

        Auth::login($user);
        $this->flash('success', 'Your account has been created.');
        $f3->reroute('@dashboard');
    }

    /** POST /logout */
    public function logout(Base $f3): void
    {
        $this->verifyCsrf();
        Auth::logout();
        $f3->reroute('@home');
    }

    /** GET /dashboard (protected) */
    public function dashboard(): void
    {
        $this->requireAuth();
        $this->view('auth/dashboard.html', [
            'title'  => 'Dashboard',
            'active' => 'dashboard',
            'user'   => Auth::user()->toArray(),
        ]);
    }

}
