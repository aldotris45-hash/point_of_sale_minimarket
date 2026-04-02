<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Basic security headers
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', "geolocation=(), microphone=(), camera=()");

        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "img-src 'self' data:",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            // Allow Google Fonts stylesheet
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            // Allow font files from Google Fonts
            "font-src 'self' https://fonts.gstatic.com data:",
            "connect-src 'self' https://cdn.jsdelivr.net",
            "frame-src 'none'",
            // Do not allow our pages to be framed by other sites
            "frame-ancestors 'none'",
        ];

        // Only set CSP if not already present (allow overrides elsewhere)
        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // HSTS only in production over HTTPS
        if (app()->environment('production') && $request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
