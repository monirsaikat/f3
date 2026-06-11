<?php

namespace App\Models;

use Base;
use DB\SQL\Mapper;

class SiteSetting extends Mapper
{
    public function __construct()
    {
        parent::__construct(Base::instance()->get('DB'), 'site_settings');
    }

    /** Return all key/value pairs stored for a section. */
    public static function getSection(string $section): array
    {
        $model = new self();
        $rows  = $model->find(['section = ?', $section]) ?: [];
        $out   = [];
        foreach ($rows as $row) {
            $out[$row->get('key')] = $row->get('value');
        }
        return $out;
    }

    /** Upsert a single setting. */
    public static function upsert(string $section, string $key, string $value): void
    {
        $model = new self();
        $model->load(['section = ? AND `key` = ?', $section, $key]);
        if ($model->dry()) {
            $model->set('section', $section);
            $model->set('key', $key);
        }
        $model->set('value', $value);
        $model->save();
    }

    /** Save every key in $data for the given section. */
    public static function saveSection(string $section, array $data): void
    {
        foreach ($data as $key => $value) {
            self::upsert($section, $key, (string) $value);
        }
    }

    /** Merge stored DB values on top of hardcoded defaults. */
    public static function withDefaults(string $section): array
    {
        return array_merge(self::defaults($section), self::getSection($section));
    }

    private static function defaults(string $section): array
    {
        $all = [
            'general' => [
                'site_name'    => 'my-f3-app',
                'site_tagline' => 'A Fat-Free Framework application. Built with F3, Bootstrap 5 and the Atrio design system.',
                'footer_copy'  => 'Built with Fat-Free Framework & Atrio',
                'github_url'   => '#',
                'twitter_url'  => '#',
            ],
            'home' => [
                'hero_eyebrow'       => 'Business · Consulting · Digital',
                'hero_title'         => 'Build a business that moves faster than the market.',
                'hero_lead'          => 'A multipurpose Fat-Free Framework application — strategy, branding, development and growth under one roof.',
                'hero_cta_primary'   => 'Explore Services',
                'hero_cta_secondary' => 'See Our Work',
                'hero_badge_number'  => '12+',
                'hero_badge_text'    => 'Years of consulting expertise',
                'stat1_count'        => '320',
                'stat1_suffix'       => '+',
                'stat1_label'        => 'Projects delivered',
                'stat2_count'        => '98',
                'stat2_suffix'       => '%',
                'stat2_label'        => 'Client retention',
                'stat3_count'        => '45',
                'stat3_suffix'       => '+',
                'stat3_label'        => 'Team members',
                'stat4_count'        => '18',
                'stat4_suffix'       => '',
                'stat4_label'        => 'Countries served',
                'cta_title'          => "Have a project in mind? Let's talk.",
                'cta_lead'           => "Tell us where you are and where you want to be — we'll map the route.",
                'cta_button'         => 'Start a Conversation',
            ],
            'about' => [
                'story_eyebrow' => 'Our story',
                'story_title'   => 'Built by operators who got tired of bad agencies',
                'story_lead'    => 'This project started in 2014 when three consultants left their firms to build the kind of partner they could never hire: senior people on every project, honest timelines, and work measured by business outcomes instead of deliverables.',
                'story_body'    => "Today we're a team of 45 strategists, designers and engineers working with clients across 18 countries — from seed-stage startups to listed companies.",
                'cta_title'     => 'Want to join the team?',
                'cta_lead'      => "We're always looking for sharp strategists, designers and engineers.",
                'cta_button'    => 'See Open Roles',
            ],
            'blog' => [
                'page_title' => 'Insights & Blog',
                'cta_title'  => 'Get insights in your inbox',
                'cta_lead'   => 'One email a month. Practical, no fluff, unsubscribe anytime.',
                'cta_button' => 'Subscribe',
            ],
            'contact' => [
                'page_title'    => 'Contact Us',
                'address'       => "14 Lakeview Avenue, Suite 240\nDhaka 1212, Bangladesh",
                'email_primary' => 'hello@example.com',
                'email_support' => 'support@example.com',
                'phone'         => '+880 1700 000 000',
                'phone_hours'   => 'Sun–Thu, 9:00–18:00 (GMT+6)',
                'form_title'    => 'Tell us about your project',
                'form_lead'     => 'We reply to every message within one business day.',
            ],
        ];

        return $all[$section] ?? [];
    }
}
