<?php

namespace ReallySimpleJWT;

use ReallySimpleJWT\Build;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Validate;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Exception\Validate as ValidateException;
use Carbon\Carbon;

/**
 * A simple Package for creating JSON Web Tokens that uses HMAC SHA256 to sign
 * signatures. Exposes a simple interface to allow you to create a simple token
 * that stores a user identifier. The Package is set up to allow extension and
 * the use of larger payloads.
 *
 * For more information on JSON Web Tokens please see https://jwt.io
 *
 * @author Rob Waller <rdwaller1984@gmail.com>
 */

class Token
{
    /**
     * Create a JSON Web Token that contains a User Identifier Payload
     *
     * @param mixed $userId
     * @param string $secret
     * @param string $expiration
     * @param string $issuer
     *
     * @return string
     */
    public static function getToken($userId, string $secret, string $expiration, string $issuer): string
    {
        $builder = self::builder();

        return $builder->setPrivateClaim('user_id', $userId)
            ->setSecret($secret)
            ->setExpiration(Carbon::parse($expiration)->getTimestamp())
            ->setIssuer($issuer)
            ->build()
            ->getToken();
    }

    /**
     * Validate a JSON Web Token's expiration and signature
     *
     * @param string $token
     * @param string $secret
     *
     * @return bool
     */
    public static function validate(string $token, string $secret): bool
    {
        $parse = self::validator($token, $secret);

        try {
            $parse->validate()->validateExpiration();
            return true;
        } catch (ValidateException $e) {
            return false;
        }
    }

    /**
     * Return the payload of the token as a JSON string. You should run the
     * validate method on your token before retrieving the payload.
     *
     * @param string $token
     *
     * @return array
     */
    public static function getPayload(string $token, string $secret): array
    {
        $validator = self::validator($token, $secret);

        return $validator->validate()->parse()->getPayload();
    }

    /**
     * Interface to return instance of the token builder
     *
     * @return Build
     */
    public static function builder(): Build
    {
        return new Build('JWT', new Validate(), new Encode());
    }

    /**
     * Interface to return instance of the token validator
     *
     * @return Parse
     */
    public static function validator(string $token, string $secret): Parse
    {
        $jwt = new Jwt($token, $secret);

        return new Parse($jwt, new Validate(), new Encode());
    }
}
