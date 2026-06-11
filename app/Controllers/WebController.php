<?php

namespace App\Controllers;

use App\Models\SiteSetting;
use App\Support\Auth;

/**
 * Base controller for server-rendered web pages. Exposes the authenticated
 * user, a CSRF token and one-shot flash messages to every template, and
 * provides guest/auth route guards.
 */
abstract class WebController extends Controller
{
    public function beforeroute(): void
    {
        parent::beforeroute();

        $user = Auth::user();
        $this->f3->set('current_user', $user?->toArray());
        $this->f3->set('csrf_token', Auth::csrf());

        // Move any pending flash message into the hive, then clear it.
        $this->f3->set('flash', $this->f3->get('SESSION.flash'));
        $this->f3->clear('SESSION.flash');

        // General site settings available in every layout (site name, footer, etc.).
        $this->f3->set('general_settings', SiteSetting::withDefaults('general'));
    }

    /** Redirect logged-in users away from guest-only pages (login/register). */
    protected function requireGuest(): void
    {
        if (Auth::check()) {
            $this->f3->reroute('@dashboard');
        }
    }

    /** Redirect anonymous users to the login page. */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->flash('warning', 'Please sign in to continue.');
            $this->f3->reroute('@login');
        }
    }

    /** Abort if the POST CSRF token doesn't match the session token. */
    protected function verifyCsrf(): void
    {
        if (!Auth::checkCsrf($this->f3->get('POST.csrf'))) {
            $this->flash('danger', 'Your session expired. Please try again.');
            $this->f3->reroute($this->f3->get('PATH'));
        }
    }

    /** Queue a flash message for the next request. */
    protected function flash(string $type, string $message): void
    {
        $this->f3->set('SESSION.flash', ['type' => $type, 'message' => $message]);
    }
}
