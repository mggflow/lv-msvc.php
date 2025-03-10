<?php

namespace MGGFLOW\LVMSVC\Routes;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use MGGFLOW\ExceptionManager\ManageException;

class ConfigureRateLimiting
{
    const int DEFAULT_MAX_REQUESTS_PER_MINUTE = 128;

    public static function configure(): void
    {
        $maxRequestsPerMinute = config('app.max_requests_per_minute', self::DEFAULT_MAX_REQUESTS_PER_MINUTE);
        RateLimiter::for('api', function (Request $request) use ($maxRequestsPerMinute) {
            return Limit::perMinute($maxRequestsPerMinute)
                ->by(self::genRequestKey($request))->response(function () {
                    throw ManageException::build()
                        ->log()->warning()->b()
                        ->desc()->tooMany(null, 'Requests')->b()
                        ->fill();
                });
        });
    }

    protected static function genRequestKey(Request $request): string
    {
        return (string)$request->ip();
    }
}