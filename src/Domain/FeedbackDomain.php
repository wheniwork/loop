<?php
namespace Wheniwork\Feedback\Domain;

use Spark\Adr\DomainInterface;
use Aura\Payload\Payload;
use Predis\Client as RedisClient;
use Wheniwork\Feedback\Service\GithubService;
use Wheniwork\Feedback\Service\HipChatService;

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

    protected function isTaggedFeedback($content)
    {
        return stripos($content, '#feedback') !== FALSE;
    }

    protected function createFeedback($body, $source)
    {
        HipChatService::postMessage("From $source: $body");
        GithubService::createIssue("Feedback from $source", $body);
    }
}
