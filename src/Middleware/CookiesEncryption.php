<?php

namespace MGGFLOW\LVMSVC\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class CookiesEncryption extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [

    ];
}
