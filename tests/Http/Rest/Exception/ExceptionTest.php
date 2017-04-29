<?php
namespace Serato\SwsApp\Test\Http\Rest\Exception;

use Serato\SwsApp\Test\TestCase;
use Serato\SwsApp\Http\Rest\Exception\ClientUserGrantsException;
use Serato\SwsApp\Http\Rest\Exception\ExpiredAccessTokenException;
use Serato\SwsApp\Http\Rest\Exception\InvalidAccessTokenException;

/**
 * Unit tests for Exception classes in the Serato\SwsApp\Http\Rest\Exception
 * namespace
 */
class ExceptionTest extends TestCase
{
    public function testClientUserGrantsException()
    {
        try {
            throw new ClientUserGrantsException;
        } catch (ClientUserGrantsException $e) {
            $this->assertEquals($e->getHttpResponseCode(), 403);
            $this->assertEquals($e->getCode(), 2000);
        }
    }

    public function testExpiredAccessTokenException()
    {
        try {
            throw new ExpiredAccessTokenException;
        } catch (ExpiredAccessTokenException $e) {
            $this->assertEquals($e->getHttpResponseCode(), 401);
            $this->assertEquals($e->getCode(), 2002);
        }
    }

    public function testInvalidAccessTokenException()
    {
        try {
            throw new InvalidAccessTokenException;
        } catch (InvalidAccessTokenException $e) {
            $this->assertEquals($e->getHttpResponseCode(), 403);
            $this->assertEquals($e->getCode(), 2001);
        }
    }
}
