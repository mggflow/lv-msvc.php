<?php

namespace MGGFLOW\LVMSVC\Auth\Zitadel;

use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MGGFLOW\ExceptionManager\Interfaces\UniException;
use MGGFLOW\ExceptionManager\ManageException;

if (!function_exists('MGGFLOW\LVMSVC\Auth\Zitadel\validate_intro')) {
    /**
     * Validate token introspection result.
     * @param $intro
     * @return void
     * @throws UniException
     */
    function validate_intro($intro): void
    {
        if (empty($intro)) {
            throw ManageException::build()
                ->log()->error()->b()
                ->desc()->no(null, 'Token')->b()
                ->fill();
        }
        if (empty($intro['active'])) {
            throw ManageException::build()
                ->log()->error()->b()
                ->desc()->invalid(null, 'Token')->b()
                ->fill();
        }
        if ($intro['exp'] < time()) {
            throw ManageException::build()
                ->log()->error()->b()
                ->desc()->expired(null, 'Token')->b()
                ->fill();
        }
    }
}

if (!function_exists('MGGFLOW\LVMSVC\Auth\Zitadel\introspect_token_via_JWT_auth')) {
    /**
     * Introspect token via msvc JWT authentication.
     * @param $token
     * @param Config $config
     * @param true $assoc
     * @return mixed|null
     * @throws GuzzleException
     */
    function introspect_token_via_JWT_auth($token, Config $config, bool $assoc = true): mixed
    {
        if (empty($token)) {
            return null;
        }

        $appJWT = gen_app_auth_JWT($config);
        $client = new Client();
        $data = [
            'client_assertion_type' => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
            'client_assertion' => $appJWT,
            'token' => $token,
        ];

        if (!$config->introspectionUrl) {
            return null;
        }

        $response = $client->post($config->introspectionUrl, [
            'form_params' => $data,
        ]);

        return json_decode($response->getBody()->getContents(), $assoc);
    }
}

if (!function_exists('MGGFLOW\LVMSVC\Auth\Zitadel\introspect_token_via_basic_auth')) {
    /**
     * Introspect token via msvc basic authentication.
     * @param $token
     * @param Config $config
     * @param bool $assoc
     * @return mixed|null
     * @throws GuzzleException
     */
    function introspect_token_via_basic_auth($token, Config $config, bool $assoc = true): mixed
    {
        if (empty($token)) {
            return null;
        }

        if (empty($config->introspectionUrl)) {
            return null;
        }

        $client = new Client();
        $data = [
            'token' => $token,
            'token_type_hint' => 'access_token',
            'scope' => 'openid'
        ];
        $response = $client->post($config->introspectionUrl, [
            'form_params' => $data,
            'auth' => [
                $config->basicAuthClientId,
                $config->basicAuthClientSecret,
            ]
        ]);

        return json_decode($response->getBody()->getContents(), $assoc);
    }
}

if (!function_exists('MGGFLOW\LVMSVC\Auth\Zitadel\gen_app_auth_JWT')) {
    /**
     * Generate msvc JWT token for introspection.
     * @param Config $config
     * @param int $duration
     * @return string|null
     */
    function gen_app_auth_JWT(Config $config, int $duration = 60 * 60): ?string
    {
        if (empty($config->JWTPrivateKeyData)) {
            return null;
        }

        $payload = [
            'iss' => $config->JWTPrivateKeyData->clientId,
            'sub' => $config->JWTPrivateKeyData->clientId,
            'aud' => $config->authDomain,
            'exp' => time() + $duration,
            'iat' => time(),
        ];

        $headers = [
            'alg' => $config->JWTKeyAlgorithm,
            'kid' => $config->JWTPrivateKeyData->keyId,
        ];
        return JWT::encode(
            $payload, $config->JWTPrivateKeyData->key, $config->JWTKeyAlgorithm, head: $headers
        );
    }
}