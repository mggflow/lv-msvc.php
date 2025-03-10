<?php

namespace MGGFLOW\LVMSVC\Auth\Zitadel;

/**
 * Authentication config for Zitadel.
 */
class Config
{
    public function __construct(
        public ?string         $authDomain = null,
        public ?string         $introspectionUrl = null,
        public string|int|null $projectId = null,
        public string|int|null $basicAuthClientId = null,
        public ?string         $basicAuthClientSecret = null,
        public string|int|null $JWTClientId = null,
        public ?object         $JWTPrivateKeyData = null,
        public ?string         $personalAccessToken = null,
        public int             $tokenIntrospectionPeriod = 90,
        public string          $JWTKeyAlgorithm = 'RS256',
    )
    {
    }
}