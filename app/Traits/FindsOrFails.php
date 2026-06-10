<?php

namespace App\Traits;

/**
 * Helpers for loading a single record by primary key and persisting with
 * conflict detection — the two patterns repeated in every CRUD controller.
 *
 * Requires $this->abort() (Controller) and $this->ok() / $this->fail() (JsonResponder).
 */
trait FindsOrFails
{
    /**
     * Load a SQL mapper record by its `id` column, or abort with 404.
     *
     * Example:
     *   $user = $this->findOrFail(User::class, $params['id']);
     *
     * @template T of \DB\SQL\Mapper
     * @param  class-string<T> $class
     * @param  string|int      $id
     * @return T
     */
    protected function findOrFail(string $class, string|int $id): mixed
    {
        $record = new $class();
        $record->load(['id = ?', $id]);

        if ($record->dry()) {
            $label = substr(strrchr($class, '\\'), 1) ?: $class;
            $this->abort(404, "$label '$id' not found");
        }

        return $record;
    }

    /**
     * Save a mapper record, converting a unique-key collision (SQLSTATE 23000)
     * into a structured 409 response instead of a fatal PDO exception.
     *
     * Example:
     *   $this->persistOrConflict($user, 201, 'email');
     *
     * @param object $record       any mapper with save() and toArray()
     * @param int    $status       2xx status to use on success (201 for create, 200 for update)
     * @param string $conflictField  field name to name in the conflict message
     */
    protected function persistOrConflict(object $record, int $status, string $conflictField = 'email'): void
    {
        try {
            $record->save();
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->fail("$conflictField is already taken", 409);
                exit;
            }
            throw $e;
        }

        $this->ok($record->toArray(), $status);
    }
}
