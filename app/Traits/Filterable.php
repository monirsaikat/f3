<?php

namespace App\Traits;

/**
 * Generic LIKE-filter builder for F3 SQL mapper paginate() calls.
 *
 * Usage in a model:
 *   use Filterable;
 *
 *   public static function searchFilter(string $q): ?array {
 *       return static::likeFilter($q, ['name', 'email']);
 *   }
 *
 * The returned array is the filter passed directly to $mapper->paginate():
 *   ['LOWER(name) LIKE ? OR LOWER(email) LIKE ?', '%term%', '%term%']
 *
 * Returns null when the query is empty (= no filter, return all rows).
 */
trait Filterable
{
    /**
     * Build a case-insensitive OR filter across $columns.
     *
     * @param  string   $query    search term (trimmed by this method)
     * @param  string[] $columns  column names to search inside
     * @return array|null
     */
    protected static function likeFilter(string $query, array $columns): ?array
    {
        $query = trim($query);

        if ($query === '' || $columns === []) {
            return null;
        }

        $like    = '%' . mb_strtolower($query) . '%';
        $clauses = array_map(fn (string $col) => "LOWER($col) LIKE ?", $columns);

        return [implode(' OR ', $clauses), ...array_fill(0, count($columns), $like)];
    }
}
