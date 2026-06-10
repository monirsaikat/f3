<?php

namespace App\Controllers;

use App\Controllers\Api\GuardedController;
use App\Models\User;
use App\Traits\FindsOrFails;
use App\Traits\ValidatesRequest;
use Base;

/**
 * REST resource for /api/users — demonstrates every HTTP verb,
 * route params (@id) and query params (?page, ?limit, ?search).
 * Requires a valid Bearer token (see GuardedController).
 *
 * Traits in use:
 *   ValidatesRequest  — rule-based input validation → 422 on failure
 *   FindsOrFails      — load by PK or 404; save with duplicate detection → 409
 *   JsonResponder     — ok() / fail() / paginated() (inherited via Controller)
 */
class UserController extends GuardedController
{
    use ValidatesRequest, FindsOrFails;

    private const MAX_LIMIT = 50;

    /** GET /api/me — the token owner's own profile */
    public function me(): void
    {
        $this->ok($this->apiUser->toArray());
    }

    /** GET /api/users?page=1&limit=10&search=ada */
    public function index(): void
    {
        $page   = max(1, (int) $this->query('page', 1));
        $limit  = min(self::MAX_LIMIT, max(1, (int) $this->query('limit', 10)));
        $search = trim((string) $this->query('search', ''));

        $result = (new User())->paginate(
            $page - 1,
            $limit,
            User::searchFilter($search),
            ['order' => 'name']
        );

        $this->paginated(
            array_map(fn ($u) => $u->toArray(), $result['subset']),
            $result['total'],
            $page,
            $limit,
            $search ?: null
        );
    }

    /** GET /api/users/@id */
    public function show(Base $f3, array $params): void
    {
        $this->ok($this->findOrFail(User::class, $params['id'])->toArray());
    }

    /** POST /api/users */
    public function create(): void
    {
        $data = $this->validate($this->body(), [
            'name'   => 'required|max:120',
            'email'  => 'required|email|max:191',
            'gender' => 'in:Male,Female,Other',
        ]);

        $user = new User();
        $user->copyfrom(array_intersect_key($data, array_flip(User::FIELDS)));
        $this->persistOrConflict($user, 201);
    }

    /** PUT /api/users/@id — full replacement of the record */
    public function replace(Base $f3, array $params): void
    {
        $this->write($params['id'], partial: false);
    }

    /** PATCH /api/users/@id — partial update, only the fields provided */
    public function update(Base $f3, array $params): void
    {
        $this->write($params['id'], partial: true);
    }

    /** DELETE /api/users/@id */
    public function delete(Base $f3, array $params): void
    {
        $user = $this->findOrFail(User::class, $params['id']);
        $id   = (int) $user->get('id');
        $user->erase();

        $this->ok(['deleted' => $id]);
    }

    /** Shared write path for PUT (full) and PATCH (partial). */
    private function write(string $id, bool $partial): void
    {
        $body  = $this->body();

        // Build only the rules relevant to this request.
        $rules = array_filter([
            'name'   => (!$partial || \array_key_exists('name', $body))
                        ? ($partial ? 'max:120' : 'required|max:120') : null,
            'email'  => (!$partial || \array_key_exists('email', $body))
                        ? ($partial ? 'email|max:191' : 'required|email|max:191') : null,
            'gender' => (!$partial || \array_key_exists('gender', $body))
                        ? 'in:Male,Female,Other' : null,
        ]);

        $data = $this->validate($body, $rules);
        $user = $this->findOrFail(User::class, $id);
        $user->copyfrom(array_intersect_key($data, array_flip(User::FIELDS)));
        $this->persistOrConflict($user, 200);
    }
}
