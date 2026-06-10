<?php

namespace App\Models;

use Audit;
use Base;
use DB\Jig\Mapper;

/**
 * User model on top of F3's Jig flat-file store.
 *
 * To move to MySQL later: extend DB\SQL\Mapper instead and pass the
 * connection + table name to parent::__construct() — the controller
 * code does not change.
 */
class User extends Mapper
{
    public const FIELDS = ['name', 'email'];

    public function __construct()
    {
        parent::__construct(Base::instance()->get('JIG'), 'users');
    }

    /** Public identifier used in URLs (Jig's internal _id stays hidden). */
    public static function newId(): string
    {
        return bin2hex(random_bytes(8));
    }

    /** Record as a response-ready array, without storage internals. */
    public function toArray(): array
    {
        $row = $this->cast();
        unset($row['_id']);

        return $row;
    }

    /** Filter for a case-insensitive name/email search, or null for "all". */
    public static function searchFilter(string $term): ?array
    {
        if ($term === '') {
            return null;
        }

        return [
            '(isset(@name) && stripos(@name,?)!==false)'
            . ' || (isset(@email) && stripos(@email,?)!==false)',
            $term,
            $term,
        ];
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
}
