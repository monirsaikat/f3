<?php

namespace App\Controllers;

use Base;

/**
 * Base controller: shared request/response helpers for every controller.
 */
abstract class Controller
{
    protected Base $f3;

    public function __construct()
    {
        $this->f3 = Base::instance();
    }

    /** Runs automatically before any routed method of a child controller. */
    public function beforeroute(): void
    {
        header('Access-Control-Allow-Origin: *');
    }

    /** Send a JSON response with the given HTTP status code. */
    protected function json(mixed $data, int $status = 200): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /** Decoded JSON request body (PUT/PATCH/POST), [] when absent or invalid. */
    protected function body(): array
    {
        $data = json_decode($this->f3->get('BODY') ?: '', true);

        return is_array($data) ? $data : [];
    }

    /** Read a query-string parameter with an optional default. */
    protected function query(string $key, mixed $default = null): mixed
    {
        return $this->f3->get('GET.' . $key) ?? $default;
    }

    /** Abort the request with an error status; handled by ONERROR as JSON. */
    protected function abort(int $code, string $message): never
    {
        $this->f3->error($code, $message);
        exit;
    }
}
