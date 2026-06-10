<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\User;
use App\Support\ApiAuth;

/**
 * Base controller for API routes that require a valid Bearer token.
 * The authenticated user is available to subclasses as $this->apiUser.
 */
abstract class GuardedController extends Controller
{
    protected ?User $apiUser = null;

    public function beforeroute(): void
    {
        parent::beforeroute();

        // Let CORS pre-flight through without authentication.
        if ($this->f3->get('VERB') === 'OPTIONS') {
            return;
        }

        $user = ApiAuth::resolve($this->f3);

        if (!$user) {
            $this->json([
                'status'  => 'error',
                'message' => 'Unauthenticated. Provide a valid Bearer token.',
            ], 401);
            exit;
        }

        $this->apiUser = $user;
        $this->f3->set('api_user', $user);
    }
}
