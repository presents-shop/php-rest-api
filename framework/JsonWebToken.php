<?php

class JsonWebToken
{
    private static $secretKey = 'your-secret-key'; // Заменете със собствен ключ
    private static $algorithm = 'HS256'; // Алгоритъм за подписване

    /**
     * Генерира JWT.
     *
     * @param array $payload Данни за токена.
     * @param int $expiry Време на изтичане в секунди.
     * @return string
     */
    public static function generate(array $payload, $expiry = 3600)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload['exp'] = time() + $expiry;

        $base64UrlHeader = self::base64UrlEncode($header);
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    /**
     * Проверява валидността на JWT.
     *
     * @param string $jwt Токен за проверка.
     * @return bool|array False при неуспех, масив с payload при успех.
     */
    public static function validate($jwt)
    {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) {
            return false;
        }

        $base64UrlHeader = $tokenParts[0];
        $base64UrlPayload = $tokenParts[1];
        $base64UrlSignature = $tokenParts[2];

        $header = json_decode(self::base64UrlDecode($base64UrlHeader), true);
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);
        $signatureProvided = self::base64UrlDecode($base64UrlSignature);

        if ($header['alg'] !== self::$algorithm) {
            return false;
        }

        $expiry = $payload['exp'] ?? 0;
        if (time() >= $expiry) {
            return false;
        }

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, self::$secretKey, true);
        if (!hash_equals($signature, $signatureProvided)) {
            return false;
        }

        return $payload;
    }

    /**
     * Base64 URL Encode.
     *
     * @param string $data Данни за кодиране.
     * @return string
     */
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL Decode.
     *
     * @param string $data Данни за декодиране.
     * @return string
     */
    private static function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
