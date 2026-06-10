<?php

namespace App\Traits;

/**
 * Consistent JSON response shapes for API controllers.
 *
 * Requires $this->json() to be available (defined on Controller).
 * Mix this trait into any Controller subclass.
 */
trait JsonResponder
{
    /** 200/201 success with a data payload. */
    protected function ok(mixed $data, int $status = 200): void
    {
        $this->json(['status' => 'ok', 'data' => $data], $status);
    }

    /** 4xx/5xx error with a human-readable message and optional field errors. */
    protected function fail(string $message, int $status = 400, array $errors = []): void
    {
        $payload = ['status' => 'error', 'message' => $message];
        if ($errors) {
            $payload['errors'] = $errors;
        }
        $this->json($payload, $status);
    }

    /** Paginated collection with meta block. */
    protected function paginated(
        array   $subset,
        int     $total,
        int     $page,
        int     $limit,
        ?string $search = null
    ): void {
        $this->json([
            'status' => 'ok',
            'meta'   => [
                'page'   => $page,
                'limit'  => $limit,
                'total'  => $total,
                'pages'  => $limit > 0 ? (int) ceil($total / $limit) : 0,
                'search' => $search ?: null,
            ],
            'data'   => $subset,
        ]);
    }

    /** 204 No Content — for deletes that return nothing. */
    protected function noContent(): void
    {
        http_response_code(204);
        exit;
    }
}
