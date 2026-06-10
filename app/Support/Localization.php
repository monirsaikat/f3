<?php

namespace App\Support;

use Base;

/**
 * UI localization. Resolves the active language from (in order):
 *   1. ?lang= query parameter   (and remembers it in a cookie)
 *   2. the "lang" cookie         (previous choice)
 *   3. the en fallback
 *
 * The chosen code is pushed into F3's LANGUAGE, which loads the matching
 * dictionary from dict/ over the en fallback.
 */
final class Localization
{
    /** Supported languages: code => native name. */
    public const SUPPORTED = [
        'en' => 'English',
        'bn' => 'বাংলা',
        'es' => 'Español',
    ];

    /** Languages that read right-to-left (none here yet, but ready for e.g. 'ar'). */
    private const RTL = ['ar', 'he', 'fa', 'ur'];

    public static function boot(Base $f3): void
    {
        $lang = (string) ($f3->get('GET.lang') ?? '');

        if (!isset(self::SUPPORTED[$lang])) {
            $lang = (string) ($f3->get('COOKIE.lang') ?? '');
        }
        if (!isset(self::SUPPORTED[$lang])) {
            $lang = 'en';
        }

        // Loads dict/<lang>.php merged over the FALLBACK dictionary.
        $f3->set('LANGUAGE', $lang);

        // Remember the choice for 30 days.
        $f3->set('COOKIE.lang', $lang, 60 * 60 * 24 * 30);

        // Expose helpers for the templates.
        $f3->set('languages', self::SUPPORTED);
        $f3->set('current_lang', $lang);
        $f3->set('current_lang_name', self::SUPPORTED[$lang]);
        $f3->set('dir', in_array($lang, self::RTL, true) ? 'rtl' : 'ltr');
    }
}
