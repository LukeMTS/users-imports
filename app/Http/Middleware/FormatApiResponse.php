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
            $statusCode = $original['status_code'] ?? $response->getStatusCode();
            $isSuccess = $original['success'] ?? $statusCode < Response::HTTP_BAD_REQUEST;

            return response()->json([
                'success' => $isSuccess,
                'status_code' => $statusCode,
                'message' => $original['message'] ?? null,
                'data'  => $original['data'] ?? [],
            ], $statusCode);
        }

        return $response;
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
}
