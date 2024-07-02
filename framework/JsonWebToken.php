<?php

require "vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JsonWebToken
{
    private static $privateKey = "private.key";
    private static $publicKey = "public.key";
    private static $alg = 'RS256';

    public static function generateToken($payload)
    {
        $privateKey = file_get_contents(self::$privateKey);
        $payload['exp'] = time() + 3600;

        $token = JWT::encode($payload, $privateKey, self::$alg);
        return $token;
    }

    public static function validateToken($jwt)
    {
        try {
            $publicKey = file_get_contents(self::$publicKey);
            $decoded = JWT::decode($jwt, new Key($publicKey, self::$alg));
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }
}
