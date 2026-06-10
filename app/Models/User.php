<?php

namespace App\Models;

use Audit;
use Base;
use DB\SQL\Mapper;

class User extends Mapper
{
    /** Client-writable columns. Everything else (id, timestamps) is managed. */
    public const FIELDS = ['name', 'email', 'gender'];

    public function __construct()
    {
        parent::__construct(Base::instance()->get('DB'), 'users');

        // F3 native mapper hooks — set timestamps automatically before every write.
        // beforeinsert fires before INSERT SQL is built, so the values land in the query.
        // beforeupdate fires before UPDATE SQL is built, same principle.
        $this->beforeinsert(function (self $mapper): void {
            $now = date('Y-m-d H:i:s');
            $mapper->set('created_at', $now);
            $mapper->set('updated_at', $now);
        });

        $this->beforeupdate(function (self $mapper): void {
            $mapper->set('updated_at', date('Y-m-d H:i:s'));
        });
    }

    /** Load a single user by email, or null if none. */
    public static function findByEmail(string $email): ?self
    {
        $user = new self();
        $user->load(['email = ?', $email]);

        return $user->dry() ? null : $user;
    }

    /** Hash and store a plaintext password (never stored in clear). */
    public function setPassword(string $plain): void
    {
        $this->set('password', \password_hash($plain, PASSWORD_BCRYPT));
    }

    /** Verify a plaintext password against the stored hash. */
    public function verifyPassword(string $plain): bool
    {
        $hash = $this->get('password');

        return \is_string($hash) && $hash !== '' && \password_verify($plain, $hash);
    }

    /**
     * Build an F3 filter array for a case-insensitive name/email search.
     * Returns null when the query is empty (no filter = return all rows).
     */
    public static function searchFilter(string $q): ?array
    {
        if (($q = trim($q)) === '') {
            return null;
        }

        $like = '%' . mb_strtolower($q) . '%';

        return ['LOWER(name) LIKE ? OR LOWER(email) LIKE ?', $like, $like];
    }

    /**
     * Validate incoming data. Returns a list of error messages, empty if OK.
     *
     * @param bool $partial true for PATCH — only validate the keys present
     */
    public static function validate(array $data, bool $partial = false): array
    {
        $errors = [];

        if (!$partial || \array_key_exists('name', $data)) {
            if (trim((string) ($data['name'] ?? '')) === '') {
                $errors[] = 'name is required and must be a non-empty string';
            }
        }

        if (!$partial || \array_key_exists('email', $data)) {
            if (!Audit::instance()->email((string) ($data['email'] ?? ''), false)) {
                $errors[] = 'email is missing or not a valid address';
            }
        }

        return $errors;
    }

    /** Record as a response-ready array with typed id. */
    public function toArray(): array
    {
        return [
            'id'         => (int) $this->get('id'),
            'name'       => $this->get('name'),
            'gender'     => $this->get('gender'),
            'email'      => $this->get('email'),
            'created_at' => $this->get('created_at'),
            'updated_at' => $this->get('updated_at'),
        ];
    }
}
