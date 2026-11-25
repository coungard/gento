<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Запоминает UTM-метки первого визита, чтобы приложить их к заявке.
 */
class TrackUtm
{
    public const SESSION_KEY = 'utm';

    /**
     * @var list<string>
     */
    public const PARAMETERS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    public function handle(Request $request, Closure $next): Response
    {
        $utm = array_filter(
            $request->only(self::PARAMETERS),
            fn (mixed $value): bool => is_string($value) && $value !== '',
        );

        if ($utm !== [] && session()->has(self::SESSION_KEY) === false) {
            session()->put(self::SESSION_KEY, array_map(
                fn (string $value): string => mb_substr($value, 0, 190),
                $utm,
            ));
        }

        return $next($request);
    }
}
