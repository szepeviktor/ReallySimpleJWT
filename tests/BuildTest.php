<?php

namespace Tests;

use ReallySimpleJWT\Build;
use ReallySimpleJWT\Validate;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\TokenBuilder;
use PHPUnit\Framework\TestCase;
use Carbon\Carbon;
use ReallySimpleJWT\Encode;
use ReallySimpleJWT\Token;

class BuildTest extends TestCase
{
    public function testBuild()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build);
    }

    public function testBuildSetSecret()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setSecret('Hello123$$Abc!!4538'));
    }

    /**
     * @expectedException ReallySimpleJWT\Exception\Validate
     * @expectedExceptionMessage Please set a valid secret. It must be at least twelve characters in length, contain lower and upper case letters, a number and one of the following characters *&!@%^#$.
     */
    public function testBuildSetSecretInvalid()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setSecret('Hello'));
    }

    public function testSetExpiration()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setExpiration(Carbon::now()->addMinutes(5)->getTimestamp()));
    }

    /**
     * @expectedException ReallySimpleJWT\Exception\Validate
     * @expectedExceptionMessage The expiration timestamp you set has already expired.
     */
    public function testSetExpirationInvalid()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setExpiration(Carbon::now()->subMinutes(5)->getTimestamp()));
    }

    public function testSetExpirationCheckPayload()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $timestamp = Carbon::now()->addMinutes(5)->getTimestamp();

        $build->setExpiration($timestamp);

        $this->assertSame($build->getPayload()['exp'], $timestamp);
    }

    public function testGetPayload()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $build->setExpiration(Carbon::now()->addMinutes(5)->getTimestamp());

        $this->assertArrayHasKey('exp', $build->getPayload());
    }

    public function testSetIssuer()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setIssuer('127.0.0.1'));
    }

    public function testSetIssuerCheckPayload()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $build->setIssuer('127.0.0.1');

        $this->assertSame($build->getPayload()['iss'], '127.0.0.1');
    }

    public function testSetPrivateClaim()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $this->assertInstanceOf(Build::class, $build->setPrivateClaim('user_id', 1));
    }

    public function testSetPrivateClaimCheckPayload()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $build->setPrivateClaim('user_id', 1);

        $this->assertSame($build->getPayload()['user_id'], 1);
    }

    public function testBuildMethod()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $token = $build->setSecret('helLLO123$!456ht')
            ->setIssuer('127.0.0.1')
            ->setExpiration(time() + 100)
            ->setPrivateClaim('user_id', 2)
            ->build();

        $this->assertInstanceOf(Jwt::class, $token);
    }

    public function testBuildMethodCheckJwt()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $token = $build->setSecret('!123$!456htHeLOOl!')
            ->setIssuer('https://google.com')
            ->setExpiration(time() + 200)
            ->setPrivateClaim('user_id', 3)
            ->build();

        $this->assertSame($token->getSecret(), '!123$!456htHeLOOl!');
        $this->assertRegExp('/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/', $token->getToken());
    }

    public function testBuildMethodParse()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $token = $build->setSecret('!123$!456htHeLOOl!')
            ->setIssuer('https://google.com')
            ->setExpiration(time() + 200)
            ->setPrivateClaim('user_id', 3)
            ->build();

        $parse = new Parse($token, new Validate, new Encode());

        $parsed = $parse->validate()
            ->validateExpiration()
            ->parse();

        $this->assertSame($parsed->getPayload()->user_id, 3);
    }

    public function testGetHeader()
    {
        $build = new Build('JWT', new Validate, new Encode);

        $result = $build->getHeader();

        $this->assertSame('JWT', $result['typ']);
        $this->assertSame('HS256', $result['alg']);
    }
}
