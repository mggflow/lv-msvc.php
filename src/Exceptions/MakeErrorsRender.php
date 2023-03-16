<?php

namespace MGGFLOW\LVMSVC\Exceptions;

use MGGFLOW\ExceptionManager\AccumulateException;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\ExceptionManager\ManageException;
use MGGFLOW\ExceptionManager\UnexpectedError;
use Throwable;

class MakeErrorsRender
{
    public static function make(?Throwable $e = null): array
    {
        if (!is_null($e) and !($e instanceof UniException)) {
            ManageException::build((new UnexpectedError())->import($e))
                ->log()->fatal()->b()
                ->desc()->internal()->b()
                ->fill();
        }

        $exceptions = AccumulateException::getAccumulated();

        $showSensitive = !config('app.debug', false);

        return array_map(function ($exception) use ($showSensitive) {
            if (method_exists($exception, 'toArray')) {
                return $exception->toArray($showSensitive);
            }

            return $exception;
        }, $exceptions);
    }
}
