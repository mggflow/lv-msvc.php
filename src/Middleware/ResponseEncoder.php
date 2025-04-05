<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ResponseEncoder
{
    /**
     * The Response Factory our app uses.
     *
     * @var ResponseFactory
     */
    protected ResponseFactory $factory;

    /**
     * ResponseEncoder constructor.
     *
     * @param ResponseFactory $factory
     */
    public function __construct(ResponseFactory $factory)
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

        if (is_array($data) || is_scalar($data) || $data === null || $data instanceof \JsonSerializable) {
            return response()->json($data, $response->getStatusCode(), $response->headers->all());
        }

        return $response;
    }
}
