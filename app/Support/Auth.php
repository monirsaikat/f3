<?php

namespace App\Support;

use App\Models\User;
use Base;

/**
 * Session-based authentication for the web panel, plus CSRF helpers.
 * Accessing the SESSION hive lazily starts the PHP session.
 */
final class Auth
{
    /** Verify credentials and, on success, start an authenticated session. */
    public static function attempt(string $email, string $password): bool
    {
        $user = User::findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return false;
        }

        self::login($user);

        return true;
    }

    /** Mark the given user as logged in (regenerates the session id). */
    public static function login(User $user): void
    {
        $f3 = Base::instance();
        $f3->set('SESSION.uid', (int) $user->get('id'));

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /** Destroy the authenticated session. */
    public static function logout(): void
    {
        Base::instance()->clear('SESSION');
    }

    /** Id of the logged-in user, or null. */
    public static function id(): ?int
    {
        $id = Base::instance()->get('SESSION.uid');

        return $id ? (int) $id : null;
    }

    /** The logged-in user model, or null. */
    public static function user(): ?User
    {
        $id = self::id();

        if (!$id) {
            return null;
        }

        $user = new User();
        $user->load(['id = ?', $id]);

        return $user->dry() ? null : $user;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    /** Current CSRF token, generated and stored in the session on first use. */
    public static function csrf(): string
    {
        $f3 = Base::instance();
        $token = $f3->get('SESSION.csrf');

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            $f3->set('SESSION.csrf', $token);
        }

        return $token;
    }

    /** Constant-time check of a submitted CSRF token. */
    public static function checkCsrf(?string $token): bool
    {
        $real = Base::instance()->get('SESSION.csrf');

        return is_string($real) && is_string($token) && $token !== '' && hash_equals($real, $token);
    }
}
