<?php

namespace App\Controllers;

use App\Models\SiteSetting;
use Base;
use Template;

/**
 * Admin panel — page settings management. Requires an authenticated session.
 */
class AdminController extends WebController
{
    private const SECTIONS = ['general', 'home', 'about', 'blog', 'contact'];

    public function beforeroute(): void
    {
        parent::beforeroute();
        $this->requireAuth();
    }

    /** GET /admin */
    public function index(): void
    {
        $this->adminView('admin/index.html', [
            'title'         => 'Admin Panel',
            'admin_section' => '',
        ]);
    }

    /** GET /admin/settings/@section */
    public function editSettings(Base $f3): void
    {
        $section = $f3->get('PARAMS.section');
        if (!in_array($section, self::SECTIONS, true)) {
            $f3->error(404);
            return;
        }
        $this->adminView("admin/settings/{$section}.html", [
            'title'         => ucfirst($section) . ' Settings',
            'admin_section' => $section,
            'settings'      => SiteSetting::withDefaults($section),
        ]);
    }

    /** POST /admin/settings/@section */
    public function saveSettings(Base $f3): void
    {
        $section = $f3->get('PARAMS.section');
        if (!in_array($section, self::SECTIONS, true)) {
            $f3->error(404);
            return;
        }
        $this->verifyCsrf();

        $data = $f3->get('POST');
        unset($data['csrf']);
        SiteSetting::saveSection($section, $data);

        $this->flash('success', ucfirst($section) . ' settings saved successfully.');
        $f3->reroute('/admin/settings/' . $section);
    }

    /** Render a template inside the admin layout instead of the public layout. */
    private function adminView(string $template, array $data = []): void
    {
        $this->f3->mset($data);
        $this->f3->set('content', $template);
        echo Template::instance()->render('admin/layout.html');
    }
}
