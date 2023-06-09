<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\ExceptionManager\ManageException;

class Mapping
{
    /**
     * Name of service connection
     *
     * @var string
     */
    protected string $connectionName = 'msvc';
    /**
     * Table name for microservices map table
     *
     * @var string
     */
    protected string $mapTableName = 'map';
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
     * Request key of microservice name
     *
     * @var string
     */
    protected string $msvcRequestKey = 'msvc';

    /**
     * Mapping constructor.
     *
     * @param DB $dbFacade
     */
    public function __construct(DB $dbFacade)
    {
        $this->db = $dbFacade;

        $this->currentMsvcName = config('msvc.name', 'msvc');
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws UniException
     */
    public function handle(Request $request, Closure $next)
    {
        $incomingMSVCName = $request->input($this->msvcRequestKey);

        if ($incomingMSVCName != $this->currentMsvcName) {
            $redirect = $this->createRedirectResponse($incomingMSVCName);

            if ($redirect === false) {
                throw ManageException::build()
                    ->log()->info()->b()
                    ->desc()->not('MSVC')->found()->b()
                    ->fill();
            } else {
                return $redirect;
            }
        }

        return $next($request);
    }

    /**
     * Create response to redirect to other Microservice.
     *
     * @param string $incomingMSVCName
     * @return false|RedirectResponse
     */
    protected function createRedirectResponse(string $incomingMSVCName)
    {
        $msvcAddress = $this->findRequestMicroserviceAddress($incomingMSVCName);

        if ($msvcAddress !== false) {
            return Response::redirectTo($msvcAddress);
        }

        return false;
    }

    /**
     * Find microservice address by microservice name.
     *
     * @param string $incomingMSVCName
     * @return false|mixed
     */
    protected function findRequestMicroserviceAddress(string $incomingMSVCName)
    {
        $mapping = $this->db::connection($this->connectionName)->table($this->mapTableName)
            ->where('name', '=', $incomingMSVCName)
            ->first();

        if (!empty($mapping) and $mapping->redirect) {
            return $mapping->address;
        }

        return false;
    }
}
