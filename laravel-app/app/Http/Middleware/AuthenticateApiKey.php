<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PpApi;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('MHS-PIPRAPAY-API-KEY');

        if (!$apiKey) {
            return response()->json([
                'error' => [
                    'code' => 'MISSING_API_KEY',
                    'message' => 'The API key is required in the MHS-PIPRAPAY-API-KEY header.'
                ]
            ], 401);
        }

        $apiRow = PpApi::where('api_key', $apiKey)
            ->where('status', 'active')
            ->first();

        if (!$apiRow) {
            return response()->json([
                'error' => [
                    'code' => 'INVALID_API_KEY',
                    'message' => 'The API key provided is incorrect or invalid.'
                ]
            ], 401);
        }

        if ($apiRow->expired_date && strtotime((string)$apiRow->expired_date) < time()) {
             return response()->json([
                'error' => [
                    'code' => 'EXPIRED_API_KEY',
                    'message' => 'The API key provided has expired.'
                ]
            ], 401);
        }

        // Attach API model to request for easy access in controllers
        $request->attributes->set('authenticated_api', $apiRow);

        return $next($request);
    }
}
