<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormatApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response instanceof JsonResponse && $this->isApiRequest($request)) {
            $original = $response->getData(true);

            return response()->json([
                'success' => false,
                'status_code' => $original['status_code'] ?? $response->getStatusCode(),
                'message' => $original['message'] ?? null,
                'data'  => $original['data'] ?? [],
            ], $response->getStatusCode());
        }

        return $response;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
