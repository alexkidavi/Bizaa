<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $allowOrigin = $this->resolveAllowedOrigin($request->headers->get('Origin'));

        // Handle OPTIONS preflight request early
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $allowOrigin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        }

        // Proceed to the next middleware/controller
        $response = $next($request);

        // Define CORS headers
        $headers = [
            'Access-Control-Allow-Origin' => $allowOrigin,
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ];

        // Apply headers safely depending on response type
        foreach ($headers as $key => $value) {
            if (method_exists($response, 'header')) {
                $response->header($key, $value);
            } else {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }

    /**
     * Set CORS_ALLOWED_ORIGINS in .env (comma-separated) to lock this down
     * to known frontend origins. Left as '*' when unset, preserving prior
     * behavior until that's configured.
     */
    private function resolveAllowedOrigin(?string $origin): string
    {
        $configured = array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))));

        if (empty($configured)) {
            return '*';
        }

        return ($origin && in_array($origin, $configured, true)) ? $origin : $configured[0];
    }
}
