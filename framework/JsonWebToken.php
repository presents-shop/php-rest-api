<?php

class JsonWebToken
{
    private static $algorithm = JWT_ALGORITHM;
    private static $secretKey = JWT_SECRET_KEY;
    private static $salt = JWT_SALT;

    public static function generate(array $payload, $expiry = 3600)
    {
        $header = json_encode(["typ" => "JWT", "alg" => self::$algorithm]);
        $payload["exp"] = time() + $expiry;

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $combinedKey = self::$secretKey . self::$salt;
        $signature = hash_hmac("sha256", $base64UrlHeader . "." . $base64UrlPayload, $combinedKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validate($jwt)
    {
        list($header, $payload, $signature) = explode(".", $jwt);
        $header = json_decode(self::base64UrlDecode($header), true);
        $payload = json_decode(self::base64UrlDecode($payload), true);

        $combinedKey = self::$secretKey . self::$salt;
        $validSignature = hash_hmac("sha256", $header . "." . $payload, $combinedKey, true);
        $validSignature = self::base64UrlEncode($validSignature);

        if ($signature !== $validSignature) {
            throw new Exception("Invalid signature");
        }

        $now = time();
        if ($payload["exp"] < $now) {
            throw new Exception("Token has expired");
        }

        return $payload;
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(["+", "/", "="], ["-", "_", ""], base64_encode($data));
    }

    private static function base64UrlDecode($data)
    {
        return base64_decode(str_replace(["-", "_"], ["+", "/"], $data));
    }
}
