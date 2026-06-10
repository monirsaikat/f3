<?php

namespace App\Support;

use App\Models\ApiToken;
use App\Models\User;
use Base;

/**
 * Stateless Bearer-token authentication for the API.
 *
 * Tokens are random 64-char strings; only their SHA-256 hash is persisted.
 * The plaintext is returned once (at login/register) and never recoverable.
 */
final class ApiAuth
{
    /** Token lifetime in seconds (30 days). */
    private const TTL = 60 * 60 * 24 * 30;

    /** Issue a new token for a user and return the plaintext (show once). */
    public static function issue(User $user, string $name = 'api'): string
    {
        $plain = bin2hex(random_bytes(32));

        $token = new ApiToken();
        $token->user_id    = (int) $user->get('id');
        $token->token_hash = ApiToken::hash($plain);
        $token->name       = $name;
        $token->expires_at = date('Y-m-d H:i:s', time() + self::TTL);
        $token->save();

        return $plain;
    }

    /**
     * Resolve the Bearer token on the current request to a user, or null.
     * Touches last_used_at and honours expiry.
     */
    public static function resolve(Base $f3): ?User
    {
        $plain = self::bearer($f3);

        if ($plain === null) {
            return null;
        }

        $token = new ApiToken();
        $token->load(['token_hash = ?', ApiToken::hash($plain)]);

        if ($token->dry()) {
            return null;
        }

        $expires = $token->get('expires_at');
        if ($expires !== null && strtotime((string) $expires) < time()) {
            $token->erase();

            return null;
        }

        $user = new User();
        $user->load(['id = ?', $token->get('user_id')]);

        if ($user->dry()) {
            return null;
        }

        $token->last_used_at = date('Y-m-d H:i:s');
        $token->save();

        return $user;
    }

    /** Revoke the token used on the current request. */
    public static function revokeCurrent(Base $f3): void
    {
        $plain = self::bearer($f3);

        if ($plain === null) {
            return;
        }

        $token = new ApiToken();
        $token->load(['token_hash = ?', ApiToken::hash($plain)]);

        if (!$token->dry()) {
            $token->erase();
        }
    }

    /** Extract the Bearer token from the Authorization header, or null. */
    private static function bearer(Base $f3): ?string
    {
        $header = $f3->get('HEADERS.Authorization')
            ?? $f3->get('SERVER.HTTP_AUTHORIZATION')
            ?? $f3->get('SERVER.REDIRECT_HTTP_AUTHORIZATION')
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', trim((string) $header), $m)) {
            return trim($m[1]);
        }

        return null;
    }
}
