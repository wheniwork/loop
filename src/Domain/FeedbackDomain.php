<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Adr\DomainInterface;
use Aura\Payload\Payload;
use Predis\Client as RedisClient;

abstract class FeedbackDomain implements DomainInterface
{
    protected $redis;

    public function __construct(RedisClient $redis)
    {
        $this->redis = $redis;
    }

    public function getPayload()
    {
        return new Payload();
    }
}
