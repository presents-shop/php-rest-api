<?php

require "vendor/autoload.php";

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\Clock\SystemClock;

class TokenService
{
    private static ?Configuration $config = null;

    private static function getConfig(): Configuration
    {
        if (self::$config === null) {
            self::$config = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText(EMAIL_VERIFY_SECRET_KEY)
            );
        }

        return self::$config;
    }

    public static function generateToken(): string
    {
        $config = self::getConfig();
        $now = new DateTimeImmutable();

        $token = $config->builder()
            ->issuedBy(WEBSITE_TITLE)
            ->issuedAt($now)
            ->expiresAt($now->modify(EMAIL_VERIFY_TOKEN_EXPIRY))
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

    public static function verifyToken(string $tokenString): bool
    {
        $config = self::getConfig();
        $token = $config->parser()->parse($tokenString);

        $constraints = [
            new SignedWith($config->signer(), $config->verificationKey()),
            new ValidAt(SystemClock::fromUTC())
        ];

        return $config->validator()->validate($token, ...$constraints);
    }
}