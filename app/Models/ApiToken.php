<?php

namespace App\Models;

use Base;
use DB\SQL\Mapper;

/**
 * Personal access token for the API. Only the SHA-256 hash of the token is
 * stored — the plaintext is shown to the client exactly once, at creation.
 */
class ApiToken extends Mapper
{
    public function __construct()
    {
        parent::__construct(Base::instance()->get('DB'), 'api_tokens');
    }

    /** Hash a plaintext token the same way it is stored. */
    public static function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
