<?php

namespace App\Http\Middleware;

use App\Models\Spa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the published spa named by the {spaSlug} route segment and the
 * guest's language, so controllers never look either up themselves.
 *
 * The language is ?lang= on pages and "locale" in JSON bodies; an unsupported
 * value falls back to the spa's default language.
 */
class ResolveSpa
{
    private const SPA = 'spa';

    private const LOCALE = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $spa = Spa::query()
            ->published()
            ->where('slug', $request->route('spaSlug'))
            ->firstOrFail();

        $requested = $request->query('lang', $request->input('locale'));

        $locale = in_array($requested, config('spa.locales'), true) ? $requested : $spa->default_locale;

        $request->attributes->set(self::SPA, $spa);
        $request->attributes->set(self::LOCALE, $locale);

        app()->setLocale($locale);

        return $next($request);
    }

    public static function spa(Request $request): Spa
    {
        return $request->attributes->get(self::SPA);
    }

    public static function locale(Request $request): string
    {
        return $request->attributes->get(self::LOCALE);
    }
}
