<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MsvcAuthentication
{
    protected string $accessKeyCacheKey = 'msvc_access_key';
    /**
     * Name of service connection
     *
     * @var string
     */
    protected string $connectionName;
    /**
     * Table name for microservices map table
     *
     * @var string
     */
    protected string $accessTableName;
    /**
     * Database facade.
     *
     * @var DB
     */
    protected DB $db;

    /**
     * Current Microservice name
     *
     * @var string
     */
    protected string $currentMsvcName;

    /**
     * Request key of microservice access key
     *
     * @var string
     */
    protected string $msvcRequestAccessKey = 'msvc_access_key';

    /**
     * Mapping constructor.
     *
     * @param DB $dbFacade
     */
    public function __construct(DB $dbFacade)
    {
        $this->db = $dbFacade;

        $this->connectionName = config('database.msvc_default', 'msvc');
        $this->accessTableName = config('app.msvc_access_table_name', 'access');
        $this->currentMsvcName = config('app.name', 'msvc');
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $requestAccessKey = $request->input($this->msvcRequestAccessKey);

        if ($requestAccessKey) {
            $selfAccessKey = $this->getSelfAccessKey();

            if ($selfAccessKey and $requestAccessKey == $selfAccessKey) {
                $request->merge(['msvc_authenticated' => true]);
            }
        }


        return $next($request);
    }

    /**
     * Find microservice Access key in DB
     *
     * @return ?string
     */
    protected function getSelfAccessKey(): ?string
    {
        if (Cache::has($this->accessKeyCacheKey)){
            return Cache::get($this->accessKeyCacheKey);
        }

        $access = $this->db::connection($this->connectionName)->table($this->accessTableName)
            ->where('name', '=', $this->currentMsvcName)
            ->first();

        if (empty($access)) return null;
        $accessKey = $access->access_key;
        Cache::put($this->accessKeyCacheKey, $accessKey, now()->addHours(7));

        return $accessKey;
    }
}
