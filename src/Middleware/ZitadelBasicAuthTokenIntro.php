<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\LVMSVC\Auth\Zitadel\Config;
use Symfony\Component\HttpFoundation\Response;
use function MGGFLOW\LVMSVC\Auth\Zitadel\introspect_token_via_basic_auth;
use function MGGFLOW\LVMSVC\Auth\Zitadel\validate_intro;

class ZitadelBasicAuthTokenIntro
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     * @throws UniException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        $cacheKey = 'bearerToken_' . str($token);

        $config = config('auth.zitadel_config', new Config);
        $intro = Cache::remember($cacheKey, $config->tokenIntrospectionPeriod, function () use ($token, $config) {
            return introspect_token_via_basic_auth($token, $config, true);
        });
        validate_intro($intro);

        $request->attributes->set('user', $intro);

        return $next($request);
    }
}
