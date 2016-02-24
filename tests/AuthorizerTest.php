<?php

namespace FeedbackTests;

use Wheniwork\Feedback\Exception\AuthorizationException;
use Wheniwork\Feedback\Service\Authorizer;

class AuthorizerTest extends FeedbackTestBase
{
    const KEY = 'test_key';

    private $authorizer;

    public function setUp()
    {
        $this->authorizer = new Authorizer(self::KEY);
    }

    public function testNoKey()
    {
        $this->setExpectedException(AuthorizationException::class);

        $this->authorizer->ensure([]);
    }

    public function testBadKey()
    {
        $this->setExpectedException(AuthorizationException::class);

        $this->authorizer->ensure([
            'key' => 'bad_key',
        ]);
    }

    public function testGoodKey()
    {
        $this->authorizer->ensure([
            'key' => self::KEY,
        ]);
    }
}
