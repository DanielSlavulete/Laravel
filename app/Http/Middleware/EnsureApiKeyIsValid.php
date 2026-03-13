<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;


class EnsureApiKeyIsValid
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->query('api_key');

        if ($apiKey !== config('services.wordpress.api_key')) {
            abort(403, 'API key inválida');
        }

        // Dominio permitido (WordPress)
        $allowedHost = config('app.wordpress_domain', 'inclusionmadrid21.org');

        $referer = $request->headers->get('referer');

        if ($referer && ! str_contains($referer, $allowedHost)) {
            abort(403, 'Origen no permitido');
        }

        return $next($request);
    }
}
