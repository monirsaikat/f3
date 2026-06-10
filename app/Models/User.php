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
    }

    /** Filter for a case-insensitive name/email search, or null for "all". */
    public static function searchFilter(string $term): ?array
    {
        if ($term === '') {
            return null;
        }

        $like = '%' . $term . '%';

        return ['name LIKE ? OR email LIKE ?', $like, $like];
    }

    /**
     * Validate incoming data. Returns a list of error messages, empty if OK.
     *
     * @param bool $partial true for PATCH: only validate the keys present
     */
    public static function validate(array $data, bool $partial = false): array
    {
        $errors = [];

        if (!$partial || array_key_exists('name', $data)) {
            if (trim((string) ($data['name'] ?? '')) === '') {
                $errors[] = 'name is required and must be a non-empty string';
            }
        }

        if (!$partial || array_key_exists('email', $data)) {
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
