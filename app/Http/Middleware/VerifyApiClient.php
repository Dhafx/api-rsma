<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ApiClient;

class VerifyApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. Token not provided.'], 401);
        }

        // Hash token dari header dengan SHA-256 sebelum dicocokkan dengan database
        $hashedToken = hash('sha256', $token);
        $client = ApiClient::where('api_key', $hashedToken)->first();

        if (!$client || !$client->is_active) {
            return response()->json(['message' => 'Unauthorized. Invalid or inactive token.'], 401);
        }

        // IP Whitelist Check
        if (!empty($client->ip_whitelist)) {
            $allowedIps = array_map('trim', explode(',', $client->ip_whitelist));
            
            if (!in_array($request->ip(), $allowedIps)) {
                return response()->json(['message' => 'Forbidden. IP address not allowed.'], 403);
            }
        }

        return $next($request);
    }
}
