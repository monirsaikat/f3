<?php

namespace App\Controllers;

use App\Models\User;
use Base;

/**
 * REST resource for /api/users — demonstrates every HTTP verb,
 * route params (@id) and query params (?page, ?limit, ?search).
 */
class UserController extends Controller
{
    private const MAX_LIMIT = 50;

    /** GET /api/users?page=1&limit=10&search=ada */
    public function index(Base $f3): void
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

        $this->json([
            'status' => 'ok',
            'meta'   => [
                'page'   => $page,
                'limit'  => $limit,
                'total'  => $result['total'],
                'pages'  => (int) ceil($result['total'] / $limit),
                'search' => $search ?: null,
            ],
            'data'   => array_map(fn ($user) => $user->toArray(), $result['subset']),
        ]);
    }

    /** GET /api/users/@id */
    public function show(Base $f3, array $params): void
    {
        $user = $this->findOrFail($params['id']);

        $this->json(['status' => 'ok', 'data' => $user->toArray()]);
    }

    /** POST /api/users  body: {"name": "...", "email": "..."} */
    public function create(Base $f3): void
    {
        $data = $this->body();

        if ($errors = User::validate($data)) {
            $this->json(['status' => 'error', 'errors' => $errors], 422);

            return;
        }

        $user = new User();
        $user->copyfrom($this->onlyFields($data));

        $this->persist($user, 201);
    }

    /** PUT /api/users/@id — full replacement of the record. */
    public function replace(Base $f3, array $params): void
    {
        $this->save($params['id'], partial: false);
    }

    /** PATCH /api/users/@id — partial update, only the fields sent. */
    public function update(Base $f3, array $params): void
    {
        $this->save($params['id'], partial: true);
    }

    /** DELETE /api/users/@id */
    public function delete(Base $f3, array $params): void
    {
        $this->findOrFail($params['id'])->erase();

        $this->json(['status' => 'ok', 'deleted' => $params['id']]);
    }

    /** Shared write path for PUT (full) and PATCH (partial). */
    private function save(string $id, bool $partial): void
    {
        $data = $this->body();

        if ($errors = User::validate($data, $partial)) {
            $this->json(['status' => 'error', 'errors' => $errors], 422);

            return;
        }

        // PUT: validation guarantees all fields are present, so the whole
        // resource is overwritten; PATCH only touches the keys sent.
        $user = $this->findOrFail($id);
        $user->copyfrom($this->onlyFields($data));

        $this->persist($user, 200);
    }

    /** Save a mapper, turning a duplicate-email collision into a 409. */
    private function persist(User $user, int $status): void
    {
        try {
            $user->save();
        } catch (\PDOException $e) {
            // SQLSTATE 23000 = integrity constraint violation (unique email)
            if ($e->getCode() === '23000') {
                $this->json(['status' => 'error', 'errors' => ['email is already taken']], 409);

                return;
            }
            throw $e;
        }

        $this->json(['status' => 'ok', 'data' => $user->toArray()], $status);
    }

    /** Whitelist the writable fields, dropping anything else the client sent. */
    private function onlyFields(array $data): array
    {
        return array_intersect_key($data, array_flip(User::FIELDS));
    }

    /** Load a user by route param or abort with 404. */
    private function findOrFail(string $id): User
    {
        $user = new User();
        $user->load(['id = ?', $id]);

        if ($user->dry()) {
            $this->abort(404, "User '{$id}' not found");
        }

        return $user;
    }
}
