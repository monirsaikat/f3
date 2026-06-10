<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\User;
use App\Support\ApiAuth;
use Base;

/**
 * Token-based authentication for the API.
 *
 *   POST /api/auth/register  -> create account, returns a token
 *   POST /api/auth/login     -> returns a token
 *   GET  /api/auth/me        -> current user (requires token)
 *   POST /api/auth/logout    -> revoke the current token
 */
class AuthController extends Controller
{
    /** POST /api/auth/register */
    public function register(Base $f3): void
    {
        $data = $this->body();

        $fields = [
            'name'   => trim((string) ($data['name'] ?? '')),
            'email'  => trim((string) ($data['email'] ?? '')),
            'gender' => $data['gender'] ?? null,
        ];
        $password = (string) ($data['password'] ?? '');

        $errors = User::validate($fields);
        if (strlen($password) < 6) {
            $errors[] = 'password must be at least 6 characters';
        }
        if ($errors) {
            $this->json(['status' => 'error', 'errors' => $errors], 422);

            return;
        }

        $user = new User();
        $user->copyfrom(array_intersect_key($fields, array_flip(User::FIELDS)));
        $user->setPassword($password);

        try {
            $user->save();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->json(['status' => 'error', 'errors' => ['email is already taken']], 409);

                return;
            }
            throw $e;
        }

        $token = ApiAuth::issue($user, 'register');

        $this->json([
            'status' => 'ok',
            'data'   => ['user' => $user->toArray(), 'token' => $token],
        ], 201);
    }

    /** POST /api/auth/login */
    public function login(Base $f3): void
    {
        $data     = $this->body();
        $email    = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $this->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);

            return;
        }

        $token = ApiAuth::issue($user, 'login');

        $this->json([
            'status' => 'ok',
            'data'   => ['user' => $user->toArray(), 'token' => $token],
        ]);
    }

    /** GET /api/auth/me */
    public function me(Base $f3): void
    {
        $user = ApiAuth::resolve($f3);

        if (!$user) {
            $this->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);

            return;
        }

        $this->json(['status' => 'ok', 'data' => $user->toArray()]);
    }

    /** POST /api/auth/logout */
    public function logout(Base $f3): void
    {
        if (!ApiAuth::resolve($f3)) {
            $this->json(['status' => 'error', 'message' => 'Unauthenticated.'], 401);

            return;
        }

        ApiAuth::revokeCurrent($f3);
        $this->json(['status' => 'ok', 'message' => 'Token revoked']);
    }
}
