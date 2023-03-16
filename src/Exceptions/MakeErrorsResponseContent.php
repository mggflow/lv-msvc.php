<?php

namespace MGGFLOW\LVMSVC\Exceptions;

use Throwable;

class MakeErrorsResponseContent
{
    public static function make(?Throwable $e = null): array
    {
        return ['errors' => MakeErrorsRender::make($e)];
    }
}
