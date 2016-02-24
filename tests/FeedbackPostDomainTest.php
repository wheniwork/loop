<?php

namespace FeedbackTests;

use Equip\Adr\PayloadInterface;
use Wheniwork\Feedback\Domain\DoGeneric;
use Wheniwork\Feedback\Exception\AuthorizationException;

class FeedbackPostDomainTest extends FeedbackTestBase
{
    protected function setUp()
    {
        // We construct a DoGeneric instance because it
        // implements FeedbackPostDomain.
        $this->domain = new DoGeneric(
            $this->getMockHipChatService(),
            $this->getMockDatabaseService(),
            $this->getMockAuthorizerThatFails()
        );
    }

    public function getMockAuthorizerThatFails()
    {
        $stub = $this->getMockAuthorizer();

        $stub->method('ensure')
            ->will($this->throwException(new AuthorizationException));

        return $stub;
    }

    public function testAuthFails()
    {
        $payload = $this->payloadForInput([
            'body' => parent::ITEM_BODY,
            'source' => parent::ITEM_SOURCE,
        ]);

        $this->assertSame(PayloadInterface::STATUS_BAD_REQUEST, $payload->getStatus());
        $this->assertArrayHasKey('error', $payload->getOutput());
    }
}
