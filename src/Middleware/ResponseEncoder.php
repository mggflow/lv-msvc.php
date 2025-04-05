<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponseEncoder
{
    /**
     * ResponseEncoder constructor.
     */
    public function __construct()
    {
    }


    /**
     * Encode response as JSON.
     *
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            return $response;
        }

        if ($response instanceof BinaryFileResponse || $response->isRedirect()) {
            return $response;
        }

        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT) {
            return response()->json(null, Response::HTTP_NO_CONTENT);
        }

        $data = $response->original ?? $response->getContent();
        $response->headers->remove('Content-Type');
        $headers = $response->headers->all();

        return response()->json($data, $response->getStatusCode(), $headers);
    }
}
